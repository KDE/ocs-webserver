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
 *
 */

namespace Library;


class IpAddress
{
    /**
     * detects ip address for Request from $_SERVER array
     *
     * @return string|null
     */
    public static function get_ip_address()
    {
        foreach (array(
                     'HTTP_CLIENT_IP',
                     'HTTP_X_FORWARDED_FOR',
                     'HTTP_X_FORWARDED',
                     'HTTP_X_CLUSTER_CLIENT_IP',
                     'HTTP_FORWARDED_FOR',
                     'HTTP_FORWARDED',
                     'REMOTE_ADDR',
                 ) as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return null;
    }

    /**
     * returns the first valid ip address from comma separated string which is not from local address range
     *
     * @param string $ipClient
     *
     * @return mixed|string|null
     */
    public static function getRemoteAddress($ipClient)
    {
        $ipList = explode(',', $ipClient);
        foreach ($ipList as $ip) {
            if (self::validate_ip($ip)) {
                return $ip;
            }
        }

        return null;
    }

    /**
     * check the given ip for local and reserved address range
     *
     * @param string $ip
     *
     * @return bool
     */
    public static function validate_ip($ip)
    {
        $filter = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        if (APPLICATION_ENV == 'development') {
            $filter = FILTER_FLAG_NO_RES_RANGE;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, $filter) === false) {
            return false;
        }

        return true;
    }
}