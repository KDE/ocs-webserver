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

namespace Application\Model\Repository;

use Application\Model\Entity\ProjectPlings;
use Application\Model\Interfaces\ProjectPlingsInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ProjectPlingsRepository extends BaseRepository implements ProjectPlingsInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "project_plings";
        $this->_key = "project_plings_id";
        $this->_prototype = ProjectPlings::class;
    }

    /**
     * @param $project_id
     *
     * @return \Laminas\Db\ResultSet\ResultSet
     */
    public function getPlings($project_id)
    {
        return $this->fetchAllRows(['project_id' => $project_id, 'is_deleted' => 0]);
    }

    public function getPling($project_id, $member_id)
    {
        return $this->fetchAllRows(['project_id' => $project_id, 'member_id' => $member_id, 'is_deleted' => 0])
                    ->current();
    }

    public function setDelete($id)
    {
        $this->setIsDeleted($id);
    }

    public function countPlingsHeGave($member_id)
    {
        $sql = "
                SELECT count(*) AS `count` 
                FROM `project_plings` `f`   
                INNER JOIN `stat_projects` `p` on p.project_id = f.project_id
                WHERE  `f`.`member_id` =:member_id AND `f`.`is_deleted` = 0 AND `f`.`is_active` = 1
        ";
        $resultRow = $this->fetchRow($sql, array('member_id' => $member_id));

        return $resultRow['count'];
    }

    public function getPlingsAmount($project_id)
    {

        $sql = "
                SELECT count(*) AS `count` 
                FROM `project_plings` `f`   
                INNER JOIN `stat_projects` `p` on p.project_id = f.project_id
                WHERE  `f`.`project_id` =:project_id AND `f`.`is_deleted` = 0 AND `f`.`is_active` = 1 
        ";
        $resultRow = $this->fetchRow($sql, array('project_id' => $project_id));

        return $resultRow['count'];
    }

    public function countPlingsHeGotAll($member_id)
    {
        $sql = "
                SELECT count(*) AS `count` 
                FROM `project_plings` `f`  
                INNER JOIN `stat_projects` `p` ON `p`.`project_id` = `f`.`project_id` AND `p`.`status` = 100 
                WHERE  `p`.`member_id` =:member_id AND `f`.`is_deleted` = 0 AND `f`.`is_active` = 1
        ";
        $resultRow = $this->fetchRow($sql, array('member_id' => $member_id));

        return $resultRow['count'];

    }

    public function countPlingsHeGot($member_id)
    {

        $sql = "
                    SELECT IFNULL(sum(`cntplings`), 0)  AS `count` 
                    FROM
                    (
                        SELECT 
                        `p`.`project_id`
                        ,(SELECT count(1) FROM `project_plings` `f` WHERE `f`.`project_id` = `p`.`project_id` AND `f`.`is_deleted` = 0 AND `f`.`is_active` = 1 ) `cntplings`
                        FROM `stat_projects` `p` WHERE `p`.`member_id` =:member_id AND `p`.`status` = 100
                    ) `tt`
         ";
        $resultRow = $this->fetchRow($sql, array('member_id' => $member_id));

        return $resultRow['count'];
    }

    public function getAllPlingListReceived()
    {
        $sql = "
            SELECT `p`.`member_id`
            ,`m`.`username`
            ,count(1) AS `plings`
            FROM `project_plings` `f`, `project` `p`,  `member` `m`
            WHERE `f`.`project_id` = `p`.`project_id` 
            AND `p`.`member_id` = `m`.`member_id` AND `f`.`is_deleted` = 0 AND `f`.`is_active` = 1
            GROUP BY `p`.`member_id`
            ORDER BY `plings` DESC,`m`.`username` ASC
            
        ";

        return $this->fetchAll($sql);
    }

    public function getAllPlingListGiveout()
    {
        $sql = "
                SELECT `f`.`member_id`
                ,`m`.`username`
                ,count(1) AS `plings`
                FROM `project_plings` `f`, `member` `m`
                WHERE `f`.`member_id` = `m`.`member_id` AND `f`.`is_deleted` = 0 AND `f`.`is_active` = 1
                GROUP BY `f`.`member_id`
                ORDER BY `plings` DESC ,`m`.`username` ASC    
        ";

        return $this->fetchAll($sql);
    }


    
}