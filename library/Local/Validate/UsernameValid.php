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
 **/
class Local_Validate_UsernameValid extends Zend_Validate_Abstract
{
    const INVALID   = 'regexInvalid';
    const NOT_MATCH = 'regexNotMatch';
    const ERROROUS  = 'regexErrorous';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID   => "Invalid type given. String, integer or float expected",
        self::NOT_MATCH => "'%value%' does not match our rules for username",
        self::ERROROUS  => "There was an internal error while checking input",
    );

    /**
     * @param mixed $value
     * @param null  $context
     *
     * @return bool
     * @throws Zend_Exception
     */
    public function isValid($value, $context = null)
    {
        $value = (string)$value;
        $this->_setValue($value);

        return $this->isUsernameValid($value, $context);
    }

    /**
     * @param $member_data
     *
     * @return bool
     * @throws Zend_Validate_Exception
     */
    private function isUsernameValid($username, $context)
    {
        $usernameValidChars = new Zend_Validate_Regex('/^(?=.{4,20}$)(?![-])(?!.*[-]{2})[a-z0-9-]+(?<![-])$/');

        if (false == $usernameValidChars->isValid($username)) {
            $this->setMessages($usernameValidChars->getMessages());

            return false;
        }

        return true;
    }

}