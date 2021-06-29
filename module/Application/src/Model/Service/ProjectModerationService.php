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

use Application\Model\Repository\ProjectModerationRepository;
use Application\Model\Service\Interfaces\ProjectModerationServiceInterface;
use Application\View\Helper\Image;
use Laminas\Db\Adapter\AdapterInterface;

class ProjectModerationService extends BaseService implements ProjectModerationServiceInterface
{

    const M_TYPE_GET_HOT_NEW_STUFF_EXCLUDED = 1;

    protected $db;
    private $projectModerationRepository;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
        $this->projectModerationRepository = new ProjectModerationRepository($db);
    }

    public function createModeration($project_id, $project_moderation_type_id, $is_set, $userid, $note)
    {
        $sql = '
            SELECT
            `p`.*
            FROM `project_moderation` AS `p`
            WHERE 
            `p`.`project_id` = :project_id
            AND `p`.`is_deleted` = :is_deleted                             
            AND `p`.`project_moderation_type_id` = :project_moderation_type_id
                      ';
        $row = $this->projectModerationRepository->fetchRow(
            $sql, array(
                    'project_id'                 => $project_id,
                    'is_deleted'                 => 0,
                    'project_moderation_type_id' => $project_moderation_type_id,
                )
        );

        if ($row != null) {
            $updateValues = array(
                'is_deleted' => 1,
            );

            $this->projectModerationRepository->update($updateValues, ' project_id=' . $row['project_id'] . ' and project_moderation_type_id=' . $row['project_moderation_type_id']);

            $insertValues = array(
                'project_moderation_type_id' => $row['project_moderation_type_id'],
                'project_id'                 => $row['project_id'],
                'value'                      => $is_set,
                'created_by'                 => $userid,
                'note'                       => $note,
            );

            $this->projectModerationRepository->insert($insertValues);
        } else {
            $this->projectModerationRepository->insertModeration($project_moderation_type_id, $project_id, $is_set, $userid, $note);
        }
    }

    public function getTotalCount($filter)
    {
        $sql = "SELECT count(1)  AS `cnt`
                    FROM `project_moderation` `m`
                    JOIN `project_moderation_type` `t` ON `m`.`project_moderation_type_id` = `t`.`project_moderation_type_id`
                    JOIN `stat_projects` `p` ON `m`.`project_id` = `p`.`project_id` AND `p`.`status`=100
                    WHERE `m`.`is_deleted`= 0  AND `m`.`value` = 1             
        ";
        if ($filter && $filter['member_id']) {
            $sql = $sql . ' and m.created_by = ' . $filter['member_id'];
        }
        $result = $this->projectModerationRepository->fetchRow($sql);

        return $result['cnt'];
    }

    public function getList($member_id = null, $orderby = 'created_at desc', $limit = null, $offset = null)
    {
        $sql = "
            SELECT 
               `m`.*
               ,`t`.`tag_id`
               ,`t`.`name` AS `type_name`
               , `p`.`title`
               , `p`.`count_comments`
               , `p`.`count_dislikes`
               ,`p`.`count_likes`
               ,`p`.`laplace_score`
               ,`p`.`image_small`
               ,`p`.`version`
               ,`p`.`member_id`
               ,`p`.`username`
               ,`p`.`created_at`  AS `project_created_at`
               ,`p`.`changed_at` AS `project_changed_at`
               ,`p`.`cat_title`
               ,(SELECT `username` FROM `member` `mm` WHERE `mm`.`member_id` = `m`.`created_by`) AS `exclude_member_name`                       
            FROM `project_moderation` `m`
            JOIN `project_moderation_type` `t` ON `m`.`project_moderation_type_id` = `t`.`project_moderation_type_id`
            JOIN `stat_projects` `p` ON `m`.`project_id` = `p`.`project_id` AND `p`.`status`=100
            WHERE `m`.`is_deleted`= 0  AND `m`.`value` = 1                   
             ";

        if (isset($member_id) && $member_id != '') {
            $sql = $sql . ' and m.created_by = ' . $member_id;
        }

        if (isset($orderby)) {
            $sql = $sql . '  order by ' . $orderby;
        }

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }


        $resultSet = $this->projectModerationRepository->fetchAll($sql);

        $image = new Image();
        foreach ($resultSet as &$value) {
            $value['image_small'] = $image->Image($value['image_small'], array('height' => 120, 'width' => 120));
        }

        //return$this->generateRowClass($resultSet);;
        return $resultSet;
    }

    public function getMembers()
    {
        $sql = "
            SELECT 
                DISTINCT `m`.`created_by` AS `member_id`                    
               ,(SELECT `username` FROM `member` `mm` WHERE `mm`.`member_id` = `m`.`created_by`) AS `username`
               FROM `project_moderation` `m`                       
               JOIN `stat_projects` `p` ON `m`.`project_id` = `p`.`project_id` AND `p`.`status`=100
               WHERE `m`.`is_deleted`= 0   AND `m`.`value` = 1                                                            
             ";


        return $this->projectModerationRepository->fetchAll($sql);
    }

}
