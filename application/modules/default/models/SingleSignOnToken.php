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

    public function createAuthToken($member_id, $remember_me = false, $action = self::ACTION_LOGIN)
    {
        $token = substr(Local_Tools_UUID::generateUUID(),0,45);
        $data = array('token_member_id' => $member_id, 'token_value' => $token, 'token_action' => $action, 'remember_me' => (int)$remember_me);
        $result = $this->save($data);
        if (count($result->toArray()) <= 0) {
            throw new Zend_Exception('could not save auth token in database');
        }
        return $token;
    }

    /**
     * @param $token_value
     * @param $action
     * @return bool
     */
    public function isValidToken($token_value, $action)
    {
        if (empty($token_value)) {
            return false;
        }

        $sql = "
            SELECT `sso_auth_token`.*
            FROM `sso_auth_token`
            JOIN member ON member.member_id = `sso_auth_token`.token_member_id
            WHERE member.is_active = :active
            AND member.is_deleted = :deleted
            AND member.login_method = :login
            AND `sso_auth_token`.token_value = :token_value
            AND `sso_auth_token`.token_expired >= NOW()
            AND `sso_auth_token`.token_action = :token_action
            ";

        $this->_db->getProfiler()->setEnabled(true);
        $resultSet = $this->_db->fetchAll($sql, array(
            'active' => Default_Model_DbTable_Member::MEMBER_ACTIVE,
            'deleted' => Default_Model_DbTable_Member::MEMBER_NOT_DELETED,
            'login' => Default_Model_DbTable_Member::MEMBER_LOGIN_LOCAL,
            'token_value' => $token_value,
            'token_action' => $action
        ));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - sql take seconds: ' . $this->_db->getProfiler()->getLastQueryProfile()->getElapsedSecs());
        $this->_db->getProfiler()->setEnabled(false);

        return count($resultSet) == 1;
    }

    /**
     * @param $token_value
     * @param $action
     * @return array|null
     */
    public function getValidTokenData($token_value, $action)
    {
        if (empty($token_value)) {
            return null;
        }

        $sql = "
            SELECT `sso_auth_token`.*
            FROM `sso_auth_token`
            JOIN member ON member.member_id = `sso_auth_token`.token_member_id
            WHERE member.is_active = :active
            AND member.is_deleted = :deleted
            AND member.login_method = :login
            AND `sso_auth_token`.token_value = :token_value
            AND `sso_auth_token`.token_expired >= NOW()
            AND `sso_auth_token`.token_action = :token_action
            ";

        $this->_db->getProfiler()->setEnabled(true);
        $resultSet = $this->_db->fetchRow($sql, array(
            'active' => Default_Model_DbTable_Member::MEMBER_ACTIVE,
            'deleted' => Default_Model_DbTable_Member::MEMBER_NOT_DELETED,
            'login' => Default_Model_DbTable_Member::MEMBER_LOGIN_LOCAL,
            'token_value' => $token_value,
            'token_action' => $action
        ));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - sql take seconds: ' . $this->_db->getProfiler()->getLastQueryProfile()->getElapsedSecs());
        $this->_db->getProfiler()->setEnabled(false);

        return $resultSet;
    }

    /**
     * @param $member_id
     * @return string
     */
    public static function createAuthTokenLogin($member_id)
    {
        $token = substr(Local_Tools_UUID::generateUUID(),0,45);
        $expiry = time() + 300;
        $data = array('member_id' => $member_id, 'token' => $token, 'expiry' => $expiry, 'url_path' => '/login/propagate');
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cache->save($data, self::LOGIN_CACHE_NAME_PREFIX . $member_id, array(), 300);
        return $token;
    }

    /**
     * @param $member_id
     * @return null|string
     */
    public static function getAuthToken($member_id, $action)
    {
        switch ($action) {
            case self::ACTION_LOGIN: $prefix = self::LOGIN_CACHE_NAME_PREFIX; break;
            case self::ACTION_LOGOUT: $prefix = self::LOGOUT_CACHE_NAME_PREFIX; break;
            default: $prefix = self::LOGIN_CACHE_NAME_PREFIX;
        }

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $data = $cache->load($prefix . $member_id);
        if (false == $data) {
            return null;
        }
        return $data['token'];
    }

    /**
     * @param $member_id
     * @return null|string
     */
    public static function getUrlPath($member_id, $action)
    {
        switch ($action) {
            case self::ACTION_LOGIN: $prefix = self::LOGIN_CACHE_NAME_PREFIX; break;
            case self::ACTION_LOGOUT: $prefix = self::LOGOUT_CACHE_NAME_PREFIX; break;
            default: $prefix = self::LOGIN_CACHE_NAME_PREFIX;
        }

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $data = $cache->load(self::LOGIN_CACHE_NAME_PREFIX . $member_id);
        if (false == $data) {
            return null;
        }
        return $data['url_path'];
    }

}