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
class Default_Plugin_SignOn extends Zend_Controller_Plugin_Abstract
{

    /** @var Zend_Auth */
    protected $_auth;

    public function __construct($auth)
    {
        $this->_auth = $auth;
    }

    /**
     * @inheritDoc
     *
     * @param Zend_Controller_Request_Abstract $request
     *
     * @throws Zend_Auth_Storage_Exception
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        // at this point the zend framework has already tested the session cookie and we should have an auth object.
        if ($this->_auth->hasIdentity()) {
            return;
        }

        if (false === ($token_id = $request->getCookie(Default_Model_SingleSignOnToken::ACTION_LOGIN, false))) {
            return;
        }

        $modelAuthToken = new Default_Model_SingleSignOnToken();
        if (false === $modelAuthToken->isValid($token_id)) {
            return;
        }

        $token_data = $modelAuthToken->getData($token_id);

        if (isset($token_data['member_id']) AND isset($token_data['auth_result'])) {
            $modelAuth = new Default_Model_Authorization();
            $authResult = $modelAuth->authenticateUser($token_data['member_id'], null, $token_data['remember_me'],
                Local_Auth_AdapterFactory::LOGIN_SSO);
            if (false === $authResult->isValid()) {
                Zend_Registry::get('logger')->warn(__METHOD__ . ' - Sign on with OAuth failed: Can not authenticate user ('
                    . $token_data['member_id'] . ',' . $token_id . ')' . implode('; ', $authResult->getMessages()))
                ;
            }
        }
    }

}