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
 *    Created: 22.11.2016
 **/
class Default_Model_Oauth_Github
{

    const PREFIX_SEPARATOR = '_';

    const URI_AUTH = "https://github.com/login/oauth/authorize";
    const URI_ACCESS = 'https://github.com/login/oauth/access_token';
    const URI_USER = 'https://api.github.com/user';
    const URI_EMAIL = 'https://api.github.com/user/emails';
    const URI_CREATE_AUTH = 'https://api.github.com/authorizations/clients';

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

    /**
     * @inheritDoc
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

        $this->session = new Zend_Session_Namespace('GITHUB_AUTH');
    }

    public function authStart($redirectUrlAfterSuccess = null)
    {
        $state_token = $this->generateToken('auth');
        $this->saveStateData($state_token, $redirectUrlAfterSuccess);

        $requestUrl = self::URI_AUTH . "?client_id={$this->config->client_id}&redirect_uri=" . urlencode($this->config->client_callback) . "&scope=user&state={$state_token}";

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - redirectUrl: ' . print_r($requestUrl, true));

        /** @var Zend_Controller_Action_Helper_Redirector $redirection */
        $redirection = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirection->gotoUrl($requestUrl);
    }

    private function saveStateData($token, $redirect = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        return $cache->save(array('redirect' => $redirect), $token, array('auth', 'github'), 120);
    }

    private function generateToken($prefix_state)
    {
        $prefix = '';
        if (false == empty($prefix_state)) {
            $prefix = $prefix_state . self::PREFIX_SEPARATOR;
        }
        return $prefix . Local_Tools_UUID::generateUUID();
    }

    /**
     * @param array $http_params
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

        if(isset($this->access_token)) {
            $this->connected = true;
        }

        $this->redirect = $this->getRedirectFromState($session_state_token);
//        $this->clearStateToken($session_state_token);

        return $this->access_token;
    }

    /**
     * @param $session_state
     * @return bool
     */
    protected function isValidStateCode($session_state)
    {
        if (empty($session_state)) {
            return false;
        }

        /** @var Zend_Cache_Backend_Apc $cache */
        $cache = Zend_Registry::get('cache');
        if (false == $cache->test($session_state)) {
           Zend_Registry::get('logger')->err(__METHOD__ . ' - Authentication failed. OAuth provider send a token that does not match.');
           return false;
        }
        return true;
    }

    /**
     * @param string $code
     * @param $state_token
     * @return null|string
     * @throws Zend_Exception
     */
    protected function requestAccessToken($code, $state_token)
    {
        $response = $this->requestHttpAccessToken($code, $state_token);
        $data = $this->parseResponse($response);

        if ($response->getStatus() != 200) {
            throw new Zend_Exception('Authentication failed. OAuth provider send error message: ' . $data['error'] . ' : ' . $data['error_description']);
        }

        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response for post request\n' . print_r($data, true));

        return (array_key_exists('access_token', $data)) ? $data['access_token'] : null;
    }

    /**
     * @param $request_code
     * @param $state_token
     * @return Zend_Http_Response
     */
    protected function requestHttpAccessToken($request_code, $state_token)
    {
        $httpClient = new Zend_Http_Client(self::URI_ACCESS);
        $httpClient->setMethod(Zend_Http_Client::POST);
        $httpClient->setHeaders('Accept', 'application/json');
        $httpClient->setParameterPost(array(
            'client_id' => $this->config->client_id,
            'client_secret' => $this->config->client_secret,
            'code' => $request_code,
            'redirect_uri' => $this->config->client_callback,
            'state' => $state_token
        ));

        $response = $httpClient->request();

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - request: \n' . $httpClient->getLastRequest());
        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response for post request\n' . $response->getHeadersAsString());

        return $response;
    }

    /**
     * @param Zend_Http_Response $response
     * @return mixed
     */
    protected function parseResponse(Zend_Http_Response $response)
    {
        $data = Zend_Json::decode($response->getBody());
        return $data;
    }

    protected function clearStateToken($token)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        return $cache->remove($token);
    }

    /**
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $userEmail = $this->getUserEmail();

        $authResult = $this->authenticateUserEmail($userEmail);

        if ($authResult->isValid()) {
            $authModel = new Default_Model_Authorization();
            $authModel->storeAuthSessionDataByIdentity($this->memberData['member_id']);
            $authModel->updateRememberMe(true);
            $authModel->updateUserLastOnline('member_id', $this->memberData['member_id']);
            return $authResult;
        }

        if ($authResult->getCode() == Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND) {
            return $this->registerLocal();
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - error while authenticate user from oauth provider: ' .
            implode(",\n", $authResult->getMessages()));
        return $authResult;
    }

    public function getUserEmail()
    {
        $httpClient = new Zend_Http_Client(self::URI_EMAIL);
        $httpClient->setHeaders('Authorization', 'token ' . $this->access_token);
        $httpClient->setHeaders('Accept', 'application/json');
        $response = $httpClient->request();
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - last request: \n' . $httpClient->getLastRequest());
        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response from post request\n' . $response->getHeadersAsString());
        $data = $this->parseResponse($response);
        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response from post request\n' . print_r($data, true));
        if ($response->getStatus() > 200) {
            throw new Zend_Exception('error while request users data');
        }
        foreach ($data as $datum) {
            if ($datum['primary']) {
                return $datum['email'];
            }
        }
        return '';
    }

    private function authenticateUserEmail($userEmail)
    {
        $validator = new Zend_Validate_EmailAddress();
        if ($validator->isValid($userEmail)) {
            $resultSet = $this->fetchUserByEmail($userEmail);
        } else {
            $resultSet = $this->fetchUserByUsername($userEmail);
        }

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
        return $this->createAuthResult(Zend_Auth_Result::SUCCESS, $userEmail,
                array('Authentication successful.'));
    }

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
            'active' => Default_Model_DbTable_Member::MEMBER_ACTIVE,
            'deleted' => Default_Model_DbTable_Member::MEMBER_NOT_DELETED,
            'login' => Default_Model_DbTable_Member::MEMBER_LOGIN_LOCAL,
            'mail' => $userEmail
        ));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - sql take seconds: ' . $this->_db->getProfiler()->getLastQueryProfile()->getElapsedSecs());
        $this->_db->getProfiler()->setEnabled(false);

        return $resultSet;
    }

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
            'active' => Default_Model_DbTable_Member::MEMBER_ACTIVE,
            'deleted' => Default_Model_DbTable_Member::MEMBER_NOT_DELETED,
            'login' => Default_Model_DbTable_Member::MEMBER_LOGIN_LOCAL,
            'username' => $userEmail
        ));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - sql take seconds: ' . $this->_db->getProfiler()->getLastQueryProfile()->getElapsedSecs());
        $this->_db->getProfiler()->setEnabled(false);

        return $resultSet;
    }

    protected function createAuthResult($code, $identity, $messages)
    {
        return new Zend_Auth_Result(
            $code,
            $identity,
            $messages
        );
    }

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
     * @return Zend_Auth_Result
     */
    public function registerLocal()
    {
        $this->setRegisterAfterLogin(false);

        $userInfo = $this->getUserInfo();
        $userInfo['email'] = $this->getUserEmail();

        $newUserValues = array(
            'username' => $userInfo['login'],
            'password' => $this->generateNewPassword(),
            'lastname' => $userInfo['name'],
            'mail' => $userInfo['email'],
            'roleId' => Default_Model_DbTable_Member::ROLE_ID_DEFAULT,
            'is_active' => 1,
            'mail_checked' => 1,
            'agb' => 1,
            'login_method' => Default_Model_Member::MEMBER_LOGIN_LOCAL,
            'profile_img_src' => 'local',
            'profile_image_url' => $userInfo['avatar_url'],
            'social_username' => $userInfo['login'],
            'social_user_id' => $userInfo['id'],
            'link_github' => $userInfo['name'],
            'created_at' => new Zend_Db_Expr('Now()'),
            'changed_at' => new Zend_Db_Expr('Now()'),
            'uuid' => Local_Tools_UUID::generateUUID(),
            'verificationVal' => MD5($userInfo['id'] . $userInfo['login'] . time())
        );

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - new user data: '. print_r($newUserValues, true));
        $modelMember = new Default_Model_Member();
        $member = $modelMember->createNewUser($newUserValues);
        $modelEmail = new Default_Model_MemberEmail();
        $userEmail = $modelEmail->saveEmailAsPrimary($member['member_id'], $member['mail'], $newUserValues['verificationVal']);
        $userEmail->email_checked = new Zend_Db_Expr('Now()');
        $userEmail->save();

        if ($member->member_id) {
            $authModel = new Default_Model_Authorization();
            $authModel->storeAuthSessionDataByIdentity($member->member_id);
            $authModel->updateRememberMe(true);
            $authModel->updateUserLastOnline('member_id', $member->member_id);
            return $this->createAuthResult(Zend_Auth_Result::SUCCESS, $member['mail'],
                array('Authentication successful.'));
        }
        return $this->createAuthResult(Zend_Auth_Result::FAILURE, $userEmail,
            array('A user with given data could not registered.'));
    }

    public function getUserInfo()
    {
        $httpClient = new Zend_Http_Client(self::URI_USER);
        $httpClient->setHeaders('Authorization', 'token ' . $this->access_token);
        $httpClient->setHeaders('Accept', 'application/json');
        $response = $httpClient->request();
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - last request: \n' . $httpClient->getLastRequest());
        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response from post request\n' . $response->getHeadersAsString());
        $data = $this->parseResponse($response);
        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response from post request\n' . print_r($data,
                true));
        if ($response->getStatus() > 200) {
            throw new Zend_Exception('error while request users data');
        }
        return $data;
    }

    /**
     * @return string
     */
    protected function generateNewPassword()
    {
        include_once('PWGen.php');
        $pwgen = new PWGen();
        $newPass = $pwgen->generate();
        return $newPass;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return (boolean)$this->connected;
    }

    /**
     * @param $access_token
     * @param null $username
     * @return mixed
     */
    public function storeAccessToken($access_token, $username = null)
    {
        $member_id = Zend_Auth::getInstance()->getIdentity()->member_id;

        $modelToken = new Default_Model_DbTable_MemberToken();
        $rowToken = $modelToken->save(array(
            'token_member_id' => $member_id,
            'token_provider_name' => 'github_login',
            'token_value' => $access_token,
            'token_provider_username' => $username
        ));
        return $rowToken;
    }

    public function requestUsername()
    {
        $userinfo = $this->getUserInfo();
        return (array_key_exists('login', $userinfo)) ? $userinfo['login'] : '';
    }

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

    private function getRedirectFromState($session_state_token)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $data = $cache->load($session_state_token);
        return (is_array($data) AND array_key_exists('redirect', $data)) ? $data['redirect'] : null;
    }

    /**
     * @param $token_id
     * @return string
     */
    public function authStartWithToken($token_id)
    {
        $requestUrl = self::URI_AUTH . "?client_id={$this->config->client_id}&redirect_uri=" . urlencode($this->config->client_callback) . "&scope=user&state={$token_id}";

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - redirectUrl: ' . print_r($requestUrl, true));

        return $requestUrl;
    }

}