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

use Application\Model\Repository\ProjectPlingsRepository;
use Application\Model\Service\Interfaces\ProjectPlingsServiceInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;

class ProjectPlingsService extends BaseService implements ProjectPlingsServiceInterface
{

    protected $db;
    private $projectPlingsRepository;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
        $this->projectPlingsRepository = new ProjectPlingsRepository($db);
    }

    public function fetchPlingsForMember($memberId)
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
            ,`p`.`username` AS `project_username`
            ,`p`.`project_category_id`
            ,`p`.`status`
            ,`p`.`title`
            ,`p`.`description`
            ,`p`.`image_small`
            ,`p`.`project_created_at`
            ,`p`.`project_changed_at`
            ,`p`.`laplace_score`
            ,`p`.`cat_title`
            ,`p`.`count_likes`
            ,`p`.`count_dislikes`
            ,`p`.`laplace_score`
            FROM `project_plings` `f`                        
            INNER JOIN `stat_projects` `p` ON `p`.`project_id` = `f`.`project_id` 
            WHERE (`p`.`status` = 100) AND `f`.`is_active` = 1 AND `f`.`is_deleted`= 0 AND (`f`.`member_id` = :member_id) 
            ORDER BY `f`.`created_at` DESC
             ";

        $resultSet = $this->projectPlingsRepository->fetchAll($sql, array('member_id' => $memberId));
        $this->writeCache($cache_name, $resultSet, 600);

        return new Paginator(new ArrayAdapter($resultSet));
    }

    public function fetchPlingsForSupporter($memberId)
    {
        $cache_name = __FUNCTION__ . '_' . $memberId;
        if ($resultSet = $this->readCache($cache_name)) {
            return new Paginator(new ArrayAdapter($resultSet));
        }

        $sql = "
            SELECT                          
               `f`.`member_id`                       
               , count(1) AS `cntplings`
               ,(SELECT `profile_image_url` FROM  `member` `m` WHERE `m`.`member_id` = `f`.`member_id` ) AS `profile_image_url`
               ,(SELECT `username` FROM  `member` `m` WHERE `m`.`member_id` = `f`.`member_id` ) AS `username`
               ,(SELECT max(`active_time`) FROM `support` `s` WHERE `s`.`member_id` = `f`.`member_id` AND `status_id` = 2) AS `active_time`
            FROM `project_plings` `f`                        
            INNER JOIN `project` `p` ON `p`.`project_id` = `f`.`project_id` 
            WHERE (`p`.`status` = 100) AND `f`.`is_active` = 1 AND `f`.`is_deleted`= 0 AND (`p`.`member_id` = :member_id) 
            GROUP BY `f`.`member_id`
            ORDER BY `cntplings` DESC
                                                
              ";

        $resultSet = $this->projectPlingsRepository->fetchAll($sql, array('member_id' => $memberId));
        $this->writeCache($cache_name, $resultSet, 600);

        return new Paginator(new ArrayAdapter($resultSet));
    }

    public function fetchPlingsForProject($project_id)
    {

        $sql = "
            SELECT 
                `f`.`project_id`
                ,`f`.`member_id`
                ,`f`.`created_at`
                ,`m`.`profile_image_url`
                ,`m`.`created_at` AS `member_created_at`
                ,`m`.`username`
            FROM `project_plings` `f`
            INNER JOIN `member` `m` ON `f`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
            WHERE  `f`.`project_id` = :project_id AND `f`.`is_deleted` = 0
            ORDER BY `f`.`created_at` DESC
             ";

        return $this->projectPlingsRepository->fetchAll($sql, array('project_id' => $project_id));
    }

    public function fetchPlingsCntForProject($project_id)
    {

        $sql = "
            SELECT 
               count(1) AS `count`
            FROM `project_plings` `f`
            INNER JOIN `member` `m` ON `f`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
            WHERE  `f`.`project_id` = :project_id AND `f`.`is_deleted` = 0            
             ";
        $resultSetCnt = $this->projectPlingsRepository->fetchRow($sql, array('project_id' => $project_id));

        return $resultSetCnt['count'];

    }

}
