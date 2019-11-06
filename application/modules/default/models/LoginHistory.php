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
class Default_Model_LoginHistory extends Default_Model_DbTable_LoginHistory
{
    
    /**
     * @param int   $objectId
     * @param int   $projectId
     * @param int   $userId
     * @param int   $activity_type_id
     * @param array $data array with ([type_id], [pid], description, title, image_small)
     *
     * @throws Zend_Exception
     */
    public static function log($memberId, $ip = null, $user_agent = null, $fingerprint = null)
    {
        $newEntry = array(
            'member_id'     => $memberId,
            'ip'    => $ip,
            'ip_inet'     => null!=$ip?inet_pton($ip):null,
            'browser'    => null!=$user_agent?$this->getBrowser($user_agent):null,
            'os'  => null!=$user_agent?$this->getOS($user_agent):null,
            'architecture'   => null,
            'fingerprint'    => $fingerprint
        );

        $sql = "
            INSERT INTO `login_history`
            SET `member_id` = :member_id, 
                `ip` = :ip, 
                `ip_inet` = :ip_inet, 
                `browser` = :browser,
                `os` = :os,
                `architecture` = :architecture,
                `fingerprint` = :fingerprint
                ;
        ";

        try {
            Zend_Db_Table::getDefaultAdapter()->query($sql, $newEntry);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ERROR write activity log - ' . print_r($e, true));
        }
    }
    
    private function getOS($user_agent) { 

        $os_platform  = "Unknown OS Platform";

        $os_array     = array(
                              '/windows nt 10/i'      =>  'Windows 10',
                              '/windows nt 6.3/i'     =>  'Windows 8.1',
                              '/windows nt 6.2/i'     =>  'Windows 8',
                              '/windows nt 6.1/i'     =>  'Windows 7',
                              '/windows nt 6.0/i'     =>  'Windows Vista',
                              '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                              '/windows nt 5.1/i'     =>  'Windows XP',
                              '/windows xp/i'         =>  'Windows XP',
                              '/windows nt 5.0/i'     =>  'Windows 2000',
                              '/windows me/i'         =>  'Windows ME',
                              '/win98/i'              =>  'Windows 98',
                              '/win95/i'              =>  'Windows 95',
                              '/win16/i'              =>  'Windows 3.11',
                              '/macintosh|mac os x/i' =>  'Mac OS X',
                              '/mac_powerpc/i'        =>  'Mac OS 9',
                              '/linux/i'              =>  'Linux',
                              '/ubuntu/i'             =>  'Ubuntu',
                              '/iphone/i'             =>  'iPhone',
                              '/ipod/i'               =>  'iPod',
                              '/ipad/i'               =>  'iPad',
                              '/android/i'            =>  'Android',
                              '/blackberry/i'         =>  'BlackBerry',
                              '/webos/i'              =>  'Mobile'
                        );

        foreach ($os_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $os_platform = $value;

        return $os_platform;
    }

    private function getBrowser($user_agent) {

        $browser        = "Unknown Browser";

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
                                '/mobile/i'    => 'Handheld Browser'
                         );

        foreach ($browser_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $browser = $value;

        return $browser;
    }


}
