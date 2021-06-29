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

namespace Library\Tools;


use RuntimeException;

class PasswordEncrypt
{

    const PASSWORDSALT = 'ghdfklsdfgjkldfghdklgioerjgiogkldfgndfohgfhhgfhgfhgfhgfhfghfgnndf';
    const PASSWORD_TYPE_HIVE = 1;
    const PASSWORD_TYPE_OCS = 0;

    /**
     * @param string $password
     * @param int    $passwordType
     *
     * @return string
     */
    public static function get($password, $passwordType)
    {
        if (empty($password)) {
            throw new RuntimeException('password is empty');
        }

        return $passwordType == self::PASSWORD_TYPE_HIVE ? sha1((self::PASSWORDSALT . $password . self::PASSWORDSALT)) : md5($password);
    }

    /**
     * @param string $password
     *
     * @return string
     */
    public static function getLdap($password)
    {
        return '{MD5}' . base64_encode(md5($password, true));
    }

}