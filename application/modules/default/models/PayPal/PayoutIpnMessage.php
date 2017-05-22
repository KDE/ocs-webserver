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

class Default_Model_PayPal_PayoutIpnMessage extends Local_Payment_PayPal_AdaptivePayment_Ipn
{

    /** @var \Default_Model_Pling */
    protected $_tablePayout;

    function __construct($config = null, $logger = null)
    {
        if (null == $logger) {
            $logger = Zend_Registry::get('logger');
        }

        if (null == $config) {
            $config = Zend_Registry::get('config');
        }

        parent::__construct($config->third_party->paypal, $logger);

        $this->_tablePayout = new Default_Model_DbTable_MemberPayout();
    }
    
    
    /**
     * @param $rawData
     * @throws Exception
     */
    public function processIpn($rawData)
    {
        Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process Payout IPN in Default_Model_PayPal_PayoutIpnMessage - ');
        if (false === $this->verifyIpnOrigin($rawData)) {
            $this->_logger->err(__FUNCTION__ . '::Abort IPN processing. IPN not verified: ' . $rawData);
            return;
        }

        $this->_dataIpn = $this->_parseRawMessage($rawData);
        $this->_logger->info(__FUNCTION__ . '::_dataIpn: ' . print_r($this->_dataIpn, true) . "\n");

        $this->_ipnMessage = Local_Payment_PayPal_Response::buildResponse($this->_dataIpn);
        $this->_logger->info(__FUNCTION__ . '::_ipnMessage: ' . print_r($this->_ipnMessage, true) . "\n");

        if (false === $this->validateTransaction()) {
            $this->_logger->err(__FUNCTION__ . '::Abort IPN processing. Transaction not valid:' . $rawData);
            return;
        }

        $this->processPaymentStatus();

    }
    
    protected function processPaymentStatus()
    {
        Zend_Registry::get('logger')->info(__METHOD__ . ' - Status: ' .$this->_dataIpn['status']);
        
        switch ($this->_dataIpn['status']) {
            case 'COMPLETED':
                $this->_statusCompleted();
                break;
            case 'INCOMPLETE':
                $this->_statusIncomplete();
                break;
            case 'CREATED':
                $this->_statusCreated();
                break;
            case 'ERROR':
                $this->_statusError();
                break;
            case 'REVERSALERROR':
                $this->_statusReversalError();
                break;
            case 'PROCESSING':
                $this->_statusProcessing();
                break;
            case 'PENDING':
                $this->_statusPending();
                break;
            default:
                throw new Local_Payment_Exception('Unknown status from PayPal: ' . $this->_ipnMessage->getStatus());
        }
    }

    protected function validateTransaction()
    {
        $this->_dataIpn = $this->_tablePayout->fetchPayoutFromResponse($this->_ipnMessage)->toArray();
        if (empty($this->_dataIpn)) {
            $this->_logger->err(__METHOD__ . ' - ' . 'No transaction found for IPN message.' . PHP_EOL);
            return false;
        }

        $tableProject = new Default_Model_Project();
        $member = $tableProject->find($this->_dataIpn['project_id'])->current()->findDependentRowset('Default_Model_DbTable_Member', 'Owner')->current();

        return $this->_checkAmount() AND $this->_checkEmail($member->paypal_mail);
    }

    protected function _checkAmount()
    {
        $amount = isset($this->_dataIpn['amount']) ? $this->_dataIpn['amount'] : 0;
        $receiver_amount = (float)$amount - (float)$this->_config->facilitator_fee;
        $currency = new Zend_Currency('en_US');
        $this->_logger->debug(__METHOD__ . ' - ' . $this->_ipnMessage->getTransactionAmount() . ' == ' . $currency->getShortName() . ' ' . $receiver_amount);
        return $this->_ipnMessage->getTransactionAmount() == $currency->getShortName() . ' ' . $amount;
    }

    protected function _checkEmail()
    {
        $email = isset($this->_dataIpn['email']) ? $this->_dataIpn['email'] : '';
        $this->_logger->debug(__METHOD__ . ' - ' . $this->_ipnMessage->getTransactionReceiver() . ' == ' . $email);
        return $this->_ipnMessage->getTransactionReceiver() == $email;
    }

    protected function _statusCompleted()
    {
        Zend_Registry::get('logger')->info(__METHOD__);
        $this->processTransactionStatus();
    }
    
    protected function processTransactionStatus()
    {
        Zend_Registry::get('logger')->info(__METHOD__ . ' - IPN Status: ' .$this->_ipnMessage->getTransactionStatus());
        switch (strtoupper($this->_ipnMessage->getTransactionStatus())) {
            case 'COMPLETED':
                $this->_processTransactionStatusCompleted();
                break;
            case 'PENDING':
                $this->_processTransactionStatusPending();
                break;
            case 'REFUNDED':
                $this->_processTransactionStatusRefunded();
                break;
            case 'DENIED':
                $this->_processTransactionStatusDenied();
                break;
            default:
                throw new Local_Payment_Exception('Unknown transaction status from PayPal: ' . $this->_ipnMessage->getTransactionStatus());
        }
    }

    protected function _statusError()
    {
        $this->_tablePayout->deactivatePayoutFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusCompleted()
    {
        Zend_Registry::get('logger')->info(__METHOD__);
        $this->_tablePayout->activatePayoutFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusPending()
    {
        $this->_tablePayout->activatePayoutFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusRefunded()
    {
        $this->_tablePayout->deactivatePayoutFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusDenied()
    {
        $this->_tablePayout->deactivatePayoutFromResponse($this->_ipnMessage);
    }

} 