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

namespace Application\Model\Service\Ocs\HttpTransport;


use Exception;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Config\Config;
use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use Laminas\Json\Json;
use Laminas\Log\Logger;

class OAuthServer
{
    protected $messages;
    protected $httpClient;
    /** @var Config */
    private $_config;
    /** @var StorageInterface */
    private $_cache;
    /**
     * @var Logger
     */
    private $log;
    /**
     * @var mixed|null
     */
    private $user_agent;

    /**
     *
     * @param Config           $config
     * @param StorageInterface $cache
     * @param Logger           $logger
     *
     * @throws Exception
     */
    public function __construct(Config $config, StorageInterface $cache, Logger $logger)
    {
        $this->_config = $config;
        if (empty($this->_config)) {
            throw new Exception('No config present');
        }

        $this->_cache = $cache;
        $uri = $this->_config->host;
        $this->user_agent = $this->_config->user_agent;
        $this->httpClient = new Client(
            $uri, array(
                    'adapter'         => 'Laminas\Http\Client\Adapter\Curl',
                    'useragent'       => $this->user_agent,
                    'keepalive'       => true,
                    'strictredirects' => true,
                    'timeout'         => 120,
                )
        );
        $this->log = $logger;
    }

    /**
     * @param array      $userdata
     * @param array|null $options
     *
     * @return bool
     * @throws Exception
     */
    public function pushHttpUserData($userdata, $options = null)
    {
        $this->messages = array();
        $access_token = $this->getAccessToken();
        $map_user_data = array(
            'user' => array(
                'id'             => $userdata['external_id'],
                'ocs_user_id'    => $userdata['ocs_user_id'],
                'username'       => $userdata['username'],
                'password'       => $userdata['password'],
                'email'          => $userdata['email'],
                'emailVerified'  => $userdata['emailVerified'],
                'is_hive'        => $userdata['is_hive'],
                'creationTime'   => $userdata['creationTime'],
                'lastUpdateTime' => $userdata['lastUpdateTime'],
                'avatarUrl'      => $userdata['avatarUrl'],
                'biography'      => $userdata['biography'],
                'admin'          => $userdata['admin'],
            ),
        );
        if ((false == $userdata['is_active']) or (true == $userdata['is_deleted'])) {
            $map_user_data['user']['disabledReason'] = 'user account disabled';
        }

        if (isset($options) and is_array($options)) {
            $map_user_data['options'] = $options;
        }

        $jsonUserData = Encoder::encode($map_user_data);
        $httpClient = new Client($this->_config->create_user_url);
        $request = new Request();
        $request->setUri($this->_config->create_user_url);
        $request->setMethod(Request::METHOD_POST);
        $request->getHeaders()->addHeaders(
            [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ]
        );
        $request->setContent($jsonUserData);

        $response = $httpClient->send($request);

        $this->log->debug(__METHOD__ . ' - request: ' . $jsonUserData);
        $this->log->debug(__METHOD__ . ' - response: ' . $response->getBody());

        if ($response->getStatusCode() != 200) {
            throw new Exception('push user data failed. OCS ID server send message: ' . $response->getBody() . PHP_EOL . $jsonUserData . PHP_EOL);
        }

        $this->messages[] = $response->getBody();

        return true;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    protected function getAccessToken()
    {
        $cache_name = 'id_server_token' . $this->_config->client_id;
        $token = $this->_cache->getItem($cache_name);
        if (empty($token)) {
            $token = $this->requestHttpAccessToken();
            $token->expire_at = microtime(true) + $token->expires_in;
            $this->_cache->setItem($cache_name, $token);
        }
        $this->log->debug(__METHOD__ . " - microtime:" . microtime(true) . " expire_at: " . print_r($token->expire_at, true));
        if (isset($token) and (microtime(true) > $token->expire_at)) {
            $token = $this->requestHttpRefreshToken($token->refresh_token);
            $token->expire_at = microtime(true) + $token->expires_in;
            $this->_cache->setItem($cache_name, $token);
        }

        return $token->access_token;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    protected function requestHttpAccessToken()
    {
        $httpClient = new Client($this->_config->token_url);
        $httpClient->setOptions(
            array(
                'adapter'         => 'Laminas\Http\Client\Adapter\Curl',
                'useragent'       => $this->user_agent,
                'keepalive'       => true,
                'strictredirects' => true,
                'timeout'         => 120,
            )
        );
        $request = new Request();
        $request->setUri($this->_config->token_url);
        $request->setMethod(Request::METHOD_POST);
        $request->getHeaders()->addHeaders(
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept'       => 'application/json',
            ]
        );
        $request->getPost()->fromArray(
            array(
                'client_id'     => $this->_config->client_id,
                'client_secret' => $this->_config->client_secret,
                'grant_type'    => 'client_credentials',
            )
        );

        $response = $httpClient->send($request);

        $this->log->debug(__METHOD__ . " - request: " . $httpClient->getUri());
        $this->log->debug(__METHOD__ . " - response: " . $response->getBody());

        if ($response->getStatusCode() != 200) {
            throw new Exception('request access token failed. OCS ID server send message: ' . $response->getBody());
        }

        $data = $this->parseResponse($response);

        return $data;
    }

    /**
     * @param Response $response
     *
     * @return mixed
     */
    protected function parseResponse(Response $response)
    {
        $data = Decoder::decode($response->getBody(), Json::TYPE_OBJECT);

        return $data;
    }

    /**
     * @param $refresh_token
     *
     * @return mixed
     * @throws Exception
     */
    protected function requestHttpRefreshToken($refresh_token)
    {
        $httpClient = new Client($this->_config->token_url);
        $httpClient->setOptions(
            array(
                'adapter'         => 'Laminas\Http\Client\Adapter\Curl',
                'useragent'       => $this->user_agent,
                'keepalive'       => true,
                'strictredirects' => true,
                'timeout'         => 120,
            )
        );
        $request = new Request();
        $request->setUri($this->_config->token_url);
        $request->setMethod(Request::METHOD_POST);
        $request->getHeaders()->addHeaders(
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept'       => 'application/json',
            ]
        );
        $request->getPost()->fromArray(
            array(
                'client_id'     => $this->_config->client_id,
                'client_secret' => $this->_config->client_secret,
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refresh_token,
            )
        );

        $response = $httpClient->send($request);

        $this->log->debug(__METHOD__ . " - request: " . $httpClient->getUri());
        $this->log->debug(__METHOD__ . " - response: " . $response->getBody());

        if ($response->getStatusCode() != 200) {
            throw new Exception('request refresh token failed. OCS ID server send message: ' . $response->getBody());
        }

        $data = $this->parseResponse($response);

        return $data;
    }

    /**
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string     $uri
     * @param string     $uid
     * @param string     $method
     * @param array|null $post_param
     *
     * @return bool|array
     * @throws Exception
     */
    public function httpRequest($uri, $uid, $method = Request::METHOD_GET, $post_param = null)
    {
        $access_token = $this->getAccessToken();
        $this->httpClient->resetParameters();
        $request = new Request();
        $request->setUri($uri);
        $request->setMethod($method);
        $request->getHeaders()->addHeaders(
            [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'User-Agent'    => $this->_config->user_agent,
            ]
        );
        if (isset($post_param)) {
            $jsonUserData = Encoder::encode($post_param);
            $request->setContent($jsonUserData);
        }

        $response = $this->httpClient->send($request);
        if ($response->getStatusCode() < 200 or $response->getStatusCode() >= 500) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS OAuth server send message: ' . $response->getBody();

            return false;
        }

        $body = Decoder::decode($response->getBody());

        if (array_key_exists("message", $body) or array_key_exists("error", $body)) {
            $this->messages[] = "id: {$uid} ($uri) - " . $response->getBody();

            return false;
        }

        return $body;
    }

    public function resetMessages()
    {
        $this->messages = array();
    }
}