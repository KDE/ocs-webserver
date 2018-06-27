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
class Local_Validate_UsernameExists extends Zend_Validate_Abstract
{
    const EXISTS = 'already_exists';

    protected $_messageTemplates = array(
        self::EXISTS => 'Username already exists.'
    );

    public function isValid($value, $context = null)
    {
        $value = (string)$value;
        $this->_setValue($value);

        return $this->checkUsernameExist($value);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function checkUsernameExist($value)
    {
        $modelMember = new Default_Model_Member();
        $resultSet = $modelMember->findUsername($value, Default_Model_Member::CASE_INSENSITIVE);
        if (count($resultSet) > 0) {
            return true;
        }

        return false;
    }

}