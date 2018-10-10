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
 * Created: 10.10.2018
 */

class LogoutController extends Local_Controller_Action_DomainSwitch
{

    public function logoutAction()
    {
        $this->_helper->layout()->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $user_id = Zend_Auth::getInstance()->getStorage()->read()->member_id;

            //$modelAuth = new Default_Model_Authorization();
            //$modelAuth->logout();
            //
            //$modelToken = new Default_Model_SingleSignOnToken();
            //$data = array(
            //    'remember_me' => false,
            //    'redirect'    => $this->getParam('redirect'),
            //    'action'      => Default_Model_SingleSignOnToken::ACTION_LOGOUT
            //);
            //$token_id = $modelToken->createToken($data);
            //setcookie(Default_Model_SingleSignOnToken::ACTION_LOGOUT, $token_id, time() + 120, '/',
            //    Local_Tools_ParseDomain::get_domain($this->getRequest()->getHttpHost()), null, true);

            $config = Zend_Registry::get('config')->settings->domain;

            $jwt = Default_Model_Jwt::encode($user_id);
            setcookie($config->openid->cookie_name, $jwt, time() - 120, '/', $config->openid->host, null, true);

            setcookie($config->forum->cookie_name, $jwt, time() - 120, '/', $config->forum->host, null, true);

            setcookie($config->opencode->cookie_name, $jwt, time() - 120, '/', $config->opencode->host, null, true);
        }

    }

}