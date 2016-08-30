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

abstract class Local_Payment_PayPal_Base
{
    /** @var \Zend_Config */
    protected $_config;
    /** @var \Zend_Log */
    protected $_logger;

    /**
     * @param array|Zend_config $config
     * @param Zend_Log_Writer_Abstract $logger
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

        $this->_paymentUserData = new Local_Payment_PayPal_UserData();
    }

    /**
     * @param array $request
     * @param string $apiName
     * @param string $apiOperation
     * @param bool $withAuthHeader
     * @throws Local_Payment_Exception
     * @return array
     */
    protected function _makeRequest($request, $apiName, $apiOperation, $withAuthHeader = true)
    {
        $url = $this->_config->api->endpoint . '/' . $apiName . '/' . $apiOperation;
        $http = new Zend_Http_Client($url);
        if (true === $withAuthHeader) {
            $http->setHeaders($this->_buildHeader($this->_config));
        }
        $http->setMethod(Zend_Http_Client::POST);
        $http->setParameterPost($request);

        try {
            $response = $http->request();
        } catch (Zend_Http_Client_Exception $e) {
            throw new Local_Payment_Exception('Error while request PayPal website.', 0, $e);
        }

        if (false === $response) {
            $this->_logger->err(__METHOD__ . " - Error while request PayPal Website.\n Server replay was: " . $http->getLastResponse()->getStatus() . PHP_EOL . $http->getLastResponse()->getMessage() . PHP_EOL);
            $this->_logger->err(__METHOD__ . ' - Last Request: ' . print_r($http->getLastRequest(), true));
            $this->_logger->err(__METHOD__ . ' - Headers: ' . print_r($response->getHeaders(), true));
            $this->_logger->err(__METHOD__ . ' - Body: ' . print_r($response->getBody(), true) . PHP_EOL);
        } else {
            $this->_logger->debug(__METHOD__ . ' - Last Request: ' . print_r($http->getLastRequest(), true));
            $this->_logger->debug(__METHOD__ . ' - Headers: ' . print_r($response->getHeaders(), true));
            $this->_logger->debug(__METHOD__ . ' - Body: ' . print_r($response->getBody(), true) . PHP_EOL);
        }

        $resultArray = $this->_parseRawMessage($response->getBody());
        $this->_logger->debug(__METHOD__ . ' - resultArray' . print_r($resultArray, true) . PHP_EOL);

        return $resultArray;
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
            'X-PAYPAL-SECURITY-USERID: ' . $config->security->userid,
            'X-PAYPAL-SECURITY-PASSWORD: ' . $config->security->password,
            'X-PAYPAL-SECURITY-SIGNATURE: ' . $config->security->signature,
            'X-PAYPAL-REQUEST-DATA-FORMAT: ' . $config->request->data->format,
            'X-PAYPAL-RESPONSE-DATA-FORMAT: ' . $config->response->data->format,
            'X-PAYPAL-APPLICATION-ID: ' . $config->application->id
        );

        if (APPLICATION_ENV == 'development') {
            array_push($header, 'X-PAYPAL-SANDBOX-EMAIL-ADDRESS: ' . $config->sandbox->email);
        }

        return $header;
    }

    /**
     * @param string $raw_post
     * @return array
     */
    protected function _parseRawMessage($raw_post)
    {
        //log_message('error', "testing");
        if (empty($raw_post)) {
            return array();
        } # else:
        $parsedPost = array();
        $pairs = explode('&', $raw_post);
        foreach ($pairs as $pair) {
            list($key, $value) = explode('=', $pair, 2);
            $key = urldecode($key);
            $value = urldecode($value);
            # This is look for a key as simple as 'return_url' or as complex as 'somekey[x].property'
//            preg_match('/(\w+)(?:\[(\d+)\])?(?:\.(\w+))?/', $key, $key_parts);
            preg_match('/(\w+)(?:(?:\[|\()(\d+)(?:\]|\)))?(?:\.(\w+))?/', $key, $key_parts);
            switch (count($key_parts)) {
                case 4:
                    # Original key format: somekey[x].property
                    # Converting to $post[somekey][x][property]
                    if (false === isset($parsedPost[$key_parts[1]])) {
                        if (empty($key_parts[2]) && '0' != $key_parts[2]) {
                            $parsedPost[$key_parts[1]] = array($key_parts[3] => $value);
                        } else {
                            $parsedPost[$key_parts[1]] = array($key_parts[2] => array($key_parts[3] => $value));
                        }
                    } else {
                        if (false === isset($parsedPost[$key_parts[1]][$key_parts[2]])) {
                            if (empty($key_parts[2]) && '0' != $key_parts[2]) {
                                $parsedPost[$key_parts[1]][$key_parts[3]] = $value;
                            } else {
                                $parsedPost[$key_parts[1]][$key_parts[2]] = array($key_parts[3] => $value);
                            }
                        } else {
                            $parsedPost[$key_parts[1]][$key_parts[2]][$key_parts[3]] = $value;
                        }
                    }
                    break;
                case 3:
                    # Original key format: somekey[x]
                    # Converting to $post[somekey][x]
                    if (!isset($parsedPost[$key_parts[1]])) {
                        $parsedPost[$key_parts[1]] = array();
                    }
                    $parsedPost[$key_parts[1]][$key_parts[2]] = $value;
                    break;
                default:
                    # No special format
                    $parsedPost[$key] = $value;
                    break;
            }
            #switch
        }
        #foreach

        return $parsedPost;
    }

} 