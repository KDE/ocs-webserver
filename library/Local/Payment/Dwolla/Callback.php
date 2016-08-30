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

abstract class Local_Payment_Dwolla_Callback
{

    /** @var \Zend_Config */
    protected $_config;
    /** @var \Zend_Log */
    protected $_logger;
    /** @var  array */
    protected $_dataCallback;
    /** @var  Local_Payment_Dwolla_ResponseInterface */
    protected $_transactionMessage;


    /**
     * @param $config
     * @param null $logger
     * @throws Exception
     */
    function __construct($config, $logger = null)
    {
        if (is_array($config)) {
            $this->_config = new Zend_Config($config);
        } else {
            if ($config instanceof Zend_Config) {
                $this->_config = $config;
            }
        }
        if (is_null($logger)) {
            $this->_logger = Zend_Registry::get('logger');
        } else {
            if ($logger instanceof Zend_Log) {
                $this->_logger = $logger;
            } else {
                throw new Exception('Logger must be an instance of Zend_Log');
            }
        }
    }

    /**
     * @param string $rawData
     */
    public function processCallback($rawData)
    {
        $this->_dataCallback = $this->_decodeData($rawData);

        $this->_transactionMessage = Local_Payment_Dwolla_Response::buildResponse($this->_dataCallback);
        $this->_transactionMessage->setMsgBody($rawData);
        if (APPLICATION_ENV != 'production') {
            $this->_logger->debug(__METHOD__ . ' - _transactionMessage - ' . print_r($this->_transactionMessage, true) . PHP_EOL);
        }

        if (false === $this->_transactionMessage->verifySignature($this->_config->consumer->access_secret)) {
            $this->_logger->err(__METHOD__ . ' - Abort Dwolla callback processing. Message not valid - ' . print_r($rawData, true) . PHP_EOL);
            return;
        }

        if (false === $this->validateTransaction()) {
            $this->_logger->err(__METHOD__ . ' - Abort Dwolla callback processing. Transaction not valid - ' . print_r($rawData, true) . PHP_EOL);
            return;
        }

        $this->_processPaymentStatus();
    }

    /**
     * @param $rawData
     * @return array
     */
    protected function _decodeData($rawData)
    {
        return json_decode($rawData, true);
    }

    protected function validateTransaction()
    {

        return $this->_checkCheckoutId() AND $this->_checkAmount();

    }

    /**
     * Check CheckoutId has not already been used.
     * Override this method to ensure CheckoutId is not a duplicate.
     * Throw an Exception if data is invalid or other things go wrong.
     */
    protected function _checkCheckoutId()
    {
        // check that CheckoutId has not been previously processed

        $this->_logger->err(__METHOD__ . ' - not implemented - ' . PHP_EOL);

        return false;
    }

    /**
     * Check that the amount is correct for item_id.
     * You should override this method to ensure the amount is correct.
     * Throw an Exception if data is invalid or other things go wrong.
     */
    protected function _checkAmount()
    {
        // check that payment_amount/payment_currency are correct

        $this->_logger->err(__METHOD__ . ' - not implemented - ' . PHP_EOL);

        return false;
    }

    protected function _processPaymentStatus()
    {
        switch ($this->_transactionMessage->getStatus()) {
            case 'COMPLETED':
                $this->_statusCompleted();
                break;
            case 'FAILED':
                $this->_statusError();
                break;
            case 'PENDING':
                $this->_statusPending();
                break;
            case 'PROCESSED':
                $this->_statusProcessed();
                break;
            case 'CANCELLED':
                $this->_statusCancelled();
                break;
            default:
                throw new Exception('Unknown status from dwolla: ' . $this->_dataCallback['Status']);
        }

    }

    /**
     * Transaction/Payment completed.
     *
     * This is typically the most important method you'll need to override to perform
     * some sort of action when a successful transaction has been completed.
     *
     * You could override the other status's (such as reverse or denied) to
     * reverse whatever was done, but that could interfere if you're denying a
     * payment or refunding someone for a good reason. In those cases, it's
     * probably best to simply do whatever steps are required manually.
     */
    protected function _statusCompleted()
    {
        $this->_logger->err(__METHOD__ . ' - not implemented - ' . PHP_EOL);
    }

    /**
     * The payment failed and all attempted transfers failed or all completed transfers were successfully reversed
     */
    protected function _statusError()
    {
        $this->_logger->err(__METHOD__ . ' - not implemented - ' . PHP_EOL);
    }

    protected function _statusPending()
    {
        $this->_logger->err(__METHOD__ . ' - not implemented - ' . PHP_EOL);
    }

    protected function _statusProcessed()
    {
        $this->_logger->err(__METHOD__ . ' - not implemented - ' . PHP_EOL);
    }

    protected function _statusCancelled()
    {
        $this->_logger->err(__METHOD__ . ' - not implemented - ' . PHP_EOL);
    }

    /**
     * @param $proposedSignature
     * @param $checkoutId
     * @param $amount
     * @return bool
     */
    protected function verifyGatewaySignature($proposedSignature, $checkoutId, $amount)
    {
        $amount = number_format($amount, 2);
        $signature = hash_hmac("sha1", "{$checkoutId}&{$amount}", $this->_config->consumer->access_secret);

        return $signature == $proposedSignature;
    }

    protected function verifyWebhookSignature($body)
    {
        // Get Dwolla's signature
        $headers = getallheaders();
        $signature = $headers['X-Dwolla-Signature'];

        // Calculate hash, and compare to the signature
        $hash = hash_hmac('sha1', $body, $this->_config->consumer->access_secret);

        return ($hash == $signature);
    }

    private function fetchDetailedTransactionInformation()
    {
        // Create request body
        $requestBody = array();

        $response = $this->_makeRequest($requestBody, 'rest/transactions', false);
        if (APPLICATION_ENV != 'production') {
            $this->_logger->debug(__METHOD__ . ' - response - ' . print_r($response, true) . PHP_EOL);
        }

        $lastResponse = new Local_Payment_Dwolla_ResponsePayRequest($response);

        if (false === $lastResponse->isSuccessful()) {
            throw new Local_Payment_Exception('Dwolla payment request failed. Request response:' . print_r($lastResponse->getRawMessage(),
                    true));
        }

        return $lastResponse;
    }

    /**
     * @param array $request
     * @param string $apiNameOperation
     * @param bool $withAuthHeader
     * @throws Local_Payment_Exception
     * @return array
     */
    protected function _makeRequest($request, $apiNameOperation, $withAuthHeader = true)
    {
        $url = $this->_config->api->endpoint . '/' . $apiNameOperation;
        $http = new Zend_Http_Client($url);
        if (true === $withAuthHeader) {
            $http->setHeaders($this->_buildHeader($this->_config));
        }
        $http->setHeaders('Content-Type', 'application/json');
        $http->setMethod(Zend_Http_Client::POST);
        $http->setRawData(json_encode($request));

        try {
            $response = $http->request();
        } catch (Zend_Http_Client_Exception $e) {
            throw new Local_Payment_Exception('Error while request Dwolla website.', 0, $e);
        }

        if (false === $response) {
            $logMsg = __METHOD__ . "::Error while request Dwolla Website.\n Server replay was: " . $http->getLastResponse()->getStatus() . PHP_EOL . $http->getLastResponse()->getMessage() . PHP_EOL;
            $logMsg .= __METHOD__ . '::' . print_r($http->getLastRequest(), true) . PHP_EOL;
            $logMsg .= __METHOD__ . '::' . print_r($response->getHeaders(), true) . PHP_EOL;
            $logMsg .= __METHOD__ . '::' . print_r($response->getBody(), true) . PHP_EOL;
            $this->_logger->err($logMsg);
            throw new Local_Payment_Exception('Error while request Dwolla website.');
        } else {
            $logMsg = __METHOD__ . '::' . print_r($http->getLastRequest(), true) . PHP_EOL;
            $logMsg .= __METHOD__ . '::' . print_r($response->getHeaders(), true) . PHP_EOL;
            $logMsg .= __METHOD__ . '::' . print_r($response->getBody(), true) . PHP_EOL;
            $this->_logger->debug($logMsg);
        }

        return json_decode($response->getBody(), true);
    }

}