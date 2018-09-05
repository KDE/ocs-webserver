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
 * Created: 19.06.2018
 */
class Default_Model_Ocs_OpenId
{
    private $id_server;

    /**
     * @inheritDoc
     */
    public function __construct($id_server = null)
    {
        if (isset($id_server)) {
            $this->id_server = $id_server;
        } else {
            $config = Zend_Registry::get('config')->settings->server->oauth;
            $this->id_server = new Default_Model_Ocs_HttpTransport_OpenIdServer($config);
        }
    }

    /**
     * @param $member_id
     *
     * @return bool
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    public function createUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $user = $this->getUserData($member_id);
        $data = $this->mapUserData($user);

        return $this->id_server->pushHttpUserData($data);
    }

    /**
     * @param $member_id
     *
     * @return array
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Db_Statement_Exception
     */
    protected function getUserData($member_id)
    {
        $modelMember = new Default_Model_Member();
        $member = $modelMember->fetchMemberData($member_id, false)->toArray();

        if (empty($member)) {
            throw new Default_Model_Ocs_Exception('member with id ' . $member_id . ' could not found.');
        }

        $modelExternalId = new Default_Model_DbTable_MemberExternalId();
        $externalId = $modelExternalId->fetchRow(array("member_id = ?" => $member['member_id']));
        if (count($externalId->toArray()) > 0) {
            $member['external_id'] = $externalId->external_id;
        }

        return $member;
    }

    /**
     * @param array $user
     *
     * @return array
     */
    protected function mapUserData($user)
    {
        $data = array(
            'external_id'    => $user['external_id'],
            'ocs_user_id'    => $user['member_id'],
            'username'       => $user['username'],
            'password'       => $user['password'],
            'email'          => $user['mail'],
            'emailVerified'  => empty($user['mail_checked']) ? 'false' : 'true',
            'creationTime'   => strtotime($user['created_at']),
            'lastUpdateTime' => strtotime($user['changed_at']),
            'avatarUrl'      => $user['profile_image_url'],
            'biography'      => empty($user['biography']) ? '' : $user['biography'],
            'admin'          => $user['roleId'] == 100 ? 'true' : 'false',
            'is_hive'        => $user['password_type'] == 0 ? 'false' : 'true',
            'is_active'      => $user['is_active'],
            'is_deleted'     => $user['is_deleted']
        );

        return $data;
    }

    /**
     * @param $member_id
     *
     * @return bool
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    public function updateMailForUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $user = $this->getUserData($member_id);

        return $this->updateUser($user);
    }

    /**
     * @param array $member
     *
     * @return bool
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    public function updateUser($member)
    {
        if (empty($member)) {
            return false;
        }

        $data = $this->mapUserData($member);

        $options = array('bypassEmailCheck' => 'true', 'bypassUsernameCheck' => 'true', 'update' => 'true');

        return $this->id_server->pushHttpUserData($data, $options);
    }

    /**
     * @param $member_id
     *
     * @return bool
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    public function updateAvatarForUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $user = $this->getUserData($member_id);

        return $this->updateUser($user);
    }

    /**
     * @param $member_id
     *
     * @return bool
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    public function updatePasswordForUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $user = $this->getUserData($member_id);

        return $this->updateUser($user);
    }

    /**
     * @param $member_id
     *
     * @return bool
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    public function deactivateLoginForUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $user = $this->getUserData($member_id);

        return $this->updateUser($user);
    }

    /**
     * @param array $user
     * @param bool  $create
     *
     * @return bool
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    public function exportUser($user, $create = false)
    {
        if (empty($user)) {
            return false;
        }

        $data = $this->mapUserData($user);

        $options = array();
        if (false === $create) {
            $options = array('bypassEmailCheck' => 'true', 'bypassUsernameCheck' => 'true', 'update' => 'true');
        }

        return $this->id_server->pushHttpUserData($data, $options);
    }

}