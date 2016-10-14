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


    public function getLatestProductsForAllStores($limit = 10)
    {
        $activeCategories = $this->getActiveCategoriesForAllStores();

        $sql = '
            SELECT 
                *
            FROM
                project
            WHERE
                project.status = 100
                    AND project.project_category_id IN (' .
            implode(',', $activeCategories)
            . ')
            ORDER BY project.created_at DESC
            ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);

        if (count($resultSet) > 0) {
            return $resultSet;
        } else {
            return array();
        }
    }

    public function getActiveCategoriesForAllStores($limit = null)
    {
        $sql = '
        SELECT DISTINCT
            config_store_category.project_category_id
        FROM
            config_store
        JOIN
            config_store_category ON config_store.store_id = config_store_category.store_id
        JOIN
            project_category ON config_store_category.project_category_id = project_category.project_category_id
        WHERE project_category.is_active = 1
        ORDER BY config_store_category.`order`;
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
                    image_small
                    ,project_id
                    ,title
                FROM
                    project
                WHERE
                    project.image_small is not null 
                    and project.status = 100
                        AND project.project_category_id IN (' .
                implode(',', $activeCategories)
                . ')
                ORDER BY ifnull(project.changed_at, project.created_at) DESC
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

    public function getLatestCommentsForAllStores($limit = 10)
    {
        $activeCategories = $this->getActiveCategoriesForAllStores();

        $sql = '
            SELECT *
            FROM comments
            JOIN project ON comments.comment_target_id = project.project_id AND comments.comment_type = 0
            WHERE comments.comment_active = 1
            AND project.is_active = 1            
            AND project.project_category_id IN (' .
            implode(',', $activeCategories)
            . ')
            ORDER BY comments.comment_created_at DESC
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);

        if (count($resultSet) > 0) {
            return $resultSet;
        } else {
            return array();
        }
    }

    public function getLatestPlingsForAllStores($limit = 10)
    {
        $activeCategories = $this->getActiveCategoriesForAllStores();

        $sql = '
        SELECT *
        FROM plings
        JOIN project ON project.project_id = plings.project_id
        JOIN comments ON comments.comment_target_id = plings.project_id
        WHERE 
        plings.status_id = 2
         AND
        plings.project_id IN (' .
            implode(',', $activeCategories)
            . ')
        ORDER BY plings.create_time DESC
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);

        if (count($resultSet) > 0) {
            return $resultSet;
        } else {
            return array();
        }
    }

    /**
     * if category id not set the latest comments for all categories on the current host wil be returned.
     *
     * @param int $limit
     * @param int|null $project_category_id
     * @return array
     */
    public function getLatestComments($limit = 5, $project_category_id = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit . (int)$project_category_id);

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
                comment_id
                ,comment_text
                ,member.member_id
                ,profile_image_url
                ,comment_created_at
                ,username
                ,comment_target_id
                ,title
                ,project_id               
            FROM comments
            STRAIGHT_JOIN member on comments.comment_member_id = member.member_id
            JOIN project ON comments.comment_target_id = project.project_id AND comments.comment_type = 0
            WHERE comments.comment_active = 1            
            AND project.status = 100
            AND project.type_id = 1
            AND project.project_category_id IN (' .
            implode(',', $activeCategories)
            . ')              
            ORDER BY comments.comment_created_at DESC
        ';

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
     * if category id not set the latest plings for all categories on the current host wil be returned.
     *
     * @param int $limit
     * @param null $project_category_id
     * @return array|false|mixed
     */
    public function getLatestPlings($limit = 5, $project_category_id = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit . (int)$project_category_id);

        if (($latestPlings = $cache->load($cacheName))) {
            return $latestPlings;
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
        STRAIGHT_JOIN member on plings.member_id = member.member_id     
        WHERE 
        plings.status_id = 2        
        AND project.status <> 30
        AND project.project_category_id IN (' .
            implode(',', $activeCategories)
            . ')

        ORDER BY plings.create_time DESC
        ';

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
     * if category id not set the most downloaded products for all categories on the current host wil be returned.
     *
     * @param int $limit
     * @param null $project_category_id
     * @return array|false|mixed
     */
    public function getMostDownloaded($limit = 100, $project_category_id = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit . (int)$project_category_id);

        if (($mostDownloaded = $cache->load($cacheName))) {
            return $mostDownloaded;
        }

        if (empty($project_category_id)) {
            $activeCategories = $this->getActiveCategoriesForCurrentHost(self::WALLPAPERCATEGORYID);
        } else {
            $activeCategories = $this->getActiveCategoriesForCatId($project_category_id, self::WALLPAPERCATEGORYID);
        }

        if (count($activeCategories) == 0) {
            return array();
        }

        $sql = '
            SELECT 
                p.*
                ,(round(((p.count_likes + 6) / ((p.count_likes + p.count_dislikes) + 12)),2) * 100) as laplace_score
                ,s.amount 
                ,s.category_title       
                from stat_downloads_half_year s    
                inner join project p on s.project_id = p.project_id
                WHERE
                    p.status=100
                    and 
                    p.project_category_id IN (' .
            implode(',', $activeCategories)
            . ')
            ORDER BY s.amount DESC
            ';

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
     * @param int $omitCategoryId
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

//        if ($omitCategoryId == self::WALLPAPERCATEGORYID) {
//            return $this->getActiveCategoriesForHostStores_includeWallpaper($omitCategoryId);
//        } else {
//            $total = $this->getActiveCategoriesForHostStores_includeWallpaper($omitCategoryId);
//            $wallp = $this->getActiveCategoriesForHostStores_includeWallpaper(self::WALLPAPERCATEGORYID);
//            $result = array_diff($total, $wallp);
//            return $result;
//        }
    }


    /**
     * @param int $project_category_id
     * @param int|null $omitCategoryId
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

    public function getLastProductsForHostStores($limit = 10, $project_category_id = null)
    {
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
                p.*
                ,(round(((p.count_likes + 6) / ((p.count_likes + p.count_dislikes) + 12)),2) * 100) as laplace_score
                ,sp.count_plingers
            FROM
                project as p
                LEFT JOIN stat_plings as sp ON p.project_id = sp.project_id
            WHERE
                p.status = 100
                    AND p.project_category_id IN (' .
            implode(',', $activeCategories)
            . ')
            ORDER BY IFNULL(p.changed_at,p.created_at)  DESC
            ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);

        if (count($resultSet) > 0) {
            return $resultSet;
        } else {
            return array();
        }
    }

    /**
     * @param int $limit
     * @param null $project_category_id
     * @return array|Zend_Paginator
     */
    public function getFeaturedProductsForHostStores($limit = 10, $project_category_id = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit . (int)$project_category_id);

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
                p.*
                ,(round(((p.count_likes + 6) / ((p.count_likes + p.count_dislikes) + 12)),2) * 100) as laplace_score
                ,sp.count_plingers
                ,(select profile_image_url from member m where m.member_id = p.member_id) as profile_image_url
                ,(select username from member m where m.member_id = p.member_id) as username
            FROM
                project as p
                LEFT JOIN stat_plings as sp ON p.project_id = sp.project_id
            WHERE
                p.status = 100
                and p.type_id = 1
                and p.featured = 1
                    AND p.project_category_id IN (' .
            implode(',', $activeCategories)
            . ')
            ORDER BY p.changed_at DESC
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




    public function getActiveUsersForHostStores($limit = 100, $project_category_id = null)
    {
        if (empty($project_category_id)) {
            $activeCategories = $this->getActiveCategoriesForCurrentHost();
        } else {
            $activeCategories = $this->getActiveCategoriesForCatId($project_category_id);
        }

        if (count($activeCategories) == 0) {
            return array();
        }

        $sql = '
                select member_id, count(1) cnt
                ,(select profile_image_url from member m where m.member_id = p.member_id) as profile_image_url
                ,(select username from member m where m.member_id = p.member_id) as username
                 from project p
                where 
                p.type_id = 1 
                and p.status = 100 
                and p.ppload_collection_id is not null
                AND p.project_category_id IN (' .
            implode(',', $activeCategories)
            . ')   
                group by member_id
                order by cnt desc                
                ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }


        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);

        if (count($resultSet) > 0) {
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array($resultSet));
        } else {
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array(array()));
        }
    }

    public function getMostPageviewsForHostStores($limit = 10, $project_category_id = null)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit . (int)$project_category_id);

        if (false !== ($resultSet = $cache->load($cacheName))) {
            return $resultSet;
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
                    select t.cnt, pt.*                                        
                    from
                    (
                    select s.project_id, count(s.project_id) cnt,
                    (select p.project_category_id from project p where p.project_id = s.project_id) gid
                    from stat_page_views s
                    group by s.project_id
                    ) t
                    join project pt on pt.project_id = t.project_id

                    where t.gid IN (' .
            implode(',', $activeCategories)
            . ')         
                    order by t.cnt desc                    
        ';

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

    public function getLastCommentsForUsersProjects($member_id, $limit = 10)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$member_id . (int)$limit);

        if (false !== ($resultSet = $cache->load($cacheName))) {
            return $resultSet;
        }

        $sql = '
                SELECT 
                comment_id
                ,comment_text
                , member.member_id
                ,comment_created_at
                ,username            
                ,title
                ,project_id
                FROM comments           
                JOIN project ON comments.comment_target_id = project.project_id AND comments.comment_type = 0
                STRAIGHT_JOIN member on comments.comment_member_id = member.member_id
                WHERE comments.comment_active = 1
                AND project.is_active = 1 and project.status <> 30
                and project.member_id =:member_id
                ORDER BY comments.comment_created_at DESC               
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

    public function getLastVotesForUsersProjects($member_id, $limit = 10)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$member_id . (int)$limit);

        if (false !== ($resultSet = $cache->load($cacheName))) {
            return $resultSet;
        }

        $sql = '
                SELECT 
                rating_id                
                , member.member_id                
                ,username            
                ,user_like
                ,user_dislike
                ,project_rating.project_id
                ,project_rating.created_at
                ,project.title
                FROM project_rating           
                JOIN project ON project_rating.project_id = project.project_id 
                STRAIGHT_JOIN member on project_rating.member_id = member.member_id
                WHERE project.is_active = 1 and project.status <> 30
                and project.member_id = :member_id
                ORDER BY rating_id DESC               
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('member_id' => $member_id));

        if (count($resultSet) > 0) {
            $cache->save($resultSet, $cacheName, array(), 300);
            return $resultSet;
        } else {
            $cache->save( array(), $cacheName, array(), 300);
            return array();
        }
    }


    public function getLastDonationsForUsersProjects($member_id, $limit = 10)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((int)$member_id . (int)$limit);

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
                STRAIGHT_JOIN member on plings.member_id = member.member_id     
                WHERE 
                plings.status_id = 2        
                AND project.is_active = 1
                and project.status=100
                and project.member_id = :member_id
                order by create_time desc
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
                SELECT *
                FROM member
                WHERE `is_active` = :activeVal
                   AND `type` = :typeVal     
                  order by created_at desc             
            ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultMembers = Zend_Db_Table::getDefaultAdapter()->query($sql, array(
            'activeVal' => Default_Model_Member::MEMBER_ACTIVE,
            'typeVal' => Default_Model_Member::MEMBER_TYPE_PERSON
        ))->fetchAll();

        $cache->save($resultMembers, $cacheName, array(), 300);
        return $resultMembers;
    }

}