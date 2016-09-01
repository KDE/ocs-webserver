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


    public function getLastProductsForAllStores($limit = 10)
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

    public function getLastCommentsForAllStores($limit = 10)
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

    public function getLastPlingsForAllStores($limit = 10)
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


    public function getLastPlingsForHostStores($limit = 5, $project_category_id = null)
    {
        $activeCategories = $this->getActiveCategoriesForHostStores($project_category_id);
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
            return $resultSet;
        } else {
            return array();
        }
    }

    public function getLastPlingsForHostStores_local($limit = 5, $project_category_id = null)
    {
       
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



    public function getActiveCategoriesForHostStores($project_category_id)
    {        
        if($project_category_id == self::WALLPAPERCATEGORYID)
        {
            return $this->getActiveCategoriesForHostStores_includeWallpaper($project_category_id);
        }else
        {
            $total = $this->getActiveCategoriesForHostStores_includeWallpaper($project_category_id);
            $wallp = $this->getActiveCategoriesForHostStores_includeWallpaper(self::WALLPAPERCATEGORYID);
            $result = array_diff($total, $wallp);
            return $result;
        }
    }


    public function getActiveCategoriesForHostStores_includeWallpaper($project_category_id)
    {

        $cache = Zend_Registry::get('cache');

        $host = Zend_Registry::get('store_host');

        if (is_array($project_category_id)) {
            $project_category_id = null;
        }

        if ($project_category_id != null) {
            $cacheName = __FUNCTION__ . md5('getActiveCategoriesForHostStores_includeWallpaper' . $host . $project_category_id);
        } else {
            $cacheName = __FUNCTION__ . md5('getActiveCategoriesForHostStores_includeWallpaper' . $host);
        }


        $resultSet = $cache->load($cacheName);

        if ($resultSet) {
            return $resultSet;
        } else {

            if ($project_category_id == null) {

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
                and config_store.host = "' . $host . '"
                ORDER BY config_store_category.`order`;
                ';


                $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);

                $values = array_map(function ($row) {
                    return $row['project_category_id'];
                }, $resultSet);


            } else {


                $resultSet = array(
                    array("project_category_id" => $project_category_id)
                );

                $values = array(
                    $project_category_id
                );

            }

            $helperFetchChildren = new Default_Model_DbTable_ProjectCategory();
            foreach ($resultSet as $row) {
                $children = $helperFetchChildren->fetchSubCatIds($row['project_category_id']);
                if (count($children) > 0) {
                    $values = array_merge($values, $children);
                }
            }


            if (count($values) > 0) {
                $cache->save($values, $cacheName, array(), 14400);
                return $values;
            } else {
                return array();
            }


        }
    }

    public function getLastProductsForHostStores($limit = 10, $project_category_id = null)
    {
        $activeCategories = $this->getActiveCategoriesForHostStores($project_category_id);

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
            ORDER BY p.changed_at DESC
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

    public function getLastProductsForHostStores_local($limit = 10, $project_category_id = null)
    {
        

       
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
            ORDER BY p.changed_at DESC
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

    public function getFeaturedProductsForHostStores($limit = 10, $project_category_id = null)
    {
        $activeCategories = $this->getActiveCategoriesForHostStores($project_category_id);

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

        if (count($resultSet) > 0) {
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array($resultSet));
        } else {
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array(array()));
        }
    }

    public function getFeaturedProductsForHostStores_local($limit = 10, $project_category_id = null)
    {
        

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
               
            ORDER BY p.changed_at DESC
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



    public function getMostDownloadedForHostStores($limit = 100, $project_category_id = null)
    {

        
        $activeCategories = $this->getActiveCategoriesForHostStores($project_category_id);
        
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
            return $resultSet;
        } else {
            return array();
        }
    }


    public function getLastCommentsForHostStores($limit = 5, $project_category_id = null)
    {

        $activeCategories = $this->getActiveCategoriesForHostStores($project_category_id);

        if (count($activeCategories) == 0) {
            return array();
        }
        $sql = '
            SELECT
                comment_id
                ,comment_text
                , member.member_id
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
            AND project.status <> 30
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
            return $resultSet;
        } else {
            return array();
        }
    }

     public function getLastCommentsForHostStores_local($limit = 5, $project_category_id = null)
    {

        $sql = '
            SELECT
                comment_id
                ,comment_text
                , member.member_id
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

    public function getActivUsersForHostStores($limit = 100, $project_category_id = null)
    {
        $activeCategories = $this->getActiveCategoriesForHostStores($project_category_id);
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

        $activeCategories = $this->getActiveCategoriesForHostStores($project_category_id);

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
            return $resultSet;
        } else {
            return array();
        }
    }

    public function getLastCommentsForUsersProjects($member_id, $limit = 10)
    {

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
            return $resultSet;
        } else {
            return array();
        }
    }

    public function getLastVotesForUsersProjects($member_id, $limit = 10)
    {

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
            return $resultSet;
        } else {
            return array();
        }
    }


    public function getLastDonationsForUsersProjects($member_id, $limit = 10)
    {
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
                and project.status<>30
                and project.member_id = :member_id
                order by create_time desc
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('member_id' => $member_id));

        if (count($resultSet) > 0) {
            return $resultSet;
        } else {
            return array();
        }
    }


    public function getNewActiveMembers($limit = 20)
    {
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

        return $resultMembers;


    }

 public function getNewActiveMembers_local($limit = 20)
    {
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

        return $resultMembers;


    }
}