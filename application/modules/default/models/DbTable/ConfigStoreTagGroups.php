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
class Default_Model_DbTable_ConfigStoreTagGroups extends Local_Model_Table
{

    protected $_name = "config_store_tag_group";

    protected $_keyColumnsForRow = array('config_store_taggroup_id');

    protected $_key = 'config_store_taggroup_id';

    public function delete($where)
    {
        $where = parent::_whereExpr($where);

        /**
         * Build the DELETE statement
         */
        $sql = "UPDATE " . parent::getAdapter()->quoteIdentifier($this->_name, true) . " SET `is_active` = 0, `deleted_at` = NOW() " . (($where) ? " WHERE $where" : '');

        /**
         * Execute the statement and return the number of affected rows
         */
        $stmt = parent::getAdapter()->query($sql);
        $result = $stmt->rowCount();

        return $result;
    }

} 