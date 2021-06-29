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

use Application\Model\Entity\ReportComments;
use Application\Model\Interfaces\ReportCommentsInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ReportCommentsRepository extends BaseRepository implements ReportCommentsInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "reports_comment";
        $this->_key = "report_id";
        $this->_prototype = ReportComments::class;
    }

    public function setDelete($reportId)
    {
        $this->setIsDeleted($reportId, false);
    }

    public function setDeleteByMember($member_id)
    {
        $this->update(['is_deleted' => 1], ['reported_by' => $member_id]);
    }

    public function getReportsForComments(
        $startIndex = 0,
        $pageSize = 10,
        $orderBy = 'reports_comment.created_at DESC',
        $where = null
    ) {
        $sqlWhere = '';
        if (isset($where)) {
            $sqlWhere = 'where ' . $where;
        }
        $sql = "select 
                    max(reports_comment.report_id) as report_id,
                    max(reports_comment.project_id) as project_id,
                    max(reports_comment.comment_id) as comment_id,
                    max(reports_comment.created_at) as last_reported_at,
                    max(comments.comment_active) as comment_active,
                    max(comments.comment_text) as comment_text,
                    max(comments.comment_created_at) as comment_created_at,
                    max(comments.comment_deleted_at) as comment_deleted_at,
                    count(reports_comment.comment_id) as counter
                from reports_comment
                straight_join comments on comments.comment_id = reports_comment.comment_id
                straight_join member on member.member_id = comments.comment_member_id and member.is_active = 1
                straight_join project on project.project_id = comments.comment_target_id and project.`status` = 100
                {$sqlWhere} 
                group by reports_comment.comment_id
              ";
        $limit = ' limit ' . (int)$pageSize . ' offset ' . (int)$startIndex;

        if (is_array($orderBy)) {
            $orderBy = implode(',', $orderBy);
        }
        $orderBy = ' order by ' . $orderBy;
        $sql .= $orderBy;
        $sql .= $limit;

        $rowSet = $this->fetchAll($sql);
        if (0 == count($rowSet)) {
            return array();
        }

        return $rowSet;
    }

    public function getTotalCountForReportedComments($where = null)
    {
        $sqlWhere = '';
        if (isset($where)) {
            $sqlWhere = 'where ' . $where;
        }
        $sql = "    select count(1) as cnt
                    from
                    (
                        select 1
                        from reports_comment
                        straight_join comments on comments.comment_id = reports_comment.comment_id
                        straight_join member on member.member_id = comments.comment_member_id and member.is_active = 1
                        straight_join project on project.project_id = comments.comment_target_id and project.`status` = 100
                        {$sqlWhere} 
                        group by reports_comment.comment_id
                    ) t
              ";

        $rowSet = $this->fetchRow($sql);

        return $rowSet['cnt'];
    }
}