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
class Default_Model_DbTable_ConfigStore extends Local_Model_Table
{

    protected $_keyColumnsForRow = array('store_id');
    protected $_key = 'store_id';
    protected $_name = "config_store";

    /**
     * @param null $id
     * @return array
     * @throws Zend_Db_Select_Exception
     */
    public function fetchHostnamesForJTable($id = null)
    {
        $select = $this->select()->from($this->_name)->columns('host')->group('host');

        $resultRows = $this->fetchAll($select);

        $resultForSelect = array();
        foreach ($resultRows as $row) {
            $resultForSelect[] = array('DisplayText' => $row['host'], 'Value' => $row['store_id']);
        }

        return $resultForSelect;
    }

    /**
     * @return array
     */
    public function fetchAllStoresAndCategories()
    {
        if (Zend_Registry::isRegistered('cache')) {
            /** @var Zend_Cache_Core $cache */
            $cache = Zend_Registry::get('cache');
            $cacheName = __FUNCTION__;
            if (false == ($configArray = $cache->load($cacheName))) {
                $resultSet = $this->queryAllStoresAndCategories();
                $configArray = $this->createArrayAllStoresAndCategories($resultSet);
                $cache->save($configArray, $cacheName);
            }
        } else {
            $resultSet = $this->queryAllStoresAndCategories();
            $configArray = $this->createArrayAllStoresAndCategories($resultSet);
        }

        return $configArray;
    }

    /**
     * @return array
     */
    private function queryAllStoresAndCategories()
    {
        $sql = "
                SELECT
                    config_store.`host`,
                    config_store_category.store_id,
                    config_store_category.project_category_id
                FROM
                    config_store
                JOIN
                    config_store_category ON config_store.store_id = config_store_category.store_id
                JOIN
                    project_category ON project_category.project_category_id = config_store_category.project_category_id
                ORDER BY config_store.`host`,config_store_category.`order`,project_category.title;
        ";
        $resultSet = $this->_db->fetchAll($sql);
        return $resultSet;
    }

    /**
     * @param array $resultSetConfig
     * @return array
     */
    private function createArrayAllStoresAndCategories($resultSetConfig)
    {
        $result = array();
        foreach ($resultSetConfig as $element) {
            $result[$element['host']][] = $element['project_category_id'];
        }
        array_walk($result, create_function('&$v', '$v = (count($v) == 1)? array_pop($v): $v;'));
        return $result;
    }

    /**
     * @return array
     */
    public function fetchDomainConfigIdList()
    {
        if (Zend_Registry::isRegistered('cache')) {
            /** @var Zend_Cache_Core $cache */
            $cache = Zend_Registry::get('cache');
            $cacheName = __FUNCTION__;
            if (false == ($configArray = $cache->load($cacheName))) {
                $resultSet = $this->queryDomainConfigIdList();
                $configArray = $this->createDomainStoreIdArray($resultSet);
                $cache->save($configArray, $cacheName);
            }
        } else {
            $resultSet = $this->queryDomainConfigIdList();
            $configArray = $this->createDomainStoreIdArray($resultSet);
        }

        return $configArray;
    }

    /**
     * @return array
     */
    private function queryDomainConfigIdList()
    {
        $sql = "SELECT host, config_id_name FROM config_store ORDER BY host;";
        $resultSet = $this->_db->fetchAll($sql);
        return $resultSet;
    }

    /**
     * @param array $resultSetConfig
     * @return array
     */
    private function createDomainStoreIdArray($resultSetConfig)
    {
        $result = array();
        foreach ($resultSetConfig as $element) {
            $result[$element['host']] = $element['config_id_name'];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function fetchDomainsStoreNameList()
    {
        if (Zend_Registry::isRegistered('cache')) {
            /** @var Zend_Cache_Core $cache */
            $cache = Zend_Registry::get('cache');
            $cacheName = __FUNCTION__;
            if (false == ($configArray = $cache->load($cacheName))) {
                $resultSet = $this->queryDomains();
                $configArray = $this->createDomainsArray($resultSet);
                $cache->save($configArray, $cacheName);
            }
        } else {
            $resultSet = $this->queryDomains();
            $configArray = $this->createDomainsArray($resultSet);
        }

        return $configArray;
    }

    /**
     * @return array
     */
    private function queryDomains()
    {
        $sql = "SELECT host, name FROM config_store ORDER BY `order`;";
        $resultSet = $this->_db->fetchAll($sql);
        return $resultSet;
    }

    /**
     * @param array $resultSetConfig
     * @return array
     */
    private function createDomainsArray($resultSetConfig)
    {
        $result = array();
        foreach ($resultSetConfig as $element) {
            $result[$element['host']] = $element['name'];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function fetchDomainObjects()
    {
        $sql = "SELECT *  FROM config_store ORDER BY `order`;";
        $resultSet = $this->_db->fetchAll($sql);
        return $resultSet;
    }

    public function deleteId($dataId)
    {
        $sql = "DELETE FROM config_store WHERE {$this->_key} = ?";
        $this->_db->query($sql, $dataId)->execute();
    }

    /**
     * @return array
     */
    public function fetchAllStoresConfigArray()
    {
        if (Zend_Registry::isRegistered('cache')) {
            /** @var Zend_Cache_Core $cache */
            $cache = Zend_Registry::get('cache');
            $cacheName = __FUNCTION__;
            if (false == ($configArray = $cache->load($cacheName))) {
                $resultSet = $this->queryStoreConfigArray();
                $configArray = $this->createStoreConfigArray($resultSet);
                $cache->save($configArray, $cacheName);
            }
        } else {
            $resultSet = $this->queryStoreConfigArray();
            $configArray = $this->createStoreConfigArray($resultSet);
        }

        return $configArray;
    }

    /**
     * @return array
     */
    private function queryStoreConfigArray()
    {
        $sql = "SELECT * FROM config_store ORDER BY `order`;";
        $resultSet = $this->_db->fetchAll($sql);
        return $resultSet;
    }

    /**
     * @param array $resultSetConfig
     * @return array
     */
    private function createStoreConfigArray($resultSetConfig)
    {
        $result = array();
        foreach ($resultSetConfig as $element) {
            $result[$element['host']] = $element;
        }
        return $result;
    }

}