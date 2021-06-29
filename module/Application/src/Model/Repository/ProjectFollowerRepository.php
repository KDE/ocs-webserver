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

namespace Application\Model\Repository;

use Application\Model\Entity\ProjectFollower;
use Application\Model\Interfaces\ProjectFollowerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;

class ProjectFollowerRepository extends BaseRepository implements ProjectFollowerInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "project_follower";
        $this->_key = "project_follower_id";
        $this->_prototype = ProjectFollower::class;
    }

    public function countLikesHeGave($memberId)
    {
        $sql = "
                SELECT count(*) AS `count` 
                FROM `project_follower` `f`  
                INNER JOIN `member` `m` ON `f`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0   
                INNER JOIN `project` `p` ON `p`.`project_id` = `f`.`project_id` AND `p`.`status` = 100 
                WHERE  `f`.`member_id` =:memberId
        ";
        $resultRow = $this->fetchRow($sql, array('memberId' => $memberId));

        return $resultRow['count'];
    }

    public function countLikesHeGot($memberId)
    {
        $sql = "
                SELECT count(*) AS `count` 
                FROM `project_follower` `f`  
                INNER JOIN `member` `m` ON `f`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0   
                INNER JOIN `project` `p` ON `p`.`project_id` = `f`.`project_id` AND `p`.`status` = 100 
                WHERE  `p`.`member_id` =:memberId
        ";
        $resultRow = $this->fetchRow($sql, array('memberId' => $memberId));

        return $resultRow['count'];
    }

    public function countForProject($project_id)
    {
        $selectArr = $this->fetchRow('SELECT count(*) AS count FROM project_follower f  inner join member m on f.member_id = m.member_id and m.is_active=1 AND m.is_deleted=0   WHERE  project_id = ' . $project_id);

        return $selectArr ['count'];
    }

    /**
     * @param int $memberId
     *
     * @return Paginator
     */
    public function fetchLikesForMember($memberId)
    {
        $cache_name = __FUNCTION__ . '_' . $memberId;
        if ($resultSet = $this->readCache($cache_name)) {
            return new Paginator(new ArrayAdapter($resultSet));
        }

        $sql = "   
                SELECT 
                `f`.`project_id`
                ,`f`.`member_id`
                ,`f`.`created_at`
                ,`p`.`member_id` AS `project_member_id`
                ,`m`.`username` AS `project_username`
                ,`p`.`project_category_id`
                ,`p`.`status`
                ,`p`.`title`
                ,`p`.`description`
                ,`p`.`image_small`
                ,`p`.`created_at` AS `project_created_at`
                ,`p`.`changed_at` AS `project_changed_at`                       
                ,`c`.`title` AS `cat_title`                                               
                ,`pr`.`likes` AS `count_likes`
                ,`pr`.`dislikes` AS `count_dislikes`
                ,IFNULL(`pr`.`score_with_pling`, 500) AS `laplace_score`
                FROM `project_follower` `f`                        
                INNER JOIN `project` `p` ON `p`.`project_id` = `f`.`project_id` 
                LEFT JOIN  `stat_rating_project` AS `pr`  ON `p`.`project_id` = `pr`.`project_id`
                INNER JOIN `member` `m` ON `p`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
                INNER JOIN `project_category` `c` ON `p`.`project_category_id` = `c`.`project_category_id`
                WHERE (`p`.`status` = 100) AND (`f`.`member_id` = :member_id) 
                ORDER BY `f`.`created_at` DESC                      
             ";

        $resultSet = $this->fetchAll($sql, array('member_id' => $memberId));
        $this->writeCache($cache_name, $resultSet, 600);

        return new Paginator(new ArrayAdapter($resultSet));
    }

    public function fetchLikesForProject($project_id)
    {
        $sql = "
                         SELECT 
                        f.project_id
                        ,f.member_id
                        ,f.created_at
                        ,m.profile_image_url
                        ,m.created_at as member_created_at
                        ,m.username
                        FROM project_follower f
                        inner join member m on f.member_id = m.member_id and m.is_active=1 AND m.is_deleted=0 
                        WHERE  f.project_id = :project_id
                        order by f.created_at desc
             ";

        return $this->fetchAll($sql, array('project_id' => $project_id));
    }

    public function fetchLikesCntForProject($project_id)
    {
        $sql = "
                         SELECT                      
                        count(1) AS `count`
                        FROM `project_follower` `f`
                        INNER JOIN `member` `m` ON `f`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
                        WHERE  `f`.`project_id` = :project_id
                      
             ";
        $resultSet = $this->fetchRow($sql, array('project_id' => $project_id));

        return $resultSet['count'];
    }

    public function isFollower($member_id, $project_id)
    {
        if (empty($member_id) || empty($project_id)) {
            return false;
        }
        $sql = "
                         SELECT                      
                        count(1) as count
                        FROM project_follower f                    
                        WHERE  f.project_id = :project_id
                        and f.member_id=:member_id                      
             ";
        $resultSet = $this->fetchRow($sql, array('project_id' => (int)$project_id,'member_id' => (int)$member_id));
        if((int)$resultSet['count']>0) return true;
        return false;
    }
}
