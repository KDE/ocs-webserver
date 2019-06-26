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
class Default_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{

    /**
     * @inheritDoc
     *
     * @param Zend_Controller_Request_Abstract $request
     *
     * @throws Zend_Exception
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        /** @var Default_Model_Auth_User $auth */
        $auth = Default_Model_Auth_User::getInstance();
        $config_session = Zend_Registry::get('config')->settings->session->cookie->toArray();
        if (APPLICATION_ENV == 'development') {
            $config_session['secure'] = false;
        }
        $auth->initSession($config_session);
    }

}