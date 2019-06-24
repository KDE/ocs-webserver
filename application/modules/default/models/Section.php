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
    public function fetchAllSectionStats($yearmonth = null)
    {
        $sql = "SELECT s.section_id, s.name AS section_name, SUM(p.num_downloads) AS sum_dls, SUM(p.probably_payout_amount) AS sum_amount, SUM(payout.amount) AS sum_payoutamount , SUM(payout.num_downloads) AS sum_payoutanum_downloads 
                FROM member_dl_plings p
                LEFT JOIN section_category sc ON sc.project_category_id = p.project_category_id
                LEFT JOIN section s ON s.section_id = sc.section_id
                LEFT JOIN (SELECT * FROM (select `member_dl_plings`.`yearmonth` AS `yearmonth`,`member_dl_plings`.`member_id` AS `member_id`,`member_dl_plings`.`project_id`,`member_dl_plings`.`mail` AS `mail`,`member_dl_plings`.`paypal_mail` AS `paypal_mail`,sum(`member_dl_plings`.`num_downloads`) AS `num_downloads`,round(sum(`member_dl_plings`.`probably_payout_amount`),2) AS `amount` from `member_dl_plings` where ((length(`member_dl_plings`.`paypal_mail`) > 0) and (`member_dl_plings`.`paypal_mail` regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$')) group by `member_dl_plings`.`member_id`,`member_dl_plings`.`project_id`,`member_dl_plings`.`paypal_mail`) po WHERE po.amount >= 1) payout ON payout.member_id = p.member_id AND payout.project_id = p.project_id AND payout.yearmonth = p.yearmonth
                WHERE p.yearmonth = :yearmonth
                GROUP BY s.section_id";
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
        $sql = "SELECT p.yearmonth, s.section_id, s.name AS section_name, SUM(p.num_downloads) AS sum_dls, SUM(p.probably_payout_amount) AS sum_amount, SUM(payout.amount) AS sum_payoutamount , SUM(payout.num_downloads) AS sum_payoutanum_downloads 
                FROM member_dl_plings p
                LEFT JOIN section_category sc ON sc.project_category_id = p.project_category_id
                LEFT JOIN section s ON s.section_id = sc.section_id
                LEFT JOIN (SELECT * FROM (select `member_dl_plings`.`yearmonth` AS `yearmonth`,`member_dl_plings`.`member_id` AS `member_id`,`member_dl_plings`.`project_id`,`member_dl_plings`.`mail` AS `mail`,`member_dl_plings`.`paypal_mail` AS `paypal_mail`,sum(`member_dl_plings`.`num_downloads`) AS `num_downloads`,round(sum(`member_dl_plings`.`probably_payout_amount`),2) AS `amount` from `member_dl_plings` where ((length(`member_dl_plings`.`paypal_mail`) > 0) and (`member_dl_plings`.`paypal_mail` regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$')) group by `member_dl_plings`.`member_id`,`member_dl_plings`.`project_id`,`member_dl_plings`.`paypal_mail`) po WHERE po.amount >= 1) payout ON payout.member_id = p.member_id AND payout.project_id = p.project_id AND payout.yearmonth = p.yearmonth
                WHERE p.yearmonth = :yearmonth
                AND p.section_id = :section_id
                GROUP BY s.section_id";
        if(empty($yearmonth)) {
            $yearmonth = "DATE_FORMAT(NOW(),'%Y%m')";
        }
        $resultSet = $this->getAdapter()->fetchAll($sql, array('yearmonth' => $yearmonth, 'section_id' => $section_id));

        return $resultSet;
    }

    
    public function getAllDownloadYears()
    {
        $sql = "
                SELECT 
                    SUBSTR(`member_dl_plings`.`yearmonth`,1,4) as year,
                    MAX(`member_dl_plings`.`yearmonth`) as max_yearmonth
                FROM
                    `member_dl_plings`
                GROUP BY SUBSTR(`member_dl_plings`.`yearmonth`,1,4)
                ORDER BY SUBSTR(`member_dl_plings`.`yearmonth`,1,4) DESC
            ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql);

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }
    
    
    public function getAllDownloadMonths($year)
    {
        $sql = "
                SELECT 
                    DISTINCT `member_dl_plings`.`yearmonth`
                FROM
                    `member_dl_plings`
                WHERE
                SUBSTR(`member_dl_plings`.`yearmonth`,1,4) = :year
                ORDER BY `member_dl_plings`.`yearmonth` DESC
            ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('year' => $year));

        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();

        }
    }
    
}