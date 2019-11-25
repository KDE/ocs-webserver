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
class Default_View_Helper_BuildMemberUrl extends Zend_View_Helper_Abstract
{

    /**
     * @param int|string $member_ident
     * @param string $action
     * @param array  $params
     *
     * @return string
     * @throws Zend_Exception
     */
    public function buildMemberUrl($member_ident, $action = '', $params = null)
    {
        /** @var Zend_Controller_Request_Http $request */
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $http_scheme = $request->getScheme();
        
        $baseurl = '';
        
        $http_host = $request->getHttpHost();
        $http_scheme = isset($scheme) ? $scheme : $request->getScheme();
        $host = $http_scheme . '://' . $http_host;

        $storeId = null;
        if (false === isset($params['store_id'])) {
            if ($request->getParam('domain_store_id')) {
                $storeId = '/s/' . $request->getParam('domain_store_id');
            }
        } else {
            $storeId = "/s/{$params['store_id']}";
            unset($params['store_id']);
        }

        //20190214 ronald: removed to stay in context, if set in store config
        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
        
        if(null != $storeConfig && $storeConfig->stay_in_context == false) {
            $baseurl = Zend_Registry::get('config')->settings->client->default->baseurl_member;
        } else {
            //20191125 but if the url is a real domain, then we do not need the /s/STORE_NAME
            if($storeConfig->is_show_real_domain_as_url == true) {
                $baseurl = "{$host}";
            } else {
                //otherwiese send to baseurl_member
                //$baseurl = "{$host}{$storeId}";
                $baseurl = Zend_Registry::get('config')->settings->client->default->baseurl_member;
            }
        }
        

        $url_param = '';
        if (is_array($params)) {
            array_walk($params, create_function('&$i,$k', '$i="$k/$i/";'));
            $url_param = implode('/', $params);
        }

        if ($action != '') {
            $action = $action . '/';
        }
        
        $member_ident = strtolower($member_ident);
        $member_ident = urlencode($member_ident);
        
        $memberLink = "u";
        if(is_int($member_ident)) {
            $memberLink = "member";
        }

        return "{$baseurl}/{$memberLink}/{$member_ident}/{$action}{$url_param}";
    }


    /**
     * @param int    $member_id
     * @param string $action
     * @param array  $params
     * @param bool   $withHost
     * @param string $scheme
     *
     * @return string
     */
    /*
    public function buildMemberUrl($member_id, $action = '', $params = null, $withHost = false, $scheme = null)
    {      
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $host = '';
        if ($withHost) {
            $http_scheme = isset($scheme) ? $scheme : $request->getScheme();
            $host = $http_scheme . '://' . $request->getHttpHost();
        }

        $storeId = null;
        if (false === isset($params['store_id'])) {
            if ($request->getParam('domain_store_id')) {
                $storeId = 's/' . $request->getParam('domain_store_id') . '/';
            }
        } else {
            $storeId = "s/{$params['store_id']}/";
            unset($params['store_id']);
        }

        $url_param = '';
        if (is_array($params)) {
            array_walk($params, create_function('&$i,$k', '$i="$k/$i/";'));
            $url_param = implode('/', $params);
        }

        if ($action != '') {
            $action = $action . '/';
        }

        return "{$host}/{$storeId}member/{$member_id}/{$action}{$url_param}";
    }
    */

    public function buildExternalUrl($member_id, $action = null, $params = null)
    {
        $url_param = '';
        if (is_array($params)) {
            array_walk($params, create_function('&$i,$k', '$i="$k/$i/";'));
            $url_param = implode('/', $params);
        }

        if (isset($action)) {
            $action .= '/';
        }

        $member_host = Zend_Registry::get('config')->settings->member->page->server;

        return "//{$member_host}/me/{$member_id}/{$action}{$url_param}";
    }

}