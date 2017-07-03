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
class Default_Model_DbTable_PaypalValidStatus extends Local_Model_Table
{
    protected $_keyColumnsForRow = array('id');
    protected $_key = 'id';
    protected $_name = "paypal_valid_status";
    
    public static $PAYOUT_STATUS_NEW = 0;
    public static $PAYOUT_STATUS_REQUESTED = 1;
    public static $PAYOUT_STATUS_PROCESSED = 10;
    public static $PAYOUT_STATUS_PENDING = 50;
    public static $PAYOUT_STATUS_COMPLETED = 100;
    public static $PAYOUT_STATUS_DENIED = 999;
    public static $PAYOUT_STATUS_REFUND = 900;
    public static $PAYOUT_STATUS_ERROR = 99;
    
    
    
    /**
     * @param int $id
     */
    public function setDeleted($id)
    {
        $updateValues = array(
            'is_active'  => 0
        );

        $this->update($updateValues, $this->_db->quoteInto('id=?', $id, 'INTEGER'));

    }
    
    /**
     * @return array
     * @deprecated
     */
    public function getStatiForSelectList()
    {
        $selectArr =
            $this->_db->fetchAll("SELECT id,title FROM {$this->_name} WHERE is_active=1 ORDER BY id");

        $arrayModified = array();

        $arrayModified[0] = "";
        foreach ($selectArr as $item) {
            $arrayModified[$item['id']] = stripslashes($item['title'] . $item['title']);
        }

        return $arrayModified;
    }
    
    
}