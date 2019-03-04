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
class Default_Model_Ocs_Matrix
{
    protected $messages;
    private $httpServer;

    /**
     * @inheritDoc
     */
    public function __construct($config = null)
    {
        if (isset($config)) {
            $this->config = $config;
        } else {
            $this->config = Zend_Registry::get('config')->settings->server->matrix;
        }
        $uri = $this->config->host;
        $this->httpServer = new Zend_Http_Client($uri, array('keepalive' => true, 'strictredirects' => true));
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

        $uid = $member_data['member_id'];
        $user = $this->createUserAvailable($member_data['username']);

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
     * @param string     $uri
     * @param string     $uid
     * @param string     $method
     * @param array|null $post_param
     *
     * @return bool|array
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    protected function httpRequest($uri, $uid, $method = Zend_Http_Client::GET, $post_param = null)
    {
        $this->httpServer->resetParameters();
        $this->httpServer->setUri($uri);
        $this->httpServer->setHeaders('Private-Token', $this->config->private_token);
        $this->httpServer->setHeaders('Sudo', $this->config->user_sudo);
        $this->httpServer->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpServer->setMethod($method);
        if (isset($post_param)) {
            $this->httpServer->setParameterPost($post_param);
        }

        $response = $this->httpServer->request();
        if ($response->getStatus() < 200 OR $response->getStatus() >= 500) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS Matrix server send message: ' . $response->getBody();

            return false;
        }

        $body = Zend_Json::decode($response->getBody());

        if ($body && is_array($body) && array_key_exists("message", $body)) {
            $this->messages[] = "id: {$uid} ($uri) - " . Zend_Json::encode($body["message"]);
        }

        return $body;
    }

}