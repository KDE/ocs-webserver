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
    const LOGIN_METHOD_FACEBOOK = 'facebook';
    const PROFILE_IMG_SRC_LOCAL = 'local';
    const LOGIN_METHOD_TWITTER = 'twitter';
    const LOGIN_METHOD_AMAZON = 'amazon';


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

            $newPass = $this->generateNewPassword();

            $newPasswordHash = $this->storeNewPassword($newPass, $user);

            Zend_Registry::get('logger')->info(__METHOD__ . ' - old password hash: ' . $user->password . ', new password hash: ' . $newPasswordHash);

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

    public function loginfromcookieAction()
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
        $authResult = $authModel->authenticateUserSession($values['mail'], $values['password'], $values['remember_me']);

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

    public function loginAction()
    {
        //TODO: check redirect for a local valid url.
        $this->view->redirect = $this->getParam('redirect');

        $formLogin = new Default_Form_Login();
        //Default_Model_CsrfProtection::createCSRF($formLogin, 'login', 'csrfLogin');

        if ($this->_request->isGet()) { // not a POST request
            $this->view->formLogin = $formLogin->populate(array('redirect' => $this->view->redirect));
            $this->view->error = 0;

            return;
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - Start authentication on host: ' . Zend_Registry::get('store_host'));
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
        $authResult = $authModel->authenticateUserSession($values['mail'], $values['password'], $values['remember_me']);

        if (false == $authResult->isValid()) { // authentication fail
            Zend_Registry::get('logger')->info(__METHOD__ . ' - authentication failed.');
            Zend_Registry::get('logger')->info(__METHOD__ . ' - auth_user: ' . print_r($values['mail'], true));
            Zend_Registry::get('logger')->info(__METHOD__ . ' - auth_result: ' . print_r($authResult->getMessages(), true));

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

        // handle redirect
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

    public function registerAction()
    {
        $this->view->redirect = $this->getParam('redirect');

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
        } else {
            //check reCAPTCHA
            $formRegisterValues = $formRegister->getValues();
            //$resultReCaptcha = $formRegisterValues['g-recaptcha-response'];

            if (false == $this->validateReCaptcha($_POST['g-recaptcha-response'])) {
                $this->view->formRegister = $formRegister;
                $this->view->error = 1;

                if ($this->_request->isXmlHttpRequest()) {
                    $this->_helper->json(array(
                        'status' => 'error',
                        'message' => '<h3>Your ar not a human? Please try again: <a href="/register">Register</a></h3>'
                    ));
                    return;
                }
                return;
            }
        }

        $formRegisterValues = $formRegister->getValues();
        $formRegisterValues['password'] = $formRegisterValues['password1'];

        $newUserData = $this->createNewUser($formRegisterValues);

        $this->sendConfirmationMail($formRegisterValues, $newUserData['verificationVal']);

        $this->sendAdminNotificationMail($formRegisterValues);

        if ($this->_request->isXmlHttpRequest()) {
            $viewRegisterForm = $this->view->render('authorization/partials/registerSuccess.phtml');
            $this->_helper->json(array('status' => 'ok', 'message' => $viewRegisterForm));
        } else {
            $this->_helper->viewRenderer('registerSuccess');
        }
    }

    private function validateReCaptcha($reCaptchaResponse)
    {
        if ('development' == APPLICATION_ENV) {
            return true;
        }

        $google_url = "https://www.google.com/recaptcha/api/siteverify";
        $secret = '6Lej1yITAAAAAO9DzumzsLBhv4J6-zKTqNDmLXPC';
        $ip = $_SERVER['REMOTE_ADDR'];
        $url = $google_url . "?secret=" . $secret . "&response=" . $reCaptchaResponse . "&remoteip=" . $ip;


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_USERAGENT,
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
        $curlData = curl_exec($curl);
        curl_close($curl);

        $res = json_decode($curlData, true);
        $log = Zend_Registry::get('logger');
        $log->debug(__METHOD__ . " - ********** validateReCaptcha: **********");
        $log->debug(__METHOD__ . ' - ' . print_r($res, true));
        $log->debug(__METHOD__ . " - ********** validateReCaptcha: **********");

        $success = ($res['success'] == 1);

        return $success;
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
            $verificationVal = Default_Model_MemberEmail::getVerificationValue($userData['username'], $userData['mail']);
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

    public function logoutAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();

        $session = new Zend_Session_Namespace();
        $session->unsetAll();
        Zend_Session::forgetMe();
        Zend_Session::destroy();

        $config = Zend_Registry::get('config');
        $cookieName = $config->settings->auth_session->remember_me->name;
        $cookieData = $this->_request->getCookie($cookieName, null);
        if ($cookieData) {
            $cookieData = unserialize($cookieData);
            $remember_me_seconds = $config->settings->auth_session->remember_me->timeout;
            $domain = $this->getServerName();
            $cookieExpire = time() - $remember_me_seconds;

            setcookie($cookieName, null, $cookieExpire, '/', $domain, null, true);

            //TODO: Remove Cookie from database
            $modelAuthorization = new Default_Model_Authorization();
            $modelAuthorization->removeAllCookieInformation('member_id', $cookieData['mi']);
        }

        $this->redirect('/');
    }

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->getResponse()
            ->clearHeaders(array('Expires', 'Pragma', 'Cache-Control'))
            ->setHeader('Pragma', 'no-cache', true)
            ->setHeader('Cache-Control', 'private, no-cache, must-revalidate', true);
    }

    public function facebookAction()
    {
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r($this->getRequest(), true));

        if ($this->hasParam('error_reason') && $this->getParam('error_reason') == 'user_denied') {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - User denied access. Forwarded to register page. - ' . print_r($this->getAllParams(),
                    true));
            $this->redirect('/register');
            return;
        }

        if ($this->hasParam('error')) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - Undefined error. Throw error. - ' . print_r($this->getAllParams(),
                    true));
            throw new Zend_Controller_Action_Exception('Undefined error at facebook login', 500);
        }

        $this->_helper->layout->disableLayout();
        include_once 'facebook/facebook.php';
        $facebook = new Facebook(
            array(
                'appId' => FACEBOOK_APP_ID,
                'secret' => FACEBOOK_SECRET,
                'cookie' => true,
            ));

        $user_uid = $facebook->getUser();

        if (empty($user_uid)) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - No Facebook Login. Get Login URL from Facebook and redirect user - ' . print_r($facebook->getAppId(),
                    true));
            $loginUrl = $facebook->getLoginUrl(array('scope' => 'email'));
            $this->redirect($loginUrl);
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - has user id - ' . print_r($user_uid, true));

        $facebookUserData = $facebook->api('/me');
        Zend_Registry::get('logger')->info(__METHOD__ . ' - facebook user data - ' . print_r($facebookUserData, true));

        $authModelData = new Default_Model_Authorization();
        $authMember = $authModelData->getAuthDataFromSocialUser($facebookUserData['username'], 'facebook');

        if (null !== $authMember) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - update user data and init auth session - ');

            $this->updateMemberDataFromFacebook($authMember->member_id, $facebookUserData);

            $this->storeAuthSessionData($authMember->member_id);

            $authModelData->updateUserLastOnline('member_id', $authMember->member_id);

            $member_id = $authMember->member_id;

        } else {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - register new facebook user - ');

            $location = $facebookUserData['location']['name'];
            $locationArr = explode(",", $location);

            //update profile-img
            $imageModel = new Default_Model_DbTable_Image();
            $fileName = $imageModel->storeExternalImage('https://graph.facebook.com/' . $facebookUserData['username'] . '/picture?type=large');

            $newUserValues = array(
                'username' => $facebookUserData['username'],
                'firstname' => $facebookUserData['first_name'],
                'lastname' => $facebookUserData['last_name'],
                'mail' => $facebookUserData['email'],
                'roleId' => self::DEFAULT_ROLE_ID,
                'mail_checked' => 1,
                'agb' => 1,
                'city' => trim($locationArr[0]),
                'country' => trim($locationArr[1]),
                'avatar' => $fileName,
                'login_method' => self::LOGIN_METHOD_FACEBOOK,
                'profile_img_src' => self::PROFILE_IMG_SRC_LOCAL,
                'profile_image_url' => IMAGES_MEDIA_SERVER . '/cache/200x200-2/img/' . $fileName,
                'social_username' => $facebookUserData['username'],
                'social_user_id' => $facebookUserData['id'],
                'link_facebook' => $facebookUserData['link'],
                'created_at' => new Zend_Db_Expr('Now()'),
                'changed_at' => new Zend_Db_Expr('Now()'),
                'uuid' => Local_Tools_UUID::generateUUID(),
                'verificationVal' => MD5($facebookUserData['id'] . $facebookUserData['username'] . time())
            );

            $member_id = $this->storeNewUser($newUserValues);

            $this->storeAuthSessionData($member_id);

            $authModelData->updateUserLastOnline('member_id', $member_id);

            $this->sendAdminNotificationMail($newUserValues);
        }

        $this->redirect("/member/{$member_id}/activities/");
    }

    /**
     * @param int $member_id
     * @param array $facebookUserData
     */
    public function updateMemberDataFromFacebook($member_id, $facebookUserData)
    {
        $tableMember = new Default_Model_Member();
        $member = $tableMember->find($member_id)->current();
        $member->mail = $facebookUserData['email'];
        $member->link_facebook = $facebookUserData['link'];
        $member->changed_at = new Zend_Db_Expr('Now()');
        $member->save();
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
     * @param array $userData
     * @return int
     */
    protected function storeNewUser($userData)
    {
        $userTable = new Default_Model_Member();
        $userData = $userTable->storeNewUser($userData);

        return $userData->member_id;
    }

    public function twitterAction()
    {
        $this->_helper->layout->disableLayout();

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r($this->getRequest(), true));

        if ($this->hasParam('denied')) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - User denied access. Forwarded to register page. - ' . print_r($this->getAllParams(),
                    true));
            $this->redirect('/register');
            return;
        }

        // require_once('Zend/Oauth/Consumer.php');
        $consumer = new Zend_Oauth_Consumer($this->getTwitterAuthConfig());

        // require_once('Zend/Session/Namespace.php');
        $session = new Zend_Session_Namespace('oauth');

        if (strlen($session->request_token) > 0 && strlen($session->request_secret) > 0) {
            $this->getAccessToken($session, $this->getTwitterAuthConfig());
            // return to login user
            $this->redirect('/login/twitter');
        }

        if (strlen($session->access_token) > 0 && strlen($session->access_secret) > 0) {
            $twitterUserData = $this->getUserDataFromTwitter($session);
            Zend_Registry::get('logger')->info(__METHOD__ . ' - twitter user data - ' . print_r($twitterUserData,
                    true));

            $authModel = new Default_Model_Authorization();
            $authMember = $authModel->getAuthDataFromSocialUser($twitterUserData->id, 'twitter');

            if (count((array)$authMember) > 0) {
                Zend_Registry::get('logger')->info(__METHOD__ . ' - twitter login - ');

                $this->storeAuthSessionData($authMember->member_id);

                $authModel->updateUserLastOnline('member_id', $authMember->member_id);

                $member_id = $authMember->member_id;

            } else {
                Zend_Registry::get('logger')->info(__METHOD__ . ' - twitter register user - ');

                //update profile-img
                $imageModel = new Default_Model_DbTable_Image();
                $fileName = $imageModel->storeExternalImage(str_replace('_normal', '',
                    $twitterUserData->profile_image_url));
                $location = $twitterUserData->time_zone;
                $locationArr = explode(",", $location);
                $name = $twitterUserData->name;
                $nameArr = explode(" ", $name);

                $newUserValues = array(
                    'username' => $twitterUserData->screen_name,
                    'firstname' => $nameArr[0],
                    'lastname' => $nameArr[1],
                    'roleId' => self::DEFAULT_ROLE_ID,
                    'mail_checked' => 1,
                    'agb' => 1,
                    'city' => trim($locationArr[0]),
                    'country' => trim($locationArr[1]),
                    'profile_image_url' => IMAGES_MEDIA_SERVER . '/cache/200x200-2/img/' . $fileName,
                    'profile_img_src' => self::PROFILE_IMG_SRC_LOCAL,
                    'social_username' => $twitterUserData->screen_name,
                    'social_user_id' => $twitterUserData->id,
                    'avatar' => $fileName,
                    'login_method' => self::LOGIN_METHOD_TWITTER,
                    'created_at' => new Zend_Db_Expr('Now()'),
                    'changed_at' => new Zend_Db_Expr('Now()'),
                    'uuid' => Local_Tools_UUID::generateUUID(),
                    'verificationVal' => MD5($twitterUserData->id . $twitterUserData->screen_name . time())
                );

                $member_id = $this->storeNewUser($newUserValues);

                $this->storeAuthSessionData($member_id);

                $authModel->updateUserLastOnline('member_id', $member_id);

                $this->sendAdminNotificationMail($newUserValues);
            }

            $this->redirect("/member/{$member_id}/activities/");
        }

        $this->getRequestToken($session, $consumer);
    }

    /**
     * @return array
     */
    protected function getTwitterAuthConfig()
    {
        return array(
            'callbackUrl' => 'http://' . $this->getServerName() . '/login/twitter',
            'siteUrl' => 'https://twitter.com/oauth',
            'consumerKey' => TWITTER_CONSUMER_KEY,
            'consumerSecret' => TWITTER_CONSUMER_SECRET
        );
    }

    /**
     * @param Zend_Session_Namespace $session
     * @param array $authConfig
     * @throws Zend_Controller_Action_Exception
     */
    protected function getAccessToken($session, $authConfig)
    {
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));

        // build the token request based on the original token and secret
        $request = new Zend_Oauth_Token_Request();
        $request->setToken($session->request_token)
            ->setTokenSecret($session->request_secret);
        try {
            // try to retrieve the token
            $consumer = new Zend_Oauth_Consumer($authConfig);
            $accessToken = $consumer->getAccessToken($_GET, $request);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - twitter access token - ' . print_r($accessToken,
                    true));

            if (false === $accessToken->isValid()) {
                Zend_Registry::get('logger')->info(__METHOD__ . ' - twitter access token invalid - ');
                return;
            }

            // we now have a token to access, insert into session
            $session->access_token = $accessToken->getToken();
            $session->access_secret = $accessToken->getTokenSecret();

            // clear the request tokens from session
            unset($session->request_token);
            unset($session->request_secret);

            return;
        } catch (Exception $ex) {
            // error retrieving token, handle accordingly
            throw new Zend_Controller_Action_Exception('Error while request access token');
        }
    }

    /**
     * @param Zend_Session_Namespace $session
     * @return Zend_Rest_Client_Result
     */
    protected function getUserDataFromTwitter($session)
    {
        // require_once('Zend/Oauth/Token/Access.php');
        $token = new Zend_Oauth_Token_Access();
        $token->setToken($session->access_token)->setTokenSecret($session->access_secret);

        // require_once('Zend/Service/Twitter.php');
        $twitter = new Zend_Service_Twitter();
        $twitter->setHttpClient(
            $token->getHttpClient($this->getTwitterAuthConfig())
        );

        $userData = $twitter->accountVerifyCredentials();
        return $userData;
    }

    /**
     * @param Zend_Session_Namespace $session
     * @param Zend_Oauth_Consumer $consumer
     * @param array|null $customServiceParameters
     */
    protected function getRequestToken($session, $consumer, $customServiceParameters = null)
    {
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));
        // fetch a request token
        $token = $consumer->getRequestToken($customServiceParameters);
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - twitter request token - ' . print_r($token, true));

        if (false === $token->isValid()) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - twitter request token invalid - ');
            return;
        }

        // save the token to session
        $session->request_token = $token->getToken();
        $session->request_secret = $token->getTokenSecret();

        // redirect the user to third-party
        $consumer->redirect();
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

        if ($authUser AND ($authUser->mail_checked == 0)) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - activate user from email link : ' . print_r($authUser->member_id,
                    true) . ' - username: ' . print_r($authUser->username, true));
            $modelMember = new Default_Model_Member();
            $result = $modelMember->activateMemberFromVerification($authUser->member_id, $_vId);

            if (false == $result) {
                throw new Zend_Controller_Action_Exception('Your member account could not activated.');
            }

            $this->view->member = $authUser;
            $this->view->username = $authUser->username;

            $this->view->form = new Default_Form_Register();
            $this->view->overlay = $this->view->render('authorization/registerWelcome.phtml');

            $this->storeAuthSessionData($authUser->member_id);

            $tableProduct = new Default_Model_Project();
            $this->view->products = $tableProduct->fetchAllProjectsForMember($authUser->member_id);

            $this->forward('index', 'settings', 'default', array('member_id' => $authUser->member_id));
        } else {
            $this->view->formRegister = new Default_Form_Register();
            $this->view->overlay = $this->view->render('authorization/registerError.phtml');
            $this->fetchTopProducts();
            $this->_helper->viewRenderer('register');
        }
    }

    protected function fetchTopProducts()
    {
        $tableProjects = new Default_Model_Project();
        $this->view->projects = $tableProjects->fetchTopProducts();
    }

    public function amazonAction()
    {
        $this->_helper->layout->disableLayout();

        $log = Zend_Registry::get('logger');
        $log->debug("********** amazon-auth: Start **********");

        $config = Zend_Registry::get('config');
        $amazonConfig = $config->third_party->amazon;

        $serviceConfig = array(
            'callbackUrl' => 'https://' . $this->getServerName() . '/login/amazon',
            'siteUrl' => 'https://www.amazon.com/ap/oa',
            'consumerKey' => $amazonConfig->consumer->key,
            'consumerSecret' => $amazonConfig->consumer->secret
        );

        $extendedServiceParam = array('client_id' => $amazonConfig->consumer->key, 'sandbox' => true);


        // require_once('Zend/Oauth/Consumer.php');
        $consumer = new Zend_Oauth_Consumer($serviceConfig);

        // require_once('Zend/Session/Namespace.php');
        $session = new Zend_Session_Namespace('oauth_amazon');

        foreach ($_GET as $name => $value) {
            $session->$name = $value;
        }


        if (strlen($session->request_token) > 0 && strlen($session->request_secret) > 0) {
            $this->getAccessToken($session, $serviceConfig);
            // return to login user
            $this->redirect('/login/amazon');
        }

        if (strlen($session->access_token) > 0) {
            $userData = $this->getUserDataFromAmazon($session, $amazonConfig);
            $log->debug(__FUNCTION__ . ': ' . print_r($userData, true));

            $authModel = new Default_Model_Authorization();
            $authMember = $authModel->getAuthDataFromSocialUser($userData->user_id, self::LOGIN_METHOD_AMAZON);

            if (count((array)$authMember) > 0) {
                $log->debug("amazon-auth: Login");

                $this->storeAuthSessionData($authMember->member_id);

                $authModel->updateUserLastOnline('member_id', $authMember->member_id);

                $member_id = $authMember->member_id;

            } else {
                $log->debug("amazon-auth: Register");

                $name = $userData->name;
                $nameArr = explode(" ", $name);

                $newUserValues = array(
                    'username' => $userData->email,
                    'mail' => $userData->email,
                    'firstname' => $nameArr[0],
                    'lastname' => $nameArr[1],
                    'roleId' => self::DEFAULT_ROLE_ID,
                    'mail_checked' => 1,
                    'agb' => 1,
                    'social_username' => $userData->screen_name,
                    'social_user_id' => $userData->user_id,
                    'login_method' => self::LOGIN_METHOD_AMAZON
                );

                $userData = $this->createNewUser($newUserValues);

                $this->storeAuthSessionData($userData->member_id);

                $authModel->updateUserLastOnline('member_id', $userData->member_id);

                $this->sendAdminNotificationMail($newUserValues);
            }

            $log->debug("********** amazon-auth: Stop **********");

            $this->redirect("/member/{$userData->member_id}/activities/");
        }

        $this->getRequestToken($session, $consumer, $extendedServiceParam);

        $log->debug("********** amazon-auth: Stop **********");
    }

    /**
     * @param Zend_Session_Namespace $session
     * @param Zend_Config $amazonConfig
     * @throws Zend_Controller_Exception
     * @return Zend_Rest_Client_Result
     */
    protected function getUserDataFromAmazon($session, $amazonConfig)
    {
        // verify that the access token belongs to us
        $c = curl_init('https://api.sandbox.amazon.com/auth/o2/tokeninfo?access_token=' . urlencode($session->access_token));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($c);
        curl_close($c);
        $data = json_decode($response);

        if ($data->aud != $amazonConfig->consumer->key) {
            // the access token does not belong to us
            throw new Zend_Controller_Exception('Amazon access token was not validated.');
        }

        // exchange the access token for user profile
        $c = curl_init('https://api.sandbox.amazon.com/user/profile');
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $session->access_token));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($c);
        curl_close($c);
        $userData = json_decode($response);

        if ($userData->error) {
            throw new Zend_Controller_Exception('Error while request user data from amazon.');
        }

        return $userData;
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

        $formRegister = new Default_Form_Register();

        $name = $this->getParam('name');
        $value = $this->getParam('value');

        $result = $formRegister->getElement($name)->isValid($value);

        $this->_helper->json(array('status' => $result, $name => $formRegister->getElement($name)->getMessages()));
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
     * @return array
     */
    protected function getThingiverseAuthConfig()
    {
        return array(
            'callbackUrl' => 'http://' . $this->getServerName() . '/login/thingiverse',
            'siteUrl' => 'https://www.thingiverse.com/login/oauth/authorize',
            'consumerKey' => THINGIVERSE_CONSUMER_KEY,
            'consumerSecret' => THINGIVERSE_CONSUMER_SECRET
        );
    }

}