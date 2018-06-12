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

class Default_View_Helper_FetchTopProductsForStore
{

    public function fetchTopProducts($pageLimit = 10)
    {
        $filter = array();
        
        $storeCatIds = Zend_Registry::isRegistered('store_category_list') ? Zend_Registry::get('store_category_list') : null;
        $filter['category'] = $storeCatIds;
        $filter['order'] = "top";
        
        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
        if ($storeConfig) {
            $filter['package_type'] = $storeConfig['package_type'];
        }
        $modelProject = new Default_Model_Project();
        $requestedElements = $modelProject->fetchProjectsByFilter($filter, $pageLimit, 0);

        return $requestedElements;
    }

} 