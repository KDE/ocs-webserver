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

use Application\Model\Entity\Section;
use Application\Model\Interfaces\SectionInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use RuntimeException;


class SectionRepository extends BaseRepository implements SectionInterface
{
    const CACHE_STORES_CATEGORIES = 'section_categories_list';
    const CACHE_STORES_CONFIGS = 'section_list';
    const CACHE_STORE_CONFIG = 'section';
    const CACHE_STORES_CONFIGS_BY_ID = 'section_id_list';
    private $cache;

    public function __construct(
        AdapterInterface $db,
        StorageInterface $storage
    ) {
        parent::__construct($db);
        $this->_name = "section";
        $this->_key = "section_id";
        $this->_prototype = Section::class;
        $this->cache = $storage;
    }

    // update or insert
    public function save(Section $obj)
    {
        $data = [
            'section_id'  => $obj->section_id,
            'name'        => $obj->name,
            'description' => $obj->description,
            'goal_amount' => $obj->goal_amount,
            'order'       => $obj->order,
            'hide'        => $obj->hide,
            'is_active'   => $obj->is_active,
            'created_at'  => $obj->created_at,
            'deleted_at'  => $obj->deleted_at,

        ];

        $id = (int)$obj->section_id;
        if ($id === 0) {
            //$this->db->insert($data);

            $sql = new Sql($this->db);
            $insert = $sql->insert($this->_name)->values($data);
            $statement = $sql->prepareStatementForSqlObject($insert);
            $insertResult = $statement->execute();
            $id = $insertResult->getGeneratedValue();

            return $this->findById($id);
        }

        try {
            $this->findById($id);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                sprintf(
                    'Cannot update ' . $this->_name . ' with identifier %d; does not exist', $id
                )
            );
        }
        //$this->db->update($data, [$this->_key => $id]);
        $sql = new Sql($this->db);
        $update = $sql->update($this->_name)->set($data);
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
        $result = $this->findById($obj->section_id);

        return $obj;
    }

    public function fetchAllSections()
    {
        $sql = "
            SELECT `section_id`,`name`,`description`
            FROM `section`
            WHERE `is_active` = 1
            AND `hide` = 0
            ORDER BY `section`.`order`
        ";
        $statement = $this->db->query($sql);

        return $statement->execute();
    }

    /**
     * @param null $id
     *
     * @return array
     */
    public function fetchNamesForJTable($id = null)
    {
        $sql = new Sql($this->db);
        $select = $sql->select($this->_name)->columns(array('name'))->group('name');
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultRows = $statement->execute();

        $resultForSelect = array();
        foreach ($resultRows as $row) {
            $resultForSelect[] = array('DisplayText' => $row['name'], 'Value' => $row['section_id']);
        }

        return $resultForSelect;
    }

    /**
     * @param bool $clearCache
     *
     * @return array
     */
    public function fetchAllSectionsAndCategories($clearCache = false)
    {
        $cache = $this->cache;
        $cacheName = self::CACHE_STORES_CATEGORIES;

        if ($clearCache) {
            $cache->setItem($cacheName, null);
        }

        if (false == ($configArray = $cache->getItem($cacheName))) {
            $resultSet = $this->queryAllSectionsAndCategories();
            $configArray = $this->createArrayAllSectionsAndCategories($resultSet);
            $cache->setItem($cacheName, $configArray);
        }

        return $configArray;
    }

    /**
     * @return array
     */
    private function queryAllSectionsAndCategories()
    {
        $sql = "
                SELECT
                    `section`.`name`,
                    `section_category`.`section_id`,
                    `section_category`.`project_category_id`
                FROM
                    `section`
                JOIN
                    `section_category` ON `section`.`section_id` = `section_category`.`section_id`
                JOIN
                    `project_category` ON `project_category`.`project_category_id` = `section_category`.`project_category_id`
                ORDER BY `section`.`name`,`project_category`.`title`;
        ";
        $statement = $this->db->query($sql);

        return $statement->execute();
    }

    /**
     * @param array $resultSetConfig
     *
     * @return array
     */
    private function createArrayAllSectionsAndCategories($resultSetConfig)
    {
        $result = array();
        foreach ($resultSetConfig as $element) {
            $result[$element['name']][] = $element['project_category_id'];
        }
        array_walk($result, create_function('&$v', '$v = (count($v) == 1)? array_pop($v): $v;'));

        return $result;
    }

    public function deleteId($dataId)
    {
        $sql = "DELETE FROM `section` WHERE {$this->_key} = ?";
        $statement = $this->db->query($sql);
        $statement->execute();
    }

    public function delete($where)
    {
        $this->update(array('is_active' => '0', 'deleted_at' => new Expression("NOW()")), $where);

        return true;
    }

    /**
     * @param bool $clearCache
     *
     * @return array
     */
    public function fetchAllSectionsArray($clearCache = false)
    {
        if ($this->cache) {
            $cache = $this->cache;
            $cacheName = self::CACHE_STORES_CONFIGS;

            if ($clearCache) {
                $cache->setItem($cacheName, null);
            }

            if (false == ($configArray = $cache->getItem($cacheName))) {
                $resultSet = $this->querySectionArray();
                $configArray = $this->createSectionArray($resultSet);
                $cache->setItem($cacheName, $configArray);
            }
        } else {
            $resultSet = $this->querySectionArray();
            $configArray = $this->createSectionArray($resultSet);
        }

        return $configArray;
    }

    /**
     * @return array
     */
    private function querySectionArray()
    {
        $sql = "SELECT * FROM `section` ORDER BY `name`;";
        $statement = $this->db->query($sql);

        return $statement->execute();
    }

    /**
     * @param array  $resultSetConfig
     * @param string $key
     *
     * @return array
     */
    private function createSectionArray($resultSetConfig, $key = 'name')
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
    public function fetchAllSectionByIdArray($clearCache = false)
    {
        if ($this->cache) {
            $cache = $this->cache;
            $cacheName = self::CACHE_STORES_CONFIGS_BY_ID;

            if ($clearCache) {
                $cache->setItem($cacheName, null);
            }

            if (false == ($configArray = $cache->getItem($cacheName))) {
                $resultSet = $this->querySectionArray();
                $configArray = $this->createSectionArray($resultSet, 'section_id');
                $cache->setItem($cacheName, $configArray);
            }
        } else {
            $resultSet = $this->querySectionArray();
            $configArray = $this->createSectionArray($resultSet, 'section_id');
        }

        return $configArray;
    }
}
