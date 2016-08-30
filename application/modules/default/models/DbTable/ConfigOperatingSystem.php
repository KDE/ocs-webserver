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
class Default_Model_DbTable_ConfigOperatingSystem extends Local_Model_Table
{

    protected $_keyColumnsForRow = array('os_id');
    protected $_key = 'os_id';
    protected $_name = "config_operating_system";

    public function fetchOsNamesForJTable($id = null)
    {
        $select = $this->select()->from($this->_name)->columns('name')->group('name');

        $resultRows = $this->fetchAll($select);

        $resultForSelect = array();
        foreach ($resultRows as $row) {
            $resultForSelect[] = array('DisplayText' => $row['name'], 'Value' => $row['os_id']);
        }

        return $resultForSelect;
    }

    

    public function deleteId($dataId)
    {
        $sql = "DELETE FROM {$this->_name} WHERE {$this->_key} = ?";
        $this->_db->query($sql,$dataId)->execute();
    }
    
    
    /**
     * @return array
     */
    public function fetchOperatingSystems()
    {
    	if (Zend_Registry::isRegistered('cache')) {
    		/** @var Zend_Cache_Core $cache */
    		$cache = Zend_Registry::get('cache');
    		$cacheName = __FUNCTION__;
    		if (false == ($configArray = $cache->load($cacheName))) {
    			$resultSet = $this->queryOperatingSystems();
    			$configArray = $this->createOperatingSystemsArray($resultSet);
    			$cache->save($configArray, $cacheName);
    		}
    	} else {
    		$resultSet = $this->queryOperatingSystems();
    		$configArray = $this->createOperatingSystemsArray($resultSet);
    	}
    
    	return $configArray;
    }
    
    /**
     * @return array
     */
    private function queryOperatingSystems()
    {
    	$sql = "SELECT os_id, displayname FROM {$this->_name} ORDER BY `order`;";
    	$resultSet = $this->_db->fetchAll($sql);
    	return $resultSet;
    }
    
    /**
     * @param array $resultSetConfig
     * @return array
     */
    private function createOperatingSystemsArray($resultSetConfig)
    {
    	$result = array();
    	foreach ($resultSetConfig as $element) {
    		$result[$element['os_id']] = $element['displayname'];
    	}
    	return $result;
    }

}