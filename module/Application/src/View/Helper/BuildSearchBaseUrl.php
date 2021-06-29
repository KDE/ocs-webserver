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

class BuildSearchBaseUrl extends AbstractHelper
{

    /**
     *
     * @param array  $params
     * @param bool   $withHost
     * @param string $scheme
     *
     * @return string
     */
    public function __invoke($params = null, $withHost = false, $scheme = null)
    {
        /** @var ServerUrl $server_url */
        $server_url = $this->getView()->plugin('serverurl');
        $server_url->setUseProxy(true);
        $host = '';
        if ($withHost) {
            $http_host = $server_url->getHost();
            $http_scheme = isset($scheme) ? $scheme : $server_url->getScheme();
            $host = $http_scheme . '://' . $http_host;
        }

        $storeId = null;
        if (false === isset($params['store_id'])) {
            if ($this->getParam('domain_store_id')) {
                $storeId = 's/' . $this->getParam('domain_store_id') . '/';
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

        return "{$host}/{$storeId}find";
    }

    private function getParam($string)
    {
        $url_components = parse_url($_SERVER['REQUEST_URI']);
        if (!is_array($url_components)) {
            return null;
        }
        $params = array();

        if (in_array('query', $url_components)) {
            parse_str($url_components['query'], $params);
        }

        return isset($params[$string]) ? $params[$string] : null;
    }

}