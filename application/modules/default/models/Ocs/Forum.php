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
    protected $isRateLimitError;
    protected $rateLimitWaitSeconds;

    /**
     * @inheritDoc
     * @param array|null $config
     * @throws Default_Model_Ocs_Gitlab_Exception
     * @throws Zend_Exception
     */
    public function __construct($config = null)
    {
        if (isset($config)) {
            $this->config = $config;
        } else {
            $this->config = Zend_Registry::get('config')->settings->server->forum;
        }
        $uri = $this->config->host;
        $this->httpClient = $this->getHttpClient($uri);
    }

    /**
     * @param $uri
     * @return Zend_Http_Client
     * @throws Default_Model_Ocs_Gitlab_Exception
     */
    protected function getHttpClient($uri)
    {
        try {
            if (empty($uri)) {
                return new Zend_Http_Client(null, array('keepalive' => true, 'strictredirects' => true));
            }

            return new Zend_Http_Client($uri, array('keepalive' => true, 'strictredirects' => true));
        } catch (Zend_Exception $e) {
            throw new Default_Model_Ocs_Gitlab_Exception('Can not create http client for uri: ' . $uri, 0, $e);
        }
    }

    /**
     * @param array $member_data
     *
     * @param bool  $force
     *
     * @return array|bool
     */
    public function createUserFromArray($member_data, $force = false)
    {
        if (empty($member_data)) {
            return false;
        }

        $this->messages = array();

        $data = $this->mapUserData($member_data);

        try {
            $user = $this->getUser($member_data['external_id'], $member_data['username']);
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }
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
            $uid = $user['user']['username'];
            unset($data['password']);

            try {
                $uri = $this->config->host . "/users/{$uid}.json";
                $method = Zend_Http_Client::PUT;
                $result = $this->httpRequest($uri, $uid, $method, $data);
            } catch (Zend_Exception $e) {
                $this->messages[] = "Fail " . $e->getMessage();

                return false;
            }
            $this->messages[] = "overwritten : " . json_encode($result);

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
        } else {
            if (isset($user['mail'])) {
                $paramEmail = $user['mail'];
            }
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
     * @throws Zend_Exception
     */
    private function getUser($extern_uid, $username)
    {
        $user_by_uid = $this->getUserByExternUid($extern_uid);
        if (false === empty($user_by_uid)) {
            return $user_by_uid;
        }

        $user_by_dn = $this->getUserByUsername($username);
        if (false === empty($user_by_dn)) {
            return $user_by_dn;
        }

        return null;
    }

    /**
     * @param string $extern_uid
     *
     * @return bool|array
     * @throws Zend_Exception
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
     * @param string     $uri
     * @param string     $uid
     * @param string     $method
     * @param array|null $post_param
     *
     * @return bool|array
     * @throws Zend_Exception
     */
    protected function httpRequest($uri, $uid, $method = Zend_Http_Client::GET, $post_param = null)
    {
        $this->isRateLimitError = false;
        $this->rateLimitWaitSeconds = 0;

        $this->httpClient->resetParameters();
        try {
            $this->httpClient->setUri($uri);
            $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
            $this->httpClient->setMethod($method);
        } catch (Zend_Http_Client_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') httpClient error message: ' . $e->getMessage();

            return false;
        } catch (Zend_Uri_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') httpClient error message: ' . $e->getMessage();

            return false;
        }
        $this->httpClient->setParameterGet('api_key', $this->config->private_token);
        $this->httpClient->setParameterGet('api_username', $this->config->user_sudo);
        if (isset($post_param)) {
            $this->httpClient->setParameterPost($post_param);
        }

        try {
            $response = $this->httpClient->request();
        } catch (Zend_Http_Client_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') httpClient error message: ' . $e->getMessage();

            return false;
        }
        if ($response->getStatus() < 200 OR $response->getStatus() >= 500) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS Forum server send message: ' . $response->getBody();

            return false;
        }

        $body = $response->getRawBody();
        $content_encoding = $response->getHeader('Content-encoding');
        $transfer_encoding = $response->getHeader('Transfer-encoding');
        if ($transfer_encoding == 'chunked') {
            $body = Zend_Http_Response::decodeChunkedBody($body);
        }
        if ($content_encoding == 'gzip') {
            $body = Zend_Http_Response::decodeGzip($body);
        }
        if (substr($body, 0, strlen('<html>')) === '<html>') {
            $this->messages[] = $body;

            return false;
        }
        try {
            $body = Zend_Json::decode($body);
        } catch (Zend_Json_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') Zend_Json::decode error message: ' . $e->getMessage();

            return false;
        }

        if (empty($body)) {
            return array('message' => 'empty body received');
        }

        if (array_key_exists("error_type", $body) OR array_key_exists("errors", $body)) {
            $this->messages[] = "id: {$uid} ($uri) - " . $response->getBody();

            if (isset($body['error_type']) AND ($body['error_type'] == "rate_limit")) {

                $this->isRateLimitError = true;
                $this->rateLimitWaitSeconds = $body['extras']['wait_seconds'];

                throw new Zend_Exception($body['errors'][0]);
            }

            return false;
        }

        if (array_key_exists('success', $body) AND $body['success'] == false) {
            $this->messages[] = "id: {$uid} ($uri) - " . $body['message'];

            return false;
        }

        return $body;
    }

    /**
     * @param string $username
     *
     * @return bool|array
     * @throws Zend_Exception
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
     * @param string $member_id
     *
     * @return bool|array
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     */
    public function getUserNotifications($member_id)
    {
        $member_data = $this->getMemberData($member_id);
        $forum_member = $this->getUser($member_data['external_id'], $member_data['username']);
        if (empty($forum_member)) {
            return false;
        }
        $username = $forum_member['user']['username'];

        $uri = $this->config->host . "/notifications.json?username=" . $username;
        $method = Zend_Http_Client::GET;
        $uid = 'external_id';

        $user = $this->httpRequest($uri, $uid, $method);

        if (false === $user) {
            return false;
        }

        return $user;
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
     * @param string $email
     *
     * @return bool|array
     * @throws Zend_Exception
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
     */
    public function deleteUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $member_data = $this->getMemberData($member_id, false);
        if (empty($member_data)) {
            return false;
        }

        $forum_member = $this->getUser($member_data['external_id'], $member_data['username']);
        if (empty($forum_member)) {
            return false;
        }
        $uri = $this->config->host . '/admin/users/' . $forum_member['user']['id'] . '.json';
        $method = Zend_Http_Client::DELETE;
        $uid = $member_id;

        $user = $this->httpRequest($uri, $uid, $method);

        if (false === $user) {
            return false;
        }

        return $user;
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

    /**
     * @param $member_data
     * @param $oldUsername
     *
     * @return array|bool|null
     * @throws Zend_Exception
     */
    public function updateUserFromArray($member_data, $oldUsername)
    {
        if (empty($member_data)) {
            return false;
        }

        $this->messages = array();

        $data = $this->mapUserData($member_data);

        $user = $this->getUser($member_data['external_id'], $oldUsername);

        if (empty($user)) {
            $this->messages[] = "Fail ";

            return false;
        }

        $uid = $user['user']['username'];
        unset($data['password']);

        try {
            $uri = $this->config->host . "/users/{$uid}.json";
            $method = Zend_Http_Client::PUT;
            $this->httpRequest($uri, $uid, $method, $data);
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }
        $this->messages[] = "overwritten : " . json_encode($user);

        return $user;
    }

    /**
     * @param array $member_data
     *
     * @return array|bool
     * @throws Zend_Exception
     */
    public function deleteUserWithArray($member_data)
    {
        if (empty($member_data)) {
            return false;
        }

        $forum_member = $this->getUserByExternUid($member_data['external_id']);
        if (empty($forum_member)) {
            return false;
        }
        $uri = $this->config->host . '/admin/users/' . $forum_member['id'] . '.json';
        $method = Zend_Http_Client::DELETE;
        $uid = $member_data['member_id'];

        $user = $this->httpRequest($uri, $uid, $method);

        if (false === $user) {
            return false;
        }

        return $user;
    }

    /**
     * @param int|array $member_data
     *
     * @return array|bool
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     */
    public function blockUser($member_data)
    {
        if (is_int($member_data)) {
            $member_data = $this->getMemberData($member_data, false);
        }

        if (empty($member_data)) {
            return false;
        }

        $forum_member = $this->getUser($member_data['external_id'], $member_data['username']);
        if (empty($forum_member)) {
            return false;
        }

        $uri = $this->config->host . '/admin/users/' . $forum_member['user']['id'] . '/suspend';
        $method = Zend_Http_Client::PUT;
        $uid = $member_data['member_id'];
        $suspend_until = new DateTime();
        $suspend_until->add(new DateInterval('P1Y'));
        $data = array('suspend_until' => $suspend_until->format("Y-m-d"), "reason" => "");

        try {
            $user = $this->httpRequest($uri, $uid, $method, $data);
            if (false === $user) {
                $this->messages[] = "Fail " . json_encode($this->messages);

                return false;
            }
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        $this->messages[] = 'Forum user suspended: ' . json_encode($user);

        $memberLog = new Default_Model_MemberDeactivationLog();
        $memberLog->addLog($member_data['member_id'], Default_Model_MemberDeactivationLog::OBJ_TYPE_DISCOURSE_USER,
            $forum_member['user']['id']);

        return $user;
    }

    public function blockUserPosts($member_data)
    {
        if (is_int($member_data)) {
            $member_data = $this->getMemberData($member_data, false);
        }

        if (empty($member_data)) {
            return false;
        }

        $posts = $this->getPostsFromUser($member_data);

        if (false == $posts) {
            $this->messages[] = "Fail. No posts for user {$member_data['username']} received";
        }

        $memberLog = new Default_Model_MemberDeactivationLog();
        if (array_key_exists('posts', $posts) && is_array($posts['posts'])) {
            foreach ($posts['posts'] as $id => $item) {
                $result = $this->deletePostFromUser($id);
                if (false === $result) {
                    continue;
                }
                $this->messages[] = 'Forum user post deleted: ' . json_encode($id);
                $memberLog->addLog($member_data['member_id'],
                    Default_Model_MemberDeactivationLog::OBJ_TYPE_DISCOURSE_POST, $id);
            }
        }

        //handle topics
        if (array_key_exists('topics', $posts) && is_array($posts['topics'])) {
            foreach ($posts['topics'] as $id => $topic) {
                $result = $this->deleteTopicFromUser($id);
                if (false === $result) {
                    continue;
                }
                $this->messages[] = 'Forum user topic deleted: ' . json_encode($id);
                $memberLog->addLog($member_data['member_id'],
                    Default_Model_MemberDeactivationLog::OBJ_TYPE_DISCOURSE_TOPIC, $id);
            }
        }

        return true;
    }

    public function getPostsFromUser($member_data)
    {
        if (empty($member_data)) {
            return false;
        }

        if (is_int($member_data)) {
            $member_data = $this->getMemberData($member_data, false);
        }

        //$forum_member = $this->getUser($member_data['external_id'], $member_data['username']);
        //if (empty($forum_member)) {
        //    return false;
        //}

        $username = substr($member_data['username'], 0, 20);
        $uri = $this->config->host . "/user_actions.json?offset=0&username={$username}&filter=4,5&no_results_help_key=user_activity.no_default";
        $method = Zend_Http_Client::GET;
        $uid = $member_data['member_id'];

        $result = $this->httpRequest($uri, $uid, $method);

        if (false === is_array($result)) {
            return false;
        }

        $posts = array();

        foreach ($result['user_actions'] as $user_action) {
            if ($user_action['action_type'] == 4) {
                $posts['topics'][$user_action['topic_id']] = $user_action;
            }
            if ($user_action['action_type'] == 5) {
                $posts['posts'][$user_action['post_id']] = $user_action;
            }
        }

        return $posts;
    }

    /**
     * @param int $post_id
     *
     * @return array|bool
     */
    public function deletePostFromUser($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        $uri = $this->config->host . '/posts/' . $post_id;
        $method = Zend_Http_Client::DELETE;
        $uid = $post_id;

        try {
            $result = $this->httpRequest($uri, $uid, $method);
        } catch (Zend_Exception $e) {
            $this->httpClient->getAdapter()->close();
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        return $result;
    }

    /**
     * @param int $topic_id
     *
     * @return array|bool
     */
    public function deleteTopicFromUser($topic_id)
    {
        if (empty($topic_id)) {
            return false;
        }

        $uri = $this->config->host . '/t/' . $topic_id;
        $method = Zend_Http_Client::DELETE;
        $uid = $topic_id;

        try {
            $result = $this->httpRequest($uri, $uid, $method);
        } catch (Zend_Exception $e) {
            $this->httpClient->getAdapter()->close();
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        return $result;
    }

    public function silenceUser($member_data)
    {
        if (is_int($member_data)) {
            $member_data = $this->getMemberData($member_data, false);
        }

        if (empty($member_data)) {
            return false;
        }

        $forum_member = $this->getUser($member_data['external_id'], $member_data['username']);
        if (empty($forum_member)) {
            return false;
        }

        $uri = $this->config->host . '/admin/users/' . $forum_member['user']['id'] . '/silence';
        $method = Zend_Http_Client::PUT;
        $uid = $member_data['member_id'];
        $suspend_until = new DateTime();
        $suspend_until->add(new DateInterval('PT5M'));
        $data = array(
            'silenced_till' => $suspend_until->format(DateTime::ATOM),
            "reason"        => "probably a spam user",
            "post_action"   => "delete"
        );

        $user = $this->httpRequest($uri, $uid, $method, $data);

        $this->messages[] = 'Forum user silenced: ' . json_encode($user);

        return $user;
    }

    public function unblockUser($member_data)
    {
        if (empty($member_data)) {
            return false;
        }

        if (is_int($member_data)) {
            $member_data = $this->getMemberData($member_data, false);
        }

        $forum_member = $this->getUser($member_data['external_id'], $member_data['username']);
        if (empty($forum_member)) {
            return false;
        }

        $uri = $this->config->host . '/admin/users/' . $forum_member['user']['id'] . '/unsuspend';
        $method = Zend_Http_Client::PUT;
        $uid = $member_data['member_id'];

        try {
            $user = $this->httpRequest($uri, $uid, $method);
            if (false === $user) {
                $this->messages[] = "Fail " . json_encode($this->messages);

                return false;
            }
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        $this->messages[] = 'Forum user unsuspend: ' . json_encode($user);

        $memberLog = new Default_Model_MemberDeactivationLog();
        $memberLog->deleteLog($member_data['member_id'], Default_Model_MemberDeactivationLog::OBJ_TYPE_DISCOURSE_USER,
            $forum_member['user']['id']);

        return $user;
    }

    public function unblockUserPosts($member_data)
    {
        if (empty($member_data)) {
            return false;
        }

        if (is_int($member_data)) {
            $member_data = $this->getMemberData($member_data, false);
        }

        $forum_member = $this->getUser($member_data['external_id'], $member_data['username']);
        if (empty($forum_member)) {
            return false;
        }
        $memberLog = new Default_Model_MemberDeactivationLog();
        $deletedPosts = $memberLog->getLogForumPosts($member_data['member_id']);

        foreach ($deletedPosts['topics'] as $deleted_post) {
            $result = $this->undeleteTopicFromUser($deleted_post['object_id']);
            if (false === $result) {
                continue;
            }
            $this->messages[] = 'Forum user topic undeleted: ' . json_encode($deleted_post['object_id']);
            $memberLog->deleteLog($member_data['member_id'],
                Default_Model_MemberDeactivationLog::OBJ_TYPE_DISCOURSE_TOPIC, $deleted_post['object_id']);
        }
        foreach ($deletedPosts['posts'] as $deleted_post) {
            $result = $this->undeletePostFromUser($deleted_post['object_id']);
            if (false === $result) {
                continue;
            }
            $this->messages[] = 'Forum user post undeleted: ' . json_encode($deleted_post['object_id']);
            $memberLog->deleteLog($member_data['member_id'],
                Default_Model_MemberDeactivationLog::OBJ_TYPE_DISCOURSE_POST, $deleted_post['object_id']);
        }

        return true;
    }

    /**
     * @param int $topic_id
     *
     * @return array|bool
     */
    public function undeleteTopicFromUser($topic_id)
    {
        if (empty($topic_id)) {
            return false;
        }

        $uri = $this->config->host . '/t/' . $topic_id . '/recover';
        $method = Zend_Http_Client::PUT;
        $uid = $topic_id;

        try {
            $result = $this->httpRequest($uri, $uid, $method);
        } catch (Zend_Exception $e) {
            $this->httpClient->getAdapter()->close();
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        return $result;
    }

    public function undeletePostFromUser($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        $uri = $this->config->host . '/posts/' . $post_id . '/recover';
        $method = Zend_Http_Client::PUT;
        $uid = $post_id;

        try {
            $result = $this->httpRequest($uri, $uid, $method);
        } catch (Zend_Exception $e) {
            $this->httpClient->getAdapter()->close();
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function hasRateLimitError()
    {
        return $this->isRateLimitError;
    }

    /**
     * @return mixed
     */
    public function getRateLimitWaitSeconds()
    {
        return $this->rateLimitWaitSeconds;
    }

    public function getTopic($id)
    {
        if (empty($id)) {
            return false;
        }

        $uri = $this->config->host . '/t/' . $id . '.json';
        $method = Zend_Http_Client::GET;
        $uid = $id;

        try {
            $result = $this->httpRequest($uri, $uid, $method);
        } catch (Zend_Exception $e) {
            $this->httpClient->getAdapter()->close();
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        return $result;
    }

}