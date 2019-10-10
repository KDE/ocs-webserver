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
class Default_Model_ProjectCategory
{
    const CACHE_TREE_STORE = 'store_cat_tree';
    const CACHE_TREE_SECTION = 'section_cat_tree';

    /** @var string */
    protected $_dataTableName;
    /** @var  Default_Model_DbTable_ProjectCategory */
    protected $_dataTable;

    /**
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * param [ mixed $args [, $... ]]
     *
     * @param string $_dataTableName
     *
     * @link http://php.net/manual/en/language.oop5.decon.php
     */
    public function __construct($_dataTableName = 'Default_Model_DbTable_ProjectCategory')
    {
        $this->_dataTableName = $_dataTableName;
        $this->_dataTable = new $this->_dataTableName;
    }

    /**
     * @param null $store_id
     *
     * @return array
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     */
    public function fetchTreeForView($store_id = null)
    {
        $tags = null;

        if (empty($store_id)) {
            $store_config = Zend_Registry::get('store_config');
            $store_id = $store_config->store_id;
            $tags = Zend_Registry::isRegistered('config_store_tags') ? Zend_Registry::get('config_store_tags') : array();
        }

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cache_id = __CLASS__ . '_' . __FUNCTION__ . "_{$store_id}";

        $tree = $cache->load($cache_id);

        if (false === $tree OR empty($tree)) {
            try {
                $rows = $this->fetchCategoryTreeWithTags($store_id, $tags);
            } catch (Zend_Exception $e) {
                Zend_Registry::get('logger')->err(__METHOD__ . ' - can not fetch categories : ' . $e->getMessage());
                $modelCategories = new Default_Model_DbTable_ConfigStore();
                $defaultStore = $modelCategories->fetchDefaultStoreId();
                $rows = $this->fetchCategoryTreeWithTags($defaultStore->store_id, $tags);
            }

            list($rows, $tree) = $this->buildTreeForView($rows);
            $cache->save($tree, $cache_id, array(), 120);
        }

        return $tree;
    }

    /**
     * @param int|null    $store_id
     * @param string|null $tags
     *
     * @return array
     * @throws Zend_Exception
     */
    protected function fetchCategoryTreeWithTags($store_id = null, $tags = null)
    {
        if (empty($store_id)) {
            return array();
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . $store_id . ' - ' . json_encode($tags));

        if (empty($tags)) {
            $statement = $this->_dataTable->getAdapter()->query("CALL fetchCatTreeForStore(:store_id)", array("store_id" => $store_id));
        } else {
            $statement = $this->_dataTable->getAdapter()->query("CALL fetchCatTreeWithTagsForStore(:store_id,:tagids)", array("store_id"=>$store_id, "tagids" => implode(',',$tags)));
        }

        $result = $statement->fetchAll();

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
                'parent_id'     => $row['parent_id']
            );

            //has children?
            if ($row['has_children'] == 1) {
                $result_element['has_children'] = true;
                $rememberParent = $row['id'];
                list($rows, $children) = $this->buildTreeForView($rows, $rememberParent);
                uasort($children, function ($a, $b) {return strcasecmp($a['title'], $b['title']);});
                $result_element['children'] = $children;
                $rememberParent = null;
            }

            $result[] = $result_element;

            if (isset($parent_id) AND isset($rows[0]['parent_id']) AND $parent_id != $rows[0]['parent_id']) {
                break;
            }
        }

        return array($rows, $result);
    }

    /**
     * @param int|null $store_id If not set, the tree for the current store will be returned
     * @param bool     $clearCache
     *
     * @return array
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @deprecated use fetchTreeForView
     */
    public function fetchCategoryTreeForStore($store_id = null, $clearCache = false)
    {
        if (empty($store_id)) {
            $store_config = Zend_Registry::get('store_config');
            $store_id = $store_config->store_id;
        }

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cache_id = self::CACHE_TREE_STORE . "_{$store_id}";

        if ($clearCache) {
            $cache->remove($cache_id);
        }

        if (false === ($tree = $cache->load($cache_id))) {
            $modelCategoryStore = new Default_Model_DbTable_ConfigStoreCategory();
            $rows = $modelCategoryStore->fetchCatIdsForStore((int)$store_id);

            if (count($rows) < 1) {
                $modelCategories = new Default_Model_DbTable_ProjectCategory();
                $root = $modelCategories->fetchRoot();
                $rows = $modelCategories->fetchImmediateChildrenIds($root['project_category_id'], $modelCategories::ORDERED_TITLE);
                $tree = $this->buildTree($rows, null, null);
            } else {
                $tree = $this->buildTree($rows, null, (int)$store_id);
            }

            $cache->save($tree, $cache_id, array(), 600);
        }

        return $tree;
    } 
    
    /**
     * @param int|null $store_id If not set, the tree for the current store will be returned
     * @param bool     $clearCache
     *
     * @return array
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @deprecated use fetchTreeForView
     */
    public function fetchCategoryTreeForSection($section_id = null, $clearCache = false)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cache_id = self::CACHE_TREE_SECTION . "_{$section_id}";

        if ($clearCache) {
            $cache->remove($cache_id);
        }

        if (false === ($tree = $cache->load($cache_id))) {
            $modelCategoryStore = new Default_Model_DbTable_SectionCategory();
            $rows = $modelCategoryStore->fetchCatIdsForSection((int)$store_id);

            if (count($rows) < 1) {
                $modelCategories = new Default_Model_DbTable_ProjectCategory();
                $root = $modelCategories->fetchRoot();
                $rows = $modelCategories->fetchImmediateChildrenIds($root['project_category_id'], $modelCategories::ORDERED_TITLE);
                $tree = $this->buildTree($rows, null, null);
            } else {
                $tree = $this->buildTree($rows, null, null);
            }

            $cache->save($tree, $cache_id, array(), 600);
        }

        return $tree;
    } 
    

    private function buildTree($list, $parent_id = null, $store_id = null)
    {
        if (false === is_array($list)) {
            $list = array($list);
        }
        $modelCategories = new Default_Model_DbTable_ProjectCategory();
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
                'has_children'  => false
            );

            if (isset($parent_id)) {
                $result_element['parent_id'] = $parent_id;
            }

            //has children?
            if (($currentCategory['rgt'] - $currentCategory['lft']) > 1) {
                $result_element['has_children'] = true;
                $ids = $modelCategories->fetchImmediateChildrenIds($currentCategory['project_category_id'],
                    $modelCategories::ORDERED_TITLE);
                $result_element['children'] = $this->buildTree($ids, $currentCategory['project_category_id'], $store_id);
            }
            $result[] = $result_element;
        }

        return $result;
    }

    private function fetchProductCount($cat_id, $store_id = null)
    {
       
        $store_config = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
        $tagFilter = null;
        if($store_config)
        {
            $tagFilter  = Zend_Registry::isRegistered('config_store_tags') ? Zend_Registry::get('config_store_tags') : null;
        }else
        {
            $tagFilter = null;
        }
       

        if ($tagFilter) {
            $sql =
                "SELECT count_product FROM stat_cat_prod_count WHERE project_category_id = :cat_id AND tag_id = :tags";
            $bind = array('cat_id' => $cat_id, 'tags' => $tagFilter);
        } else {
            $sql = "SELECT count_product FROM stat_cat_prod_count WHERE project_category_id = :cat_id AND tag_id IS NULL";
            $bind = array('cat_id' => $cat_id);
        }

        $result = $this->_dataTable->getAdapter()->fetchRow($sql, $bind);

        return (int)$result['count_product'];
    }

    /**
     * @param bool $clearCache
     *
     * @return array|false|mixed
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     *
     * @deprecated use fetchTreeForView
     */
    public function fetchCategoryTreeCurrentStore($clearCache = false)
    {
        $store_config = Zend_Registry::get('store_config');
        $store_id = $store_config->store_id;

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cache_id = self::CACHE_TREE_STORE . "_{$store_id}";

        if ($clearCache) {
            $cache->remove($cache_id);
        }

        if (false === ($tree = $cache->load($cache_id))) {
            $list_cat_id = self::fetchCatIdsForCurrentStore();
            $tree = $this->buildTree($list_cat_id);
            $cache->save($tree, $cache_id, array(), 600);
        }

        return $tree;
    }

    /**
     * @return mixed|null
     * @throws Zend_Exception
     */
    public static function fetchCatIdsForCurrentStore()
    {
        return Zend_Registry::isRegistered('store_category_list') ? Zend_Registry::get('store_category_list') : null;
    }

    /**
     * @return array
     * @throws Zend_Exception
     */
    public function fetchCatNamesForCurrentStore()
    {
        $list_cat_id = self::fetchCatIdsForCurrentStore();

        $sql = "SELECT project_category_id, title FROM project_category WHERE project_category_id IN (" . implode(',', $list_cat_id)
            . ")";

        $result = $this->_dataTable->getAdapter()->fetchPairs($sql);

        return $result;
    }

    /**
     * @return array
     * @throws Zend_Exception
     */
    public function fetchCatNamesForID($list_cat_id)
    {
        
        $sql = "SELECT title FROM project_category WHERE project_category_id IN (" . implode(',', $list_cat_id)
            . ") order by title " ;

        $results = $this->_dataTable->getAdapter()->fetchAll($sql);       
        $values = array_map(function($row) { return $row['title']; }, $results);
        return $values;
    }


    /**
     * @return array
     */
    public function fetchCatNames()
    {
        $sql = "SELECT project_category_id, title FROM project_category";

        $result = $this->_dataTable->getAdapter()->fetchPairs($sql);

        return $result;
    }

}