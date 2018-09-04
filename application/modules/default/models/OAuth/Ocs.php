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
 *    Created: 16.12.2016
 **/
class Default_Model_OAuth_Ocs implements Default_Model_OAuth_Interface
{
    const PREFIX_SEPARATOR = '_';

    /** @var Zend_Db_Adapter_Abstract $_db */
    protected $_db;
    /** @var null|string $_tableName */
    protected $_tableName;
    /** @var Zend_Config $config */
    protected $config;
    /** @var Zend_Session_Namespace $session */
    protected $session;
    /** @var  Zend_Db_Table_Row_Abstract */
    protected $memberData;
    /** @var  string */
    protected $access_token;
    /** @var  boolean */
    protected $connected;
    /** @var  string */
    protected $redirect;
    private $client_secret;
    private $client_id;
    private $uri_callback;
    private $uri_token;
    private $uri_auth;
    private $uri_profile;

    /**
     * Default_Model_Oauth_Ocs constructor.
     *
     * @param Zend_Db_Adapter_Abstract|null $dbAdapter
     * @param null                          $tableName
     * @param Zend_Config                   $config
     *
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     */
    public function __construct(Zend_Db_Adapter_Abstract $dbAdapter = null, $tableName = null, Zend_Config $config)
    {
        $this->_db = $dbAdapter;
        if (empty($this->_db)) {
            $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
            if (empty($this->_db)) {
                throw new Zend_Exception('No database adapter present');
            }
        }

        $this->_tableName = $tableName;

        $this->config = $config;
        if (empty($this->config)) {
            throw new Zend_Exception('No config present');
        }

        // Zend_Registry::get('logger')->debug(__METHOD__ . ' - config: ' . print_r($this->config->toArray(), true));

        $this->uri_auth = $this->config->authorize_url;
        $this->uri_token = $this->config->token_url;
        $this->uri_callback = $this->config->callback;
        $this->client_id = $this->config->client_id;
        $this->client_secret = $this->config->client_secret;
        $this->uri_profile = $this->config->profile_user_url;

        $this->session = new Zend_Session_Namespace('OCS_AUTH');
    }

    /**
     * @param null $redirectUrlAfterSuccess
     *
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     */
    public function authStart($redirectUrlAfterSuccess = null)
    {
        $state_token = $this->generateToken('auth');
        $this->saveStateData($state_token, $redirectUrlAfterSuccess);

        $requestUrl = $this->uri_auth . "?client_id={$this->client_id}&redirect_uri=" . urlencode($this->uri_callback)
            . "&scope=profile&state={$state_token}";

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - redirectUrl: ' . print_r($requestUrl, true));

        /** @var Zend_Controller_Action_Helper_Redirector $redirection */
        $redirection = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirection->gotoUrl($requestUrl);
    }

    /**
     * @param $prefix_state
     *
     * @return string
     */
    private function generateToken($prefix_state)
    {
        $prefix = '';
        if (false == empty($prefix_state)) {
            $prefix = $prefix_state . self::PREFIX_SEPARATOR;
        }

        return $prefix . Local_Tools_UUID::generateUUID();
    }

    /**
     * @param      $token
     * @param null $redirect
     *
     * @return bool
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     */
    private function saveStateData($token, $redirect = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');

        return $cache->save(array('redirect' => $redirect), $token, array('auth', 'github'), 120);
    }

    /**
     * @param array $http_params
     *
     * @return null|string
     * @throws Zend_Exception
     */
    public function authFinish($http_params)
    {
        $error = (array_key_exists('error', $http_params)) ? $http_params['error'] : null;
        if ($error) {
            throw new Zend_Exception('Authentication failed. OAuth provider returned an error: ' . $error);
        }

        $request_code = (array_key_exists('code', $http_params)) ? $http_params['code'] : null;
        $session_state_token = (array_key_exists('state', $http_params)) ? $http_params['state'] : null;

        $result = $this->isValidStateCode($session_state_token);
        if ($result === false) {
            $this->connected = false;

            return false;
        }

        $this->access_token = $this->requestAccessToken($request_code, $session_state_token);

        if (isset($this->access_token)) {
            $this->connected = true;
        }

        $this->redirect = $this->getRedirectFromState($session_state_token);

        //        $this->clearStateToken($session_state_token);

        return $this->access_token;
    }

    /**
     * @param $session_state
     *
     * @return bool
     * @throws Zend_Exception
     */
    protected function isValidStateCode($session_state)
    {
        if (empty($session_state)) {
            return false;
        }

        /** @var Zend_Cache_Backend_Apc $cache */
        $cache = Zend_Registry::get('cache');
        if (false == $cache->test($session_state)) {
            Zend_Registry::get('logger')->err(__METHOD__
                . ' - Authentication failed. OAuth provider send a token that does not match.')
            ;

            return false;
        }

        return true;
    }

    /**
     * @param string $code
     * @param        $state_token
     *
     * @return null|string
     * @throws Zend_Exception
     */
    protected function requestAccessToken($code, $state_token)
    {
        $response = $this->requestHttpAccessToken($code, $state_token);
        $data = $this->parseResponse($response);

        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response for post request\n' . print_r($data, true));

        if ($response->getStatus() != 200) {
            throw new Zend_Exception('Authentication failed. OAuth provider send error message: ' . $data['error'] . ' : '
                . $data['error_description']);
        }

        return (array_key_exists('access_token', $data)) ? $data['access_token'] : null;
    }

    /**
     * @param $request_code
     * @param $state_token
     *
     * @return Zend_Http_Response
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    protected function requestHttpAccessToken($request_code, $state_token)
    {
        $httpClient = new Zend_Http_Client($this->uri_token);
        $httpClient->setMethod(Zend_Http_Client::POST);
        $httpClient->setHeaders('Accept', 'application/json');
        $httpClient->setParameterPost(array(
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'code'          => $request_code,
            'redirect_uri'  => $this->uri_callback,
            'state'         => $state_token,
            'grant_type'    => 'authorization_code'
        ));

        $response = $httpClient->request();

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - request: \n' . $httpClient->getLastRequest());
        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response for post request\n'
            . $response->getHeadersAsString())
        ;

        return $response;
    }

    /**
     * @param Zend_Http_Response $response
     *
     * @return mixed
     * @throws Zend_Json_Exception
     */
    protected function parseResponse(Zend_Http_Response $response)
    {
        $data = Zend_Json::decode($response->getBody());

        return $data;
    }

    /**
     * @param $session_state_token
     *
     * @return mixed|null
     * @throws Zend_Exception
     */
    private function getRedirectFromState($session_state_token)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $data = $cache->load($session_state_token);

        return (is_array($data) AND array_key_exists('redirect', $data)) ? $data['redirect'] : null;
    }

    /**
     * @return Zend_Auth_Result
     * @throws Exception
     * @throws Zend_Exception
     */
    public function authenticate()
    {
        $userEmail = $this->getUserEmail();

        Zend_Registry::get('logger')->info(__METHOD__ . ' - userEmail: ' . $userEmail);

        $authResult = $this->authenticateUserEmail($userEmail);

        if ($authResult->isValid()) {
            $authModel = new Default_Model_Authorization();
            $authModel->storeAuthSessionDataByIdentity($this->memberData['member_id']);
            $authModel->updateRememberMe(true);
            $authModel->updateUserLastOnline('member_id', $this->memberData['member_id']);

            return $authResult;
        }

        if ($authResult->getCode() == Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND) {
            return $this->createAuthResult(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, $userEmail,
                array('A record with the supplied identity could not be found.'));
        }

        Zend_Registry::get('logger')->info(__METHOD__ . "\n"
            . ' - authentication error : user=>'.$userEmail.': ' . "\n"
            . ' - messages : ' . implode(",\n",$authResult->getMessages())
        );

        return $authResult;
    }

    /**
     * @return string
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function getUserEmail()
    {
        $httpClient = new Zend_Http_Client($this->uri_profile);
        $httpClient->setHeaders('Authorization', 'Bearer ' . $this->access_token);
        $httpClient->setHeaders('Accept', 'application/json');
        $response = $httpClient->request();
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - last request: \n' . $httpClient->getLastRequest());
        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response from post request\n'
            . $response->getHeadersAsString())
        ;
        $data = $this->parseResponse($response);
        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - parsed response from post request\n' . print_r($data,
                true))
        ;
        if ($response->getStatus() > 200) {
            throw new Zend_Exception('error while request users data');
        }
        if (isset($data['email'])) {
            return $data['email'];
        }

        return '';
    }

    /**
     * @param $userEmail
     *
     * @return Zend_Auth_Result
     * @throws Zend_Exception
     */
    private function authenticateUserEmail($userEmail)
    {
        $validator = new Zend_Validate_EmailAddress();
        if ($validator->isValid($userEmail)) {
            $resultSet = $this->fetchUserByEmail($userEmail);
        } else {
            $resultSet = $this->fetchUserByUsername($userEmail);
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - ResultSet: ' . print_r($resultSet, true));

        if (count($resultSet) == 0) {
            return $this->createAuthResult(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, $userEmail,
                array('A record with the supplied identity could not be found.'));
        }

        if (count($resultSet) > 1) {
            return $this->createAuthResult(Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS, $userEmail,
                array('More than one record matches the supplied identity.'));
        }
        $this->memberData = array_shift($resultSet);
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - this->memberData: ' . print_r($this->memberData, true));

        return $this->createAuthResult(Zend_Auth_Result::SUCCESS, $userEmail, array('Authentication successful.'));
    }

    /**
     * @param $userEmail
     *
     * @return array
     * @throws Zend_Exception
     */
    private function fetchUserByEmail($userEmail)
    {
        $sql = "
            SELECT * 
            FROM {$this->_tableName} 
            WHERE 
            is_active = :active AND 
            is_deleted = :deleted AND 
            login_method = :login AND 
            mail = :mail";

        $this->_db->getProfiler()->setEnabled(true);
        $resultSet = $this->_db->fetchAll($sql, array(
            'active'  => Default_Model_DbTable_Member::MEMBER_ACTIVE,
            'deleted' => Default_Model_DbTable_Member::MEMBER_NOT_DELETED,
            'login'   => Default_Model_DbTable_Member::MEMBER_LOGIN_LOCAL,
            'mail'    => $userEmail
        ));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - ResultSet: ' . print_r($resultSet, true));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - sql take seconds: ' . $this->_db->getProfiler()->getLastQueryProfile()
                                                                                            ->getElapsedSecs())
        ;
        $this->_db->getProfiler()->setEnabled(false);

        return $resultSet;
    }

    /**
     * @param $userEmail
     *
     * @return array
     * @throws Zend_Exception
     */
    private function fetchUserByUsername($userEmail)
    {
        $sql = "
            SELECT * 
            FROM {$this->_tableName} 
            WHERE 
            is_active = :active AND 
            is_deleted = :deleted AND 
            login_method = :login AND 
            username = :username";

        $this->_db->getProfiler()->setEnabled(true);
        $resultSet = $this->_db->fetchAll($sql, array(
            'active'   => Default_Model_DbTable_Member::MEMBER_ACTIVE,
            'deleted'  => Default_Model_DbTable_Member::MEMBER_NOT_DELETED,
            'login'    => Default_Model_DbTable_Member::MEMBER_LOGIN_LOCAL,
            'username' => $userEmail
        ));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - sql take seconds: ' . $this->_db->getProfiler()->getLastQueryProfile()
                                                                                            ->getElapsedSecs())
        ;
        $this->_db->getProfiler()->setEnabled(false);

        return $resultSet;
    }

    /**
     * @param $code
     * @param $identity
     * @param $messages
     *
     * @return Zend_Auth_Result
     */
    protected function createAuthResult($code, $identity, $messages)
    {
        return new Zend_Auth_Result($code, $identity, $messages);
    }

    /**
     * @param $email
     *
     * @return bool|Zend_Db_Table_Row_Abstract
     */
    public function findActiveMemberByEmail($email)
    {
        $modelMember = new Default_Model_Member();
        $member = $modelMember->findActiveMemberByIdentity($email);
        if (empty($member->member_id)) {
            return false;
        }

        return $member;
    }

    /**
     * @param      $access_token
     * @param null $username
     *
     * @return mixed
     * @throws Exception
     */
    public function storeAccessToken($access_token, $username = null)
    {
        $member_id = Zend_Auth::getInstance()->getIdentity()->member_id;

        $modelToken = new Default_Model_DbTable_MemberToken();
        $rowToken = $modelToken->save(array(
            'token_member_id'         => $member_id,
            'token_provider_name'     => 'ocs_login',
            'token_value'             => $access_token,
            'token_provider_username' => $username
        ));

        return $rowToken;
    }

    /**
     * @return bool|mixed
     */
    public function getRedirect()
    {
        if ($this->redirect) {
            $filterRedirect = new Local_Filter_Url_Decrypt();
            $redirect = $filterRedirect->filter($this->redirect);
            $this->redirect = null;

            return $redirect;
        }

        return false;
    }

    /**
     * @param $token_id
     *
     * @return string
     * @throws Zend_Exception
     */
    public function authStartWithToken($token_id)
    {
        $requestUrl = $this->uri_auth . "?client_id={$this->client_id}&redirect_uri=" . urlencode($this->uri_callback)
            . "&scope=profile&state={$token_id}&response_type=code";

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - redirectUrl: ' . print_r($requestUrl, true));

        return $requestUrl;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return (boolean)$this->connected;
    }

    /**
     * @param $token
     *
     * @return bool
     * @throws Zend_Exception
     */
    protected function clearStateToken($token)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');

        return $cache->remove($token);
    }

}