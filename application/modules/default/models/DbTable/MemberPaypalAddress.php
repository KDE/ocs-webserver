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
class Default_Model_DbTable_MemberPaypalAddress extends Local_Model_Table
{
    protected $_keyColumnsForRow = array('id');
    protected $_key = 'id';
    protected $_name = "member_paypal";
    
    /**
     * @param int $member_id
     */
    public function setDeleted($id)
    {
        $updateValues = array(
            'is_active'  => 0
        );

        $this->update($updateValues, $this->_db->quoteInto('id=?', $id, 'INTEGER'));

    }
    
}