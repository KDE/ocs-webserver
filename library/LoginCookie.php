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
 * Created: 26.11.2018
 */

namespace Library;

use DateInterval;
use DateTime;
use Library\Tools\JWT;

class LoginCookie
{

    const secret = 'jrEeZKyGCCVJCa7Hsgwqv5wf21GREJ9j';

    public static function createJwt($data, $expire)
    {
        $payload = self::buildPayload($data, $expire);

        return JWT::encode($payload, self::secret, $algo = 'HS256');
    }

    private static function buildPayload($data, $expire)
    {
        $date = new DateTime();
        $interval = DateInterval::createFromDateString($expire);
        $payload['exp'] = $date->add($interval)->getTimestamp();
        $payload['data'] = $data;

        return $payload;
    }

    public static function readJwt($key)
    {
        $payload = JWT::decode($key, self::secret, true);

        if (self::isExpired($payload->exp)) {
            return false;
        }

        return $payload->data;
    }

    public static function isExpired($expiration_time)
    {
        $time = time();

        return $time < $expiration_time;
    }

}