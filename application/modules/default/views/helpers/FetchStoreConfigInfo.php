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
class Default_View_Helper_FetchStoreConfigInfo
{

    /**
     * @param string $storeHostName
     * @return string
     * @throws Zend_Exception
     */
    public function getDomainLogo($storeHostName)
    {
        $configName = $this->getConfigIdName($storeHostName);
        $clientFileId = '_' . $configName;
        $clientConfigPath = Zend_Registry::get('config')->settings->client->config->path;
        $clientConfigFileName = "client{$clientFileId}.ini.php";
        $clientConfigData = null;
        if (file_exists($clientConfigPath . $clientConfigFileName)) {
            $clientConfigData = require $clientConfigPath . $clientConfigFileName;
        } else {
            $clientConfigData = require $clientConfigPath . "default.ini.php";
        }

        return $clientConfigData['header-logo']['image-src'];
    }

   

    /**
     * @param string $hostname
     * @return string
     * @throws Zend_Exception
     */
    public function getConfigIdName($hostname)
    {
        $clientName = Zend_Registry::get('config')->settings->client->default->name; // set to default

        $store_config_list = Zend_Registry::get('application_store_config_list');

        if (isset($store_config_list[$hostname])) {
            $clientName = $store_config_list[$hostname]['config_id_name'];
            return $clientName;
        } else {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - ' . $hostname . ' :: no store id name configured');
        }

        // search for default config
        $result = $this->search($store_config_list, 'default', 1);

        if (isset($result[0]['config_id_name'])) {
            $clientName = $result[0]['config_id_name'];
        } else {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - ' . $hostname . ' :: no default store config configured');
        }

        return $clientName;
    }

    private function search($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, $this->search($subarray, $key, $value));
            }
        }

        return $results;
    }

    /**
     * @param string $storeHostName
     * @return int|array
     * @throws Zend_Exception
     */
    public function getStoreCategories($storeHostName)
    {
        $idCategory = null;
        if (Zend_Registry::isRegistered('application_store_category_list')) {
            $store_category_list = Zend_Registry::get('application_store_category_list');
            if (isset($store_category_list[$storeHostName])) {
                $idCategory = $store_category_list[$storeHostName];
            } else {
                Zend_Registry::get('logger')->warn(__METHOD__ . ' - storeIdName: ' . $storeHostName . ' no categories defined for this context');
            }
        }
        return $idCategory;
    }

} 