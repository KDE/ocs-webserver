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

class BuildSearchUrl extends AbstractHelper
{

    /**
     * @param        $searchstring
     * @param string $action
     * @param array  $params
     * @param bool   $withHost
     * @param string $scheme
     *
     * @return string
     */
    public function __invoke($searchstring, $action = '', $params = null, $withHost = false, $scheme = null)
    {
        if (empty($searchstring)) {
            return '';
        }

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
            if ($params['domain_store_id']) {
                $storeId = 's/' . $params['domain_store_id'] . '/';
            }
        } else {
            $storeId = "s/{$params['store_id']}/";
            unset($params['store_id']);
        }

        return "{$host}/{$storeId}s/{$searchstring}";
    }

}