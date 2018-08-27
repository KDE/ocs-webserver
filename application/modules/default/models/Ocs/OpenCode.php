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
            $this->config = Zend_Registry::get('config')->settings->opencode;
        }
        $uri = $this->config->host . $this->config->url->user_create;
        $this->httpClient = new Zend_Http_Client($uri, array('keepalice' => true, 'strictredirects' => true));
    }

    /**
     * @param $member_data
     *
     * @return bool
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     */
    public function exportUser($member_data)
    {
        if (empty($member_data)) {
            return false;
        }

        $data = $this->mapUserData($member_data);

        $userid = $this->userExists($data);

        if (false === empty($userid)) {
            return $this->httpUserUpdate($data, $userid);
        }

        return $this->httpUserCreate($data);
    }

    /**
     * @param array $user
     *
     * @return array
     */
    protected function mapUserData($user)
    {
        $data = array(
            'email'             => $user['email_address'],
            'username'          => $user['username'],
            'name'              => (false == empty($user['lastname'])) ? trim($user['firstname'] . ' ' . $user['lastname'])
                : $user['username'],
            'password'          => $user['password'],
            'extern_uid'        => $user['external_id'],
            'provider'          => 'all',
            'bio'               => empty($user['biography']) ? '' : $user['biography'],
            'admin'             => $user['roleId'] == 100 ? 'true' : 'false',
            'can_create_group'  => 'true',
            'skip_confirmation' => 'true',
            'skip_reconfirmation' => 'true'
        );

        return $data;
    }

    private function userExists($data)
    {
        $this->httpClient->resetParameters();
        $uri = $this->config->host . '/api/v4/users?username=' . $data['username'];
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
            if ((int) $result_code >= 300) {
                throw new Zend_Exception($body["message"]);
            }
        }

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

        $this->messages[0] = 'response for creation request: ' . $body;

        if ($response->getStatus() < 200 AND $response->getStatus() >= 300) {
            throw new Zend_Exception('push user data failed. OCS OpenCode server send message: ' . $body);
        }

        return true;
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
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    protected function getUserData($member_id)
    {
        $modelMember = new Default_Model_Member();
        $member = $modelMember->fetchMemberData($member_id)->toArray();

        $modelExternalId = new Default_Model_DbTable_MemberExternalId();
        $externalId = $modelExternalId->fetchRow(array("member_id = ?" => $member['member_id']));
        if (count($externalId->toArray()) > 0) {
            $member['external_id'] = $externalId->external_id;
        }

        return $member;
    }

    /**
     * @param $data
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
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

        //Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - request:\n" . $this->httpClient->getLastRequest());
        //Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - response:\n" . $response->asString());
        //Zend_Registry::get('logger')->debug("----------\n" . __METHOD__ . " - body:\n" . $response->getBody());

        $this->messages[0] = 'response for update request: ' . $body;

        if ($response->getStatus() < 200 AND $response->getStatus() >= 300) {
            throw new Zend_Exception('push user data failed. OCS OpenCode server send message: ' . $body);
        }

        return true;
    }

}