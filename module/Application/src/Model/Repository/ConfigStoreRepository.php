<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Model\Repository;

use Application\Model\Entity\ConfigStore;
use Application\Model\Interfaces\ConfigStoreInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;

class ConfigStoreRepository extends BaseRepository implements ConfigStoreInterface
{
    const CACHE_STORES_CATEGORIES = 'stores_categories_list';
    const CACHE_STORES_CONFIGS = 'stores_configs_list';
    const CACHE_STORE_CONFIG = 'store_config';
    const CACHE_STORES_CONFIGS_BY_ID = 'stores_configs_id_list';
    private $cache;

    public function __construct(
        AdapterInterface $db,
        StorageInterface $storage
    ) {
        parent::__construct($db);
        $this->_name = "config_store";
        $this->_key = "store_id";
        $this->_prototype = ConfigStore::class;
        $this->cache = $storage;
    }

    public function fetchHostnamesForJTable($id = null)
    {
        $sql = "SELECT `host`,`store_id` FROM `config_store` order by host asc";
        $resultSet = $this->fetchAll($sql, null, false);
        $resultForSelect = array();
        foreach ($resultSet as $row) {
            $resultForSelect[] = array('DisplayText' => $row->host, 'Value' => $row->store_id);
        }

        return $resultForSelect;
    }

    public function fetchAllStoresAndCategories($clearCache = false)
    {
        $cache = $this->cache;
        $cacheName = self::CACHE_STORES_CATEGORIES;
        if ($clearCache) {
            $cache->setItem($cacheName, null);
        }
        if (false == ($configArray = $cache->getItem($cacheName))) {
            $resultSet = $this->queryAllStoresAndCategories();
            $configArray = $this->createArrayAllStoresAndCategories($resultSet);
            $cache->setItem($cacheName, $configArray);
        }

        return $configArray;
    }

    /**
     * @return array|ResultSet
     */
    private function queryAllStoresAndCategories()
    {
        $sql = "
                SELECT
                    `config_store`.`host`,
                    `config_store_category`.`store_id`,
                    `config_store_category`.`project_category_id`
                FROM
                    `config_store`
                JOIN
                    `config_store_category` ON `config_store`.`store_id` = `config_store_category`.`store_id`
                JOIN
                    `project_category` ON `project_category`.`project_category_id` = `config_store_category`.`project_category_id`
                ORDER BY `config_store`.`host`,`config_store_category`.`order`,`project_category`.`title`;
        ";

        return $this->fetchAll($sql, null, false);
    }

    /**
     * @param array $resultSetConfig
     *
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
        $cache = $this->cache;
        $cacheName = __FUNCTION__;

        if (false == ($configArray = $cache->getItem($cacheName))) {
            $resultSet = $this->queryDomainConfigIdList();
            $configArray = $this->createDomainStoreIdArray($resultSet);
            $cache->setItem($cacheName, $configArray);
        }

        return $configArray;
    }

    /**
     * @return resultSet ArrayObject
     */
    public function queryDomainConfigIdList()
    {
        $sql = "SELECT `host`, `config_id_name` FROM `config_store` ORDER BY `host`;";

        return $this->fetchAll($sql, null, false);
    }

    /**
     * @param array $resultSetConfig
     *
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
        $cache = $this->cache;
        $cacheName = __FUNCTION__;

        if (false == ($configArray = $cache->getItem($cacheName))) {
            $resultSet = $this->queryDomains();
            $configArray = $this->createDomainsArray($resultSet);
            $cache->setItem($cacheName, $configArray);
        }

        return $configArray;
    }

    /**
     * @return array
     */
    private function queryDomains()
    {
        $sql = "SELECT `host`, `name` FROM `config_store` ORDER BY `order`;";

        return $this->fetchAll($sql, null, false);
    }

    /**
     * @param array $resultSetConfig
     *
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
        $sql = "SELECT *  FROM `config_store` ORDER BY `order`;";

        return $this->fetchAll($sql, null, false);
        // $resultSet = $this->db->driver->getConnection()->execute($sql);
        // return $resultSet;
    }

    /**
     * @param $name
     *
     * @return array
     */
    public function fetchDomainObjectsByName($name)
    {
        $sql = "SELECT * FROM `config_store` WHERE `name`='" . $name . "'";
        //$resultSet = $this->db->driver->getConnection()->execute($sql); 
        //return array_pop($resultSet);
        return $this->fetchRow($sql);
    }

    //deprecated
    public function _deleteId($dataId)
    {
        $this->deleteReal($dataId);
    }

    //deprecated use delete(id) instead
    public function _delete($where)
    {
        /*
        $where = parent::_whereExpr($where);
        
        $sql = "UPDATE " . parent::getAdapter()->quoteIdentifier($this->_name,
                true) . " SET `deleted_at` = NOW() " . (($where) ? " WHERE $where" : '');

        $stmt = parent::getAdapter()->query($sql);
        $result = $stmt->rowCount();

        return $result;
        */
        return null;
    }

    /**
     * @param bool $clearCache
     *
     * @return array
     */
    public function fetchAllStoresConfigArray($clearCache = false)
    {
        $cache = $this->cache;
        $cacheName = self::CACHE_STORES_CONFIGS;
        if ($clearCache) {
            $cache->setItem($cacheName, null);
        }
        if (false == ($configArray = $cache->getItem($cacheName))) {
            $resultSet = $this->queryStoreConfigArray();
            $configArray = $this->createStoreConfigArray($resultSet);
            $cache->setItem($cacheName, $configArray);
        }

        return $configArray;
    }

    /**
     * @return array
     */
    private function queryStoreConfigArray()
    {
        $sql = "SELECT * FROM `config_store` ORDER BY `order`;";

        return $this->fetchAll($sql, null, true);
    }

    /**
     * @param array  $resultSetConfig
     * @param string $key
     *
     * @return array
     */
    private function createStoreConfigArray($resultSetConfig, $key = 'host')
    {
        $result = array();
        foreach ($resultSetConfig as $element) {
            $result[$element[$key]] = $element;
        }

        return $result;
    }

    /**
     * @param bool $clearCache
     *
     * @return array
     */
    public function fetchAllStoresConfigByIdArray($clearCache = false)
    {
        $cache = $this->cache;
        $cacheName = self::CACHE_STORES_CONFIGS_BY_ID;
        if ($clearCache) {
            $cache->setItem($cacheName, null);
        }
        if (false == ($configArray = $cache->getItem($cacheName))) {
            $resultSet = $this->queryStoreConfigArray();
            $configArray = $this->createStoreConfigArray($resultSet, 'store_id');
            $cache->setItem($cacheName, $configArray);
        }

        return $configArray;

    }

    public function fetchConfigForStore($store_id, $clearCache = false)
    {

        $cache = $this->cache;
        $cacheName = self::CACHE_STORE_CONFIG . "_{$store_id}";
        if ($clearCache) {
            $cache->setItem($cacheName, null);
        }
        if (false == ($configArray = $cache->getItem($cacheName))) {
            $configArray = $this->queryStoreConfig($store_id);
            $cache->setItem($cacheName, $configArray);
        }

        return $configArray;
    }

    private function queryStoreConfig($store_id)
    {
        $sql = "SELECT * FROM `config_store` WHERE `store_id` = :store";

        return $this->fetchRow($sql, array('store' => $store_id));
    }

    /**
     * @return array
     */
    public function fetchDefaultStoreId()
    {
        $sql = "SELECT `store_id`, `package_type` FROM `config_store` WHERE `default` = 1";

        return $this->fetchRow($sql);
    }

}