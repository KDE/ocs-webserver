<?php /** @noinspection PhpUndefinedFieldInspection */
/** @noinspection PhpUnusedPrivateMethodInspection */

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
 * */

namespace Application\Model\Service;

use Application\Model\Entity\Member;
use Application\Model\Repository\MemberRepository;
use Application\Model\Service\Interfaces\ReviewProfileDataServiceInterface;
use Laminas\Config\Config;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Regex;

class ReviewProfileDataService extends BaseService implements ReviewProfileDataServiceInterface
{

    const INVALID_USERNAME = 1;
    const INVALID_USERNAME_DEACTIVATED = 10;
    const INVALID_USERNAME_NOT_ALLOWED = 11;
    const INVALID_USERNAME_NOT_UNIQUE = 12;
    const INVALID_EMAIL = 2;
    const INVALID_EMAIL_DEACTIVATED = 20;
    const INVALID_EMAIL_NOT_UNIQUE = 21;
    const USERNAME_DEACTIVATED_TEXT = "_double";
    const EMAIL_DEACTIVATED_TEXT = "_double";

    protected $message;
    protected $errorCode;
    protected $db;
    private $usernameValidationChain = array('isUsernameDeactivated', 'isUsernameValid', 'isUsernameUnique');
    private $emailValidationChain = array('isEmailDeactivated', 'isEmailValid', 'isEmailUnique');
    private $memberRepository;
    /** @var Config */
    private $validation_rules;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
        $this->memberRepository = new MemberRepository($db);
        $this->errorCode = 0;
        $this->message = array();
        $this->validation_rules = $GLOBALS['ocs_config']->settings->validation->rules;
    }

    /**
     *
     * @param Member $member_data
     *
     * @return boolean
     */
    public function hasValidProfile($member_data)
    {
        $this->errorCode = 0;
        $this->message = array();

        $result = $this->hasValidUsername($member_data);
        if (false == $result) {
            return false;
        }

        $result = $this->hasValidEmail($member_data);
        if (false == $result) {
            return false;
        }

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
        $validatorEmail = new EmailAddress();

        if (false == $validatorEmail->isValid($member_data->mail)) {
            $this->message['email'][] = $validatorEmail->getMessages();
            $this->errorCode = $this::INVALID_EMAIL;

            return false;
        }

        return true;
    }

    /**
     * @param $member_data
     *
     * @return bool
     */
    private function isEmailUnique($member_data)
    {
        $sql = "
        SELECT `mail`, COUNT(*) AS `amount`
        FROM `member` AS `m`
        WHERE `m`.`is_active` = 1 AND `m`.`is_deleted` = 0 
        AND lower(`mail`) = lower(:mail)
        GROUP BY lower(`mail`)
        HAVING COUNT(*) > 1
        ";

        $result = $this->memberRepository->fetchRow($sql, array('mail' => $member_data->mail));

        if ((false === empty($result)) and $result['amount'] > 1) {
            $this->message['email'][] = array('email is not unique');
            $this->errorCode = $this::INVALID_EMAIL_NOT_UNIQUE;

            return false;
        }

        return true;
    }

    /**
     * @param $member_data
     *
     * @return bool
     */
    private function isEmailDeactivated($member_data)
    {
        if (strpos($member_data->mail, $this::EMAIL_DEACTIVATED_TEXT) > 0) {
            $this->message['email'][] = 'Email is deactivated';
            $this->errorCode = $this::INVALID_EMAIL_DEACTIVATED;

            return false;
        }

        return true;
    }

    /**
     * @param $member_data
     *
     * @return bool
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
     */
    private function isUsernameValid($member_data)
    {
//        $usernameValidChars = new \Laminas\Validator\Regex('/^(?=.{3,40}$)(?![-])(?!.*[-]{2})[a-zA-Z0-9-]+(?<![-])$/');
        $usernameValidChars = new Regex($this->validation_rules->login);

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
     */
    private function isUsernameUnique($member_data)
    {
        $sql = "
        SELECT MAX(`username`) AS `username`, MAX(`mail`) AS `mail`, COUNT(*) AS `amount`
        FROM `member` AS `m`
        WHERE `m`.`is_active` = 1 AND `m`.`is_deleted` = 0 AND lower(`username`) = lower(:username)
        GROUP BY lower(`username`) -- , mail
        HAVING COUNT(*) > 1
        ";

        $result = $this->memberRepository->fetchAll($sql, array('username' => $member_data->username));

        if (is_array($result) and count($result) > 0) {
            $this->message['username'][] = array('username is not unique');
            $this->errorCode = $this::INVALID_USERNAME_NOT_UNIQUE;

            return false;
        }

        return true;
    }

}
