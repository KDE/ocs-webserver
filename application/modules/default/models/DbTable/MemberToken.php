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
 *    Created: 09.12.2016
 **/
class Default_Model_DbTable_MemberToken extends Local_Model_Table
{

    protected $_name = "member_token";

    protected $_keyColumnsForRow = array('token_member_id', 'token_provider_name');

    protected $_key = 'token_id';

    protected $_defaultValues = array(
        'token_member_id' => null,
        'token_provider_name' => null,
        'token_value' => 0,
        'token_provider_username' => null,
        'token_fingerprint' => 0,
        'token_created' => null,
        'token_changed' => null,
        'token_deleted' => null
    );

    /**
     * @param int $identifer
     * @return int
     */
    public function setDeleted($identifer)
    {
        return $this->delete($identifer);
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete($id)
    {
        $sql = "UPDATE `{$this->_name}` SET `token_deleted` = NOW() WHERE `{$this->_key}` = :elementId";
        $stmnt = $this->_db->query($sql, array('elementId' => $id));
        return $stmnt->rowCount();
    }

}