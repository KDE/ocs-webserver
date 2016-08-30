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

abstract class Local_Payment_Amazon_FlexiblePayment_Gateway implements Local_Payment_GatewayInterface
{
    /** @var \Zend_Config */
    protected $_config;
    /** @var \Zend_Log */
    protected $_logger;

    /** @var  \Local_Payment_Amazon_UserData */
    protected $_paymentUserData;
    protected $_dataIpn;
    protected $_returnUrl;
    protected $_ipnNotificationUrl;
    protected $_cancelUrl;

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

        $this->_paymentUserData = new Local_Payment_Amazon_UserData();
    }

    /**
     * @param float $amount
     * @param string $requestMsg
     * @return Local_Payment_ResponseInterface
     */
    public function requestPayment($amount, $requestMsg = null)
    {
        $log = $this->_logger;

        $log->info('********** Start Amazon Payment **********');
        $log->info(__FUNCTION__);
        $log->debug(APPLICATION_ENV);

        $response = new Local_Payment_Amazon_FlexiblePayment_Response(null);

        $log->info('********** Finished Amazon Payment **********');

        return $response;

    }

    /**
     * @param Local_Payment_Amazon_UserData $userData
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
     * @return Local_Payment_Amazon_UserData
     */
    public function getUserDataStore()
    {
        return $this->_paymentUserData;
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->_cancelUrl;
    }

    /**
     * @param string $cancelUrl
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->_cancelUrl = $cancelUrl;
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