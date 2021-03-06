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
        Zend_Registry::get('logger')->info(__METHOD__ . ' - Status: ' .$this->_ipnMessage->getTransactionStatus());
        
        switch ($this->_ipnMessage->getTransactionStatus()) {
            case 'COMPLETED':
                $this->_statusCompleted();
                break;
            case 'INCOMPLETE':
                $this->_statusIncomplete();
                break;
            case 'CREATED':
                $this->_statusCreated();
                break;
            case 'DENIED':
               	$this->_statusDenied();
               	break;
            case 'REVERSED':
               	$this->_statusReserved();
               	break;
            case 'REFUNDED':
               	$this->_statusRefunded();
               	break;
            case 'FAILED':
               	$this->_statusFailed();
               	break;
            case 'PARTIALLY_REFUNDED':
               	$this->_statusPartiallyRefunded();
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
                
                //No normal transaction status, so look into status_for_sender_txn
                switch ($this->_ipnMessage->getTransactionForSenderStatus()) {
                    case 'COMPLETED':
                        $this->_statusCompleted();
                        break;
                    case 'INCOMPLETE':
                        $this->_statusIncomplete();
                        break;
                    case 'CREATED':
                        $this->_statusCreated();
                        break;
                    case 'DENIED':
                        $this->_statusDenied();
                        break;
                    case 'REVERSED':
                        $this->_statusReserved();
                        break;
                    case 'REFUNDED':
                        $this->_statusRefunded();
                        break;
                    case 'FAILED':
                        $this->_statusFailed();
                        break;
                    case 'PARTIALLY_REFUNDED':
                        $this->_statusPartiallyRefunded();
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
                        throw new Local_Payment_Exception('2. Unknown transaction status from PayPal: IPN = ' . print_r($this->_ipnMessage));
                }
                
                throw new Local_Payment_Exception('1. Unknown transaction status from PayPal: IPN = ' . print_r($this->_ipnMessage));
        }
    }

    protected function validateTransaction()
    {
        $this->_dataIpn = $this->_tablePayout->fetchPayoutFromResponse($this->_ipnMessage)->toArray();
        if (empty($this->_dataIpn)) {
            $this->_logger->err(__METHOD__ . ' - ' . 'No transaction found for IPN message.' . PHP_EOL);
            return false;
        }
        
        $ckAmount = $this->_checkAmount();
        $ckEmail = $this->_checkEmail($this->_dataIpn['paypal_mail']);

        return $ckAmount AND $ckEmail;
    }

    protected function _checkAmount()
    {
        $amount = isset($this->_dataIpn['amount']) ? $this->_dataIpn['amount'] : 0;
        $receiver_amount = (float)$amount - (float)$this->_config->facilitator_fee;
        $currency = new Zend_Currency('en_US');
        $this->_logger->info(__METHOD__ . ' - ' . $this->_ipnMessage->getTransactionAmount() . ' == ' . $currency->getShortName() . ' ' . number_format((float)$amount, 2, '.', ''));
        return ($this->_ipnMessage->getTransactionAmount()) == $currency->getShortName() . ' ' . number_format((float)$amount, 2, '.', '');
    }

    protected function _checkEmail()
    {
        //$email = isset($this->_dataIpn['email']) ? $this->_dataIpn['email'] : '';
        //$this->_logger->info(__METHOD__ . ' - ' . $this->_ipnMessage->getTransactionReceiver() . ' == ' . $email);
        //return $this->_ipnMessage->getTransactionReceiver() == $email;
        
        return true;
    }

    protected function _statusCompleted()
    {
        Zend_Registry::get('logger')->info(__METHOD__);
        $this->_processTransactionStatusCompleted();
    }
    protected function _statusDenied()
    {
    	Zend_Registry::get('logger')->info(__METHOD__);
    	$this->_processTransactionStatusDenied();
    }
    protected function _statusReserved()
    {
    	Zend_Registry::get('logger')->info(__METHOD__);
    	$this->_processTransactionStatusReserved();
    }
    protected function _statusRefunded()
    {
    	Zend_Registry::get('logger')->info(__METHOD__);
    	$this->_processTransactionStatusRefunded();
    }
    protected function _statusPending()
    {
    	Zend_Registry::get('logger')->info(__METHOD__);
    	$this->_processTransactionStatusPending();
    }
    protected function _statusFailed()
    {
    	Zend_Registry::get('logger')->info(__METHOD__);
    	$this->_processTransactionStatusFailed();
    }
    
    protected function processTransactionStatus()
    {
        Zend_Registry::get('logger')->info(__METHOD__ . ' - IPN Status: ' .$this->_ipnMessage->getTransactionStatus());
        $this->_tablePayout->updatePayoutTransactionStatusFromResponse($this->_ipnMessage);
        
        
        switch (strtoupper($this->_ipnMessage->getTransactionStatus())) {
            case 'COMPLETED':
                $this->_processTransactionStatusCompleted();
                break;
            case 'PENDING':
                $this->_processTransactionStatusPending();
                break;
            case 'DENIED':
                $this->_processTransactionStatusDenied();
                break;
            case 'REVERSED':
                $this->_processTransactionStatusReserved();
                break;
            case 'REFUNDED':
                $this->_processTransactionStatusRefunded();
                break;
            case 'FAILED':
                $this->_processTransactionStatusFailed();
                break;
            default:
                switch (strtoupper($this->_ipnMessage->getTransactionForSenderStatus())) {
                    case 'PENDING':
                        $this->_processTransactionStatusPending();
                        break;
                    default:
                        throw new Local_Payment_Exception('Unknown transaction status from PayPal: TransactionStatus = ' . $this->_ipnMessage->getTransactionStatus() . ' --- TransactionForSenderStatus = ' . $this->_ipnMessage->getTransactionForSenderStatus);
                }
                throw new Local_Payment_Exception('Unknown transaction status from PayPal: TransactionStatus = ' . $this->_ipnMessage->getTransactionStatus() . ' --- TransactionForSenderStatus = ' . $this->_ipnMessage->getTransactionForSenderStatus);
        }
    }

    protected function _statusError()
    {
        $this->_tablePayout->deactivatePayoutFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusCompleted()
    {
        Zend_Registry::get('logger')->info(__METHOD__);
        $this->_tablePayout->setPayoutStatusCompletedFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusPending()
    {
        $this->_tablePayout->setPayoutStatusPendingFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusRefunded()
    {
        $this->_tablePayout->setPayoutStatusRefundFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusDenied()
    {
        $this->_tablePayout->setPayoutStatusDeniedFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusReserved()
    {
        $this->_tablePayout->setPayoutStatusReservedFromResponse($this->_ipnMessage);
    }
    
    protected function _processTransactionStatusFailed()
    {
        $this->_tablePayout->setPayoutStatusFailedFromResponse($this->_ipnMessage);
    }
} 