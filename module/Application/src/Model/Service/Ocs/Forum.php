<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Model\Service\Ocs;


use Application\Model\Entity\CurrentUser;
use Application\Model\Interfaces\MemberDeactivationLogInterface;
use Application\Model\Repository\MemberDeactivationLogRepository;
use ArrayObject;
use DateInterval;
use DateTime;
use Exception;
use Laminas\Config\Config;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Json\Decoder;
use Laminas\Json\Json;

class Forum
{
    protected $config;
    protected $messages;
    protected $httpClient;
    protected $isRateLimitError;
    protected $rateLimitWaitSeconds;
    /**
     * @var Adapter
     */
    private $db;
    /**
     * @var MemberDeactivationLogRepository
     */
    private $member_deactivation_log;

    /**
     * @inheritDoc
     *
     * @param Config $config
     *
     * @throws Exception
     */
    public function __construct(
        Config $config,
        AdapterInterface $db,
        MemberDeactivationLogInterface $member_deactivation_log
    ) {
        if (isset($config)) {
            $this->config = $config;
        } else {
            throw new Exception('config missing');
        }
        $uri = $this->config->host;
        $this->httpClient = $this->getHttpClient($uri);
        $this->db = $db;
        $this->member_deactivation_log = $member_deactivation_log;
    }

    /**
     * @param $uri
     *
     * @return Client
     * @throws ServerException
     */
    protected function getHttpClient($uri)
    {
        try {
            if (empty($uri)) {
                return new Client(null, array('keepalive' => true, 'strictredirects' => true));
            }

            return new Client($uri, array('keepalive' => true, 'strictredirects' => true));
        } catch (Exception $e) {
            throw new ServerException('Can not create http client for uri: ' . $uri, 0, $e);
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
        } catch (Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }
        $uid = $data['username'];

        if (empty($user)) {
            try {
                $uri = $this->config->host . "/users";
                $method = Request::METHOD_POST;
                $result = $this->httpRequest($uri, $uid, $method, $data);
                if (false === $result) {
                    $this->messages[] = "Fail ";

                    return false;
                }
            } catch (Exception $e) {
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
                $method = Request::METHOD_PUT;
                $result = $this->httpRequest($uri, $uid, $method, $data);
            } catch (Exception $e) {
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
            'name'        => (false == empty($user['lastname'])) ? trim($user['firstname'] . ' ' . $user['lastname']) : $user['username'],
            'email'       => $paramEmail,
            'password'    => $user['password'],
            'username'    => strtolower($user['username']),
            'active'      => $user['is_active'] ? true : false,
            'approved'    => (false == empty($user['email_checked'])) ? true : false,
            'user_fields' => array('2' => $user['external_id']),
        );

        return $data;
    }

    /**
     * @param $external_uid
     * @param $username
     *
     * @return array|null
     * @throws ServerException
     */
    private function getUser($external_uid, $username)
    {
        $user_by_uid = $this->getUserByExternUid($external_uid);
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
     * @throws ServerException
     */
    public function getUserByExternUid($extern_uid)
    {
        $uri = $this->config->host . "/u/by-external/{$extern_uid}.json";
        $method = Request::METHOD_GET;
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
     * @throws ServerException
     */
    protected function httpRequest($uri, $uid, $method = Request::METHOD_GET, $post_param = null)
    {
        $this->isRateLimitError = false;
        $this->rateLimitWaitSeconds = 0;

        $this->httpClient->resetParameters();
        try {
            $this->httpClient->setUri($uri);
            $this->httpClient->setHeaders(array('User-Agent' => $this->config->user_agent));
            $this->httpClient->setMethod($method);
        } catch (Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') httpClient error message: ' . $e->getMessage();

            return false;
        }
        $this->httpClient->setParameterGet(
            array(
                'api_key'      => $this->config->private_token,
                'api_username' => $this->config->user_sudo,
            )
        );
        if (isset($post_param)) {
            $this->httpClient->setParameterPost($post_param);
        }

        try {
            $response = $this->httpClient->send();
        } catch (Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') httpClient error message: ' . $e->getMessage();

            return false;
        }
        if ($response->getStatusCode() < 200 or $response->getStatusCode() >= 500) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS Forum server send message: ' . $response->getBody();

            return false;
        }

        $body = $response->getBody();
        if (substr($body, 0, strlen('<html>')) === '<html>') {
            $this->messages[] = $body;

            return false;
        }
        try {
            $body = Decoder::decode($body, Json::TYPE_ARRAY);
        } catch (Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') Zend_Json::decode error message: ' . $e->getMessage();

            return false;
        }

        if (empty($body)) {
            return array('message' => 'empty body received');
        }

        if (array_key_exists("error_type", $body) or array_key_exists("errors", $body)) {
            $this->messages[] = "id: {$uid} ($uri) - " . $response->getBody();

            if (isset($body['error_type']) and ($body['error_type'] == "rate_limit")) {

                $this->isRateLimitError = true;
                $this->rateLimitWaitSeconds = $body['extras']['wait_seconds'];

                throw new ServerException($body['errors'][0]);
            }

            return false;
        }

        if (array_key_exists('success', $body) and $body['success'] == false) {
            $this->messages[] = "id: {$uid} ($uri) - " . $body['message'];

            return false;
        }

        return $body;
    }

    /**
     * @param string $username
     *
     * @return bool|array
     * @throws ServerException
     */
    public function getUserByUsername($username)
    {
        $encoded_username = urlencode($username);
        $uri = $this->config->host . "/users/{$encoded_username}.json";
        $method = Request::METHOD_GET;
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
     * @throws ServerException
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
        $method = Request::METHOD_GET;
        $uid = 'external_id';

        $notifications = $this->httpRequest($uri, $uid, $method);

        if (false === $notifications) {
            return false;
        }

        return $notifications;
    }

    /**
     * @param      $member_id
     * @param bool $onlyActive
     *
     * @return array|ArrayObject
     * @throws ServerException
     */
    private function getMemberData($member_id, $onlyActive = true)
    {
        $onlyActiveFilter = '';
        if ($onlyActive) {
            $onlyActiveFilter = " AND `m`.`is_active` = 1 AND `m`.`is_deleted` = 0 AND `me`.`email_checked` IS NOT NULL AND `me`.`email_deleted` = 0";
        }
        $sql = "
            SELECT `mei`.`external_id`,`m`.`member_id`, `m`.`username`, `me`.`email_address`, `m`.`password`, `m`.`roleId`, `m`.`firstname`, `m`.`lastname`, `m`.`profile_image_url`, `m`.`biography`, `m`.`created_at`, `m`.`changed_at`, `m`.`source_id`
            FROM `member` AS `m`
            LEFT JOIN `member_email` AS `me` ON `me`.`email_member_id` = `m`.`member_id` AND `me`.`email_primary` = 1
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE `m`.`member_id` = :memberId {$onlyActiveFilter}
            ORDER BY `m`.`member_id` DESC
        ";

        $result = $this->db->query($sql, array('memberId' => $member_id))->current();
        if (count($result) == 0) {
            throw new ServerException('member with id ' . $member_id . ' could not found.');
        }

        return $result;
    }

    /**
     * @param string $email
     *
     * @return bool|array
     * @throws ServerException
     */
    public function getUserByEmail($email)
    {
        $uri = $this->config->host . "/admin/users/list/all.json?email={$email}";
        $method = Request::METHOD_GET;

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
     * @throws ServerException
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
        $method = Request::METHOD_DELETE;
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
        $method = Request::METHOD_GET;

        try {
            $result = $this->httpRequest($uri, $uid, $method);
        } catch (Exception $e) {
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
        $method = Request::METHOD_POST;
        $data = array(
            "group[name]" => $name,
        );

        try {
            $result = $this->httpRequest($uri, $name, $method, $data);
        } catch (Exception $e) {
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

    /**
     * @param $group_id
     *
     * @return array|bool
     */
    public function deleteGroup($group_id)
    {
        $uri = $this->config->host . '/admin/groups/' . $group_id . '.json';
        $method = Request::METHOD_DELETE;

        try {
            $result = $this->httpRequest($uri, $group_id, $method);
        } catch (Exception $e) {
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
     * @param $groupname
     * @param $members
     *
     * @throws ServerException
     */
    public function addGroupMember($groupname, $members)
    {
        throw new ServerException('not implemented');
    }

    /**
     * @param $member_data
     * @param $oldUsername
     *
     * @return array|bool|null
     * @throws ServerException
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
            $method = Request::METHOD_PUT;
            $this->httpRequest($uri, $uid, $method, $data);
        } catch (Exception $e) {
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
     * @throws ServerException
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
        $method = Request::METHOD_DELETE;
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
     * @throws ServerException
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
        $method = Request::METHOD_PUT;
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
        } catch (Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        $this->messages[] = 'Forum user suspended: ' . json_encode($user);

        /** @var CurrentUser $current_user */
        $current_user = $GLOBALS['ocs_user'];
        $this->member_deactivation_log->addLog(
            $member_data['member_id'], MemberDeactivationLogRepository::OBJ_TYPE_DISCOURSE_USER, $forum_member['user']['id'], $current_user->member_id
        );

        return $user;
    }

    /**
     * @param array $member_data
     *
     * @return bool
     * @throws ServerException
     */
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

            return false;
        }

        $this->messages[] = 'Forum user post deleted: ' . json_encode($posts);
        /** @var CurrentUser $current_user */
        $current_user = $GLOBALS['ocs_user'];

        if (array_key_exists('posts', $posts) && is_array($posts['posts'])) {
            foreach ($posts['posts'] as $id => $item) {
                $result = $this->deletePostFromUser($id);
                if (false === $result) {
                    continue;
                }
                $this->member_deactivation_log->addLog(
                    $member_data['member_id'], MemberDeactivationLogRepository::OBJ_TYPE_DISCOURSE_POST, $id, $current_user->member_id
                );
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
                $this->member_deactivation_log->addLog(
                    $member_data['member_id'], MemberDeactivationLogRepository::OBJ_TYPE_DISCOURSE_TOPIC, $id, $current_user->member_id
                );
            }
        }

        return true;
    }

    /**
     * @param array $member_data
     *
     * @return array|bool
     * @throws ServerException
     */
    public function getPostsFromUser($member_data)
    {
        if (empty($member_data)) {
            return false;
        }

        if (is_int($member_data)) {
            $member_data = $this->getMemberData($member_data, false);
        }

        $username = substr($member_data['username'], 0, 20);
        $uri = $this->config->host . "/user_actions.json?offset=0&username={$username}&filter=4,5&no_results_help_key=user_activity.no_default";
        $method = Request::METHOD_GET;
        $uid = $member_data['member_id'];

        $result = $this->httpRequest($uri, $uid, $method);

        if (false === is_array($result)) {
            return false;
        }

        if (empty($result)) {
            return false;
        }

        $posts = array();

        if (array_key_exists('user_actions', $result)) {
            $actions = $result['user_actions'];
            if (is_array($actions)) {
                foreach ($actions as $user_action) {
                    if ($user_action['action_type'] == 4) {
                        $posts['topics'][$user_action['topic_id']] = $user_action;
                    }
                    if ($user_action['action_type'] == 5) {
                        $posts['posts'][$user_action['post_id']] = $user_action;
                    }
                }
            } else {
                $this->messages[] = "Fail. user_actions not an array: {$actions}";
            }
        } else {
            $this->messages[] = "Fail. No user_actions for user {$member_data['username']} received";
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
        $method = Request::METHOD_DELETE;
        $uid = $post_id;

        try {
            $result = $this->httpRequest($uri, $uid, $method);
        } catch (Exception $e) {
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
        $method = Request::METHOD_DELETE;
        $uid = $topic_id;

        try {
            $result = $this->httpRequest($uri, $uid, $method);
        } catch (Exception $e) {
            $this->httpClient->getAdapter()->close();
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        return $result;
    }

    /**
     * @param array $member_data
     *
     * @return array|bool
     * @throws ServerException
     */
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
        $method = Request::METHOD_PUT;
        $uid = $member_data['member_id'];
        $suspend_until = new DateTime();
        $suspend_until->add(new DateInterval('PT5M'));
        $data = array(
            'silenced_till' => $suspend_until->format(DateTime::ATOM),
            "reason"        => "probably a spam user",
            "post_action"   => "delete",
        );

        $user = $this->httpRequest($uri, $uid, $method, $data);

        $this->messages[] = 'Forum user silenced: ' . json_encode($user);

        return $user;
    }

    /**
     * @param array $member_data
     *
     * @return array|bool
     * @throws ServerException
     */
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
        $method = Request::METHOD_PUT;
        $uid = $member_data['member_id'];

        try {
            $user = $this->httpRequest($uri, $uid, $method);
            if (false === $user) {
                $this->messages[] = "Fail " . json_encode($this->messages);

                return false;
            }
        } catch (Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        $this->messages[] = 'Forum user unsuspend: ' . json_encode($user);

        $this->member_deactivation_log->deleteLog(
            $member_data['member_id'], MemberDeactivationLogRepository::OBJ_TYPE_DISCOURSE_USER, $forum_member['user']['id']
        );

        return $user;
    }

    /**
     * @param array|int $member_data
     *
     * @return bool
     * @throws ServerException
     */
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
        $deletedPosts = $this->member_deactivation_log->getLogForumPosts($member_data['member_id']);

        foreach ($deletedPosts['topics'] as $deleted_post) {
            $result = $this->undeleteTopicFromUser($deleted_post['object_id']);
            if (false === $result) {
                continue;
            }
            $this->messages[] = 'Forum user topic undeleted: ' . json_encode($deleted_post['object_id']);
            $this->member_deactivation_log->deleteLog(
                $member_data['member_id'], MemberDeactivationLogRepository::OBJ_TYPE_DISCOURSE_TOPIC, $deleted_post['object_id']
            );
        }
        foreach ($deletedPosts['posts'] as $deleted_post) {
            $result = $this->undeletePostFromUser($deleted_post['object_id']);
            if (false === $result) {
                continue;
            }
            $this->messages[] = 'Forum user post undeleted: ' . json_encode($deleted_post['object_id']);
            $this->member_deactivation_log->deleteLog(
                $member_data['member_id'], MemberDeactivationLogRepository::OBJ_TYPE_DISCOURSE_POST, $deleted_post['object_id']
            );
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
        $method = Request::METHOD_PUT;
        $uid = $topic_id;

        try {
            $result = $this->httpRequest($uri, $uid, $method);
        } catch (Exception $e) {
            $this->httpClient->getAdapter()->close();
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        return $result;
    }

    /**
     * @param int $post_id
     *
     * @return array|bool
     */
    public function undeletePostFromUser($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        $uri = $this->config->host . '/posts/' . $post_id . '/recover';
        $method = Request::METHOD_PUT;
        $uid = $post_id;

        try {
            $result = $this->httpRequest($uri, $uid, $method);
        } catch (Exception $e) {
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

    /**
     * @param int $id
     *
     * @return array|bool
     */
    public function getTopic($id)
    {
        if (empty($id)) {
            return false;
        }

        $uri = $this->config->host . '/t/' . $id . '.json';
        $method = Request::METHOD_GET;
        $uid = $id;

        try {
            $result = $this->httpRequest($uri, $uid, $method);
        } catch (Exception $e) {
            $this->httpClient->getAdapter()->close();
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        return $result;
    }

}