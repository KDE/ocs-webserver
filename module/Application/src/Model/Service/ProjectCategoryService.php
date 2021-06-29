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

namespace Application\Model\Service;

use Application\Model\Repository\ConfigStoreCategoryRepository;
use Application\Model\Repository\ConfigStoreRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Repository\SectionCategoryRepository;
use Application\Model\Service\Interfaces\ProjectCategoryServiceInterface;
use Exception;
use Laminas\Db\Adapter\AdapterInterface;

class ProjectCategoryService extends BaseService implements ProjectCategoryServiceInterface
{
    const CACHE_TREE_STORE = 'store_cat_tree';
    const CACHE_TREE_SECTION = 'section_cat_tree';

    protected $db;
    protected $cache;
    protected $config;
    protected $store;
    private $projectCategoryRepository;

    public function __construct(
        AdapterInterface $db,
        array $config
    ) {
        $this->db = $db;
        $this->config = $config;
        $this->cache = $GLOBALS['ocs_cache'];
        $this->store = $GLOBALS['ocs_store'];
        $this->projectCategoryRepository = new ProjectCategoryRepository($this->db, $this->cache);
    }

    /**
     * @param null $store_id
     *
     * @return array
     * @throws Exception
     */
    public function fetchTreeForView($store_id = null)
    {
        $tags = null;

        if (empty($store_id)) {
            $store_id = $this->store->config->store_id;
            $tags = $GLOBALS['ocs_config_store_tags'];
        }

        $cache = $this->cache;
        $cache_id = __FUNCTION__ . "_{$store_id}";

        $tree = $cache->getItem($cache_id);

        if (false === $tree or empty($tree)) {
            try {
                $rows = $this->fetchCategoryTreeWithTags($store_id, $tags);
            } catch (Exception $e) {
                $GLOBALS['ocs_log']->err(__METHOD__ . ' - can not fetch categories : ' . $e->getMessage());
                $modelCategories = new ConfigStoreRepository($this->db, $this->cache);
                $defaultStore = $modelCategories->fetchDefaultStoreId();
                $rows = $this->fetchCategoryTreeWithTags($defaultStore['store_id'], $tags);
            }

            list($rows, $tree) = $this->buildTreeForView($rows);
            $cache->setItem($cache_id, $tree);
        }

        return $tree;
    }

    /**
     * @param int|null    $store_id
     * @param string|null $tags
     *
     * @return array
     * @throws Exception
     */
    protected function fetchCategoryTreeWithTags($store_id = null, $tags = null)
    {
        if (empty($store_id)) {
            return array();
        }
        if (is_array($tags)) {
            $tags = implode(',', $tags);
        }

        $GLOBALS['ocs_log']->debug(__METHOD__ . ' - ' . $store_id . ' - ' . json_encode($tags));

        if (empty($tags)) {
            $result = $this->projectCategoryRepository->fetchAll("CALL fetchCatTreeForStore(:store_id)", array("store_id" => $store_id));
        } else {
            $result = $this->projectCategoryRepository->fetchAll(
                "CALL fetchCatTreeWithTagsForStore(:store_id,:tagids)", array(
                                                                          "store_id" => $store_id,
                                                                          "tagids"   => $tags,
                                                                      )
            );
        }

//        if (count($result) == 0) {
//            throw new Zend_Exception('no categories could be found for store id: ' . $store_id);
//        }

        return $result;
    }

    /**
     * @param array    $rows
     * @param int|null $parent_id
     *
     * @return array
     */
    protected function buildTreeForView($rows, $parent_id = null)
    {
        $result = array();
        $rememberParent = null;

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
                list($rows, $children) = $this->buildTreeForView($rows, $rememberParent);
                uasort(
                    $children, function ($a, $b) {
                    return strcasecmp($a['title'], $b['title']);
                }
                );
                $result_element['children'] = $children;
                $rememberParent = null;
            }

            $result[] = $result_element;

            if (isset($parent_id) and isset($rows[0]['parent_id']) and $parent_id != $rows[0]['parent_id']) {
                break;
            }
        }

        return array($rows, $result);
    }

    /**
     * @param null $store_id
     * @param null $member_id
     *
     * @return array
     */
    public function fetchTreeForViewForProjectFavourites($store_id = null, $member_id = null)
    {
        if (empty($store_id)) {
            $store_id = $this->store->config->store_id;
        }
        if ($member_id == null) {
            if (isset($GLOBALS['ocs_user'])) {
                $member_id = $GLOBALS['ocs_user']->member_id;
            } else {
                return null;
            }
        }
        $sql = "
                SELECT `sct`.`lft`, `sct`.`rgt`, `sct`.`project_category_id` AS `id`, `sct`.`title`, `scpc`.`count_product` AS `product_count`, `sct`.`xdg_type`, `sct`.`name_legacy`, if(`sct`.`rgt`-`sct`.`lft` = 1, 0, 1) AS `has_children`, (SELECT `project_category_id` FROM `stat_cat_tree` AS `sct2` WHERE `sct2`.`lft` < `sct`.`lft` AND `sct2`.`rgt` > `sct`.`rgt` ORDER BY `sct2`.`rgt` - `sct`.`rgt` LIMIT 1) AS `parent_id`
                FROM (
                        SELECT `csc`.`store_id`, `csc`.`project_category_id`, `csc`.`order`, `pc`.`title`, `pc`.`lft`, `pc`.`rgt`
                        FROM `config_store_category` AS `csc`
                        JOIN `project_category` AS `pc` ON `pc`.`project_category_id` = `csc`.`project_category_id`
                        WHERE `csc`.`store_id` = :store_id
                        GROUP BY `csc`.`store_category_id`
                        ORDER BY `csc`.`order`, `pc`.`title`
                
                ) AS `cfc`
                JOIN `stat_cat_tree` AS `sct` ON find_in_set(`cfc`.`project_category_id`, `sct`.`ancestor_id_path`)   
                JOIN (
                
                    SELECT
                    `sct2`.`project_category_id`,          
                    count(DISTINCT `p`.`project_id`) AS `count_product`
                    FROM `stat_cat_tree` AS `sct1`
                    JOIN `stat_cat_tree` AS `sct2` ON `sct1`.`lft` BETWEEN `sct2`.`lft` AND `sct2`.`rgt`
                    LEFT JOIN (
                        SELECT `project_category_id`, `f`.`project_id`
                        FROM `project_follower` `f`, `stat_projects` `p`
                        WHERE `f`.`project_id` = `p`.`project_id`
                        AND `f`.`member_id` = :member_id			
                    ) AS `p` ON `p`.`project_category_id` = `sct1`.`project_category_id`                           
                    GROUP BY `sct2`.`project_category_id`
                
                ) AS `scpc` ON  `sct`.`project_category_id` = `scpc`.`project_category_id`  
                WHERE `cfc`.`store_id` = :store_id
                ORDER BY `cfc`.`order`, `sct`.`lft`        
        ";

        $rows = $this->projectCategoryRepository->fetchAll(
            $sql, array(
                    'store_id'  => $store_id,
                    'member_id' => $member_id,
                )
        );
        list($rows, $tree) = $this->buildTreeForView($rows);

        return $tree;
    }

    /**
     * @param null $store_id
     *
     * @param null $storeTagFilter
     * @param null $tagFilter
     *
     * @return array
     */
    public function fetchTreeForViewForProjectTagGroupTags($store_id = null, $storeTagFilter = null, $tagFilter = null)
    {
        if (empty($store_id)) {
            $store_id = $this->store->config->store_id;
        }

        $cacheName = __FUNCTION__ . '_' . md5(serialize($storeTagFilter) . '_' . serialize($tagFilter) . '_' . serialize($store_id));
        $cache = $this->cache;
        $tree = $cache->getItem($cacheName);

        if (false === $tree or empty($tree)) {
            $filterString = "";
            //Store Tag Filter
            if (null != $storeTagFilter) {
                if (is_array($storeTagFilter)) {
                    foreach ($storeTagFilter as $value) {
                        $filterString .= " AND FIND_IN_SET('" . $value . "',p.tag_ids)";
                    }
                } else {
                    $filterString .= " AND FIND_IN_SET('" . $storeTagFilter . "',p.tag_ids)";
                }
            }
            //Store-Tag-Group-Filter
            if (is_array($tagFilter)) {
                $tagList = $tagFilter;
                foreach ($tagList as $key => $value) {
                    if ($value != null && $value != "0") {
                        $filterString .= " AND FIND_IN_SET('" . $value . "',p.tag_ids)";
                    }
                }
            } else {
                $filterString .= " AND FIND_IN_SET('" . $tagFilter . "',p.tag_ids)";
            }

            $sql = "
                    SELECT `sct`.`lft`, `sct`.`rgt`, `sct`.`project_category_id` AS `id`, `sct`.`title`, `scpc`.`count_product` AS `product_count`, `sct`.`xdg_type`, `sct`.`name_legacy`, if(`sct`.`rgt`-`sct`.`lft` = 1, 0, 1) AS `has_children`, (SELECT `project_category_id` FROM `stat_cat_tree` AS `sct2` WHERE `sct2`.`lft` < `sct`.`lft` AND `sct2`.`rgt` > `sct`.`rgt` ORDER BY `sct2`.`rgt` - `sct`.`rgt` LIMIT 1) AS `parent_id`
                    FROM (
                            SELECT `csc`.`store_id`, `csc`.`project_category_id`, `csc`.`order`, `pc`.`title`, `pc`.`lft`, `pc`.`rgt`
                            FROM `config_store_category` AS `csc`
                            JOIN `project_category` AS `pc` ON `pc`.`project_category_id` = `csc`.`project_category_id`
                            WHERE `csc`.`store_id` = :store_id
                            GROUP BY `csc`.`store_category_id`
                            ORDER BY `csc`.`order`, `pc`.`title`

                    ) AS `cfc`
                    JOIN `stat_cat_tree` AS `sct` ON find_in_set(`cfc`.`project_category_id`, `sct`.`ancestor_id_path`)   
                    join (

                        SELECT
                        sct2.project_category_id,          
                        count(distinct p.project_id) as count_product
                        FROM stat_cat_tree as sct1
                        JOIN stat_cat_tree as sct2 ON sct1.lft between sct2.lft AND sct2.rgt
                        left join (
                            SELECT project_category_id, project_id
                            from stat_projects p
                            WHERE 1=1
                            " . $filterString . "
                        ) as p on p.project_category_id = sct1.project_category_id                           
                        GROUP BY sct2.project_category_id

                    ) AS `scpc` on  `sct`.`project_category_id` = `scpc`.`project_category_id`  
                    WHERE cfc.store_id = :store_id
                    ORDER BY cfc.`order`, sct.lft        
            ";

            $GLOBALS['ocs_log']->debug(__METHOD__ . ' - SQL: ' . $sql);

            $rows = $this->projectCategoryRepository->fetchAll($sql, array('store_id' => $store_id));
            list($rows, $tree) = $this->buildTreeForView($rows);

            $cache->setItem($cacheName, $tree);
        }

        return $tree;
    }

    /**
     * @param int|null $store_id If not set, the tree for the current store will be returned
     * @param bool     $clearCache
     *
     * @return array
     * @throws Exception
     * @deprecated use fetchTreeForView
     */
    public function fetchCategoryTreeForStore($store_id = null, $clearCache = false)
    {
        if (empty($store_id)) {
            $store_id = $this->store->config->store_id;
        }

        $cache = $this->cache;
        $cache_id = self::CACHE_TREE_STORE . "_{$store_id}";

        if ($clearCache) {
            $cache->setItem($cache_id, false);
        }

        $tree = $cache->getItem($cache_id);

        if (false === $tree or empty($tree)) {
            $modelCategoryStore = new ConfigStoreCategoryRepository($this->db);
            $rows = $modelCategoryStore->fetchCatIdsForStore((int)$store_id);

            if (count($rows) < 1) {
                $root = $this->projectCategoryRepository->fetchRoot();
                $rows = $this->projectCategoryRepository->fetchImmediateChildrenIds($root['project_category_id'], ProjectCategoryRepository::ORDERED_TITLE);
                $tree = $this->buildTree($rows, null, null);
            } else {
                $tree = $this->buildTree($rows, null, (int)$store_id);
            }

            $cache->setItem($cache_id, $tree);
        }

        return $tree;
    }

    private function buildTree($list, $parent_id = null, $store_id = null)
    {
        if (false === is_array($list)) {
            $list = array($list);
        }
        $modelCategories = $this->projectCategoryRepository;
        $result = array();
        foreach ($list as $cat_id) {
            $currentCategory = $modelCategories->fetchElement($cat_id);
            $countProduct = $this->fetchProductCount($cat_id, $store_id);

            $result_element = array(
                'id'            => $cat_id,
                'title'         => $currentCategory['title'],
                'product_count' => $countProduct,
                'xdg_type'      => $currentCategory['xdg_type'],
                'name_legacy'   => $currentCategory['name_legacy'],
                'has_children'  => false,
            );

            if (isset($parent_id)) {
                $result_element['parent_id'] = $parent_id;
            }

            //has children?
            if (($currentCategory['rgt'] - $currentCategory['lft']) > 1) {
                $result_element['has_children'] = true;
                $ids = $modelCategories->fetchImmediateChildrenIds(
                    $currentCategory['project_category_id'], $modelCategories::ORDERED_TITLE
                );
                $result_element['children'] = $this->buildTree($ids, $currentCategory['project_category_id'], $store_id);
            }
            $result[] = $result_element;
        }

        return $result;
    }

    private function fetchProductCount($cat_id, $store_id = null)
    {

        $tagFilter = null;
        if (isset($GLOBALS['ocs_config_store_tags'])) {
            $tagFilter = $GLOBALS['ocs_config_store_tags'];
        } else {
            $tagFilter = null;
        }


        if ($tagFilter) {
            $sql = "SELECT `count_product` FROM `stat_cat_prod_count` WHERE `project_category_id` = :cat_id AND `tag_id` = :tags";
            $bind = array('cat_id' => $cat_id, 'tags' => $tagFilter);
        } else {
            $sql = "SELECT `count_product` FROM `stat_cat_prod_count` WHERE `project_category_id` = :cat_id AND `tag_id` IS NULL";
            $bind = array('cat_id' => $cat_id);
        }

        $result = $this->projectCategoryRepository->fetchRow($sql, $bind);

        return (int)$result['count_product'];
    }

    /**
     * @param null $section_id
     * @param bool $clearCache
     *
     * @return array
     * @throws Exception
     * @deprecated use fetchTreeForView
     */
    public function fetchCategoryTreeForSection($section_id = null, $clearCache = false)
    {
        throw new Exception('Deprecated. Use fetchTreeForView');

        $cache = $this->cache;
        $cache_id = self::CACHE_TREE_SECTION . "_{$section_id}";

        if ($clearCache) {
            $cache->setItem($cache_id, false);
        }

        $tree = $cache->getItem($cache_id);
        if (false === $tree or empty($tree)) {
            $modelCategoryStore = new SectionCategoryRepository($this->db);
            $rows = $modelCategoryStore->fetchCatIdsForSection((int)$section_id);

            if (count($rows) < 1) {
                $modelCategories = $this->projectCategoryRepository;
                $root = $modelCategories->fetchRoot();
                $rows = $modelCategories->fetchImmediateChildrenIds($root['project_category_id'], ProjectCategoryRepository::ORDERED_TITLE);
                $tree = $this->buildTree($rows, null, null);
            } else {
                $tree = $this->buildTree($rows, null, null);
            }

            $cache->setItem($cache_id, $tree);
        }

        return $tree;
    }

    /**
     * @param bool $clearCache
     *
     * @return array|false|mixed
     *
     * @deprecated use fetchTreeForView
     */
    public function fetchCategoryTreeCurrentStore($clearCache = false)
    {
        $store_id = $this->store->config->store_id;

        $cache = $this->cache;
        $cache_id = self::CACHE_TREE_STORE . "_{$store_id}";

        if ($clearCache) {
            $cache->setItem($cache_id, false);
        }

        $tree = $cache->getItem($cache_id);
        if (false === $tree or empty($tree)) {
            $list_cat_id = self::fetchCatIdsForCurrentStore();
            $tree = $this->buildTree($list_cat_id);
            $cache->setItem($cache_id, $tree);
        }

        return $tree;
    }

    /**
     * @return mixed|null
     */
    public static function fetchCatIdsForCurrentStore()
    {
        return $GLOBALS['ocs_store_category_list'];
    }

    /**
     * @return array
     */
    public function fetchCatNamesForCurrentStore()
    {
        $list_cat_id = self::fetchCatIdsForCurrentStore();

        $sql = "SELECT project_category_id, title FROM project_category WHERE project_category_id IN (" . implode(',', $list_cat_id) . ")";

        return $this->projectCategoryRepository->fetchPairs($sql);
    }

    /**
     * @param $list_cat_id
     *
     * @return array
     */
    public function fetchCatNamesForID($list_cat_id)
    {

        $sql = "SELECT title FROM project_category WHERE project_category_id IN (" . implode(',', $list_cat_id) . ") order by title ";

        $results = $this->projectCategoryRepository->fetchAll($sql);

        return array_map(
            function ($row) {
                return $row['title'];
            }, $results
        );
    }

    /**
     * @return array
     */
    public function fetchCatNames()
    {
        return $this->projectCategoryRepository->fetchCatNames();
    }

    /**
     * @param int    $category_id
     * @param string $orderBy
     *
     * @return array
     */
    public function fetchAllSubCategories($category_id, $orderBy = self::ORDERED_HIERARCHIC)
    {
        $resultSet = $this->projectCategoryRepository->fetchImmediateChildren($category_id, $orderBy);

        $rows = array();
        if (count($resultSet) > 0) {
            foreach ($resultSet as $row) {
                $rows[$row['project_category_id']] = $row['title'];
            }
        }

        return $rows;
    }
}