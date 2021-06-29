<?php /** @noinspection PhpUnused */

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

use Application\Model\Entity\ProjectCategory;
use Application\Model\Entity\ProjectCategoryData;
use Application\Model\Interfaces\ProjectCategoryInterface;
use ArrayObject;
use Exception;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Hydrator\Reflection as ReflectionHydrator;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class ProjectCategoryRepository extends BaseRepository implements ProjectCategoryInterface
{
    const CATEGORY_ACTIVE = 1;
    const CATEGORY_INACTIVE = 0;
    const CATEGORY_NOT_DELETED = 0;
    const CATEGORY_DELETED = 1;
    const ORDERED_TITLE = 'title';
    const ORDERED_ID = 'project_category_id';
    const ORDERED_HIERARCHIC = 'lft';
    const DEFAULT_STORE_ID = 22;
    private $cache;

    public function __construct(
        AdapterInterface $db,
        StorageInterface $storage
    ) {
        parent::__construct($db);
        $this->_name = "project_category";
        $this->_key = "project_category_id";
        $this->_prototype = ProjectCategory::class;
        $this->cache = $storage;
    }

    /**
     * @deprecated
     */
    public function getSelectList()
    {
        return [];
    }

    /**
     * @deprecated
     */
    public function getInternSelectList()
    {
        return [];
    }

    /**
     * @return array
     * @deprecated
     */
    public function fetchAllActive()
    {
        return [];
    }

    /**
     * @param $status
     * @param $id
     */
    public function setStatus($status, $id)
    {
        $updateValues = array(
            'is_active'  => $status,
            'changed_at' => new Expression('Now()'),
        );
        $this->update($updateValues, 'project_category_id=' . $id);
    }

    /**
     * @param $id
     *
     */
    public function setDelete($id)
    {
        $updateValues = array(
            'is_active'  => 0,
            'is_deleted' => 1,
            'deleted_at' => new Expression('Now()'),
        );
        $this->update($updateValues, 'project_category_id=' . $id);
    }

    /**
     * @param int|array $nodeId
     * @param bool      $clearCache
     *
     * @return array
     */
    public function fetchActive($nodeId, $clearCache = false)
    {
        $str = is_array($nodeId) ? implode(',', $nodeId) : $nodeId;
        /** @var AbstractAdapter $cache */
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5($str);

        if ($clearCache) {
            $cache->setItem($cacheName, false);
        }
        if (false == ($result = $cache->getItem($cacheName))) {
            $inQuery = $nodeId;
            if (is_array($nodeId)) {
                $inQuery = implode(',', $nodeId);
            }

            $sql = "SELECT *,
                  (SELECT
                      `project_category_id`
                       FROM
                         `project_category` AS `t2`
                       WHERE
                         `t2`.`lft`  < `node`.`lft` AND
                         `t2`.`rgt` > `node`.`rgt`
                         AND `t2`.`is_deleted` = 0
                       ORDER BY
                         `t2`.`rgt`-`node`.`rgt` ASC
                       LIMIT 1) AS `parent`
                FROM project_category as node
                WHERE project_category_id IN ($inQuery)
                AND is_active = 1
                ";

            $result = $this->fetchAll($sql);
            $cache->setItem($cacheName, $result);
        }

        return $result;
    }

    /**
     * @param int|array $nodeId
     *
     * @return array
     */
    public function fetchActiveOrder($nodeId)
    {
        $inQuery = '?';
        if (is_array($nodeId)) {
            $inQuery = implode(',', array_fill(0, count($nodeId), '?'));
        }

        $sql = "SELECT *,
                  (SELECT
                      `project_category_id`
                       FROM
                         `project_category` AS `t2`
                       WHERE
                         `t2`.`lft`  < `node`.`lft` AND
                         `t2`.`rgt` > `node`.`rgt`
                         AND `t2`.`is_deleted` = 0
                       ORDER BY
                         `t2`.`rgt`-`node`.`rgt`ASC
                       LIMIT
                         1) AS `parent`
                FROM project_category as node
                WHERE project_category_id IN ($inQuery)
                AND is_active = 1
                ";
        $active = $this->fetchAll($sql, $nodeId);
        if (count($active)) {
            return $active;
        } else {
            return array();
        }
    }

    public function setCategoryDeleted($id, $updateChildren = true)
    {
        $node = $this->findCategory($id);

        $this->db->getDriver()->getConnection()->beginTransaction();
        try {
            $data = [
                'is_active'           => 0,
                'is_deleted'          => 1,
                'deleted_at'          => new Expression('Now()'),
                'project_category_id' => $id,
            ];
            $this->update($data);
            if ($updateChildren) {
                $where = 'lft >' . $node->lft . ' and rgt < ' . $node->rgt;
                $this->update($data, $where);
            }
            $this->db->getDriver()->getConnection()->commit();
        } catch (Exception $e) {
            $this->db->getDriver()->getConnection()->rollback();
            error_log(__METHOD__ . ' - ERROR write activity log - ' . print_r($e, true));
        }

        return $node;
    }

    /* ------------------------ */
    /* New Nested Set Functions */
    /* ------------------------ */

    public function findCategory($nodeId)
    {
        return $this->fetchById($nodeId);
    }

    /**
     * @param $title
     *
     * @return array|ArrayObject|null
     */
    public function appendNewElement($title)
    {
        $root = $this->fetchRoot();
        $data['rgt'] = $root->rgt - 1;
        $data['title'] = $title;

        return $this->addNewElement($data);
    }

    /**
     */
    public function fetchRoot()
    {
        $resultset = $this->fetchAllRows(['lft' => 0]);

        return $resultset->current();
    }

    public function addNewElement($data)
    {
        $this->db->getDriver()->getConnection()->beginTransaction();
        try {

            $this->db->query(
                "UPDATE `project_category` SET `rgt` = `rgt` + 2 WHERE `rgt` > :param_right;", array('param_right' => $data['rgt'])
            );
            $this->db->query(
                "UPDATE `project_category` SET `lft` = `lft` + 2 WHERE `lft` > :param_right;", array('param_right' => $data['rgt'])
            );
            $this->db->query(
                "
                        INSERT INTO `project_category` (`lft`, `rgt`, `title`, `is_active`, `name_legacy`, `xdg_type`, `dl_pling_factor`, `show_description`, `source_required`) 
                        VALUES (:param_right + 1, :param_right + 2, :param_title, :param_status, :param_legacy, :param_xgd, :param_pling, :param_show_desc, :param_source);", array(
                                                                                                                                                                                                'param_right'     => $data['rgt'],
                                                                                                                                                                                                'param_title'     => $data['title'],
                                                                                                                                                                                                'param_status'    => array_key_exists('is_active', $data) ? $data['is_active'] : 1,
                                                                                                                                                                                                'param_legacy'    => array_key_exists('name_legacy', $data) ? $data['name_legacy'] : null,
                                                                                                                                                                                                'param_xgd'       => array_key_exists('xdg_type', $data) ? $data['xdg_type'] : null,
                                                                                                                                                                                                'param_show_desc' => array_key_exists('show_description', $data) ? $data['show_description'] : 0,
                                                                                                                                                                                                'param_source'    => array_key_exists('source_required', $data) ? $data['source_required'] : 0,
                                                                                                                                                                                                'param_pling'     => array_key_exists('dl_pling_factor', $data) ? $data['dl_pling_factor'] : null,
                                                                                                                                                                                            )
            );


            $this->db->getDriver()->getConnection()->commit();
        } catch (Exception $e) {
            $this->db->getDriver()->getConnection()->rollBack();
            error_log(__METHOD__ . ' - ERROR write activity log - ' . print_r($e, true));
        }

        return $this->fetchAllRows(['lft' => $data['rgt'] + 1])->current();

    }

    /**
     * @param $cat_id
     *
     * @return array
     */
    public function fetchTreeForJTable($cat_id)
    {
        $resultRows = $this->fetchTree(false, true, 5);

        $resultForSelect = array();
        foreach ($resultRows as $row) {
            if (($row['project_category_id'] == $cat_id) or ($row['parent'] == $cat_id)) {
                continue;
            }
            $resultForSelect[] = array('DisplayText' => $row['title_show'], 'Value' => $row['project_category_id']);
        }

        return $resultForSelect;
    }

    /**
     * @param bool $isActive
     * @param bool $withRoot
     * @param int  $depth
     *
     * @return array
     * @internal param int $pageSize
     * @internal param int $startIndex
     * @internal param bool $clearCache
     */
    public function fetchTree(
        $isActive = false,
        $withRoot = true,
        $depth = null
    ) {
        $sqlActive = $isActive == true ? " parent_active = 1 AND pc.is_active = 1" : 'pc.is_active = 0';
        $sqlRoot = $withRoot == true ? "(pc.lft BETWEEN pc2.lft AND pc2.rgt)" : "(pc.lft BETWEEN pc2.lft AND pc2.rgt) AND pc2.lft > 0";
        $sqlDepth = is_null($depth) == true ? '' : " AND depth <= " . (int)$depth;
        $sqlHaving = $sqlActive || $sqlDepth ? "HAVING {$sqlActive} {$sqlDepth}" : '';

        $sql = "
        	  SELECT
                `pc`.`project_category_id`,
                `pc`.`lft`,
                `pc`.`rgt`,
                `pc`.`title`,
                `pc`.`name_legacy`,
                `pc`.`is_active`,
                `pc`.`orderPos`,
                `pc`.`xdg_type`,
                `pc`.`dl_pling_factor`,
                `pc`.`show_description`,
                `pc`.`source_required`,
                MIN(`pc2`.`is_active`)                                       AS `parent_active`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), `pc`.`title`) AS `title_show`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), IF(LENGTH(TRIM(`pc`.`name_legacy`))>0,`pc`.`name_legacy`,`pc`.`title`)) AS `title_legacy`,
                count(`pc`.`lft`) - 1                                        AS `depth`,
                GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`)   AS `ancestor_id_path`,
                GROUP_CONCAT(`pc2`.`title` ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path`,
                GROUP_CONCAT(IF(LENGTH(TRIM(`pc2`.`name_legacy`))>0,`pc2`.`name_legacy`,`pc2`.`title`) ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path_legacy`
              FROM
                  `project_category` AS `pc`
              JOIN
                    `project_category` AS `pc2` ON {$sqlRoot}
              GROUP BY pc.lft
              {$sqlHaving}
              ORDER BY pc.lft

        ";

        return $this->fetchAll($sql);
    }

    /**
     * @param bool $isActive
     * @param int  $depth
     *
     * @return array
     * @internal param int $pageSize
     * @internal param int $startIndex
     * @internal param bool $clearCache
     */
    public function fetchTreeWithParentId(
        $isActive = true,
        $depth = null
    ) {
        $sqlActive = $isActive == true ? " parent_active = 1 AND is_active = 1" : 'is_active = 0';
        $sqlDepth = is_null($depth) == true ? '' : " AND depth <= " . (int)$depth;
        $sqlHaving = $sqlActive || $sqlDepth ? "HAVING {$sqlActive} {$sqlDepth}" : '';
        $sql = "
        	SELECT
                MAX(`pc`.`project_category_id`) AS project_category_id,
                `pc`.`lft`,
                MAX(`pc`.`rgt`) AS rgt,
                MAX(`pc`.`title`) AS title,
                MAX(`pc`.`name_legacy`) AS name_legacy,
                MAX(`pc`.`is_active`) AS is_active,
                MAX(`pc`.`orderPos`) AS orderPos,
                MAX(`pc`.`xdg_type`) AS xdg_type,
                MAX(`pc`.`dl_pling_factor`) AS dl_pling_factor,
                MAX(`pc`.`mv_pling_factor`) AS mv_pling_factor,
                MAX(`pc`.`show_description`) AS show_description,
                MAX(`pc`.`source_required`) AS source_required,
                MAX(`blt`.`name`) as `browse_list_type_name`,
                MAX(`pc`.`browse_list_type`) AS browse_list_type,
                MAX(`pc`.`tag_rating`) AS tag_rating,
                MAX(`tg`.`group_name`) as `tag_rating_name`,
                MIN(`pc2`.`is_active`)                                       AS `parent_active`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1),  MAX(`pc`.`title`)) AS `title_show`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), IF(LENGTH(TRIM( MAX(`pc`.`name_legacy`)))>0, MAX(`pc`.`name_legacy`), MAX(`pc`.`title`))) AS `title_legacy`,
                count(`pc`.`lft`) - 1                                        AS `depth`,
                GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`)   AS `ancestor_id_path`,
                GROUP_CONCAT(`pc2`.`title` ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path`,
                GROUP_CONCAT(IF(LENGTH(TRIM(`pc2`.`name_legacy`))>0,`pc2`.`name_legacy`,`pc2`.`title`) ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path_legacy`,
                SUBSTRING_INDEX( GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`), ',', -1) AS `parent`
              FROM
                  `project_category` AS `pc`
              JOIN
                    `project_category` AS `pc2` ON (`pc`.`lft` BETWEEN `pc2`.`lft` AND `pc2`.`rgt`) AND `pc2`.`project_category_id` <> `pc`.`project_category_id`
              LEFT JOIN 
                    `browse_list_types` AS `blt`  ON `pc`.`browse_list_type` = `blt`.`browse_list_type_id`
            LEFT JOIN 
            `tag_group` AS `tg`  ON `pc`.`tag_rating` = `tg`.`group_id`
              GROUP BY `pc`.`lft`
              {$sqlHaving}
              ORDER BY pc.lft

        ";

        return $this->fetchAll($sql);
    }

    /**
     * @param bool $isActive
     * @param int  $depth
     *
     * @return array
     * @internal param int $pageSize
     * @internal param int $startIndex
     * @internal param bool $clearCache
     */
    public function fetchTreeWithParentIdAndTags(
        $isActive = true,
        $depth = null
    ) {
        $sqlActive = $isActive == true ? " parent_active = 1 AND is_active = 1" : 'is_active = 0';
        $sqlDepth = is_null($depth) == true ? '' : " AND depth <= " . (int)$depth;
        $sqlHaving = $sqlActive || $sqlDepth ? "HAVING {$sqlActive} {$sqlDepth}" : '';
        $sql = "
              SELECT
                MAX(`pc`.`project_category_id`) AS project_category_id,
                `pc`.`lft`,
                MAX(`pc`.`rgt`) AS rgt,
                MAX(`pc`.`title`) AS title,
                MAX(`pc`.`name_legacy`) AS name_legacy,
                MAX(`pc`.`is_active`) AS is_active,
                MAX(`pc`.`orderPos`) AS orderPos,
                MAX(`pc`.`xdg_type`) AS xdg_type,
                MAX(`pc`.`dl_pling_factor`) AS dl_pling_factor,
                MAX(`pc`.`mv_pling_factor`) AS mv_pling_factor,
                MAX(`pc`.`show_description`) AS show_description,
                MAX(`pc`.`source_required`) AS source_required,
                MIN(`pc2`.`is_active`)                                       AS `parent_active`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), MAX(`pc`.`title`)) AS `title_show`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), IF(LENGTH(TRIM(MAX(`pc`.`name_legacy`)))>0,MAX(`pc`.`name_legacy`),MAX(`pc`.`title`))) AS `title_legacy`,
                count(`pc`.`lft`) - 1                                        AS `depth`,
                GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`)   AS `ancestor_id_path`,
                GROUP_CONCAT(`pc2`.`title` ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path`,
                GROUP_CONCAT(IF(LENGTH(TRIM(`pc2`.`name_legacy`))>0,`pc2`.`name_legacy`,`pc2`.`title`) ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path_legacy`,
                SUBSTRING_INDEX( GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`), ',', -1) AS `parent`,
                (SELECT GROUP_CONCAT(`tag`.`tag_name`)
                FROM `category_tag`,`tag`            
                WHERE `tag`.`tag_id` = `category_tag`.`tag_id` AND `category_tag`.`category_id` = MAX(`pc`.`project_category_id`)        
                GROUP BY `category_tag`.`category_id`) AS `tags_name`,
                (SELECT GROUP_CONCAT(`tag`.`tag_id`)
                FROM `category_tag`,`tag`            
                WHERE `tag`.`tag_id` = `category_tag`.`tag_id` AND `category_tag`.`category_id` = MAX(`pc`.`project_category_id`)        
                GROUP BY `category_tag`.`category_id`) AS `tags_id`
              FROM
                  `project_category` AS `pc`
              JOIN
                    `project_category` AS `pc2` ON (`pc`.`lft` BETWEEN `pc2`.`lft` AND `pc2`.`rgt`) AND `pc2`.`project_category_id` <> `pc`.`project_category_id`
              GROUP BY `pc`.`lft`
              {$sqlHaving}
              ORDER BY pc.lft

        ";

        return $this->fetchAll($sql);
    }

    /**
     * @param bool $isActive
     * @param int  $depth
     *
     * @return array
     * @internal param int $pageSize
     * @internal param int $startIndex
     * @internal param bool $clearCache
     */
    public function fetchTreeWithParentIdAndTagGroups(
        $isActive = true,
        $depth = null
    ) {
        $sqlActive = $isActive == true ? " parent_active = 1 AND is_active = 1" : 'is_active = 0';
        $sqlDepth = is_null($depth) == true ? '' : " AND depth <= " . (int)$depth;
        $sqlHaving = $sqlActive || $sqlDepth ? "HAVING {$sqlActive} {$sqlDepth}" : '';
        $sql = "
              SELECT
                MAX(`pc`.`project_category_id`) AS project_category_id,
                MAX(`pc`.`lft`) AS lft,
                MAX(`pc`.`rgt`) AS rgt,
                MAX(`pc`.`title`) AS title,
                MAX(`pc`.`name_legacy`) AS name_legacy,
                MAX(`pc`.`is_active`) AS is_active,
                MAX(`pc`.`orderPos`) AS orderPos,
                MAX(`pc`.`xdg_type`) AS xdg_type,
                MAX(`pc`.`dl_pling_factor`) AS dl_pling_factor,
                MAX(`pc`.`show_description`) AS show_description,
                MAX(`pc`.`source_required`) AS source_required,
                MIN(`pc2`.`is_active`) AS `parent_active`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), MAX(`pc`.`title`)) AS `title_show`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), IF(LENGTH(TRIM(MAX(`pc`.`name_legacy`)))>0,MAX(`pc`.`name_legacy`),MAX(`pc`.`title`))) AS `title_legacy`,
                count(`pc`.`lft`) - 1                                        AS `depth`,
                GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`)   AS `ancestor_id_path`,
                GROUP_CONCAT(`pc2`.`title` ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path`,
                GROUP_CONCAT(IF(LENGTH(TRIM(`pc2`.`name_legacy`))>0,`pc2`.`name_legacy`,`pc2`.`title`) ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path_legacy`,
                SUBSTRING_INDEX( GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`), ',', -1) AS `parent`,
                (SELECT GROUP_CONCAT(`tag_group`.`group_name`)
                FROM `category_tag_group`,`tag_group`            
                WHERE `tag_group`.`group_id` = `category_tag_group`.`tag_group_id` AND `category_tag_group`.`category_id` = MAX(`pc`.`project_category_id`)        
                GROUP BY `category_tag_group`.`category_id`) AS `tag_group_name`,
                (SELECT GROUP_CONCAT(`tag_group`.`group_id`)
                FROM `category_tag_group`,`tag_group`            
                WHERE `tag_group`.`group_id` = `category_tag_group`.`tag_group_id` AND `category_tag_group`.`category_id` = MAX(`pc`.`project_category_id`)        
                GROUP BY `category_tag_group`.`category_id`) AS `tag_group_id`
              FROM
                  `project_category` AS `pc`
              JOIN
                    `project_category` AS `pc2` ON (`pc`.`lft` BETWEEN `pc2`.`lft` AND `pc2`.`rgt`) AND `pc2`.`project_category_id` <> `pc`.`project_category_id`
              GROUP BY `pc`.`lft`
              {$sqlHaving}
              ORDER BY pc.lft

        ";

        return $this->fetchAll($sql);
    }

    /**
     * @param bool $isActive
     * @param int  $depth
     *
     * @return array
     * @internal param int $pageSize
     * @internal param int $startIndex
     * @internal param bool $clearCache
     */
    public function fetchTreeWithParentIdAndSections(
        $isActive = true,
        $depth = null
    ) {

        $sqlActive = $isActive == true ? " parent_active = 1 AND is_active = 1" : 'is_active = 0';
        $sqlDepth = is_null($depth) == true ? '' : " AND depth <= " . (int)$depth;
        $sqlHaving = $sqlActive || $sqlDepth ? "HAVING {$sqlActive} {$sqlDepth}" : '';
        $sql = "
              SELECT
                MAX(`pc`.`project_category_id`) as project_category_id,
                `pc`.`lft`,
                MAX(`pc`.`rgt`) as rgt,
                MAX(`pc`.`title`) as title,
                MAX(`pc`.`name_legacy`) as name_legacy,
                MAX(`pc`.`is_active`) as is_active,
                MAX(`pc`.`orderPos`) as orderPos,
                MAX(`pc`.`xdg_type`) as xdg_type,
                MAX(`pc`.`dl_pling_factor`) as dl_pling_factor,
                MAX(`pc`.`show_description`) as show_description,
                MAX(`pc`.`source_required`) as source_required,
                MIN(`pc2`.`is_active`)                                       AS `parent_active`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), MAX(`pc`.`title`)) AS `title_show`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), IF(LENGTH(TRIM(MAX(`pc`.`name_legacy`)))>0,MAX(`pc`.`name_legacy`),MAX(`pc`.`title`))) AS `title_legacy`,
                count(`pc`.`lft`) - 1                                        AS `depth`,
                GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`)   AS `ancestor_id_path`,
                GROUP_CONCAT(`pc2`.`title` ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path`,
                GROUP_CONCAT(IF(LENGTH(TRIM(`pc2`.`name_legacy`))>0,`pc2`.`name_legacy`,`pc2`.`title`) ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path_legacy`,
                SUBSTRING_INDEX( GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`), ',', -1) AS `parent`,
                (SELECT `section`.name
                FROM `section_category`, `section`
                WHERE `section`.section_id = `section_category`.section_id and `section_category`.`project_category_id` = MAX(`pc`.`project_category_id`)) AS `section_name`,
                (SELECT `section`.section_id
                FROM `section_category`, `section`
					 WHERE `section`.section_id = `section_category`.section_id and `section_category`.`project_category_id` = MAX(`pc`.`project_category_id`)) AS `section_id`
              FROM
                  `project_category` AS `pc`
              JOIN
                    `project_category` AS `pc2` ON (`pc`.`lft` BETWEEN `pc2`.`lft` AND `pc2`.`rgt`) AND `pc2`.`project_category_id` <> `pc`.`project_category_id`
              GROUP BY `pc`.`lft`
              {$sqlHaving}
              ORDER BY pc.lft

        ";

        return $this->fetchAll($sql);
    }

    /**
     * @param integer $cat_id
     *
     * @return array
     */
    public function fetchTreeForJTableStores($cat_id)
    {
        $sql = "
                SELECT
                max(`pc`.`project_category_id`) AS `project_category_id`,
                `pc`.`lft`,
                max(`pc`.`rgt`) AS `rgt`,
                max(`pc`.`title`) AS `title`,
                max(`pc`.`name_legacy`) AS `name_legacy`,
                max(`pc`.`is_active`) AS `is_active`,
                max(`pc`.`orderPos`) AS `orderPos`,
                max(`pc`.`xdg_type`) AS `xdg_type`,
                max(`pc`.`dl_pling_factor`) AS `dl_pling_factor`,
                max(`pc`.`show_description`) AS `show_description`,
                max(`pc`.`source_required`) AS `source_required`,
                MIN(`pc2`.`is_active`)                                       AS `parent_active`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), max(`pc`.`title`)) AS `title_show`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), IF(LENGTH(TRIM(max(`pc`.`name_legacy`)))>0,max(`pc`.`name_legacy`),max(`pc`.`title`))) AS `title_legacy`,
                count(`pc`.`lft`) - 1                                        AS `depth`,
                GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`)   AS `ancestor_id_path`,
                GROUP_CONCAT(`pc2`.`title` ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path`,
                GROUP_CONCAT(IF(LENGTH(TRIM(`pc2`.`name_legacy`))>0,`pc2`.`name_legacy`,`pc2`.`title`) ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path_legacy`,
                SUBSTRING_INDEX( GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`), ',', -1) AS `parent`
              FROM
                  `project_category` AS `pc`
              JOIN
                    `project_category` AS `pc2` ON (`pc`.`lft` BETWEEN `pc2`.`lft` AND `pc2`.`rgt`) AND (IF(`pc`.`project_category_id` <> 34,`pc2`.`project_category_id` <> `pc`.`project_category_id`,TRUE))
              GROUP BY `pc`.`lft`
              HAVING `parent_active` = 1 AND `is_active` = 1
              ORDER BY `pc`.`lft`
        ";
        $resultRows = $this->fetchAll($sql);

        $resultForSelect = array();
        foreach ($resultRows as $row) {
            if (($row['project_category_id'] == $cat_id) or ($row['parent'] == $cat_id)) {
                continue;
            }
            $resultForSelect[] = array('DisplayText' => $row['title_show'], 'Value' => $row['project_category_id']);
        }

        return $resultForSelect;
    }

    /**
     * @param $cat_id
     *
     * @return array
     */
    public function fetchTreeForJTableSection($cat_id)
    {
        $sql = "
                SELECT
                max(`pc`.`project_category_id`) AS `project_category_id`,
                `pc`.`lft`,
                max(`pc`.`rgt`) AS `rgt`,
                max(`pc`.`title`) AS `title`,
                max(`pc`.`name_legacy`) AS `name_legacy`,
                max(`pc`.`is_active`) AS `is_active`,
                max(`pc`.`orderPos`) AS `orderPos`,
                max(`pc`.`xdg_type`) AS `xdg_type`,
                max(`pc`.`dl_pling_factor`) AS `dl_pling_factor`,
                max(`pc`.`show_description`) AS `show_description`,
                max(`pc`.`source_required`) AS `source_required`,
                MIN(`pc2`.`is_active`)                                       AS `parent_active`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), max(`pc`.`title`)) AS `title_show`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), IF(LENGTH(TRIM(max(`pc`.`name_legacy`)))>0,max(`pc`.`name_legacy`),max(`pc`.`title`))) AS `title_legacy`,
                count(`pc`.`lft`) - 1                                        AS `depth`,
                GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`)   AS `ancestor_id_path`,
                GROUP_CONCAT(`pc2`.`title` ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path`,
                GROUP_CONCAT(IF(LENGTH(TRIM(`pc2`.`name_legacy`))>0,`pc2`.`name_legacy`,`pc2`.`title`) ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path_legacy`,
                SUBSTRING_INDEX( GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`), ',', -1) AS `parent`
              FROM
                  `project_category` AS `pc`
              JOIN
                    `project_category` AS `pc2` ON (`pc`.`lft` BETWEEN `pc2`.`lft` AND `pc2`.`rgt`) AND (IF(`pc`.`project_category_id` <> 34,`pc2`.`project_category_id` <> `pc`.`project_category_id`,TRUE))
              GROUP BY `pc`.`lft`
              HAVING `parent_active` = 1 AND `is_active` = 1
              ORDER BY `pc`.`lft`
        ";
        $resultRows = $this->fetchAll($sql);

        $resultForSelect = array();
        foreach ($resultRows as $row) {
            if (($row['project_category_id'] == $cat_id) or ($row['parent'] == $cat_id)) {
                continue;
            }
            $resultForSelect[] = array('DisplayText' => $row['title_show'], 'Value' => $row['project_category_id']);
        }

        return $resultForSelect;
    }

    /**
     * @param $cat_id
     *
     * @return array
     */
    public function fetchTreeForCategoryStores($cat_id)
    {
        $sql = "
                SELECT
                max(`pc`.`project_category_id`) AS `project_category_id`,
                `pc`.`lft`,
                max(`pc`.`rgt`) AS `rgt`,
                max(`pc`.`title`) AS `title`,
                max(`pc`.`is_active`) AS `is_active`,
                MIN(`pc2`.`is_active`)                                       AS `parent_active`,
                count(`pc`.`lft`) - 1                                        AS `depth`,
                SUBSTRING_INDEX( GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`), ',', -1) AS `parent`
              FROM
                  `project_category` AS `pc`
              JOIN
                  `project_category` AS `pc2` ON (`pc`.`lft` BETWEEN `pc2`.`lft` AND `pc2`.`rgt`) AND (IF(`pc`.`project_category_id` <> 34,`pc2`.`project_category_id` <> `pc`.`project_category_id`,TRUE))
              GROUP BY `pc`.`lft`
              HAVING `parent_active` = 1 AND `is_active` = 1
              ORDER BY `pc`.`lft`
        ";
        $resultRows = $this->fetchAll($sql);

        $resultForSelect = array();
        foreach ($resultRows as $row) {
            if (($row['project_category_id'] == $cat_id) or ($row['parent'] == $cat_id)) {
                continue;
            }
            $resultForSelect[] = array('DisplayText' => $row['title'], 'Value' => $row['project_category_id']);
        }

        return $resultForSelect;
    }

    /**
     * @param array $node
     * @param int   $newLeftPosition
     *
     * @return bool
     * @deprecated use moveTo instead
     */
    public function moveElement($node, $newLeftPosition)
    {
        return false;
    }

    /**
     * @param $data
     *
     * @return null
     * @deprecated  wrong with the select
     */
    public function findAncestor($data)
    {
        /*      
        $resultRow = $this->fetchAllRows(['rgt' =>$data['lft']-1])->current();
        if (($resultRow->rgt - $resultRow->lft) > 1) {            
            $resultRow = $this->fetchAllRows(['lft' =>$resultRow->lft-2])->current();
        }
        return $resultRow;
        */
        return null;
    }

    /**
     * @param $data
     *
     * @return array|null
     */
    public function findPreviousSibling($data)
    {
        $parent = $this->fetchParentForId($data);
        $parent_category_id = $parent->project_category_id;

        $sql = "SELECT `node`.`project_category_id`, `node`.`lft`, `node`.`rgt`, `node`.`title`, (SELECT
                       `project_category_id`
                        FROM
                          `project_category` AS `t2`
                        WHERE
                          `t2`.`lft`  < `node`.`lft` AND
                          `t2`.`rgt` > `node`.`rgt`
                        ORDER BY
                          `t2`.`rgt`-`node`.`rgt`ASC
                        LIMIT
                          1) AS `parent_category_id`
                FROM `project_category` AS `node`,
                     `project_category` AS `parent`
                WHERE `node`.`lft` BETWEEN `parent`.`lft` AND `parent`.`rgt`
                GROUP BY `node`.`project_category_id`
                HAVING `parent_category_id` = :parent_category_id
                ORDER BY `node`.`lft`";

        $siblings = $this->fetchAll($sql, array('parent_category_id' => $parent_category_id));

        $resultRow = null;
        $bufferRow = null;

        foreach ($siblings as $row) {
            if ($row['project_category_id'] != $data['project_category_id']) {
                $bufferRow = $row;
                continue;
            }
            $resultRow = $bufferRow;
        }

        return $resultRow;
    }

    /**
     * @param $data
     *
     * @return array|ArrayObject|null
     */
    public function fetchParentForId($data)
    {
        $sql = "
        SELECT `title`, (SELECT
              `project_category_id`
               FROM
                 `project_category` AS `t2`
               WHERE
                 `t2`.`lft`  < `node`.`lft` AND
                 `t2`.`rgt` > `node`.`rgt`
               ORDER BY
                 `t2`.`rgt`-`node`.`rgt`ASC
               LIMIT
                 1) AS `parent`
        FROM `project_category` AS `node`
        WHERE `project_category_id` = :category_id
        ORDER BY (`rgt`-`lft`) DESC
        ";
        $resultRow = $this->fetchRow($sql, array('category_id' => $data['project_category_id']));

        return $this->fetchAllRows(['project_category_id' => $resultRow['parent']])->current();
    }

    /**
     * @param $data
     *
     * @return array|null
     */
    public function findNextSibling($data)
    {
        $parent = $this->fetchParentForId($data);
        $parent_category_id = $parent->project_category_id;

        $sql = "SELECT `node`.`project_category_id`, `node`.`lft`, `node`.`rgt`, `node`.`title`, (SELECT
                       `project_category_id`
                        FROM
                          `project_category` AS `t2`
                        WHERE
                          `t2`.`lft`  < `node`.`lft` AND
                          `t2`.`rgt` > `node`.`rgt`
                        ORDER BY
                          `t2`.`rgt`-`node`.`rgt`ASC
                        LIMIT
                          1) AS `parent_category_id`
                FROM `project_category` AS `node`,
                     `project_category` AS `parent`
                WHERE `node`.`lft` BETWEEN `parent`.`lft` AND `parent`.`rgt`
                GROUP BY `node`.`project_category_id`
                HAVING `parent_category_id` = :parent_category_id
                ORDER BY `node`.`lft`";

        $siblings = $this->fetchAll($sql, array('parent_category_id' => $parent_category_id));

        $resultRow = null;
        $found = false;

        foreach ($siblings as $row) {
            if ($found == true) {
                $resultRow = $row;
                break;
            }
            if ($row['project_category_id'] == $data['project_category_id']) {
                $found = true;
                continue;
            }
        }

        return $resultRow;
    }

    /**
     * @param $data
     *
     * @return array|ArrayObject|null
     */
    public function findPreviousElement($data)
    {
        $resultRow = $this->fetchAllRows(['rgt' => $data['lft'] - 1])->current();
        if (($resultRow->rgt - $resultRow->lft) > 1) {
            $resultRow = $this->fetchAllRows(['lft' => $resultRow->rgt - 2])->current();
        }

        return $resultRow;
    }

    /**
     * @param $data
     *
     * @return array|ArrayObject|null
     */
    public function findNextElement($data)
    {
        $resultRow = $this->fetchAllRows(['lft' => $data['rgt'] + 1])->current();
        if (($resultRow->rgt - $resultRow->lft) > 1) {
            $resultRow = $this->fetchAllRows(['lft' => $resultRow->lft + 2])->current();
        }

        return $resultRow;
    }

    /**
     * @param string|array $nodeId
     * @param array        $options
     *
     * @return array
     */
    public function fetchChildTree($nodeId, $options = array())
    {
        $clearCache = false;
        if (isset($options['clearCache'])) {
            $clearCache = $options['clearCache'];
            unset($options['clearCache']);
        }

        /** @var AbstractAdapter $cache */
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5(serialize($nodeId) . serialize($options));

        if ($clearCache) {
            $cache->setItem($cacheName, null);
        }

        if (false == ($tree = $cache->getItem($cacheName))) {

            $extSqlWhereActive = " AND o.is_active = 1";
            if (isset($options['isActive']) and $options['isActive'] == false) {
                $extSqlWhereActive = '';
            }

            $extSqlHavingDepth = '';
            if (isset($options['depth'])) {
                $extSqlHavingDepth = " HAVING depth <= " . (int)$options['depth'];
            }

            $inQuery = '?';
            if (is_array($nodeId)) {
                $inQuery = implode(',', array_fill(0, count($nodeId), '?'));
            }

            $sql = "SELECT `o`.*,
                      COUNT(`p`.`project_category_id`)-1 AS `depth`,
                      CONCAT( REPEAT( '&nbsp;&nbsp;', (COUNT(`p`.`title`) - 1) ), `o`.`title`) AS `title_show`,
                      `pc`.`product_counter`
                    FROM `project_category` AS `n`
                    INNER JOIN `project_category` AS `p`
                    INNER JOIN `project_category` AS `o`
                    LEFT JOIN (SELECT
                                 `project`.`project_category_id`,
                                 count(`project`.`project_category_id`) AS `product_counter`
                               FROM
                                 `project`
                               WHERE `project`.`status` = 100 AND `project`.`type_id` = 1
                               GROUP BY `project`.`project_category_id`) AS `pc` ON `pc`.`project_category_id` = `o`.`project_category_id`
                    WHERE `o`.`lft` BETWEEN `p`.`lft` AND `p`.`rgt`
                          AND `o`.`lft` BETWEEN `n`.`lft` AND `n`.`rgt`
                          AND `n`.`project_category_id` IN ({$inQuery})
                          AND `o`.`lft` > `p`.`lft` AND `o`.`lft` > `n`.`lft`
                          {$extSqlWhereActive}
                    GROUP BY o.lft
                    {$extSqlHavingDepth}
                    ORDER BY o.lft;
                    ;
                    ";
            $tree = $this->fetchAll($sql, $nodeId);
            $cache->setItem($cacheName, $tree);
        }

        return $tree;
    }

    /**
     * @param int|array $nodeId
     * @param bool      $isActive
     *
     * @return array Set of subnodes
     */
    public function fetchChildElements($nodeId, $isActive = true)
    {
        if (is_null($nodeId) or $nodeId == '') {
            return array();
        }

        /** @var AbstractAdapter $cache */
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5(serialize($nodeId) . (int)$isActive);

        if (($children = $cache->getItem($cacheName))) {
            return $children;
        }

        $inQuery = '?';
        if (is_array($nodeId)) {
            $inQuery = implode(',', array_fill(0, count($nodeId), '?'));
        }
        $whereActive = $isActive == true ? ' AND o.is_active = 1' : '';
        $sql = "
            SELECT o.project_category_id,
                   COUNT(p.project_category_id)-2 AS depth
                FROM project_category AS n,
                     project_category AS p,
                     project_category AS o
               WHERE o.lft BETWEEN p.lft AND p.rgt
                 AND o.lft BETWEEN n.lft AND n.rgt
                 AND n.project_category_id IN ({$inQuery})
                 {$whereActive}
            GROUP BY o.lft,o.project_category_id
            HAVING depth > 0
            ORDER BY o.lft;
        ";
        $children = $this->fetchAll($sql, $nodeId);
        $cache->setItem($cacheName, $children);
        if (count($children)) {
            return $children;
        } else {
            return array();
        }
    }

    /**
     * @param int|array $nodeId
     * @param bool      $isActive
     *
     * @return array Set of subnodes
     */
    public function fetchChildIds($nodeId, $isActive = true)
    {
        if (empty($nodeId) or $nodeId == '') {
            return array();
        }

        /** @var AbstractAdapter $cache */
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5(serialize($nodeId) . (int)$isActive);
        $cache->setItem($cacheName, null);
        if (($children = $cache->getItem($cacheName))) {
            return $children;
        }


        $inQuery = '?';
        if (is_array($nodeId)) {
            $inQuery = implode(',', array_fill(0, count($nodeId), '?'));
        }
        $whereActive = $isActive == true ? ' AND o.is_active = 1' : '';
        $sql = "
            SELECT MAX(o.project_category_id) AS project_category_id
                FROM project_category AS n,
                     project_category AS p,
                     project_category AS o
               WHERE o.lft BETWEEN p.lft AND p.rgt
                 AND o.lft BETWEEN n.lft AND n.rgt
                 AND n.project_category_id IN ({$inQuery})
                 {$whereActive}
            GROUP BY o.lft
            HAVING COUNT(p.project_category_id)-2 > 0
            ORDER BY o.lft;
        ";

        $children = $this->fetchAll($sql, $nodeId);

        if (count($children)) {
            $result = $this->flattenArray($children);
            $result = $this->removeUnnecessaryValues($nodeId, $result);
            $cache->setItem($cacheName, $result);

            return $result;
        } else {
            return array();
        }
    }

    /**
     *
     * @flatten multi-dimensional array
     *
     * @param array $array
     *
     * @return array
     *
     */
    private function flattenArray(array $array)
    {
        $ret_array = array();
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value) {
            $ret_array[] = $value;
        }

        return $ret_array;
    }

    /**
     * @param array $nodeId
     * @param array $children
     *
     * @return array
     */
    private function removeUnnecessaryValues($nodeId, $children)
    {
        $nodeId = is_array($nodeId) ? $nodeId : array($nodeId);

        return array_diff($children, $nodeId);
    }

    /**
     * @param        $nodeId
     * @param string $orderBy
     *
     * @return array
     */
    public function fetchImmediateChildrenIds($nodeId, $orderBy = self::ORDERED_HIERARCHIC)
    {
        $sql = "
                SELECT `node`.`project_category_id`
                FROM `project_category` AS `node`
                WHERE `node`.`is_active` = 1
                HAVING (SELECT `parent`.`project_category_id` FROM `project_category` AS `parent` WHERE `parent`.`lft` < `node`.`lft` AND `parent`.`rgt` > `node`.`rgt` ORDER BY `parent`.`rgt`-`node`.`rgt` LIMIT 1) = ?
                ORDER BY `node`.`{$orderBy}`;
            ";
        $children = $this->fetchAll($sql, $nodeId);
        if (count($children)) {
            return $this->flattenArray($children);
        } else {
            return array();
        }
    }

    /**
     * @param $first
     * @param $second
     *
     * @return null
     * @deprecated
     */
    public function switchElements($first, $second)
    {
        return null;
    }

    /**
     * @param int $returnAmount
     * @param int $fetchLimit
     *
     * @return array|false|mixed
     */
    public function fetchMainCategories($returnAmount = 25, $fetchLimit = 25)
    {
        $categories = $this->fetchTree(true, false, 1);

        return array_slice($categories, 0, $returnAmount);
    }

    /**
     * @return array
     */
    public function fetchMainCatIdsOrdered()
    {
        /** @var AbstractAdapter $cache */
        $cache = $this->cache;
        $cacheName = __FUNCTION__;

        if (($returnValue = $cache->getItem($cacheName))) {
            return $returnValue;
        }

        $sql = "
                SELECT
                    `node`.`project_category_id`
                FROM
                    `project_category` AS `node`
                INNER JOIN
                    `project_category` AS `parent`
                WHERE
                    `node`.`lft` BETWEEN `parent`.`lft` AND `parent`.`rgt`
                        AND `node`.`is_active` = 1
                        AND `node`.`is_deleted` = 0
                        AND `node`.`lft` > 0
                GROUP BY `node`.`project_category_id`
                HAVING (COUNT(`parent`.`title`) - 1) = 1
                ORDER BY `node`.`orderPos`, `node`.`lft`;
        ";

        $result = $this->fetchAll($sql);
        if (count($result) > 0) {
            $returnValue = $this->flattenArray($result);
            $cache->setItem($cacheName, $returnValue);

            return $returnValue;
        } else {
            return array();
        }
    }

    /**
     * @return array
     */
    public function fetchMainCatsOrdered()
    {
        $sql = "
                SELECT
                    `node`.`project_category_id`, `node`.`title`, `node`.`lft`, `node`.`rgt`
                FROM
                    `project_category` AS `node`
                INNER JOIN
                    `project_category` AS `parent`
                WHERE
                    `node`.`lft` BETWEEN `parent`.`lft` AND `parent`.`rgt`
                        AND `node`.`is_active` = 1
                        AND `node`.`is_deleted` = 0
                        AND `node`.`lft` > 0
                GROUP BY `node`.`project_category_id`
                HAVING (COUNT(`parent`.`title`) - 1) = 1
                ORDER BY `node`.`orderPos`, `node`.`lft`;
        ";
        $result = $this->fetchAll($sql);
        if (count($result) > 0) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * @param int    $cat_id
     * @param string $orderBy
     *
     * @return array
     */
    public function fetchSubCatIds($cat_id, $orderBy = self::ORDERED_HIERARCHIC)
    {
        $sql = "
                SELECT
                    node.project_category_id
                FROM
                    project_category AS node
                INNER JOIN
                    project_category AS parent
                WHERE
                    parent.project_category_id IN (:cat_id)
                        --  AND node.lft BETWEEN parent.lft AND parent.rgt
                        AND node.lft > parent.lft AND node.rgt < parent.rgt
                        AND node.is_active = 1
                        AND node.is_deleted = 0
                        AND node.lft > 0
                GROUP BY node.project_category_id
                ORDER BY node.`{$orderBy}`
                ;
        ";
        $result = $this->fetchAll($sql, array('cat_id' => $cat_id));
        if (count($result) > 0) {
            //            array_shift($result);
            return $this->flattenArray($result);
        } else {
            return array();
        }
    }

    /**
     * @param int $returnAmount
     * @param int $fetchLimit
     *
     * @return array
     */
    public function fetchRandomCategories($returnAmount = 5, $fetchLimit = 25)
    {
        $categories = $this->fetchTree(true, false, 1);

        return $this->_array_random($categories, $returnAmount);
    }

    /**
     * @param array $categories
     * @param int   $count
     *
     * @return array
     */
    protected function _array_random($categories, $count = 1)
    {
        shuffle($categories);

        return array_slice($categories, 0, $count);
    }

    /**
     * @param int    $currentNodeId
     * @param int    $newParentNodeId
     * @param string $position
     *
     * @return bool
     */

    public function moveToParent($currentNodeId, $newParentNodeId, $position = 'top')
    {
        if ($currentNodeId <= 0) {
            return false;
        }
        $currentNode = $this->fetchElement($currentNodeId);
        $currentParentNode = $this->fetchParentForId($currentNode);

        if ($newParentNodeId == $currentParentNode->project_category_id) {
            return false;
        }

        $newParentNode = $this->fetchElement($newParentNodeId);

        if ($position == 'top') {
            return $this->moveTo($currentNode, $newParentNode['lft'] + 1);
        } else {
            return $this->moveTo($currentNode, $newParentNode['rgt']); // move to bottom otherwise
        }
    }

    /**
     * @param int $nodeId
     *
     * @return ArrayObject|null Returns Element as array or (if empty) an array with empty values
     */
    public function fetchElement($nodeId)
    {
        return $this->fetchById($nodeId);
    }

    /**
     * @param array $node            complete node data
     * @param int   $newLeftPosition new left position for the node
     *
     * @return bool
     */
    public function moveTo($node, $newLeftPosition)
    {
        $space = $node['rgt'] - $node['lft'] + 1;
        $distance = $newLeftPosition - $node['lft'];
        $srcPosition = $node['lft'];

        //for backwards movement, we have to fix some values
        if ($distance < 0) {
            $distance -= $space;
            $srcPosition += $space;
        }

        $this->db->getDriver()->getConnection()->beginTransaction();

        try {

            // create space for subtree
            $this->db->query(
                "UPDATE {$this->_name} SET lft = lft + :space WHERE lft >= :newLeftPosition;", array(
                                                                                                 'space'           => $space,
                                                                                                 'newLeftPosition' => $newLeftPosition,
                                                                                             )
            );
            $this->db->query(
                "UPDATE {$this->_name} SET rgt = rgt + :space WHERE rgt >= :newLeftPosition;", array(
                                                                                                 'space'           => $space,
                                                                                                 'newLeftPosition' => $newLeftPosition,
                                                                                             )
            );

            // move tree
            $this->db->query(
                "UPDATE {$this->_name} SET lft = lft + :distance, rgt = rgt + :distance WHERE lft >= :srcPosition AND rgt < :srcPosition + :space;", array(
                                                                                                                                                       'distance'    => $distance,
                                                                                                                                                       'srcPosition' => $srcPosition,
                                                                                                                                                       'space'       => $space,
                                                                                                                                                   )
            );

            // remove old space
            $this->db->query(
                "UPDATE {$this->_name} SET rgt = rgt - :space WHERE rgt > :srcPosition;", array(
                                                                                            'space'       => $space,
                                                                                            'srcPosition' => $srcPosition,
                                                                                        )
            );
            $this->db->query(
                "UPDATE {$this->_name} SET lft = lft - :space WHERE lft >= :srcPosition;", array(
                                                                                             'space'       => $space,
                                                                                             'srcPosition' => $srcPosition,
                                                                                         )
            );

            // move it
            $this->db->getDriver()->getConnection()->commit();
        } catch (Exception $e) {
            $this->db->getDriver()->getConnection()->rollBack();
            error_log(__METHOD__ . ' - ' . print_r($e, true));

            return false;
        }

        return true;
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function fetchMainCategoryForProduct($productId)
    {
        $sql = "SELECT `pc`.`project_category_id`, `pc`.`title`
                FROM `project_category` AS `pc`
                JOIN `project` AS `p` ON `p`.`project_category_id` = `pc`.`project_category_id`
                WHERE `p`.`project_id` = :projectId
                ;";

        return $this->fetchAll($sql, array('projectId' => $productId));
    }

    /**
     * @param $productId
     *
     * @return array
     * @deprecated
     */
    public function fetchAllCategoriesForProduct($productId)
    {
        return [];
    }

    /**
     * @param int $cat_id
     *
     * @return int|string
     */
    public function countSubCategories($cat_id)
    {
        $cat = $this->findCategory($cat_id);

        $countSubCat = (int)$cat->rgt - (int)$cat->lft - 1;

        if ($countSubCat < 0) {
            return 0;
        } else {
            return $countSubCat;
        }
    }

    /**
     * @param $valueCatId
     *
     * @return array
     */
    public function fetchCategoriesForForm($valueCatId)
    {
        $level = 0;
        $mainCatArray = $this->fetchMainCatForSelect(self::ORDERED_TITLE);
        $ancestors = array("catLevel-{$level}" => $mainCatArray);

        $level++;

        if (false == empty($valueCatId)) {

            foreach (array_keys($mainCatArray) as $element) {
                if ($element == $valueCatId) {
                    return $ancestors;
                }
            }

            $categoryAncestors = $this->fetchAncestorsAsId($valueCatId);
            if ($categoryAncestors) {
                $categoryPath = explode(',', $categoryAncestors['ancestors']);
                foreach ($categoryPath as $element) {

                    $catResult = $this->fetchImmediateChildren($element, self::ORDERED_TITLE);
                    $ancestors["catLevel-{$level}"] = $this->prepareDataForFormSelect($catResult);

                    $level++;
                }
            }
        }

        return $ancestors;
    }

    /**
     * @param string $orderBy
     *
     * @return array
     */
    public function fetchMainCatForSelect($orderBy = self::ORDERED_HIERARCHIC)
    {

        $root = $this->fetchRoot();
        $resultRows = $this->fetchImmediateChildren($root['project_category_id'], $orderBy);

        return $this->prepareDataForFormSelect($resultRows);
    }

    /**
     * @param int|array $nodeId
     * @param string    $orderBy
     *
     * @return array
     */
    public function fetchImmediateChildren($nodeId, $orderBy = 'lft')
    {
        $str = is_array($nodeId) ? implode(',', $nodeId) : $nodeId;
        /** @var AbstractAdapter $cache */
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5($str . $orderBy);
        $children = $cache->getItem($cacheName);

        if (!$children || null == $children) {
            $inQuery = '?';
            if (is_array($nodeId)) {
                $inQuery = implode(',', array_fill(0, count($nodeId), '?'));
            }
            $sql = '
            SELECT node.*, (SELECT parent.project_category_id FROM project_category AS parent WHERE parent.lft < node.lft AND parent.rgt > node.rgt ORDER BY parent.rgt-node.rgt LIMIT 1) AS parent
            FROM project_category AS node
            WHERE node.is_active = 1
            HAVING parent IN (' . $inQuery . ')
            ORDER BY node.' . $orderBy . '
            ';
            $children = $this->fetchAll($sql, $nodeId);
            if (count($children) == 0) {
                $children = array();
            }
            $cache->setItem($cacheName, $children);

        }

        return $children;
    }

    /**
     * @param $resultRows
     *
     * @return array
     */
    protected function prepareDataForFormSelect($resultRows)
    {
        $resultForSelect = array();
        if ($resultRows && count($resultRows) > 0) {
            foreach ($resultRows as $row) {
                $resultForSelect[$row['project_category_id']] = $row['title'];
            }
        }

        return $resultForSelect;
    }

    /**
     * @param $catId
     *
     * @return array|mixed
     */
    public function fetchAncestorsAsId($catId)
    {
        $sql = '
        SELECT `node`.`title`, GROUP_CONCAT(`parent`.`project_category_id` ORDER BY `parent`.`lft`) AS `ancestors` 
        FROM `project_category` AS `node`
        LEFT JOIN `project_category` AS `parent` ON `parent`.`lft` < `node`.`lft` AND `parent`.`rgt` > `node`.`rgt` AND `parent`.`lft` > 0
        WHERE `node`.`project_category_id` = :categoryId
        GROUP BY `node`.`project_category_id`
        HAVING `ancestors` IS NOT NULL
        ';

        $result = $this->fetchRow($sql, array('categoryId' => $catId));

        if ($result and count($result) > 0) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * @param integer $cat_id
     *
     * @return ProjectCategoryData
     */
    public function readCategoryData($cat_id)
    {
        $sql = "
            SELECT
                MAX(`pc`.`project_category_id`) AS `project_category_id`,
                MAX(`pc`.`lft`) AS `lft`,
                MAX(`pc`.`rgt`) AS `rgt`,
                MAX(`pc`.`title`) AS `title`,
                MAX(`pc`.`name_legacy`) AS `name_legacy`,
                MAX(`pc`.`is_active`) AS `is_active`,
                MAX(`pc`.`orderPos`) AS `orderPos`,
                MAX(`pc`.`xdg_type`) AS `xdg_type`,
                MAX(`pc`.`dl_pling_factor`) AS `dl_pling_factor`,
                MAX(`pc`.`show_description`) AS `show_description`,
                MAX(`pc`.`source_required`) AS `source_required`,
                MIN(`pc2`.`is_active`)                                         AS `parent_active`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), max(`pc`.`title`)) AS `title_show`,
                concat(repeat('&nbsp;&nbsp;',count(`pc`.`lft`) - 1), IF(LENGTH(TRIM(max(`pc`.`name_legacy`)))>0,max(`pc`.`name_legacy`),max(`pc`.`title`))) AS `title_legacy`,
                count(`pc`.`lft`) - 1                                          AS `depth`,
                GROUP_CONCAT(if(`pc2`.`project_category_id` <> 34,`pc2`.`project_category_id`,NULL) ORDER BY `pc2`.`lft`)   AS `ancestor_id_path`,
                GROUP_CONCAT(if(`pc2`.`project_category_id` <> 34,`pc2`.`title`,NULL) ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path`,
                GROUP_CONCAT(if(`pc2`.`project_category_id` <> 34,IF(LENGTH(TRIM(`pc2`.`name_legacy`))>0,`pc2`.`name_legacy`,`pc2`.`title`),NULL) ORDER BY `pc2`.`lft` SEPARATOR ' | ') AS `ancestor_path_legacy`,
                SUBSTRING_INDEX( GROUP_CONCAT(`pc2`.`project_category_id` ORDER BY `pc2`.`lft`), ',', -1)               AS `parent`
              FROM
                  `project_category` AS `pc`
              JOIN
                  `project_category` AS `pc2` ON (`pc`.`lft` BETWEEN `pc2`.`lft` AND `pc2`.`rgt`) AND (IF(`pc`.`project_category_id` <> 34,`pc2`.`project_category_id` <> `pc`.`project_category_id`,TRUE))
              WHERE `pc`.`project_category_id` <> 34 # remove root from result set
              GROUP BY `pc`.`lft`
              HAVING `parent_active` = 1 AND `is_active` = 1 AND `project_category_id` = :cat_id
              ORDER BY `pc`.`lft`
        ";

        //$resultSet = $this->fetchAll($sql, array('cat_id' => $cat_id), false);
      
        // $object = new ProjectCategoryData();

        // if ($resultSet instanceof ResultInterface && $resultSet->isQueryResult()) {
        //     $resultSet = new HydratingResultSet(new ReflectionHydrator(), new ProjectCategoryData());
        //     $object = $resultSet->initialize($resultSet);
        // }      
        // return $object;

        $object = $this->fetchRow($sql, array('cat_id' => $cat_id), false);        
        return $object;
    }

    /**
     * @param $valueCatId
     *
     * @return array
     */
    public function fetchCategoriesForFormNew($valueCatId)
    {
        $level = 0;
        $mainCatArray = $this->fetchMainCatForSelectNew(self::ORDERED_TITLE);
        $ancestors = array("catLevel-{$level}" => $mainCatArray);

        $level++;

        if (false == empty($valueCatId)) {

            foreach (array_keys($mainCatArray) as $element) {
                if ($element == $valueCatId) {
                    return $ancestors;
                }
            }

            $categoryAncestors = $this->fetchAncestorsAsId($valueCatId);
            if ($categoryAncestors) {
                $categoryPath = explode(',', $categoryAncestors['ancestors']);
                foreach ($categoryPath as $element) {

                    $catResult = $this->fetchImmediateChildren($element, self::ORDERED_TITLE);
                    $ancestors["catLevel-{$level}"] = $this->prepareDataForFormSelect($catResult);

                    $level++;
                }
            }
        }

        return $ancestors;
    }

    /**
     * @param string $orderBy
     *
     * @return array
     */
    public function fetchMainCatForSelectNew($orderBy = self::ORDERED_HIERARCHIC)
    {

        $storeCatIds = $GLOBALS['ocs_store_category_list'];
        if (null == $storeCatIds) {
            $root = $this->fetchRoot();
            $resultRows = $this->fetchImmediateChildrenNew($root->project_category_id, $orderBy);
        } else {
            $resultRows = $this->fetchImmediateChildrenNew($storeCatIds, $orderBy);
        }

        return $this->prepareDataForFormSelectNew($resultRows);
    }

    /**
     * @param int|array $nodeId
     * @param string    $orderBy
     *
     * @return array
     */

    public function fetchImmediateChildrenNew($nodeId, $orderBy = 'lft')
    {
        $str = is_array($nodeId) ? implode(',', $nodeId) : $nodeId;
        $cache = $this->cache;
        $cacheName = $this->getCacheName(__FUNCTION__, $str . $orderBy);

        $children = $cache->getItem($cacheName);
        if (!$children) {
            // $proCatModel = new Default_Model_ProjectCategory();
            // $store_config = Zend_Registry::get('store_config');
            // $store_id = $store_config->store_id;
            // $rows = $proCatModel->fetchTreeForView($store_id);
            $store = $this->fetchDefaultStoreId();
            $store_id = $store['store_id'];
            $rows = $this->fetchTreeForView($store_id);

            $children = array();

            if (is_array($nodeId)) {
                $inQuery = implode(',', array_fill(0, count($nodeId), '?'));
                foreach ($rows as $row) {
                    foreach ($nodeId as $node) {
                        if ($row['id'] == $node) {
                            $children[] = $row;
                        }
                    }
                }
            } else {
                echo 'hier';
                foreach ($rows as $row) {
                    if ($row['parent_id'] == $nodeId) {
                        $children[] = $row;
                    }
                }
            }


            if (count($children) == 0) {
                $children = array();
            }
            $cache->setItem($cacheName, $children);
        }

        return $children;
    }

    private function getCacheName($functionname, $key)
    {
        $cachename = __CLASS__ . '_' . $functionname . md5($key);

        return str_replace('\\', '_', $cachename);
    }

    private function fetchDefaultStoreId()
    {
        $sql = "SELECT `store_id`, `package_type` FROM `config_store` WHERE `default` = 1";

        return $this->fetchRow($sql);
    }

    /**
     * @param null $store_id
     *
     * @return array
     */
    public function fetchTreeForView($store_id = null)
    {
        $tags = null;
        if (empty($store_id)) {
            $store_config = $GLOBALS['ocs_store']->config;
            $store_id = $store_config->store_id;
        }
        $tags = $GLOBALS['ocs_config_store_tags'] ? $GLOBALS['ocs_config_store_tags'] : array();

        /** @var AbstractAdapter $cache */
        $cache = $this->cache;
        $cache_id = $this->getCacheName(__FUNCTION__, $store_id);

        $tree = $cache->getItem($cache_id);
        $rows = array();
        if (false === $tree or empty($tree)) {
            try {
                $rows = $this->fetchCategoryTreeWithTags($store_id, $tags);
            } catch (Exception $e) {
                //Zend_Registry::get('logger')->err(__METHOD__ . ' - can not fetch categories : ' . $e->getMessage());
                error_log(__METHOD__ . ' - can not fetch categories : ' . $e->getMessage());
            }
            list($leftover_rows, $tree) = $this->buildTreeForView($rows);
            $cache->setItem($cache_id, $tree);
        }

        return $tree;
    }

    /**
     * @param int|null   $store_id
     * @param array|null $tags
     *
     * @return array
     */
    public function fetchCategoryTreeWithTags($store_id = null, $tags = null)
    {
        if (empty($store_id)) {
            return array();
        }
        if (is_array($tags)) {
            $tags = implode(',', $tags);
        }
        $GLOBALS['ocs_log']->debug(__METHOD__ . ' - ' . $store_id . ' - ' . json_encode($tags));
        if (empty($tags)) {
            $result = $this->fetchAll("CALL fetchCatTreeForStore(:store_id)", array("store_id" => $store_id));
        } else {
            $result = $this->fetchAll(
                "CALL fetchCatTreeWithTagsForStore(:store_id,:tagids)", array(
                                                                          "store_id" => $store_id,
                                                                          "tagids"   => $tags,
                                                                      )
            );
        }

        return $result;
    }

    /**
     * @param array    $rows
     * @param int|null $current_parent_id
     * @param int      $depth
     *
     * @return array
     */
    private function buildTreeForView($rows, $current_parent_id = null, $depth = 0)
    {
        $result = array();
        $rememberParent = null;
        $depth += 1;

        while (false === empty($rows)) {
            $row = array_shift($rows);

            $result_element = array(
                'id'            => $row['id'],
                'title'         => $row['title'],
                'product_count' => $row['product_count'],
                'xdg_type'      => $row['xdg_type'],
                'name_legacy'   => $row['name_legacy'],
                'has_children'  => $row['has_children'],
                'parent_id'     => $row['parent_id'],
            );

            //has children?
            if ($row['has_children'] == 1) {
                $result_element['has_children'] = true;
                $rememberParent = $row['id'];
                list($rows, $children) = $this->buildTreeForView($rows, $rememberParent, $depth);
                uasort(
                    $children, function ($a, $b) {
                    return strcasecmp($a['title'], $b['title']);
                }
                );
                $result_element['children'] = $children;
                $rememberParent = null;
            }

            $result[] = $result_element;

            if (empty($rows)) {
                break;
            }

            if ($depth > 1 and ($current_parent_id != $rows[0]['parent_id'])) {
                break;
            }
        }

        return array($rows, $result);
    }

    /**
     * @param $resultRows
     *
     * @return array
     */
    protected function prepareDataForFormSelectNew($resultRows)
    {
        $resultForSelect = array();
        //$resultForSelect[''] = '';
        foreach ($resultRows as $row) {
            $resultForSelect[$row['id']] = $row['title'];
        }

        return $resultForSelect;
    }

    /**
     * @return array
     */
    public function fetchCatNames()
    {
        $sql = "SELECT project_category_id, title FROM project_category";

        return $this->fetchPairs($sql);
    }

    /**
     * @param $resultRows
     *
     * @return array
     */
    protected function prepareDataForFormSelectWithTitleKey($resultRows)
    {
        $resultForSelect = array();
        //$resultForSelect[''] = '';
        foreach ($resultRows as $row) {
            $resultForSelect[$row['title']] = $row['project_category_id'];
        }

        return $resultForSelect;
    }

    /**
     * @deprecated
     */
    protected function initLocalCache()
    {
    }
}