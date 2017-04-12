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

class Default_Model_PayPal_MasspayIpnMessage extends Local_Payment_PayPal_Masspay_Ipn
{

    /** @var \Default_Model_Pling */
    protected $_tablePayment;
    protected $_payoutsArray;
    
    protected $_payer_id;
    protected $_payment_date;
    protected $_payment_status;
    protected $_charset;
    protected $_first_name;
    protected $_notify_version;
    protected $_payer_status;
    protected $_verify_sign;
    protected $_payer_email;
    protected $_payer_business_name;
    protected $_last_name;
    protected $_txn_type;
    protected $_residence_country;
    protected $_test_ipn;
    protected $_ipn_track_id;
    
    function __construct($config = null, $logger = null)
    {
        if (null == $logger) {
            $logger = Zend_Registry::get('logger');
        }

        if (null == $config) {
            $config = Zend_Registry::get('config');
        }

        parent::__construct($config->third_party->paypal, $logger);

        $this->_tablePayment = new Default_Model_DbTable_Payout();
        
    }

    protected function validateTransaction()
    {
        return true;
        /*
        $dataTransaction = $this->_tablePayment->fetchPlingFromResponse($this->_ipnMessage);
        if (null === $dataTransaction) {
            $this->_logger->err(__METHOD__ . ' - ' . 'No transaction found for IPN message.' . PHP_EOL);
            return false;
        }

        $tableProject = new Default_Model_Project();
        $member = $tableProject->find($dataTransaction->project_id)->current()->findDependentRowset('Default_Model_DbTable_Member', 'Owner')->current();

        return $this->_checkAmount($dataTransaction->amount) AND $this->_checkEmail($member->paypal_mail);
         */
    }

    protected function _checkAmount($amount)
    {
        $receiver_amount = (float)$amount - (float)$this->_config->facilitator_fee;
        $currency = new Zend_Currency('en_US');
        $this->_logger->debug(__METHOD__ . ' - ' . $this->_ipnMessage->getTransactionAmount() . ' == ' . $currency->getShortName() . ' ' . $receiver_amount);
        return $this->_ipnMessage->getTransactionAmount() == $currency->getShortName() . ' ' . $amount;
    }

    protected function _checkEmail($email)
    {
        $this->_logger->debug(__METHOD__ . ' - ' . $this->_ipnMessage->getTransactionReceiver() . ' == ' . $email);
        return $this->_ipnMessage->getTransactionReceiver() == $email;
    }

    protected function _statusCompleted()
    {
        $this->processTransactionStatus();
    }

    protected function _statusError()
    {
        //$this->_tablePayment->deactivatePlingsFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusCompleted()
    {
        //$this->_tablePayment->activatePlingsFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusPending()
    {
        //$this->_tablePayment->activatePlingsFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusRefunded()
    {
        //$this->_tablePayment->deactivatePlingsFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusDenied()
    {
        //$this->_tablePayment->deactivatePlingsFromResponse($this->_ipnMessage);
    }

    

} 