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

use Application\Model\Entity\ReportProducts;
use Application\Model\Interfaces\ReportProductsInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ReportProductsRepository extends BaseRepository implements ReportProductsInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "reports_project";
        $this->_key = "report_id";
        $this->_prototype = ReportProducts::class;
    }

    public function setDelete($reportId)
    {
        $this->setIsDeleted($reportId, false);
    }

    public function setDeleteByMember($member_id)
    {
        $this->update(['is_deleted' => 1], ['reported_by' => $member_id]);
    }

    public function countMisuseForProject($project_id)
    {
        return $this->fetchAllRowsCount(['project_id' => $project_id, 'report_type' => 1, 'is_deleted' => 0]);
    }

    public function countSpamForProject($project_id)
    {
        return $this->fetchAllRowsCount(['project_id' => $project_id, 'report_type' => 0, 'is_deleted' => 0]);
    }

    public function fetchMisuseCandidate()
    {
        $sql = "
            SELECT `p`.*, `rp`.`text` AS `report_text`, `rp`.`report_id`, `rp`.`created_at` AS `report_created_at`, `m`.`member_id` AS `report_member_id`, `m`.`username` AS `report_username`
            FROM `reports_project` `rp`
            INNER JOIN `stat_projects` `p` ON `p`.`project_id` = `rp`.`project_id`
            LEFT OUTER JOIN `member` `m` ON `m`.`member_id` = `rp`.`reported_by`
            WHERE `rp`.`report_type` = 1
            AND `rp`.`is_deleted` = 0
            AND `rp`.`is_valid` = 0
            AND `p`.`status` = 100
            ORDER BY `rp`.`created_at` DESC;
        ";

        return $this->fetchAll($sql, null, false);
    }

    public function getReportsForProjects(
        $startIndex = 0,
        $pageSize = 10,
        $orderBy = 'counter DESC, reports_project.project_id',
        $filterDeleted = 0
    ) {
        $sql = "SELECT 	
		  max(`reports_project`.`report_id`) AS `report_id`,
                  max(`reports_project`.`project_id`) AS `project_id`,
                  max(`reports_project`.`report_type`) AS `report_type`,
                  max(`reports_project`.`reported_by`) AS `reported_by`,
                  max(`reports_project`.`text`) AS `text`,
                  max(`reports_project`.`is_deleted`) AS `is_deleted`,
                  max(`reports_project`.`is_valid`) AS `is_valid`,
                  max(`reports_project`.`created_at`) AS `created_at`,
						  `project`.`status`, count(`reports_project`.`project_id`) AS `counter`, max(`reports_project`.`created_at`) AS `last_report_date`
                FROM `reports_project`
                STRAIGHT_JOIN `project` ON `project`.`project_id` = `reports_project`.`project_id`
                WHERE `reports_project`.`is_deleted` = :deleted
                GROUP BY `reports_project`.`project_id`
        ";

        $limit = ' limit ' . (int)$startIndex . ',' . (int)$pageSize;
        $orderBy = ' order by ' . $orderBy;

        $rowSet = $this->fetchAll($sql . $orderBy . $limit, array('deleted' => $filterDeleted));
        if (0 == count($rowSet)) {
            return array();
        }

        return $rowSet;
    }

    public function getTotalCountForReportedProject($filterDeleted = 0)
    {
        $sql = "
                SELECT count(1) AS `cnt`
                FROM(
                SELECT '1'
                FROM `reports_project`
                STRAIGHT_JOIN `project` ON `project`.`project_id` = `reports_project`.`project_id`
                WHERE `reports_project`.`is_deleted` = :deleted
                GROUP BY `reports_project`.`project_id`
                ) `t`
              ";

        $rowSet = $this->fetchRow($sql, array('deleted' => $filterDeleted));

        return $rowSet['cnt'];
    }

    public function doDelete($projectId, $onlySpam = true)
    {
        $sql = "UPDATE `reports_project` SET `is_deleted` = 1 WHERE `project_id` = :projectId";

        if ($onlySpam) {
            $sql .= " and report_type = 0";
        }

        $result = $this->query($sql, ['projectId' => $projectId]);
        $this->updateMaterializedView($projectId);

        return $result;
    }

    private function updateMaterializedView($project_id)
    {
        $sql = "update stat_projects set amount_reports = 0 where project_id = " . $project_id;
        $result = $this->query($sql);
    }

    public function setReportAsValid($reportId)
    {
        $sql = "update reports_project set is_valid = 1 where report_id = " . $reportId;

        return $this->query($sql);
    }

    public function setReportAsDeleted($reportId)
    {
        $sql = "update reports_project set is_deleted = 1 where report_id = " . $reportId;

        return $this->query($sql);
    }

    public function saveNewFraud($project_id, $_authMemeber)
    {
        $sql = "INSERT INTO reports_project (project_id, report_type, reported_by, is_valid, text) VALUES (:project_id, 1, :member_id, 1, :text)";

        return $this->query(
            $sql, array(
            'project_id' => $project_id,
            'member_id'  => $_authMemeber->member_id,
            'text'       => 'Admin: moved from spam to misuse',
        )
        );
    }
}