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
class Default_View_Helper_BuildBaseUrlStore extends Zend_View_Helper_Abstract
{

    /**
     * @param string $action
     * @param array  $params
     * @param string $scheme
     *
     * @return string
     * @throws Zend_Exception
     */
    public function buildBaseUrlStore($action = '', $params = null, $scheme = null)
    {
        /** @var Zend_Controller_Request_Http $request */
        $request = Zend_Controller_Front::getInstance()->getRequest();

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

        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;

        $baseurl = "{$host}{$storeId}";
        if (null != $storeConfig && $storeConfig->stay_in_context == false) {
            $baseurl = Zend_Registry::get('config')->settings->client->default->baseurl_store;
        }

        $url_param = '';
        if (is_array($params)) {
            array_walk($params, create_function('&$i,$k', '$i="$k/$i/";'));
            $url_param = implode('/', $params);
        }

        return "{$baseurl}/{$action}{$url_param}";
    }

   

}