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
class Default_Model_DbTable_ReportProducts extends Local_Model_Table
{

    protected $_name = "reports_project";

    protected $_keyColumnsForRow = array('report_id');

    protected $_key = 'report_id';

    protected $_defaultValues = array(
        'report_id'   => null,
        'report_type'   => null,
        'project_id'  => null,
        'reported_by' => null,
        'text'  => null,
        'is_deleted'  => null,
        'is_valid'   => null,
        'created_at'  => null
    );

    public function setDelete($reportId)
    {
        $updateValues = array(
            'is_deleted' => 1,
        );

        $this->update($updateValues, 'report_id=' . $reportId);
    }

    public function setDeleteByMember($member_id)
    {
        $updateValues = array(
            'is_deleted' => 1,
        );

        $this->update($updateValues, 'reported_by=' . $member_id);
    }

    public function countMisuseForProject($project_id)
    {
        $q = $this->select()
                        ->where('project_id = ?', $project_id)
                        ->where('report_type = ?', 1)
                         ->where('is_deleted = ?', 0)
                        ;
        return count($q->query()->fetchAll());
    }

    public function countSpamForProject($project_id)
    {
        $q = $this->select()
                        ->where('project_id = ?', $project_id)
                        ->where('report_type = ?', 0)
                         ->where('is_deleted = ?', 0)
                        ;
        return count($q->query()->fetchAll());
    }

    

}