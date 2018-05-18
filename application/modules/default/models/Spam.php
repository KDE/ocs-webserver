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
 *
 * Created: 31.05.2017
 */
class Default_Model_Spam
{

    const SPAM_THRESHOLD = 1;

    public function fetchSpamCandidate()
    {
        $sql = "
            select p.*, rp.text as report_text, rp.report_id, rp.created_at as report_created_at, m.member_id as report_member_id, m.username as report_username
            from reports_project rp
            inner join stat_projects p on p.project_id = rp.project_id
            left outer join member m on m.member_id = rp.reported_by
            where rp.report_type = 0
            and rp.is_deleted = 0
            and rp.is_valid = 0
            and p.`status` = 100
            ORDER BY stat_projects.changed_at DESC, stat_projects.created_at DESC, stat_projects.amount_reports DESC
        ";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('threshold' => self::SPAM_THRESHOLD));
        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();
        }
    }

}