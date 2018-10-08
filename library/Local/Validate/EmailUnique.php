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
class Local_Validate_EmailUnique extends Zend_Validate_Abstract
{
    const NOT_UNIQUE = 'mail_not_unique';

    protected $_messageTemplates = array(
        self::NOT_UNIQUE => 'mail address already exists.'
    );

    public function isValid($value, $context = null)
    {
        $value = (string)$value;
        $this->_setValue($value);

        return $this->isEmailUnique($value, $context);
    }

    /**
     * @param $mail
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    private function isEmailUnique($mail, $context)
    {
        $sql = "
        SELECT `mail`, COUNT(*) AS `amount`
        FROM `member` AS `m`
        WHERE `m`.`is_active` = 1 AND `m`.`is_deleted` = 0 
        AND lower(`mail`) = lower(:mail)
        GROUP BY lower(`mail`)
        HAVING COUNT(*) > 1
        ";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('mail' => $mail))->fetch();

        if ((false === empty($result)) AND $result['amount'] > 1) {
            $this->_error(self::NOT_UNIQUE);

            return false;
        }

        return true;
    }

}