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

use Application\Model\Repository\ConfigStoreRepository;
use Application\Model\Repository\MemberRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Repository\ProjectFollowerRepository;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Service\Interfaces\InfoServiceInterface;
use Application\View\Helper\Image;
use Application\View\Helper\PrintDateSince;
use Exception;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Client;
use Laminas\Json\Encoder;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Library\Parsedown;

class InfoService extends BaseService implements InfoServiceInterface
{
    const WALLPAPERCATEGORYID = '295';
    const TAG_ISORIGINAL = 'original-product';
    /**
     * @var MemberService
     */
    protected $memberService;
    private $cache;
    private $config;
    private $projectRepository;
    private $configStoreRepository;
    private $store;
    /**
     * @var AdapterInterface
     */
    private $db;

    public function __construct(
        AdapterInterface $db,
        array $config,
        MemberService $memberService
    ) {
        $this->db = $db;
        $this->config = $config;
        $this->cache = $GLOBALS['ocs_cache'];
        $this->projectRepository = new ProjectRepository($this->db);
        $this->configStoreRepository = new ConfigStoreRepository($this->db, $this->cache);
        $this->store = $GLOBALS['ocs_store'];
        $this->memberService = $memberService;
    }

    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getLast200ImgsProductsForAllStores($limit = 200)
    {

        $cache = $this->cache;
        $cacheName = __FUNCTION__ . md5('getLast200ImgsProductsForAllStores' . $limit);
        if ($resultSet = $cache->getItem($cacheName)) {
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

            $resultSet = $this->projectRepository->fetchAll($sql);

            if (count($resultSet) > 0) {

                $cache->setItem($cacheName, $resultSet);

                return $resultSet;
            } else {
                return array();
            }
        }
    }

    /**
     *
     * @param int $limit
     *
     * @return array
     */
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
        ORDER BY `config_store_category`.`order`
        ';

        if (isset($limit) && $limit > 0) {
            $sql .= ' LIMIT ' . (int)$limit;
        }

        $resultSet = $this->configStoreRepository->fetchAll($sql);

        if (count($resultSet) > 0) {
            return array_map(
                function ($row) {
                    return $row['project_category_id'];
                }, $resultSet
            );
        } else {
            return array();
        }
    }

    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getActiveStoresForCrossDomainLogin($limit = null)
    {
        $sql = '
        SELECT DISTINCT
            `config_store`.`host`
        FROM
            `config_store`
        WHERE `config_store`.`cross_domain_login` = 1
        ORDER BY `config_store`.`order`
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = $this->configStoreRepository->fetchAll($sql);

        if (count($resultSet) > 0) {
            return array_map(
                function ($row) {
                    return $row['host'];
                }, $resultSet
            );
        } else {
            return array();
        }
    }

    /**
     *
     * @return int
     */
    public function countTotalActiveMembers()
    {

        $cacheName = __FUNCTION__ . md5('countTotalActiveMembers');
        $cache = $this->cache;

        $result = $cache->getItem($cacheName);

        if ($result) {
            return (int)$result['count_active_members'];
        }

        $sql = "SELECT count(1) AS `count_active_members` FROM (                    
                    SELECT count(1) AS `count_active_projects` FROM `project` `p`
                    WHERE `p`.`status` = 100
                    AND `p`.`type_id` = 1
                    GROUP BY `p`.`member_id`
                ) AS `A`;";

        $result = $resultSet = $this->projectRepository->fetchRow($sql);
        $cache->setItem($cacheName, $result);

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
     */
    public function getLatestComments($limit = 5, $project_category_id = null, $tags = null)
    {
        $cache = $this->cache;
        $cacheNameTags = is_array($tags) ? implode('_', $tags) : '';
        $cacheName = __FUNCTION__ . '_new_' . md5($this->store->config->store_id . (int)$limit . (int)$project_category_id . $cacheNameTags);

        if (($latestComments = $cache->getItem($cacheName))) {
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
                       ,`project_category_id` AS `project_category_id`   
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

            if (!is_array($tagList)) {
                $tagList = array($tagList);
            }

            foreach ($tagList as $item) {
                #and
                $sql .= ' find_in_set(' . $item . ', tag_ids) AND ';
            }
            $sql .= ' 1=1)';
        }

        $sql .= '  ORDER BY comments.comment_created_at DESC ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = $this->projectRepository->fetchAll($sql);

        if (count($resultSet) > 0) {
            $cache->setItem($cacheName, $resultSet);

            return $resultSet;
        } else {
            $cache->setItem($cacheName, $resultSet);

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
        $currentHostMainCategories = $GLOBALS['ocs_store_category_list'];


        $modelCategory = new ProjectCategoryRepository($this->db, $this->cache);
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
        $modelCategory = new ProjectCategoryRepository($this->db, $this->cache);
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
     * @throws Exception
     */
    public function getMostDownloaded($limit = 100, $project_category_id = null, $tags = null)
    {
        $cache = $this->cache;

        if (null != $tags) {
            $cacheNameTags = is_array($tags) ? implode('_', $tags) : '';
        } else {
            $cacheNameTags = "";
        }
        if (null != $project_category_id) {
            $cacheNameCat = $project_category_id;
        } else {
            $cacheNameCat = "";
        }
        $cacheName = __FUNCTION__ . '_new_' . md5($this->store->config->store_id . (int)$limit . $cacheNameCat . $cacheNameTags);

        if (($mostDownloaded = $cache->getItem($cacheName))) {
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
                ,`p`.`project_category_id`
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

            if (!is_array($tagList)) {
                $tagList = array($tagList);
            }

            foreach ($tagList as $item) {
                #and
                $sql .= ' find_in_set(' . $item . ', tag_ids) AND ';
            }
            $sql .= ' 1=1)';
        }

        $sql .= '  ORDER BY s.amount DESC ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = $this->projectRepository->fetchAll($sql);

        if (count($resultSet) > 0) {
            $cache->setItem($cacheName, $resultSet);

            return $resultSet;
        } else {
            $cache->setItem($cacheName, $resultSet);

            return array();
        }
    }


    /**
     * @param int $project_id
     * 
     * @return array [project_category_id, position]
     */    
    public function findProductPostion($project_id){
        $project_id = (int)$project_id;
        $productInfo = $this->projectRepository->fetchProductInfo($project_id);
        $catId =  $productInfo['project_category_id']; 
        $modelCategory = new ProjectCategoryRepository($this->db, $this->cache);    
        $cat = $modelCategory->findCategory($catId); 
        $sql = 'select * from stat_downloads_quarter_year where project_id = :project_id';
        $amountArray =  $this->projectRepository->fetchRow($sql, array('project_id' => $project_id));        
        $text = '';
        if($amountArray && $amountArray['amount'])
        {
            $sqlRank = '
                                select count(1) as rk from (
                                select s.*,lft, rgt from stat_downloads_quarter_year s
                                inner join project_category p on s.project_category_id = p.project_category_id
                                where lft>='.$cat->lft.' and rgt<='.$cat->rgt.' and amount >= '.(int)$amountArray['amount'] .'
                                group by amount ) t
                            ';        
            $rankCat =  $this->projectRepository->fetchRow($sqlRank);

            $sqlRank = '
                select count(1) as rk from (
                select s.* from stat_downloads_quarter_year s                
                where amount >= '.(int)$amountArray['amount'] .'
                group by amount ) t
            ';        
            $rankTotal =  $this->projectRepository->fetchRow($sqlRank);
                  //  "No.6 of 42 in Inkscape Templates No.532 on Pling"
            $sql = 'select count_product from stat_store_prod_count c where project_category_id = :project_category_id  and stores is null';
            $prodCnt =  $this->projectRepository->fetchRow($sql, array('project_category_id' => $catId));            
            $text = 'Pling-Rank: No.'.$rankCat['rk'].' of '.$prodCnt['count_product'].' in '.$cat->title.'  &nbsp;&nbsp; No.'.$rankTotal['rk'].' on Pling.';
        }
        return $text;
    }

   

    // /**
    //  * @param int $project_id
    //  * 
    //  * @return array [project_category_id, position]
    //  */    
    // public function findProductPostion($project_id)
    // {
    //     $project_id = (int)$project_id;
    //     $productInfo = $this->projectRepository->fetchProductInfo($project_id);
    //     $catId =  $productInfo['project_category_id']; 
    
    //     $sql = 'SELECT ancestor_id_path,ancestor_path FROM stat_cat_tree where project_category_id = :project_category_id';
    //     $ancestor =  $this->projectRepository->fetchRow($sql, array('project_category_id' => $catId));
       
    //     $ancestorIdArray = explode(",", $ancestor['ancestor_id_path']);
    //     $ancestorArray = explode(" | ", $ancestor['ancestor_path']);
    //     // remove first root & reverse
    //     array_shift($ancestorIdArray);
    //     array_shift($ancestorArray);
    //     $ancestorIdArray = array_reverse($ancestorIdArray);
    //     $ancestorArray = array_reverse($ancestorArray);

    //     $cnt = 0;
    //     $modelCategory = new ProjectCategoryRepository($this->db, $this->cache);  
    //     $data = [];   
        
    //     foreach ($ancestorIdArray as $catid) {            
    //         $activeChildren = $modelCategory->fetchChildIds($catid);
    //         $activeChildrenString = implode(',', $activeChildren);
    //         if(sizeof($activeChildren)==0){
    //             $activeChildrenString = $catid;
    //         }
    //         $sqlRank = 'SELECT  FIND_IN_SET( project_id, (
    //             SELECT GROUP_CONCAT( project_id ORDER BY amount DESC ) 
    //             FROM stat_downloads_quarter_year where  project_category_id in (
    //                '.$activeChildrenString.'
    //                 )
    //             )
    //             ) AS rk
    //             from stat_downloads_quarter_year d where project_category_id in (
    //                 '.$activeChildrenString.'
    //             ) and d.project_id = '.$project_id;
            
    //         $rank =  $this->projectRepository->fetchRow($sqlRank);
    //         $data[]=['cat_id'=>$catid,'title'=>$ancestorArray[$cnt++],'rank'=>$rank['rk']];    
            
    //     }      
            
    //     return $data;
    // }    
    /**
     * @param int         $limit
     * @param int|null    $project_category_id
     * @param array|null  $tags
     * @param string|null $tag_isoriginal
     *
     * @return array|false
     * @throws Exception
     */
    public function getLastProductsForHostStores(
        $limit = 10,
        $project_category_id = null,
        $tags = null,
        $tag_isoriginal = null
    ) {
        $catids = "";
        if ($project_category_id) {
            $catids = str_replace(',', '', (string)$project_category_id);
        }

        $cache = $this->cache;
        $cacheNameTags = is_array($tags) ? implode('_', $tags) : '';
        $cacheName = __FUNCTION__ . '_' . md5($this->store->config->store_id . (int)$limit . $catids . $cacheNameTags . $tag_isoriginal);

        if (($resultSet = $cache->getItem($cacheName))) {
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

            if (!is_array($tagList)) {
                $tagList = array($tagList);
            }

            foreach ($tagList as $item) {
                #and
                $sql .= ' find_in_set(' . $item . ', tag_ids) AND ';
            }
            $sql .= ' 1=1)';
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

        $resultSet = $this->projectRepository->fetchAll($sql);
        $cache->setItem($cacheName, $resultSet);

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
     * @throws Exception
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
            $store_config = $this->store->config;
            $store_id = $store_config->store_id;
            $store_tags = isset($store_config->tags) ? $store_config->tags : array();

            if (empty($tags)) {
                $tags = $store_tags;
            } else {
                $tags = array_merge($tags, $store_tags);
            }
        }


        $cat_ids = "";
        if ($project_category_id) {
            $cat_ids = str_replace(',', '_', (string)$project_category_id);
        }

        $cache = $this->cache;
        $cacheNameTags = is_array($tags) ? implode('_', $tags) : '';
        $cacheName = __FUNCTION__ . '_' . md5($this->store->config->store_id . (int)$limit . $cat_ids . $cacheNameTags . $tag_isoriginal . $offset);

        if (($resultSet = $cache->getItem($cacheName))) {
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
                `count_plings`,
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

            if (!is_array($tagList)) {
                $tagList = array($tagList);
            }

            foreach ($tagList as $item) {
                #and
                $sql .= ' find_in_set(' . $item . ', tag_ids) AND ';
            }
            $sql .= ' 1=1)';
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

        $resultSet = $this->projectRepository->fetchAll($sql);
        $imagehelper = new Image();
        foreach ($resultSet as &$value) {
            $value['image_small'] = $imagehelper->Image($value['image_small'], array('width' => 200, 'height' => 200));
        }
        if (count($resultSet) > 0) {
            $result = Encoder::encode($resultSet);
            $cache->setItem($cacheName, $result);

            return $result;
        }

        return Encoder::encode('');
    }

    /**
     * @param int         $limit
     * @param string|null $project_category_id
     * @param array|null  $tags
     *
     * @return array|false
     * @throws Exception
     */
    public function getTopProductsForHostStores($limit = 10, $project_category_id = null, $tags = null)
    {
        $catids = "";
        if ($project_category_id) {
            $catids = str_replace(',', '', (string)$project_category_id);
        }

        $cache = $this->cache;
        $cacheNameTags = is_array($tags) ? implode('_', $tags) : '';
        $cacheName = __FUNCTION__ . '_' . md5($this->store->config->store_id . (int)$limit . $catids . $cacheNameTags);

        if (($resultSet = $cache->getItem($cacheName))) {
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

            if (!is_array($tagList)) {
                $tagList = array($tagList);
            }

            foreach ($tagList as $item) {
                #and
                $sql .= ' find_in_set(' . $item . ', tag_ids) AND ';
            }
            $sql .= ' 1=1)';
        }

        /*$sql .= ' ORDER BY (round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100) DESC, created_at DESC
            ';*/
        $sql .= ' ORDER BY laplace_score DESC, created_at DESC
            ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = $this->projectRepository->fetchAll($sql);
        $cache->setItem($cacheName, $resultSet);

        if (count($resultSet) > 0) {
            return $resultSet;
        }

        return array();
    }

    /**
     * gets a random project for this store
     *
     * @return array
     */
    //TODO need to be reviewed. 
    public function getRandProduct()
    {
        // $pid = $this->getRandomStoreProjectId();
        // $project_id = $pid['project_id'];
        $project_id = $this->getRandomStoreProjectId();

        $sql = "SELECT 
                `p`.`project_id`
                ,`p`.`title`
                ,`p`.`description`  
                ,`p`.`image_small`
                ,`p`.`count_comments`   
                ,`p`.`changed_at`
                ,`p`.`project_category_id`
                ,`pr`.`likes` AS `count_likes`
                ,`pr`.`dislikes` AS `count_dislikes`
                ,IFNULL(`pr`.`score_with_pling`, 500) AS `laplace_score`
                ,`m`.`member_id`     
                ,`m`.`profile_image_url`
                ,`m`.`username`     

                FROM
                `project` AS `p`            
                JOIN `member` AS `m` ON `m`.`member_id` = `p`.`member_id`
                LEFT JOIN  `stat_rating_project` AS `pr`  ON `p`.`project_id` = `pr`.`project_id`
                WHERE
                `p`.`project_id` = :project_id
                ";

        return $this->projectRepository->fetchRow($sql, array('project_id' => $project_id));

    }

    public function getRandomStoreProjectId()
    {
        $activeCategories = $this->getActiveCategoriesForCurrentHost();
        if (count($activeCategories) == 0) {
            return array();
        }
        $sql = '
                SELECT 
                    max(project_id) as project_id                
                FROM
                    `project` AS `p` 
                JOIN `member` AS `m` ON `m`.`member_id` = `p`.`member_id` and `m`.`is_active` = 1
                WHERE
                    `p`.`status` = 100
                    AND `p`.`type_id` = 1                    
                    AND `p`.`project_category_id` IN (' . implode(',', $activeCategories) . ')                    
                ';
        $resultSet = $this->projectRepository->fetchRow($sql);
        $maxProjectId = $resultSet['project_id'];
        $irandom = rand(997623, $maxProjectId);

        $sql = '
                SELECT 
                    project_id                
                FROM
                    `project` AS `p`                
                WHERE
                    `p`.`status` = 100
                    AND `p`.`type_id` = 1                    
                    AND `p`.`project_category_id` IN (' . implode(',', $activeCategories) . ')  
                    AND `p`.`project_id` >= ' . $irandom . ' limit 1';

        $resultSet = $this->projectRepository->fetchRow($sql);

        return $resultSet['project_id'];
    }


    /**
     * get a random project id in an array
     *
     * @return array
     */
    // public function getRandomStoreProjectId()
    // {
    //     $cache = $this->cache;
    //     $cacheName = __FUNCTION__ . '_' . md5($this->store->config->store_id);

    //     $resultSet = $cache->getItem($cacheName);

    //     if (false == $resultSet) {
    //         $activeCategories = $this->getActiveCategoriesForCurrentHost();
    //         if (count($activeCategories) == 0) {
    //             return array();
    //         }
    //         $sql = '
    //                 SELECT 
    //                     `p`.`project_id`                   
    //                 FROM
    //                     `project` AS `p`                
    //                 WHERE
    //                     `p`.`status` = 100
    //                     AND `p`.`type_id` = 1                    
    //                     AND `p`.`project_category_id` IN (' . implode(',', $activeCategories) . ')                    
    //                 ';
    //         $resultSet = $this->projectRepository->fetchAll($sql);
    //         $cache->setItem($cacheName, $resultSet);
    //     }

    //     $irandom = rand(0, sizeof($resultSet) - 1);

    //     return $resultSet[$irandom];
    // }

    /**
     * get random project id as array
     *
     * @return array
     */
    public function getRandPlingedProduct()
    {
        $pid = $this->getRandomPlingedProjectId();
        $project_id = $pid['project_id'];

        $sql = "SELECT 
                `p`.`project_id`
                ,`p`.`title`
                ,`p`.`description`  
                ,`p`.`image_small`
                ,`p`.`count_comments`   
                ,`p`.`changed_at`
                ,`p`.`project_category_id`
                ,`pr`.`likes` AS `count_likes`
                ,`pr`.`dislikes` AS `count_dislikes`
                ,IFNULL(`pr`.`score_with_pling`, 500) AS `laplace_score`
                ,`m`.`member_id`    
                ,`m`.`profile_image_url`
                ,`m`.`username`                               
                ,(SELECT count(1) FROM `project_plings` `pp` WHERE `pp`.`project_id` = `p`.`project_id` AND `pp`.`is_deleted` = 0) AS `sum_plings`
                FROM
                `project` AS `p`            
                JOIN `member` AS `m` ON `m`.`member_id` = `p`.`member_id`
                LEFT JOIN  `stat_rating_project` AS `pr`  ON `p`.`project_id` = `pr`.`project_id`
                WHERE
                `p`.`project_id` = :project_id";

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

        return $this->projectRepository->fetchRow($sql, array('project_id' => $project_id));
        /*$resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('project_id' => $project_id));
        if (count($resultSet) > 0) {
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array($resultSet));
        }

        return new Zend_Paginator(new Zend_Paginator_Adapter_Array(array()));*/
    }

    /**
     * get random project id as array
     *
     * @return array
     */
    public function getRandomPlingedProjectId()
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__;

        $resultSet = $cache->getItem($cacheName);

        if (false == $resultSet) {
            $sql = "      SELECT    
                            `p`.`project_id`
                        FROM `project_plings` `pl`
                        INNER JOIN `stat_projects` `p` ON `pl`.`project_id` = `p`.`project_id`            
                        WHERE `pl`.`is_deleted` = 0 AND `pl`.`is_active` = 1 ";
            $resultSet = $this->projectRepository->fetchAll($sql);
            $cache->setItem($cacheName, $resultSet); //cache is cleaned once a day
        }

        $irandom = rand(0, sizeof($resultSet) - 1);

        return $resultSet[$irandom];
    }

    /**
     * get random project id as array
     *
     * @return array
     */
    public function getRandFeaturedProduct()
    {
        $pid = $this->getRandomFeaturedProjectId();
        $project_id = $pid['project_id'];

        $sql = "SELECT 
                `p`.`project_id`
                ,`p`.`project_category_id`
                ,`p`.`title`
                ,`p`.`description`  
                ,`p`.`image_small`
                ,`p`.`count_comments`   
                ,`p`.`changed_at`
                ,`pr`.`likes` AS `count_likes`
                ,`pr`.`dislikes` AS `count_dislikes`
                ,IFNULL(`pr`.`score_with_pling`, 500) AS `laplace_score`
                ,`m`.`member_id`                               
                ,`m`.`profile_image_url`
                ,`m`.`username`                               
                FROM
                `project` AS `p`            
                JOIN `member` AS `m` ON `m`.`member_id` = `p`.`member_id`
                LEFT JOIN  `stat_rating_project` AS `pr`  ON `p`.`project_id` = `pr`.`project_id`
                WHERE
                `p`.`project_id` = :project_id";

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

        return $this->projectRepository->fetchRow($sql, array('project_id' => $project_id));
    }

    /**
     * get a random project id as array
     *
     * @return array
     */
    public function getRandomFeaturedProjectId()
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__;

        $resultSet = $cache->getItem($cacheName);

        if (false == $resultSet) {
            $sql = "SELECT `project_id` FROM  `project` `p` WHERE `p`.`status` = 100 AND `p`.`featured` = 1 ";
            $resultSet = $this->projectRepository->fetchAll($sql);
            $cache->setItem($cacheName, $resultSet); //cache is cleaned once a day
        }

        $irandom = rand(0, sizeof($resultSet) - 1);

        return $resultSet[$irandom];
    }

    /**
     * @param int  $limit
     * @param null $project_category_id
     *
     * @return array|Paginator
     */
    public function getFeaturedProductsForHostStores($limit = 10, $project_category_id = null)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5($this->store->config->store_id . (int)$limit . (int)$project_category_id);

        if (false !== ($resultSet = $cache->getItem($cacheName)) && null != $resultSet) {
            return new Paginator(new ArrayAdapter($resultSet));
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

        $resultSet = $this->projectRepository->fetchAll($sql);
        $cache->setItem($cacheName, $resultSet);

        if (count($resultSet) > 0) {
            return new Paginator(new ArrayAdapter($resultSet));
        } else {
            return new Paginator(new ArrayAdapter(array()));
        }
    }

    /**
     * get last comments for actual store and member
     *
     * @param int $member_id
     * @param int $limit
     * @param int $comment_type
     *
     * @return array
     */
    public function getLastCommentsForUsersProjects($member_id, $limit = 10, $comment_type = 0)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5($this->store->config->store_id . (int)$member_id . (int)$limit) . $comment_type;
        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }


        $sql = '
                SELECT 
                `comment_id`
                ,`comment_text`
                , `member`.`member_id`
                ,`member`.`profile_image_url`
                ,`comment_created_at`
                ,`username`            
                ,`title`
                ,`project_id`
                ,`comments`.`comment_target_id`

                FROM `comments`           
                JOIN `project` ON `comments`.`comment_target_id` = `project`.`project_id` 
                STRAIGHT_JOIN `member` ON `comments`.`comment_member_id` = `member`.`member_id`
                WHERE `comments`.`comment_active` = 1
                AND `project`.`status` = 100
                AND `comments`.`comment_type`=:comment_type
                AND `project`.`member_id` =:member_id
                AND `comments`.`comment_member_id` <>:member_id
                ORDER BY `comments`.`comment_created_at` DESC               
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }


        $resultSet = $this->projectRepository->fetchAll(
            $sql, array('member_id' => $member_id, 'comment_type' => $comment_type)
        );

        if (count($resultSet) > 0) {
            $cache->setItem($cacheName, $resultSet);

            return $resultSet;
        } else {
            $cache->setItem($cacheName, array());

            return array();
        }
    }

    /**
     * get featured products for user
     *
     * @param int $member_id
     * @param int $limit
     *
     * @return array
     */
    public function getFeaturedProductsForUser($member_id, $limit = 10)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5($this->store->config->store_id . (int)$member_id . (int)$limit);

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = "SELECT 
                `p`.`project_id`
                ,`p`.`title`
                ,`p`.`description`  
                ,`p`.`image_small`
                ,`p`.`count_comments`   
                ,`p`.`changed_at`                
                ,`p`.`laplace_score`
                ,`p`.`profile_image_url`
                ,`p`.`username`                               
                FROM
                `stat_projects` AS `p`                                           
                WHERE
                `p`.`status` = 100 AND `p`.`featured` = 1  AND `p`.`member_id` = :member_id 
        ";

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $result = $this->projectRepository->fetchAll($sql, array('member_id' => $member_id));

        if ($result) {
            $resultSet = $result;
        } else {
            $resultSet = array();
        }
        $cache->setItem($cacheName, $resultSet);

        return $resultSet;
    }

    /**
     * last votes for user
     *
     * @param int $member_id
     * @param int $limit
     *
     * @return array
     */
    public function getLastVotesForUsersProjects($member_id, $limit = 10)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5($this->store->config->store_id . (int)$member_id . (int)$limit);
        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
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
                JOIN `comments` ON `project_rating`.`comment_id` = `comments`.`comment_id`   
                STRAIGHT_JOIN `member` ON `project_rating`.`member_id` = `member`.`member_id`
                WHERE `project`.`status` = 100 AND `project_rating`.`rating_active`=1
                AND `project`.`member_id` = :member_id
                ORDER BY `rating_id` DESC               
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $resultSet = $this->projectRepository->fetchAll($sql, array('member_id' => $member_id));

        if (count($resultSet) > 0) {
            $cache->setItem($cacheName, $resultSet);

            return $resultSet;
        } else {
            $cache->setItem($cacheName, array());

            return array();
        }
    }

    /**
     * Last spam projects for user
     *
     * @param int $member_id
     * @param int $limit
     *
     * @return array
     */
    public function getLastSpamProjects($member_id, $limit = 10)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5($this->store->config->store_id . (int)$member_id . (int)$limit);

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
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

        $result = $this->projectRepository->fetchAll(
            $sql, array('threshold' => SpamService::SPAM_THRESHOLD, 'member_id' => $member_id)
        );

        if ($result) {
            $resultSet = $result;
        } else {
            $resultSet = array();
        }
        $cache->setItem($cacheName, $resultSet);

        return $resultSet;

    }

    /**
     *
     * @param int $member_id
     * @param int $limit
     *
     * @return array
     */
    public function getLastDonationsForUsersProjects($member_id, $limit = 10)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5($this->store->config->store_id . (int)$member_id . (int)$limit);

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '
         SELECT 
                `plings`.`project_id`,
                `plings`.`id` 
                ,`member`.`member_id`
                ,`profile_image_url`
                ,`plings`.`create_time`
                ,`username`
                ,`plings`.`amount`
                ,`comment`
                ,`project`.`title`
                FROM `plings`
                JOIN `project` ON `project`.`project_id` = `plings`.`project_id`   
                STRAIGHT_JOIN `member` ON `plings`.`member_id` = `member`.`member_id`     
                WHERE 
                `plings`.`status_id` = 2
                AND `project`.`status`=100
                AND `project`.`member_id` = :member_id
                ORDER BY `create_time` DESC
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultSet = $this->projectRepository->fetchAll($sql, array('member_id' => $member_id));

        if ($resultSet) {
            $cache->setItem($cacheName, $resultSet);

            return $resultSet;
        } else {
            $cache->setItem($cacheName, array());

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
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit);

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '
                SELECT 
                `member_id`,
                `profile_image_url`,
                `username`,
                `created_at`
                FROM `member`
                WHERE `is_active` = :activeVal
                AND `type` = :typeVal     
                ORDER BY `created_at` DESC             
            ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        $resultMembers = $this->projectRepository->fetchAll(
            $sql, array(
                    'activeVal' => MemberRepository::MEMBER_ACTIVE,
                    'typeVal'   => MemberRepository::MEMBER_TYPE_PERSON,
                )
        );

        $cache->setItem($cacheName, $resultMembers);

        return $resultMembers;
    }

    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getSupporters($limit = 20)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit);

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }
        $sql = '
                SELECT  
                `s`.`member_id` AS `supporter_id`
                ,`s`.`member_id`
                ,(SELECT `username` FROM `member` `m` WHERE `m`.`member_id` = `s`.`member_id`) AS `username`
                ,(SELECT `profile_image_url` FROM `member` `m` WHERE `m`.`member_id` = `s`.`member_id`) AS `profile_image_url`
                ,max(`s`.`active_time_max`) AS `created_at`
                FROM `v_support` `s`
                GROUP BY `member_id`
                ORDER BY max(`s`.`active_time_max`) DESC                                       
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = $this->projectRepository->fetchAll($sql);
        $cache->setItem($cacheName, $result);

        return $result;
    }

    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getNewActiveSupporters($limit = 20)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit);

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
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
                SELECT  
                `s`.`member_id` AS `supporter_id`
                ,`s`.`member_id`
                ,(SELECT `username` FROM `member` `m` WHERE `m`.`member_id` = `s`.`member_id`) AS `username`
                ,(SELECT `profile_image_url` FROM `member` `m` WHERE `m`.`member_id` = `s`.`member_id`) AS `profile_image_url`
                ,max(`s`.`active_time_max`) AS `created_at`
                FROM `v_support` `s`
                GROUP BY `member_id`
                ORDER BY max(`s`.`active_time_max`) DESC                                       
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = $this->projectRepository->fetchAll($sql);
        $cache->setItem($cacheName, $result);

        return $result;
    }

    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getNewActiveSupportersForSectionAll($limit = 20)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit);

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '
                SELECT  `s`.*,
                `s`.`member_id` AS `supporter_id`
                ,`s`.`member_id`
                ,(SELECT `username` FROM `member` `m` WHERE `m`.`member_id` = `s`.`member_id`) AS `username`
                ,(SELECT `profile_image_url` FROM `member` `m` WHERE `m`.`member_id` = `s`.`member_id`) AS `profile_image_url`
                ,MAX(`s`.`active_time`) AS `active_time_max`
                ,`ss`.`tier` AS `section_support_tier`
                FROM `section_support_paypements` `ss`
                JOIN `support` `s` ON `s`.`id` = `ss`.`support_id`
                WHERE `ss`.`yearmonth` = DATE_FORMAT(NOW(), "%Y%m")
                GROUP BY `s`.`member_id`,`ss`.`tier`
                ORDER BY `active_time_max` DESC                                       
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = $this->projectRepository->fetchAll($sql);
        $cache->setItem($cacheName, $result);

        return $result;
    }

    /**
     *
     * @param int $member_id
     *
     * @return boolean
     */
    public function isMemberActiveSupporter($member_id)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5((int)$member_id);

        $isActiveSupporter = false;

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '
                SELECT  `s`.`member_id` AS `supporter_id`, COUNT(1) AS `count_supports`
                FROM `section_support_paypements` `ss`
                JOIN `support` `s` ON `s`.`id` = `ss`.`support_id`
                WHERE `ss`.`yearmonth` = DATE_FORMAT(NOW(), "%Y%m")
                AND `s`.`member_id` = :member_id 
                GROUP BY `s`.`member_id`                                     
        ';
        $result = $this->projectRepository->fetchRow($sql, array('member_id' => $member_id));

        if ($result) {
            $isActiveSupporter = true;
        }

        $cache->setItem($cacheName, $isActiveSupporter);

        return $isActiveSupporter;
    }

    /**
     *
     * @param int $member_id
     *
     * @return array
     */
    public function getSupporterActiveMonths($member_id)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . $member_id;
        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }
        $sql = "SELECT 
                count(DISTINCT `yearmonth`) `active_months`
                FROM `section_support_paypements` `ss`
                JOIN `support` `s` ON `s`.`id` = `ss`.`support_id`
                WHERE `s`.`member_id` = :member_id              
                ";
        $result = $this->projectRepository->fetchRow($sql, array('member_id' => $member_id));
        $cache->setItem($cacheName, $result);

        return $result;
    }

    /**
     *
     * @param int $section_id
     *
     * @return array
     */
    public function getSectionSupportersActiveMonths($section_id)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . $section_id;
        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }
        $sql = "SELECT COUNT(1) AS `active_months`, `member_id`,sum(`tier`) `sum_support` FROM
                (
                SELECT `s`.`member_id`, `p`.`yearmonth` , sum(`p`.`tier`) `tier` FROM `section_support_paypements` `p`
                JOIN `support` `s` ON `s`.`id` = `p`.`support_id`
                WHERE `p`.`section_id` = :section_id
                GROUP BY `s`.`member_id`, `p`.`yearmonth`
                ) `A`
                GROUP BY `member_id`
                ";

        $result = $this->projectRepository->fetchAll($sql, array('section_id' => $section_id));
        $cache->setItem($cacheName, $result);
        return $result;
    }

    
    

    /**
     *
     * @param int $section_id
     * @param int $limit
     *
     * @return array
     */
    public function getNewActiveSupportersForSectionUnique($section_id, $limit = 1000)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . $section_id . '_' . md5((int)$limit);
        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '
                SELECT  
                `s`.`member_id`
                ,`m`.`username`
                ,`m`.`profile_image_url`
                ,sum(`ss`.`tier`) AS `sum_support`
                FROM `section_support_paypements` `ss`
                JOIN `support` `s` ON `s`.`id` = `ss`.`support_id`
                JOIN `member` `m` ON `m`.`member_id` = `s`.`member_id`
                WHERE `ss`.`section_id` = :section_id
                AND `ss`.`yearmonth` = DATE_FORMAT(NOW(), "%Y%m")
                GROUP BY `s`.`member_id`
                ORDER BY SUM(`ss`.`tier`) DESC                                  
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = $this->projectRepository->fetchAll($sql, array('section_id' => $section_id));
        $cache->setItem($cacheName, $result);

        return $result;
    }

    /**
     *
     * @param int $section_id
     *
     * @return array
     */
    public function getRandomSupporterForSection($section_id)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . $section_id;
        $supporters = $cache->getItem($cacheName);

        if ($supporters) {
            $supporters = $cache->getItem($cacheName);
        } else {

            $sql = '
                    SELECT `section_id`, `member_id`, `weight` 
                    FROM `v_supporter_view_queue` 
                    WHERE `section_id` = :section_id
                    ORDER BY `weight` DESC                        
            ';
            $supporters = $this->projectRepository->fetchAll($sql, array('section_id' => $section_id));

            //If there is no real supporter, show pling user
            if (!$supporters || count($supporters) == 0) {
                $sql = '
                    SELECT `section_id`, `member_id`, `weight` 
                    FROM `v_supporter_view_queue_all` 
                    WHERE `section_id` = :section_id
                    ORDER BY `weight` DESC                        
                ';
                $supporters = $this->projectRepository->fetchAll($sql, array('section_id' => $section_id));
            }

            $cache->setItem($cacheName, $supporters);
        }

        $sumWeight = 0;
        foreach ($supporters as $s) {
            $sumWeight = $sumWeight + $s['weight'];
        }
        // select Random [1.. sumWeight];
        $randomWeight = rand(1, $sumWeight);
        $sumWeight = 0;
        $member_id = null;
        foreach ($supporters as $s) {
            $sumWeight = $sumWeight + $s['weight'];
            if ($sumWeight >= $randomWeight) {
                $member_id = $s['member_id'];
                break;
            }
        }

        if ($member_id) {
            $sql = "SELECT `member_id`,`username`,`profile_image_url` FROM `member` WHERE `member_id`=:member_id";
            $memberRepo = $this->memberService->getMemberRepository();

            return $memberRepo->fetchRow($sql, array('member_id' => $member_id));
        }


        return null;
    }

    /**
     *
     * @param int $section_id
     * @param int $limit
     *
     * @return array
     */
    public function getNewActiveSupportersForSection($section_id, $limit = 20)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . $section_id . '_' . md5((int)$limit);

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '
                SELECT  `s`.*,
                `s`.`member_id` AS `supporter_id`
                ,`s`.`member_id`
                ,(SELECT `username` FROM `member` `m` WHERE `m`.`member_id` = `s`.`member_id`) AS `username`
                ,(SELECT `profile_image_url` FROM `member` `m` WHERE `m`.`member_id` = `s`.`member_id`) AS `profile_image_url`
                ,MAX(`s`.`active_time`) AS `active_time_max`
                ,`ss`.`tier` AS `section_support_tier`
                FROM `section_support_paypements` `ss`
                JOIN `support` `s` ON `s`.`id` = `ss`.`support_id`
                WHERE `ss`.`section_id` = :section_id
                AND `ss`.`yearmonth` = DATE_FORMAT(NOW(), "%Y%m")
                GROUP BY `s`.`member_id`,`ss`.`tier`
                ORDER BY  `active_time_max` DESC                                       
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = $this->projectRepository->fetchAll($sql, array('section_id' => $section_id));
        $cache->setItem($cacheName, $result);

        return $result;
    }

    /**
     *
     * @param int $section_id
     * @param int $yearmonth
     * @param int $limit
     *
     * @return array
     */
    public function getNewActiveSupportersForSectionAndMonth($section_id, $yearmonth, $limit = 100)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . $section_id . '_' . $yearmonth . '_' . md5((int)$limit);

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '
                SELECT  `s`.*,
                `s`.`member_id` AS `supporter_id`
                ,`s`.`member_id`
                ,(SELECT `username` FROM `member` `m` WHERE `m`.`member_id` = `s`.`member_id`) AS `username`
                ,(SELECT `profile_image_url` FROM `member` `m` WHERE `m`.`member_id` = `s`.`member_id`) AS `profile_image_url`
                ,MAX(`s`.`active_time`) AS `active_time_max`
                ,SUM(`ss`.`tier`) AS `sum_tier`
                FROM `section_support_paypements` `ss`
                JOIN `support` `s` ON `s`.`id` = `ss`.`support_id`
                WHERE `ss`.`section_id` = :section_id
                AND `ss`.`yearmonth` = :yearmonth
                GROUP BY `s`.`id`,`s`.`member_id`
        ';
        //order BY SUM(ss.tier) DESC, active_time_max DESC
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = $this->projectRepository->fetchAll(
            $sql, array('section_id' => $section_id, 'yearmonth' => $yearmonth)
        );
        $cache->setItem($cacheName, $result);

        return $result;
    }

    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getNewActivePlingProduct($limit = 20)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit);
        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '  
            SELECT 
            `pl`.`member_id` AS `pling_member_id`
            ,`pl`.`project_id`                        
            ,`p`.`title`
            ,`p`.`image_small`
            ,`p`.`laplace_score`
            ,`p`.`count_likes`
            ,`p`.`count_dislikes`   
            ,`p`.`member_id` 
            ,`p`.`profile_image_url`
            ,`p`.`username`
            ,`p`.`cat_title` AS `catTitle`
            ,(
                SELECT max(`created_at`) FROM `project_plings` `pt` WHERE `pt`.`member_id` = `pl`.`member_id` AND `pt`.`project_id`=`pl`.`project_id`
            ) AS `created_at`
            ,(SELECT count(1) FROM `project_plings` `pl2` WHERE `pl2`.`project_id` = `p`.`project_id` AND `pl2`.`is_active` = 1 AND `pl2`.`is_deleted` = 0  ) AS `sum_plings`
            FROM `project_plings` `pl`
            INNER JOIN `stat_projects` `p` ON `pl`.`project_id` = `p`.`project_id` AND `p`.`status`=100                   
            WHERE `pl`.`is_deleted` = 0 AND `pl`.`is_active` = 1 
            ORDER BY `created_at` DESC                                                  
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = $this->projectRepository->fetchAll($sql);

        $cache->setItem($cacheName, $result);

        return $result;
    }

    /**
     *
     * @param int $limit
     * @param int $offset
     *
     * @return string
     */
    public function getJsonNewActivePlingProduct($limit = 20, $offset = null)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit) . md5((int)$offset);

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '  
                SELECT 
                `pl`.`project_id`
                ,`p`.`project_category_id`
                , max(`pl`.`created_at`) AS `pling_created_at`             
                , min(`pl`.`member_id`) AS `pling_member_id`
                ,`p`.`title`
                ,`p`.`image_small`
                ,`p`.`laplace_score`
                ,`p`.`count_likes`
                ,`p`.`count_dislikes`   
                ,`p`.`member_id` 
                ,`p`.`description`
                ,`p`.`profile_image_url`
                ,`p`.`username`
                ,`p`.`cat_title` 
                ,`p`.`count_comments`    
                ,count(`pl`.`project_id`) AS `sum_plings`                
                ,`p`.`project_changed_at` AS `changed_at`
                ,`p`.`project_created_at` AS `created_at`                
                FROM `project_plings` `pl`
                INNER JOIN `stat_projects` `p` ON `pl`.`project_id` = `p`.`project_id` AND `p`.`status` > 30           
                WHERE `pl`.`is_deleted` = 0 AND `pl`.`is_active` = 1 
                GROUP BY `pl`.`project_id`
                ORDER BY `pling_created_at` DESC                                                 
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }
        $resultSet = $this->projectRepository->fetchAll($sql, array());

        $imagehelper = new Image();
        foreach ($resultSet as &$value) {
            $value['image_small'] = $imagehelper->Image($value['image_small'], array('width' => 200, 'height' => 200));
        }

        $result = Encoder::encode($resultSet);
        $cache->setItem($cacheName, $result);

        return $result;
    }

    /**
     * @param int  $limit
     *
     * @param null $offset
     *
     * @return array|false|mixed
     */
    public function getTopScoreUsers($limit = 120, $offset = null)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit) . md5((int)$offset);
        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '
            SELECT  
            `s`.*
            ,`m`.`profile_image_url`
            ,`m`.`username`
            FROM `member_score` `s`
            INNER JOIN `member` `m` ON `s`.`member_id` = `m`.`member_id`
            ORDER BY `s`.`score` DESC             
                ';


        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }

        $resultMembers = $this->projectRepository->fetchAll($sql, array());

        $cache->setItem($cacheName, $resultMembers);

        return $resultMembers;
    }

    /**
     *
     * @return mixed
     */
    public function getMostPlingedProductsTotalCnt()
    {
        $sql = '
            SELECT count(1) AS `total_count`
            FROM
            (
                SELECT DISTINCT `pl`.`project_id` 
                FROM `project_plings` `pl`
                INNER JOIN `stat_projects` `p` ON `pl`.`project_id` = `p`.`project_id` AND `p`.`status` = 100
                WHERE `pl`.`is_deleted` = 0 AND `pl`.`is_active` = 1 
            ) `t`
        ';
        $result = $this->projectRepository->fetchRow($sql);

        return $result['total_count'];
    }

    /**
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getMostPlingedProducts($limit = 20, $offset = null)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit) . md5((int)$offset);

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '  
                SELECT `pl`.`project_id`
                ,count(1) AS `sum_plings` 
                ,`p`.`title`
                ,`p`.`image_small`
                ,`p`.`laplace_score`
                ,`p`.`count_likes`
                ,`p`.`count_dislikes`   
                ,`p`.`member_id` 
                ,`p`.`profile_image_url`
                ,`p`.`username`
                ,`p`.`cat_title` AS `catTitle`
                ,`p`.`project_changed_at`
                ,`p`.`version`
                ,`p`.`description`
                ,`p`.`package_names`
                ,`p`.`count_comments`
                FROM `project_plings` `pl`
                INNER JOIN `stat_projects` `p` ON `pl`.`project_id` = `p`.`project_id` AND `p`.`status` = 100
                WHERE `pl`.`is_deleted` = 0 AND `pl`.`is_active` = 1 
                GROUP BY `pl`.`project_id`
                ORDER BY `sum_plings` DESC                                                               
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }
        $result = $this->projectRepository->fetchAll($sql);

        $cache->setItem($cacheName, $result);

        return $result;
    }

    /**
     *
     * @param int $member_id
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getMostPlingedProductsForUser($member_id, $limit = 20, $offset = null)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5($member_id) . md5((int)$limit) . md5((int)$offset);
        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '  
                        SELECT `pl`.`project_id`
                        ,count(1) AS `sum_plings` 
                        ,`p`.`title`
                        ,`p`.`image_small`                        
                        ,`p`.`cat_title` AS `catTitle`
                        ,`p`.`project_changed_at`                        
                        FROM `project_plings` `pl`
                        INNER JOIN `stat_projects` `p` ON `pl`.`project_id` = `p`.`project_id` AND `p`.`status` = 100
                        WHERE `pl`.`is_deleted` = 0 AND `pl`.`is_active` = 1 AND `p`.`member_id` = :member_id
                        GROUP BY `pl`.`project_id`
                        ORDER BY `sum_plings` DESC 
                                                              
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }
        $result = $this->projectRepository->fetchAll($sql, array('member_id' => $member_id));

        $cache->setItem($cacheName, $result);

        return $result;
    }

    /**
     *
     * @return int
     */
    public function getMostPlingedCreatorsTotalCnt()
    {
        $sql = '
            SELECT count(1) AS `total_count`
            FROM
            (
                SELECT DISTINCT `p`.`member_id`
                FROM `stat_projects` `p`
                JOIN `project_plings` `pl` ON `p`.`project_id` = `pl`.`project_id`                       
                WHERE `p`.`status` = 100 AND `pl`.`is_deleted` = 0 AND `pl`.`is_active` = 1 
            ) `t`
        ';
        $result = $this->projectRepository->fetchRow($sql);

        return $result['total_count'];
    }

    /**
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getMostPlingedCreators($limit = 20, $offset = null)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5((int)$limit) . md5((int)$offset);

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '  
                       SELECT `p`.`member_id`,
                        count(1) AS `cnt`,
                        `m`.`username`,
                        `m`.`profile_image_url`,
                        `m`.`created_at`
                        FROM `stat_projects` `p`
                        JOIN `project_plings` `pl` ON `p`.`project_id` = `pl`.`project_id`
                        JOIN `member` `m` ON `p`.`member_id` = `m`.`member_id`
                        WHERE `p`.`status` = 100
                        AND `pl`.`is_deleted` = 0 AND `pl`.`is_active` = 1 
                        GROUP BY `p`.`member_id`
                        ORDER BY `cnt` DESC                        
                                                              
        ';
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }

        $result = $this->projectRepository->fetchAll($sql);

        $cache->setItem($cacheName, $result);

        return $result;
    }

    /**
     *
     * @return int
     */
    public function getCountActiveSupporters()
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__;

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
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
                    count( DISTINCT `s`.`member_id`) AS `total_count`
                    FROM `v_support` `s`                         
                    WHERE `is_valid` = 1
        ';
        $result = $this->projectRepository->fetchAll($sql);
        $totalcnt = $result['total_count'];
        $cache->setItem($cacheName, $totalcnt);

        return $totalcnt;
    }

    /**
     *
     * @return int
     */
    public function getCountAllSupporters()
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__;
        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }
        $sql = '
                        SELECT 
                        count( DISTINCT `s`.`member_id`) AS `total_count`
                        FROM `v_support` `s`                         
                                          
        ';

        $result = $this->projectRepository->fetchRow($sql);
        $totalcnt = $result['total_count'];
        $cache->setItem($cacheName, $totalcnt);

        return $totalcnt;
    }

    /**
     *
     * @param int $tier
     *
     * @return int
     */
    public function getCountTierSupporters($tier)
    {
        $sql = "
                SELECT count(1) AS `cnt` FROM
                    (
                    SELECT 
                    `member_id`,
                    max(`amount`),
                    `tier`
                    FROM `support`
                    WHERE `status_id` = 2
                    GROUP BY `member_id`
                    ) `t` WHERE `tier` = :tier

        ";
        $result = $this->projectRepository->fetchRow($sql, array('tier' => $tier));

        return $result[0]['cnt'];
    }

    /**
     *
     * @param int $section_id
     *
     * @return int
     */
    public function getCountSectionSupporters($section_id)
    {
        $sql = "
                SELECT count(1) AS `cnt` FROM
                    `section_support`
                    JOIN `support` ON `support`.`id` = `section_support`.`support_id`
                    WHERE `support`.`status_id` = 2
                    AND `support`.`type_id` = 1
                    AND `section_support`.`section_id` = :section_id
        ";
        $result = $this->projectRepository->fetchRow($sql, array('section_id' => $section_id));

        return $result['cnt'];
    }

    /**
     *
     * @param int $section_id
     * @param int $member_id
     *
     * @return int
     */
    public function getCountSupportedMonthsForSectionAndMember($section_id, $member_id)
    {
        $sql = "
                SELECT COUNT(1) AS `num_months` FROM
                (
                SELECT `s`.`member_id`, `p`.`yearmonth` FROM `section_support_paypements` `p`
                JOIN `support` `s` ON `s`.`id` = `p`.`support_id`
                WHERE `member_id` = :member_id
                AND `p`.`section_id` = :section_id
                GROUP BY `s`.`member_id`, `p`.`yearmonth`
                ) `A`
        ";
        $result = $this->projectRepository->fetchRow(
            $sql, array('member_id' => $member_id, 'section_id' => $section_id)
        );

        return $result['num_months'];
    }

    /**
     *
     * @return double
     */
    public function getSumSupporting()
    {
        $sql = "
                SELECT SUM(`s`.`tier`) AS `sum_tier` FROM `v_support` `v`
                JOIN `support` `s` ON `s`.`member_id` = `v`.`member_id` AND `s`.`active_time` = `v`.`active_time_max`
                WHERE `v`.`is_valid` = 1
        ";
        $result = $this->projectRepository->fetchRow($sql);

        return $result['sum_tier'];
    }

    /**
     *
     * @param int $yearmonth
     *
     * @return double
     */
    public function getSumPayoutForMonth($yearmonth)
    {
        $sql = "
                SELECT SUM(`p`.`probably_payout_amount`) AS `sum_payout` FROM `member_dl_plings` `p`
                WHERE `p`.`yearmonth` = :yearmonth
                AND `p`.`paypal_mail` IS NOT NULL
        ";
        $result = $this->projectRepository->fetchRow($sql, array('yearmonth' => $yearmonth));

        return $result['sum_payout'];
    }

    /**
     *
     * @return array
     */
    public function getModeratorsList()
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__;

        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $sql = '
                SELECT 
                `member_id`,
                `profile_image_url`,
                `username`,
                `created_at`
                FROM `member`
                WHERE `is_active` = :activeVal
                AND `type` = :typeVal     
                AND `roleid` = :roleid
                ORDER BY `created_at` DESC             
            ';


        $resultMembers = $this->projectRepository->fetchAll(
            $sql, array(
                    'activeVal' => MemberRepository::MEMBER_ACTIVE,
                    'typeVal'   => MemberRepository::MEMBER_TYPE_PERSON,
                    'roleid'    => MemberRepository::ROLE_ID_MODERATOR,
                )
        );

        $cache->setItem($cacheName, $resultMembers);

        return $resultMembers;
    }

    /**
     *
     * @return int
     */
    public function getCountMembers()
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__;
        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }
        $sql = "
                        SELECT
                            count(1) AS `total_count`
                        FROM
                            `member`
                        WHERE
                            `is_active`=1 AND `is_deleted`=0
                       ";

        $result = $this->projectRepository->fetchRow($sql);
        $totalcnt = $result['total_count'];
        $cache->setItem($cacheName, $totalcnt);

        return $totalcnt;
    }

    /**
     * gets all data from a user for the tooltip
     *
     * @param int $member_id
     *
     * @return array
     */
    public function getTooltipForMember($member_id)
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5($member_id);
        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $modelMember = $this->memberService;
        $modelMemberTable = $this->memberService->getMemberRepository();
        $tblFollower = new ProjectFollowerRepository($this->db);
        $modelProject = new ProjectRepository($this->db);
        $printDateSince = new PrintDateSince();

        $cnt = $modelMember->fetchCommentsCount($member_id);
        $cntLikesGave = $tblFollower->countLikesHeGave($member_id);
        $cntLikesGot = $tblFollower->countLikesHeGot($member_id);
        $donationinfo = $modelMember->fetchSupporterDonationInfo($member_id);
        $lastactive = $modelMember->fetchLastActiveTime($member_id);
        $cntprojects = $modelProject->countAllProjectsForMember($member_id, true);

        $member = $modelMemberTable->findById($member_id);
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
            'cntLikesGot'   => $cntLikesGot,
        );

        $cache->setItem($cacheName, $data);

        return $data;
    }

    /**
     *
     * @param int $project_id
     *
     * @return double
     */
    public function getProbablyPayoutPlingsCurrentmonth($project_id)
    {
        $sql = " SELECT FORMAT(`probably_payout_amount`, 2) AS `amount` FROM `member_dl_plings` WHERE `project_id` = :project_id AND `yearmonth`=(DATE_FORMAT(NOW(),'%Y%m'))";
        $result = $this->projectRepository->fetchRow($sql, array('project_id' => $project_id));

        return $result['amount'];
    }

    /**
     *
     * @return string
     */
    public function getOCSInstallInstruction()
    {
        $cache = $this->cache;
        $cacheName = __FUNCTION__;
        if ($cache->hasItem($cacheName)) {
            return $cache->getItem($cacheName);
        }

        $config = $this->config['ocs_config']['settings']['server']['opencode'];

        $readme = 'https://opencode.net/OCS/ocs-url/raw/master/docs/How-to-install.md?inline=false';

        //$httpClient = new Zend_Http_Client($readme, array('keepalive' => true, 'strictredirects' => true));
        $httpClient = new Client(
            $readme, array(
                       'adapter'         => 'Laminas\Http\Client\Adapter\Curl',
                       'keepalive'       => true,
                       'strictredirects' => true,
                   )
        );


        $httpClient->resetParameters();
        $httpClient->setUri($readme);
        $httpClient->setHeaders(array('Private-Token' => $config['private_token']));
        $httpClient->setHeaders(array('Sudo' => $config['user_sudo']));
        $httpClient->setHeaders(array('User-Agent' => $config['user_agent']));
        $httpClient->setMethod('GET');


        $response = $httpClient->send();

        $body = $response->getBody();

        if (!$body) {
            return array();
        }
        $Parsedown = new Parsedown();

        $readmetext = $Parsedown->text($body);

        $cache->setItem($cacheName, $readmetext);

        return $readmetext;
    }

    /**
     *
     * @param int $member_id
     *
     * @return array
     */
    public function getDiscussionOpendeskop($member_id)
    {
        $sql = "
                SELECT 
                 `c`.`comment_id`
                ,`c`.`comment_text`
                ,`c`.`comment_member_id`
                ,`c`.`comment_created_at`
                ,`m`.`username`          
                ,`p`.`project_id`
                ,`p`.`title`
                ,`cp`.`comment_member_id` `p_comment_member_id`
                ,(SELECT `username` FROM `member` `m` WHERE `m`.`member_id` = `cp`.`comment_member_id`) `p_username`
                FROM `comments` `c` 
                INNER JOIN `project` `p` ON `c`.`comment_target_id` = `p`.`project_id` AND `p`.`status` = 100
                INNER JOIN  `member` `m` ON `c`.`comment_member_id` = `m`.`member_id`
                LEFT JOIN `comments` `cp` ON `c`.`comment_parent_id` = `cp`.`comment_id`
                WHERE `c`.`comment_type` = 0 AND `c`.`comment_active` = 1
                AND `c`.`comment_member_id` = :member_id
                ORDER BY `c`.`comment_created_at` DESC
                LIMIT 10
                ";

        return $this->projectRepository->fetchAll($sql, array('member_id' => $member_id));
    }

}