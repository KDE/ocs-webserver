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

use Application\Model\Repository\LoginHistoryRepository;
use Application\Model\Service\Interfaces\LoginHistoryServiceInterface;
use Exception;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Library\IpAddress;

class LoginHistoryService extends BaseService implements LoginHistoryServiceInterface
{
    /**
     * @param int    $memberId
     * @param string $ip
     * @param string $ipv4
     * @param string $ipv6
     * @param string $user_agent
     * @param string $fingerprint
     */
    public static function log(
        $memberId,
        $ip = null,
        $ipv4 = null,
        $ipv6 = null,
        $user_agent = null,
        $fingerprint = null
    ) {

        if (empty($ip)) {
            $ip = IpAddress::get_ip_address();
        }

        $namespace = $GLOBALS['ocs_session'];

        if (empty($ipv4)) {
            $ipv4 = empty($namespace->stat_ipv4) ? null : $namespace->stat_ipv4;
        }

        if (empty($ipv6)) {
            $ipv6 = empty($namespace->stat_ipv6) ? null : $namespace->stat_ipv6;
        }

        if (empty($fingerprint)) {
            $fingerprint = empty($namespace->stat_fp) ? null : $namespace->stat_fp;
        }

        if (empty($user_agent)) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
        }

        $newEntry = array(
            'member_id'    => $memberId,
            'ip'           => $ip,
            'ip_inet'      => null != $ip ? inet_pton($ip) : null,
            'ipv4'         => $ipv4,
            'ipv4_inet'    => null != $ipv4 ? inet_pton($ipv4) : null,
            'ipv6'         => $ipv6,
            'ipv6_inet'    => null != $ipv6 ? inet_pton($ipv6) : null,
            'browser'      => self::getBrowser($user_agent),
            'os'           => self::getOS($user_agent),
            'architecture' => self::getArchitecture($user_agent),
            'fingerprint'  => $fingerprint,
            'user_agent'   => $user_agent,
        );

        try {
            $adapter = GlobalAdapterFeature::getStaticAdapter();

            $loginTable = new LoginHistoryRepository($adapter, null);
            $loginTable->insert($newEntry);
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - ERROR write activity log - ' . print_r($e, true));
        }
    }

    public static function getBrowser($user_agent)
    {

        $browser = "Unknown Browser";

        $browser_array = array(
            '/msie/i'      => 'Internet Explorer',
            '/firefox/i'   => 'Firefox',
            '/safari/i'    => 'Safari',
            '/chrome/i'    => 'Chrome',
            '/edge/i'      => 'Edge',
            '/opera/i'     => 'Opera',
            '/netscape/i'  => 'Netscape',
            '/maxthon/i'   => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i'    => 'Handheld Browser',
        );

        foreach ($browser_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $browser = $value;
            }
        }

        return $browser;
    }

    public static function getOS($user_agent)
    {

        $os_platform = "Unknown OS Platform";

        if (null == $user_agent) {
            return $os_platform;
        }


        $os_array = array(
            '/windows nt 10/i'      => 'Windows 10',
            '/windows nt 6.3/i'     => 'Windows 8.1',
            '/windows nt 6.2/i'     => 'Windows 8',
            '/windows nt 6.1/i'     => 'Windows 7',
            '/windows nt 6.0/i'     => 'Windows Vista',
            '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     => 'Windows XP',
            '/windows xp/i'         => 'Windows XP',
            '/windows nt 5.0/i'     => 'Windows 2000',
            '/windows me/i'         => 'Windows ME',
            '/win98/i'              => 'Windows 98',
            '/win95/i'              => 'Windows 95',
            '/win16/i'              => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i'        => 'Mac OS 9',
            '/linux/i'              => 'Linux',
            '/ubuntu/i'             => 'Ubuntu',
            '/iphone/i'             => 'iPhone',
            '/ipod/i'               => 'iPod',
            '/ipad/i'               => 'iPad',
            '/android/i'            => 'Android',
            '/blackberry/i'         => 'BlackBerry',
            '/webos/i'              => 'Mobile',
        );

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
            }
        }

        return $os_platform;
    }

    public static function getArchitecture($user_agent)
    {

        $os_platform = "Unknown";

        if (null == $user_agent) {
            return $os_platform;
        }


        $os_array = array(
            '/x64/i'     => 'x86_64',
            '/x86_64/i'  => 'x86_64',
            '/x32/i'     => 'x86_32',
            '/x86_32/i'  => 'x86_32',
            '/iPhone/i'  => 'iPhone',
            '/Android/i' => 'ARM',
        );

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
            }
        }

        return $os_platform;
    }
}