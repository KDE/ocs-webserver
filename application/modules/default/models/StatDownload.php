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
                    member_payout.`status`,
                    member_payout.payment_transaction_id,
                    CASE WHEN tag_object.tag_item_id IS NULL THEN 1 ELSE 0 END AS is_license_missing_now,
                    CASE WHEN ((project_category.source_required = 1 AND project.source_url IS NOT NULL AND LENGTH(project.source_url) > 0) OR  (project_category.source_required = 0)) THEN 0 ELSE 1 END AS is_source_missing_now,
                    project.pling_excluded as is_pling_excluded_now
                FROM
                    member_dl_plings
                        STRAIGHT_JOIN
                    project ON project.project_id = member_dl_plings.project_id
                        STRAIGHT_JOIN 
                    project_category ON project_category.project_category_id = member_dl_plings.project_category_id
                        LEFT JOIN
                    member_payout ON member_payout.member_id = member_dl_plings.member_id
                        AND member_payout.yearmonth = member_dl_plings.yearmonth
                    LEFT JOIN tag_object ON tag_object.tag_type_id = 1 AND tag_object.tag_group_id = 7 AND tag_object.tag_object_id = project.project_id
                WHERE
                    member_dl_plings.member_id = :member_id
                    
                ORDER BY member_dl_plings.`yearmonth` DESC, project_category.title, project.title
            ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('member_id' => $member_id));

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }


}