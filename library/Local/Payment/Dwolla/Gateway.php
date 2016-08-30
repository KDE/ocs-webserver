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

class Local_Payment_Dwolla_Gateway implements Local_Payment_GatewayInterface
{
    /** @var  Local_Payment_Dwolla_UserData */
    protected $_paymentUserData;
    /** @var  string */
    protected $_checkoutEndpoint;
    /** @var string Transaction mode. Can be 'live' or 'test' */
    protected $_mode;
    /** @var  boolean */
    protected $_allowGuestCheckout;
    /** @var  boolean */
    protected $_allowFundingSources;
    /** @var  string comma delimited value possible values: 'credit', 'banks', 'fisync', 'realtime', 'true', 'false' */
    protected $_additionalFundingSources;

    /** @var  string callback url for transaction response */
    protected $_ipnNotificationUrl;
    /** @var  string redirect URL after thes authorize or cancel the purchase */
    protected $_returnUrl;
    /** @var  Local_Payment_ResponseInterface */
    protected $_lastResponse;
    /** @var  string */
    protected $_message;
    /** @var null|Zend_Log|Zend_Log_Writer_Abstract */
    protected $_logger;
    /** @var array|Zend_Config */
    protected $_config;


    /**
     * @param array|Zend_config $config
     * @param Zend_Log_Writer_Abstract $logger
     * @throws Local_Payment_Exception
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
                throw new Local_Payment_Exception('Logger must be an instance of Zend_Log');
            }
        }

        $this->_paymentUserData = new Local_Payment_Dwolla_UserData();
        $this->_allowFundingSources = true;
        $this->_allowGuestCheckout = true;
        $this->_additionalFundingSources = 'true';
    }

    /**
     * @return string
     */
    public function getCheckoutEndpoint()
    {
        return ($this->_config->api->endpoint . '/payment/checkout/' . $this->_lastResponse->getPaymentId());
    }

    /**
     * @param Local_Payment_UserDataInterface $userData
     * @throws Exception
     */
    public function setUserDataStore($userData)
    {
        if (false === ($userData instanceof Local_Payment_UserDataInterface)) {
            throw new Exception('Wrong data type for user data');
        }
        $this->_paymentUserData = $userData;
    }

    /**
     * @return Local_Payment_PayPal_UserData
     */
    public function getUserDataStore()
    {
        return $this->_paymentUserData;
    }

    /**
     * @param float $amount
     * @param string $requestMsg
     * @throws Local_Payment_Exception
     * @return Local_Payment_ResponseInterface
     */
    public function requestPayment($amount, $requestMsg = null)
    {
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));

        if (empty($this->_returnUrl)) {
            throw new Local_Payment_Exception('return url was not set.');
        }

        if (empty($this->_ipnNotificationUrl)) {
            throw new Local_Payment_Exception('ipn notification url was not set.');
        }

        // Create request body
        $requestBody = array(
            'Key' => $this->_config->consumer->access_key,
            'Secret' => $this->_config->consumer->access_secret,
            'Test' => ($this->_mode == 'test') ? 'true' : 'false',
            'AdditionalFundingSources' => $this->_additionalFundingSources,
            'AllowGuestCheckout' => $this->_allowGuestCheckout ? 'true' : 'false',
            'AllowFundingSources' => $this->_allowFundingSources ? 'true' : 'false',
            'PurchaseOrder' => array(
                'DestinationId' => $this->_paymentUserData->getPaymentUserId(),
                'Total' => $amount,
                'FacilitatorAmount' => 0,
                'Notes' => $requestMsg
            ),
            'Redirect' => $this->_returnUrl,
            'Callback' => $this->_ipnNotificationUrl
        );

        $response = $this->_makeRequest($requestBody, 'payment/request', false);
        if (APPLICATION_ENV != 'production') {
            $this->_logger->debug(__METHOD__ . '- response - ' . print_r($response, true) . PHP_EOL);
        }

        $this->_lastResponse = new Local_Payment_Dwolla_ResponsePayRequest($response);
        if (APPLICATION_ENV != 'production') {
            $this->_logger->debug(__METHOD__ . ' - lastResponse - ' . print_r($this->_lastResponse, true) . PHP_EOL);
        }

        if (false === $this->_lastResponse->isSuccessful()) {
            throw new Local_Payment_Exception('Dwolla payment request failed. Request response:' . print_r($this->_lastResponse->getRawMessage(),
                    true));
        }

        return $this->_lastResponse;

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

    /**
     * Build all HTTP headers required for the API call.
     *
     * @access    protected
     * @param array|Zend_Config $config
     * @return    array $headers
     */
    protected function _buildHeader($config = null)
    {
        if (is_array($config)) {
            $config = new Zend_Config($config);
        }
        $header = array(
            'Content-Type: application/json'
        );

        return $header;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * @param mixed $mode
     */
    public function setMode($mode = 'live')
    {
        $this->_mode = $mode;
    }

    /**
     * @return string
     */
    public function getIpnNotificationUrl()
    {
        return $this->_ipnNotificationUrl;
    }

    /**
     * @param string $ipnNotificationUrl
     */
    public function setIpnNotificationUrl($ipnNotificationUrl)
    {
        $this->_ipnNotificationUrl = $ipnNotificationUrl;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->_returnUrl;
    }

    /**
     * @param string $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->_returnUrl = $returnUrl;
    }

}