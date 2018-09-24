<?php

/**
 *  ocs-webserver
 *
 *  Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-webserver.
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Created: 03.09.2018
 */
class Default_Model_ReviewProfileData
{

    const INVALID_USERNAME = 1;
    const INVALID_EMAIL = 2;
    
    const INVALID_USERNAME_DEACTIVATED = 10;
    const INVALID_USERNAME_NOT_ALLOWED = 11;
    const INVALID_USERNAME_NOT_UNIQUE = 12;
    
    const USERNAME_DEACTIVATED_TEXT = "_deactivated";
    

    protected $message;
    protected $errorCode;

    private $usernameValidationChain = array('isUsernameDeactivated', 'isUsernameValid', 'isUsernameUnique');
    private $emailValidationChain = array('isEmailValid', 'isEmailUnique');

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->errorCode = 0;
    }


    public function hasValidProfile($member_data)
    {
        $result = $this->hasValidUsername($member_data);
        if (false == $result) {
            return false;
        }
        /*
        $result = $this->hasValidEmail($member_data);
        if (false == $result) {
            return false;
        }
        */

        return true;
    }

    /**
     * @param $member_data
     *
     * @return boolean
     */
    public function hasValidUsername($member_data)
    {
        $result = true;
        foreach ($this->usernameValidationChain as $validator) {
            $result = $this->$validator($member_data);
            if (false == $result) {
                //$this->errorCode |= self::INVALID_USERNAME;
                return false;
            }
        }

        return $result;
    }

    public function hasValidEmail($member_data)
    {
        $result = true;
        foreach ($this->emailValidationChain as $validator) {
            $result = $this->$validator($member_data);
            if (false == $result) {
                //$this->errorCode |= self::INVALID_EMAIL;
                return false;
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param $member_data
     *
     * @return bool
     */
    private function isEmailValid($member_data)
    {
        $validatorEmail = new Zend_Validate_EmailAddress();

        if (false == $validatorEmail->isValid($member_data->mail)) {
            $this->message['email'][] = $validatorEmail->getMessages();

            return false;
        }

        return true;
    }

    /**
     * @param $member_data
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    private function isEmailUnique($member_data)
    {
        $sql = "
        SELECT `mail`, COUNT(*) AS `amount`
        FROM `member` AS `m`
        WHERE `m`.`is_active` = 1 AND `m`.`is_deleted` = 0 AND lower(`mail`) = lower(:mail)
        GROUP BY lower(`mail`)
        HAVING COUNT(*) > 1
        ";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('mail' => $member_data->mail))->fetch();

        if ((false === empty($result)) AND $result['amount'] > 1) {
            $this->message['email'][] = array('email is not unique');

            return false;
        }

        return true;
    }
    
    /**
     * @param $member_data
     *
     * @return bool
     * @throws Zend_Validate_Exception
     */
    private function isUsernameDeactivated($member_data)
    {
        if (strpos($member_data->username, $this::USERNAME_DEACTIVATED_TEXT) > 0) {
            $this->message['username'][] = 'User is deactivated';
            $this->errorCode = $this::INVALID_USERNAME_DEACTIVATED;

            return false;
        }

        return true;
    }

    /**
     * @param $member_data
     *
     * @return bool
     * @throws Zend_Validate_Exception
     */
    private function isUsernameValid($member_data)
    {
        $usernameValidChars = new Zend_Validate_Regex('/^(?=.{3,40}$)(?![-])(?!.*[-]{2})[a-zA-Z0-9-]+(?<![-])$/');

        if (false == $usernameValidChars->isValid($member_data->username)) {
            $this->message['username'][] = $usernameValidChars->getMessages();
            $this->errorCode = $this::INVALID_USERNAME_NOT_ALLOWED;

            return false;
        }

        return true;
    }

    /**
     * @param $member_data
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    private function isUsernameUnique($member_data)
    {
        $sql = "
        SELECT `username`, `mail`, COUNT(*) AS `amount`
        FROM `member` AS `m`
        WHERE `m`.`is_active` = 1 AND `m`.`is_deleted` = 0 AND lower(`username`) = lower(:username)
        GROUP BY lower(`username`) -- , mail
        HAVING COUNT(*) > 1
        ";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('username' => $member_data->username))->fetch();

        if (is_array($result) AND count($result) > 0) {
            $this->message['username'][] = array('username is not unique');
            $this->errorCode = $this::INVALID_USERNAME_NOT_UNIQUE;
            return false;
        }

        return true;
    }

}