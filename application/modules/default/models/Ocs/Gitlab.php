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
class Default_Model_Ocs_Gitlab
{
    /** @var  Zend_Cache_Core */
    protected $cache;
    protected $config;
    protected $messages;
    /** @var Zend_Http_Client */
    protected $httpClient;

    /**
     * @inheritDoc
     */
    public function __construct($config = null)
    {
        if (isset($config)) {
            $this->config = $config;
        } else {
            $this->config = Zend_Registry::get('config')->settings->server->opencode;
        }
        $uri = $this->config->host;
        $this->httpClient = $this->getHttpClient($uri);

        $this->cache = Zend_Registry::get('cache');
        $this->messages = array();
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
     * @param int|array $member_data
     *
     * @return bool
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     */
    public function unblockUserProjects($member_data)
    {
        if (is_int($member_data)) {
            $member_data = $this->getMemberData($member_data, false);
        }

        if (empty($member_data)) {
            return false;
        }

        $user = $this->getUser($member_data['external_id'], $member_data['username']);
        if (false === $user) {
            return false;
        }

        $uri = $this->config->host . "/api/v4/users/{$user['id']}/projects";
        $method = Zend_Http_Client::GET;
        $uid = $member_data['member_id'];
        try {
            $response = $this->httpRequest($uri, $uid, $method);
            if (false === $response) {
                $this->messages[] = "Fail ";

                return false;
            }
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        $memberLog = new Default_Model_MemberDeactivationLog();
        foreach ($response as $project) {
            $log_data =
                $memberLog->getLogEntries($member_data['member_id'],
                    Default_Model_MemberDeactivationLog::OBJ_TYPE_GITLAB_PROJECT,
                    $project['id']);
            if (false === $log_data) {
                continue;
            }
            $object_data = json_decode($log_data['object_data'], true);
            $visibility = $object_data['visibility'];

            $uri = $this->config->host . "/api/v4/projects/{$project['id']}";
            $method = Zend_Http_Client::PUT;
            $uid = $member_data['member_id'];
            $data = array('visibility' => $visibility);
            try {
                $response = $this->httpRequest($uri, $uid, $method, $data);
                if (false === $response) {
                    $this->messages[] = "Fail " . $uri;

                    continue;
                }
            } catch (Zend_Exception $e) {
                $this->messages[] = "Fail " . $e->getMessage();

                return false;
            }
            $this->messages[] = "Successful unblock user project: {$project['id']}";

            $memberLog->deleteLog($member_data['member_id'],
                Default_Model_MemberDeactivationLog::OBJ_TYPE_GITLAB_PROJECT,
                $project['id']);
        }

        return true;
    }

    /**
     * @param int  $member_id
     *
     * @param bool $onlyActive
     *
     * @return array
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
     * @param $extern_uid
     * @param $username
     *
     * @return array|false
     * @throws Zend_Exception
     */
    public function getUser($extern_uid, $username)
    {
        $user_by_uid = $this->getUserByExternUid($extern_uid);

        if (false === empty($user_by_uid)) {
            return $user_by_uid;
        }
        $this->messages[] = "external id not found. external_id: " . $extern_uid;

        $user_by_dn = $this->getUserByDN(urlencode($username));

        if (false === empty($user_by_dn)) {
            return $user_by_dn;
        }
        $this->messages[] = "ldap dn not found. username: " . $username;

        $user_by_name = $this->getUserWithName(urlencode($username));

        if (false === empty($user_by_name)) {
            return $user_by_name;
        }
        $this->messages[] = "username not found. username: " . $username;

        return false;
    }

    /**
     * @param string $extern_uid
     *
     * @return array
     * @throws Zend_Exception
     */
    public function getUserByExternUid($extern_uid)
    {
        $uri = $this->config->host . "/api/v4/users?extern_uid={$extern_uid}&provider=" . $this->config->provider_name;
        $body = $this->httpRequest($uri, $extern_uid);

        if (count($body) == 0) {
            return array();
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . " - body: " . $body);

        return $body[0];
    }

    /**
     * @param string     $uri
     * @param string     $uid
     * @param string     $method
     * @param array|null $post_param
     *
     * @return bool|array
     */
    protected function httpRequest($uri, $uid, $method = Zend_Http_Client::GET, $post_param = null)
    {
        $body = array();
        try {
            $this->httpClient->setUri($uri);
        } catch (Zend_Http_Client_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') setUri error message: ' . $e->getMessage();

            return false;
        } catch (Zend_Uri_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') setUri error message: ' . $e->getMessage();

            return false;
        }
        $this->httpClient->resetParameters();
        try {
            $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
            $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
            $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
            $this->httpClient->setMethod($method);
        } catch (Zend_Http_Client_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') setHeaders error message: ' . $e->getMessage();

            return false;
        }
        if (isset($post_param)) {
            $this->httpClient->setParameterPost($post_param);
        }

        try {
            $response = $this->httpClient->request();
        } catch (Zend_Http_Client_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') request error message: ' . $e->getMessage();

            return false;
        }
        if ($response->getStatus() < 200 OR $response->getStatus() >= 500) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS Forum server send message: ' . $response->getBody();

            return false;
        }

        try {
            $body = Zend_Json::decode($response->getBody());
        } catch (Zend_Json_Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') Zend_Json::decode error message: ' . $e->getMessage();
        }

        if ($body && is_array($body) && array_key_exists("message", $body)) {
            $this->messages[] = "id: {$uid} ($uri) - " . Zend_Json::encode($body["message"]);
        }

        return $body;
    }

    /**
     * @param $username
     *
     * @return array
     * @throws Zend_Exception
     */
    public function getUserByDN($username)
    {
        $user_id = $this->buildUserDn($username);
        $uri = $this->config->host . "/api/v4/users?extern_uid={$user_id}&provider=ldapmain";

        $body = $this->httpRequest($uri, $username);

        if (count($body) == 0) {
            return array();
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . " - body: " . $body);

        return $body[0];
    }

    /**
     * @param string $extern_uid
     *
     * @return string
     * @throws Zend_Exception
     */
    private function buildUserDn($extern_uid)
    {
        $username = mb_strtolower($extern_uid);
        $baseDn = Default_Model_Ocs_Ldap::getBaseDn();
        $dn = "cn={$username},{$baseDn}";

        return $dn;
    }

    /**
     * @param string $username
     *
     * @return array
     * @throws Zend_Exception
     */
    public function getUserWithName($username)
    {
        $uri = $this->config->host . "/api/v4/users?username=" . $username;
        $body = $this->httpRequest($uri, $username);

        if (count($body) == 0) {
            return array();
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . " - body: " . $body);

        return $body[0];
    }

    /**
     * @param array $member_data
     * @return bool
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     */
    public function blockUserProjects($member_data)
    {
        if (is_int($member_data)) {
            $member_data = $this->getMemberData($member_data, false);
        }

        if (empty($member_data)) {
            return false;
        }

        $user = $this->getUser($member_data['external_id'], $member_data['username']);
        if (false === $user) {
            return false;
        }

        $uri = $this->config->host . "/api/v4/users/{$user['id']}/projects";
        $method = Zend_Http_Client::GET;
        $uid = $member_data['member_id'];
        try {
            $response = $this->httpRequest($uri, $uid, $method);
            if (false === $response) {
                $this->messages[] = "Fail ";

                return false;
            }
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        foreach ($response as $project) {
            $memberLog = new Default_Model_MemberDeactivationLog();
            $memberLog->addLogData($member_data['member_id'],
                Default_Model_MemberDeactivationLog::OBJ_TYPE_GITLAB_PROJECT,
                $project['id'], json_encode($project));

            $uri = $this->config->host . "/api/v4/projects/{$project['id']}";
            $method = Zend_Http_Client::PUT;
            $uid = $member_data['member_id'];
            $data = array('visibility' => 'private');
            try {
                $response = $this->httpRequest($uri, $uid, $method, $data);
                if (false === $response) {
                    $this->messages[] = "Fail " . $uri;

                    continue;
                }
            } catch (Zend_Exception $e) {
                $this->messages[] = "Fail " . $e->getMessage();

                return false;
            }

            $this->messages[] = "Successful block user project: {$project['id']}";
        }

        return true;
    }

    /**
     * @param int|array $member_data
     *
     * @return bool
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

        $user = $this->getUser($member_data['external_id'], $member_data['username']);
        if (false === $user) {

            return false;
        }

        $uri = $this->config->host . "/api/v4/users/{$user['id']}/block";
        $method = Zend_Http_Client::POST;
        $uid = $member_data['member_id'];

        try {
            $response = $this->httpRequest($uri, $uid, $method);
            if (false === $response) {
                $this->messages[] = "Fail ";

                return false;
            }
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        $this->messages[] = "Successful block user: {$member_data['username']}";

        $memberLog = new Default_Model_MemberDeactivationLog();
        $memberLog->addLog($member_data['member_id'], Default_Model_MemberDeactivationLog::OBJ_TYPE_GITLAB_USER,
            $user['id']);

        return true;
    }

    /**
     * @param int|array $member_data
     *
     * @return bool
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     */
    public function unblockUser($member_data)
    {
        if (is_int($member_data)) {
            $member_data = $this->getMemberData($member_data, false);
        }

        if (empty($member_data)) {
            return false;
        }

        $user = $this->getUser($member_data['external_id'], $member_data['username']);
        if (false === $user) {
            return false;
        }

        $uri = $this->config->host . "/api/v4/users/{$user['id']}/unblock";
        $method = Zend_Http_Client::POST;
        $uid = $member_data['member_id'];

        try {
            $response = $this->httpRequest($uri, $uid, $method);
            if (false === $response) {
                $this->messages[] = "Fail ";

                return false;
            }
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        $this->messages[] = "Successful unblock user: {$member_data['username']}";

        $memberLog = new Default_Model_MemberDeactivationLog();
        $memberLog->deleteLog($member_data['member_id'], Default_Model_MemberDeactivationLog::OBJ_TYPE_GITLAB_USER,
            $user['id']);

        return true;
    }

    /**
     * @param int|array $member_data
     * @param string    $oldUsername
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

        $user = $this->getUser($data['extern_uid'], $oldUsername);

        if (false === $user) {
            $this->messages[] = "Fail";

            return false;
        }
        //        $data['skip_reconfirmation'] = 'true';
        //        unset($data['password']);

        try {
            foreach ($data as $datum) {
                $datum['skip_reconfirmation'] = 'true';
                unset($datum['password']);

                $this->httpUserUpdate($data, $user['id']);
            }
        } catch (Zend_Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }
        $this->messages[] = "overwritten : " . json_encode($user);

        return $user;
    }

    /**
     * @param array $user
     *
     * @return array
     * @throws Zend_Exception
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

        if (strlen($user['biography']) > 254) {
            $helperTruncate = new Default_View_Helper_Truncate();
            $bio = $helperTruncate->truncate($user['biography'], 250);
        } else {
            $bio = empty($user['biography']) ? '' : $user['biography'];
        }

        $data = array(
            array(
                'email'            => $paramEmail,
                'username'         => mb_strtolower($user['username']),
                //                'name'             => (false == empty($user['lastname'])) ? trim($user['firstname'] . ' ' . $user['lastname']) : $user['username'],
                'name'             => $user['username'],
                'password'         => $user['password'],
                'provider'         => $this->config->provider_name,
                'extern_uid'       => $user['external_id'],
                'bio'              => $bio,
                'admin'            => $user['roleId'] == 100 ? 'true' : 'false',
                'can_create_group' => 'true'
            ),
            array(
                'email'            => $paramEmail,
                'username'         => mb_strtolower($user['username']),
                //                'name'             => (false == empty($user['lastname'])) ? trim($user['firstname'] . ' ' . $user['lastname']) : $user['username'],
                'name'             => $user['username'],
                'password'         => $user['password'],
                'provider'         => "ldapmain",
                'extern_uid'       => $this->buildUserDn($user['username']),
                'bio'              => $bio,
                'admin'            => $user['roleId'] == 100 ? 'true' : 'false',
                'can_create_group' => 'true'
            )
        );

        return $data;
    }

    /**
     * @param $data
     *
     * @param $id
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Http_Exception
     */
    private function httpUserUpdate($data, $id)
    {
        $uri = $this->config->host . '/api/v4/users/' . $id;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::PUT);
        $this->httpClient->setParameterPost($data);

        $response = $this->httpClient->request();
        if ($response->getStatus() < 200 OR $response->getStatus() >= 300) {
            throw new Default_Model_Ocs_Exception('update user data failed. OCS OpenCode server send message: '
                                                  . $response->getRawBody());
        }

        $body = Zend_Json::decode($response->getRawBody());
        if (array_key_exists("message", $body)) {
            throw new Default_Model_Ocs_Exception($body["message"]);
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - request: ' . $uri);
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - response: ' . $response->getRawBody());

        return $body;
    }

    /**
     * @param string $email
     *
     * @return array
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function getUserByEmail($email)
    {
        $uri = $this->config->host . "/api/v4/users?search={$email}";
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::GET);

        $response = $this->httpClient->request();

        $body = Zend_Json::decode($response->getRawBody());

        if (count($body) == 0) {
            return array();
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new Default_Model_Ocs_Exception($body["message"]);
            }
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . " - body: " . $response->getRawBody());

        return $body;
    }

    /**
     * @param array $member
     * @param array $userSubsystem
     *
     * @return array
     * @throws Zend_Exception
     */
    public function validateUserData($member, $userSubsystem)
    {
        $userDn = $this->buildUserDn(strtolower($member['username']));
        $result = array();

        if (mb_strtolower($member['email_address']) != $userSubsystem['email']) {
            $result[] = 'email_address<=>email';
            $result[] = $member['email_address'] . '<=>' . $userSubsystem['email'];
        }
        if (mb_strtolower($member['username']) != $userSubsystem['username']) {
            $result[] = 'username<=>username';
            $result[] = mb_strtolower($member['username']) . '<=>' . $userSubsystem['username'];
        }
        if ($member['username'] != $userSubsystem['name']) {
            $result[] = 'username<=>name';
            $result[] = $member['username'] . '<=>' . $userSubsystem['name'];
        }
        if (($member['roleId'] == 100 ? true : false) != $userSubsystem['is_admin']) {
            $result[] = 'roleId<=>admin';
            $result[] = $member['roleId'] . '<=>' . $userSubsystem['admin'];
        }
        if ("active" != $userSubsystem['state']) {
            $result[] = 'is_active<=>state';
            $result[] = ($member['is_active'] ? 'active' : 'inactive') . '<=>' . $userSubsystem['state'];
        }
        if (false === array_search('oauth_opendesktop', array_column($userSubsystem['identities'], 'provider'))) {
            $result[] = 'oauth_opendesktop missing';
        }
        $provider = array_column($userSubsystem['identities'], 'extern_uid', 'provider');
        $providerOauth = isset($provider['oauth_opendesktop']) ? $provider['oauth_opendesktop'] : '';
        if ($member['external_id'] != $providerOauth) {
            $result[] = 'external_id<=>oauth_opendesktop->extern_uid';
            $result[] = $member['external_id'] . '<=>' . $providerOauth;
        }
        if (false === array_search('ldapmain', array_column($userSubsystem['identities'], 'provider'))) {
            $result[] = 'ldapmain missing';
        }
        $providerLdap = isset($provider['ldapmain']) ? $provider['ldapmain'] : '';
        if ($userDn != $providerLdap) {
            $result[] = 'userDn<=>ldapmain->extern_uid';
            $result[] = $userDn . '<=>' . $providerLdap;
        }

        return $result;
    }

    public function resetMessages()
    {
        $this->messages = array();
    }

    /**
     * @param array $member_data
     * @param bool  $force
     *
     * @return array|bool
     * @throws Zend_Exception
     */
    public function createUserFromArray($member_data, $force = false)
    {
        if (empty($member_data)) {
            return false;
        }

        $this->messages = array();

        $data = $this->mapUserData($member_data);

        $user = $this->getUser($member_data['external_id'], $member_data['username']);

        $updatedUser = null;

        if (false === $user) {
            try {
                $data[0]['skip_confirmation'] = 'true';
                $user = $this->httpUserCreate($data[0]);
                $this->messages[] = "created : " . json_encode($user);
                $data[1]['skip_reconfirmation'] = 'true';
                $updatedUser = $this->httpUserUpdate($data[1], $user['id']);
                $this->messages[] = "updated : " . json_encode($updatedUser);
            } catch (Zend_Exception $e) {
                $this->messages[] = "Fail " . $e->getMessage();

                return false;
            }

            return $updatedUser;
        }

        if ($force === true) {
            try {
                foreach ($data as $datum) {
                    $datum['skip_reconfirmation'] = 'true';
                    unset($datum['password']);
                    $updatedUser = $this->httpUserUpdate($datum, $user['id']);
                }
            } catch (Zend_Exception $e) {
                $this->messages[] = "Fail " . $e->getMessage();

                return false;
            }
            $this->messages[] = "overwritten : " . json_encode($updatedUser);

            return $updatedUser;
        }

        $this->messages[0] = 'user already exists.';

        return false;
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    private function httpUserCreate($data)
    {
        $uri = $this->config->host . '/api/v4/users';
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::POST);
        $this->httpClient->setParameterPost($data);

        $response = $this->httpClient->request();
        if ($response->getStatus() < 200 OR $response->getStatus() >= 300) {
            throw new Default_Model_Ocs_Exception('push user data failed. OCS OpenCode server send message: '
                                                  . $response->getRawBody());
        }

        $body = Zend_Json::decode($response->getRawBody());
        if (array_key_exists("message", $body)) {
            throw new Default_Model_Ocs_Exception(Zend_Json::encode($body["message"]));
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - request: ' . $uri);
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - response: ' . $response->getRawBody());

        return $body;
    }

    /**
     * @param int $member_id
     *
     * @return bool
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Http_Exception
     */
    public function deleteUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $member_data = $this->getMemberData($member_id, false);

        $user = $this->getUser($member_data['external_id'], mb_strtolower($member_data['username']));

        if (false === $user) {
            $this->messages[0] = 'Not deleted. User not exists. ';

            return false;
        }

        return $this->httpUserDelete($user['id']);
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Http_Exception
     */
    private function httpUserDelete($id)
    {
        $uri = $this->config->host . '/api/v4/users/' . $id;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::DELETE);

        $response = $this->httpClient->request();

        if (204 == $response->getStatus()) {
            $this->messages[0] = ' - response : ' . $response->getRawBody() . " - user id: {$id}";

            return true;
        }

        if ($response->getStatus() < 200 AND $response->getStatus() >= 300) {
            throw new Default_Model_Ocs_Exception('delete user failed. OCS OpenCode server send message: ' . $response->getRawBody()
                                                  . PHP_EOL . " - OpenCode user id: {$id}");
        }

        $body = Zend_Json::decode($response->getRawBody());
        if (array_key_exists("message", $body)) {
            throw new Default_Model_Ocs_Exception($body["message"]);
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - request: ' . $uri);
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - response: ' . $response->getRawBody());

        $this->messages[0] = ' - response : ' . $response->getRawBody() . " - user id: {$id}";

        return true;
    }

    /**
     * @param string $username
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function userExists($username)
    {
        $uri = $this->config->host . '/api/v4/users?username=' . $username;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::GET);

        $response = $this->httpClient->request();

        $body = Zend_Json::decode($response->getRawBody());
        if (array_key_exists("message", $body)) {
            throw new Default_Model_Ocs_Exception($body["message"]);
        }

        if (count($body) == 0) {
            return false;
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - request: ' . $uri);
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - response: ' . $response->getBody());

        if ($response->getStatus() < 200 AND $response->getStatus() >= 300) {
            throw new Zend_Exception('exists user failed. OCS OpenCode server send message: ' . $response->getBody() . PHP_EOL
                                     . " - OpenCode user id: {$username}");
        }

        $this->messages[0] =
            ' - response for user exists request: ' . $response->getBody() . PHP_EOL . " - OpenCode user id: {$username}" . PHP_EOL;

        return $body[0]['id'];
    }

    /**
     * @return array|null
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param int $member_id
     *
     * @return array|bool
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     */
    public function createUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $member_data = $this->getMemberData($member_id);
        $data = $this->mapUserData($member_data);

        $userId = $this->getUser($data['extern_uid'], $data['username']);

        if (false === $userId) {

            try {
                $data[0]['skip_confirmation'] = 'true';
                $user = $this->httpUserCreate($data[0]);
                $this->messages[] = "created : " . json_encode($user);
                $data[1]['skip_reconfirmation'] = 'true';
                $updatedUser = $this->httpUserUpdate($data[1], $user['id']);
                $this->messages[] = "updated : " . json_encode($updatedUser);
            } catch (Zend_Exception $e) {
                $this->messages[] = "Fail " . $e->getMessage();

                return false;
            }
            $this->messages[] = "Success";

            return $data;
        }

        $this->messages[0] = 'user already exists.';

        return false;
    }

    /**
     * @param string $name
     *
     * @return bool
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Uri_Exception
     */
    public function groupExists($name)
    {
        $uri = $this->config->host . '/api/v4/groups?search=' . $name;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::GET);

        $response = $this->httpClient->request();

        try {
            $body = Zend_Json::decode($response->getRawBody());
        } catch (Zend_Json_Exception $e) {
            throw new Default_Model_Ocs_Exception($e, 0, $e);
        } catch (Zend_Http_Client_Exception $e) {
            // Gitlab send empty response when group not found, this cause an Exception in http client
            // we cannot distinguish between a real error and a successful empty response
            // so we catch this exception and log only
            // TODO: maybe use plain curl request
            error_log($e->getMessage());
        }

        if (count($body) > 0) {
            return true;
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new Default_Model_Ocs_Exception($body["message"]);
            }
        }

        if (array_key_exists("error_description", $body)) {
            throw new Default_Model_Ocs_Exception($body["error_description"]);
        }

        return false;
    }

    /**
     * @param $member_id
     *
     * @return bool
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Http_Exception
     */
    public function updateMail($member_id)
    {
        if (empty($member_id)) {
            throw new Default_Model_Ocs_Exception('given member_id is empty');
        }

        $member_data = $this->getMemberData($member_id, false);
        $entry = $this->getUser($member_data['external_id'], $member_data['username']);

        if (false === $entry) {
            $this->messages[] = "Failed. User not found;";

            return false;
        }

        $entry['skip_reconfirmation'] = 'true';
        $entry['email'] = $member_data['email_address'];
        unset($entry['password']);
        $this->httpUserUpdate($entry, $entry['id']);
        $this->messages[] = "Success";

        return true;
    }

    /**
     * @return array|mixed
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     * @throws Zend_Uri_Exception
     */
    public function getUsers()
    {
        $uri = $this->config->host . "/api/v4/users";
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::GET);

        $response = $this->httpClient->request();

        $body = Zend_Json::decode($response->getRawBody());

        if (count($body) == 0) {
            return array();
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new Default_Model_Ocs_Exception($body["message"]);
            }
        }

        return $body;
    }

    /**
     * @param int $id
     *
     * @return array|mixed
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     * @throws Zend_Uri_Exception
     */
    public function getUserWithId($id)
    {
        $uri = $this->config->host . "/api/v4/users/" . $id;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::GET);

        $response = $this->httpClient->request();

        $body = Zend_Json::decode($response->getRawBody());

        if (count($body) == 0) {
            return array();
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new Default_Model_Ocs_Exception($body["message"]);
            }
        }

        return $body;
    }

    /**
     * @param int    $page
     * @param int    $limit
     * @param string $order_by
     * @param string $sort
     *
     * @return array|false|mixed
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Uri_Exception
     */
    public function getProjects($page = 1, $limit = 5, $order_by = 'created_at', $sort = 'desc')
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__;
        if (!($body = $cache->load($cacheName))) {
            $this->httpClient->resetParameters();
            $uri =
                $this->config->host . '/api/v4/projects?order_by=' . $order_by . '&sort=' . $sort . '&visibility=public&page=' . $page
                . '&per_page=' . $limit;
            $this->httpClient->setUri($uri);
            $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
            $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
            $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
            $this->httpClient->setMethod(Zend_Http_Client::GET);

            try {
                $response = $this->httpClient->request();

                $body = Zend_Json::decode($response->getRawBody());

                if (count($body) == 0) {
                    return array();
                }

                if (array_key_exists("message", $body)) {
                    $result_code = substr(trim($body["message"]), 0, 3);
                    if ((int)$result_code >= 300) {
                        throw new Default_Model_Ocs_Exception($body["message"]);
                    }
                }

                //fetch also user data
                $returnArray = array();
                foreach ($body as $git_project) {
                    $gituser = $this->getUserWithName($git_project['namespace']['name']);
                    $git_project['namespace']['avatar_url'] = $gituser['avatar_url'];
                    $returnArray[] = $git_project;
                }
                $body = $returnArray;
            } catch (Exception $exc) {
                return array();
            }
        }

        return $body;
    }

    /**
     * @param int $id
     *
     * @return mixed|null
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     * @throws Zend_Uri_Exception
     */
    public function getProject($id)
    {
        $uri = $this->config->host . "/api/v4/projects/" . $id . "/";
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::GET);

        $response = $this->httpClient->request();

        $body = Zend_Json::decode($response->getRawBody());

        if ($body['visibility'] <> 'public') {
            throw new Default_Model_Ocs_Exception('Project not found in gitlab');
        }

        if (count($body) == 0) {
            return null;
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new Default_Model_Ocs_Exception($body["message"]);
            }
        }

        return $body;
    }

    /**
     * @param int    $id
     * @param string $state
     * @param int    $page
     * @param int    $limit
     *
     * @return array|mixed
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     * @throws Zend_Uri_Exception
     */
    public function getProjectIssues($id, $state = 'opened', $page = 1, $limit = 5)
    {
        $uri = $this->config->host . '/api/v4/projects/' . $id . '/issues?state=' . $state . '&page=' . $page . '&per_page=' . $limit;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::GET);

        $response = $this->httpClient->request();

        $body = Zend_Json::decode($response->getRawBody());

        if (count($body) == 0) {
            return array();
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new Default_Model_Ocs_Exception($body["message"]);
            }
        }

        return $body;
    }

    /**
     * @param int $user_id
     * @param int $page
     * @param int $limit
     *
     * @return array|mixed
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     * @throws Zend_Uri_Exception
     */
    public function getUserProjects($user_id, $page = 1, $limit = 50)
    {
        $uri = $this->config->host . '/api/v4/users/' . $user_id . '/projects?visibility=public&page=' . $page . '&per_page=' . $limit;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::GET);
        $response = null;
        try {
            $response = $this->httpClient->request();

        } catch (Exception $ex) {
            $response = null;
        }

        if ($response && !empty($response)) {

            $body = Zend_Json::decode($response->getRawBody());

            if (count($body) == 0) {
                return array();
            }

            if (array_key_exists("message", $body)) {
                $result_code = substr(trim($body["message"]), 0, 3);
                if ((int)$result_code >= 300) {
                    throw new Default_Model_Ocs_Exception($body["message"]);
                }
            }

            return $body;
        }

        return null;
    }

}