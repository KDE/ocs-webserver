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
class Default_Model_DbTable_BrowseListType extends Local_Model_Table
{

    const CACHE_STORES_CATEGORIES = 'browse_list_types';
    const CACHE_STORES_CONFIGS = 'browse_list_type_list';
    const CACHE_STORES_CONFIGS_BY_ID = 'browse_list_type_id_list';

    protected $_keyColumnsForRow = array('browse_list_type_id');
    protected $_key = 'browse_list_type_id';
    protected $_name = "browse_list_types";
    

    /**
     * @param null $id
     *
     * @return array
     * @throws Zend_Db_Select_Exception
     */
    public function fetchNamesForJTable($id = null)
    {
        $select = $this->select()->from($this->_name)->columns('name')->group('name');

        $resultRows = $this->fetchAll($select);

        $resultForSelect = array();
        foreach ($resultRows as $row) {
            $resultForSelect[] = array('DisplayText' => $row['name'], 'Value' => $row['browse_list_type_id']);
        }

        return $resultForSelect;
    }


    public function deleteId($dataId)
    {
        $sql = "DELETE FROM $this->_name WHERE {$this->_key} = ?";
        $this->_db->query($sql, $dataId)->execute();
//        return $this->delete(array('store_id = ?' => (int)$dataId));
    }

    public function delete($where)
    {
        $where = parent::_whereExpr($where);

        /**
         * Build the DELETE statement
         */
        $sql = "UPDATE " . parent::getAdapter()->quoteIdentifier($this->_name, true) . " SET is_active = 1, `deleted_at` = NOW() " . (($where) ? " WHERE $where" : '');

        /**
         * Execute the statement and return the number of affected rows
         */
        $stmt = parent::getAdapter()->query($sql);
        $result = $stmt->rowCount();

        return $result;
    }

    /**
     * @param bool $clearCache
     *
     * @return array
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     */
    public function fetchAllBrowseListTypeArray($clearCache = false)
    {
        if (Zend_Registry::isRegistered('cache')) {
            /** @var Zend_Cache_Core $cache */
            $cache = Zend_Registry::get('cache');
            $cacheName = self::CACHE_STORES_CONFIGS;

            if ($clearCache) {
                $cache->remove($cacheName);
            }

            if (false == ($configArray = $cache->load($cacheName))) {
                $resultSet = $this->queryBrowseListTypeArray();
                $configArray = $this->createBrowseListTypeArray($resultSet);
                $cache->save($configArray, $cacheName, array(), 28800);
            }
        } else {
            $resultSet = $this->querySectionArray();
            $configArray = $this->createBrowseListTypeArray($resultSet);
        }

        return $configArray;
    }

    /**
     * @return array
     */
    private function queryBrowseListTypeArray()
    {
        $sql = "SELECT * FROM $this->_name ORDER BY `name`;";
        $resultSet = $this->_db->fetchAll($sql);

        return $resultSet;
    }

    /**
     * @param array  $resultSetConfig
     * @param string $key
     *
     * @return array
     */
    private function createBrowseListTypeArray($resultSetConfig, $key = 'name')
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
    public function fetchAllBrowseListTypeByIdArray($clearCache = false)
    {
        if (Zend_Registry::isRegistered('cache')) {
            /** @var Zend_Cache_Core $cache */
            $cache = Zend_Registry::get('cache');
            $cacheName = self::CACHE_STORES_CONFIGS_BY_ID;

            if ($clearCache) {
                $cache->remove($cacheName);
            }

            if (false == ($configArray = $cache->load($cacheName))) {
                $resultSet = $this->queryBrowseListTypeArray();
                $configArray = $this->createBrowseListTypeArray($resultSet, 'section_id');
                $cache->save($configArray, $cacheName, array(), 28800);
            }
        } else {
            $resultSet = $this->queryBrowseListTypeArray();
            $configArray = $this->createBrowseListTypeArray($resultSet, 'section_id');
        }

        return $configArray;
    }
    
    /**
     * @param int $nodeId
     *
     * @return Zend_Db_Table_Row_Abstract
     * @throws Zend_Db_Table_Exception
     */
    public function findBrowseListType($nodeId)
    {
        $result = $this->find($nodeId);
        if (count($result) > 0) {
            return $result->current();
        }
        
        return null;
    }
    
}