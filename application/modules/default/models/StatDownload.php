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
 *    Created: 16.12.2016
 **/
class Default_Model_StatDownload
{

    /**
     * Default_Model_OAuth constructor.
     */
    public function __construct()
    {
    }

    public function getUserDownloads($member_id)
    {
        $sql = "
                SELECT 
                    member_dl_plings.*,
                    project.title,
                    project.image_small,
                    project_category.title as cat_title,
                    member_payout.amount,
                    member_payout.`status`
                FROM
                    member_dl_plings
                        STRAIGHT_JOIN
                    project ON project.project_id = member_dl_plings.project_id
                        STRAIGHT_JOIN 
                    project_category ON project_category.project_category_id = member_dl_plings.project_category_id
                        LEFT JOIN
                    member_payout ON member_payout.member_id = member_dl_plings.member_id
                        AND member_payout.yearmonth = member_dl_plings.yearmonth
                WHERE
                    member_dl_plings.member_id = :member_id
                    AND project.status = 100
                ORDER BY member_dl_plings.`yearmonth` DESC
            ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('member_id' => $member_id));

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }


}