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
 * Created: 13.09.2017
 */

class Default_Model_Section
{

    /**
     * @inheritDoc
     */
    public function __construct()
    {

    }

    public function fetchSponsorHierarchy()
    {
        $sql = "
            SELECT section.name AS section_name, sponsor.sponsor_id,sponsor.name AS sponsor_name
            FROM section_sponsor
            JOIN sponsor ON sponsor.sponsor_id = section_sponsor.sponsor_id
            JOIN section ON section.section_id = section_sponsor.section_id

        ";
        $resultSet = $this->getAdapter()->fetchAll($sql);
        $optgroup = array();
        foreach ($resultSet as $item) {
            $optgroup[$item['section_name']][$item['sponsor_id']] = $item['sponsor_name'];
        }

        return $optgroup;
    }
    
    public function fetchAllSections()
    {
        $sql = "
            SELECT *
            FROM section
            WHERE is_active = 1
        ";
        $resultSet = $this->getAdapter()->fetchAll($sql);

        return $resultSet;
    }
    
    public function fetchFirstSectionForStoreCategories($category_array)
    {
        $sql = "
            SELECT *
            FROM section
            JOIN section_category on section_category.section_id = section.section_id
            WHERE is_active = 1
            AND section_category.project_category_id in (:category_id)
            LIMIT 1
        ";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('category_id' => $category_id))->toArray();

        return $resultSet;
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    private function getAdapter()
    {
        return Zend_Db_Table::getDefaultAdapter();
    }

    /**
     * @param int $yearmonth
     *
     * @return array
     */
    public function fetchAllSectionStats($yearmonth = null, $isForAdmin = false)
    {
        $sql = "SELECT p.yearmonth, s.section_id, s.name AS section_name
                ,(SELECT ROUND(SUM(ss.tier)/12,2) AS sum_support FROM section_support ss
                    JOIN support su2 ON su2.id = ss.support_id
                    WHERE s.section_id = ss.section_id
                    AND ss.is_active = 1
                    AND su2.status_id = 2
                    AND su2.type_id = 1
                    AND DATE_FORMAT(su2.active_time, '%Y%m') <= :yearmonth 
                    GROUP BY ss.section_id
                ) AS sum_support
                ,(SELECT SUM(sp.amount * (ssp.percent_of_sponsoring/100)) AS sum_sponsor FROM sponsor sp
                LEFT JOIN section_sponsor ssp ON ssp.sponsor_id = sp.sponsor_id
                WHERE sp.is_active = 1
                AND ssp.section_id = s.section_id) AS sum_sponsor
                , SUM(p.num_downloads) AS sum_dls
                , ROUND(SUM(p.probably_payout_amount),2) AS sum_amount
                , p3.num_downloads AS sum_dls_payout, p3.amount AS sum_amount_payout
                FROM member_dl_plings p
                LEFT JOIN section_category sc ON sc.project_category_id = p.project_category_id
                LEFT JOIN section s ON s.section_id = sc.section_id
                LEFT JOIN (
                        SELECT yearmonth, section_id, SUM(num_downloads) AS num_downloads, SUM(amount) AS amount FROM (
                                SELECT m.yearmonth, `m`.`member_id`,`m`.`paypal_mail`, s.section_id, sum(`m`.`num_downloads`) AS `num_downloads`,round(sum(`m`.`probably_payout_amount`),2) AS `amount` 
                                from `member_dl_plings` `m` 
                                LEFT JOIN section_category sc ON sc.project_category_id = m.project_category_id
                                LEFT JOIN section s ON s.section_id = sc.section_id
                                where ((`m`.`yearmonth` = :yearmonth) 
                                and (length(`m`.`paypal_mail`) > 0) and (`m`.`paypal_mail` regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') and (`m`.`is_license_missing` = 0) and (`m`.`is_source_missing` = 0) and (`m`.`is_pling_excluded` = 0) and (`m`.`is_member_pling_excluded` = 0)) 
                                group by m.yearmonth, `m`.`member_id`,`m`.`paypal_mail`, s.section_id
                                HAVING sum(`m`.`probably_payout_amount`) >= 1
                        ) A GROUP BY yearmonth, section_id
                ) p3 ON p3.yearmonth = p.yearmonth AND p3.section_id = s.section_id
                WHERE p.yearmonth = :yearmonth
                AND sc.section_id IS NOT null ";
        
        if(!$isForAdmin) {
            $sql .= " AND p.yearmonth >= DATE_FORMAT((NOW() - INTERVAL 1 MONTH),'%Y%m')";
        }
        
        $sql .= " GROUP BY s.section_id";
        if(empty($yearmonth)) {
            $yearmonth = "DATE_FORMAT(NOW(),'%Y%m')";
        }
        $resultSet = $this->getAdapter()->fetchAll($sql, array('yearmonth' => $yearmonth));

        return $resultSet;
    }
    
    /**
     * @param int $yearmonth
     * @param int $section_id
     *
     * @return array
     */
    public function fetchSectionStats($section_id, $yearmonth = null)
    {
        $sql = "SELECT p.yearmonth, s.section_id, s.name AS section_name
                ,(SELECT SUM(tier * (s.percent_of_support/100)) AS sum_support FROM (
                        SELECT * FROM support su
                        WHERE su.status_id = 2
                        AND su.type_id = 0
                        AND DATE_FORMAT(su.active_time, '%Y%m') <= :yearmonth
                        AND DATE_FORMAT(su.active_time + INTERVAL 1 YEAR, '%Y%m') >= :yearmonth
                        UNION ALL 
                        SELECT * FROM support su2
                        WHERE su2.status_id = 2
                        AND su2.type_id = 1
                        AND DATE_FORMAT(su2.active_time, '%Y%m') <= :yearmonth
                ) A) AS sum_support
                ,(SELECT SUM(sp.amount * (ssp.percent_of_sponsoring/100)) AS sum_sponsor FROM sponsor sp
                LEFT JOIN section_sponsor ssp ON ssp.sponsor_id = sp.sponsor_id
                WHERE sp.is_active = 1
                AND ssp.section_id = s.section_id) AS sum_sponsor
                , SUM(p.num_downloads) AS sum_dls
                , ROUND(SUM(p.probably_payout_amount),2) AS sum_amount
                , p3.num_downloads AS sum_dls_payout, p3.amount AS sum_amount_payout
                FROM member_dl_plings p
                LEFT JOIN section_category sc ON sc.project_category_id = p.project_category_id
                LEFT JOIN section s ON s.section_id = sc.section_id
                LEFT JOIN (
                        SELECT yearmonth, section_id, SUM(num_downloads) AS num_downloads, SUM(amount) AS amount FROM (
                                SELECT m.yearmonth, `m`.`member_id`,`m`.`paypal_mail`, s.section_id, sum(`m`.`num_downloads`) AS `num_downloads`,round(sum(`m`.`probably_payout_amount`),2) AS `amount` 
                                from `member_dl_plings` `m` 
                                LEFT JOIN section_category sc ON sc.project_category_id = m.project_category_id
                                LEFT JOIN section s ON s.section_id = sc.section_id
                                where ((`m`.`yearmonth` = :yearmonth) 
                                and (length(`m`.`paypal_mail`) > 0) and (`m`.`paypal_mail` regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') and (`m`.`is_license_missing` = 0) and (`m`.`is_source_missing` = 0) and (`m`.`is_pling_excluded` = 0) and (`m`.`is_member_pling_excluded` = 0)) 
                                group by m.yearmonth, `m`.`member_id`,`m`.`paypal_mail`, s.section_id
                                HAVING sum(`m`.`probably_payout_amount`) >= 1
                        ) A GROUP BY yearmonth, section_id
                ) p3 ON p3.yearmonth = p.yearmonth AND p3.section_id = s.section_id
                WHERE p.yearmonth = :yearmonth
                AND p.section_id = :section_id
                GROUP BY s.section_id";
        if(empty($yearmonth)) {
            $yearmonth = "DATE_FORMAT(NOW(),'%Y%m')";
        }
        $resultSet = $this->getAdapter()->fetchAll($sql, array('yearmonth' => $yearmonth, 'section_id' => $section_id));

        return $resultSet;
    }

    
    public function getAllDownloadYears($isForAdmin = false)
    {
        $sql = "
                SELECT 
                    SUBSTR(`member_dl_plings`.`yearmonth`,1,4) as year,
                    MAX(`member_dl_plings`.`yearmonth`) as max_yearmonth
                FROM
                    `member_dl_plings`";
        if(!$isForAdmin) {
            $sql .= " WHERE SUBSTR(`member_dl_plings`.`yearmonth`,1,4) = DATE_FORMAT(NOW(),'%Y')";
        }
        
        $sql .= " GROUP BY SUBSTR(`member_dl_plings`.`yearmonth`,1,4)
                ORDER BY SUBSTR(`member_dl_plings`.`yearmonth`,1,4) DESC
            ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql);

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }
    
    
    public function getAllDownloadMonths($year, $isForAdmin = false)
    {
        $sql = "
                SELECT 
                    DISTINCT `member_dl_plings`.`yearmonth`
                FROM
                    `member_dl_plings`
                WHERE
                SUBSTR(`member_dl_plings`.`yearmonth`,1,4) = :year ";
                
        if(!$isForAdmin) {
            $sql .= " AND `member_dl_plings`.`yearmonth` >= DATE_FORMAT((NOW() - INTERVAL 1 MONTH),'%Y%m')";
        }

        $sql .= " ORDER BY `member_dl_plings`.`yearmonth` DESC";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('year' => $year));

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }
    
}