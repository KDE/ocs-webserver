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

class Local_Auth_AdapterFactory
{

    const LOGIN_INFINITY = 'infinity';
    const LOGIN_HIVE = 'encryptionHive01';
    const LOGIN_PLING = 'encryptionPling01';
    const LOGIN_DEFAULT = 'default';

    /**
     * @param null $userIdentity
     * @param null $loginMethod
     * @return Zend_Auth_Adapter_DbTable
     */
    public static function getAuthAdapter($userIdentity = null, $loginMethod = null)
    {
        if (empty($loginMethod)) {
            $loginMethod = self::findAlternativeMethod($userIdentity);
        }

        return self::createAuthAdapter($loginMethod);
    }

    protected static function findAlternativeMethod($identity)
    {
        $modelMember = new Default_Model_Member();
        $memberData = $modelMember->findActiveMemberByIdentity($identity);

        if (empty($memberData)) {
            return self::LOGIN_DEFAULT;
        }

        if ($modelMember->isHiveUser($memberData)) {
            return self::LOGIN_HIVE;
        }

        return self::LOGIN_DEFAULT;
    }

    protected static function createAuthAdapter($loginMethod)
    {
        switch ($loginMethod) {
            case self::LOGIN_INFINITY:
                $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('db'), 'session', 'uuid', 'member_id');
                break;

            case self::LOGIN_HIVE:
                $authAdapter = new Local_Auth_Adapter_Ocs(Zend_Registry::get('db'), 'member');
                $authAdapter->setEncryption(Local_Auth_Adapter_Ocs::SHA);
                break;

            case self::LOGIN_PLING:
            case self::LOGIN_DEFAULT:
            default:
                $authAdapter = new Local_Auth_Adapter_Ocs(Zend_Registry::get('db'), 'member');
                $authAdapter->setEncryption(Local_Auth_Adapter_Ocs::MD5);

                break;
        }

        return $authAdapter;
    }

}