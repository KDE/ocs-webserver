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
 * Created: 27.09.2017
 */

class Local_Db_Select_Mysql extends Zend_Db_Select
{
    const SQL_CALC_FOUND_ROWS = 'sqlCalcFoundRows';

    // add other options as needed

    public function __construct(Zend_Db_Adapter_Abstract $adapter)
    {
        /**
         * Use array_merge() instead of simply setting a key
         * because the order of keys is significant to the
         * rendering of the query.
         */
        self::$_partsInit = array_merge(array(
            self::SQL_CALC_FOUND_ROWS => false
            // add other options as needed
        ), self::$_partsInit);
        parent::__construct($adapter);
    }

    public function sqlCalcFoundRows($flag = true)
    {
        $this->_parts[self::SQL_CALC_FOUND_ROWS] = (bool)$flag;

        return $this;
    }

    protected function _renderSqlCalcFoundRows($sql)
    {
        if ($this->_parts[self::SQL_CALC_FOUND_ROWS]) {
            $sql .= ' SQL_CALC_FOUND_ROWS';
        }

        return $sql;
    }

}