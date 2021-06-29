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

class BuildMemberUrl extends AbstractHelper
{

    /**
     *
     * @param int    $member_ident
     * @param string $action
     * @param array  $params
     *
     * @return string
     */
    public function __invoke($member_ident, $action = '', $params = null)
    {

        /** @var ServerUrl $server_url */
        $server_url = $this->getView()->plugin('serverurl');
        $http_host = $server_url->setUseProxy(true)->getHost();
        $http_scheme = $server_url->getScheme();
        $host = '';
        $host = $http_scheme . '://' . $http_host;

        $storeId = null;
        if (false === isset($params['store_id'])) {
            if ($this->getParam('domain_store_id')) {
                $storeId = 's/' . $this->getParam('domain_store_id') . '/';
            }
        } else {
            $storeId = "s/{$params['store_id']}/";
            unset($params['store_id']);
        }

        //20190214 ronald: removed to stay in context, if set in store config
        $storeConfig = $GLOBALS['ocs_store'] ? $GLOBALS['ocs_store']->config : null;

        if (null != $storeConfig && $storeConfig->stay_in_context == false) {
            $baseurl = $GLOBALS['ocs_config']->settings->client->default->baseurl_member;
        } else {
            //20191125 but if the url is a real domain, then we do not need the /s/STORE_NAME
            if ($storeConfig->is_show_real_domain_as_url == true) {
                $baseurl = "{$host}";
            } else {
                //otherwiese send to baseurl_member
                //$baseurl = "{$host}{$storeId}";
                $baseurl = $GLOBALS['ocs_config']->settings->client->default->baseurl_member;
            }
        }


        $url_param = '';
        if (is_array($params)) {
            array_walk($params, create_function('&$i,$k', '$i="$k/$i/";'));
            $url_param = implode('/', $params);
        }

        $member_ident = strtolower($member_ident);
        $member_ident = urlencode($member_ident);

        $memberLink = "u";
        if (is_int($member_ident)) {
            $memberLink = "member";
        }

        return "{$baseurl}/{$memberLink}/{$member_ident}/{$action}{$url_param}";
    }

    private function getParam($string)
    {
        $url_components = parse_url($_SERVER['REQUEST_URI']);
        $params = array();

        if ($url_components && is_array($url_components) && in_array('query', $url_components)) {
            parse_str($url_components['query'], $params);
        }

        return isset($params[$string]) ? $params[$string] : null;
    }

}