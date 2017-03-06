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
 *    Created: 21.10.2016
 **/
class Default_Plugin_RememberMe extends Zend_Controller_Plugin_Abstract
{

    /** @var Zend_Auth */
    protected $_auth;

    public function __construct($auth)
    {
        $this->_auth = $auth;
    }

    /**
     * @inheritDoc
     * @param Zend_Controller_Request_Http $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        // at this point the zend framework has already tested the session cookie and we should have an auth object.
        if ($this->_auth->hasIdentity()) {
            return;
        }

        // on login page we don't need a remember me check
        if ($request->getActionName() == 'login') {
            return;
        }

        //Check if rememberMe login cookie exists and authenticate user
        $modelRememberMe = new Default_Model_RememberMe();
        if (true === $modelRememberMe->hasValidCookie()) {
            $cookieData = $modelRememberMe->getCookieData();
            $authModel = new Default_Model_Authorization();
            $authResult = $authModel->authenticateUser($cookieData['member_id'], $cookieData['remember_me_id'], true, Default_Model_Authorization::LOGIN_REMEMBER_ME);
            if (false === $authResult->isValid()) {
                Zend_Registry::get('logger')->warn(__METHOD__ . ' - Can not authenticate user (' . $cookieData['member_id'] . ',' . $cookieData['remember_me_id'] . ') with "remember me" cookie. ' .
                    implode('; ', $authResult->getMessages())
                );
            }
        }
    }

}