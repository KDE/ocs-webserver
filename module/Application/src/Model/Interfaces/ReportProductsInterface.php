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

namespace Application\Model\Interfaces;

interface ReportProductsInterface extends BaseInterface
{
    public function setDelete($reportId);

    public function setDeleteByMember($member_id);

    public function countMisuseForProject($project_id);

    public function countSpamForProject($project_id);

    public function fetchMisuseCandidate();

    public function getReportsForProjects(
        $startIndex = 0,
        $pageSize = 10,
        $orderBy = 'counter DESC, reports_project.project_id',
        $filterDeleted = 0
    );

    public function getTotalCountForReportedProject($filterDeleted = 0);

    public function doDelete($projectId, $onlySpam = true);

    public function setReportAsValid($reportId);

    public function setReportAsDeleted($reportId);

    public function saveNewFraud($project_id, $_authMemeber);
} 