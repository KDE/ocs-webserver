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
class Default_Model_Ocs_OpenCode
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
            $this->config = Zend_Registry::get('config')->settings->server->opencode;
        }
        $uri = $this->config->host . $this->config->url->user_create;
        $this->httpClient = new Zend_Http_Client($uri, array('keepalive' => true, 'strictredirects' => true));
    }

    /**
     * @param      $member_data
     *
     * @param bool $force
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function exportUser($member_data, $force = false)
    {
        if (empty($member_data)) {
            return false;
        }

        $data = $this->mapUserData($member_data);

        $userId = $this->getUser($data['extern_uid']);

        if (empty($userId)) {
            $data['skip_confirmation'] = 'true';

            return $this->httpUserCreate($data);
        }

        if ($force === true) {
            $data['skip_reconfirmation'] = 'true';
            unset($data['password']);

            return $this->httpUserUpdate($data, $userId);
        }

        $this->messages[0] = 'User exists and we do not update. Use the force parameter instead.';

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
            'email'            => $paramEmail,
            'username'         => $user['username'],
            'name'             => (false == empty($user['lastname'])) ? trim($user['firstname'] . ' ' . $user['lastname'])
                : $user['username'],
            'password'         => $user['password'],
            'provider'         => 'all',
            'extern_uid'       => $user['external_id'],
            'bio'              => empty($user['biography']) ? '' : $user['biography'],
            'admin'            => $user['roleId'] == 100 ? 'true' : 'false',
            'can_create_group' => 'true'
            //'skip_confirmation' => 'true',
            //'skip_reconfirmation' => 'true',
            //'confirm'           => 'no'
        );

        return $data;
    }

    /**
     * @param string $username
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function getUser($extern_uid)
    {
        $this->httpClient->resetParameters();
        $uri = $this->config->host . "/api/v4/users?extern_uid={$extern_uid}&provider=all";
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::GET);

        $response = $this->httpClient->request();

        $body = Zend_Json::decode($response->getRawBody());

        if (count($body) == 0) {
            return false;
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new Zend_Exception($body["message"]);
            }
        }

        Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - body:\n" . $response->getRawBody());

        return $body[0]['id'];
    }

    /**
     * @param $data
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    private function httpUserCreate($data)
    {
        $this->httpClient->resetParameters();
        $uri = $this->config->host . $this->config->url->user_create;
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::POST);
        $this->httpClient->setParameterPost($data);

        $response = $this->httpClient->request();

        $transfer_encoding = $response->getHeader('Transfer-encoding');
        $body = $response->getRawBody();
        if ('chunked' == trim(strtolower($transfer_encoding))) {
            $body = Zend_Http_Response::decodeChunkedBody($response->getRawBody());
        }
        $content_encoding = $response->getHeader('Content-encoding');
        if ('gzip' == trim(strtolower($content_encoding))) {
            $body = Zend_Http_Response::decodeGzip($body);
        }

        //Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - request:\n" . $this->httpClient->getLastRequest());
        //Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - response:\n" . $response->asString());
        //Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - body:\n" . $response->getBody());

        $this->messages[0] = ' - response for creation request: ' . $body . PHP_EOL . " - userdata: " . implode(";", $data);

        if ($response->getStatus() < 200 AND $response->getStatus() >= 300) {
            throw new Default_Model_Ocs_Exception('push user data failed. OCS OpenCode server send message: ' . $body);
        }

        $body = Zend_Json::decode($response->getRawBody());
        if (array_key_exists("message", $body)) {
            throw new Default_Model_Ocs_Exception($body["message"]);
        }

        return true;
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
        $this->httpClient->resetParameters();
        $uri = $this->config->host . $this->config->url->user_create . '/' . $id;
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::PUT);
        $this->httpClient->setParameterPost($data);

        $response = $this->httpClient->request();

        $transfer_encoding = $response->getHeader('Transfer-encoding');
        $body = $response->getRawBody();
        if ('chunked' == trim(strtolower($transfer_encoding))) {
            $body = Zend_Http_Response::decodeChunkedBody($response->getRawBody());
        }
        $content_encoding = $response->getHeader('Content-encoding');
        if ('gzip' == trim(strtolower($content_encoding))) {
            $body = Zend_Http_Response::decodeGzip($body);
        }

        Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - request:\n" . $this->httpClient->getLastRequest());
        //Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - response:\n" . $response->asString());
        //Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - body:\n" . $response->getBody());

        $this->messages[0] =
            ' - response for update request: ' . $body . PHP_EOL . " - userdata: " . implode(';', $data) . PHP_EOL . " - opencode id: "
            . $id;

        if ($response->getStatus() < 200 AND $response->getStatus() >= 300) {
            throw new Zend_Exception('push user data failed. OCS OpenCode server send message: ' . $body);
        }

        $body = Zend_Json::decode($response->getRawBody());
        if (array_key_exists("message", $body)) {
            throw new Default_Model_Ocs_Exception($body["message"]);
        }

        return true;
    }

    /**
     * @param array $member_data
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Http_Exception
     * @throws Zend_Json_Exception
     */
    public function deleteUser($member_data)
    {
        if (empty($member_data)) {
            return false;
        }

        $userId = $this->getUser($member_data['external_id']);

        if (empty($userId)) {
            return false;
        }

        return $this->httpUserDelete($userId);
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
        $this->httpClient->resetParameters();
        $uri = $this->config->host . '/api/v4/users/' . $id;
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::DELETE);

        $response = $this->httpClient->request();

        $transfer_encoding = $response->getHeader('Transfer-encoding');
        $body = $response->getRawBody();
        if ('chunked' == trim(strtolower($transfer_encoding))) {
            $body = Zend_Http_Response::decodeChunkedBody($response->getRawBody());
        }
        $content_encoding = $response->getHeader('Content-encoding');
        if ('gzip' == trim(strtolower($content_encoding))) {
            $body = Zend_Http_Response::decodeGzip($body);
        }

        //Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - request:\n" . $this->httpClient->getLastRequest());
        //Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - response:\n" . $response->asString());
        //Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - body:\n" . $response->getBody());

        $this->messages[0] = ' - response for delete request: ' . $body . PHP_EOL . " - OpenCode user id: {$id}" . PHP_EOL;

        if ($response->getStatus() < 200 AND $response->getStatus() >= 300) {
            throw new Zend_Exception('delete user failed. OCS OpenCode server send message: ' . $body . PHP_EOL
                . " - OpenCode user id: {$id}");
        }

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
        $this->httpClient->resetParameters();
        $uri = $this->config->host . '/api/v4/users?username=' . $username;
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::GET);

        $response = $this->httpClient->request();

        $body = Zend_Json::decode($response->getRawBody());

        if (count($body) == 0) {
            return false;
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new Zend_Exception($body["message"]);
            }
        }

        Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - body:\n" . $response->getRawBody());

        return $body[0]['id'];
    }

    /**
     * @return array|null
     */
    public function getMessages()
    {
        return $this->messages;
    }

    public function createUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $member_data = $this->getMemberData($member_id);
        $data = $this->mapUserData($member_data);

        $userId = $this->getUser($data['extern_uid']);

        if (empty($userId)) {
            $data['skip_confirmation'] = 'true';

            return $this->httpUserCreate($data);
        }

        $this->messages[0] = 'User exists and we do not update. Use the force parameter instead.';

        return false;
    }

    /**
     * @param int $member_id
     *
     * @return array
     * @throws Zend_Exception
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
     * @param string $name
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function groupExists($name)
    {
        $this->httpClient->resetParameters();
        $uri = $this->config->host . '/api/v4/groups?search=' . $name;
        $this->httpClient->setUri($uri);
        $this->httpClient->setHeaders('Private-Token', $this->config->private_token);
        $this->httpClient->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpClient->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpClient->setMethod(Zend_Http_Client::GET);

        $response = $this->httpClient->request();

        $body = Zend_Json::decode($response->getRawBody());

        if (count($body) > 0) {
            return true;
        }

        if (array_key_exists("message", $body)) {
            $result_code = substr(trim($body["message"]), 0, 3);
            if ((int)$result_code >= 300) {
                throw new Zend_Exception($body["message"]);
            }
        }

        if (array_key_exists("error_description", $body)) {
            throw new Zend_Exception($body["error_description"]);
        }

        return false;
    }

    /**
     * @param $member_id
     *
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    protected function getUserData($member_id)
    {
        $modelMember = new Default_Model_Member();
        $member = $modelMember->fetchMemberData($member_id, false)->toArray();

        $modelExternalId = new Default_Model_DbTable_MemberExternalId();
        $externalId = $modelExternalId->fetchRow(array("member_id = ?" => $member['member_id']));
        if (count($externalId->toArray()) > 0) {
            $member['external_id'] = $externalId->external_id;
        }

        return $member;
    }

}