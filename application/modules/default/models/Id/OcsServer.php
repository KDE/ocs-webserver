<?php

class Default_Model_Id_OcsServer
{

    private $_config;
    private $_cache;

    /**
     *
     * @param Zend_Db_Adapter_Abstract|null $dbAdapter
     * @param null                          $tableName
     * @param Zend_Config                   $config
     * @throws Zend_Exception
     */
    public function __construct(Zend_Config $config)
    {
        $this->_config = $config;
        if (empty($this->_config)) {
            throw new Zend_Exception('No config present');
        }

        $this->_cache = $this->initCache();
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
     * @return bool
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    public function pushHttpUserData($userdata, $options = null)
    {
        $access_token = $this->getAccessToken();
        $map_user_data = array(
            'user' => array(
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
        if (false == $userdata['is_active'] AND true == $userdata['is_deleted']) {
            $map_user_data['user']['disabledReason'] = 'user account disabled';
        }

        if (isset($options) AND is_array($options)) {
            $map_user_data['options'] = $options;
        }

        $httpClient = new Zend_Http_Client($this->_config->create_user_url);
        $httpClient->setMethod(Zend_Http_Client::POST);
        $httpClient->setHeaders('Authorization', 'Bearer ' . $access_token);
        $httpClient->setHeaders('Content-Type', 'application/json');
        $httpClient->setHeaders('Accept', 'application/json');
        $httpClient->setRawData(Zend_Json::encode($map_user_data), 'application/json');

        $response = $httpClient->request();

        Zend_Registry::get('logger')->debug("----------\n".__METHOD__ . " - request:\n" . $httpClient->getLastRequest());
        Zend_Registry::get('logger')->debug("----------\n".__METHOD__ . " - response:\n" . $response->asString());

        if ($response->getStatus() != 200) {
            throw new Zend_Exception('push user data failed. OCS ID server send message: ' . $response->getBody());
        }

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
        $cache_name = 'id_server_token';
        $token = $this->_cache->load($cache_name);
        if (false === $token) {
            $token = $this->requestHttpAccessToken();
            $token->expire_at = microtime(true) + $token->expires_in;
            $this->_cache->save($token, $cache_name, array(), false);
        }
        Zend_Registry::get('logger')->debug("----------\n".__METHOD__ . " - microtime:" . microtime(true)."\n expire_at: " . print_r($token->expire_at, true));
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
        $httpClient->setMethod(Zend_Http_Client::POST);
        $httpClient->setHeaders('Content-Type', 'application/x-www-form-urlencoded');
        $httpClient->setHeaders('Accept', 'application/json');
        $httpClient->setParameterPost(array(
            'client_id'     => $this->_config->client_id,
            'client_secret' => $this->_config->client_secret,
            'grant_type'    => 'client_credentials'
        ));

        $response = $httpClient->request();

        Zend_Registry::get('logger')->debug("----------\n".__METHOD__ . " - request:\n" . $httpClient->getLastRequest());
        Zend_Registry::get('logger')->debug("----------\n".__METHOD__ . " - response:\n" . $response->asString());

        if ($response->getStatus() != 200) {
            throw new Zend_Exception('request access token failed. OCS ID server send message: ' . $response->getBody());
        }

        $data = $this->parseResponse($response);

        Zend_Registry::get('logger')->debug("----------\n".__METHOD__ . " - parsed response:\n" . print_r($data, true));

        return $data;
    }

    /**
     * @param Zend_Http_Response $response
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

        Zend_Registry::get('logger')->debug("----------\n".__METHOD__ . " - request:\n" . $httpClient->getLastRequest());
        Zend_Registry::get('logger')->debug("----------\n".__METHOD__ . " - response:\n" . $response->asString());

        if ($response->getStatus() != 200) {
            throw new Zend_Exception('request access token failed. OCS ID server send message: ' . $response->getBody());
        }

        $data = $this->parseResponse($response);

        Zend_Registry::get('logger')->debug("----------\n".__METHOD__ . " - parsed response:\n" . print_r($data, true));

        return $data;
    }

}