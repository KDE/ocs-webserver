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
class AuthorizationController extends Local_Controller_Action_DomainSwitch
{

    const DEFAULT_ROLE_ID = 300;
    const PROFILE_IMG_SRC_LOCAL = 'local';

    public function githubAction()
    {
        require_once APPLICATION_LIB . '/Local/CrawlerDetect.php';
        if (crawlerDetect($_SERVER['HTTP_USER_AGENT'])) {
            $this->getResponse()->setHttpResponseCode(404);
            $this->forward('index', 'explore');

            return;
        }
        $this->forward('login', 'oauth', 'default',
            array('provider' => 'github', 'redirect' => $this->getParam('redirect')));
    }

    public function ocsAction()
    {
        require_once APPLICATION_LIB . '/Local/CrawlerDetect.php';
        if (crawlerDetect($_SERVER['HTTP_USER_AGENT'])) {
            $this->getResponse()->setHttpResponseCode(404);
            $this->forward('index', 'explore');

            return;
        }
        $this->forward('login', 'oauth', 'default',
            array('provider' => 'ocs', 'redirect' => $this->getParam('redirect')));
    }

    public function redirectAction()
    {
        $param = null;
        if (preg_match("/redirect\/(.*?)$/i", $this->getRequest()->getRequestUri(), $result)) {
            $param = array('redirect' => $result[1]);
        }
        $this->forward('login', null, null, $param);
    }

    /**
     * login from cookie
     *
     * @throws Zend_Auth_Storage_Exception
     * @throws Zend_Form_Exception
     * @throws Zend_Session_Exception
     * @throws exception
     */
    public function lfcAction()
    {
        $this->view->success = 0;
        $this->view->noPopup = true;

        //TODO: check redirect for a local valid url.
        $this->view->redirect = $this->getParam('redirect');

        $formLogin = new Default_Form_Login();
        $formLogin->setAction('/login/lfc/');
        $formLogin->getElement('remember_me')->setValue(true);

        if ($this->_request->isGet()) { // not a POST request
            $this->view->form = $formLogin->populate(array('redirect' => $this->view->redirect));
            $this->view->error = 0;

            return;
        }

        Zend_Registry::get('logger')->info(__METHOD__
                                           . PHP_EOL . ' - authentication attempt on host: ' . Zend_Registry::get('store_host')
                                           . PHP_EOL . ' - param redirect: ' . $this->getParam('redirect')
                                           . PHP_EOL . ' - from ip: ' . $this->_request->getClientIp()
        );

        if (false === $formLogin->isValid($_POST)) { // form not valid
            Zend_Registry::get('logger')->info(__METHOD__
                                               . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
                                               . PHP_EOL . ' - form not valid:'
                                               . PHP_EOL . print_r($formLogin->getMessages(), true));

            $this->view->form = $formLogin;
            $this->view->errorText = 'index.login.error.auth';
            $this->view->error = 1;

            return;
        }

        $values = $formLogin->getValues();
        $authModel = new Default_Model_Authorization();
        $authResult = $authModel->authenticateUser($values['mail'], $values['password'], $values['remember_me']);

        if (false == $authResult->isValid()) { // authentication fail
            Zend_Registry::get('logger')->info(__METHOD__
                                               . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
                                               . PHP_EOL . ' - authentication fail: '
                                               . PHP_EOL . print_r($authResult->getMessages(), true)
            );
            $this->view->errorText = 'index.login.error.auth';
            $this->view->form = $formLogin;
            $this->view->error = 1;
            $this->_helper->viewRenderer('login');

            return;
        }

        $auth = Zend_Auth::getInstance();
        $userId = $auth->getStorage()->read()->member_id;

        // handle redirect
        if (false === empty($this->view->redirect)) {
            $redirect = $this->decodeString($this->view->redirect);
            if (false !== strpos('/register', $redirect)) {
                $redirect = '/member/' . $userId . '/activities/';
            }
            $this->redirect($redirect);
        } else {
            $this->redirect('/member/' . $userId . '/activities/');
        }
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function decodeString($string)
    {
        $decodeFilter = new Local_Filter_Url_Decrypt();

        return $decodeFilter->filter($string);
    }

    /**
     * @throws Zend_Auth_Storage_Exception
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     * @throws exception
     */
    public function propagateAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->json(array('status' => 'ok', 'message' => 'Already logged in.'));
        }

        Zend_Registry::get('logger')->info(__METHOD__
                                           . PHP_EOL . ' - token: ' . $this->getParam('token')
                                           . PHP_EOL . ' - host: ' . Zend_Registry::get('store_host')
                                           . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
        );

        $modelAuthToken = new Default_Model_SingleSignOnToken();
        $token_data = $modelAuthToken->getData($this->getParam('token'));
        if (false === $token_data) {
            Zend_Registry::get('logger')->warn(__METHOD__
                                               . PHP_EOL . ' - Login failed: no token exists'
                                               . PHP_EOL . ' - host: ' . Zend_Registry::get('store_host')
                                               . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
            );
            $this->_helper->json(array('status' => 'fail', 'message' => 'Login failed.'));
        }
        $remember_me = isset($token_data['remember_me']) ? (boolean)$token_data['remember_me'] : false;
        $member_id = isset($token_data['member_id']) ? (int)$token_data['member_id'] : null;

        $modelAuth = new Default_Model_Authorization();
        $authResult = $modelAuth->authenticateUser($member_id, null, $remember_me,
            Local_Auth_AdapterFactory::LOGIN_SSO);

        if ($authResult->isValid()) {
            Zend_Registry::get('logger')->info(__METHOD__
                                               . PHP_EOL . ' - authentication successful: '
                                               . PHP_EOL . ' - host: ' . Zend_Registry::get('store_host')
                                               . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
            );
            $this->getResponse()->setHeader('Access-Control-Allow-Origin', $this->getParam('origin'))
                 ->setHeader('Access-Control-Allow-Credentials', 'true')
                 ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
                 ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept');

            $this->_helper->json(array('status' => 'ok', 'message' => 'Login successful.'));
        } else {
            Zend_Registry::get('logger')->info(__METHOD__
                                               . PHP_EOL . ' - authentication fail: '
                                               . PHP_EOL . ' - host: ' . Zend_Registry::get('store_host')
                                               . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
                                               . PHP_EOL . print_r($authResult->getMessages(), true)
            );
            $this->_helper->json(array('status' => 'fail', 'message' => 'Login failed.'));
        }
    }

    public function checkuserAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $this->getResponse()->setHeader('Access-Control-Allow-Origin', 'https://gitlab.pling.cc')
             ->setHeader('Access-Control-Allow-Credentials', 'true')->setHeader('Access-Control-Allow-Methods',
                'POST, GET, OPTIONS')
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept');

        $formLogin = new Default_Form_Login();

        if (false === $formLogin->isValid($_GET)) { // form not valid
            $this->_helper->json(array('status' => 'error', 'message' => 'not valid'));

            return;
        }

        $values = $formLogin->getValues();
        $authModel = new Default_Model_Authorization();
        $authResult = $authModel->authenticateUser($values['mail'], $values['password'], $values['remember_me']);

        if (false == $authResult->isValid()) { // authentication fail
            $this->_helper->json(array('status' => 'error', 'message' => 'not valid'));

            return;
        }

        $auth = Zend_Auth::getInstance();
        $userId = $auth->getStorage()->read()->member_id;


        //Send user to LDAP
        try {
            $ldap_server = new Default_Model_Ocs_Ldap();
            $ldap_server->createUser($userId);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - ldap : ' . implode(PHP_EOL . " - ",
                    $ldap_server->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        //If the user is a hive user, we have to update his password
        $this->changePasswordIfNeeded($userId, $values['password']);

        $this->_helper->json(array('status' => 'ok', 'message' => 'User is OK.'));
    }

    /**
     * @param int $member_id
     * @param string $password
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    private function changePasswordIfNeeded($member_id, $password)
    {
        $userTabel = new Default_Model_Member();
        $showMember = $userTabel->fetchMember($member_id);
        $memberSettings = $showMember;

        //User with OCS Password
        if ($showMember->password_type == Default_Model_Member::PASSWORD_TYPE_OCS) {
            return;
        }

        //Hive User
        if ($memberSettings->password_type == Default_Model_Member::PASSWORD_TYPE_HIVE) {
            //Save old data
            $memberSettings->password_old = $memberSettings->password;
            $memberSettings->password_type_old = Default_Model_Member::PASSWORD_TYPE_HIVE;

            //Change type and password
            $memberSettings->password_type = Default_Model_Member::PASSWORD_TYPE_OCS;
            $memberSettings->password = Local_Auth_Adapter_Ocs::getEncryptedPassword($password, Default_Model_Member::PASSWORD_TYPE_OCS);
            $memberSettings->save();

            //Update Auth-Services
            try {
                $id_server = new Default_Model_Ocs_OAuth();
                $id_server->updatePasswordForUser($memberSettings->member_id);
                $messages = $id_server->getMessages();
                if (false == empty($messages)) {
                    Zend_Registry::get('logger')->info(json_encode($messages));
                }
            } catch (Exception $e) {
                Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            try {
                $ldap_server = new Default_Model_Ocs_Ldap();
                $ldap_server->updatePassword($memberSettings->member_id,$password);
                $messages = $ldap_server->getMessages();
                if (false == empty($messages)) {
                    Zend_Registry::get('logger')->info(json_encode($messages));
                }
            } catch (Exception $e) {
                Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        }

        return;
    }

    /**
     * @throws Zend_Auth_Storage_Exception
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     * @throws Zend_Session_Exception
     * @throws exception
     */
    public function loginAction()
    {
        $this->view->noheader = true;
        //TODO: check redirect for a local valid url.
        $this->view->redirect = $this->getParam('redirect');

        // if the user is still logged in, we do not show the login page. They should log out first.
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->flashMessenger->addMessage('<p class="text-danger center">You are still logged in. Please click <a href="/logout" class="bold">here</a> to log out first.</p>');
            $this->handleRedirect(Zend_Auth::getInstance()->getIdentity()->member_id);
        }

        $formLogin = new Default_Form_Login();

        if ($this->_request->isGet()) { // not a POST request
            $this->view->formLogin = $formLogin->populate(array('redirect' => $this->view->redirect));
            $this->view->error = 0;

            return;
        }

        Zend_Registry::get('logger')->info(__METHOD__
                                           . PHP_EOL . ' - authentication attempt on host: ' . Zend_Registry::get('store_host')
                                           . PHP_EOL . ' - param redirect: ' . $this->getParam('redirect')
                                           . PHP_EOL . ' - from ip: ' . $this->_request->getClientIp()
                                           . PHP_EOL . ' - http method: ' . $this->_request->getMethod()
                                           . PHP_EOL . ' - csrf string: ' . (isset($_POST['login_csrf']) ? $_POST['login_csrf'] : '')
        );

        if (false === Default_Model_CsrfProtection::validateCsrfToken($_POST['login_csrf'])) {
            Zend_Registry::get('logger')->info(__METHOD__
                                               . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
                                               . PHP_EOL . ' - validate CSRF token failed:'
                                               . PHP_EOL . ' - received token: ' . $_POST['login_csrf']
                                               . PHP_EOL . ' - stored token: ' . Default_Model_CsrfProtection::getCsrfToken()
            );

            $this->view->error = 0;
            $this->view->formLogin = $formLogin;
            if ($this->_request->isXmlHttpRequest()) {
                $viewLoginForm = $this->view->render('authorization/partials/loginForm.phtml');
                $this->_helper->json(array('status' => 'ok', 'message' => $viewLoginForm));
            }

            return;
        }

        if (false === $formLogin->isValid($_POST)) { // form not valid
            Zend_Registry::get('logger')->info(__METHOD__
                                               . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
                                               . PHP_EOL . ' - form not valid:'
                                               . PHP_EOL . print_r($formLogin->getMessages(), true)
            );
            $this->view->formLogin = $formLogin;
            $this->view->errorText = 'index.login.error.auth';
            $this->view->error = 1;

            if ($this->_request->isXmlHttpRequest()) {
                $viewLoginForm = $this->view->render('authorization/partials/loginForm.phtml');
                $this->_helper->json(array('status' => 'ok', 'message' => $viewLoginForm));
            }

            return;
        }

        $values = $formLogin->getValues();
        $authModel = new Default_Model_Authorization();
        $authResult = $authModel->authenticateUser($values['mail'], $values['password'], $values['remember_me']);

        if (false == $authResult->isValid()) { // authentication fail
            Zend_Registry::get('logger')->info(__METHOD__
                                               . PHP_EOL . ' - authentication fail.'
                                               . PHP_EOL . ' - user: ' . $values['mail']
                                               . PHP_EOL . ' - remember_me: ' . $values['remember_me']
                                               . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
                                               . PHP_EOL . print_r($authResult->getMessages(), true)
            );

            if ($authResult->getCode() == Local_Auth_Result::MAIL_ADDRESS_NOT_VALIDATED) {
                $session = new Zend_Session_Namespace();
                $session->mail_verify_member_id = $authResult->getIdentity();

                if ($this->_request->isXmlHttpRequest()) {
                    $viewMessage = $this->view->render('verify/resend.phtml');
                    $this->_helper->json(array('status' => 'ok', 'message' => $viewMessage));
                }
            }

            $this->view->errorText = 'index.login.error.auth';
            $this->view->formLogin = $formLogin;
            $this->view->error = 1;

            if ($this->_request->isXmlHttpRequest()) {
                $viewLoginForm = $this->view->render('authorization/partials/loginForm.phtml');
                $this->_helper->json(array('status' => 'ok', 'message' => $viewLoginForm));
            }

            return;
        }

        Zend_Registry::get('logger')->info(__METHOD__
                                           . PHP_EOL . ' - authentication successful.'
                                           . PHP_EOL . ' - user: ' . $values['mail']
                                           . PHP_EOL . ' - user_id: ' . isset(Zend_Auth::getInstance()->getStorage()->read()->member_id) ? Zend_Auth::getInstance()->getStorage()->read()->member_id : ''
                                                                                                                                                                                                       . PHP_EOL . ' - remember_me: ' . $values['remember_me']
                                                                                                                                                                                                       . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
        );


        $filter = new Local_Filter_Url_Encrypt();
        $p = $filter->filter($values['password']);
        $sess = new Zend_Session_Namespace('ocs_meta');
        $sess->phash = $p;

        $auth = Zend_Auth::getInstance();
        $userId = $auth->getStorage()->read()->member_id;

        $jwt = Default_Model_Jwt::encode($userId);
        $sess->openid = $jwt;

        //If the user is a hive user, we have to update his password
        $this->changePasswordIfNeeded($userId, $values['password']);

        //log login
        try {
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']) : $_SERVER['REMOTE_ADDR'];

            if (is_array($ip)) {
                $ip = $ip[0];
            }

            $agent = null;
            if ( isset( $_SERVER ) ) {
                $agent = $_SERVER['HTTP_USER_AGENT'];
            }
            
            Zend_Registry::get('logger')->info(__METHOD__ . ' - USER_AGENT: ' . print_r($agent, true));

            $fingerprint = null;
            
            $session = new Zend_Session_Namespace();
            $fp = $session->client_fp;
            if (!empty($fp)) {
                $fingerprint = $fp;
            }
            
            $ipv4 = null;
            $ipv6 = null;
            
            $client_ipv4 = $session->client_ipv4;
            if (!empty($client_ipv4)) {
                $ipv4 = $client_ipv4;
            }
            $client_ipv6 = $session->client_ipv6;
            if (!empty($client_ipv6)) {
                $ipv6 = $client_ipv6;
            }

            $loginHistory = new Default_Model_LoginHistory();
            $loginHistory->log($userId, $ip, $ipv4, $ipv6, $agent, $fingerprint);
        } catch (Exception $exc) {
        }

        
        
        //$modelToken = new Default_Model_SingleSignOnToken();
        //$data = array(
        //    'remember_me' => $values['remember_me'],
        //    //'redirect'    => $this->getParam('redirect'),
        //    'redirect'    => $this->view->redirect,
        //    'action'      => Default_Model_SingleSignOnToken::ACTION_LOGIN,
        //    'member_id'   => $userId
        //);
        //$token_id = $modelToken->createToken($data);
        //setcookie(Default_Model_SingleSignOnToken::ACTION_LOGIN, $token_id, time() + 120, '/',
        //    Local_Tools_ParseDomain::get_domain($this->getRequest()->getHttpHost()), null, true);

        //user has to correct his data?
        $modelReviewProfile = new Default_Model_ReviewProfileData();
        if (false === $modelReviewProfile->hasValidProfile($auth->getStorage()->read())) {
            Zend_Registry::get('logger')->info(__METHOD__
                                               . PHP_EOL . ' - User has to change user data!'
                                               . PHP_EOL . ' - error code: ' . print_r($modelReviewProfile->getErrorCode(),
                    true)
            );

            if ($this->_request->isXmlHttpRequest()) {
                $redirect = $this->getParam('redirect') ? '/redirect/' . $this->getParam('redirect') : '';
                $this->_helper->json(array(
                    'status'   => 'ok',
                    'redirect' => '/r/change/e/' . $modelReviewProfile->getErrorCode() . $redirect
                ));
            } else {
                $this->getRequest()->setParam('member_id', $userId);
                $this->redirect("/r/change/e/" . $modelReviewProfile->getErrorCode(), $this->getAllParams());
            }

            return;
        }

        // handle redirect
        $this->view->loginok = true;
        $this->handleRedirect($userId);
    }

    /**
     * @param int $userId
     *
     * @throws Zend_Exception
     * @throws Zend_Filter_Exception
     */
    protected function handleRedirect($userId)
    {
        if (empty($this->view->redirect)) {

            Zend_Registry::get('logger')->info(__METHOD__ . PHP_EOL . ' - user_id: ' . $userId . PHP_EOL . ' - redirect: empty');

            if ($this->_request->isXmlHttpRequest()) {
                $redirect_url = $this->encodeString('/member/' . $userId . '/activities/');
                $redirect = '/home/redirectme?redirect=' . $redirect_url;
                $this->_helper->json(array('status' => 'ok', 'redirect' => $redirect));

                return;
            }

            $this->getRequest()->setParam('member_id', $userId);
            $redirect_url = $this->encodeString('/member/' . $userId . '/activities/');
            $redirect = '/home/redirectme?redirect=' . $redirect_url;
            $this->redirect($redirect, $this->getAllParams());

            return;
        }

        $redirect = $this->decodeString($this->view->redirect);
        Zend_Registry::get('logger')->info(__METHOD__ . PHP_EOL . ' - user_id: ' . $userId . PHP_EOL . ' - redirect: ' . $redirect);
        if (false !== strpos('/register', $redirect)) {
            $redirect = '/member/' . $userId . '/activities/';
        }

        $redirect = '/home/redirectme?redirect=' . $this->encodeString($redirect);
        if ($this->_request->isXmlHttpRequest()) {

            $this->_helper->json(array('status' => 'ok', 'redirect' => $redirect));

            return;
        }

        $this->redirect($redirect);

        return;
    }

    /**
     * @param string $string
     *
     * @return string
     * @throws Zend_Filter_Exception
     */
    protected function encodeString($string)
    {
        $encodeFilter = new Local_Filter_Url_Encrypt();

        return $encodeFilter->filter($string);
    }

    /**
     * @throws Exception
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     */
    public function registerAction()
    {
        $this->view->redirect = $this->getParam('redirect');

        $this->view->headScript()->appendFile('//www.google.com/recaptcha/api.js');
        $this->view->addHelperPath(APPLICATION_LIB . '/Cgsmith/View/Helper', 'Cgsmith\\View\\Helper\\');
        $formRegister = new Default_Form_Register();

        if ($this->_request->isGet()) {
            $this->view->formRegister = $formRegister->populate(array('redirect' => urlencode($this->view->redirect)));
            $this->view->error = 0;

            return;
        }

        if (false === $formRegister->isValid($_POST)) {

            $this->view->formRegister = $formRegister;
            $this->view->error = 1;

            if ($this->_request->isXmlHttpRequest()) {
                $viewRegisterForm = $this->view->render('authorization/partials/registerForm.phtml');
                $this->_helper->json(array('status' => 'ok', 'message' => $viewRegisterForm));
            }

            return;
        }

        $formRegisterValues = $formRegister->getValues();
        unset($formRegisterValues['g-recaptcha-response']);
        $formRegisterValues['password'] = $formRegisterValues['password1'];

        $formRegisterValues['username'] = Default_Model_HtmlPurify::purify($formRegisterValues['username']);
        $formRegisterValues['mail'] = strtolower($formRegisterValues['mail']);

        $doubleOptIn = (boolean)Zend_Registry::get('config')->settings->double_opt_in->active;
        $newUserData = $this->createNewUser($formRegisterValues, $doubleOptIn);

        Default_Model_ActivityLog::logActivity($newUserData['main_project_id'], null, $newUserData['member_id'],
            Default_Model_ActivityLog::MEMBER_JOINED, array());

        if ($doubleOptIn) {
            $this->sendConfirmationMail($formRegisterValues, $newUserData['verificationVal']);
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - member_id: ' . $newUserData['member_id'] . ' - Link for verification: '
                                            . 'http://' . $this->getServerName() . '/verification/' . $newUserData['verificationVal']);

        if ($this->_request->isXmlHttpRequest()) {
            $viewRegisterForm = $this->view->render('authorization/partials/registerSuccess.phtml');
            $this->_helper->json(array('status' => 'ok', 'message' => $viewRegisterForm));
        } else {
            $this->view->overlay = $this->view->render('authorization/registerSuccess.phtml');
            $this->forward('index', 'explore', 'default');
        }
    }

    /**
     * @param array $userData
     *
     * @return array
     * @throws Exception
     */
    protected function createNewUser($userData, $doubleOptIn = true)
    {
        if (false === $doubleOptIn) {
            $userData['mail_checked'] = 1;
            $userData['is_active'] = 1;
            $userData['is_deleted'] = 0;
        }
        $userTable = new Default_Model_Member();
        $userData = $userTable->createNewUser($userData);

        return $userData;
    }

    /**
     * @param array  $val
     * @param string $verificationVal
     */
    protected function sendConfirmationMail($val, $verificationVal)
    {
        $confirmMail = new Default_Plugin_SendMail('tpl_verify_user');
        $confirmMail->setTemplateVar('servername', $this->getServerName());
        $confirmMail->setTemplateVar('username', $val['username']);
        $confirmMail->setTemplateVar('verificationlinktext',
            '<a href="https://' . $this->getServerName() . '/verification/' . $verificationVal
            . '">Click here to verify your email address</a>');
        $confirmMail->setTemplateVar('verificationlink',
            '<a href="https://' . $this->getServerName() . '/verification/' . $verificationVal . '">https://' . $this->getServerName()
            . '/verification/' . $verificationVal . '</a>');
        $confirmMail->setTemplateVar('verificationurl',
            'https://' . $this->getServerName() . '/verification/' . $verificationVal);
        $confirmMail->setReceiverMail($val['mail']);
        $confirmMail->setFromMail('registration@opendesktop.org');
        $confirmMail->send();
    }

    /**
     * @return mixed
     */
    protected function getServerName()
    {
        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        return $request->getHttpHost();
    }

    /**
     * @throws Zend_Controller_Action_Exception
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     */
    public function propagatelogoutAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if (false == Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->json(array('status' => 'ok', 'message' => 'Already logged out.'));
        }

        $modelAuth = new Default_Model_Authorization();
        $modelAuth->logout();

        $this->_helper->json(array('status' => 'ok', 'message' => 'Logout successful.'));
    }

    /**
     * @throws Zend_Cache_Exception
     * @throws Zend_Controller_Action_Exception
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     */
    public function logoutAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $modelAuth = new Default_Model_Authorization();
            $modelAuth->logout();

            $modelToken = new Default_Model_SingleSignOnToken();
            $data = array(
                'remember_me' => false,
                'redirect'    => $this->getParam('redirect'),
                'action'      => Default_Model_SingleSignOnToken::ACTION_LOGOUT
            );
            $token_id = $modelToken->createToken($data);
            setcookie(Default_Model_SingleSignOnToken::ACTION_LOGOUT, $token_id, time() + 120, '/',
                Local_Tools_ParseDomain::get_domain($this->getRequest()->getHttpHost()), null, true);
        }

        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->json(array('status' => 'ok', 'message' => 'Logout successful.'));
        } else {
            $this->redirect('/');
        }
    }

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->getResponse()->clearHeaders(array('Expires', 'Pragma', 'Cache-Control'))->setHeader('Pragma', 'no-cache',
            true)
             ->setHeader('Cache-Control', 'private, no-cache, must-revalidate', true);
    }

    /**
     * @throws Zend_Auth_Storage_Exception
     * @throws Zend_Controller_Action_Exception
     * @throws Zend_Exception
     * @throws exception
     */
    public function verificationAction()
    {
        $filterInput = new Zend_Filter_Input(array('*' => 'StringTrim', 'vid' => 'Alnum'),
            array('vid' => array('Alnum', 'presence' => 'required')), $this->getAllParams());
        $_vId = $filterInput->getEscaped('vid');

        if (!$_vId) {
            $this->redirect('/');
        }

        $translate = Zend_Registry::get('Zend_Translate');
        $this->view->title = $translate->_('member.email.verification.title');

        $authModel = new Default_Model_Authorization();
        $authUser = $authModel->getAuthUserDataFromUnverified($_vId);

        if (empty($authUser)) {
            throw new Zend_Controller_Action_Exception('This member account could not activated. verification id:'
                                                       . print_r($this->getParam('vid'), true));
        }

        if ($authUser AND (false == empty($authUser->email_checked))) {
            $this->view->headScript()->appendFile('//www.google.com/recaptcha/api.js');
            $this->view->addHelperPath(APPLICATION_LIB . '/Cgsmith/View/Helper', 'Cgsmith\\View\\Helper\\');
            $this->view->formRegister = new Default_Form_Register();
            $this->view->registerErrMsg =
                "<p>Your account has already been activated.</p><p class='small'><a href='/login'>Log in</a> or try to generate a <a href='/login/forgot'>new password</a> for your account. </p> ";
            $this->view->overlay = $this->view->render('authorization/registerError.phtml');
            $this->_helper->viewRenderer('register');

            return;
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - activate user from email link. (member_id, username): ('
                                           . print_r($authUser->member_id, true) . ', ' . print_r($authUser->username,
                true) . ')');
        $modelMember = new Default_Model_Member();
        $result = $modelMember->activateMemberFromVerification($authUser->member_id, $_vId);

        if (false == $result) {
            throw new Zend_Controller_Action_Exception('Your member account could not activated.');
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - user activated. member_id: ' . print_r($authUser->member_id,
                true));

        $modelMember = new  Default_Model_Member();
        $record = $modelMember->fetchMemberData($authUser->member_id, false);

        try {
            $oauth = new Default_Model_Ocs_OAuth();
            $oauth->createUserFromArray($record->toArray());
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - oauth : ' . implode(PHP_EOL . " - ",
                    $oauth->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $ldap = new Default_Model_Ocs_Ldap();
            $ldap->addUserFromArray($record->toArray());
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - ldap : ' . implode(PHP_EOL . " - ",
                    $ldap->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $openCode = new Default_Model_Ocs_Gitlab();
            $openCode->createUserFromArray($record->toArray());
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - opencode : ' . implode(PHP_EOL . " - ",
                    $openCode->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $forum = new Default_Model_Ocs_Forum();
            $forum->createUserFromArray($record->toArray());
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - forum : ' . implode(PHP_EOL . " - ",
                    $forum->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }


        Default_Model_ActivityLog::logActivity($authUser->member_id, null, $authUser->member_id,
            Default_Model_ActivityLog::MEMBER_EMAIL_CONFIRMED, array());
        $this->view->member = $authUser;
        $this->view->username = $authUser->username;

        $this->view->headScript()->appendFile('//www.google.com/recaptcha/api.js');
        $this->view->addHelperPath(APPLICATION_LIB . '/Cgsmith/View/Helper', 'Cgsmith\\View\\Helper\\');
        $this->view->form = new Default_Form_Register();
        $this->view->overlay = $this->view->render('authorization/registerWelcome.phtml');

        $this->storeAuthSessionData($authUser->member_id);

        $tableProduct = new Default_Model_Project();
        $this->view->products = $tableProduct->fetchAllProjectsForMember($authUser->member_id);

        $this->forward('index', 'settings', 'default', array('member_id' => $authUser->member_id));
    }

    /**
     * @param string|int $identity
     *
     * @throws Zend_Auth_Storage_Exception
     * @throws exception
     */
    protected function storeAuthSessionData($identity)
    {
        $authDataModel = new Default_Model_Authorization();
        $authDataModel->storeAuthSessionDataByIdentity($identity);
    }

    /**
     * ppload
     */
    public function pploadloginAction()
    {
        $this->_helper->layout()->disableLayout();

        // Init identity and credential
        $identity = null;
        $credential = null;
        if (!empty($_REQUEST['identity'])) {
            $identity = $_REQUEST['identity'];
        } else {
            if (!empty($_SERVER['PHP_AUTH_USER'])) {
                $identity = $_SERVER['PHP_AUTH_USER'];
            }
        }
        if (!empty($_REQUEST['credential'])) {
            $credential = $_REQUEST['credential'];
        } else {
            if (!empty($_SERVER['PHP_AUTH_PW'])) {
                $credential = $_SERVER['PHP_AUTH_PW'];
            }
        }

        // Authenticate and get user data
        $response = array('status' => 'error');
        if ($identity && $credential) {
            $authModel = new Default_Model_Authorization();
            $authData = $authModel->getAuthDataFromApi($identity, $credential);
            if (!empty($authData->member_id)) {
                $response = array(
                    'status'    => 'ok',
                    'member_id' => $authData->member_id
                );
            }
        }

        $this->_helper->json($response);
    }

    public function htmlloginAction()
    {
        $this->_helper->layout()->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);
    }

    public function validateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $this->view->headScript()->appendFile('//www.google.com/recaptcha/api.js');
        $this->view->addHelperPath(APPLICATION_LIB . '/Cgsmith/View/Helper', 'Cgsmith\\View\\Helper\\');
        $formRegister = new Default_Form_Register();

        $name = $this->getParam('name');
        $value = $this->getParam('value');

        $result = $formRegister->getElement($name)->isValid($value);

        $this->_helper->json(array('status' => $result, $name => $formRegister->getElement($name)->getMessages()));
    }

    /**
     * @param array $val
     *
     * @throws Zend_Exception
     */
    protected function sendAdminNotificationMail($val)
    {
        $config = Zend_Registry::get('config');
        $receiver = $config->admin->email;
        $oNotificationMail = new Default_Plugin_SendMail('tpl_newuser_notification');
        $oNotificationMail->setReceiverMail($receiver);
        $oNotificationMail->setTemplateVar('username', $val['username']);
        $oNotificationMail->send();
    }

    /**
     * @param array $userData
     *
     * @return int
     */
    protected function storeNewUser($userData)
    {
        $userTable = new Default_Model_Member();
        $userData = $userTable->storeNewUser($userData);

        return $userData->member_id;
    }

    /**
     * @param int $identity
     */
    protected function updateUsersLastOnline($identity)
    {
        $authModel = new Default_Model_Authorization();
        $authModel->updateUserLastOnline('member_id', $identity);
    }

}