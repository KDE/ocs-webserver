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

use Application\Model\Entity\ConfigStoreCategory;
use Application\Model\Interfaces\ConfigStoreCategoryInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\ResultSet\ResultSetInterface;

class ConfigStoreCategoryRepository extends BaseRepository implements ConfigStoreCategoryInterface
{
    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "config_store_category";
        $this->_key = "store_category_id";
        $this->_prototype = ConfigStoreCategory::class;
    }

    /**
     * @param int $dataId
     */
    public function deleteId($dataId)
    {
        // $sql = "DELETE FROM {$this->_name} WHERE {$this->_key} = ?";        
        // $this->_db->query($sql,$dataId);
        $this->deleteReal($dataId);
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function fetchAllCategoriesForStore($storeId)
    {

        $active = ProjectCategoryRepository::CATEGORY_ACTIVE;
        $notDeleted = ProjectCategoryRepository::CATEGORY_NOT_DELETED;

        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql = "
                SELECT pc2.project_category_id
                FROM project_category AS pc, project_category AS pc2
                WHERE pc.project_category_id IN (SELECT DISTINCT csc.project_category_id
                                                 FROM config_store_category AS csc
                                                   JOIN project_category AS pc ON csc.project_category_id = pc.project_category_id AND pc.is_active = 1
                                                 WHERE csc.store_id = " . $fp('id') . ")
                      AND pc2.lft BETWEEN pc.lft AND pc.rgt
                      AND pc2.is_active = " . $fp('active') . " AND pc2.is_deleted = " . $fp('notDeleted') . "
                ORDER BY pc2.lft;
                ";
        $statement = $this->db->query($sql);
        /** @var ResultSetInterface $resultSet */
        $resultSet = $statement->execute(['id' => $storeId, 'active' => $active, 'notDeleted' => $notDeleted]);
        $result = array();
        foreach ($resultSet as $element) {
            $result[] = $element['project_category_id'];
        }

        //$values = array_map(function($row) { return $row['project_category_id']; }, $resultSet);
        //return $values;
        return $result;

    }

    /**
     * @param int|array $listCatId
     *
     * @return array
     */
    public function fetchStoresForCatdId($listCatId)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $inQuery = $listCatId;
        if (is_array($listCatId)) {
            $inQuery = implode(',', array_fill(0, count($listCatId), ''));
        }

        $sql = '
            SELECT cs.store_id, cs.config_id_name 
            FROM config_store_category as csc
            join config_store as cs on cs.store_id = csc.store_id 
            where csc.project_category_id in (' . $fp('inQuery') . ')
        ';


        $statement = $this->db->query($sql);
        /** @var ResultSet $result */
        $result = $statement->execute(['inQuery' => $inQuery]);

        if (count($result) > 0) {
            return $result;
        } else {
            return array();
        }
    }

    public function fetchCatIdsForStore($store_id)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql = "
            SELECT csc.project_category_id 
            FROM config_store_category AS csc
            JOIN project_category AS pc ON pc.project_category_id = csc.project_category_id
            WHERE csc.store_id = " . $fp('id') . "
            AND csc.deleted_at IS NULL
             ORDER BY csc.`order`, pc.title
        ";
        $statement = $this->db->query($sql);
        /** @var ResultSet $resultSet */
        $resultSet = $statement->execute(['id' => $store_id]);
        //$values = array_map(function($row) { return $row['project_category_id']; }, $results);
        //return $values;
        $result = array();
        foreach ($resultSet as $element) {
            $result[] = $element['project_category_id'];
        }

        return $result;
    }

}
