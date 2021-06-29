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

use Application\Model\Entity\HiveContentCategory;
use Application\Model\Interfaces\HiveContentCategoryInterface;
use Exception;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;

class HiveContentCategoryRepository extends BaseRepository implements HiveContentCategoryInterface
{
    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "hive_content_category";
        $this->_key = "id";
        $this->_prototype = HiveContentCategory::class;
    }

    /**
     * @param int $category_id
     *
     * @return int Num of rows
     **/
    public function fetchCountProjectsForCategory($category_id)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql = "
                SELECT
                    count(1) AS 'count'
                FROM
                    hive_content
                WHERE
                    type = " . $fp('id') . "
                    AND is_imported = 0
    		    AND deletedat = 0
                GROUP BY type
               ";

        //$sql = $this->_db->quoteInto($sql, $category_id, 'INTEGER');
        //$result = $this->_db->fetchRow($sql);
        //return $result['count'];

        $statement = $this->db->query($sql);
        /** @var ResultInterface $resultSet */
        $resultSet = $statement->execute(['id' => $category_id])->current();

        if ($resultSet && count($resultSet) > 0) {
            return $resultSet['count'];
        } else {
            return 0;
        }

    }

    /**
     * @param int $category_id
     *
     * @return int Num of rows
     **/
    public function fetchCountAllProjectsForCategory($category_id)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };
        $sql = "
                SELECT
                    count(1) AS 'count'
                FROM
                    hive_content
                WHERE
                    type = " . $fp('id') . "
                    AND deletedat = 0
                GROUP BY type
               ";

        //$sql = $this->_db->quoteInto($sql, $category_id, 'INTEGER');
        //$result = $this->_db->fetchRow($sql);
        //return $result['count'];
        $statement = $this->db->query($sql);
        /** @var ResultInterface $resultSet */
        $resultSet = $statement->execute(['id' => $category_id])->current();

        if ($resultSet && count($resultSet) > 0) {
            return $resultSet['count'];
        } else {
            return 0;
        }

    }

    /**
     * @param int  $category_id Hive-Dat-Id
     * @param int  $startIndex  Default 0
     * @param int  $limit       Default 5
     * @param bool $alsoDeleted Default false
     *
     * @return int Num of rows
     */
    public function fetchAllProjectsForCategory($category_id, $startIndex = 0, $limit = 5, $alsoDeleted = false)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };
        $sql = "
                SELECT
                    *,convert(cast(convert(description using  latin1) as binary) using utf8) as description_utf8,from_unixtime(created) as created_at,from_unixtime(changed) as changed_at, CASE WHEN deletedat > 0 THEN FROM_UNIXTIME(deletedat) ELSE null END as deleted_at
                FROM
                    hive_content
                WHERE
                    type = " . $fp('id') . "
                    AND is_imported = 0
    			";
        if (!$alsoDeleted) {
            $sql .= "
                    AND deletedat = 0 AND status = 1
                    ";
        }
        $sql .= "
    		LIMIT " . $fp('limit') . " OFFSET " . $fp('offset') . "
               ";

        try {
            $statement = $this->db->query($sql);
            $resultSet = $statement->execute(['id' => $category_id, 'limit' => $limit, 'offset' => $startIndex]);

        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }

        return $resultSet;
    }

    /**
     * @param $cat_ids
     *
     * @return array
     */
    public function fetchHiveCategories($cat_ids)
    {
        return $this->queryCategories($cat_ids);
    }

    /**
     * @param $cat_ids
     *
     * @return array
     */
    private function queryCategories($cat_ids)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };
        $sql = "SELECT id, `desc`, pling_cat_id FROM hive_content_category WHERE id in (" . $fp('ids') . ") ORDER BY `desc`;";
        $statement = $this->db->query($sql);

        return $statement->execute(['ids' => $cat_ids]);
    }

    public function fetchOcsCategoryForHiveCategory($cat_id)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };
        $sql = "SELECT DISTINCT pling_cat_id FROM hive_content_category WHERE id = " . $fp('id') . ";";
        $statement = $this->db->query($sql);
        $resultSet = $statement->execute(['id' => $cat_id])->current();

        return $resultSet['pling_cat_id'];
    }

}
