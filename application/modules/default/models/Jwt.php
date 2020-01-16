<?php

include_once APPLICATION_LIB . '/JWT.php';

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
class Default_Model_Jwt
{

    public static function encode($member_id)
    {
        $config = Zend_Registry::get('config')->settings->jwt;
        $member_data = self::getMemberData($member_id);
        $payload = self::buildPayload($member_data, $config);

        return JWT::encode($payload, $config->secret, $algo = 'HS256');
    }

    private static function getMemberData($member_id)
    {
        $model = new Default_Model_Member();

        return $model->fetchMemberData($member_id)->toArray();
    }

    private static function buildPayload($member_data, $config)
    {
        $date = new DateTime();
        $interval = DateInterval::createFromDateString($config->expire->cookie);
        $payload['exp'] = $date->add($interval)->getTimestamp();
        $payload['vt'] = 4; //type=cookie_ltat
        $payload['user'] = $member_data['external_id'];
        $payload['hash'] = crc32($member_data['username'] . $member_data['mail'] . $member_data['password']);

        return $payload;
    }

    public static function decode($jwt, $verify = true)
    {
        $config = Zend_Registry::get('config')->settings->jwt;

        return JWT::decode($jwt, $config->secret, $verify);
    }

    public static function encodeFromArray(array $payload)
    {
        $config = Zend_Registry::get('config')->settings->jwt;

        return JWT::encode($payload, $config->secret, $algo = 'HS256');
    }

}