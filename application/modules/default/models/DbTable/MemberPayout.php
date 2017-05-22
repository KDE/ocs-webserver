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
class Default_Model_DbTable_MemberPayout extends Local_Model_Table
{
    protected $_keyColumnsForRow = array('id');
    protected $_key = 'id';
    protected $_name = "member_payout";
    
    public static $PAYOUT_STATUS_NEW = 0;
    public static $PAYOUT_STATUS_REQUESTED = 1;
    public static $PAYOUT_STATUS_PROCESSED = 10;
    public static $PAYOUT_STATUS_COMPLETED = 100;
    public static $PAYOUT_STATUS_DENIED = 999;
    public static $PAYOUT_STATUS_ERROR = 99;
    
    
    
    /**
     * @param int $status
     * @return all payouts with status = $status
     **/
    public function fetchAllPayouts($status=0)
    {
    
    	$sql = "
                SELECT
                    *
                FROM
                    ".$this->_name."
                WHERE
                    status = ".$status;
    
    	$result = $this->_db->fetchAll($sql);
    
    	return $result;
    
    }
    
    
    /**
     * Mark plings as payed.
     * So they can be used to pling.
     *
     * @param Local_Payment_ResponseInterface $payment_response
     *
     */
    public function activatePlingsFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id' => self::STATUS_PLINGED,
            'payment_transaction_id' => $payment_response->getTransactionId(),
            'payment_raw_Message' => serialize($payment_response->getRawMessage()),
            'payment_status' => $payment_response->getTransactionStatus(),
            'active_time' => new Zend_Db_Expr ('Now()')
        );

        $this->update($updateValues, "payment_reference_key='" . $payment_response->getPaymentId() . "'");
    }

    /**
     * @param Local_Payment_ResponseInterface $payment_response
     */
    public function deactivatePlingsFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id' => 0,
            'payment_status' => $payment_response->getTransactionStatus(),
            'payment_raw_error' => serialize($payment_response->getRawMessage())
        );

        $this->update($updateValues,
            "payment_transaction_id='" . $payment_response->getTransactionId() . "' and (status_id=1 or status_id=2)");

    }

    /**
     * @param Local_Payment_ResponseInterface $payment_response
     * @return null|\Zend_Db_Table_Row_Abstract
     */
    public function fetchPlingFromResponse($payment_response)
    {
        if ($payment_response->getPaymentId() != null) {
            $where = array('payment_reference_key = ?' => $payment_response->getPaymentId());
        } elseif ($payment_response->getTransactionId() != null) {
            $where = array('payment_transaction_id = ?' => $payment_response->getTransactionId());
        } else {
            return null;
        }

        return $this->fetchRow($where);

    }

    /**
     * @param Local_Payment_ResponseInterface $payment_response
     */
    public function updatePlingTransactionStatusFromResponse($payment_response)
    {
        $updateValues = array(
            'payment_status' => $payment_response->getTransactionStatus(),
            'payment_raw_error' => serialize($payment_response->getRawMessage())
        );

        $this->update($updateValues,
            "payment_transaction_id='" . $payment_response->getTransactionId() . "' and (status_id=0 or status_id=1 or status_id=2)");

    }
    
    
}