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
class OAuthController extends Zend_Controller_Action
{

    const PARAM_NAME_PROVIDER = 'provider';
    const ERR_MSG_DEFAULT = '<p class="text-danger center">An error occurred while trying authenticate you. Please try later or try our local login or register.</p>';

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * @throws Zend_Exception
     */
    public function loginAction()
    {
        $filterInput = new Zend_Filter_Input(array('*' => array('StringTrim', 'StripTags')),
            array(self::PARAM_NAME_PROVIDER => array('Alpha', 'presence' => 'required')), $this->getAllParams());

        if ($filterInput->hasInvalid()) {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - ' . print_r($this->getAllParams(), true));
            $this->_helper->flashMessenger->addMessage(self::ERR_MSG_DEFAULT);
            $this->forward('index', 'explore', 'default');

            return;
        }

        $data = array(
            'remember_me' => true,
            'redirect'    => $this->getParam('redirect'),
            'action'      => Default_Model_SingleSignOnToken::ACTION_LOGIN
        );
        $token_id = $this->createAToken($data);

        /** @var Default_Model_OAuth_Ocs $authAdapter */
        $authAdapter = Default_Model_OAuth::factory($this->getParam(self::PARAM_NAME_PROVIDER));
        $requestUrl = $authAdapter->authStartWithToken($token_id);

        $this->redirect($requestUrl);
    }

    /**
     * @param $data
     *
     * @return string
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     */
    protected function createAToken($data)
    {
        $modelToken = new Default_Model_SingleSignOnToken();
        $token_id = $modelToken->createToken($data);
        setcookie(Default_Model_SingleSignOnToken::ACTION_LOGIN, $token_id, time() + 120, '/',
            Local_Tools_ParseDomain::get_domain($this->getRequest()->getHttpHost()), null, true);

        return $token_id;
    }

    /**
     * @throws Exception
     * @throws Zend_Exception
     */
    public function githubAction()
    {
        /** @var Default_Model_Oauth_Github $authAdapter */
        $authAdapter = Default_Model_OAuth::factory('github');
        $access_token = $authAdapter->authFinish($this->getAllParams());

        if (false == $authAdapter->isConnected()) {
            $this->_helper->flashMessenger->addMessage(self::ERR_MSG_DEFAULT);
            $this->forward('index', 'explore', 'default');
        }

        $authResult = $authAdapter->authenticate();

        switch ($authResult->getCode()) {

            case Zend_Auth_Result::SUCCESS:
                Zend_Registry::get('logger')->info(__METHOD__ . ' - authentication successful - member_id: ' . Zend_Auth::getInstance()
                                                                                                                        ->getIdentity()->member_id)
                ;
                break;

            case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                $registerResult = $authAdapter->registerLocal();
                if (false === $registerResult->isValid()) {
                    Zend_Registry::get('logger')->info(__METHOD__ . ' - ip: ' . $this->_request->getClientIp()
                        . ' - registration from social provider failed.' . PHP_EOL . print_r($registerResult->getMessages(), true))
                    ;
                    $this->_helper->flashMessenger->addMessage(self::ERR_MSG_DEFAULT);
                    $this->forward('index', 'explore', 'default');

                    return;
                } else {
                    $this->_helper->flashMessenger->addMessage('Your account was created. Please set a password.');
                    //New Github-User was created, now let him set a password
                    $this->redirect('/password/setpassword');
                    
                    return;
                    
                }
                Zend_Registry::get('logger')->info(__METHOD__ . ' - registration from social provider successful - member_id: '
                    . Zend_Auth::getInstance()->getIdentity()->member_id)
                ;
                break;

            case  Zend_Auth_Result::FAILURE_UNCATEGORIZED:
            case  Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
            case  Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS:
            case  Zend_Auth_Result::FAILURE:
                Zend_Registry::get('logger')->info(__METHOD__ . ' - ip: ' . $this->_request->getClientIp()
                    . ' - authentication failed.')
                ;
                $this->_helper->flashMessenger->addMessage(self::ERR_MSG_DEFAULT);
                $this->forward('index', 'explore', 'default');

                return;
        }

        $modelToken = new Default_Model_SingleSignOnToken();
        $modelToken->addData($this->getParam('state'), array(
            'member_id'   => Zend_Auth::getInstance()->getIdentity()->member_id,
            'auth_result' => $authResult->isValid()
        ));

        $authAdapter->storeAccessToken($access_token);
        $redirect_url = $authAdapter->getRedirect();

        if (false === $redirect_url) {
            $this->forward('products', 'user');

            return;
        }
        $this->redirect($redirect_url);
    }

    /**
     * @throws Exception
     * @throws Zend_Exception
     */
    public function ocsAction()
    {
        /** @var Default_Model_Oauth_Ocs $authAdapter */
        $authAdapter = Default_Model_OAuth::factory('ocs');
        $access_token = $authAdapter->authFinish($this->getAllParams());

        if (false == $authAdapter->isConnected()) {
            $this->_helper->flashMessenger->addMessage(self::ERR_MSG_DEFAULT);
            $this->forward('index', 'explore', 'default');

            return;
        }

        $authResult = $authAdapter->authenticate();
        Zend_Registry::get('logger')->info(__METHOD__ . ' - AuthResult: ' . print_r($authResult, true));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - AuthResult: ' . print_r($authResult->isValid(), true));
        if (false == $authResult->isValid()) {
            Zend_Registry::get('logger')->info(__METHOD__ . '(' . __LINE__ . ')' . ' - ip: ' . $this->_request->getClientIp()
                . ' - authentication failed.')
            ;
            $this->_helper->flashMessenger->addMessage(self::ERR_MSG_DEFAULT);
            $this->forward('index', 'explore', 'default');

            return;
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - authentication successful - member_id: ' . Zend_Auth::getInstance()
                                                                                                                ->getIdentity()->member_id)
        ;

        $modelToken = new Default_Model_SingleSignOnToken();
        $modelToken->addData($this->getParam('state'), array(
            'member_id'   => Zend_Auth::getInstance()->getIdentity()->member_id,
            'auth_result' => $authResult->isValid()
        ));

        $authAdapter->storeAccessToken($access_token);
        $redirect_url = $authAdapter->getRedirect();

        if (false === $redirect_url) {
            $this->forward('products', 'user');

            return;
        }
        $this->redirect($redirect_url);
    }

    /**
     * @throws Zend_Exception
     */
    public function registerAction()
    {
        $filterInput = new Zend_Filter_Input(array('*' => array('StringTrim', 'StripTags')),
            array(self::PARAM_NAME_PROVIDER => array('Alpha', 'presence' => 'required')), $this->getAllParams());

        if (false == $filterInput->isValid(self::PARAM_NAME_PROVIDER)) {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - ' . print_r($this->getAllParams(), true));
            $this->_helper->flashMessenger->addMessage(self::ERR_MSG_DEFAULT);
            $this->forward('index', 'explore', 'default');

            return;
        }

        $authAdapter = Default_Model_OAuth::factory($filterInput->getEscaped(self::PARAM_NAME_PROVIDER));
        $authAdapter->authStart($this->getParam('redirect'));
    }

}