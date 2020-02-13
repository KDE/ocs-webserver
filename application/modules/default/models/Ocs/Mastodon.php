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
 *
 * Created: 10.09.2018
 */
class Default_Model_Ocs_Mastodon
{
    protected $config;
    protected $messages;
    protected $httpClient;
    protected $isRateLimitError;
    protected $rateLimitWaitSeconds;

    /**
     * @inheritDoc
     * @param array|null $config
     * @throws Default_Model_Ocs_Gitlab_Exception
     * @throws Zend_Exception
     */
    public function __construct($config = null)
    {
        if (isset($config)) {
            $this->config = $config;
        } else {
            $this->config = Zend_Registry::get('config')->settings->server->mastodon;
        }
        $uri = $this->config->host;
        $this->httpClient = $this->getHttpClient($uri);
    }

    /**
     * @param $uri
     * @return Zend_Http_Client
     * @throws Default_Model_Ocs_Gitlab_Exception
     */
    protected function getHttpClient($uri)
    {
        try {
            if (empty($uri)) {
                return new Zend_Http_Client(null, array('keepalive' => true, 'strictredirects' => true));
            }

            return new Zend_Http_Client($uri, array('keepalive' => true, 'strictredirects' => true));
        } catch (Zend_Exception $e) {
            throw new Default_Model_Ocs_Gitlab_Exception('Can not create http client for uri: ' . $uri, 0, $e);
        }
    }

  
    /**
     * @param string     $uri
     * @param string     $uid
     * @param string     $method
     * @param array|null $post_param
     *
     * @return bool|array
     * @throws Zend_Exception
     */
    protected function httpRequest($uri, $uid, $method = Zend_Http_Client::GET, $post_param = null)
    {
        $this->isRateLimitError = false;
        $this->rateLimitWaitSeconds = 0;

        $this->httpClient->resetParameters();
        try {
            $this->httpClient->setUri($uri);
            $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
            $this->httpClient->setMethod($method);
        } catch (Zend_Http_Client_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') httpClient error message: ' . $e->getMessage();

            return false;
        } catch (Zend_Uri_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') httpClient error message: ' . $e->getMessage();

            return false;
        }
        $this->httpClient->setParameterGet('api_key', $this->config->private_token);
        $this->httpClient->setParameterGet('api_username', $this->config->user_sudo);
        if (isset($post_param)) {
            $this->httpClient->setParameterPost($post_param);
        }

        try {
            $response = $this->httpClient->request();
        } catch (Zend_Http_Client_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') httpClient error message: ' . $e->getMessage();

            return false;
        }
        if ($response->getStatus() < 200 OR $response->getStatus() >= 500) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS Forum server send message: ' . $response->getBody();

            return false;
        }

        $body = $response->getRawBody();
        $content_encoding = $response->getHeader('Content-encoding');
        $transfer_encoding = $response->getHeader('Transfer-encoding');
        if ($transfer_encoding == 'chunked') {
            $body = Zend_Http_Response::decodeChunkedBody($body);
        }
        if ($content_encoding == 'gzip') {
            $body = Zend_Http_Response::decodeGzip($body);
        }
        if (substr($body, 0, strlen('<html>')) === '<html>') {
            $this->messages[] = $body;

            return false;
        }
        try {
            $body = Zend_Json::decode($body);
        } catch (Zend_Json_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') Zend_Json::decode error message: ' . $e->getMessage();

            return false;
        }

        if (empty($body)) {
            return array('message' => 'empty body received');
        }

        if (array_key_exists("error_type", $body) OR array_key_exists("errors", $body)) {
            $this->messages[] = "id: {$uid} ($uri) - " . $response->getBody();

            if (isset($body['error_type']) AND ($body['error_type'] == "rate_limit")) {

                $this->isRateLimitError = true;
                $this->rateLimitWaitSeconds = $body['extras']['wait_seconds'];

                throw new Zend_Exception($body['errors'][0]);
            }

            return false;
        }

        if (array_key_exists('success', $body) AND $body['success'] == false) {
            $this->messages[] = "id: {$uid} ($uri) - " . $body['message'];

            return false;
        }

        return $body;
    }

   

    /**
     * @return mixed
     */
    public function hasRateLimitError()
    {
        return $this->isRateLimitError;
    }

    /**
     * @return mixed
     */
    public function getRateLimitWaitSeconds()
    {
        return $this->rateLimitWaitSeconds;
    }

    public function getTimelines()
    {
        $uri = $this->config->host . "/api/v1/timelines/public?limit=5";
        $method = Zend_Http_Client::GET;
        $uid = 'getTimelines';
        $timelines = null;
        try {
            $timelines = $this->httpRequest($uri, $uid, $method);
            if (false === $timelines) {
                $this->messages[] = "Fail ";

                return false;
            }
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }
        return $timelines;
    }
   

}