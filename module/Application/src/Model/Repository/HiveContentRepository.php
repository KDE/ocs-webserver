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

use Application\Model\Entity\HiveContent;
use Application\Model\Interfaces\HiveContentInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSetInterface;

class HiveContentRepository extends BaseRepository implements HiveContentInterface
{
    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "hive_content";
        $this->_key = "id";
        $this->_prototype = HiveContent::class;
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

        $statement = $this->db->query($sql);
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
                    *,convert(cast(convert(description using  latin1) as binary) using utf8) as description_utf8,convert(cast(convert(name using  latin1) as binary) using utf8) as name_utf8,convert(cast(convert(changelog using  latin1) as binary) using utf8) as changelog_utf8,from_unixtime(created) as created_at,from_unixtime(changed) as changed_at, CASE WHEN deletedat > 0 THEN FROM_UNIXTIME(deletedat) ELSE null END as deleted_at
                FROM
                    hive_content
                WHERE
                    type = " . $fp('id') . "
                    AND is_imported = 0
    			";
        if (!$alsoDeleted) {
            $sql .= "
                    AND deletedat = 0
                    AND status = 1";
        }
        $sql .= "
                    LIMIT " . $limit . " OFFSET " . $startIndex . "
               ";

        $statement = $this->db->query($sql);

        return $statement->execute(['id' => $category_id]);

    }

    /**
     * @return int Num of rows
     **/
    public function fetchCountProjects()
    {

        $sql = "
                SELECT
                    count(1) AS 'count'
                FROM
                    `hive_content`
                WHERE
                    `is_imported` = 0
               ";

        $statement = $this->db->query($sql);
        /** @var ResultSetInterface $resultSet */
        $resultSet = $statement->execute()->current();

        if ($resultSet && count($resultSet) > 0) {
            return $resultSet['count'];
        } else {
            return 0;
        }

    }

    /**
     * @return array
     */
    public function fetchOcsCategories()
    {
        return $this->queryOcsCategories();
    }

    /**
     * @return array
     */
    private function queryOcsCategories()
    {
        $sql = "SELECT `project_category_id` AS `id`, `title` AS `desc` FROM `project_category` WHERE `is_deleted` = 0 AND `is_active` = 1 ORDER BY `title`;";
        $statement = $this->db->query($sql);

        return $statement->execute();
    }

    /**
     * @return array
     */
    public function fetchHiveCategories()
    {
        return $this->queryCategories();
    }

    /**
     * @return array
     */
    private function queryCategories()
    {
        $sql = "SELECT `id`, `desc` FROM `hive_content_category` ORDER BY `desc`;";
        $statement = $this->db->query($sql);

        return $statement->execute();
    }

    /**
     * @param $cat_id
     *
     * @return array
     */
    public function fetchHiveCategory($cat_id)
    {
        return $this->queryCategory($cat_id);
    }

    /**
     * @param $id
     *
     * @return array
     */
    private function queryCategory($id)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql = "SELECT id, `desc` FROM hive_content_category WHERE id = " . $id . " ORDER BY `desc`;";
        $statement = $this->db->query($sql);

        return $statement->execute(['id' => $id])->current();
    }

    /**
     * @param $cat_id
     *
     * @return array
     */
    public function fetchOcsCategory($cat_id)
    {
        return $this->queryOcsCategory($cat_id);
    }

    /**
     * @param $id
     *
     * @return array
     */
    private function queryOcsCategory($id)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql = "SELECT project_category_id as id, `title` as `desc` FROM project_category WHERE is_deleted = 0 AND is_active = 1 AND project_category_id = " . $id . " ORDER BY `title`;";
        $statement = $this->db->query($sql);

        return $statement->execute(['id' => $id])->current();
    }

    /**
     * @param array $resultSetConfig
     *
     * @return array
     */
    private function createCategoriesArray($resultSetConfig)
    {
        $result = array();
        foreach ($resultSetConfig as $element) {
            $result[$element['id']] = $element['desc'];
        }

        return $result;
    }

}
