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

abstract class Local_Payment_PayPal_Masspay_Ipn extends Local_Payment_PayPal_Base
{

    const VERIFIED = 'VERIFIED';

    /** @var \Zend_Config */
    protected $_config;
    /** @var \Zend_Log */
    protected $_logger;
    /** @var  array */
    protected $_dataIpn;
    /** @var  \Local_Payment_PayPal_PaymentInterface */
    protected $_ipnMessage;

    /**
     * @param $rawData
     * @throws Exception
     */
    public function processIpn($rawData)
    {
        if (false === $this->verifyIpnOrigin($rawData)) {
            $this->_logger->err(__FUNCTION__ . '::Abort IPN processing. IPN not verified: ' . $rawData);
            $this->_logger->info(__FUNCTION__ . '::Abort IPN processing. IPN not verified: ' . $rawData);
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

    /**
     * @param string $rawDataIpn
     * @return bool
     */
    public function verifyIpnOrigin($rawDataIpn)
    {
        $rawDataIpnCmd = 'cmd=_notify-validate&';
        $requestParams = $rawDataIpnCmd . $rawDataIpn;

        $url = $this->_config->masspay->ipn->endpoint . '/webscr';

        $response = $this->_makeRequest($requestParams, $url);

        if (strcmp($response, self::VERIFIED) == 0) {
            return true;
        }

        return false;
    }

    /**
     * @param string $requestData
     * @param string $url
     * @throws Local_Payment_Exception
     * @return string
     */
    protected function _makeRequest($requestData, $url)
    {
        $http = new Zend_Http_Client($url);
        $http->setMethod(Zend_Http_Client::POST);
        $http->setRawData($requestData);

        try {
            $response = $http->request();
        } catch (Zend_Http_Client_Exception $e) {
            throw new Local_Payment_Exception('Error while request PayPal website.', 0, $e);
        }

        if (false === $response) {
            $this->_logger->err(__FUNCTION__ . "::Error while request PayPal Website.\n Server replay was: " . $http->getLastResponse()->getStatus() . ". " . $http->getLastResponse()->getMessage() . "\n");
            $this->_logger->err(__FUNCTION__ . '::Last Request: ' . print_r($http->getLastRequest(), true));
            $this->_logger->err(__FUNCTION__ . '::Headers: ' . print_r($response->getHeaders(), true));
            $this->_logger->err(__FUNCTION__ . '::Body: ' . print_r($response->getBody(), true) . "\n");
        } else {
            $this->_logger->debug(__FUNCTION__ . '::Last Request: ' . print_r($http->getLastRequest(), true));
            $this->_logger->debug(__FUNCTION__ . '::Headers: ' . print_r($response->getHeaders(), true));
            $this->_logger->debug(__FUNCTION__ . '::Body: ' . print_r($response->getBody(), true) . "\n");
        }

        return $response->getBody();
    }

    /**
     * @return bool
     */
    protected function validateTransaction()
    {
        // Make sure the receiver email address is one of yours and the
        // amount of money is correct

        return $this->_checkEmail() AND $this->_checkTxnId() AND $this->_checkAmount();
    }

    /**
     * Check email address for validity.
     * Override this method to make sure you are the one being paid.
     * Throw an Exception if data is invalid or other things go wrong.
     *
     * $this->_dataIpn['receiver_email'] = The email who is about to receive payment.
     */
    protected function _checkEmail()
    {
        // check that receiver_email is your Primary PayPal email

        $this->_logger->info('Not doing _checkEmail(' . $this->_dataIpn['receiver_email'] . ')');

        return false;
    }

    /**
     * Check txnId has not already been used.
     * Override this method to ensure txnId is not a duplicate.
     * Throw an Exception if data is invalid or other things go wrong.
     *
     * $this->_dataIpn['txn_id'] = The transaction ID from paypal.
     */
    protected function _checkTxnId()
    {
        // check that txn_id has not been previously processed

        $this->_logger->info('Not doing _checkTxnId(' . $this->_ipnMessage->getTransactionId() . ')');

        return false;
    }

    /**
     * Check that the amount/currency is correct for item_id.
     * You should override this method to ensure the amount is correct.
     * Throw an Exception if data is invalid or other things go wrong.
     *
     * $this->_dataIpn['item_number'] = The item number
     * $this->_dataIpn['mc_gross']    = The amount being paid
     * $this->_dataIpn['mc_currency'] = Currency code of amount
     */
    protected function _checkAmount()
    {
        // check that payment_amount/payment_currency are correct

        $this->_logger->info('Not doing _checkAmount(' . $this->_dataIpn['mc_gross'] . ', ' . $this->_dataIpn['mc_currency'] . ')');

        return false;
    }

    protected function processPaymentStatus()
    {
        switch ($this->_dataIpn['payment_status']) {
            case 'Completed':
                $this->_statusCompleted();
                break;
            case 'Denied':
                $this->_statusDenied();
                break;
            case 'Processed':
                $this->_statusProcessed();
                break;
            default:
                throw new Local_Payment_Exception('Unknown status from PayPal: ' . $this->_dataIpn['ipn_track_id']);
        }
    }

    /**
     * Transaction/Payment completed.
     *
     * For Mass Payments, this means that all of your payments have been claimed, 
     * or after a period of 30 days, unclaimed payments have been returned to you. 
     */
    protected function _statusCompleted()
    {
        $this->_logger->info('Not doing anything in statusCompleted ' . $this->_dataIpn['ipn_track_id']);
    }

    /**
     * For Mass Payments, this means that your funds were not sent and the Mass Payment was not initiated. 
     * This may have been caused by lack of funds. 
     */
    protected function _statusDenied()
    {
        $this->_logger->info('Not doing anything in _statusDenied ' . $this->_dataIpn['ipn_track_id']);
    }

    /**
     * Your Mass Payment has been processed and all payments have been sent.
     */
    protected function _statusProcessed()
    {
        $this->_logger->info('Not doing anything in _statusProcessing ' . $this->_dataIpn['ipn_track_id']);
    }

    public function getCharset($rawDataIpn)
    {
        $matches = array();

        preg_match('|charset=(.*?)\&|', $rawDataIpn, $matches);

        return $matches[1];
    }

    protected function processTransactionStatus()
    {
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

    protected function _processTransactionStatusCompleted()
    {
        $this->_logger->info('Not doing anything in processTransactionStatusCompleted: ' . $this->_ipnMessage->getTransactionId());
    }

    protected function _processTransactionStatusPending()
    {
        $this->_logger->info('Not doing anything in processTransactionStatusPending: ' . $this->_ipnMessage->getTransactionId());
    }

    protected function _processTransactionStatusRefunded()
    {
        $this->_logger->info('Not doing anything in processTransactionStatusRefunded: ' . $this->_ipnMessage->getTransactionId());
    }

    protected function _processTransactionStatusDenied()
    {
        $this->_logger->info('Not doing anything in processTransactionStatusDenied: ' . $this->_ipnMessage->getTransactionId());
    }

} 