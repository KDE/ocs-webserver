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
 * Created: 10.09.2018
 */
class Default_Model_Ocs_Forum
{
    protected $config;
    protected $messages;
    protected $httpClient;

    /**
     * @inheritDoc
     */
    public function __construct($config = null)
    {
        if (isset($config)) {
            $this->config = $config;
        } else {
            $this->config = Zend_Registry::get('config')->settings->server->forum;
        }
        $uri = $this->config->host;
        $this->httpClient = new Zend_Http_Client($uri, array('keepalive' => true, 'strictredirects' => true));
    }

    /**
     * @param      $member_data
     *
     * @param bool $force
     *
     * @return array|bool
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

        $data = $this->mapUserData($member_data);

        $user = $this->getUser($member_data['external_id'], $member_data['username']);
        $uid = $data['username'];

        if (empty($user)) {
            try {
                $uri = $this->config->host . "/users";
                $method = Zend_Http_Client::POST;
                $result = $this->httpRequest($uri, $uid, $method, $data);
                if (false === $result) {
                    $this->messages[] = "Fail ";

                    return false;
                }
            } catch (Zend_Exception $e) {
                $this->messages[] = "Fail " . $e->getMessage();

                return false;
            }
            $this->messages[] = "Success";

            return $data;
        }

        if ($force === true) {
            unset($data['password']);

            try {
                $uri = $this->config->host . "/users/{$id}.json";
                $method = Zend_Http_Client::PUT;
                $this->httpRequest($uri, $uid, $method, $data);
            } catch (Zend_Exception $e) {
                $this->messages[] = "Fail " . $e->getMessage();

                return false;
            }
            $this->messages[] = "overwritten : " . json_encode($user);

            return $user;
        }

        $this->messages[] = 'user already exists.';

        return false;
    }

    /**
     * @param array $user
     *
     * @return array
     */
    protected function mapUserData($user)
    {
        $paramEmail = '';
        if (isset($user['email_address'])) {
            $paramEmail = $user['email_address'];
        } else if (isset($user['mail'])) {
            $paramEmail = $user['mail'];
        }

        $data = array(
            'name'        => (false == empty($user['lastname'])) ? trim($user['firstname'] . ' ' . $user['lastname'])
                : $user['username'],
            'email'       => $paramEmail,
            'password'    => $user['password'],
            'username'    => strtolower($user['username']),
            'active'      => $user['is_active'] ? true : false,
            'approved'    => (false == empty($user['email_checked'])) ? true : false,
            'user_fields' => array('2' => $user['external_id'])
        );

        return $data;
    }

    /**
     * @param $extern_uid
     * @param $username
     *
     * @return array|null
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    private function getUser($extern_uid, $username)
    {
        $user_by_uid = $this->getUserByExternUid($extern_uid);
        $user_by_dn = $this->getUserByUsername($username);

        if (empty($user_by_uid) AND empty($user_by_dn)) {
            return null;
        }

        if (!empty($user_by_uid) AND empty($user_by_dn)) {
            return $user_by_uid;
        }

        if (empty($user_by_uid) AND !empty($user_by_dn)) {
            return $user_by_dn;
        }

        return $user_by_uid;
    }

    /**
     * @param string $extern_uid
     *
     * @return bool|array
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function getUserByExternUid($extern_uid)
    {
        $uri = $this->config->host . "/u/by-external/{$extern_uid}.json";
        $method = Zend_Http_Client::GET;
        $uid = 'external_id';

        $user = $this->httpRequest($uri, $uid, $method);

        if (false === $user) {
            return false;
        }

        return $user;
    }

    /**
     * @param string $uri
     * @param string $uid
     * @param string $method
     * @param null   $post_param
     *
     * @return bool|array
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    protected function httpRequest($uri, $uid, $method = Zend_Http_Client::GET, $post_param = null)
    {
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setParameterGet('api_key', $this->config->private_token);
        $this->httpClient->setParameterGet('api_username', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod($method);
        if (isset($post_param)) {
            $this->httpClient->setParameterPost($post_param);
        }

        $response = $this->httpClient->request();
        if ($response->getStatus() < 200 OR $response->getStatus() >= 500) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS Forum server send message: ' . $response->getBody();

            return false;
        }

        $body = Zend_Json::decode($response->getBody());

        if (array_key_exists("error_type", $body) OR array_key_exists("errors", $body)) {
            $this->messages[] = "id: {$uid} ($uri) - " . $response->getBody();

            return false;
        }

        if (array_key_exists('success', $body) AND $body['success'] == false) {
            $this->messages[] = "id: {$uid} ($uri) - " . $body['message'];

            return false;
        }

        return $body;
    }

    /**
     * @param $username
     *
     * @return bool|array
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function getUserByUsername($username)
    {
        $encoded_username = urlencode($username);
        $uri = $this->config->host . "/users/{$encoded_username}.json";
        $method = Zend_Http_Client::GET;
        $uid = $username;

        $user = $this->httpRequest($uri, $uid, $method);

        if (false === $user) {
            return false;
        }

        return $user;
    }

    /**
     * @param string $email
     *
     * @return bool|array
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function getUserByEmail($email)
    {
        $uri = $this->config->host . "/admin/users/list/all.json?email={$email}";
        $method = Zend_Http_Client::GET;

        $user = $this->httpRequest($uri, $email, $method);

        if (false === $user) {
            return false;
        }

        return $user[0];
    }

    /**
     * @return array|null
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param $member_id
     *
     * @return bool
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function deleteUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $member_data = $this->getMemberData($member_id, false);

        $user = $this->getUser($member_data['external_id'], $member_data['username']);

        if (empty($user)) {
            $this->messages[] = 'Nothing to delete. User not found. ' . $member_data['external_id'] . ', ' . $member_data['username'];

            return false;
        }

        return $this->httpUserDelete($user['id']);
    }

    /**
     * @param      $member_id
     * @param bool $onlyActive
     *
     * @return mixed
     * @throws Default_Model_Ocs_Exception
     */
    private function getMemberData($member_id, $onlyActive = true)
    {
        $onlyActiveFilter = '';
        if ($onlyActive) {
            $onlyActiveFilter =
                " AND `m`.`is_active` = 1 AND `m`.`is_deleted` = 0 AND `me`.`email_checked` IS NOT NULL AND `me`.`email_deleted` = 0";
        }
        $sql = "
            SELECT `mei`.`external_id`,`m`.`member_id`, `m`.`username`, `me`.`email_address`, `m`.`password`, `m`.`roleId`, `m`.`firstname`, `m`.`lastname`, `m`.`profile_image_url`, `m`.`biography`, `m`.`created_at`, `m`.`changed_at`, `m`.`source_id`
            FROM `member` AS `m`
            LEFT JOIN `member_email` AS `me` ON `me`.`email_member_id` = `m`.`member_id` AND `me`.`email_primary` = 1
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE `m`.`member_id` = :memberId {$onlyActiveFilter}
            ORDER BY `m`.`member_id` DESC
        ";

        $result = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql, array('memberId' => $member_id));
        if (count($result) == 0) {
            throw new Default_Model_Ocs_Exception('member with id ' . $member_id . ' could not found.');
        }

        return $result;
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    private function httpUserDelete($id)
    {
        $this->httpClient->resetParameters();
        $uri = $this->config->host . '/admin/users/' . $id . '.json';
        $this->httpClient->setUri($uri);
        $this->httpClient->setParameterGet('api_key', $this->config->private_token);
        $this->httpClient->setParameterGet('api_username', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::DELETE);

        $response = $this->httpClient->request();

        if (200 == $response->getStatus()) {
            $this->messages[] = ' - response : ' . $response->getBody() . " - user id: {$id}";

            return true;
        }

        if ($response->getStatus() < 200 AND $response->getStatus() >= 500) {
            throw new Default_Model_Ocs_Exception('delete user failed. response: ' . $response->getBody() . PHP_EOL
                . " - user id: {$id}");
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - request: ' . $uri);
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - response: ' . $response->getBody());

        $this->messages[] = ' - response : ' . $response->getBody() . " - user id: {$id}";

        return false;
    }

    /**
     * @return bool|array
     */
    public function getGroups()
    {
        $uri = $this->config->host . '/groups.json';
        $uid = 'get groups';
        $method = Zend_Http_Client::GET;

        try {
            $result = $this->httpRequest($uri, $uid, $method);
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        if (false === $result) {
            $this->messages[] = "Fail ";

            return false;
        }

        $this->messages[] = "Success";

        return $result;
    }

    /**
     * @param string $name
     *
     * @return bool|array
     */
    public function createGroup($name)
    {
        $uri = $this->config->host . '/admin/groups';
        $method = Zend_Http_Client::POST;
        $data = array(
            "group[name]" => $name
        );

        try {
            $result = $this->httpRequest($uri, $name, $method, $data);
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        if (false === $result) {
            $this->messages[] = "Fail ";

            return false;
        }

        $this->messages[] = "Success";

        return $result['basic_group'];
    }

    public function deleteGroup($group_id)
    {
        $uri = $this->config->host . '/admin/groups/' . $group_id . '.json';
        $method = Zend_Http_Client::DELETE;

        try {
            $result = $this->httpRequest($uri, $group_id, $method);
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        if (false === $result) {
            $this->messages[] = "Fail ";

            return false;
        }

        $this->messages[] = "Success";

        return $result;
    }

    public function addGroupMember($groupname, $members)
    {

    }

}