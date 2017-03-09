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
class Default_Model_SingleSignOnToken extends Local_Model_Table
{

    const LOGIN_CACHE_NAME_PREFIX = 'sso_token_login_';
    const LOGOUT_CACHE_NAME_PREFIX = 'sso_token_logout_';
    const SSO_SESSION_NAMESPACE = 'sso_action';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const REMEMBER_ME = true;

    protected $_name = "sso_auth_token";

    protected $_keyColumnsForRow = array('token_member_id', 'token_value', 'token_action');

    protected $_key = 'sso_auth_token_id';

    protected $_defaultValues = array(
        'token_member_id' => null,
        'token_value' => 0,
        'token_created' => null,
        'token_changed' => null,
        'token_expired' => null
    );

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
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        return (boolean) $cache->test($token_id);
    }

    public function getData($token_id, $action)
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