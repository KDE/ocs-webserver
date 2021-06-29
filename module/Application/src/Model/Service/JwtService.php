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
 **/

namespace Application\Model\Service;

use DateInterval;
use DateTime;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Library\Tools\JWT;
use RuntimeException;

class JwtService
{

    public static function encode($member_id)
    {
        $config = $GLOBALS['ocs_config']->settings->jwt;
        $member_data = self::getMemberData($member_id);
        $payload = self::buildPayload($member_data, $config);

        return JWT::encode($payload, $config->secret, $algo = 'HS256');
    }

    private static function getMemberData($member_id)
    {
        $adapter = GlobalAdapterFeature::getStaticAdapter();
        $sql = "select external_id,username,mail,password from member m
                LEFT JOIN member_external_id AS mei ON mei.member_id = m.member_id
                where m.member_id=
                " . $member_id;
        $statement = $adapter->driver->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            $result = $resultSet->toArray();

            return array_pop($result);
        } else {
            throw new RuntimeException(sprintf('Failed retrieving member_id ' . $member_id . ' from table.', $sql));
        }
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
        $config = $GLOBALS['ocs_config']->settings->jwt;

        return JWT::decode($jwt, $config->secret, $verify);
    }

    public static function encodeFromArray(array $payload)
    {
        $config = $GLOBALS['ocs_config']->settings->jwt;

        return JWT::encode($payload, $config->secret, $algo = 'HS256');
    }
}