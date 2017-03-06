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
        $this->forward('login', 'oauth', 'default', array('provider' => 'github', 'redirect' => $this->getParam('redirect')));
    }

    public function redirectAction()
    {
        $param = null;
        if (preg_match("/redirect\/(.*?)$/i", $this->getRequest()->getRequestUri(), $result)) {
            $param = array('redirect' => $result[1]);
        }
        $this->forward('login', null, null, $param);
    }

    public function forgotAction()
    {
        $formForgot = new Default_Form_Forgot();

        if ($this->_request->isGet()) { // not a POST request
            $this->view->form = $formForgot;
            $this->view->error = 0;

            return;
        }

        if (false === $formForgot->isValid($_POST)) { // form not valid
            $this->view->form = $formForgot;

            if ($this->_request->isXmlHttpRequest()) {
                $viewFormForgot = $this->view->render('authorization/partials/forgot-form.phtml');
                $this->_helper->json(array('status' => 'ok', 'message' => $viewFormForgot));
            }

            return;
        }

        $userTable = new Default_Model_Member();

        $emailAddress = $formForgot->getValue('mail');
        Zend_Registry::get('logger')->info(__METHOD__ . ' - ' . print_r($emailAddress, true));

        $user = $userTable->fetchCheckedActiveLocalMemberByEmail($emailAddress);

        if ($user) {
            $oldPasswordHash = $user->password;
            $newPass = $this->generateNewPassword();
            $newPasswordHash = $this->storeNewPassword($newPass, $user);

            Zend_Registry::get('logger')->info(__METHOD__ . ' - old password hash: ' . $oldPasswordHash . ', new password hash: ' . $newPasswordHash);

            $this->sendNewPassword($user, $newPass);

            $this->view->form = $formForgot;
            $this->view->text = $this->view->translate("index.forget.new_password");

            if ($this->_request->isXmlHttpRequest()) {
                $viewFormForgot = $this->view->render('authorization/partials/forgotSuccess.phtml');
                $this->_helper->json(array('status' => 'ok', 'message' => $viewFormForgot));
            } else {
                $this->_helper->viewRenderer('forgotSuccess');
            }
        } else {
            if ($this->_request->isXmlHttpRequest()) {
                $viewFormForgot = $this->view->render('authorization/partials/forgotSuccess.phtml');
                $this->_helper->json(array('status' => 'ok', 'message' => $viewFormForgot));
            } else {
                $this->_helper->viewRenderer('forgotSuccess');
            }
        }
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
     * @param string $newPass
     * @param Zend_Db_Table_Row_Abstract $user
     * @return string return new password hash
     */
    protected function storeNewPassword($newPass, $user)
    {
        $user->password = Local_Auth_Adapter_Ocs::getEncryptedPassword($newPass, $user->source_id);
        $user->changed_at = new Zend_Db_Expr('Now()');
        $user->save();
        return $user->password;
    }

    /**
     * @param $user
     * @param $newPass
     */
    protected function sendNewPassword($user, $newPass)
    {
        $newPasMail = new Default_Plugin_SendMail('tpl_user_newpass');
        $newPasMail->setReceiverMail($user->mail);
        $newPasMail->setReceiverAlias($user->firstname . " " . $user->lastname);
        $newPasMail->setTemplateVar('username', $user->username);
        $newPasMail->setTemplateVar('newpass', $newPass);

        $newPasMail->send();
    }

    /**
     * login from cookie
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

        if (false === $formLogin->isValid($_POST)) { // form not valid
            $this->view->form = $formLogin;
            $this->view->errorText = 'index.login.error.auth';
            $this->view->error = 1;
            return;
        }

        $values = $formLogin->getValues();
        $authModel = new Default_Model_Authorization();
        $authResult = $authModel->authenticateUser($values['mail'], $values['password'], $values['remember_me']);

        if (false == $authResult->isValid()) { // authentication fail
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

    protected function decodeString($string)
    {
        $decodeFilter = new Local_Filter_Url_Decrypt();
        return $decodeFilter->filter($string);
    }

    public function propagateAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->json(array('status' => 'ok', 'message' => 'Already logged in.'));
        }

        $modelAuthToken = new Default_Model_SingleSignOnToken();
        $token_data = $modelAuthToken->getValidTokenData($this->getParam('token'), Default_Model_SingleSignOnToken::ACTION_LOGIN);
        $remember_me = isset($token_data['remember_me']) ? $token_data['remember_me'] : false;

        $modelAuth = new Default_Model_Authorization();
        $authResult = $modelAuth->authenticateUser($this->getParam('token'), null, $remember_me, Local_Auth_AdapterFactory::LOGIN_SSO);

        if ($authResult->isValid()) {
            $this->getResponse()
                ->setHeader('Access-Control-Allow-Origin', $this->getParam('origin'))
                ->setHeader('Access-Control-Allow-Credentials', 'true')
                ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
                ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept')
            ;

            $this->_helper->json(array('status' => 'ok', 'message' => 'Login successful.'));
        } else {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - Login failed: '. print_r($authResult->getMessages(), true));
            $this->_helper->json(array('status' => 'fail', 'message' => 'Login failed.'));
        }
    }

    public function loginAction()
    {
        //TODO: check redirect for a local valid url.
        $this->view->redirect = $this->getParam('redirect');

        // if the user is still logged in, we do not show the login page. They should log out first.
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->flashMessenger->addMessage('<p class="text-danger center">You are still logged in. Please click <a href="/logout" class="bold">here</a> to log out first.</p>');
            //$this->forward('news', 'user', null, $this->getAllParams());
            $this->handleRedirect(Zend_Auth::getInstance()->getIdentity()->member_id);
        }

        $formLogin = new Default_Form_Login();
        //Default_Model_CsrfProtection::createCSRF($formLogin, 'login', 'csrfLogin');

        if ($this->_request->isGet()) { // not a POST request
            $this->view->formLogin = $formLogin->populate(array('redirect' => $this->view->redirect));
            $this->view->error = 0;

            return;
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - authentication on host: ' . Zend_Registry::get('store_host'));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - param redirect: ' . $this->getParam('redirect'));

        if (false === $formLogin->isValid($_POST)) { // form not valid
            Zend_Registry::get('logger')->info(__METHOD__ . ' - form not valid:' . print_r($formLogin->getMessages(), true));
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
            Zend_Registry::get('logger')->info(__METHOD__ . ' - authentication failed.');

            $this->view->errorText = 'index.login.error.auth';
            $this->view->formLogin = $formLogin;
            $this->view->error = 1;

            if ($this->_request->isXmlHttpRequest()) {
                $viewLoginForm = $this->view->render('authorization/partials/loginForm.phtml');
                $this->_helper->json(array('status' => 'ok', 'message' => $viewLoginForm));
            }

            return;
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - authentication successful.');
        Zend_Registry::get('logger')->info(__METHOD__ . ' - auth_user: ' . print_r($values['mail'], true));

        $auth = Zend_Auth::getInstance();
        $userId = $auth->getStorage()->read()->member_id;

        $modelToken = new Default_Model_SingleSignOnToken();
        $token = $modelToken->createAuthToken($userId, $values['remember_me'], Default_Model_SingleSignOnToken::ACTION_LOGIN);

        setcookie(Default_Model_SingleSignOnToken::ACTION_LOGIN, $token, time() + 120, '/',Local_Tools_ParseDomain::get_domain($this->getRequest()->getHttpHost()), null, true);

        // handle redirect
        $this->handleRedirect($userId);
    }

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

        $newUserData = $this->createNewUser($formRegisterValues);

        $this->sendConfirmationMail($formRegisterValues, $newUserData['verificationVal']);

        $this->sendAdminNotificationMail($formRegisterValues);

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
     * @return array
     */
    protected function createNewUser($userData)
    {
        $userTable = new Default_Model_Member();
        $userData = $userTable->createNewUser($userData)->toArray();

        if (false == isset($userData['verificationVal'])) {
            $verificationVal = Default_Model_MemberEmail::getVerificationValue($userData['username'],
                $userData['mail']);
            $userData['verificationVal'] = $verificationVal;
        }

        $modelEmail = new Default_Model_MemberEmail();
        $userEmail = $modelEmail->saveEmailAsPrimary($userData['member_id'], $userData['mail'], $userData['verificationVal']);

        return $userData;
    }

    /**
     * @param array $val
     * @param string $verificationVal
     */
    protected function sendConfirmationMail($val, $verificationVal)
    {
        $confirmMail = new Default_Plugin_SendMail('tpl_verify_user');
        $confirmMail->setTemplateVar('servername', $this->getServerName());
        $confirmMail->setTemplateVar('username', $val['username']);
        $confirmMail->setTemplateVar('verificationlinktext',
            '<a href="https://' . $this->getServerName() . '/verification/' . $verificationVal . '">Click here to verify your email address</a>');
        $confirmMail->setTemplateVar('verificationlink',
            '<a href="https://' . $this->getServerName() . '/verification/' . $verificationVal . '">https://' . $this->getServerName() . '/verification/' . $verificationVal . '</a>');
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
     * @param array $val
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

    public function logoutAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $member_id = Zend_Auth::getInstance()->getIdentity()->member_id;
            $modelAuth = new Default_Model_Authorization();
            $modelAuth->logout();

            $modelToken = new Default_Model_SingleSignOnToken();
            $token = $modelToken->createAuthToken($member_id, false, Default_Model_SingleSignOnToken::ACTION_LOGOUT);

            setcookie(Default_Model_SingleSignOnToken::ACTION_LOGOUT, $token, time() + 120, '/',Local_Tools_ParseDomain::get_domain($this->getRequest()->getHttpHost()), null, true);
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
        $this->getResponse()
            ->clearHeaders(array('Expires', 'Pragma', 'Cache-Control'))
            ->setHeader('Pragma', 'no-cache', true)
            ->setHeader('Cache-Control', 'private, no-cache, must-revalidate', true);
    }

    public function verificationAction()
    {
        $filterInput = new Zend_Filter_Input(
            array('*' => 'StringTrim', 'vid' => 'Alnum'),
            array('vid' => array('Alnum', 'presence' => 'required')),
            $this->getAllParams()
        );
        $_vId = $filterInput->getEscaped('vid');


        if (!$_vId) {
            $this->redirect('/');
        }

        $translate = Zend_Registry::get('Zend_Translate');
        $this->view->title = $translate->_('member.email.verification.title');

        $authModel = new Default_Model_Authorization();
        $authUser = $authModel->getAuthUserDataFromUnverified($_vId);

        if (empty($authUser)) {
            throw new Zend_Controller_Action_Exception('This member account could not activated. verification id:' . print_r($this->getParam('vid'),
                    true));
        }

        if ($authUser AND (false == empty($authUser->email_checked))) {
            $this->view->headScript()->appendFile('//www.google.com/recaptcha/api.js');
            $this->view->addHelperPath(APPLICATION_LIB . '/Cgsmith/View/Helper', 'Cgsmith\\View\\Helper\\');
            $this->view->formRegister = new Default_Form_Register();
            $this->view->registerErrMsg = "<p>Your account has already been activated.</p><p class='small'><a href='/login'>Log in</a> or try to generate a <a href='/login/forgot'>new password</a> for your account. </p> ";
            $this->view->overlay = $this->view->render('authorization/registerError.phtml');
            $this->fetchTopProducts();
            $this->_helper->viewRenderer('register');
            return;
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - activate user from email link. member_id: ' . print_r($authUser->member_id,
                true) . ' - username: ' . print_r($authUser->username, true));
        $modelMember = new Default_Model_Member();
        $result = $modelMember->activateMemberFromVerification($authUser->member_id, $_vId);

        if (false == $result) {
            throw new Zend_Controller_Action_Exception('Your member account could not activated.');
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - user activated. member_id: ' . print_r($authUser->member_id,
                true));
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

    protected function fetchTopProducts()
    {
        $tableProjects = new Default_Model_Project();
        $this->view->projects = $tableProjects->fetchTopProducts();
    }

    /**
     * @param string|int $identity
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
                    'status' => 'ok',
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
     * @param array $userData
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

    /**
     * @param $userId
     */
    protected function handleRedirect($userId)
    {
        if (false === empty($this->view->redirect)) {
            $redirect = $this->decodeString($this->view->redirect);
            if (false !== strpos('/register', $redirect)) {
                $redirect = '/member/' . $userId . '/activities/';
            }
            if ($this->_request->isXmlHttpRequest()) {
                $this->_helper->json(array('status' => 'ok', 'redirect' => $redirect));
            } else {
                $this->redirect($redirect);
            }
        } else {
            if ($this->_request->isXmlHttpRequest()) {
                $this->_helper->json(array('status' => 'ok', 'redirect' => '/member/' . $userId . '/activities/'));
            } else {
                $this->getRequest()->setParam('member_id', $userId);
                //$this->forward('news', 'user', null, $this->getAllParams());
                $this->redirect('/member/' . $userId . '/activities/', $this->getAllParams());
            }
        }
    }

}