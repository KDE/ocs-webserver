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
class Default_Model_DbTable_MemberExternalId extends Local_Model_Table
{

    protected $_name = "member_external_id";
    protected $_keyColumnsForRow = array('external_id');

    protected $_key = 'external_id';

    protected $_defaultValues = array(
        'external_id' => 0,
        'member_id'   => 0,
        'created_at'  => null,
        'is_deleted'  => null
    );

    /**
     * @param int $identifier
     *
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function setDelete($identifier)
    {
        $sql = "UPDATE `{$this->_name}` SET `is_deleted` = 1 WHERE `{$this->_key}` = :id";
        $stmnt = $this->_db->query($sql, array('id' => $identifier));

        return $stmnt->rowCount();
    }

    /**
     * @param array|string $member_id
     *
     * @return int|void
     * @throws Exception
     */
    public function delete($member_id)
    {
        throw new Exception('Deleting of users is not allowed.');
    }

    public function createExternalId($member_id)
    {
        $sql = "INSERT INTO `{$this->_name}` (external_id, member_id) VALUES (SUBSTR(SHA(:memberId), 1, 20), :memberId)";
        $stmnt = $this->_db->query($sql, array('memberId' => $member_id));
        $sql = "SELECT external_id FROM `{$this->_name}` WHERE member_id = :memberId ORDER BY created_at DESC";
        $result = $this->_db->query($sql, array('memberId' => $member_id))->fetchAll();
        $id = $result[0]['external_id'];
        return $id;
    }

}