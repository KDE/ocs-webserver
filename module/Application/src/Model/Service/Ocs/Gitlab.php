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
use Exception;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Cache\Storage\Adapter\Memcached;
use Laminas\Config\Config;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Http\Client;
use Laminas\Http\Request;
use Library\Tools\Truncate;
use Psr\SimpleCache\InvalidArgumentException;
use stdClass;

class Gitlab
{
    /** @var  Memcached */
    protected $cache;
    /** @var Config */
    protected $config;
    protected $messages;
    /** @var Client */
    protected $httpClient;
    /** @var MemberDeactivationLogRepository */
    private $member_deactivation_log;

    /**
     * Gitlab constructor.
     *
     * @param Config                         $config
     * @param AbstractAdapter                $cache
     * @param MemberDeactivationLogInterface $member_deactivation_log
     *
     * @throws ServerException
     */
    public function __construct(
        Config $config,
        AbstractAdapter $cache,
        MemberDeactivationLogInterface $member_deactivation_log
    ) {
        if (isset($config)) {
            $this->config = $config;
        } else {
            throw new ServerException('config missing');
        }
        $uri = $this->config->host;
        $this->httpClient = $this->getHttpClient($uri);

        $this->cache = $cache;
        $this->messages = array();
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
     * @param int|array $member_data
     *
     * @return bool
     * @throws ServerException
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
        $method = Request::METHOD_GET;
        $uid = $member_data['member_id'];
        try {
            $response = $this->httpRequest($uri, $uid, $method);
            if (false === $response) {
                $this->messages[] = "Fail ";

                return false;
            }
        } catch (Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        $memberLog = $this->member_deactivation_log;
        foreach ($response as $project) {
            $log_data = $memberLog->getLogEntries(
                $member_data['member_id'], MemberDeactivationLogRepository::OBJ_TYPE_GITLAB_PROJECT, $project['id']
            );
            if (false === $log_data) {
                continue;
            }
            $object_data = json_decode($log_data['object_data'], true);
            $visibility = $object_data['visibility'];

            $uri = $this->config->host . "/api/v4/projects/{$project['id']}";
            $method = Request::METHOD_PUT;
            $uid = $member_data['member_id'];
            $data = array('visibility' => $visibility);
            try {
                $response = $this->httpRequest($uri, $uid, $method, $data);
                if (false === $response) {
                    $this->messages[] = "Fail " . $uri;

                    continue;
                }
            } catch (Exception $e) {
                $this->messages[] = "Fail " . $e->getMessage();

                return false;
            }
            $this->messages[] = "Successful unblock user project: {$project['id']}";

            $memberLog->deleteLog(
                $member_data['member_id'], MemberDeactivationLogRepository::OBJ_TYPE_GITLAB_PROJECT, $project['id']
            );
        }

        return true;
    }

    /**
     * @param int  $member_id
     *
     * @param bool $onlyActive
     *
     * @return array
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

        try {
            $adapter = GlobalAdapterFeature::getStaticAdapter();
            $result = $adapter->query($sql, array('memberId' => $member_id));
        } catch (Exception $e) {
            error_log(__METHOD__ . ' - ERROR while read member data - ' . print_r($e, true));

            return array();
        }

        if ($result->count() == 0) {
            throw new ServerException('member with id ' . $member_id . ' could not found.');
        }

        return $result->current()->getArrayCopy();
    }

    /**
     * @param $extern_uid
     * @param $username
     *
     * @return array|false
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
     */
    public function getUserByExternUid($extern_uid)
    {
        $uri = $this->config->host . "/api/v4/users?extern_uid={$extern_uid}&provider=" . $this->config->provider_name;
        $body = $this->httpRequest($uri, $extern_uid);

        if (count($body) == 0) {
            return array();
        }

        $GLOBALS['ocs_log']->debug(__METHOD__ . " - body: " . var_export($body, true));

        if (is_object($body[0])) {
            return json_decode(json_encode($body[0]), true);
        }

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
    protected function httpRequest($uri, $uid, $method = Request::METHOD_GET, $post_param = null)
    {
        $body = array();
        try {
            $this->httpClient->setUri($uri);
        } catch (Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') setUri error message: ' . $e->getMessage();

            return false;
        }
        $this->httpClient->resetParameters();
        try {
            $this->httpClient->setHeaders(
                array(
                    'Private-Token' => $this->config->private_token,
                    'Sudo'          => $this->config->user_sudo,
                    'User-Agent'    => $this->config->user_agent,
                )
            );
            $this->httpClient->setMethod($method);
        } catch (Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') setHeaders error message: ' . $e->getMessage();

            return false;
        }
        if (isset($post_param)) {
            $this->httpClient->setParameterPost($post_param);
        }

        try {
            $response = $this->httpClient->send();
        } catch (Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') request error message: ' . $e->getMessage();

            return false;
        }
        if ($response->getStatusCode() < 200 or $response->getStatusCode() >= 500) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS Forum server send message: ' . $response->getBody();

            return false;
        }

        try {
            $body = json_decode($response->getBody(), true);
        } catch (Exception $e) {
            $this->messages[] = 'Request failed.(' . $uri . ') json_decode error message: ' . $e->getMessage();
        }

        if ($body && is_array($body) && array_key_exists("message", $body)) {
            $this->messages[] = "id: {$uid} ($uri) - " . json_encode($body["message"]);
        }

        return $body;
    }

    /**
     * @param $username
     *
     * @return array
     */
    public function getUserByDN($username)
    {
        $user_id = $this->buildUserDn($username);
        $uri = $this->config->host . "/api/v4/users?extern_uid={$user_id}&provider=ldapmain";

        $body = $this->httpRequest($uri, $username);

        if (count($body) == 0) {
            return array();
        }

        $GLOBALS['ocs_log']->debug(__METHOD__ . " - body: " . var_export($body, true));

        if (is_object($body[0])) {
            return json_decode(json_encode($body[0]), true);
        }

        return $body[0];
    }

    /**
     * @param string $extern_uid
     *
     * @return string
     */
    private function buildUserDn($extern_uid)
    {
        $username = mb_strtolower($extern_uid);
        $baseDn = Ldap::getUserBaseDn();
        $dn = "cn={$username},{$baseDn}";

        return $dn;
    }

    /**
     * @param string $username
     *
     * @return array
     */
    public function getUserWithName($username)
    {
        $uri = $this->config->host . "/api/v4/users?username=" . $username;
        $body = $this->httpRequest($uri, $username);

        if (count($body) == 0) {
            return array();
        }

        $GLOBALS['ocs_log']->debug(__METHOD__ . " - body: " . var_export($body, true));

        if (is_object($body[0])) {
            return json_decode(json_encode($body[0]), true);
        }

        return $body[0];
    }

    /**
     * @param array $member_data
     *
     * @return bool
     * @throws ServerException
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
        $method = Request::METHOD_GET;
        $uid = $member_data['member_id'];
        try {
            $response = $this->httpRequest($uri, $uid, $method);
            if (false === $response) {
                $this->messages[] = "Fail ";

                return false;
            }
        } catch (Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        foreach ($response as $project) {
            /** @var CurrentUser $current_user */
            $current_user = $GLOBALS['ocs_user'];
            $memberLog = $this->member_deactivation_log;
            $memberLog->addLogData(
                $member_data['member_id'], MemberDeactivationLogRepository::OBJ_TYPE_GITLAB_PROJECT, $project['id'], json_encode($project), $current_user->member_id
            );

            $uri = $this->config->host . "/api/v4/projects/{$project['id']}";
            $method = Request::METHOD_PUT;
            $uid = $member_data['member_id'];
            $data = array('visibility' => 'private');
            try {
                $blocked = $this->httpRequest($uri, $uid, $method, $data);
                if (false === $blocked) {
                    $this->messages[] = "Fail " . $uri;

                    continue;
                }
            } catch (Exception $e) {
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

        $user = $this->getUser($member_data['external_id'], $member_data['username']);
        if (false === $user) {

            return false;
        }

        $uri = $this->config->host . "/api/v4/users/{$user['id']}/block";
        $method = Request::METHOD_POST;
        $uid = $member_data['member_id'];

        try {
            $response = $this->httpRequest($uri, $uid, $method);
            if (false === $response) {
                $this->messages[] = "Fail ";

                return false;
            }
        } catch (Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        $this->messages[] = "Successful block user: {$member_data['username']}";

        /** @var CurrentUser $current_user */
        $current_user = $GLOBALS['ocs_user'];
        $memberLog = $this->member_deactivation_log;
        $memberLog->addLog($member_data['member_id'], MemberDeactivationLogRepository::OBJ_TYPE_GITLAB_USER, $user['id'], $current_user->member_id);

        return true;
    }

    /**
     * @param int|array $member_data
     *
     * @return bool
     * @throws ServerException
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
        $method = Request::METHOD_POST;
        $uid = $member_data['member_id'];

        try {
            $response = $this->httpRequest($uri, $uid, $method);
            if (false === $response) {
                $this->messages[] = "Fail ";

                return false;
            }
        } catch (Exception $e) {
            $this->messages[] = "Fail " . $e->getMessage();

            return false;
        }

        $this->messages[] = "Successful unblock user: {$member_data['username']}";

        /** @var CurrentUser $current_user */
        $current_user = $GLOBALS['ocs_user'];
        $memberLog = $this->member_deactivation_log;
        $memberLog->deleteLog($member_data['member_id'], MemberDeactivationLogRepository::OBJ_TYPE_GITLAB_USER, $user['id'], $current_user->member_id);

        return true;
    }

    /**
     * @param int|array $member_data
     * @param string    $oldUsername
     *
     * @return array|bool|null
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
        //$data['skip_reconfirmation'] = 'true';
        //unset($data['password']);

        try {
            foreach ($data as $datum) {
                $datum['skip_reconfirmation'] = 'true';
                unset($datum['password']);

                $this->httpUserUpdate($data, $user['id']);
            }
        } catch (Exception $e) {
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
            $bio = Truncate::get($user['biography'], 250);
        } else {
            $bio = empty($user['biography']) ? '' : $user['biography'];
        }

        $data = array(
            array(
                'email'            => $paramEmail,
                'username'         => mb_strtolower($user['username']),
                'name'             => $user['username'],
                'password'         => $user['password'],
                'provider'         => $this->config->provider_name,
                'extern_uid'       => $user['external_id'],
                'bio'              => $bio,
                'admin'            => $user['roleId'] == 100 ? 'true' : 'false',
                'can_create_group' => 'true',
            ),
            array(
                'email'            => $paramEmail,
                'username'         => mb_strtolower($user['username']),
                'name'             => $user['username'],
                'password'         => $user['password'],
                'provider'         => "ldapmain",
                'extern_uid'       => $this->buildUserDn($user['username']),
                'bio'              => $bio,
                'admin'            => $user['roleId'] == 100 ? 'true' : 'false',
                'can_create_group' => 'true',
            ),
        );

        return $data;
    }

    /**
     * @param $data
     *
     * @param $id
     *
     * @return bool
     * @throws ServerException
     */
    private function httpUserUpdate($data, $id)
    {
        $uri = $this->config->host . '/api/v4/users/' . $id;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders(
            array(
                'Private-Token' => $this->config->private_token,
                'Sudo'          => $this->config->user_sudo,
                'User-Agent'    => $this->config->user_agent,
            )
        );
        $this->httpClient->setMethod(Request::METHOD_PUT);
        $this->httpClient->setParameterPost($data);

        $response = $this->httpClient->send();
        if ($response->getStatusCode() < 200 or $response->getStatusCode() >= 300) {
            throw new ServerException('update user data failed. OCS OpenCode server send message: ' . $response->getBody());
        }

        $body = json_decode($response->getBody());
        if (array_key_exists("message", $body)) {
            throw new ServerException($body["message"]);
        }

        $GLOBALS['ocs_log']->debug(__METHOD__ . ' - request: ' . $uri);
        $GLOBALS['ocs_log']->debug(__METHOD__ . ' - response: ' . $response->getBody());

        return $body;
    }

    /**
     * @param string $email
     *
     * @return array
     * @throws ServerException
     * @deprecated
     */
    public function getUserByEmail($email)
    {
        $uri = $this->config->host . "/api/v4/users?search={$email}";
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders(
            array(
                'Private-Token' => $this->config->private_token,
                'Sudo'          => $this->config->user_sudo,
                'User-Agent'    => $this->config->user_agent,
            )
        );
        $this->httpClient->setMethod(Request::METHOD_GET);

        $response = $this->httpClient->send();

        $body = json_decode($response->getBody());

        if (count($body) == 0) {
            return array();
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new ServerException($body["message"]);
            }
        }

        $GLOBALS['ocs_log']->debug(__METHOD__ . " - body: " . $response->getBody());

        return $body;
    }

    /**
     * @param array $member
     * @param array $userSubsystem
     *
     * @return array
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
        if (($member['roleId'] == 100) != $userSubsystem['is_admin']) {
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

    /**
     *
     */
    public function resetMessages()
    {
        $this->messages = array();
    }

    /**
     * @param array $member_data
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

        $user = $this->getUser($member_data['external_id'], $member_data['username']);

        $updatedUser = null;

        if (false === $user) {
            try {
                $data[0]['skip_confirmation'] = 'true';
                /** @var stdClass $user */
                $user = $this->httpUserCreate($data[0]);
                $this->messages[] = "created : " . json_encode($user);
                $data[1]['skip_reconfirmation'] = 'true';
                $updatedUser = $this->httpUserUpdate($data[1], $user->id);
                $this->messages[] = "updated : " . json_encode($updatedUser);
            } catch (Exception $e) {
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

                    if (is_object($user)) {
                        $updatedUser = $this->httpUserUpdate($datum, $user->id);
                    } else {
                        $updatedUser = $this->httpUserUpdate($datum, $user['id']);
                    }
                }
            } catch (Exception $e) {
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
     * @throws Exception
     */
    private function httpUserCreate($data)
    {
        $uri = $this->config->host . '/api/v4/users';
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders(
            array(
                'Private-Token' => $this->config->private_token,
                'Sudo'          => $this->config->user_sudo,
                'User-Agent'    => $this->config->user_agent,
            )
        );
        $this->httpClient->setMethod(Request::METHOD_POST);
        $this->httpClient->setParameterPost($data);

        $response = $this->httpClient->send();
        if ($response->getStatusCode() < 200 or $response->getStatusCode() >= 300) {
            throw new ServerException('push user data failed. OCS OpenCode server send message: ' . $response->getBody());
        }

        $body = json_decode($response->getBody());
        if (array_key_exists("message", $body)) {
            throw new ServerException(json_encode($body["message"]));
        }

        $GLOBALS['ocs_log']->debug(__METHOD__ . ' - request: ' . $uri);
        $GLOBALS['ocs_log']->debug(__METHOD__ . ' - response: ' . $response->getBody());

        return $body;
    }

    /**
     * @param int $member_id
     *
     * @return bool
     * @throws ServerException
     * @deprecated
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
     * @throws ServerException
     * @deprecated
     */
    private function httpUserDelete($id)
    {
        $uri = $this->config->host . '/api/v4/users/' . $id;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders(
            array(
                'Private-Token' => $this->config->private_token,
                'Sudo'          => $this->config->user_sudo,
                'User-Agent'    => $this->config->user_agent,
            )
        );
        $this->httpClient->setMethod(Request::METHOD_DELETE);

        $response = $this->httpClient->send();

        if (204 == $response->getStatusCode()) {
            $this->messages[0] = ' - response : ' . $response->getBody() . " - user id: {$id}";

            return true;
        }

        if ($response->getStatusCode() < 200 and $response->getStatusCode() >= 300) {
            throw new ServerException('delete user failed. OCS OpenCode server send message: ' . $response->getBody() . PHP_EOL . " - OpenCode user id: {$id}");
        }

        $body = json_decode($response->getBody());
        if (array_key_exists("message", $body)) {
            throw new ServerException($body["message"]);
        }

        $GLOBALS['ocs_log']->debug(__METHOD__ . ' - request: ' . $uri);
        $GLOBALS['ocs_log']->debug(__METHOD__ . ' - response: ' . $response->getBody());

        $this->messages[0] = ' - response : ' . $response->getBody() . " - user id: {$id}";

        return true;
    }

    /**
     * @param string $username
     *
     * @return bool
     * @throws ServerException
     */
    public function userExists($username)
    {
        $uri = $this->config->host . '/api/v4/users?username=' . $username;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders(
            array(
                'Private-Token' => $this->config->private_token,
                'Sudo'          => $this->config->user_sudo,
                'User-Agent'    => $this->config->user_agent,
            )
        );
        $this->httpClient->setMethod(Request::METHOD_GET);

        $response = $this->httpClient->send();

        $body = json_decode($response->getBody());
        if (array_key_exists("message", $body)) {
            throw new ServerException($body["message"]);
        }

        if (count($body) == 0) {
            return false;
        }

        $GLOBALS['ocs_log']->debug(__METHOD__ . ' - request: ' . $uri);
        $GLOBALS['ocs_log']->debug(__METHOD__ . ' - response: ' . $response->getBody());

        if ($response->getStatusCode() < 200 and $response->getStatusCode() >= 300) {
            throw new ServerException('exists user failed. OCS OpenCode server send message: ' . $response->getBody() . PHP_EOL . " - OpenCode user id: {$username}");
        }

        $this->messages[0] = ' - response for user exists request: ' . $response->getBody() . PHP_EOL . " - OpenCode user id: {$username}" . PHP_EOL;

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
     * @throws ServerException
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
            } catch (Exception $e) {
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
     * @throws ServerException
     */
    public function groupExists($name)
    {
        $uri = $this->config->host . '/api/v4/groups?search=' . $name;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders(
            array(
                'Private-Token' => $this->config->private_token,
                'Sudo'          => $this->config->user_sudo,
                'User-Agent'    => $this->config->user_agent,
            )
        );
        $this->httpClient->setMethod(Request::METHOD_GET);

        $response = $this->httpClient->send();

        try {
            $body = json_decode($response->getBody());
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new ServerException($e, 0, $e);
        }

        if (count($body) > 0) {
            return true;
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new ServerException($body["message"]);
            }
        }

        if (array_key_exists("error_description", $body)) {
            throw new ServerException($body["error_description"]);
        }

        return false;
    }

    /**
     * @param $member_id
     *
     * @return bool
     * @throws ServerException
     */
    public function updateMail($member_id)
    {
        if (empty($member_id)) {
            throw new ServerException('given member_id is empty');
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
     * @throws ServerException
     */
    public function getUsers()
    {
        $uri = $this->config->host . "/api/v4/users";
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders(
            array(
                'Private-Token' => $this->config->private_token,
                'Sudo'          => $this->config->user_sudo,
                'User-Agent'    => $this->config->user_agent,
            )
        );
        $this->httpClient->setMethod(Request::METHOD_GET);

        $response = $this->httpClient->send();

        $body = json_decode($response->getBody());

        if (count($body) == 0) {
            return array();
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new ServerException($body["message"]);
            }
        }

        return $body;
    }

    /**
     * @param int $id
     *
     * @return array|mixed
     * @throws ServerException
     */
    public function getUserWithId($id)
    {
        $uri = $this->config->host . "/api/v4/users/" . $id;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders(
            array(
                'Private-Token' => $this->config->private_token,
                'Sudo'          => $this->config->user_sudo,
                'User-Agent'    => $this->config->user_agent,
            )
        );
        $this->httpClient->setMethod(Request::METHOD_GET);

        $response = $this->httpClient->send();

        $body = json_decode($response->getBody());

        if (count($body) == 0) {
            return array();
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new ServerException($body["message"]);
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
     */
    public function getProjects($page = 1, $limit = 5, $order_by = 'created_at', $sort = 'desc')
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__;
        if (!($body = $cache->getItem($cacheName))) {
            $this->httpClient->resetParameters();
            $uri = $this->config->host . '/api/v4/projects?order_by=' . $order_by . '&sort=' . $sort . '&visibility=public&page=' . $page . '&per_page=' . $limit;
            $this->httpClient->setUri($uri);
            $this->httpClient->setHeaders(
                array(
                    'Private-Token' => $this->config->private_token,
                    'Sudo'          => $this->config->user_sudo,
                    'User-Agent'    => $this->config->user_agent,
                )
            );
            $this->httpClient->setMethod(Request::METHOD_GET);

            try {
                $response = $this->httpClient->send();

                $body = json_decode($response->getBody());

                if (count($body) == 0) {
                    return array();
                }

                if (array_key_exists("message", $body)) {
                    $result_code = substr(trim($body["message"]), 0, 3);
                    if ((int)$result_code >= 300) {
                        throw new ServerException($body["message"]);
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
     * @throws ServerException
     */
    public function getProject($id)
    {
        $uri = $this->config->host . "/api/v4/projects/" . $id . "/";
        
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders(
            array(
                'Private-Token' => $this->config->private_token,
                'Sudo'          => $this->config->user_sudo,
                'User-Agent'    => $this->config->user_agent,
            )
        );
        $this->httpClient->setMethod(Request::METHOD_GET);

        $response = $this->httpClient->send();

        $body = json_decode($response->getBody());
       
        if (count(json_decode($response->getBody(), true)) == 0) {
            return null;
        }

        if ($body->visibility <> 'public') {
            throw new ServerException('Project not found in gitlab');
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body->message), 0, 3);
            if ((int)$result_code >= 300) {
                throw new ServerException($body->message);
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
     * @throws ServerException
     */
    public function getProjectIssues($id, $state = 'opened', $page = 1, $limit = 5)
    {
        $uri = $this->config->host . '/api/v4/projects/' . $id . '/issues?state=' . $state . '&page=' . $page . '&per_page=' . $limit;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders(
            array(
                'Private-Token' => $this->config->private_token,
                'Sudo'          => $this->config->user_sudo,
                'User-Agent'    => $this->config->user_agent,
            )
        );
        $this->httpClient->setMethod(Request::METHOD_GET);

        $response = $this->httpClient->send();

        $body = json_decode($response->getBody());

        if (count($body) == 0) {
            return array();
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new ServerException($body["message"]);
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
     * @throws ServerException
     */
    public function getUserProjects($user_id, $page = 1, $limit = 50)
    {
        $cache_name = hash('haval128,4',__FUNCTION__ . '_' . $user_id . $page . $limit);
        $cache = new SimpleCacheDecorator($this->cache);
        try {
            if ($body = $cache->get($cache_name)) {
                return $body;
            }
        } catch (InvalidArgumentException $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . '_' . $e->getMessage());
        }

        $uri = $this->config->host . '/api/v4/users/' . $user_id . '/projects?visibility=public&page=' . $page . '&per_page=' . $limit;
        $this->httpClient->resetParameters();
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders(
            array(
                'Private-Token' => $this->config->private_token,
                'Sudo'          => $this->config->user_sudo,
                'User-Agent'    => $this->config->user_agent,
            )
        );
        $this->httpClient->setMethod(Request::METHOD_GET);
        $response = null;
        try {
            $response = $this->httpClient->send();

        } catch (Exception $ex) {
            $response = null;
        }

        if ($response && !empty($response)) {

            $body = json_decode($response->getBody());

            if (count($body) == 0) {
                return array();
            }

            if (array_key_exists("message", $body)) {
                $result_code = substr(trim($body->message), 0, 3);
                if ((int)$result_code >= 300) {
                    throw new ServerException($body->message);
                }
            }
            try {
                $cache->set($cache_name, $body, 600);
            } catch (InvalidArgumentException $e) {
                $GLOBALS['ocs_log']->err(__METHOD__ . '_' . $e->getMessage());
            }

            return $body;
        }

        return null;
    }

}