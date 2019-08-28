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
class Default_Model_Ocs_OAuth
{
    protected $messages;
    private $httpServer;

    /**
     * @inheritDoc
     */
    public function __construct($config = null)
    {
        $this->messages = array();
        if (isset($config)) {
            $this->config = $config;
        } else {
            $this->config = Zend_Registry::get('config')->settings->server->oauth;
        }
        $this->httpServer = new Default_Model_Ocs_HttpTransport_OAuthServer($this->config);
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

        return $this->httpServer->pushHttpUserData($data);
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

        if (is_int($member)) {
            $member = $this->getUserData($member);
        }

        $result = $this->createUserFromArray($member, $force = true);

        return $result;
    }

    /**
     * @param array $member_data
     * @param bool  $force
     *
     * @return bool|array
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function createUserFromArray($member_data, $force = false)
    {
        if (empty($member_data)) {
            return false;
        }

        $this->messages = array();
        $this->httpServer->resetMessages();

        $data = $this->mapData($member_data, $bypassEmailCheck = false, $bypassUsernameCheck = false);
        $uid = $member_data['member_id'];
        $user = $this->getUser($member_data['external_id']);

        if (empty($user)) {
            try {
                $method = Zend_Http_Client::POST;
                $uri = $this->config->host . "/api/v2/users/create";
                $result = $this->httpServer->httpRequest($uri, $uid, $method, $data);
                if (false === $result) {
                    $this->messages[] = $this->httpServer->getMessages();
                    $this->messages[] = "Fail ";

                    return false;
                }
            } catch (Zend_Exception $e) {
                $this->messages[] = $this->httpServer->getMessages();
                $this->messages[] = "Fail : " . $e->getMessage();

                return false;
            }
            $this->messages[] = $this->httpServer->getMessages();
            $this->messages[] = "Create : Success";

            return $result;
        }
        if ($force === true) {
            try {
                $uri = $this->config->host . "/api/v2/users/update";
                $method = Zend_Http_Client::PUT;
                $user = $this->httpServer->httpRequest($uri, $uid, $method, $data);
            } catch (Zend_Exception $e) {
                $this->messages[] = "Fail : " . $e->getMessage();

                return false;
            }
            $this->messages[] = $this->httpServer->getMessages();
            $this->messages[] = "Overwritten : " . json_encode($user);

            return $user;
        }

        $this->messages[] = 'Fail : user already exists.';

        return false;
    }

    /**
     * @param array $member_data
     * @param bool  $bypassEmailCheck
     * @param bool  $bypassUsernameCheck
     *
     * @return array
     */
    private function mapData($member_data, $bypassEmailCheck = false, $bypassUsernameCheck = false)
    {
        if (strpos($member_data['profile_image_url'], 'http') === false) {
            $urlImage = IMAGES_MEDIA_SERVER . '/img/' . $member_data['profile_image_url'];
        } else {
            $urlImage = $member_data['profile_image_url'];
        }

        $map_user_data = array(
            'user' => array(
                'id'             => $member_data['external_id'],
                'ocs_user_id'    => $member_data['member_id'],
                'username'       => trim($member_data['username']),
                'password'       => $member_data['password'],
                'email'          => trim($member_data['mail']),
                'emailVerified'  => empty($member_data['mail_checked']) ? 'false' : 'true',
                'is_hive'        => $member_data['password_type'] == 0 ? 'false' : 'true',
                'creationTime'   => strtotime($member_data['created_at']),
                'lastUpdateTime' => strtotime($member_data['changed_at']),
                'avatarUrl'      => $urlImage,
                'biography'      => empty($member_data['biography']) ? '' : $member_data['biography'],
                'admin'          => $member_data['roleId'] == 100 ? 'true' : 'false',
            )
        );

        if ((false == $member_data['is_active']) OR (true == $member_data['is_deleted'])) {
            $map_user_data['user']['disabledReason'] = 'user account disabled';
        }

        $map_user_data['options'] = array(
            'bypassEmailCheck'    => $bypassEmailCheck ? 'true' : 'false',
            'bypassUsernameCheck' => $bypassUsernameCheck ? 'true' : 'false'
        );

        return $map_user_data;
    }

    /**
     * @param string $extern_uid
     *
     * @return bool|array
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function getUser($extern_uid)
    {
        $uri = $this->config->host . "/api/v2/users/{$extern_uid}";
        $method = Zend_Http_Client::GET;
        $uid = 'external_id';

        $user = $this->httpServer->httpRequest($uri, $uid, $method);

        if (false === $user) {
            return false;
        }

        return $user;
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
    public function deleteUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $user = $this->getUserData($member_id);
        $uid = $user['member_id'];
        $id = $user['external_id'];

        try {
            $method = Zend_Http_Client::DELETE;
            $uri = $this->config->host . "/api/v2/users/{$id}";
            $result = $this->httpServer->httpRequest($uri, $uid, $method);
            $this->messages[] = print_r($this->httpServer->getMessages(), true);
            $this->messages[] = "server response:" . is_array($result) ? print_r($result, true) : $result;

            if (false === $result) {

                return false;
            }
        } catch (Zend_Exception $e) {
            $this->messages[] = $this->httpServer->getMessages();
            $this->messages[] = "Fail : " . $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    public function updateUserFromArray($member_data)
    {
        if (empty($member_data)) {
            return false;
        }

        $this->messages = array();

        $data = $this->mapUserData($member_data);
        $options = array('bypassEmailCheck' => 'true', 'bypassUsernameCheck' => 'true', 'update' => 'true');

        try {
            $this->httpServer->pushHttpUserData($data, $options);
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }
        $this->messages[] = $this->httpServer->getMessages();

        return $data;
    }

    public function validateUser($member_data)
    {
        if (empty($member_data)) {
            return false;
        }

        $result = false;
        $this->messages = array();

        try {
            $authUser = $this->getUser($member_data['external_id']);
            if (false === $authUser) {
                $this->messages[] = "Not Found : " . $member_data['member_id'];

                return false;
            }
            $result = $this->sameUserData($member_data, $authUser);
            if (false === $result) {
                $this->messages[] = "Unequal : " . print_r($authUser, true);
            }
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail : " . $e->getMessage();

            return false;
        }

        return $result;
    }

    /**
     * @param array $user
     * @param array $authUser
     *
     * @return bool
     */
    private function sameUserData($user, $authUser)
    {
        if (strpos($user['profile_image_url'], 'http') === false) {
            $urlImage = IMAGES_MEDIA_SERVER . '/img/' . $user['profile_image_url'];
        } else {
            $urlImage = $user['profile_image_url'];
        }

        $result = false;
        $result = $result || ($user['member_id'] != $authUser['ocsId']);
        $result = $result || ($user['username'] != $authUser['username']);
        $result = $result || ($user['password'] != $authUser['password']);
        $result = $result || (strtolower($user['mail']) != $authUser['email']);
        $result = $result || ($user['mail_checked'] != $authUser['emailVerified']);
        $result = $result || (($user['password_type'] == 1) != $authUser['hiveImport']);
        $result = $result || ($urlImage != $authUser['avatarUrl']);
        $result = $result || ($user['biography'] != $authUser['biography']);
        $result = $result || (($user['roleId'] == 100) != $authUser['admin']);
        $result = $result || (($user['is_active'] == 0) != $authUser['disabled']);

        return !$result;
    }

}