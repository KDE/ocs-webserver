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
 * Created: 10.10.2018
 */
class Local_Auth_Token_Jwt implements Local_Auth_Token_Interface
{

    private $config;

    /**
     * @var string
     */
    private $token;

    public function __construct(array $config = array())
    {
        if (empty($config)) {
            $config = Zend_Registry::get('config')->settings->jwt->toArray();
        }
        $this->config['secret'] = $config['secret'];
        $this->config['expire']['accessToken'] = $config['expire']['accessToken'];
        $this->config['expire']['refreshToken'] = $config['expire']['refreshToken'];
        $this->config['expire']['cookie'] = $config['expire']['cookie'];
        $this->config['expire']['authorizationCode'] = $config['expire']['authorizationCode'];
        $this->config['expire']['resetCode'] = $config['expire']['resetCode'];
        $this->config['issuer']['ident'] = $config['issuer_ident'];
    }

    /**
     * @param string $jwt
     * @return bool|object
     * @throws Zend_Exception
     */
    public function isValid($jwt)
    {
        $result = true;

        try {

            require_once APPLICATION_LIB . '/JWT.php';
            $payload = JWT::decode($jwt, $this->config['secret'], true);

            $result = $this->validateCookie($payload);

        } catch (Exception $e) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - Can not decode JWT: ' . $e->getMessage());

            $result = false;
        }

        return $result;
    }

    private function validateCookie($payload)
    {
        // check expiry date
        $date = new DateTime();
        if ($date->getTimestamp() > $payload->exp) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - member_id ' . $payload->sub . ' - cookie expired');

            return false;
        }
        // check user id
        $modelMember = new Default_Model_Member();
        $member = $modelMember->fetchMember($payload->sub);
        if ($member == null) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - member_id ' . $payload->sub . ' - cookie user id not found');

            return false;
        }
        //check hash
        if ($payload->hash != crc32($member->username . $member->mail . $member->password)) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - member_id ' . $payload->sub . ' - cookie hash invalid');

            return false;
        }

        return true;
    }

    public function encode(array $authUserData)
    {
        $payload = $this->buildPayload($authUserData);

        $this->token =  JWT::encode($payload, $this->config['secret'], $algo = 'HS256');

        return $this->token;
    }

    private function buildPayload(array $authUserData)
    {
        $date = new DateTime();
        $interval = DateInterval::createFromDateString($this->config['expire']['cookie']);
        $payload['sub'] = $authUserData['member_id'];
        $payload['exp'] = $date->add($interval)->getTimestamp();
        $payload['ext_id'] = $authUserData['external_id'];
        $payload['hash'] = crc32($authUserData['username'] . $authUserData['mail'] . $authUserData['password']);

        return $payload;
    }

    /**
     * @param string $jwt
     * @param bool   $verify
     * @return object
     * @throws Zend_Exception
     */
    public function decode($jwt, $verify = true)
    {
        try {
            return JWT::decode($jwt, $this->config['secret'], $verify);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - Can not decode JWT: ' . $e->getMessage());

            return new stdClass();
        }
    }

}