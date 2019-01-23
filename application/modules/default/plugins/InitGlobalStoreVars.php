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
class Default_Plugin_InitGlobalStoreVars extends Zend_Controller_Plugin_Abstract
{
    private static $exceptionThrown = false;

    /**
     * @param Zend_Controller_Request_Abstract $request
     *
     * @throws Zend_Exception
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        /** @var Zend_Controller_Request_Http $request */
        parent::preDispatch($request);

        $storeHost = $this->getStoreHost($request);
        Zend_Registry::set('store_host', $storeHost);

        $storeConfigName = $this->getStoreConfigName($storeHost);
        Zend_Registry::set('store_config_name', $storeConfigName);

        Zend_Registry::set('store_template', $this->getStoreTemplate($storeConfigName));

        $config_store = $this->getConfigStore($storeHost);
        Zend_Registry::set('store_config', $config_store);
        Zend_Registry::set('config_store_tags', $this->getConfigStoreTags($config_store->store_id));
        Zend_Registry::set('store_category_list', $this->getStoreCategories($storeHost));
    }

    /**
     * @param Zend_Controller_Request_Http $request
     *
     * @return mixed
     * @throws Zend_Exception
     */
    private function getStoreHost($request)
    {
        $storeHost = '';
        $storeConfigArray = Zend_Registry::get('application_store_config_list');

        // search for store id param
        $requestStoreConfigName = null;
        if ($request->getParam('domain_store_id')) {
            $requestStoreConfigName =
                $request->getParam('domain_store_id') ? preg_replace('/[^-a-zA-Z0-9_\.]/', '', $request->getParam('domain_store_id'))
                    : null;

            $result = $this->searchForConfig($storeConfigArray, 'name', $requestStoreConfigName);

            if (isset($result['host'])) {
                $storeHost = $result['host'];

                return $storeHost;
            }
        }

        // search for host
        $httpHost = strtolower($request->getHttpHost());
        if (isset($storeConfigArray[$httpHost])) {
            return $storeConfigArray[$httpHost]['host'];
        }

        // search for default
        $result = $this->searchForConfig($storeConfigArray, 'default', 1);
        $storeHost = $result['host'];

        return $storeHost;
    }

    /**
     * alternative version which replace arraySearchConfig for PHP < 5.5.0
     *
     * @param $haystack
     * @param $key
     * @param $value
     *
     * @return array
     */
    private function searchForConfig($haystack, $key, $value)
    {
        if (false === is_array($haystack)) {
            return array();
        }
        foreach ($haystack as $element) {
            if (isset($element[$key]) and (strtolower($element[$key]) == strtolower($value))) {
                return $element;
            }
        }

        return array();
    }

    /**
     * @param string $storeHostName
     *
     * @return string
     * @throws Zend_Exception
     */
    private function getStoreConfigName($storeHostName)
    {
        $storeIdName = Zend_Registry::get('config')->settings->client->default->name; //set to default

        $store_config_list = Zend_Registry::get('application_store_config_list');

        // search for host
        $httpHost = strtolower($storeHostName);
        if (isset($store_config_list[$httpHost])) {
            return $store_config_list[$httpHost]['config_id_name'];
        } else {
            Zend_Registry::get('logger')->warn(__METHOD__ . '(' . __LINE__ . ') - $httpHost = ' . $httpHost
                . ' :: no config id name configured')
            ;
        }

        // search for default
        $result = $this->searchForConfig($store_config_list, 'default', 1);

        if (isset($result['config_id_name'])) {
            $storeIdName = $result['config_id_name'];
        } else {
            Zend_Registry::get('logger')->warn(__METHOD__ . '(' . __LINE__ . ') - no default store config name configured');
        }

        return $storeIdName;
    }

    /**
     * @param string $storeConfigName
     *
     * @return array|mixed
     * @throws Zend_Exception
     */
    private function getStoreTemplate($storeConfigName)
    {
        $storeTemplate = array();

        $fileNameConfig = APPLICATION_PATH . '/configs/client_' . $storeConfigName . '.ini.php';

        if (file_exists($fileNameConfig)) {
            $storeTemplate = require APPLICATION_PATH . '/configs/client_' . $storeConfigName . '.ini.php';
        } else {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - ' . $storeConfigName
                . ' :: can not access config file for store context.')
            ;
            $this->raiseException(__METHOD__ . ' - ' . $storeConfigName . ' :: can not access config file for store context');
        }

        return $storeTemplate;
    }

    /**
     * @param $message
     */
    private function raiseException($message)
    {
        if (self::$exceptionThrown) {
            return;
        }

        $request = $this->getRequest();
        // Repoint the request to the default error handler
        $request->setModuleName('default');
        $request->setControllerName('error');
        $request->setActionName('error');
        //$request->setDispatched(true);

        // Set up the error handler
        $error = new Zend_Controller_Plugin_ErrorHandler();
        $error->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
        $error->request = clone($request);
        $error->exception = new Zend_Exception($message);
        $request->setParam('error_handler', $error);
        //$this->setRequest($request);

        self::$exceptionThrown = true;
    }

    /**
     * @param string $storeHostName
     *
     * @return Default_Model_ConfigStore
     */
    private function getConfigStore($storeHostName)
    {
        $storeConfig = new Default_Model_ConfigStore($storeHostName);

        return $storeConfig;
    }

    private function getConfigStoreTags($store_id)
    {
        $modelConfigStoreTags = new Default_Model_ConfigStoreTags();

        $result = $modelConfigStoreTags->getTagsAsIdForStore($store_id);

        return $result;
    }

    /**
     * @param string $storeHostName
     *
     * @return array
     * @throws Zend_Exception
     */
    private function getStoreCategories($storeHostName)
    {
        $storeCategoryArray = Zend_Registry::get('application_store_category_list');

        if (isset($storeCategoryArray[$storeHostName])) {
            $storeCategories = $storeCategoryArray[$storeHostName];
            if (is_string($storeCategories)) {
                $storeCategories = array($storeCategories);
            }
        } else {
            Zend_Registry::get('logger')->warn(__METHOD__ . '(' . __LINE__ . ') - ' . $storeHostName
                . ' :: no categories for domain context configured. Using main categories instead')
            ;
            $modelCategories = new Default_Model_DbTable_ProjectCategory();
            $root = $modelCategories->fetchRoot();
            $storeCategories =
                $modelCategories->fetchImmediateChildrenIds($root['project_category_id'], $modelCategories::ORDERED_TITLE);
        }

        return $storeCategories;
    }

    /**
     * needs PHP >= 5.5.0
     *
     * @param $haystack
     * @param $key
     * @param $value
     *
     * @return array
     */
    private function arraySearchConfig($haystack, $key, $value)
    {
        if (PHP_VERSION_ID <= 50500) {
            return $this->searchForConfig($haystack, $key, $value);
        }
        if (false === is_array($haystack)) {
            return array();
        }
        $key = array_search($value, array_column($haystack, $key, 'host'));
        if ($key) {
            return $haystack[$key];
        }

        return array();
    }

}