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
 * */

namespace Application\Model\Service;

use Application\Model\Repository\ProjectCloneRepository;
use Application\Model\Service\Interfaces\ProjectCloneServiceInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ProjectCloneService extends BaseService implements ProjectCloneServiceInterface
{

    protected $db;
    private $projectCloneRepository;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
        $this->projectCloneRepository = new ProjectCloneRepository($db);
    }

    public function fetchOrigins($project_id)
    {
        $sql = "
            SELECT 
            `c`.`project_id` AS `project_id_clone`
            ,`c`.`project_id_parent` AS `project_id`
            ,`c`.`external_link`
            ,`c`.`member_id`
            ,`c`.`text`
            ,' ' AS `catTitle`
            ,`p`.`project_category_id`
            ,`p`.`title`
            ,`p`.`image_small`
            ,`p`.`changed_at`
            FROM `project_clone` `c`
            LEFT JOIN `project` `p` ON `p`.`project_id` = `c`.`project_id_parent` AND `p`.`status` = 100
            WHERE `c`.`is_deleted` = 0 AND `c`.`is_valid` = 1 AND `c`.`project_id` = :project_id
            ORDER BY `c`.`created_at` DESC
             ";

        return $this->projectCloneRepository->fetchAll($sql, array('project_id' => $project_id));
        // return $resultSet;     
    }

    public function fetchClones($project_id)
    {
        $sql = "
            SELECT 
               `c`.`project_id` AS `project_id`
               ,`c`.`project_id_parent` AS `project_id_origin`
               ,`c`.`external_link`
               ,`c`.`member_id`
               ,`c`.`text`
               ,' ' AS `catTitle`
               ,(SELECT `project_category_id` FROM `project` `p` WHERE `p`.`project_id` = `c`.`project_id_parent`) `project_category_id`
               ,`p`.`title`
               ,`p`.`image_small`
               ,`p`.`changed_at`
               FROM `project_clone` `c`
               JOIN `project` `p` ON `p`.`project_id` = `c`.`project_id`
               WHERE `c`.`is_deleted` = 0 AND `c`.`is_valid` = 1 AND  `c`.`project_id_parent` = :project_id
               AND `p`.`status` = 100
               ORDER BY `c`.`created_at` DESC
             ";

        return $this->projectCloneRepository->fetchAll($sql, array('project_id' => $project_id));
        // return $resultSet;     
    }

    public function fetchParent($project_id)
    {
        $sql = "
                        SELECT 
                        *
                        FROM `project_clone` `c`
                        WHERE `c`.`is_deleted` = 0 AND `c`.`is_valid` = 1 AND  `c`.`project_id` = :project_id
             ";

        return $this->projectCloneRepository->fetchRow($sql, array('project_id' => $project_id));
    }

    public function fetchRelatedProducts($project_id)
    {
        $cacheName = __FUNCTION__ . '_' . $project_id;

        if ($returnValue = $this->readCache($cacheName)) {
            return $returnValue;
        }
        $sql = "  
                SELECT DISTINCT * FROM 
                (
                    SELECT 
                        `c`.`project_id` AS `project_id`
                        ,`c`.`external_link`
                        ,`c`.`member_id`
                        ,`c`.`text`
                        ,' ' AS `catTitle`                     
                        ,`p`.`project_category_id`
                        ,`p`.`title`
                        ,`p`.`image_small`
                        ,`p`.`changed_at`
                        FROM `project_clone` `c`
                        JOIN `project` `p` ON `p`.`project_id` =`c`.`project_id` AND `p`.`status` = 100                       
                        WHERE `c`.`is_deleted` = 0 AND `c`.`is_valid` = 1 AND  `c`.`project_id_parent` =:project_id
                    
                    UNION

                        SELECT 
                        `c`.`project_id` 
                        ,`c`.`external_link`
                        ,`c`.`member_id`
                        ,`c`.`text`
                        ,' ' AS `catTitle`
                        ,`p`.`project_category_id`
                        ,`p`.`title`
                        ,`p`.`image_small`
                        ,`p`.`changed_at`
                        FROM `project_clone` `c`
                        JOIN `project` `p` ON `p`.`project_id` = `c`.`project_id` AND `p`.`status` = 100                      
                        WHERE  `c`.`project_id`<> :project_id AND  `c`.`is_deleted` = 0 AND `c`.`is_valid` = 1 AND `c`.`project_id_parent` IN (
                            SELECT `project_id_parent` FROM `project_clone` `c`
                                WHERE `c`.`project_id` = :project_id AND `c`.`is_valid` = 1 AND `c`.`is_deleted` = 0
                            )
                ) `a`            
                ORDER BY `changed_at` DESC
                        ";
        $returnValue = $this->projectCloneRepository->fetchAll($sql, array('project_id' => $project_id));
        $this->writeCache($cacheName, $returnValue, 300);

        return $returnValue;
    }

    public function fetchMods()
    {
        $sql = "

          SELECT 
          `c`.`project_clone_id`
         ,`c`.`project_id` 
         ,`c`.`project_id_parent`
         ,`c`.`external_link`
         ,`c`.`text`
         ,`c`.`member_id` AS `reported_by`
         ,`m`.`username` AS `reporter_username`
         ,`m`.`profile_image_url`  AS `reporter_profile_image_url`
         ,`p`.`cat_title` `catTitle`
         ,`p`.`title`
         ,`p`.`image_small`
         ,`p`.`changed_at`
         ,`p`.`laplace_score`
         ,`p`.`member_id`
         ,`p`.`username`
         
         FROM `project_clone` `c`                      
         JOIN `member` `m` ON `m`.`member_id` = `c`.`member_id`
         JOIN `stat_projects` `p` ON `p`.`project_id` = `c`.`project_id`                                            
         WHERE `c`.`is_deleted` = 0 AND `c`.`is_valid` = 0  AND `c`.`project_clone_type`=1
         ORDER BY `c`.`created_at` DESC

          ";

        return $this->projectCloneRepository->fetchAll($sql);
    }

    public function fetchCredits()
    {
        $sql = "

            SELECT 
             `c`.`project_clone_id`
            ,`c`.`project_id` 
            ,`c`.`project_id_parent`
            ,`c`.`external_link`
            ,`c`.`text`
            ,`c`.`member_id` AS `reported_by`
            ,`m`.`username` AS `reporter_username`
            ,`m`.`profile_image_url`  AS `reporter_profile_image_url`
            ,`p`.`cat_title` `catTitle`
            ,`p`.`title`
            ,`p`.`image_small`
            ,`p`.`changed_at`
            ,`p`.`laplace_score`
            ,`p`.`member_id`
            ,`p`.`username`
            ,`pp`.`cat_title` `parent_catTitle`
            ,`pp`.`title` `parent_title`
            ,`pp`.`image_small` `parent_image_small`
            ,`pp`.`changed_at` `parent_changed_at`
            ,`pp`.`laplace_score` `parent_laplace_score`
            ,`pp`.`member_id` `parent_member_id`
            ,`pp`.`username` `parent_username`
            FROM `project_clone` `c`
            JOIN `stat_projects` `pp` ON  `pp`.`project_id` =`c`.`project_id_parent` 
            JOIN `member` `m` ON `m`.`member_id` = `c`.`member_id`
            LEFT JOIN `stat_projects` `p` ON `p`.`project_id` = `c`.`project_id`                                            
            WHERE `c`.`is_deleted` = 0 AND `c`.`is_valid` = 0  AND `pp`.`status` = 100 AND `c`.`project_clone_type`=0
            ORDER BY `c`.`created_at` DESC

          ";

        return $this->projectCloneRepository->fetchAll($sql);
    }

    /**
     * @param $project_id
     *
     * @return string comma seperated ids
     */
    function fetchChildrensIds($project_id)
    {
        $sql = "
        SELECT GROUP_CONCAT(`project_id`) AS `ids` FROM `project_clone` `c` WHERE `c`.`project_id_parent` = :project_id AND `is_valid`=1
        ";
        $resultSet = $this->projectCloneRepository->fetchRow($sql, array('project_id' => $project_id));

        return $resultSet['ids'];
    }

    /**
     * @param $ids
     *
     * @return string comma seperated ids
     */
    function fetchChildrensChildrenIds($ids)
    {
        $sql = "
        select GROUP_CONCAT(project_id) as ids from project_clone c where c.project_id_parent in (" . $ids . ") and is_valid=1
        ";
        $resultSet = $this->projectCloneRepository->fetchRow($sql);

        return $resultSet['ids'];
    }

    /**
     * @param $project_id
     *
     * @return string siblings project ids without itself
     */
    function fetchSiblings($project_id)
    {
        $sql = "
                SELECT GROUP_CONCAT(DISTINCT `project_id`) AS `ids` FROM `project_clone` `c` WHERE `c`.`project_id_parent` IN (
                        SELECT `project_id_parent` FROM `project_clone` `c` WHERE `c`.`project_id` = :project_id AND  `c`.`is_valid`=1
                ) AND `c`.`project_id` <> :project_id AND `c`.`is_valid`=1
            ";
        $resultSet = $this->projectCloneRepository->fetchRow($sql, array('project_id' => $project_id));

        return $resultSet['ids'];
    }

    function fetchParentLevelRelatives($project_id)
    {
        $ancesters = self::fetchAncestorsIds($project_id);
        $sql = "
                select GROUP_CONCAT(project_id) as ids from project_clone c where c.project_id_parent in (" . $ancesters . ") and is_valid=1
        ";
        $resultSet = $this->projectCloneRepository->fetchRow($sql);

        return $resultSet['ids'];
    }

    /**
     * @param     $project_id
     * @param int $level
     *
     * @return string comma seperated ids
     */
    function fetchAncestorsIds($project_id, $level = 5)
    {

        $parentIds = self::fetchParentIds($project_id);
        $ids = '';
        while ($level > 0 && strlen($parentIds) > 0) {
            $sql = "select GROUP_CONCAT(distinct project_id_parent) as ids from project_clone c where c.project_id in(" . $parentIds . ") and c.is_valid=1 and c.project_id_parent>0";
            $resultSet = $this->projectCloneRepository->fetchRow($sql);
            if ($resultSet['ids']) {
                $ids .= ',' . $resultSet['ids'];
            } else {
                break;
            }
            $parentIds = $resultSet['ids'];
            $level--;
        }
        if (substr($ids, 0, 1) == ',') {
            $ids = substr($ids, 1);
        }

        return $ids;
    }

    /**
     * @param $project_id
     *
     * @return string comma seperated ids
     */
    function fetchParentIds($project_id)
    {
        $sql = "
        SELECT GROUP_CONCAT(DISTINCT `project_id_parent`) AS `ids` FROM `project_clone` `c` WHERE `c`.`project_id` = :project_id AND `c`.`is_valid`=1 AND `c`.`is_deleted`=0
        AND `c`.`project_id_parent` >0
        ";
        $resultSet = $this->projectCloneRepository->fetchRow($sql, array('project_id' => $project_id));

        return $resultSet['ids'];
    }

    function fetchSiblingsLevelRelatives($parentids, $project_id)
    {
        $sql = "
                select GROUP_CONCAT(project_id) as ids from project_clone c where c.project_id_parent in (" . $parentids . ") and is_valid=1
                        and c.project_id <> :project_id
        ";
        $resultSet = $this->projectCloneRepository->fetchRow($sql, array('project_id' => $project_id));

        return $resultSet['ids'];
    }

    function fetchParentsLevel($ids)
    {
        $sql = "select GROUP_CONCAT(distinct project_id_parent) as ids from project_clone c where c.project_id in(" . $ids . ") and c.is_valid=1 and c.project_id_parent>0 and c.is_deleted=0";
        $resultSet = $this->projectCloneRepository->fetchRow($sql);

        return $resultSet['ids'];
    }

    function fetchChildrensLevel($ids)
    {
        $sql = "select GROUP_CONCAT(distinct project_id) as ids from project_clone c where c.project_id_parent in(" . $ids . ") and c.is_valid=1 and c.project_id_parent>0 and c.is_deleted=0";
        $resultSet = $this->projectCloneRepository->fetchRow($sql);

        return $resultSet['ids'];
    }

}
