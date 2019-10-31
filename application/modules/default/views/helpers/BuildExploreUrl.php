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

class Default_View_Helper_BuildExploreUrl
{

    /**
     * @param array $options
     * @param null|array $params
     * @param bool|false $withHost
     * @return string
     */
    public function buildFromArray($options, $params = null, $withHost = false)
    {
        $category = null;
        if (isset($options['category'])) {
            $category = $options['category'];
        }
        $filter = null;
        if (isset($options['filter'])) {
            $filter = $options['filter'];
        }
        $order = null;
        if (isset($options['order'])) {
            $order = $options['order'];
        }
        
        if(isset($options['fav']))
        {
            if($params==null){
                $params=array();
                $params['fav'] = 1;
            }else{
                $params['fav'] = 1;
            }
        }

        return $this->buildExploreUrl($category, $filter, $order, $params, $withHost);
    }

    /**
     * @param int $categoryId
     * @param int $filterId
     * @param string $order
     * @param null $params
     * @param bool $withHost
     * @return string
     */
    public function buildExploreUrl($categoryId = null, $filterId = null, $order = null, $params = null, $withHost = false)
    {
        /** @var Zend_Controller_Request_Http $request */
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $host = '';
        if ($withHost) {
            $host = $request->getScheme() . '://' . $request->getHttpHost();
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

        $paramPage = '';
        if (isset($params['page'])) {
            $paramPage = "page/{$params['page']}/";
            unset($params['page']);
        }

        $url_param = '';       
        if (is_array($params)) {            
            array_walk($params, create_function('&$i,$k', '$i="$k/$i/";'));
            $url_param = implode('/', $params);                      
        }


        $paramCategory = '';
        if (($categoryId != '') AND (false === is_array($categoryId))) {
            $paramCategory = "cat/{$categoryId}/";
        }

        $paramFilter = '';
        if ($filterId != '') {
            $paramFilter = "fil/{$filterId}/";
        }

        $paramOrder = '';
        if ($order != '') {
            $paramOrder = "ord/{$order}/";
        }

        return "{$host}/{$storeId}browse/{$paramCategory}{$paramPage}{$paramFilter}{$paramOrder}{$url_param}";
    }

}