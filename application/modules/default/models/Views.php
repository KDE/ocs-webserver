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
 */
class Default_Model_Views
{

    const OBJECT_TYPE_PRODUCT = 10;
    const OBJECT_TYPE_MEMBERPAGE = 20;
    const OBJECT_TYPE_LOGIN = 30;
    const OBJECT_TYPE_LOGOUT = 32;
    const OBJECT_TYPE_DOWNLOAD = 40;
    const OBJECT_TYPE_COLLECTION = 50;

    public static function saveViewProduct($product_id)
    {
        self::saveViewObject(self::OBJECT_TYPE_PRODUCT, $product_id);
    }

    public static function saveViewObject($object_type, $object_id)
    {
        $sql = ("INSERT IGNORE INTO `stat_page_impression` (`seen_at`, `ip_inet`, `object_type`, `object_id`, `ipv4`, `ipv6`, `fingerprint`, `user_agent`, `member_id_viewer`) VALUES (:seen, :ip_inet, :object_type, :product_id, :ipv4, :ipv6, :fp, :ua, :member)");
        $session = new Zend_Session_Namespace();
        $view_member_id = Zend_Auth::getInstance()->getIdentity()->member_id ? Zend_Auth::getInstance()->getIdentity()->member_id : null;
        $ipClient = Zend_Controller_Front::getInstance()->getRequest()->getClientIp();
        $ipClientv6 = filter_var($ipClient, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? $ipClient : null;
        $ipClientv4 = filter_var($ipClient, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $ipClient : null;
        $session_ipv6 = isset($session->stat_ipv6) ? inet_pton($session->stat_ipv6) : null;
        $session_ipv4 = isset($session->stat_ipv4) ? inet_pton($session->stat_ipv4) : null;
        $ip_inet = isset($session_ipv6) ? $session_ipv6 : (isset($session_ipv4) ? $session_ipv4 : inet_pton($ipClient));

        try {
            Zend_Db_Table::getDefaultAdapter()->query($sql, array(
                'seen'        => round(time() / 300),
                'ip_inet'     => $ip_inet,
                'object_type' => $object_type,
                'product_id'  => $object_id,
                'ipv6'        => $session->stat_ipv6 ? $session->stat_ipv6 : $ipClientv6,
                'ipv4'        => $session->stat_ipv4 ? $session->stat_ipv4 : $ipClientv4,
                'fp'          => $session->stat_fp ? $session->stat_fp : null,
                'ua'          => $_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : null,
                'member'      => $view_member_id
            ));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ERROR write - ' . print_r($e, true));
        }
    }

    public static function saveViewMemberpage($member_id)
    {
        self::saveViewObject(self::OBJECT_TYPE_MEMBERPAGE, $member_id);
    }

    public static function saveViewDownload($file_id)
    {
        self::saveViewObject(self::OBJECT_TYPE_DOWNLOAD, $file_id);
    }

    public static function saveViewCollection($_projectId)
    {
        self::saveViewObject(self::OBJECT_TYPE_COLLECTION, $_projectId);
    }

}