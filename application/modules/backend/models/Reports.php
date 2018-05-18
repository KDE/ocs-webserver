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
class Backend_Model_Reports
{

    public function getReportsForComments($startIndex = 0, $pageSize = 10, $orderBy = 'reports_comment.created_at DESC')
    {
        $sql = "select 
                    reports_comment.report_id,
                    reports_comment.project_id,
                    reports_comment.comment_id,
                    max(reports_comment.created_at) as last_reported_at,
                    comments.comment_active,
                    comments.comment_text,
                    comments.comment_created_at,
                    comments.comment_deleted_at,
                    count(reports_comment.comment_id) as counter

                from reports_comment
                straight_join comments on comments.comment_id = reports_comment.comment_id
                straight_join member on member.member_id = comments.comment_member_id and member.is_active = 1
                straight_join project on project.project_id = comments.comment_target_id and project.`status` = 100
                group by reports_comment.comment_id
              ";
        $limit = ' limit ' . (int)$startIndex . ',' . (int)$pageSize;
        if(!isset($orderBy) || count($orderBy) == 0) {
            $orderBy = 'reports_comment.created_at DESC';
        }
        $orderBy = ' order by ' . $orderBy;

        $rowSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql.$orderBy.$limit);
        if (0 == count($rowSet)) {
            return array();
        }

        return $rowSet;
    }

    public function getTotalCountForReportedComments()
    {
        $sql = "select '1'
                from reports_comment
                straight_join comments on comments.comment_id = reports_comment.comment_id
                group by reports_comment.comment_id
              ";

        $rowSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);

        return count($rowSet);
    }

    public function getReportsForProjects($startIndex = 0, $pageSize = 10, $orderBy = 'counter DESC, reports_project.project_id', $filterDeleted = 0)
    {
        $sql = "select reports_project.*, project.status, count(reports_project.project_id) as counter, max(reports_project.created_at) as last_report_date
                from reports_project
                straight_join project on project.project_id = reports_project.project_id
                where reports_project.is_deleted = :deleted
                group by reports_project.project_id
        ";

        $limit = ' limit ' . (int)$startIndex . ',' . (int)$pageSize;
        $orderBy = ' order by ' . $orderBy;

        $rowSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql.$orderBy.$limit, array('deleted' => $filterDeleted));
        if (0 == count($rowSet)) {
            return array();
        }

        return $rowSet;
    }

    public function getTotalCountForReportedProject($filterDeleted = 0)
    {
        $sql = "select '1'
                from reports_project
                straight_join project on project.project_id = reports_project.project_id
                where reports_project.is_deleted = :deleted
                group by reports_project.project_id
              ";

        $rowSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('deleted' => $filterDeleted));

        return count($rowSet);
    }

    public function setDelete($projectId, $onlySpam = true)
    {
        $sql = "update reports_project set is_deleted = 1 where project_id = :projectId";
        
        if($onlySpam) {
            $sql .= " and report_type = 0";
        }

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('projectId' => $projectId))->execute();
        $this->updateMaterializedView($projectId);
        return $result;
    }
    
    
    public function setReportAsValid($reportId)
    {
        $sql = "update reports_project set is_valid = 1 where report_id = :reportId";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('reportId' => $reportId))->execute();
        return $result;
    }
    
    public function setReportAsDeleted($reportId)
    {
        $sql = "update reports_project set is_deleted = 1 where report_id = :reportId";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('reportId' => $reportId))->execute();
        return $result;
    }

    private function updateMaterializedView($project_id)
    {
        $sql = "update stat_projects set amount_reports = 0 where project_id = :project_id";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('project_id' => $project_id))->execute();
    }
    
    public function saveNewFraud($project_id, $_authMemeber) 
    {
        $sql = "INSERT INTO reports_project (project_id, report_type, reported_by, is_valid, text) VALUES (:project_id, 1, :member_id, 1, :text)";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('project_id' => $project_id, 'member_id' => $_authMemeber->member_id, 'text' => 'Admin: moved from spam to misuse'));
        return $result;
    }

}