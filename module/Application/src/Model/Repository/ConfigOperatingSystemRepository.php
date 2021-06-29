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

namespace Application\Model\Repository;

use Application\Model\Entity\ConfigOperatingSystem;
use Application\Model\Interfaces\ConfigOperatingSystemInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use RuntimeException;

class ConfigOperatingSystemRepository extends BaseRepository implements ConfigOperatingSystemInterface
{
    private $cache;

    public function __construct(
        AdapterInterface $db,
        StorageInterface $storage
    ) {
        parent::__construct($db);
        $this->_name = "config_operating_system";
        $this->_key = "os_id";
        $this->_prototype = ConfigOperatingSystem::class;
        $this->cache = $storage;
    }

    public function fetchOsNamesForJTable()
    {

        //$select = new Select();
        //$select->from($this->_name)->columns(array('name'))->group('name');

        $sql = new Sql($this->db);
        $select = $sql->select($this->_name)->columns(array('name'))->group('name');
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();


        $resultForSelect = array();
        foreach ($resultSet as $row) {
            $resultForSelect[] = array('DisplayText' => $row['name'], 'Value' => $row['os_id']);
        }

        return $resultForSelect;
    }

    public function deleteId($dataId)
    {
        throw new RuntimeException("method not implemented");
    }

    /**
     * @return array
     */
    public function fetchOperatingSystems()
    {
        $cache = $this->cache;

        if ($cache) {
            $cacheName = 'ConfigOperatingSystems';
            $configArray = $cache->getItem($cacheName);

            if (!$configArray || count($configArray) == 0) {
                $resultSet = $this->queryOperatingSystems();
                $configArray = $this->createOperatingSystemsArray($resultSet);
                $cache->setItem($cacheName, $configArray);
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
        $statement = $this->db->query($sql);

        return $statement->execute();
    }

    /**
     * @param array $resultSetConfig
     *
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
