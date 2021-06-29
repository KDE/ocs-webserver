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

namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\ServerUrl;

class BuildBaseUrlStore extends AbstractHelper
{

    /**
     * @param string $action
     * @param array  $params
     * @param string $scheme
     *
     * @return string
     */
    public function __invoke($action = '', $params = null, $scheme = null)
    {
        /** @var ServerUrl $server_url */
        $server_url = $this->getView()->plugin('serverurl');
        $http_host = $server_url->setUseProxy(true)->getHost();
        $http_scheme = isset($scheme) ? $scheme : $server_url->getScheme();
        $host = $http_scheme . '://' . $http_host;

        $storeId = null;
        if (false === isset($params['store_id'])) {
            $domain_store_id = HelperUtil::getParam('domain_store_id');
            if ($domain_store_id) {
                $storeId = '/s/' . $domain_store_id;
            }
        } else {
            $storeId = "/s/{$params['store_id']}";
            unset($params['store_id']);
        }

        $storeConfig = $GLOBALS['ocs_store']->config;
        $baseurl = "{$host}{$storeId}";
        if (null != $storeConfig && $storeConfig->stay_in_context == false) {
            $baseurl = $GLOBALS['ocs_config']->settings->client->default->baseurl_store;
        }

        $url_param = '';
        if (is_array($params)) {
            array_walk($params, create_function('&$i,$k', '$i="$k/$i/";'));
            $url_param = implode('/', $params);
        }

        return "{$baseurl}/{$action}{$url_param}";
    }
}