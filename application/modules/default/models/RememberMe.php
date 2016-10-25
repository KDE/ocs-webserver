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
class Default_Model_RememberMe
{

    protected $salt = 'slkdmclskdaruiowrjasndf224323423rwersdf§$%ZTFG§EWRSGFSD!§RWESFD';

    /** @var null|Zend_Controller_Request_Http */
    protected $request;
    /** @var string */
    protected $dataTableName;
    /** @var  Default_Model_DbTable_Comments */
    protected $dataTable;
    /** @var  string */
    protected $cookieName;
    /** @var  int */
    protected $cookieTimeout;

    /**
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * param [ mixed $args [, $... ]]
     * @param string $_dataTableName
     * @link http://php.net/manual/en/language.oop5.decon.php
     */
    public function __construct($_dataTableName = 'Default_Model_DbTable_Session')
    {
        $this->request = Zend_Controller_Front::getInstance()->getRequest();

        $this->dataTableName = $_dataTableName;
        $this->dataTable = new $this->dataTableName;

        $config = Zend_Registry::get('config');
        $this->cookieName = $config->settings->auth_session->remember_me->name;
        $this->cookieTimeout = $config->settings->auth_session->remember_me->timeout;
    }

    /**
     * @param int $identifier
     * @return array return new session data
     */
    public function createSession($identifier)
    {
        $newSessionData = $this->createSessionData($identifier);
        $this->setCookie($newSessionData);
        $newSessionData['expiry'] = date( 'Y-m-d H:i:s', $newSessionData['expiry']); // change to mysql datetime format
        return $this->dataTable->save($newSessionData)->toArray();
    }

    /**
     * @param int $identifier
     * @return array
     */
    protected function createSessionData($identifier)
    {
        $sessionData = array();
        $sessionData['member_id'] = (int)$identifier;
        $sessionData['remember_me_id'] = Local_Tools_UUID::generateUUID();
        $sessionData['expiry'] = time() + (int)$this->cookieTimeout;
        $sessionData['token'] = base64_encode(hash('sha256',
            $sessionData['member_id'] . $sessionData['remember_me_id'] . $this->salt));
        return $sessionData;
    }

    /**
     * @param array $newSessionData
     * @return bool
     */
    protected function setCookie($newSessionData)
    {
        if (empty($newSessionData)) {
            return false;
        }

        $domain = $this->request->getHttpHost();

        $sessionData = array();
        $sessionData['mi'] = $newSessionData['member_id'];
        $sessionData['u'] = $newSessionData['remember_me_id'];
        $sessionData['t'] = $newSessionData['token'];

        return setcookie($this->cookieName, serialize($sessionData), $newSessionData['expiry'], '/', $domain, null, true);
    }

    /**
     * @return null|array
     */
    public function updateSession()
    {
        $currentSessionCookie = $this->getCookieData();
        if (empty($currentSessionCookie)) {
            return null;
        }
        $newCookieData = $this->updateSessionData($currentSessionCookie);
        $this->setCookie($newCookieData);
        return $newCookieData;
    }

    /**
     * @return null|array
     */
    public function getCookieData()
    {
        $cookieRememberMe = $this->request->getCookie($this->cookieName, null);
        if (false === isset($cookieRememberMe)) {
            return null;
        }
        $cookieData = unserialize($cookieRememberMe);
        if (empty($cookieData)) {
            return null;
        }
        $sessionData = array();
        $sessionData['member_id'] = (int)$cookieData['mi'];
        $sessionData['remember_me_id'] = $cookieData['u'];
        $sessionData['token'] = isset($cookieData['t']) ? $cookieData['t'] : null;
        return $sessionData;
    }

    /**
     * @param array $currentSessionData
     * @return null|array returns new session data
     */
    private function updateSessionData($currentSessionData)
    {
        if (false == isset($currentSessionData) OR (count($currentSessionData) == 0)) {
            return null;
        }
        $newSessionData = $this->createSessionData($currentSessionData['member_id']);

        $sql = "UPDATE `session` SET remember_me_id = :remember_new, expiry = FROM_UNIXTIME(:expiry_new), changed = NOW() WHERE member_id = :member_id AND remember_me_id = :remember_old";

        $result = $this->dataTable->getAdapter()->query($sql, array(
                'remember_new' => $newSessionData['remember_me_id'],
                'expiry_new' => $newSessionData['expiry'],
                'member_id' => $currentSessionData['member_id'],
                'remember_old' => $currentSessionData['remember_me_id']
            )
        );
        if ($result->rowCount() > 0) {
            return $newSessionData;
        } else {
            return null;
        }
    }

    public function hasValidCookie()
    {
        $sessionCookieData = $this->getCookieData();
        return $this->validateCookieData($sessionCookieData);
    }

    protected function validateCookieData($currentCookie)
    {
        if (empty($currentCookie)) {
            return false;
        }
        if (empty($currentCookie['token'])) {
            return false;
        }
        if (empty($currentCookie['member_id']) OR (false == is_int($currentCookie['member_id']))) {
            return false;
        }
        if (empty($currentCookie['remember_me_id'])) {
            return false;
        }
        $cookieToken = base64_decode($currentCookie['token']);
        $validateToken = hash('sha256', $currentCookie['member_id'] . $currentCookie['remember_me_id'] . $this->salt);
        if ($cookieToken != $validateToken) {
            return false;
        }
        return true;
    }

    public function deleteSession()
    {
        $currentSessionCookie = $this->getCookieData();
        if (empty($currentSessionCookie)) {
            return;
        }
        $this->removeSessionData($currentSessionCookie);
        $this->deleteCookie();
    }

    /**
     * @param array $currentSessionCookie
     * @return bool
     */
    protected function removeSessionData($currentSessionCookie)
    {
        $sql = "DELETE FROM `session` WHERE member_id = :member_id AND remember_me_id = :uuid";

        $result = $this->dataTable->getAdapter()->query($sql,
            array(
                'member_id' => $currentSessionCookie['member_id'],
                'uuid' => $currentSessionCookie['remember_me_id']
            ));
        if ($result->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    protected function deleteCookie()
    {
        $domain = $this->request->getHttpHost();
        $cookieExpire = time() - $this->cookieTimeout;

        setcookie($this->cookieName, null, $cookieExpire, '/', $domain, null, true);
    }

} 