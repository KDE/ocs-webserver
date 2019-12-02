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
class Default_Model_Info
{

    const WALLPAPERCATEGORYID = '295';

    const TAG_ISORIGINAL = 'original-product';

    public function getLast200ImgsProductsForAllStores($limit = 200)
    {

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . md5('getLast200ImgsProductsForAllStores' . $limit);
        if ($resultSet = $cache->load($cacheName)) {
            return $resultSet;
        } else {

            $activeCategories = $this->getActiveCategoriesForAllStores();

            $sql = '
                SELECT 
                    `image_small`
                    ,`project_id`
                    ,`title`
                FROM
                    `project`
                WHERE
                    `project`.`image_small` IS NOT NULL 
                    AND `project`.`status` = 100
                        AND `project`.`project_category_id` IN (' . implode(',', $activeCategories) . ')
                ORDER BY ifnull(`project`.`changed_at`, `project`.`created_at`) DESC
                ';

            if (isset($limit)) {
                $sql .= ' limit ' . (int)$limit;
            }

            $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);

            if (count($resultSet) > 0) {

                $cache->save($resultSet, $cacheName, array(), 14400);

                return $resultSet;
            } else {
                return array();
            }
        }
    }

    public function getActiveCategoriesForAllStores($limit = null)
    {
        $sql = '
        SELECT DISTINCT
            `config_store_category`.`project_category_id`
        FROM
            `config_store`
        JOIN
            `config_store_category` ON `config_store`.`store_id` = `config_store_category`.`store_id`
        JOIN
            `project_category` ON `config_store_category`.`project_category_id` = `project_category`.`project_category_id`
        WHERE `project_category`.`is_active` = 1
        ORDER BY `config_store_category`.`order`;
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);

        if (count($resultSet) > 0) {
            $values = array_map(function ($row) {
                return $row['project_category_id'];
            }, $resultSet);

            return $values;
        } else {
            return array();
        }
    }

    public function getActiveStoresForCrossDomainLogin($limit = null)
    {
        $sql = '
        SELECT DISTINCT
            `config_store`.`host`
        FROM
            `config_store`
        WHERE `config_store`.`cross_domain_login` = 1
        ORDER BY `config_store`.`order`;
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);

        if (count($resultSet) > 0) {
            $values = array_map(function ($row) {
                return $row['host'];
            }, $resultSet);

            return $values;
        } else {
            return array();
        }
    }

    public function countTotalActiveMembers()
    {

        $cacheName = __FUNCTION__ . md5('countTotalActiveMembers');
        $cache = Zend_Registry::get('cache');

        $result = $cache->load($cacheName);

        if ($result) {
            return (int)$result['count_active_members'];
        }

        $sql = "SELECT count(1) AS `count_active_members` FROM (                    
                    SELECT count(1) AS `count_active_projects` FROM `project` `p`
                    WHERE `p`.`status` = 100
                    AND `p`.`type_id` = 1
                    GROUP BY `p`.`member_id`
                ) AS `A`;";

        $result = $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql);
        $cache->save($result, $cacheName);

        return (int)$result['count_active_members'];
    }

    /**
     * if category id not set the latest comments for all categories on the current host wil be returned.
     *
     * @param int      $limit
     * @param int|null $project_category_id
     * @param array    $tags
     *
     * @return array
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     */
    public function getLatestComments($limit = 5, $project_category_id = null, $tags = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheNameTags = is_array($tags) ? implode('_', $tags) : '';
        $cacheName =
            __FUNCTION__ . '_new_' . md5(Zend_Registry::get('store_host') . (int)$limit . (int)$project_category_id . $cacheNameTags);

        if (($latestComments = $cache->load($cacheName))) {
            return $latestComments;
        }

        if (empty($project_category_id)) {
            $activeCategories = $this->getActiveCategoriesForCurrentHost();
        } else {
            $activeCategories = $this->getActiveCategoriesForCatId($project_category_id);
        }

        if (count($activeCategories) == 0) {
            return array();
        }

        $sql = '                
                   SELECT
                       `comment_id`
                       ,`comment_text`
                       ,`member`.`member_id`
                       ,`member`.`profile_image_url`
                       ,`comment_created_at`
                       ,`member`.`username`
                       ,`comment_target_id`
                       ,`title`
                       ,`stat_projects`.`project_id`  
                       ,`cat_title` AS `catTitle`                                  
                   FROM `comments`
                   STRAIGHT_JOIN `member` ON `comments`.`comment_member_id` = `member`.`member_id`
                   INNER JOIN `stat_projects` ON `comments`.`comment_target_id` = `stat_projects`.`project_id` ';

        /*
        if (isset($tags)) {
            $sql .= ' JOIN (SELECT DISTINCT project_id FROM stat_project_tagids WHERE tag_id in (' . implode(',', $tags)
                . ')) AS store_tags ON stat_projects.project_id = store_tags.project_id';
        }
        */
        
        $sql .= ' WHERE comments.comment_active = 1            
            AND stat_projects.status = 100
            AND stat_projects.type_id in (1,3)
            AND comments.comment_type = 0
            AND stat_projects.project_category_id IN (' . implode(',', $activeCategories) . ')                          
        ';
        
        //Store Tag Filter
        if (isset($tags)) {
            $tagList = $tags;
            //build where statement für projects
            $sql .= " AND (";

            if(!is_array($tagList)) {
                $tagList = array($tagList);
            }
            
            foreach($tagList as $item) {
                #and
                $sql .= ' find_in_set('.$item.', tag_ids) AND ';
            }
            $sql .= ' 1=1)';;
        }

        $sql .= '  ORDER BY comments.comment_created_at DESC ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);

        if (count($resultSet) > 0) {
            $cache->save($resultSet, $cacheName, array(), 300);

            return $resultSet;
        } else {
            $cache->save(array(), $cacheName, array(), 300);

            return array();
        }
    }

    /**
     * @param int $omitCategoryId
     *
     * @return array
     * @TODO: check all occurrences of this function
     */
    public function getActiveCategoriesForCurrentHost($omitCategoryId = null)
    {
        $currentHostMainCategories = Zend_Registry::get('store_category_list');

        $modelCategory = new Default_Model_DbTable_ProjectCategory();
        $activeChildren = $modelCategory->fetchChildIds($currentHostMainCategories);
        $activeCategories = array_unique(array_merge($currentHostMainCategories, $activeChildren));

        if (empty($omitCategoryId)) {
            return $activeCategories;
        }

        $omitChildren = $modelCategory->fetchChildIds($omitCategoryId);

        return array_diff($activeCategories, $omitChildren);
    }

    /**
     * @param int      $project_category_id
     * @param int|null $omitCategoryId
     *
     * @return array
     */
    public function getActiveCategoriesForCatId($project_category_id, $omitCategoryId = null)
    {
        $modelCategory = new Default_Model_DbTable_ProjectCategory();
        $activeChildren = $modelCategory->fetchChildIds($project_category_id);
        $activeCategories = array_unique(array_merge(array($project_category_id), $activeChildren));
        if (empty($omitCategoryId)) {
            return $activeCategories;
        }
        $omitChildren = $modelCategory->fetchChildIds($omitCategoryId);

        return array_diff($activeCategories, $omitChildren);
    }

    /**
     * if category id not set the most downloaded products for all categories on the current host wil be returned.
     *
     * @param int   $limit
     * @param null  $project_category_id
     * @param array $tags
     *
     * @return array|false|mixed
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     */
    public function getMostDownloaded($limit = 100, $project_category_id = null, $tags = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheNameTags = is_array($tags) ? implode('_', $tags) : '';
        $cacheName =
            __FUNCTION__ . '_new_' . md5(Zend_Registry::get('store_host') . (int)$limit . (int)$project_category_id . $cacheNameTags);

        if (($mostDownloaded = $cache->load($cacheName))) {
            return $mostDownloaded;
        }

        if (empty($project_category_id)) {
            $activeCategories = $this->getActiveCategoriesForCurrentHost();
        } else {
            $activeCategories = $this->getActiveCategoriesForCatId($project_category_id);
        }

        if (count($activeCategories) == 0) {
            return array();
        }

        $sql = '
            SELECT 
                 `p`.`project_id`
                ,`p`.`title`
                ,`p`.`image_small`       
                ,`s`.`amount` 
                ,`s`.`category_title` 
                ,`p`.`package_types`
                ,`p`.`tag_ids`
                FROM `stat_downloads_quarter_year` `s`
                INNER JOIN `stat_projects` `p` ON `s`.`project_id` = `p`.`project_id`';

        /*
        if (isset($tags)) {
            $sql .= ' JOIN (SELECT DISTINCT project_id FROM stat_project_tagids WHERE tag_id in (' . implode(',', $tags)
                . ')) AS store_tags ON p.project_id = store_tags.project_id';
        }
         * 
         */

        $sql .= ' WHERE
                    p.status=100
                    and 
                    p.project_category_id IN (' . implode(',', $activeCategories) . ')          
            ';
        
        //Store Tag Filter
        if (isset($tags)) {
            $tagList = $tags;
            //build where statement für projects
            $sql .= " AND (";

            if(!is_array($tagList)) {
                $tagList = array($tagList);
            }
            
            foreach($tagList as $item) {
                #and
                $sql .= ' find_in_set('.$item.', tag_ids) AND ';
            }
            $sql .= ' 1=1)';;
        }

        $sql .= '  ORDER BY s.amount DESC ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);

        if (count($resultSet) > 0) {
            $cache->save($resultSet, $cacheName, array(), 300);

            return $resultSet;
        } else {
            $cache->save($resultSet, $cacheName, array(), 300);

            return array();
        }
    }

    /**
     * @param int         $limit
     * @param int|null    $project_category_id
     * @param array|null  $tags
     * @param string|null $tag_isoriginal
     *
     * @return array|false
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     */
    public function getLastProductsForHostStores($limit = 10, $project_category_id = null, $tags = null, $tag_isoriginal = null)
    {
        $catids = "";
        if ($project_category_id) {
            $catids = str_replace(',', '', (string)$project_category_id);
        }

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheNameTags = is_array($tags) ? implode('_', $tags) : '';
        $cacheName =
            __FUNCTION__ . '_' . md5(Zend_Registry::get('store_host') . (int)$limit . $catids . $cacheNameTags . $tag_isoriginal);

        if (($resultSet = $cache->load($cacheName))) {
            return $resultSet;
        }

        $activeCategories = array();
        if (empty($project_category_id)) {
            $activeCategories = $this->getActiveCategoriesForCurrentHost();
        } else {
            $cats = explode(",", $project_category_id);
            if (count($cats) == 1) {
                $activeCategories = $this->getActiveCategoriesForCatId($project_category_id);
            } else {
                foreach ($cats as $cat) {
                    $tmp = $this->getActiveCategoriesForCatId($cat);
                    $activeCategories = array_merge($tmp, $activeCategories);
                }
            }
        }

        if (count($activeCategories) == 0) {
            return array();
        }

        $sql = '
            SELECT 
                `p`.*              
            FROM
                `stat_projects`  AS `p`
                ';

        /*
        if (isset($tags)) {
            $sql .= ' JOIN (SELECT DISTINCT project_id FROM stat_project_tagids WHERE tag_id in (' . implode(',', $tags)
                . ')) AS store_tags ON p.project_id = store_tags.project_id ';
        }
         * 
         */

        $sql .= '
            WHERE
                `p`.`status` = 100                
                AND `p`.`project_category_id` IN (' . implode(',', $activeCategories) . ')
                AND `p`.`amount_reports` IS NULL';
        
        //Store Tag Filter
        if (isset($tags)) {
            $tagList = $tags;
            //build where statement für projects
            $sql .= " AND (";

            if(!is_array($tagList)) {
                $tagList = array($tagList);
            }
            
            foreach($tagList as $item) {
                #and
                $sql .= ' find_in_set('.$item.', tag_ids) AND ';
            }
            $sql .= ' 1=1)';;
        }

        if (isset($tag_isoriginal)) {
            if ($tag_isoriginal) {
                $sql .= ' AND find_in_set("' . self::TAG_ISORIGINAL . '", tags)';
            } else {
                $sql .= ' AND NOT find_in_set("' . self::TAG_ISORIGINAL . '", tags)';
            }
        }

        //$sql .= ' ORDER BY IFNULL(p.changed_at,p.created_at)  DESC';
        $sql .= ' ORDER BY p.major_updated_at  DESC';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
        $cache->save($resultSet, $cacheName, array(), 300);

        if (count($resultSet) > 0) {

            return $resultSet;
        }

        return array();
    }


    /**
     * @param int         $limit
     * @param string|null $project_category_id
     * @param array|null  $tags
     * @param string|null $tag_isoriginal
     * @param int         $offset
     *
     * @return string
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     */
    public function getJsonLastProductsForHostStores(
        $limit = 10,
        $project_category_id = null,
        $tags = null,
        $tag_isoriginal = null,
        $offset = 0
    ) {
        
        $store_id = null;
        
        if (empty($store_id)) {
            $store_config = Zend_Registry::get('store_config');
            $store_id = $store_config->store_id;
            $store_tags = Zend_Registry::isRegistered('config_store_tags') ? Zend_Registry::get('config_store_tags') : array();
            
            if(empty($tags)) {
               $tags = $store_tags; 
            } else {
                $tags = array_merge($tags, $store_tags);
            }
        }
        
        
        
        $cat_ids = "";
        if ($project_category_id) {
            $cat_ids = str_replace(',', '_', (string)$project_category_id);
        }

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheNameTags = is_array($tags) ? implode('_', $tags) : '';
        $cacheName =
            __FUNCTION__ . '_' . md5(Zend_Registry::get('store_host') . (int)$limit . $cat_ids . $cacheNameTags . $tag_isoriginal
                . $offset);

        if (($resultSet = $cache->load($cacheName))) {
            return $resultSet;
        }

        $activeCategories = array();
        if (empty($project_category_id)) {
            $activeCategories = $this->getActiveCategoriesForCurrentHost();
        } else {
            $cats = explode(",", $project_category_id);
            if (count($cats) == 1) {
                $activeCategories = $this->getActiveCategoriesForCatId($project_category_id);
            } else {
                foreach ($cats as $cat) {
                    $tmp = $this->getActiveCategoriesForCatId($cat);
                    $activeCategories = array_merge($tmp, $activeCategories);
                }
            }
        }

        if (count($activeCategories) == 0) {
            return array();
        }

        $sql = '
            SELECT 
                `project_id`,
                `member_id`,
                `image_small`,
                `title`,
                `version`,
                `cat_title`,
                `count_comments`,
                `package_names`,
                `tag_ids`,
                `laplace_score`,
                `count_likes`,
                `count_dislikes`,
                `changed_at`,
                `created_at`            
            FROM
                `stat_projects`  AS `p`';

        /*
        if (isset($tags)) {
            $sql .= ' JOIN (SELECT DISTINCT project_id FROM stat_project_tagids WHERE tag_id in (' . implode(',', $tags)
                . ')) AS store_tags ON p.project_id = store_tags.project_id';
        }
         * 
         */


        $sql .= '
            WHERE
                `p`.`status` = 100                
                AND `p`.`project_category_id` IN (' . implode(',', $activeCategories) . ')
                AND `p`.`amount_reports` IS NULL';
        
        //Store Tag Filter
        if (isset($tags)) {
            $tagList = $tags;
            //build where statement für projects
            $sql .= " AND (";

            if(!is_array($tagList)) {
                $tagList = array($tagList);
            }
            
            foreach($tagList as $item) {
                #and
                $sql .= ' find_in_set('.$item.', tag_ids) AND ';
            }
            $sql .= ' 1=1)';;
        }

        if (isset($tag_isoriginal)) {
            if ($tag_isoriginal) {
                $sql .= ' AND find_in_set("' . self::TAG_ISORIGINAL . '", tags)';
            } else {
                $sql .= ' AND NOT find_in_set("' . self::TAG_ISORIGINAL . '", tags)';
            }
        }

        $sql .= ' ORDER BY IFNULL(p.changed_at,p.created_at)  DESC';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }
        
        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
        $imagehelper = new Default_View_Helper_Image();
        foreach ($resultSet as &$value) {
            $value['image_small'] = $imagehelper->Image($value['image_small'], array('width' => 200, 'height' => 200));
        }
        if (count($resultSet) > 0) {
            $result = Zend_Json::encode($resultSet);
            $cache->save($result, $cacheName, array(), 300);

            return $result;
        }

        return Zend_Json::encode('');
    }

    /**
     * @param int         $limit
     * @param string|null $project_category_id
     * @param array|null  $tags
     *
     * @return array|false
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     */
    public function getTopProductsForHostStores($limit = 10, $project_category_id = null, $tags = null)
    {
        $catids = "";
        if ($project_category_id) {
            $catids = str_replace(',', '', (string)$project_category_id);
        }

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheNameTags = is_array($tags) ? implode('_', $tags) : '';
        $cacheName = __FUNCTION__ . '_' . md5(Zend_Registry::get('store_host') . (int)$limit . $catids . $cacheNameTags);

        if (($resultSet = $cache->load($cacheName))) {
            return $resultSet;
        }

        $activeCategories = array();
        if (empty($project_category_id)) {
            $activeCategories = $this->getActiveCategoriesForCurrentHost();
        } else {
            $cats = explode(",", $project_category_id);
            if (count($cats) == 1) {
                $activeCategories = $this->getActiveCategoriesForCatId($project_category_id);
            } else {
                foreach ($cats as $cat) {
                    $tmp = $this->getActiveCategoriesForCatId($cat);
                    $activeCategories = array_merge($tmp, $activeCategories);
                }
            }
        }

        if (count($activeCategories) == 0) {
            return array();
        }

        $sql = '
            SELECT 
                `p`.*              
            FROM
                `stat_projects`  AS `p`';

        /*
        if (isset($tags)) {
            $sql .= ' JOIN (SELECT DISTINCT project_id FROM stat_project_tagids WHERE tag_id in (' . implode(',', $tags)
                . ')) AS store_tags ON p.project_id = store_tags.project_id';
        }
         * 
         */

        $sql .= '
            WHERE
                `p`.`status` = 100                
                AND `p`.`project_category_id` IN (' . implode(',', $activeCategories) . ')
                AND `p`.`amount_reports` IS NULL';

        //Store Tag Filter
        if (isset($tags)) {
            $tagList = $tags;
            //build where statement für projects
            $sql .= " AND (";

            if(!is_array($tagList)) {
                $tagList = array($tagList);
            }
            
            foreach($tagList as $item) {
                #and
                $sql .= ' find_in_set('.$item.', tag_ids) AND ';
            }
            $sql .= ' 1=1)';;
        }
        
        /*$sql .= ' ORDER BY (round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100) DESC, created_at DESC
            ';*/ 
        $sql .= ' ORDER BY laplace_score DESC, created_at DESC
            ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
        $cache->save($resultSet, $cacheName, array(), 300);

        if (count($resultSet) > 0) {
            return $resultSet;
        }

        return array();
    }

    public function getRandProduct()
    {
        $pid = $this->getRandomStoreProjectIds();
        $project_id = $pid['project_id'];

        $sql="SELECT 
                p.project_id
                ,p.title
                ,p.description  
                ,p.image_small
                ,p.count_comments   
                ,p.changed_at
                ,pr.likes as count_likes
                ,pr.dislikes as count_dislikes
                ,IFNULL(pr.score_with_pling, 500) AS laplace_score
                ,m.profile_image_url
                ,m.username                               
                FROM
                project as p            
                JOIN member AS m ON m.member_id = p.member_id
                LEFT join  stat_rating_project AS pr  ON p.project_id = pr.project_id
                WHERE
                p.project_id = :project_id";
        /*$sql = '
            SELECT 
                `p`.*
                ,laplace_score(`p`.`count_likes`, `p`.`count_dislikes`) AS `laplace_score`
                ,`m`.`profile_image_url`
                ,`m`.`username`
            FROM
                `project` AS `p`
            JOIN 
                `member` AS `m` ON `m`.`member_id` = `p`.`member_id`
            WHERE
               `p`.`project_id` = :project_id
            ';*/
        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql, array('project_id' => $project_id));
        return $resultSet;
      
    }

    public function getRandPlingedProduct()
    {
        $pid = $this->getRandomPlingedProjectIds();
        $project_id = $pid['project_id'];

        $sql = "SELECT 
                p.project_id
                ,p.title
                ,p.description  
                ,p.image_small
                ,p.count_comments   
                ,p.changed_at
                ,pr.likes as count_likes
                ,pr.dislikes as count_dislikes
                ,IFNULL(pr.score_with_pling, 500) AS laplace_score
                ,m.profile_image_url
                ,m.username                               
                ,(select count(1) from project_plings pp where pp.project_id = p.project_id and pp.is_deleted = 0) as sum_plings
                FROM
                project as p            
                JOIN member AS m ON m.member_id = p.member_id
                LEFT join  stat_rating_project AS pr  ON p.project_id = pr.project_id
                WHERE
                p.project_id = :project_id";
        /*$sql = '
            SELECT 
                `p`.*
                ,laplace_score(`p`.`count_likes`, `p`.`count_dislikes`) AS `laplace_score`
                ,`m`.`profile_image_url`
                ,`m`.`username`
                ,(select count(1) from project_plings pp where pp.project_id = p.project_id and pp.is_deleted = 0) as sum_plings
            FROM
                `project` AS `p`
            JOIN 
                `member` AS `m` ON `m`.`member_id` = `p`.`member_id`
            WHERE
               `p`.`project_id` = :project_id
            ';*/
        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql, array('project_id' => $project_id));
        return $resultSet;
        /*$resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('project_id' => $project_id));
        if (count($resultSet) > 0) {
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array($resultSet));
        }

        return new Zend_Paginator(new Zend_Paginator_Adapter_Array(array()));*/
    }

    public function getRandFeaturedProduct()
    {
        $pid = $this->getRandomFeaturedProjectIds();
        $project_id = $pid['project_id'];

        $sql="SELECT 
                p.project_id
                ,p.title
                ,p.description  
                ,p.image_small
                ,p.count_comments   
                ,p.changed_at
                ,pr.likes as count_likes
                ,pr.dislikes as count_dislikes
                ,IFNULL(pr.score_with_pling, 500) AS laplace_score
                ,m.profile_image_url
                ,m.username                               
                FROM
                project as p            
                JOIN member AS m ON m.member_id = p.member_id
                LEFT join  stat_rating_project AS pr  ON p.project_id = pr.project_id
                WHERE
                p.project_id = :project_id";
        /*$sql = '
            SELECT 
                `p`.project_id                
                ,IFNULL(`pr`.`score_with_pling`, 500) AS `laplace_score`
                ,`m`.`profile_image_url`
                ,`m`.`username`               
            FROM
                `project` AS `p`            
            JOIN 
                `member` AS `m` ON `m`.`member_id` = `p`.`member_id`
            LEFT join  `stat_rating_project` AS `pr`  ON `p`.`project_id` = `pr`.`project_id`     
            WHERE
               `p`.`project_id` = :project_id
            ';*/
        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql, array('project_id' => $project_id));
        return $resultSet;
    }

    public function getRandomStoreProjectIds()
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5(Zend_Registry::get('store_host'));

        $resultSet = $cache->load($cacheName);

        if (false == $resultSet) {
            $activeCategories = $this->getActiveCategoriesForCurrentHost();
            if (count($activeCategories) == 0) {
                return array();
            }
            $sql = '
                    SELECT 
                        `p`.`project_id`                   
                    FROM
                        `project` AS `p`                
                    WHERE
                        `p`.`status` = 100
                        AND `p`.`type_id` = 1                    
                        AND `p`.`project_category_id` IN (' . implode(',', $activeCategories) . ')                    
                    ';
            $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
            $cache->save($resultSet, $cacheName, array(), 3600 * 24);
        }

        $irandom = rand(0, sizeof($resultSet)-1);

        return $resultSet[$irandom];
    }

    public function getRandomPlingedProjectIds()
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ ;

        $resultSet = $cache->load($cacheName);

        if (false == $resultSet) {
            $sql="      select    
                            p.project_id
                        from project_plings pl
                        inner join stat_projects p on pl.project_id = p.project_id            
                        where pl.is_deleted = 0 and pl.is_active = 1 ";
            $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
            $cache->save($resultSet, $cacheName, array(), 3600 * 24); //cache is cleaned once a day
        }

        $irandom = rand(0, sizeof($resultSet)-1);

        return $resultSet[$irandom];
    }


    public function getRandomFeaturedProjectIds()
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ ;

        $resultSet = $cache->load($cacheName);

        if (false == $resultSet) {
            $sql="select project_id from  project p where p.status = 100 and p.featured = 1 ";
            $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
            $cache->save($resultSet, $cacheName, array(), 3600 * 24); //cache is cleaned once a day
        }

        $irandom = rand(0, sizeof($resultSet)-1);

        return $resultSet[$irandom];
    }

 
    /**
     * @param int  $limit
     * @param null $project_category_id
     *
     * @return array|Zend_Paginator
     */
    public function getFeaturedProductsForHostStores($limit = 10, $project_category_id = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5(Zend_Registry::get('store_host') . (int)$limit . (int)$project_category_id);

        if (false !== ($resultSet = $cache->load($cacheName))) {

            return new Zend_Paginator(new Zend_Paginator_Adapter_Array($resultSet));
        }

        if (empty($project_category_id)) {
            $activeCategories = $this->getActiveCategoriesForCurrentHost();
        } else {
            $activeCategories = $this->getActiveCategoriesForCatId($project_category_id);
        }

        if (count($activeCategories) == 0) {
            return array();
        }

        $sql = '
            SELECT 
                `p`.*              
                ,`m`.`profile_image_url`
                ,`m`.`username`
            FROM
                `stat_projects` AS `p`
            JOIN 
                `member` AS `m` ON `m`.`member_id` = `p`.`member_id`
            WHERE
                `p`.`status` = 100
                AND `p`.`type_id` = 1
                AND `p`.`featured` = 1
                AND `p`.`project_category_id` IN (' . implode(',', $activeCategories) . ')                
            ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
        $cache->save($resultSet, $cacheName, array(), 60);

        if (count($resultSet) > 0) {
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array($resultSet));
        } else {
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array(array()));
        }
    }

    public function getLastCommentsForUsersProjects($member_id, $limit = 10, $comment_type=0)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5(Zend_Registry::get('store_host') . (int)$member_id . (int)$limit).$comment_type;

        if (false !== ($resultSet = $cache->load($cacheName))) {
            return $resultSet;
        }
       

        $sql = '
                SELECT 
                comment_id
                ,comment_text
                , member.member_id
                ,member.profile_image_url
                ,comment_created_at
                ,username            
                ,title
                ,project_id
                ,comments.comment_target_id

                FROM comments           
                JOIN project ON comments.comment_target_id = project.project_id 
                STRAIGHT_JOIN member ON comments.comment_member_id = member.member_id
                WHERE comments.comment_active = 1
                AND project.status = 100
                and comments.comment_type=:comment_type
                AND project.member_id =:member_id
                and comments.comment_member_id <>:member_id
                ORDER BY comments.comment_created_at DESC               
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('member_id' => $member_id, 'comment_type'=>$comment_type));

        if (count($resultSet) > 0) {
            $cache->save($resultSet, $cacheName, array(), 300);

            return $resultSet;
        } else {
            $cache->save(array(), $cacheName, array(), 300);

            return array();
        }
    }
    public function getFeaturedProductsForUser($member_id,$limit = 10)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5(Zend_Registry::get('store_host') . (int)$member_id . (int)$limit);

        if (false !== ($resultSet = $cache->load($cacheName))) {
            return $resultSet;
        }
        
        $sql="SELECT 
                p.project_id
                ,p.title
                ,p.description  
                ,p.image_small
                ,p.count_comments   
                ,p.changed_at                
                ,p.laplace_score
                ,p.profile_image_url
                ,p.username                               
                FROM
                stat_projects as p                                           
                WHERE
                p.status = 100 and p.featured = 1  and p.member_id = 24 
        ";

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('threshold' => Default_Model_Spam::SPAM_THRESHOLD, 'member_id' => $member_id));

        if ($result->rowCount() > 0) {            
            $resultSet = $result->fetchAll();        
        } else {
            $resultSet = array();
        }
        $cache->save($resultSet, $cacheName, array(), 300);
        return $resultSet;
    }

    public function getLastVotesForUsersProjects($member_id, $limit = 10)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5(Zend_Registry::get('store_host') . (int)$member_id . (int)$limit);

        if (false !== ($resultSet = $cache->load($cacheName))) {
            return $resultSet;
        }

        $sql = '
                SELECT 
                `rating_id`                
                ,`member`.`member_id`   
                ,`member`.`profile_image_url`                           
                ,`username`            
                ,`user_like`
                ,`user_dislike`
                ,`score`
                ,`project_rating`.`project_id`
                ,`project_rating`.`created_at`
                ,`project`.`title`
                ,`comments`.`comment_text`
                ,`comments`.`comment_id`
                FROM `project_rating`           
                JOIN `project` ON `project_rating`.`project_id` = `project`.`project_id` 
                join `comments` on `project_rating`.`comment_id` = `comments`.`comment_id`   
                STRAIGHT_JOIN `member` ON `project_rating`.`member_id` = `member`.`member_id`
                WHERE `project`.`status` = 100 and `project_rating`.`rating_active`=1
                AND `project`.`member_id` = :member_id
                ORDER BY `rating_id` DESC               
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('member_id' => $member_id));

        if (count($resultSet) > 0) {
            $cache->save($resultSet, $cacheName, array(), 300);

            return $resultSet;
        } else {
            $cache->save(array(), $cacheName, array(), 300);

            return array();
        }
    }

     public function getLastSpamProjects($member_id, $limit = 10)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5(Zend_Registry::get('store_host') . (int)$member_id . (int)$limit);

        if (false !== ($resultSet = $cache->load($cacheName))) {
            return $resultSet;
        }

        $sql = "
            SELECT *
            FROM `stat_projects`
            WHERE `stat_projects`.`amount_reports` >= :threshold AND `stat_projects`.`status` = 100
            AND  `stat_projects`.`member_id` = :member_id
            ORDER BY `stat_projects`.`changed_at` DESC, `stat_projects`.`created_at` DESC, `stat_projects`.`amount_reports` DESC
        ";

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('threshold' => Default_Model_Spam::SPAM_THRESHOLD, 'member_id' => $member_id));

        if ($result->rowCount() > 0) {            
            $resultSet = $result->fetchAll();        
        } else {
            $resultSet = array();
        }
        $cache->save($resultSet, $cacheName, array(), 300);
        return $resultSet;
        
    }

    public function getLastDonationsForUsersProjects($member_id, $limit = 10)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5(Zend_Registry::get('store_host') . (int)$member_id . (int)$limit);

        if (false !== ($resultSet = $cache->load($cacheName))) {
            return $resultSet;
        }

        $sql = '
         SELECT 
                plings.project_id,
                plings.id 
                ,member.member_id
                ,profile_image_url
                ,plings.create_time
                ,username
                ,plings.amount
                ,comment
                ,project.title
                FROM plings
                JOIN project ON project.project_id = plings.project_id   
                STRAIGHT_JOIN member ON plings.member_id = member.member_id     
                WHERE 
                plings.status_id = 2
                AND project.status=100
                AND project.member_id = :member_id
                ORDER BY create_time DESC
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('member_id' => $member_id));

        if (count($resultSet) > 0) {
            $cache->save($resultSet, $cacheName, array(), 300);

            return $resultSet;
        } else {
            $cache->save(array(), $cacheName, array(), 300);

            return array();
        }
    }

    /**
     * @param int $limit
     *
     * @return array|false|mixed
     */
    public function getNewActiveMembers($limit = 20)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit);

        if (false !== ($newMembers = $cache->load($cacheName))) {
            return $newMembers;
        }

        $sql = '
                SELECT 
                member_id,
                profile_image_url,
                username,
                created_at
                FROM member
                WHERE `is_active` = :activeVal
                AND `type` = :typeVal     
                ORDER BY created_at DESC             
            ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultMembers = Zend_Db_Table::getDefaultAdapter()->query($sql, array(
            'activeVal' => Default_Model_Member::MEMBER_ACTIVE,
            'typeVal'   => Default_Model_Member::MEMBER_TYPE_PERSON
        ))->fetchAll()
        ;

        $cache->save($resultMembers, $cacheName, array(), 300);

        return $resultMembers;
    }


   
    public function getSupporters($limit = 20)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit);

        if (false !== ($newSupporters = $cache->load($cacheName))) {
            return $newSupporters;
        }
        $sql = '
                select  
                s.member_id as supporter_id
                ,s.member_id
                ,(select username from member m where m.member_id = s.member_id) as username
                ,(select profile_image_url from member m where m.member_id = s.member_id) as profile_image_url
                ,max(s.active_time_max) as created_at
                from v_support s
                group by member_id
                order by active_time_max desc                                       
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array())->fetchAll();
        $cache->save($result, $cacheName, array(), 300);

        return $result;
    }

    public function getNewActiveSupporters($limit = 20)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit);

        if (false !== ($newSupporters = $cache->load($cacheName))) {
            return $newSupporters;
        }
        /*$sql = '
                        SELECT 
                        s.member_id as supporter_id
                        ,s.member_id
                        ,(select username from member m where m.member_id = s.member_id) as username
                        ,(select profile_image_url from member m where m.member_id = s.member_id) as profile_image_url
                        ,min(s.active_time) as created_at
                        from support s 
                        where s.status_id = 2  
                        and (DATE_ADD((s.active_time), INTERVAL 1 YEAR) > now())
                        group by member_id
                        order by s.active_time desc                                       
        ';*/
        $sql = '
                select  
                s.member_id as supporter_id
                ,s.member_id
                ,(select username from member m where m.member_id = s.member_id) as username
                ,(select profile_image_url from member m where m.member_id = s.member_id) as profile_image_url
                ,max(s.active_time_max) as created_at
                from v_support s
                group by member_id
                order by active_time_max desc                                       
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array())->fetchAll();
        $cache->save($result, $cacheName, array(), 300);

        return $result;
    }
    

    public function getNewActiveSupportersForSectionAll($limit = 20)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit);

        if (false !== ($newSupporters = $cache->load($cacheName))) {
            return $newSupporters;
        }
        
        $sql = '
                SELECT  s.*,
                s.member_id as supporter_id
                ,s.member_id
                ,(select username from member m where m.member_id = s.member_id) as username
                ,(select profile_image_url from member m where m.member_id = s.member_id) as profile_image_url
                ,MAX(s.active_time) AS active_time_max
                ,ss.tier AS section_support_tier
                from section_support_paypements ss
                JOIN support s ON s.id = ss.support_id
                WHERE ss.yearmonth = DATE_FORMAT(NOW(), "%Y%m")
                GROUP BY s.member_id,ss.tier
                order BY active_time_max desc                                       
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
        $cache->save($result, $cacheName, array(), 300);

        return $result;
    }

    public function getSectionSupportersActiveMonths($section_id)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . $section_id ;

        $sql = "SELECT COUNT(1) AS active_months, member_id,sum(tier) sum_support FROM
                (
                SELECT s.member_id, p.yearmonth , sum(p.tier) tier FROM section_support_paypements p
                JOIN support s ON s.id = p.support_id
                WHERE p.section_id = :section_id
                GROUP BY s.member_id, p.yearmonth
                ) A
                GROUP BY member_id
                ";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('section_id' => $section_id))->fetchAll();
        $cache->save($result, $cacheName, array(), 300);
        return $result;
    }

    public function getNewActiveSupportersForSectionUnique($section_id, $limit = 1000)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . $section_id . '_' . md5((int)$limit);

        if (false !== ($newSupporters = $cache->load($cacheName))) {
            return $newSupporters;
        }
        
        $sql = '
                SELECT  
                s.member_id
                ,m.username
                ,m.profile_image_url
                ,sum(ss.tier) AS sum_support
                from section_support_paypements ss
                JOIN support s ON s.id = ss.support_id
                join member m on m.member_id = s.member_id
                WHERE ss.section_id = :section_id
                AND ss.yearmonth = DATE_FORMAT(NOW(), "%Y%m")
                GROUP BY s.member_id
                order BY ss.tier DESC                                  
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('section_id' => $section_id))->fetchAll();
        $cache->save($result, $cacheName, array(), 300);

        return $result;
    }

    public function getRandomSupporterForSection($section_id)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . $section_id;

        $supporters = $cache->load($cacheName);
        if (!$supporters) {
            $sql = '
                    select section_id, member_id, weight 
                    from v_supporter_view_queue 
                    where section_id = :section_id
                    order by weight desc                        
            ';        
            $supporters = Zend_Db_Table::getDefaultAdapter()->query($sql, array('section_id' => $section_id))->fetchAll();      
            
            //If there is no real supporter, show pling user
            if(!$supporters || count($supporters) == 0) {
                $sql = '
                    select section_id, member_id, weight 
                    from v_supporter_view_queue_all 
                    where section_id = :section_id
                    order by weight desc                        
                ';        
                $supporters = Zend_Db_Table::getDefaultAdapter()->query($sql, array('section_id' => $section_id))->fetchAll(); 
            }
            
            $cache->save($supporters, $cacheName, array(), 300);
        }
            
        $sumWeight =0;
        foreach ($supporters as $s) {
            $sumWeight=$sumWeight+$s['weight'];
        }
        // select Random [1.. sumWeight];
        $randomWeight = rand(1,$sumWeight);
        $sumWeight =0;
        $member_id=null;
        foreach ($supporters as $s) {
           $sumWeight=$sumWeight+$s['weight'];
           if($sumWeight >= $randomWeight)
           {
                $member_id = $s['member_id'];
                break;
           }
        }
        if($member_id)
        {
            $sql = "select member_id,username,profile_image_url from member where member_id=:member_id";
            
            $result = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql,array('member_id' => $member_id));
            return $result;
        }


        return null;
    }
   

    public function getNewActiveSupportersForSection($section_id, $limit = 20)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . $section_id . '_' . md5((int)$limit);

        if (false !== ($newSupporters = $cache->load($cacheName))) {
            return $newSupporters;
        }
        
        $sql = '
                SELECT  s.*,
                s.member_id as supporter_id
                ,s.member_id
                ,(select username from member m where m.member_id = s.member_id) as username
                ,(select profile_image_url from member m where m.member_id = s.member_id) as profile_image_url
                ,MAX(s.active_time) AS active_time_max
                ,ss.tier AS section_support_tier
                from section_support_paypements ss
                JOIN support s ON s.id = ss.support_id
                WHERE ss.section_id = :section_id
                AND ss.yearmonth = DATE_FORMAT(NOW(), "%Y%m")
                GROUP BY s.member_id,ss.tier
                order BY  active_time_max desc                                       
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('section_id' => $section_id))->fetchAll();
        $cache->save($result, $cacheName, array(), 300);

        return $result;
    }
    
    
    public function getNewActiveSupportersForSectionAndMonth($section_id, $yearmonth, $limit = 100)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . $section_id . '_' . $yearmonth . '_' . md5((int)$limit);

        if (false !== ($newSupporters = $cache->load($cacheName))) {
            return $newSupporters;
        }
        
        $sql = '
                SELECT  s.*,
                s.member_id as supporter_id
                ,s.member_id
                ,(select username from member m where m.member_id = s.member_id) as username
                ,(select profile_image_url from member m where m.member_id = s.member_id) as profile_image_url
                ,MAX(s.active_time) AS active_time_max
                ,SUM(ss.tier) AS sum_tier
                from section_support_paypements ss
                JOIN support s ON s.id = ss.support_id
                WHERE ss.section_id = :section_id
                AND ss.yearmonth = :yearmonth
                GROUP BY s.member_id
                order BY SUM(ss.tier) DESC, active_time_max desc                                       
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('section_id' => $section_id, 'yearmonth' => $yearmonth))->fetchAll();
        $cache->save($result, $cacheName, array(), 300);

        return $result;
    }

    public function getNewActivePlingProduct($limit = 20)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit);

        if (false !== ($newSupporters = $cache->load($cacheName))) {
            return $newSupporters;
        }

        $sql = '  
                        select 
                        pl.member_id as pling_member_id
                        ,pl.project_id                        
                        ,p.title
                        ,p.image_small
                        ,p.laplace_score
                        ,p.count_likes
                        ,p.count_dislikes   
                        ,p.member_id 
                        ,p.profile_image_url
                        ,p.username
                        ,p.cat_title as catTitle
                        ,(
                            select max(created_at) from project_plings pt where pt.member_id = pl.member_id and pt.project_id=pl.project_id
                        ) as created_at
                        ,(select count(1) from project_plings pl2 where pl2.project_id = p.project_id and pl2.is_active = 1 and pl2.is_deleted = 0  ) as sum_plings
                        from project_plings pl
                        inner join stat_projects p on pl.project_id = p.project_id and p.status=100                   
                        where pl.is_deleted = 0 and pl.is_active = 1 
                        order by created_at desc                                                  
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array())->fetchAll();

        $cache->save($result, $cacheName, array(), 300);

        return $result;
    }


    public function getJsonNewActivePlingProduct($limit = 20,$offset=null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit).md5((int)$offset);;

        if (false !== ($newSupporters = $cache->load($cacheName))) {
            return $newSupporters;
        }

        $sql = '  
                        select 
                        pl.member_id as pling_member_id
                        ,pl.project_id                        
                        ,p.title
                        ,p.image_small
                        ,p.laplace_score
                        ,p.count_likes
                        ,p.count_dislikes   
                        ,p.member_id 
                        ,p.description
                        ,p.profile_image_url
                        ,p.username
                        ,p.cat_title 
                        ,p.count_comments
                        ,(
                            select max(created_at) from project_plings pt where pt.member_id = pl.member_id and pt.project_id=pl.project_id
                        ) as pling_created_at
                        ,(select count(1) from project_plings pl2 where pl2.project_id = p.project_id and pl2.is_active = 1 and pl2.is_deleted = 0  ) as sum_plings
                        ,p.project_changed_at as changed_at
                        ,p.project_created_at as created_at
                        from project_plings pl
                        inner join stat_projects p on pl.project_id = p.project_id and p.status > 30                        
                        where pl.is_deleted = 0 and pl.is_active = 1 
                        order by pling_created_at desc                                                  
        ';
         if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }
        $resultSet = Zend_Db_Table::getDefaultAdapter()->query($sql, array())->fetchAll();

        $imagehelper = new Default_View_Helper_Image();
        foreach ($resultSet as &$value) {
            $value['image_small'] = $imagehelper->Image($value['image_small'], array('width' => 200, 'height' => 200));
        }

        $result = Zend_Json::encode($resultSet);
        $cache->save($result, $cacheName, array(), 300);

        return $result;
    }

     /**
     * @param int $limit
     *
     * @return array|false|mixed
     */
    public function getTopScoreUsers($limit = 120,$offset=null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit).md5((int)$offset);;

        if (false !== ($resultMembers = $cache->load($cacheName))) {
            return $resultMembers;
        }

        $sql = '
                    select  
                    s.*
                    ,m.profile_image_url
                    ,m.username
                    from member_score s
                    inner join member m on s.member_id = m.member_id
                    order by s.score desc             
                ';

        
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }
        
        $resultMembers = Zend_Db_Table::getDefaultAdapter()->query($sql, array())->fetchAll();

        $cache->save($resultMembers, $cacheName, array(), 300);

        return $resultMembers;
    }

    public function getMostPlingedProductsTotalCnt(){
        $sql = '
            select count(1) as total_count
            from
            (
                select distinct pl.project_id 
                from project_plings pl
                inner join stat_projects p on pl.project_id = p.project_id and p.status = 100
                where pl.is_deleted = 0 and pl.is_active = 1 
            ) t
        ';
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array())->fetchAll();
        $totalcnt = $result[0]['total_count'];
        return $totalcnt;
    }

    public function getMostPlingedProducts($limit = 20,$offset = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit).md5((int)$offset);

        if (false !== ($newSupporters = $cache->load($cacheName))) {
            return $newSupporters;
        }

        $sql = '  
                        select pl.project_id
                        ,count(1) as sum_plings 
                        ,p.title
                        ,p.image_small
                        ,p.laplace_score
                        ,p.count_likes
                        ,p.count_dislikes   
                        ,p.member_id 
                        ,p.profile_image_url
                        ,p.username
                        ,p.cat_title as catTitle
                        ,p.project_changed_at
                        ,p.version
                        ,p.description
                        ,p.package_names
                        ,p.count_comments
                        from project_plings pl
                        inner join stat_projects p on pl.project_id = p.project_id and p.status = 100
                        where pl.is_deleted = 0 and pl.is_active = 1 
                        group by pl.project_id
                        order by sum_plings desc 
                                                              
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array())->fetchAll();

        $cache->save($result, $cacheName, array(), 300);

        return $result;
    }

    public function getMostPlingedProductsForUser($member_id, $limit = 20,$offset = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' .md5($member_id).md5((int)$limit).md5((int)$offset);

        if (false !== ($newSupporters = $cache->load($cacheName))) {
            return $newSupporters;
        }

        $sql = '  
                        select pl.project_id
                        ,count(1) as sum_plings 
                        ,p.title
                        ,p.image_small                        
                        ,p.cat_title as catTitle
                        ,p.project_changed_at                        
                        from project_plings pl
                        inner join stat_projects p on pl.project_id = p.project_id and p.status = 100
                        where pl.is_deleted = 0 and pl.is_active = 1 and p.member_id = :member_id
                        group by pl.project_id
                        order by sum_plings desc 
                                                              
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('member_id' => $member_id))->fetchAll();

        $cache->save($result, $cacheName, array(), 300);

        return $result;
    }


    public function getMostPlingedCreatorsTotalCnt(){
        $sql = '
            select count(1) as total_count
            from
            (
                select distinct p.member_id
                from stat_projects p
                join project_plings pl on p.project_id = pl.project_id                       
                where p.status = 100 and pl.is_deleted = 0 and pl.is_active = 1 
            ) t
        ';
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array())->fetchAll();
        $totalcnt = $result[0]['total_count'];
        return $totalcnt;
    } 
    public function getMostPlingedCreators($limit = 20,$offset = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit).md5((int)$offset);

        if (false !== ($newSupporters = $cache->load($cacheName))) {
            return $newSupporters;
        }

        $sql = '  
                       select p.member_id,
                        count(1) as cnt,
                        m.username,
                        m.profile_image_url,
                        m.created_at
                        from stat_projects p
                        join project_plings pl on p.project_id = pl.project_id
                        join member m on p.member_id = m.member_id
                        where p.status = 100
                        and pl.is_deleted = 0 and pl.is_active = 1 
                        group by p.member_id
                        order by cnt desc                        
                                                              
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array())->fetchAll();

        $cache->save($result, $cacheName, array(), 300);

        return $result;
    }

    public function getCountActiveSupporters()
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__;

        if (false !== ($totalcnt = $cache->load($cacheName))) {
            return $totalcnt;
        }
        /*$sql = '
                        SELECT 
                        count( distinct s.member_id) as total_count
                        from support s                         
                        where s.status_id = 2  
                        and (DATE_ADD((s.active_time), INTERVAL 1 YEAR) > now())
        ';*/

        $sql = '
                    SELECT 
                    count( distinct s.member_id) as total_count
                    from v_support s                         
                    where is_valid = 1
        ';
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array())->fetchAll();
        $totalcnt = $result[0]['total_count'];
        $cache->save($totalcnt, $cacheName, array(), 300);

        return $totalcnt;
    }

    public function getCountAllSupporters()
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__;

        if (false !== ($totalcnt = $cache->load($cacheName))) {
            return $totalcnt;
        }
        $sql = '
                        SELECT 
                        count( distinct s.member_id) as total_count
                        from v_support s                         
                                          
        ';

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array())->fetchAll();
        $totalcnt = $result[0]['total_count'];
        $cache->save($totalcnt, $cacheName, array(), 300);

        return $totalcnt;
    }

    public function getCountTierSupporters($tier)
    {        
        $sql = "
                select count(1) as cnt from
                    (
                    select 
                    member_id,
                    max(amount),
                    tier
                    from support
                    where status_id = 2
                    group by member_id
                    ) t where tier = :tier

        ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('tier' =>$tier))->fetchAll();
        return $result[0]['cnt'];
    }
    
    public function getCountSectionSupporters($section_id)
    {        
        $sql = "
                select count(1) as cnt FROM
                    section_support
                    JOIN support ON support.id = section_support.support_id
                    where support.status_id = 2
                    AND support.type_id = 1
                    AND section_support.section_id = :section_id
        ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('section_id' =>$section_id))->fetchAll();
        return $result[0]['cnt'];
    }
    
    
    public function getCountSupportedMonthsForSectionAndMember($section_id, $member_id)
    {        
        $sql = "
                SELECT COUNT(1) AS num_months FROM
                (
                SELECT s.member_id, p.yearmonth FROM section_support_paypements p
                JOIN support s ON s.id = p.support_id
                WHERE member_id = :member_id
                AND p.section_id = :section_id
                GROUP BY s.member_id, p.yearmonth
                ) A
        ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('member_id' => $member_id, 'section_id' =>$section_id))->fetchRow();
        return $result['num_months'];
    }
    
    public function getSumSupporting()
    {        
        $sql = "
                SELECT SUM(s.tier) as sum_tier FROM v_support v
                JOIN support s ON s.member_id = v.member_id AND s.active_time = v.active_time_max
                WHERE v.is_valid = 1
        ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
        return $result[0]['sum_tier'];
    }
    
    public function getSumPayoutForMonth($yearmonth)
    {        
        $sql = "
                SELECT SUM(p.probably_payout_amount) AS sum_payout FROM member_dl_plings p
                WHERE p.yearmonth = :yearmonth
                AND p.paypal_mail IS NOT null
        ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('yearmonth' => $yearmonth))->fetchAll();
        return $result[0]['sum_tier'];
    }

    public function getModeratorsList()
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__;

        if (false !== ($newMembers = $cache->load($cacheName))) {
            return $newMembers;
        }

        $sql = '
                SELECT 
                member_id,
                profile_image_url,
                username,
                created_at
                FROM member
                WHERE `is_active` = :activeVal
                AND `type` = :typeVal     
                and `roleid` = :roleid
                ORDER BY created_at DESC             
            ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultMembers = Zend_Db_Table::getDefaultAdapter()->query($sql, array(
            'activeVal' => Default_Model_Member::MEMBER_ACTIVE,
            'typeVal'   => Default_Model_Member::MEMBER_TYPE_PERSON,
            'roleid'    => Default_Model_DbTable_Member::ROLE_ID_MODERATOR
        ))->fetchAll()
        ;

        $cache->save($resultMembers, $cacheName, array(), 300);

        return $resultMembers;
    }


    public function getCountMembers()
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__;

        if (false !== ($totalcnt = $cache->load($cacheName))) {
            return $totalcnt;
        }
        $sql = "
                        SELECT
                            count(1) AS total_count
                        FROM
                            member
                        WHERE
                            is_active=1 AND is_deleted=0
                       ";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array())->fetchAll();
        $totalcnt = $result[0]['total_count'];
        $cache->save($totalcnt, $cacheName, array(), 300);

        return $totalcnt;
    }

    public function getTooptipForMember($member_id)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5($member_id);

        if (false !== ($tooptip = $cache->load($cacheName))) {
            return $tooptip;
        }

        $modelMember = new Default_Model_Member();
        $tblFollower = new Default_Model_DbTable_ProjectFollower();
        $modelProject = new Default_Model_Project();
        $printDate = new Default_View_Helper_PrintDate();
        $printDateSince = new Default_View_Helper_PrintDateSince();

        $cnt = $modelMember->fetchCommentsCount($member_id);
        $cntLikesGave = $tblFollower->countLikesHeGave($member_id);
        $cntLikesGot = $tblFollower->countLikesHeGot($member_id);
        $donationinfo = $modelMember->fetchSupporterDonationInfo($member_id);
        $lastactive = $modelMember->fetchLastActiveTime($member_id);
        $cntprojects = $modelProject->countAllProjectsForMember($member_id, true);

        $member = $modelMember->find($member_id)->current();
        $textCountryCity = $member->city;
        $textCountryCity .= $member->country ? ', ' . $member->country : '';

        $data = array(
            'totalComments' => $cnt,
            'created_at'    => $printDateSince->printDateSince($member->created_at),
            'username'      => $member->username,
            'countrycity'   => $textCountryCity,
            'lastactive_at' => $printDateSince->printDateSince($lastactive),
            'cntProjects'   => $cntprojects,
            'issupporter'   => $donationinfo['issupporter'],
            'supportMax'    => $donationinfo['active_time_max'],
            'supportMin'    => $donationinfo['active_time_min'],
            'supportCnt'    => $donationinfo['cnt'],
            'cntLikesGave'  => $cntLikesGave,
            'cntLikesGot'   => $cntLikesGot
        );

        $cache->save($data, $cacheName, array(), 3600);

        return $data;
    }


    public function getProbablyPayoutPlingsCurrentmonth($project_id)
    {
        $sql =
            " select FORMAT(probably_payout_amount, 2) as amount from member_dl_plings where project_id = :project_id and yearmonth=(DATE_FORMAT(NOW(),'%Y%m'))";
        $result = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql, array('project_id' => $project_id));

        return $result['amount'];
    }

    public function getOCSInstallInstruction()
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__;

        if (false !== ($instruction = $cache->load($cacheName))) {
            return $instruction;
        }
        $config = Zend_Registry::get('config')->settings->server->opencode;
        $readme = 'https://opencode.net/OCS/ocs-url/raw/master/docs/How-to-install.md?inline=false';

        $httpClient = new Zend_Http_Client($readme, array('keepalive' => true, 'strictredirects' => true));
        $httpClient->resetParameters();
        $httpClient->setUri($readme);
        $httpClient->setHeaders('Private-Token', $config->private_token);
        $httpClient->setHeaders('Sudo', $config->user_sudo);
        $httpClient->setHeaders('User-Agent', $config->user_agent);
        $httpClient->setMethod(Zend_Http_Client::GET);

        $response = $httpClient->request();

        $body = $response->getRawBody();

        if (count($body) == 0) {
            return array();
        }
        include_once('Parsedown.php');
        $Parsedown = new Parsedown();

        $readmetext = $Parsedown->text($body);

        $cache->save($readmetext, $cacheName, array(), 3600);

        return $readmetext;
    }

    public function getDiscussionOpendeskop($member_id)
    {
        $sql = "
                select 
                 c.comment_id
                ,c.comment_text
                ,c.comment_member_id
                ,c.comment_created_at
                ,m.username          
                ,p.project_id
                ,p.title
                ,cp.comment_member_id p_comment_member_id
                ,(select username from member m where m.member_id = cp.comment_member_id) p_username
                from comments c 
                inner join project p on c.comment_target_id = p.project_id and p.status = 100
                inner join  member m ON c.comment_member_id = m.member_id
                left join comments cp on c.comment_parent_id = cp.comment_id
                where c.comment_type = 0 and c.comment_active = 1
                and c.comment_member_id = :member_id
                ORDER BY c.comment_created_at DESC
                limit 10
                ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('member_id' => $member_id))->fetchAll();
        return $result;
    }
}