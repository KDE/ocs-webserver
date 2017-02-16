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
        $this->storeRedirectUrl($redirectUrlAfterSuccess);
        $state_token = $this->getStateToken('auth');

        $requestUrl = self::URI_AUTH . "?client_id={$this->config->client_id}&redirect_uri=" . urlencode($this->config->client_callback) . "&scope=user&state={$state_token}";

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - redirectUrl: ' . print_r($requestUrl, true));

        /** @var Zend_Controller_Action_Helper_Redirector $redirection */
        $redirection = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirection->gotoUrl($requestUrl);
    }

    private function storeRedirectUrl($redirectUrlAfterSuccess)
    {
        $this->session->redirect = $redirectUrlAfterSuccess;
    }

    private function getStateToken($prefix_state)
    {
        if (isset($this->session->state_token)) {
            return $this->session->state_token;
        } else {
            return $this->generateToken($prefix_state);
        }
    }

    private function generateToken($prefix_state)
    {
        $prefix = '';
        if (false == empty($prefix_state)) {
            $prefix = $prefix_state . self::PREFIX_SEPARATOR;
        }
        $token = $prefix . Local_Tools_UUID::generateUUID();
        $this->session->state_token = $token;
        return $token;
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
        $session_state = (array_key_exists('state', $http_params)) ? $http_params['state'] : null;

        $this->validateStateCode($session_state);

        $access_token = $this->requestAccessToken($request_code);

        return $access_token;
    }

    /**
     * @param $session_state
     * @throws Zend_Exception
     */
    protected function validateStateCode($session_state)
    {
        if ($session_state != $this->session->state_token) {
            throw new Zend_Exception('Authentication failed. OAuth provider send a token that does not match.');
        }
    }

    /**
     * @param string $code
     * @return string|null
     * @throws Zend_Exception
     */
    protected function requestAccessToken($code)
    {
        $response = $this->requestHttpAccessToken($code);
        $data = $this->parseResponse($response);

        if ($response->getStatus() != 200) {
            throw new Zend_Exception('Authentication failed. OAuth provider send error message: ' . $data['error'] . ' : ' . $data['error_description']);
        }

        $this->clearStateToken();

        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response for post request\n' . print_r($data,
                true));

        $access_token = (array_key_exists('access_token', $data)) ? $data['access_token'] : null;
        $this->session->access_token = $access_token;

        return $access_token;
    }

    /**
     * @param $code
     * @return Zend_Http_Response
     */
    protected function requestHttpAccessToken($code)
    {
        $httpClient = new Zend_Http_Client(self::URI_ACCESS);
        $httpClient->setMethod(Zend_Http_Client::POST);
        $httpClient->setHeaders('Accept', 'application/json');
        $httpClient->setParameterPost(array(
            'client_id' => $this->config->client_id,
            'client_secret' => $this->config->client_secret,
            'code' => $code,
            'redirect_uri' => $this->config->client_callback,
            'state' => $this->getStateToken('auth')
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

    protected function clearStateToken()
    {
        $this->session->state_token = null;
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
            $authModel->updateRememberMe();
            $authModel->updateUserLastOnline('member_id', $this->memberData['member_id']);
            return $authResult;
        }

        if ($authResult->getCode() == Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND) {
            return $this->registerLocal();
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - error while authenticate user from oauth provider: ' . implode(",\n",
                $authResult->getMessages()));
        return $authResult;
    }

    public function getUserEmail()
    {
        $httpClient = new Zend_Http_Client(self::URI_EMAIL);
        $httpClient->setHeaders('Authorization', 'token ' . $this->session->access_token);
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
            $authModel->updateRememberMe();
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
        $httpClient->setHeaders('Authorization', 'token ' . $this->session->access_token);
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
        return isset($this->session->access_token);
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

    public function gotoRedirect()
    {
        if ($this->session->redirect) {
            $redirect = $this->session->redirect;
            $this->session->redirect = null;
            /** @var Zend_Controller_Action_Helper_Redirector $redirection */
            $redirection = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirection->gotoUrl($redirect);
        }
        return false;
    }

    public function setRegisterAfterLogin($doRegister = false)
    {
        if ($doRegister) {
            $this->session->doRegister = true;
        } else {
            $this->session->doRegister = null;
        }
    }

}