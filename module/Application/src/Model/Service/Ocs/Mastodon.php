<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Model\Service\Ocs;

use Exception;
use Laminas\Http\Client;
use Laminas\Json\Decoder;

class Mastodon
{
    protected $config;
    protected $messages;
    protected $httpClient;
    protected $isRateLimitError;
    protected $rateLimitWaitSeconds;

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->config = $GLOBALS['ocs_config']->settings->server->mastodon;
        $uri = $this->config->host;
        $this->httpClient = $this->getHttpClient($uri);
    }

    /**
     * @param $uri
     *
     * @return Client
     * @throws Exception
     */
    protected function getHttpClient($uri)
    {
        try {
            if (empty($uri)) {
                return new Client(
                    null, array(
                            'keepalive'       => true,
                            'strictredirects' => true,
                            'sslverifypeer'   => false,
                            'adapter'         => 'Laminas\Http\Client\Adapter\Curl',
                        )
                );
            }

            return new Client(
                $uri, array(
                        'keepalive'       => true,
                        'strictredirects' => true,
                        'sslverifypeer'   => false,
                        'adapter'         => 'Laminas\Http\Client\Adapter\Curl',
                    )
            );
        } catch (Exception $e) {
            throw new Exception('Can not create http client for uri: ' . $uri, 0, $e);
        }
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
        $method = 'GET';
        $uid = 'getTimelines';
        $timelines = null;
        $result = array();
        try {
            $timelines = $this->httpRequest($uri, $uid, $method);
            if (false === $timelines) {
                $this->messages[] = "Fail ";

                return false;
            }
            foreach ($timelines as &$m) {
                $result[] = (array)$m;
            }
        } catch (Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        return (array)$result;
    }

    /**
     * @param string     $uri
     * @param string     $uid
     * @param string     $method
     * @param array|null $post_param
     *
     * @param bool       $isAdmin
     *
     * @return bool|array
     * @throws Exception
     */
    protected function httpRequest($uri, $uid, $method = 'GET', $post_param = null, $isAdmin = false)
    {
        $this->isRateLimitError = false;
        $this->rateLimitWaitSeconds = 0;

        $this->httpClient->resetParameters();
        try {
            $this->httpClient->setUri($uri);
            $this->httpClient->setHeaders(array('User-Agent' => $this->config->user_agent));
            if ($isAdmin) {
                $this->httpClient->setHeaders(array('Authorization' => 'Bearer ' . $this->config->private_token));
            }
            $this->httpClient->setMethod($method);


            $this->httpClient->setHeaders(array('Content-Type' => 'application/json'));
            $this->httpClient->setHeaders(array('Accept' => 'application/json'));

        } catch (Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') httpClient error message: ' . $e->getMessage();

            return false;
        }
        if (isset($post_param)) {
            $this->httpClient->setParameterPost($post_param);
        }

        try {
            $response = $this->httpClient->send();

        } catch (Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') httpClient error message: ' . $e->getMessage();

            return false;
        }
        if ($response->getStatusCode() < 200 or $response->getStatusCode() >= 500) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS Forum server send message: ' . $response->getBody();

            return false;
        }

        $body = $response->getBody();
        /*
        $content_encoding = $response->getHeaders()->get('Content-encoding');
        $transfer_encoding = $response->getHeaders()->get('Transfer-encoding');
        if ($transfer_encoding == 'chunked') {
            $body = \Laminas\Http\Response::decodeChunkedBody($body);
        }
        if ($content_encoding == 'gzip') {
            $body = Zend_Http_Response::decodeGzip($body);
        }
        */
        if (substr($body, 0, strlen('<html>')) === '<html>') {
            $this->messages[] = $body;

            return false;
        }
        try {
            $body = Decoder::decode($body);
        } catch (Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') Zend_Json::decode error message: ' . $e->getMessage();

            return false;
        }

        if (empty($body)) {
            return array('message' => 'empty body received');
        }

        if (array_key_exists("error_type", $body) or array_key_exists("errors", $body)) {
            $this->messages[] = "id: {$uid} ($uri) - " . $response->getBody();

            if (isset($body['error_type']) and ($body['error_type'] == "rate_limit")) {

                $this->isRateLimitError = true;
                $this->rateLimitWaitSeconds = $body['extras']['wait_seconds'];

                throw new Exception($body['errors'][0]);
            }

            return false;
        }

        if (array_key_exists('success', $body) and $body['success'] == false) {
            $this->messages[] = "id: {$uid} ($uri) - " . $body['message'];

            return false;
        }

        return $body;
    }

    public function getUserByUsername($username)
    {
        $uri = $this->config->host . "/api/v1/admin/accounts?username=" . $username;
        $method = 'GET';
        $uid = 'getUserByUsername';
        $user = null;

        try {
            $user = $this->httpRequest($uri, $uid, $method, null, true);

            if (false === $user) {
                $this->messages[] = "Fail ";

                return false;
            }
        } catch (Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        return $user;
    }

    public function getUserStatuses($id)
    {
        $uri = $this->config->host . "/api/v1/accounts/" . $id . "/statuses";
        $method = 'GET';
        $uid = 'getUserAccount';
        $user = null;

        try {
            $user = $this->httpRequest($uri, $uid, $method);

            if (false === $user) {
                $this->messages[] = "Fail ";

                return false;
            }
        } catch (Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        return $user;
    }

}