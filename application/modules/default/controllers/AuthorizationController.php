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

    public function checkuserAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', 'https://gitlab.pling.cc')
             ->setHeader('Access-Control-Allow-Credentials', 'true')
             ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
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

        $auth = Default_Model_Auth_User::getInstance();
        $userId = $auth->getIdentity()->member_id;


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
            $memberSettings->password = Local_Auth_Adapter_Ocs::getEncryptedPassword($password,
                $memberSettings->password_type);
            $memberSettings->save();

            //Update Auth-Services
            try {
                $id_server = new Default_Model_Ocs_OAuth();
                $id_server->updatePasswordForUser($memberSettings->member_id);
            } catch (Exception $e) {
                Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            try {
                $ldap_server = new Default_Model_Ocs_Ldap();
                $ldap_server->updatePassword($memberSettings->member_id);
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
        //TODO: check redirect for a local valid url.
        $this->view->redirect = $this->getParam('redirect');

        // if the user is still logged in, we do not show the login page. They should log out first.
        if (Default_Model_Auth_User::getInstance()->hasIdentity()) {
            $this->_helper->flashMessenger->addMessage('<p class="text-danger center">You are still logged in. Please click <a href="/logout" class="bold">here</a> to log out first.</p>');
            $this->handleRedirect(Default_Model_Auth_User::getInstance()->getIdentity()->member_id);
        }

        $formLogin = new Default_Form_Login();

        if ($this->_request->isGet()) {
            $this->view->formLogin = $formLogin->populate(array('redirect' => $this->view->redirect));
            $this->view->error = 0;

            return;
        }

        Zend_Registry::get('logger')->info(__METHOD__
                                           . PHP_EOL . ' - authentication attempt on host: ' . Zend_Registry::get('store_host')
                                           . PHP_EOL . ' - param redirect: ' . $this->getParam('redirect')
                                           . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
                                           . PHP_EOL . ' - http method: ' . $this->_request->getMethod()
                                           . PHP_EOL . ' - received csrf : ' . (isset($_POST['login_csrf']) ? $_POST['login_csrf'] : '')
                                           . PHP_EOL . ' - stored csrf: ' . Default_Model_CsrfProtection::getCsrfToken()
        );

        if (false === Default_Model_CsrfProtection::validateCsrfToken($_POST['login_csrf'])) {
            Zend_Registry::get('logger')->info(__METHOD__
                                               . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
                                               . PHP_EOL . ' - validate CSRF token failed.'
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
                                               . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
                                               . PHP_EOL . ' - authentication fail.'
                                               . PHP_EOL . ' - user: ' . $values['mail']
                                               . PHP_EOL . ' - remember_me: ' . $values['remember_me']
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
                                           . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
                                           . PHP_EOL . ' - user: ' . $values['mail']
                                           . PHP_EOL . ' - user_id: ' . isset(Default_Model_Auth_User::getInstance()->getIdentity()->member_id) ? Default_Model_Auth_User::getInstance()->getIdentity()->member_id : ''
                                                                                                                                                                                                                     . PHP_EOL . ' - remember_me: ' . $values['remember_me']
                                                                                                                                                                                                                     . PHP_EOL . ' - ip: ' . $this->_request->getClientIp()
        );

        /** @var Default_Model_Auth_User $auth */
        $auth = $authModel->getAuthUser();
        $config_session = Zend_Registry::get('config')->settings->session->cookie->toArray();
        if (APPLICATION_ENV == 'development') {
            $config_session['secure'] = false;
        }
        $auth->startSession($config_session);

//        $filter = new Local_Filter_Url_Encrypt();
//        $p = $filter->filter($values['password']);
//
//        $sess = new Zend_Session_Namespace('ocs_meta');
//        $sess->phash = $p;
//
//        $userId = $auth->member_id;
//
//        $jwt = Default_Model_Jwt::encode($userId);
//        $sess->openid = $jwt;

        //If the user is a hive user, we have to update his password
        $this->changePasswordIfNeeded($auth->member_id, $values['password']);


        //user has to correct his data?
        $modelReviewProfile = new Default_Model_ReviewProfileData();
        if (false === $modelReviewProfile->hasValidProfile($auth)) {
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
                $this->getRequest()->setParam('member_id', $auth->member_id);
                $this->redirect("/r/change/e/" . $modelReviewProfile->getErrorCode(), $this->getAllParams());
            }

            return;
        }

        // handle redirect
        $this->view->loginok = true;
        $this->handleRedirect($auth->member_id);
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

                return;
            }

            return;
        }

        $formRegisterValues = $formRegister->getValues();
        unset($formRegisterValues['g-recaptcha-response']);
        $formRegisterValues['password'] = $formRegisterValues['password1'];

        $formRegisterValues['username'] = Default_Model_HtmlPurify::purify($formRegisterValues['username']);
        $formRegisterValues['mail'] = strtolower($formRegisterValues['mail']);

        $newUserData = $this->createNewUser($formRegisterValues);

        Default_Model_ActivityLog::logActivity($newUserData['main_project_id'], null, $newUserData['member_id'],
            Default_Model_ActivityLog::MEMBER_JOINED, array());

        $this->sendConfirmationMail($formRegisterValues, $newUserData['verificationVal']);

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
    protected function createNewUser($userData)
    {
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
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     */
    public function logoutAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if (Default_Model_Auth_User::getInstance()->hasIdentity()) {
            $modelAuth = new Default_Model_Authorization();
            $modelAuth->logout();
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
            $ldap->createUserFromArray($record->toArray());
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