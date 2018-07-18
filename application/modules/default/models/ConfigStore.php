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
 * Created: 13.09.2017
 */

class Default_Model_ConfigStore
{

    /**
     * @inheritDoc
     */
    public $store_id;
    public $host;
    public $name;
    public $config_id_name;
    public $mapping_id_name;
    public $order;
    public $is_client;
    public $google_id;
    public $piwik_id;
    public $package_type;
    public $cross_domain_login;
    public $is_show_title;
    public $is_show_home;
    public $layout_home;
    public $layout;
    public $created_at;
    public $changed_at;
    public $deleted_at;

    public function __construct($storeHostName)
    {
        $storeConfigArray = Zend_Registry::get('application_store_config_list');
        if (isset($storeConfigArray[$storeHostName])) {
            $storeConfig = $storeConfigArray[$storeHostName];
            $this->store_id = $storeConfig['store_id'];
            $this->host = $storeConfig['host'];
            $this->name = $storeConfig['name'];
            $this->config_id_name = $storeConfig['config_id_name'];
            $this->mapping_id_name = $storeConfig['mapping_id_name'];
            $this->order = $storeConfig['order'];
            $this->is_client = $storeConfig['is_client'];
            $this->google_id = $storeConfig['google_id'];
            $this->piwik_id = $storeConfig['piwik_id'];
            $this->package_type = $storeConfig['package_type'];
            $this->cross_domain_login = $storeConfig['cross_domain_login'];
            $this->is_show_title = $storeConfig['is_show_title'];
            $this->is_show_home = $storeConfig['is_show_home'];
            $this->layout_home = $storeConfig['layout_home'];
            $this->layout = $storeConfig['layout'];
            $this->created_at = $storeConfig['created_at'];
            $this->changed_at = $storeConfig['changed_at'];
            $this->deleted_at = $storeConfig['deleted_at'];

        } else {
            Zend_Registry::get('logger')->warn(__METHOD__ . '(' . __LINE__ . ') - ' . $host
                                               . ' :: no domain config context configured');
        }
    }

    
    
}
