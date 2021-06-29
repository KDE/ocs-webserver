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

namespace Statistic\Model\Repository;

use Statistic\Model\Interfaces\DataStatiInterface;

class DataStatiRepository extends BaseDataStatiRepository implements DataStatiInterface
{
    public function getPayoutgroupbyamountProduct()
    {
        $sql = "SELECT `gm` AS `x`
                , count(1) AS `y`
                    FROM
                    (
                    SELECT 
                    `m`.`project_id`,
                    round(sum(`m`.`credits_plings`)/100,2) AS `probably_payout_amount`,
                    floor(sum(`m`.`credits_plings`/100)/10)*10 `gm`
                    FROM `micro_payout` `m`
                    WHERE `m`.`paypal_mail` IS NOT NULL AND `m`.`paypal_mail` <> '' AND (`m`.`paypal_mail` REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$')            
                    AND `m`.`yearmonth` = DATE_FORMAT(CURRENT_DATE() - INTERVAL 4 MONTH, '%Y%m')  AND `m`.`is_license_missing` = 0 AND `m`.`is_source_missing`=0 AND `m`.`is_pling_excluded` = 0 
                    AND `m`.`is_member_pling_excluded`=0
                    GROUP BY `m`.`project_id`
                    ) `t`
                    WHERE `t`.`probably_payout_amount` >1
                    GROUP BY `gm`
                    ORDER BY `gm` ASC
            ";

        return $this->fetchAll($sql);
    }

    public function getPayoutgroupbyamountMember()
    {
        $sql = "select gm as x
            , count(1) as y
            from
            (
              select 
              m.member_id,
              round(sum(m.credits_plings)/100,2) AS probably_payout_amount,
              floor(sum(m.credits_plings/100)/10)*10 gm
              from micro_payout m
              where m.paypal_mail is not null and m.paypal_mail <> '' and (m.paypal_mail regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$')            
              and m.yearmonth = DATE_FORMAT(CURRENT_DATE() - INTERVAL 4 MONTH, '%Y%m')  and m.is_license_missing = 0 and m.is_source_missing=0 and m.is_pling_excluded = 0 
              and m.is_member_pling_excluded=0
              GROUP BY m.member_id
            ) t
            where t.probably_payout_amount >1
            group by gm
            order by gm asc
            ";

        return $this->fetchAll($sql);
    }

    public function getNewprojectWeeklystats()
    {
        $sql = "SELECT YEARWEEK(`created_at`) as yyyykw , count(*) as amount  
                  FROM project p
                  join stat_cat_tree t on p.project_category_id = t.project_category_id                
                  where status=100 and type_id = 1 
                  group by  yyyykw    
                  order by yyyykw 
                  desc limit 60";
        return $this->fetchAll($sql);
    }

    public function getTopDownloads($datum)
    {
        $sql = "select * from ppload.stat_ppload_files_top_downloads where datum = :datum order by cnt desc ";
        return $this->fetchAll($sql, array("datum" => $datum));       
    }

    public function getTopDownloadsDatum()
    {
        $sql = "select distinct datum from ppload.stat_ppload_files_top_downloads order by  datum desc";
        return $this->fetchAll($sql);       
    }
    

    

   

   
}