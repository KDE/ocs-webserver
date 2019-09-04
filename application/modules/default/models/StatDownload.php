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

    public function getUserDownloads($member_id)
    {
        $sql = "
                SELECT 
                    `member_dl_plings`.*,
                    CASE WHEN (SELECT count(1) AS `sum_plings` FROM `project_plings` `pp` WHERE `pp`.`project_id` = `member_dl_plings`.`project_id` AND `pp`.`is_deleted` = 0 AND `is_active` = 1 GROUP BY `pp`.`project_id`) > 0 THEN (SELECT count(1) AS `sum_plings` FROM `project_plings` `pp` WHERE `pp`.`project_id` = `member_dl_plings`.`project_id` AND `pp`.`is_deleted` = 0 AND `is_active` = 1 GROUP BY `pp`.`project_id`) + 1 ELSE 1 END AS `num_plings_now`,
                    `project`.`title`,
                    `project`.`image_small`,
                    `project_category`.`title` AS `cat_title`,
                    laplace_score(`project`.`count_likes`, `project`.`count_dislikes`)/100 AS `laplace_score`,
                    `member_payout`.`amount`,
                    `member_payout`.`status`,
                    `member_payout`.`payment_transaction_id`,
                    CASE WHEN `tag_object`.`tag_item_id` IS NULL THEN 1 ELSE 0 END AS `is_license_missing_now`,
                    CASE WHEN ((`project_category`.`source_required` = 1 AND `project`.`source_url` IS NOT NULL AND LENGTH(`project`.`source_url`) > 0) OR  (`project_category`.`source_required` = 0)) THEN 0 ELSE 1 END AS `is_source_missing_now`,
                    `project`.`pling_excluded` AS `is_pling_excluded_now`
                FROM
                    `member_dl_plings`
                STRAIGHT_JOIN
                    `project` ON `project`.`project_id` = `member_dl_plings`.`project_id`
                STRAIGHT_JOIN 
                    `project_category` ON `project_category`.`project_category_id` = `member_dl_plings`.`project_category_id`
                LEFT JOIN
                    `member_payout` ON `member_payout`.`member_id` = `member_dl_plings`.`member_id`
                        AND `member_payout`.`yearmonth` = `member_dl_plings`.`yearmonth`
                LEFT JOIN `tag_object` ON `tag_object`.`tag_type_id` = 1 AND `tag_object`.`tag_group_id` = 7 AND `tag_object`.`is_deleted` = 0 AND `tag_object`.`tag_object_id` = `project`.`project_id`
                WHERE
                    `member_dl_plings`.`member_id` = :member_id
                    
                ORDER BY `member_dl_plings`.`yearmonth` DESC, `project_category`.`title`, `project`.`title`
            ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('member_id' => $member_id));

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }
    
    
    public function getUserDownloadsForMonth($member_id, $yearmonth)
    {
        return $this->getUserDownloadsForMonthAndSection($member_id, $yearmonth, null);
    }
    
    
    public function getUserDownloadsForMonthAndSection($member_id, $yearmonth, $section_id = null)
    {
        $sql = "
                SELECT 
                    `member_dl_plings`.*,
                    CASE WHEN (SELECT count(1) AS `sum_plings` FROM `project_plings` `pp` WHERE `pp`.`project_id` = `member_dl_plings`.`project_id` AND `pp`.`is_deleted` = 0 AND `is_active` = 1 GROUP BY `pp`.`project_id`) > 0 THEN (SELECT count(1) AS `sum_plings` FROM `project_plings` `pp` WHERE `pp`.`project_id` = `member_dl_plings`.`project_id` AND `pp`.`is_deleted` = 0 AND `is_active` = 1 GROUP BY `pp`.`project_id`) + 1 ELSE 1 END AS `num_plings_now`,
                    `project`.`title`,
                    `project`.`image_small`,
                    `project_category`.`title` AS `cat_title`,
                    laplace_score(`project`.`count_likes`, `project`.`count_dislikes`)/100 AS `laplace_score`,
                    `member_payout`.`amount`,
                    `member_payout`.`status`,
                    `member_payout`.`payment_transaction_id`,
                    CASE WHEN `tag_object`.`tag_item_id` IS NULL THEN 1 ELSE 0 END AS `is_license_missing_now`,
                    CASE WHEN ((`project_category`.`source_required` = 1 AND `project`.`source_url` IS NOT NULL AND LENGTH(`project`.`source_url`) > 0) OR  (`project_category`.`source_required` = 0)) THEN 0 ELSE 1 END AS `is_source_missing_now`,
                    `project`.`pling_excluded` AS `is_pling_excluded_now`,
                    (SELECT u.num_downloads FROM member_dl_plings_nouk u WHERE u.member_id = `member_dl_plings`.`member_id` and u.project_id = `member_dl_plings`.`project_id` AND u.yearmonth = `member_dl_plings`.yearmonth) AS num_downloads_nouk,
                    (SELECT u.probably_payout_amount FROM member_dl_plings_nouk u WHERE u.member_id = `member_dl_plings`.`member_id` and u.project_id = `member_dl_plings`.`project_id` AND u.yearmonth = `member_dl_plings`.yearmonth) AS probably_payout_amount_nouk
                    ,sc.section_id,s.name AS section_name,`member_dl_plings`.section_payout_factor
                FROM
                    `member_dl_plings`
                STRAIGHT_JOIN
                    `project` ON `project`.`project_id` = `member_dl_plings`.`project_id`
                STRAIGHT_JOIN 
                    `project_category` ON `project_category`.`project_category_id` = `member_dl_plings`.`project_category_id`
                LEFT JOIN
                    `member_payout` ON `member_payout`.`member_id` = `member_dl_plings`.`member_id`
                        AND `member_payout`.`yearmonth` = `member_dl_plings`.`yearmonth`
                LEFT JOIN `tag_object` ON `tag_object`.`tag_type_id` = 1 AND `tag_object`.`tag_group_id` = 7 AND `tag_object`.`is_deleted` = 0 AND `tag_object`.`tag_object_id` = `project`.`project_id`
                LEFT JOIN section_category sc ON sc.project_category_id = `member_dl_plings`.`project_category_id`
                LEFT JOIN section s ON s.section_id = sc.section_id
                WHERE
                    `member_dl_plings`.`member_id` = :member_id
                    AND `member_dl_plings`.`yearmonth` = :yearmonth ";
        
        if(null != $section_id) {
            $sql .=  " AND `member_dl_plings`.`section_id` = ".$section_id;
        }
        
        $sql .=  " ORDER BY `member_dl_plings`.`yearmonth` DESC, `project_category`.`title`, `project`.`title`
            ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('member_id' => $member_id, 'yearmonth' => $yearmonth));

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }
    
    public function getUserSectionsForMonth($member_id, $yearmonth)
    {
        $sql = "
                SELECT yearmonth, section_id, section_name, section_order, section_payout_factor, COUNT(project_id) AS count_projects, SUM(num_downloads) AS num_downloads, SUM(probably_payout_amount) AS sum_probably_payout_amount, SUM(real_payout_amount) AS sum_real_payout_amount, MAX(amount) AS payout_amount, MAX(STATUS) AS payout_status, MAX(payment_transaction_id) AS payout_payment_transaction_id, MAX(paypal_mail) AS paypal_mail
                FROM (
                    SELECT 
                        `member_dl_plings`.*,
                        CASE WHEN (SELECT count(1) AS `sum_plings` FROM `project_plings` `pp` WHERE `pp`.`project_id` = `member_dl_plings`.`project_id` AND `pp`.`is_deleted` = 0 AND `is_active` = 1 GROUP BY `pp`.`project_id`) > 0 THEN (SELECT count(1) AS `sum_plings` FROM `project_plings` `pp` WHERE `pp`.`project_id` = `member_dl_plings`.`project_id` AND `pp`.`is_deleted` = 0 AND `is_active` = 1 GROUP BY `pp`.`project_id`) + 1 ELSE 1 END AS `num_plings_now`,
                        `project`.`title`,
                        `project`.`image_small`,
                        `project_category`.`title` AS `cat_title`,
                        laplace_score(`project`.`count_likes`, `project`.`count_dislikes`)/100 AS `laplace_score`,
                        `member_payout`.`amount`,
                        `member_payout`.`status`,
                        `member_payout`.`payment_transaction_id`,
                        CASE WHEN `tag_object`.`tag_item_id` IS NULL THEN 1 ELSE 0 END AS `is_license_missing_now`,
                        CASE WHEN ((`project_category`.`source_required` = 1 AND `project`.`source_url` IS NOT NULL AND LENGTH(`project`.`source_url`) > 0) OR  (`project_category`.`source_required` = 0)) THEN 0 ELSE 1 END AS `is_source_missing_now`,
                        `project`.`pling_excluded` AS `is_pling_excluded_now`,
                        (SELECT u.num_downloads FROM member_dl_plings_nouk u WHERE u.member_id = `member_dl_plings`.`member_id` and u.project_id = `member_dl_plings`.`project_id` AND u.yearmonth = `member_dl_plings`.yearmonth) AS num_downloads_nouk,
                        (SELECT u.probably_payout_amount FROM member_dl_plings_nouk u WHERE u.member_id = `member_dl_plings`.`member_id` and u.project_id = `member_dl_plings`.`project_id` AND u.yearmonth = `member_dl_plings`.yearmonth) AS probably_payout_amount_nouk
                        ,s.name AS section_name
                        ,s.`order` AS section_order
                        , case when is_license_missing = 1 OR is_source_missing = 1 OR is_pling_excluded = 1 then 0 ELSE probably_payout_amount END AS real_payout_amount

                    FROM
                        `member_dl_plings`
                    STRAIGHT_JOIN
                        `project` ON `project`.`project_id` = `member_dl_plings`.`project_id`
                    STRAIGHT_JOIN 
                        `project_category` ON `project_category`.`project_category_id` = `member_dl_plings`.`project_category_id`
                    LEFT JOIN
                        `member_payout` ON `member_payout`.`member_id` = `member_dl_plings`.`member_id`
                            AND `member_payout`.`yearmonth` = `member_dl_plings`.`yearmonth`
                    LEFT JOIN `tag_object` ON `tag_object`.`tag_type_id` = 1 AND `tag_object`.`tag_group_id` = 7 AND `tag_object`.`is_deleted` = 0 AND `tag_object`.`tag_object_id` = `project`.`project_id`
                    LEFT JOIN section_category sc ON sc.project_category_id = `member_dl_plings`.`project_category_id`
                    LEFT JOIN section s ON s.section_id = sc.section_id
                    WHERE
                        `member_dl_plings`.`member_id` = :member_id 
                        AND `member_dl_plings`.`yearmonth` = :yearmonth
                ) A
                GROUP BY yearmonth, section_id, section_name, section_payout_factor  
                ORDER BY section_order 
            ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('member_id' => $member_id, 'yearmonth' => $yearmonth));

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }
    
    public function getUserDownloadMonths($member_id, $year)
    {
        $sql = "
                SELECT 
                    DISTINCT `member_dl_plings`.`yearmonth`, `member_payout`.payment_transaction_id, `member_payout`.`status`
                FROM
                    `member_dl_plings`
                STRAIGHT_JOIN
                    `project` ON `project`.`project_id` = `member_dl_plings`.`project_id`
                STRAIGHT_JOIN 
                    `project_category` ON `project_category`.`project_category_id` = `member_dl_plings`.`project_category_id`
                LEFT JOIN
                    `member_payout` ON `member_payout`.`member_id` = `member_dl_plings`.`member_id`
                        AND `member_payout`.`yearmonth` = `member_dl_plings`.`yearmonth`
                LEFT JOIN `tag_object` ON `tag_object`.`tag_type_id` = 1 AND `tag_object`.`tag_group_id` = 7 AND `tag_object`.`is_deleted` = 0 AND `tag_object`.`tag_object_id` = `project`.`project_id`
                WHERE
                    `member_dl_plings`.`member_id` = :member_id
                AND SUBSTR(`member_dl_plings`.`yearmonth`,1,4) = :year 
                ORDER BY `member_dl_plings`.`yearmonth` DESC
            ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('member_id' => $member_id, 'year' => $year));

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }
    
    public function getUserDownloadYears($member_id)
    {
        $sql = "
                SELECT 

                    SUBSTR(`member_dl_plings`.`yearmonth`,1,4) as year,
                    MAX(`member_dl_plings`.`yearmonth`) as max_yearmonth,
                    SUM(`member_payout`.amount) as sum_amount
                FROM
                    `member_dl_plings`
                STRAIGHT_JOIN
                    `project` ON `project`.`project_id` = `member_dl_plings`.`project_id`
                STRAIGHT_JOIN 
                    `project_category` ON `project_category`.`project_category_id` = `member_dl_plings`.`project_category_id`
                LEFT JOIN
                    `member_payout` ON `member_payout`.`member_id` = `member_dl_plings`.`member_id`
                        AND `member_payout`.`yearmonth` = `member_dl_plings`.`yearmonth`
                LEFT JOIN `tag_object` ON `tag_object`.`tag_type_id` = 1 AND `tag_object`.`tag_group_id` = 7 AND `tag_object`.`is_deleted` = 0 AND `tag_object`.`tag_object_id` = `project`.`project_id`
                WHERE
                    `member_dl_plings`.`member_id` = :member_id
                GROUP BY SUBSTR(`member_dl_plings`.`yearmonth`,1,4)
                ORDER BY SUBSTR(`member_dl_plings`.`yearmonth`,1,4) DESC
            ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('member_id' => $member_id));

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }

    public function getMonthEarn($member_id,$yyyymm)
    {
        $sql = " select sum(probably_payout_amount) amount
               from member_dl_plings 
               where member_id=:member_id
               and yearmonth=:yyyymm 
               and is_pling_excluded = 0 
               and is_license_missing = 0";
 
        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('member_id' => $member_id,'yyyymm' =>$yyyymm));
        return array_pop($resultSet);    
       
        
        
    }


    public function getPayoutHistory($member_id)
    {
        $sql="
                SELECT pl.yearmonth
                ,TRUNCATE(sum(probably_payout_amount*section_payout_factor), 2) amount
                ,(select count(1) from member_payout p where p.yearmonth=pl.yearmonth and p.member_id = pl.member_id) cnt
                from member_dl_plings pl
                where pl.member_id =:member_id and yearmonth > 201704
                and is_license_missing = 0
                and is_source_missing = 0
                and is_pling_excluded = 0
                group by yearmonth
                order by yearmonth
        ";
        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('member_id' => $member_id));
        return $resultSet;
    }

}