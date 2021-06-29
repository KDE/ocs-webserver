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

use Exception;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Library\IpAddress;

class ViewsService extends BaseService implements Interfaces\ViewsServiceInterface
{

    const OBJECT_TYPE_PRODUCT = 10;
    const OBJECT_TYPE_COLLECTION = 12;
    const OBJECT_TYPE_MEMBERPAGE = 20;
    const OBJECT_TYPE_LOGIN = 30;
    const OBJECT_TYPE_LOGOUT = 32;
    const OBJECT_TYPE_DOWNLOAD = 40;
    const OBJECT_TYPE_MEDIA_VIDEO = 52;
    const OBJECT_TYPE_MEDIA_MUSIC = 54;
    const OBJECT_TYPE_MEDIA_BOOK = 56;
    protected $db;

    public static function saveViewProduct($product_id)
    {
        $sql = ("INSERT IGNORE INTO `stat_object_view` (`seen_at`, `ip_inet`, `object_type`, `object_id`, `ipv4`, `ipv6`, `fingerprint`, `user_agent`, `member_id_viewer`) VALUES (:seen, :ip_inet, :object_type, :product_id, :ipv4, :ipv6, :fp, :ua, :member)");
        self::saveViewObject(self::OBJECT_TYPE_PRODUCT, $product_id, $sql);
    }

    protected static function saveViewObject($object_type, $object_id, $sql)
    {

        $session = $GLOBALS['ocs_session'];
        $authMember = $GLOBALS['ocs_user'];
        $view_member_id = $authMember ? $authMember->member_id : null;
        $ipClient = self::get_ip_address();
        $remoteAddress = self::getRemoteAddress($ipClient);
        $ipClientv6 = filter_var($remoteAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? $remoteAddress : null;
        $ipClientv4 = filter_var($remoteAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $remoteAddress : null;
        $session_ipv6 = isset($session->stat_ipv6) ? inet_pton($session->stat_ipv6) : null;
        $session_ipv4 = isset($session->stat_ipv4) ? inet_pton($session->stat_ipv4) : null;
        $session_remote = isset($remoteAddress) ? inet_pton($remoteAddress) : null;
        $ip_inet = isset($session_ipv6) ? $session_ipv6 : (isset($session_ipv4) ? $session_ipv4 : $session_remote);
        $time = (round(time() / 300)) * 300;
        $seen_at = date('Y-m-d H:i:s', $time);

        $data = array(
            'seen'        => $seen_at,
            'ip_inet'     => $ip_inet,
            'object_type' => $object_type,
            'product_id'  => $object_id,
            'ipv6'        => $session->stat_ipv6 ? $session->stat_ipv6 : $ipClientv6,
            'ipv4'        => $session->stat_ipv4 ? $session->stat_ipv4 : $ipClientv4,
            'fp'          => $session->stat_fp ? $session->stat_fp : null,
            'ua'          => $_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : null,
            'member'      => $view_member_id,
        );

        try {
            $adapter = GlobalAdapterFeature::getStaticAdapter();
            $adapter->query($sql, $data);
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - ERROR write - ' . print_r($e, true));
        }
    }

    private static function get_ip_address()
    {
        return IpAddress::get_ip_address();
    }

    public static function getRemoteAddress($ipClient)
    {
        return IpAddress::getRemoteAddress($ipClient);
    }

    public static function validate_ip($ip)
    {
        return IpAddress::validate_ip($ip);
    }

    public static function saveViewMemberpage($member_id)
    {
        $sql = ("INSERT IGNORE INTO `stat_object_view` (`seen_at`, `ip_inet`, `object_type`, `object_id`, `ipv4`, `ipv6`, `fingerprint`, `user_agent`, `member_id_viewer`) VALUES (:seen, :ip_inet, :object_type, :product_id, :ipv4, :ipv6, :fp, :ua, :member)");
        self::saveViewObject(self::OBJECT_TYPE_MEMBERPAGE, $member_id, $sql);
    }

    public static function saveFileDownload($file_id)
    {
        $sql = ("INSERT IGNORE INTO `stat_object_download` (`seen_at`, `ip_inet`, `object_type`, `object_id`, `ipv4`, `ipv6`, `fingerprint`, `user_agent`, `member_id_viewer`) VALUES (:seen, :ip_inet, :object_type, :product_id, :ipv4, :ipv6, :fp, :ua, :member)");
        self::saveViewObject(self::OBJECT_TYPE_DOWNLOAD, $file_id, $sql);
    }

    public static function saveViewCollection($_projectId)
    {
        $sql = ("INSERT IGNORE INTO `stat_object_view` (`seen_at`, `ip_inet`, `object_type`, `object_id`, `ipv4`, `ipv6`, `fingerprint`, `user_agent`, `member_id_viewer`) VALUES (:seen, :ip_inet, :object_type, :product_id, :ipv4, :ipv6, :fp, :ua, :member)");
        self::saveViewObject(self::OBJECT_TYPE_COLLECTION, $_projectId, $sql);
    }

    public static function saveViewMusic($object_id)
    {
        $sql = ("INSERT IGNORE INTO `stat_object_view` (`seen_at`, `ip_inet`, `object_type`, `object_id`, `ipv4`, `ipv6`, `fingerprint`, `user_agent`, `member_id_viewer`) VALUES (:seen, :ip_inet, :object_type, :product_id, :ipv4, :ipv6, :fp, :ua, :member)");
        self::saveViewObject(self::OBJECT_TYPE_MEDIA_MUSIC, $object_id, $sql);
    }

    public static function saveViewVideo($object_id)
    {
        $sql = ("INSERT IGNORE INTO `stat_object_view` (`seen_at`, `ip_inet`, `object_type`, `object_id`, `ipv4`, `ipv6`, `fingerprint`, `user_agent`, `member_id_viewer`) VALUES (:seen, :ip_inet, :object_type, :product_id, :ipv4, :ipv6, :fp, :ua, :member)");
        self::saveViewObject(self::OBJECT_TYPE_MEDIA_VIDEO, $object_id, $sql);
    }

    public static function saveViewBook($object_id)
    {
        $sql = ("INSERT IGNORE INTO `stat_object_view` (`seen_at`, `ip_inet`, `object_type`, `object_id`, `ipv4`, `ipv6`, `fingerprint`, `user_agent`, `member_id_viewer`) VALUES (:seen, :ip_inet, :object_type, :product_id, :ipv4, :ipv6, :fp, :ua, :member)");
        self::saveViewObject(self::OBJECT_TYPE_MEDIA_BOOK, $object_id, $sql);
    }

}