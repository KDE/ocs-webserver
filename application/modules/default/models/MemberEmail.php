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
 *    Created: 22.09.2016
 **/
class Default_Model_MemberEmail
{
    /** @var string */
    protected $_dataTableName;
    /** @var  Default_Model_DbTable_Comments */
    protected $_dataTable;

    /**
     * @inheritDoc
     */
    public function __construct($_dataTableName = 'Default_Model_DbTable_MemberEmail')
    {
        $this->_dataTableName = $_dataTableName;
        $this->_dataTable = new $this->_dataTableName;
    }

    /**
     * @param int $member_id
     * @param bool $email_deleted
     * @return array
     */
    public function fetchAllMailAdresses($member_id, $email_deleted = false)
    {
        $deleted = $email_deleted === true ? Default_Model_DbTable_MemberEmail::EMAIL_DELETED : Default_Model_DbTable_MemberEmail::EMAIL_NOT_DELETED;
        $sql = "SELECT * FROM {$this->_dataTable->info('name')} WHERE `email_member_id` = :memberId AND `email_deleted` = :emailDeleted";
        $stmnt = $this->_dataTable->getAdapter()->query($sql, array('memberId' => $member_id, 'emailDeleted' => $deleted));
        return $stmnt->fetchAll();
    }


}