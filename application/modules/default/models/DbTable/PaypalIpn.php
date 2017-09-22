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
class Default_Model_DbTable_PaypalIpn extends Local_Model_Table
{
    protected $_keyColumnsForRow = array('id');
    protected $_key = 'id';
    protected $_name = "paypal_ipn";
    
    public function addFromIpnMessage($ipnArray, $raw) {
        $data = array();
        
        if(isset($ipnArray['txn_type'])) {
        
            $data['txn_type'] = $ipnArray['txn_type'];
            $data['ipn_track_id'] = $ipnArray['ipn_track_id'];
            $data['txn_id'] = $ipnArray['txn_id'];
            $data['payer_email'] = $ipnArray['payer_email'];
            $data['payer_id'] = $ipnArray['payer_id'];
            $data['auth_amount'] = $ipnArray['auth_amount'];
            $data['mc_currency'] = $ipnArray['mc_currency'];
            $data['mc_fee'] = $ipnArray['mc_fee'];
            $data['mc_gross'] = $ipnArray['mc_gross'];
            $data['memo'] = $ipnArray['memo'];
            $data['payer_status'] = $ipnArray['payer_status'];
            $data['payment_date'] = $ipnArray['payment_date'];
            $data['payment_fee'] = $ipnArray['payment_fee'];
            $data['payment_status'] = $ipnArray['payment_status'];
            $data['payment_type'] = $ipnArray['payment_type'];
            $data['pending_reason'] = $ipnArray['pending_reason'];
            $data['reason_code'] = $ipnArray['reason_code'];
            $data['custom'] = $ipnArray['custom'];
            $data['raw'] = $raw;
        
        } else if(isset($ipnArray['action_type']) && $ipnArray['action_type'] == 'PAY') {
            $data['txn_type'] = $ipnArray['transaction_type'];
            $data['ipn_track_id'] = $ipnArray['tracking_id'];
            $data['txn_id'] = $ipnArray['transaction[0].id'];
            $data['payer_email'] = $ipnArray['transaction[0].receiver'];
            $data['payer_id'] = $ipnArray['transaction[0].id_for_sender_txn'];
            $data['auth_amount'] = $ipnArray['transaction[0].amount'];
            $data['memo'] = $ipnArray['memo'];
            $data['payer_status'] = $ipnArray['transaction[0].status_for_sender_txn'];
            $data['payment_date'] = $ipnArray['reversal_date'];
            $data['payment_status'] = $ipnArray['transaction[0].status'];
            $data['payment_type'] = $ipnArray['transaction[0].paymentType'];
            $data['pending_reason'] = $ipnArray['transaction[0].pending_reason'];
            $data['reason_code'] = $ipnArray['reason_code'];
            $data['custom'] = $ipnArray['custom'];
            $data['raw'] = $raw;
        } else {
            $data['raw'] = $raw;
        }
        
        $this->save($data);
        
    }
    
}