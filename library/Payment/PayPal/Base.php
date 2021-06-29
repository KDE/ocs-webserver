<?php
/** @noinspection PhpUndefinedFieldInspection */

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

namespace Library\Payment\PayPal;

use Exception;
use Laminas\Config\Config;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Client;
use Laminas\Http\Client\Exception\RuntimeException;
use Laminas\Http\Exception\RuntimeException as HttpRuntimeException;
use Laminas\Log\Logger;

/**
 * Class Base
 *
 * @package Library\Payment\PayPal
 */
abstract class Base
{
    /** @var Config */
    protected $_config;
    /** @var Logger */
    protected $_logger;
    /** @var AdapterInterface $db */
    protected $db;
    /** @var UserData */
    private $_paymentUserData;

    function __construct(AdapterInterface $db, $config, $logger)
    {
        $GLOBALS['ocs_log']->info(__METHOD__ . ' - Init Class ');

        $this->db = $db;
        $this->_config = $config;
        $this->_logger = $logger;

        $this->_paymentUserData = new UserData();
    }

    /**
     * @param      $request
     * @param      $apiName
     * @param      $apiOperation
     * @param bool $withAuthHeader
     *
     * @return array
     * @throws Exception
     */
    protected function _makeRequest($request, $apiName, $apiOperation, $withAuthHeader = true)
    {
        $url = $this->_config->api->endpoint . '/' . $apiName . '/' . $apiOperation;
        $http = new Client($url);
        if (true === $withAuthHeader) {
            $http->setHeaders($this->_buildHeader($this->_config));
        }
        $http->setMethod('POST');
        $http->setParameterPost($request);
        // Increasing the HTTP timeout
        $http->setOptions(array('timeout' => 120));

        try {
            $response = $http->send();
        } catch (RuntimeException $e) {
            throw new Exception('Error while request PayPal website.', 0, $e);
        } catch (HttpRuntimeException $re) {
            throw new Exception('Error while request PayPal website.', 0, $re);
        }

        if (false === $response) {
            $this->_logger->err(__METHOD__ . "::Error while request PayPal Website.\n Server replay was: "
                                . $http->getLastRawResponse()->getStatusCode()
                                . ". " . $http->getLastRawResponse()->getContent()
                                . "\n"
            );
            $this->_logger->err(__METHOD__ . '::Last Request: ' . print_r($http->getLastRawRequest(), true));
        } else {
            $this->_logger->debug(__METHOD__ . '::Last Request: ' . print_r($http->getLastRawRequest(), true));
            $this->_logger->debug(__METHOD__ . '::Headers: ' . print_r($response->getHeaders(), true));
            $this->_logger->debug(__METHOD__ . '::Body: ' . print_r($response->getBody(), true) . "\n");
        }

        $resultArray = $this->_parseRawMessage($response->getBody());
        $this->_logger->debug(__METHOD__ . ' - resultArray' . print_r($resultArray, true) . PHP_EOL);

        return $resultArray;
    }

    /**
     * Build all HTTP headers required for the API call.
     *
     * @access    protected
     *
     * @param Config $config
     *
     * @return array $headers
     */
    protected function _buildHeader($config = null)
    {
        $header = array(
            'X-PAYPAL-SECURITY-USERID: ' . $config->security->userid,
            'X-PAYPAL-SECURITY-PASSWORD: ' . $config->security->password,
            'X-PAYPAL-SECURITY-SIGNATURE: ' . $config->security->signature,
            'X-PAYPAL-REQUEST-DATA-FORMAT: ' . $config->request->data->format,
            'X-PAYPAL-RESPONSE-DATA-FORMAT: ' . $config->response->data->format,
            'X-PAYPAL-APPLICATION-ID: ' . $config->application->id,
        );

        if (defined('APPLICATION_ENV') &&  APPLICATION_ENV == 'development') {
            array_push($header, 'X-PAYPAL-SANDBOX-EMAIL-ADDRESS: ' . $config->sandbox->email);
        }

        return $header;
    }

    /**
     * @param string $raw_post
     *
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