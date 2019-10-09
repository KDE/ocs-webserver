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
class Default_Model_Ocs_HttpTransport_OAuthServer
{

    protected $messages;
    protected $httpClient;
    private $_config;
    private $_cache;

    /**
     *
     * @param Zend_Config $config
     *
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Uri_Exception
     */
    public function __construct(Zend_Config $config)
    {
        $this->_config = $config;
        if (empty($this->_config)) {
            throw new Zend_Exception('No config present');
        }

        $this->_cache = $this->initCache();
        $uri = $this->_config->host;
        $this->httpClient = new Zend_Http_Client($uri,array('keepalive' => true, 'strictredirects' => true, 'timeout' => 120));
        $this->httpClient->setCookieJar();
    }

    /**
     * @return Zend_Cache_Core
     * @throws Zend_Cache_Exception
     */
    protected function initCache()
    {
        $frontendOptions = array(
            'lifetime'                => null,
            'automatic_serialization' => true
        );

        $backendOptions = array(
            'cache_dir'        => APPLICATION_CACHE,
            'file_name_prefix' => 'ocs_id'
        );

        return Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }

    /**
     * @param array $userdata
     *
     * @return bool
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
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
            )
        );
        if ((false == $userdata['is_active']) OR (true == $userdata['is_deleted'])) {
            $map_user_data['user']['disabledReason'] = 'user account disabled';
        }

        if (isset($options) AND is_array($options)) {
            $map_user_data['options'] = $options;
        }

        $jsonUserData = Zend_Json::encode($map_user_data);
        $httpClient = new Zend_Http_Client($this->_config->create_user_url);
        $httpClient->setMethod(Zend_Http_Client::POST);
        $httpClient->setHeaders('Authorization', 'Bearer ' . $access_token);
        $httpClient->setHeaders('Content-Type', 'application/json');
        $httpClient->setHeaders('Accept', 'application/json');
        $httpClient->setRawData($jsonUserData, 'application/json');

        $response = $httpClient->request();

        //Zend_Registry::get('logger')->debug("----------\n".__METHOD__ . " - request:\n" . $httpClient->getLastRequest());
        //Zend_Registry::get('logger')->debug("----------\n".__METHOD__ . " - response:\n" . $response->asString());
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - request: ' . $jsonUserData);
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - response: ' . $response->getBody());

        if ($response->getStatus() != 200) {
            throw new Zend_Exception('push user data failed. OCS ID server send message: ' . $response->getBody() . PHP_EOL
                                     . $jsonUserData . PHP_EOL);
        }

        $this->messages[] = $response->getBody();

        return true;
    }

    /**
     * @return mixed
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    protected function getAccessToken()
    {
        $cache_name = 'id_server_token' . $this->_config->client_id;
        $token = $this->_cache->load($cache_name);
        if (false === $token) {
            $token = $this->requestHttpAccessToken();
            $token->expire_at = microtime(true) + $token->expires_in;
            $this->_cache->save($token, $cache_name, array(), false);
        }
        Zend_Registry::get('logger')->debug(__METHOD__ . " - microtime:" . microtime(true) . " expire_at: "
                                            . print_r($token->expire_at, true));
        if (isset($token) AND (microtime(true) > $token->expire_at)) {
            $token = $this->requestHttRefreshToken($token->refresh_token);
            $token->expire_at = microtime(true) + $token->expires_in;
            $this->_cache->save($token, $cache_name, array(), false);
        }

        return $token->access_token;
    }

    /**
     * @return mixed
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    protected function requestHttpAccessToken()
    {
        $httpClient = new Zend_Http_Client($this->_config->token_url);
        //$adapter = new Zend_Http_Client_Adapter_Socket();
        //$adapter->setStreamContext(array('ssl' => array('verify_peer' => false,'allow_self_signed' => true,'capture_peer_cert' => false)));
        //$httpClient->setAdapter($adapter);
        $httpClient->setMethod(Zend_Http_Client::POST);
        $httpClient->setHeaders('Content-Type', 'application/x-www-form-urlencoded');
        $httpClient->setHeaders('Accept', 'application/json');
        $httpClient->setParameterPost(array(
            //'username'      => $this->_config->username,
            //'password'      => $this->_config->userpass,
            'client_id'     => $this->_config->client_id,
            'client_secret' => $this->_config->client_secret,
            'grant_type'    => 'client_credentials'
            //'grant_type'    => 'password',
            //'scope'         => 'profile openid user:create user:delete'
        ));

        $response = $httpClient->request();

        Zend_Registry::get('logger')->debug(__METHOD__ . " - request: " . $httpClient->getUri(true));
        Zend_Registry::get('logger')->debug(__METHOD__ . " - response: " . $response->getRawBody());

        if ($response->getStatus() != 200) {
            throw new Zend_Exception('request access token failed. OCS ID server send message: ' . $response->getRawBody());
        }

        $data = $this->parseResponse($response);

        return $data;
    }

    /**
     * @param Zend_Http_Response $response
     *
     * @return mixed
     * @throws Zend_Json_Exception
     */
    protected function parseResponse(Zend_Http_Response $response)
    {
        $data = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

        return $data;
    }

    /**
     * @param $refresh_token
     *
     * @return mixed
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    protected function requestHttRefreshToken($refresh_token)
    {
        $httpClient = new Zend_Http_Client($this->_config->token_url);
        $httpClient->setMethod(Zend_Http_Client::POST);
        $httpClient->setHeaders('Content-Type', 'application/x-www-form-urlencoded');
        $httpClient->setHeaders('Accept', 'application/json');
        $httpClient->setParameterPost(array(
            'client_id'     => $this->_config->client_id,
            'client_secret' => $this->_config->client_secret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token
        ));

        $response = $httpClient->request();

        Zend_Registry::get('logger')->debug(__METHOD__ . " - request: " . $httpClient->getUri(true));
        Zend_Registry::get('logger')->debug(__METHOD__ . " - response: " . $response->getRawBody());

        if ($response->getStatus() != 200) {
            throw new Zend_Exception('request refresh token failed. OCS ID server send message: ' . $response->getBody());
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
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function httpRequest($uri, $uid, $method = Zend_Http_Client::GET, $post_param = null)
    {
        $access_token = $this->getAccessToken();
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Authorization', 'Bearer ' . $access_token);
        $this->httpClient->setHeaders('Content-Type', 'application/json');
        $this->httpClient->setHeaders('Accept', 'application/json');
        $this->httpClient->setHeaders('User-Agent', $this->_config->user_agent);
        $this->httpClient->setMethod($method);
        if (isset($post_param)) {
            $jsonUserData = Zend_Json::encode($post_param);
            $this->httpClient->setRawData($jsonUserData, 'application/json');
        }

        $response = $this->httpClient->request();
        if ($response->getStatus() < 200 OR $response->getStatus() >= 500) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS OAuth server send message: ' . $response->getBody();

            return false;
        }

        $body = Zend_Json::decode($response->getBody());

        if (array_key_exists("message", $body) OR array_key_exists("error", $body)) {
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