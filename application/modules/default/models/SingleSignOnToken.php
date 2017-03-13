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
 *    Created: 09.12.2016
 **/
class Default_Model_SingleSignOnToken
{

    const LOGIN_CACHE_NAME_PREFIX = 'sso_token_login_';
    const LOGOUT_CACHE_NAME_PREFIX = 'sso_token_logout_';
    const SSO_SESSION_NAMESPACE = 'sso_action';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const REMEMBER_ME = true;

    /**
     * @param $data
     * @return string
     */
    public function createToken($data)
    {
        $idToken = substr(Local_Tools_UUID::generateUUID(),0,45);
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cache->save($data, $idToken, array(), 120);
        return $idToken;
    }

    /**
     * @param $token_id
     * @return bool
     */
    public function isValid($token_id)
    {
        $token_id = preg_replace('/[^-a-zA-Z0-9_]/', '',  $token_id);
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        return (boolean) $cache->test($token_id);
    }

    public function getData($token_id)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        return $cache->load($token_id);
    }

    public function addData($token_id, $data)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cached_data = $cache->load($token_id);
        return $cache->save(array_merge($cached_data, $data), $token_id, array(), 120);
    }

}