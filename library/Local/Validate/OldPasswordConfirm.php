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

class Local_Validate_OldPasswordConfirm extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notmatch';
    const IS_EMPTY = 'empty';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Current password isn\'t correct',
        self::IS_EMPTY => 'Please enter your old Password'
    );

    public function isValid($value, $context = null)
    {
        if (!isset($value) || $value == '') {
            $this->_error(self::IS_EMPTY);
            return false;
        }

        $value = (string)$value;
        $this->_setValue($value);


        $memberModel = new Default_Model_Member();
        $user = $memberModel->fetchMemberData(Zend_Auth::getInstance()->getIdentity()->member_id);

        if (Local_Auth_Adapter_Ocs::getEncryptedPassword($value, $user->password_type) == $user->password) {
            return true;
        }

        $this->_error(self::NOT_MATCH);
        return false;
    }

}