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
class Default_View_Helper_BuildProductUrl
{

    /**
     * @param        $product_id
     * @param string $action
     * @param array  $params
     * @param bool   $withHost
     * @param string $scheme
     *
     * @return string
     */
    public function buildProductUrl($product_id, $action = '', $params = null, $withHost = false, $scheme = null)
    {
        if (empty($product_id)) {
            return '';
        }

        /** @var Zend_Controller_Request_Http $request */
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $host = '';
        if ($withHost) {
//            $member_host = Zend_Registry::get('config')->settings->member->page->server;
//            $http_host = Zend_Registry::get('config')->settings->member->product->server; // set http_host to product server
//
//            if (false === strpos($request->getHttpHost(), $member_host)) {
                $http_host = $request->getHttpHost();
//            }

            $http_scheme = isset($scheme) ? $scheme : $request->getScheme();
            $host = $http_scheme . '://' . $http_host;
        }

        $storeId = null;
        if (false === isset($params['store_id'])) {
            if ($request->getParam('domain_store_id')) {
                $storeId = $request->getParam('domain_store_id') . '/';
            }
        } else {
            $storeId = "{$params['store_id']}/";
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

        return "{$host}/{$storeId}p/{$product_id}/{$action}{$url_param}";
    }

}