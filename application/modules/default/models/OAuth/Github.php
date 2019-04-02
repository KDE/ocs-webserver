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
class Default_Model_OAuth_Github implements Default_Model_OAuth_Interface
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
    /** @var  array */
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

    /**
     * @param null $redirectUrlAfterSuccess
     *
     * @throws Zend_Exception
     */
    public function authStart($redirectUrlAfterSuccess = null)
    {
        $state_token = $this->generateToken('auth');
        $this->saveStateData($state_token, $redirectUrlAfterSuccess);

        $requestUrl =
            self::URI_AUTH . "?client_id={$this->config->client_id}&redirect_uri=" . urlencode($this->config->client_callback)
            . "&scope=user&state={$state_token}";

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - redirectUrl: ' . print_r($requestUrl, true));

        /** @var Zend_Controller_Action_Helper_Redirector $redirection */
        $redirection = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirection->gotoUrl($requestUrl);
    }

    /**
     * @param string $prefix_state
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
     * @param string      $token
     * @param null|string $redirect
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

        if ($response->getStatus() != 200) {
            throw new Zend_Exception('Authentication failed. OAuth provider send error message: ' . $data['error'] . ' : '
                . $data['error_description']);
        }

        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response for post request\n' . print_r($data, true));

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
        $httpClient = new Zend_Http_Client(self::URI_ACCESS);
        $httpClient->setMethod(Zend_Http_Client::POST);
        $httpClient->setHeaders('Accept', 'application/json');
        $httpClient->setParameterPost(array(
            'client_id'     => $this->config->client_id,
            'client_secret' => $this->config->client_secret,
            'code'          => $request_code,
            'redirect_uri'  => $this->config->client_callback,
            'state'         => $state_token
        ));

        $response = $httpClient->request();

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - request : \n' . $httpClient->getLastRequest());
        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response : \n' . $response->getHeadersAsString());

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
     * @param string $session_state_token
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

        $authResult = $this->authenticateUserEmail($userEmail['email']);

        if (false === $authResult->isValid()) {
            Zend_Registry::get('logger')->info(__METHOD__ . "\n" . ' - authentication error : user=>' . $userEmail . ': ' . "\n"
                . ' - messages : ' . implode(",\n", $authResult->getMessages()))
            ;

            return $authResult;
        }

        $this->syncMemberData($userEmail);

        $authModel = new Default_Model_Authorization();
        $authModel->storeAuthSessionDataByIdentity($this->memberData['member_id']);
        $authModel->updateRememberMe(true);
        $authModel->updateUserLastOnline('member_id', $this->memberData['member_id']);

        return $authResult;
    }

    /**
     * @return array
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function getUserEmail()
    {
        $httpClient = new Zend_Http_Client(self::URI_EMAIL);
        $httpClient->setHeaders('Authorization', 'token ' . $this->access_token);
        $httpClient->setHeaders('Accept', 'application/json');
        $response = $httpClient->request();

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - last request : \n' . $httpClient->getLastRequest());
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - response header : ' . $response->getHeadersAsString());
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - response : ' . $response->getRawBody());

        if ($response->getStatus() < 200 OR $response->getStatus() >= 300) {
            throw new Zend_Exception('error while request user data : ' . $response->getRawBody());
        }

        $data = $this->parseResponse($response);
        foreach ($data as $element) {
            if ($element['primary']) {
                return $element;
            }
        }

        return array();
    }

    /**
     * @param string $userEmail
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
            throw new Zend_Exception('no valid email address from github given.');
        }

        if (count($resultSet) == 0) {
            return $this->createAuthResult(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, $userEmail,
                array('A record with the supplied identity could not be found.'));
        }

        if (count($resultSet) > 1) {
            return $this->createAuthResult(Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS, $userEmail,
                array('More than one record matches the supplied identity.'));
        }

        if (empty($resultSet[0]['email_checked'])) {
            return $this->createAuthResult(Local_Auth_Result::MAIL_ADDRESS_NOT_VALIDATED, $resultSet[0]['member_id'],
                array('Mail address not validated.'));
        }

        if ($resultSet[0]['is_active'] == 0) {
            return $this->createAuthResult(Local_Auth_Result::ACCOUNT_INACTIVE, $userEmail,
                array('User account is inactive.'));
        }

        $this->memberData = array_shift($resultSet);
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - this->memberData: ' . Zend_Json::encode($this->memberData));

        if ($this->memberData['is_deleted'] == 1) {
            return $this->createAuthResult(Zend_Auth_Result::FAILURE, $userEmail, array('User is deleted.'));
        }

        return $this->createAuthResult(Zend_Auth_Result::SUCCESS, $userEmail, array('Authentication successful.'));
    }

    /**
     * @param string $userEmail
     *
     * @return array
     * @throws Zend_Exception
     */
    private function fetchUserByEmail($userEmail)
    {
        $sql = "            
            SELECT `m`.*, `member_email`.`email_verification_value`, `member_email`.`email_checked`, `mei`.`external_id` 
            FROM `member` AS `m`
            JOIN `member_email` ON `m`.`member_id` = `member_email`.`email_member_id` 
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE  
              `m`.`is_deleted` = 0 
            AND 
              `member_email`.`email_deleted` = 0
            AND
              `member_email`.`email_primary` = 1
            AND
            ( LOWER(`member_email`.`email_address`) = LOWER(:mail) OR LOWER(`member_email`.`email_address`) = CONCAT(LOWER(:mail),'_double') )";

        $this->_db->getProfiler()->setEnabled(true);
        $resultSet = $this->_db->fetchAll($sql, array(
            'mail' => $userEmail
        ));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - seconds: ' . $this->_db->getProfiler()->getLastQueryProfile()
                                                                                   ->getElapsedSecs())
        ;
        $this->_db->getProfiler()->setEnabled(false);

        return $resultSet;
    }

    /**
     * @param int    $code
     * @param string $identity
     * @param array  $messages
     *
     * @return Zend_Auth_Result
     */
    protected function createAuthResult($code, $identity, $messages)
    {
        return new Zend_Auth_Result($code, $identity, $messages);
    }

    /**
     * @param $userEmail
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    private function syncMemberData($userEmail)
    {
        if (empty($this->memberData)) {
            return false;
        }

        $modelMember = new Default_Model_Member();
        $member = $modelMember->fetchMemberData($this->memberData['member_id'], false);

        $userInfo = $this->getUserInfo();

        $updated = false;

        if ($member->social_username != $userInfo['login']) {
            $member->social_username = $userInfo['login'];
            $updated = $updated && true;
        }
        if ($member->social_user_id != $userInfo['id']) {
            $member->social_user_id = $userInfo['id'];
            $updated = $updated && true;
        }
        if ($member->link_github != $userInfo['login']) {
            $member->link_github = $userInfo['login'];
            $updated = $updated && true;
        }
        $verified = $userEmail['verified'] ? 1 : 0;
        if ($member->mail_checked != $verified) {
            $member->mail_checked = $verified;

            $updated = $updated && true;
        }

        if ($updated) {
            $member->save();
        }

        if ($member->is_active == Default_Model_Member::MEMBER_INACTIVE) {
            $modelMember->setActive($member->member_id, $userEmail['email']);
        }
    }

    /**
     * @return mixed
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function getUserInfo()
    {
        $httpClient = new Zend_Http_Client(self::URI_USER);
        $httpClient->setHeaders('Authorization', 'token ' . $this->access_token);
        $httpClient->setHeaders('Accept', 'application/json');
        $response = $httpClient->request();
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - last request: \n' . $httpClient->getLastRequest());
        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response from post request\n'
            . $response->getHeadersAsString())
        ;
        $data = $this->parseResponse($response);
        Zend_Registry::getInstance()->get('logger')->debug(__METHOD__ . ' - response from post request\n' . print_r($data, true));
        if ($response->getStatus() > 200) {
            throw new Zend_Exception('error while request users data');
        }

        return $data;
    }

    /**
     * @param string $email
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
     * @return Zend_Auth_Result
     * @throws Exception
     * @throws Zend_Exception
     */
    public function registerLocal()
    {
        $userInfo = $this->getUserInfo();
        $usermail = $this->getUserEmail();
        $userInfo['email'] = $usermail['email'];
        $userInfo['verified'] = $usermail['verified'] ? 1 : 0;

        $newUserValues = array(
            'username'          => strtolower($userInfo['login']),
            'password'          => $this->generateNewPassword(),
            'lastname'          => $userInfo['name'],
            'mail'              => $userInfo['email'],
            'roleId'            => Default_Model_DbTable_Member::ROLE_ID_DEFAULT,
            'is_active'         => 1,
            'mail_checked'      => $userInfo['verified'],
            'agb'               => 1,
            'login_method'      => Default_Model_Member::MEMBER_LOGIN_LOCAL,
            'profile_img_src'   => 'local',
            'profile_image_url' => $userInfo['avatar_url'],
            'avatar'            => basename($userInfo['avatar_url']),
            'social_username'   => $userInfo['login'],
            'social_user_id'    => $userInfo['id'],
            'link_github'       => $userInfo['login'],
            'created_at'        => new Zend_Db_Expr('Now()'),
            'changed_at'        => new Zend_Db_Expr('Now()'),
            'uuid'              => Local_Tools_UUID::generateUUID(),
            'verificationVal'   => MD5($userInfo['id'] . $userInfo['login'] . time())
        );

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - new user data: ' . print_r($newUserValues, true));
        $modelMember = new Default_Model_Member();
        $result = $modelMember->findUsername(strtolower($userInfo['login']));
        $flagUsernameChanged = false;
        if (count($result) > 0) {
            $newUserValues['username'] = $modelMember->generateUniqueUsername(strtolower($userInfo['login']));
            $flagUsernameChanged = true;
            Zend_Registry::get('logger')->info(__METHOD__ . ' - username already in use. new generated username: '
                . $userInfo['username'])
            ;
        }
        $member = $modelMember->createNewUser($newUserValues);

        if (empty($member)) {
            return $this->createAuthResult(Zend_Auth_Result::FAILURE, $member['mail'],
                array('A user with given data could not registered.'));
        }

        //Send user to subsystems
        try {
            $id_server = new Default_Model_Ocs_OAuth();
            $id_server->createUser($member['member_id']);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $ldap_server = new Default_Model_Ocs_Ldap();
            $ldap_server->createUser($member['member_id']);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - ldap : ' . implode(PHP_EOL . " - ", $ldap_server->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $openCode = new Default_Model_Ocs_Gitlab();
            $openCode->createUser($member['member_id']);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - opencode : ' . implode(PHP_EOL . " - ", $openCode->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        Default_Model_ActivityLog::logActivity($member['main_project_id'], null, $member['member_id'],
            Default_Model_ActivityLog::MEMBER_JOINED, array());

        $authModel = new Default_Model_Authorization();
        $authModel->storeAuthSessionDataByIdentity($member['member_id']);
        $authModel->updateRememberMe(true);
        $authModel->updateUserLastOnline('member_id', $member['member_id']);
        if ($flagUsernameChanged) {
            return $this->createAuthResult(Zend_Auth_Result::SUCCESS, $userInfo,
                array('Authentication successful but username was changed.'));
        }

        return $this->createAuthResult(Zend_Auth_Result::SUCCESS, $member['mail'], array('Authentication successful.'));
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
            'token_provider_name'     => 'github_login',
            'token_value'             => $access_token,
            'token_provider_username' => $username
        ));

        return $rowToken;
    }

    /**
     * @return string
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function requestUsername()
    {
        $userinfo = $this->getUserInfo();

        return (array_key_exists('login', $userinfo)) ? $userinfo['login'] : '';
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
        $requestUrl =
            self::URI_AUTH . "?client_id={$this->config->client_id}&redirect_uri=" . urlencode($this->config->client_callback)
            . "&scope=user&state={$token_id}";

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - redirectUrl: ' . print_r($requestUrl, true));
        Zend_Registry::get('logger')->debug(__METHOD__ . '(' . __LINE__ . ') - ' . PHP_EOL
            . 'HOST        :: ' . $_SERVER['HTTP_HOST'] . PHP_EOL
            . 'USER_AGENT  :: ' . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'undefined') . PHP_EOL
            . 'REQUEST_URI :: ' . $_SERVER['REQUEST_URI'] . PHP_EOL
            . 'FORWARDED_IP:: ' . (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : 'undefined') . PHP_EOL
            . 'REMOTE_ADDR :: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL
            . 'ENVIRONMENT :: ' . APPLICATION_ENV . PHP_EOL
        );

        return $requestUrl;
    }

    /**
     * @param string $token
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

    /**
     * @param string $userEmail
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
            is_deleted = :deleted AND 
            username = :username";

        $this->_db->getProfiler()->setEnabled(true);
        $resultSet = $this->_db->fetchAll($sql, array(
            'deleted'  => Default_Model_DbTable_Member::MEMBER_NOT_DELETED,
            'username' => $userEmail
        ));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - seconds: ' . $this->_db->getProfiler()->getLastQueryProfile()
                                                                                   ->getElapsedSecs())
        ;
        $this->_db->getProfiler()->setEnabled(false);

        return $resultSet;
    }

}