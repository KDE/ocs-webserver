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

use Application\Model\Interfaces\ConfigStoreCategoryInterface;
use Application\Model\Interfaces\ProjectCategoryInterface;
use Laminas\Db\Adapter\AdapterInterface;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Statistic\Model\Interfaces\DataStatiDwhInterface;

class DataStatiDwhRepository extends BaseDataStatiRepository implements DataStatiDwhInterface
{
    const DEFAULT_STORE_ID = 22; //opendesktop
    private $configStoreCategoryRepository;
    private $projectCategoryRepository;

    public function __construct(
        AdapterInterface $db,
        ConfigStoreCategoryInterface $configStoreCategoryRepository,
        ProjectCategoryInterface $projectCategoryRepository
    ) {
        parent::__construct($db);
        $this->configStoreCategoryRepository = $configStoreCategoryRepository;
        $this->projectCategoryRepository = $projectCategoryRepository;
    }

    public function getNewmemberstats()
    {
        $sql = "SELECT DATE(`created_at`) AS `memberdate` , count(*) AS `daycount` FROM `dwh`.`ods_member_v`  GROUP BY  `memberdate`    ORDER BY `memberdate` DESC LIMIT 30";

        return $this->fetchAll($sql);
    }

    public function getNewprojectstats()
    {
        $sql = "SELECT DATE(`created_at`) AS `projectdate` , count(*) AS `daycount`  FROM `dwh`.`ods_project_v`   WHERE `status`>=40 GROUP BY  `projectdate`    ORDER BY `projectdate` DESC LIMIT 30";

        return $this->fetchAll($sql);
    }

    public function getPayout($yyyymm)
    {
        $sql = "SELECT * ,
          (SELECT `username` FROM `member` `m` WHERE `m`.`member_id` = `p`.`member_id`) `username`
        FROM `dwh`.`member_payout` `p` WHERE `yearmonth` = :yyyymm ORDER BY `amount` DESC";

        return $this->fetchAll($sql, array("yyyymm" => $yyyymm));
    }

    public function getPayoutMemberPerCategory($yyyymm, $catid)
    {

        $ids = $this->fetchChildIds($catid);
        array_push($ids, $catid);
        $idstring = implode(',', $ids);
        $sql = "
           select * from
                                (
                                     select
                                       member_id    
                                      ,(select username from member m where m.member_id = v.member_id) username
                                      ,round(sum(probably_payout_amount)) as amount                            
                                     from member_dl_plings_v as v
                                    where project_category_id IN (" . $idstring . ") and v.yearmonth= :yyyymm
                                    group by v.member_id
                                    order by amount desc
                                ) tmp where amount>0
        ";
        $result = $this->fetchAll($sql, array("yyyymm" => $yyyymm));

        return $result;

    }

    private function fetchChildIds($nodeId, $isActive = true)
    {
        if (empty($nodeId) or $nodeId == '') {
            return array();
        }

        $inQuery = '?';
        if (is_array($nodeId)) {
            $inQuery = implode(',', array_fill(0, count($nodeId), '?'));
        }
        $whereActive = $isActive == true ? ' AND o.is_active = 1' : '';
        $sql = "
            SELECT o.project_category_id
                FROM dwh.stg_project_category AS n,
                    dwh.stg_project_category AS p,
                    dwh.stg_project_category AS o
               WHERE o.lft BETWEEN p.lft AND p.rgt
                 AND o.lft BETWEEN n.lft AND n.rgt
                 AND n.project_category_id IN ({$inQuery})
                 {$whereActive}
            GROUP BY o.lft, o.project_category_id
            HAVING COUNT(p.project_category_id)-2 > 0
            ORDER BY o.lft;
        ";

        $children = $this->fetchAll($sql, $nodeId);
        if (count($children)) {
            $result = $this->flattenArray($children);
            $result = $this->removeUnnecessaryValues($nodeId, $result);

            return $result;
        } else {
            return array();
        }
    }

    /**
     *
     * @flatten multi-dimensional array
     *
     * @param array $array
     *
     * @return array
     *
     */
    private function flattenArray(array $array)
    {
        $ret_array = array();
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value) {
            $ret_array[] = $value;
        }

        return $ret_array;
    }

    /**
     * @param array $nodeId
     * @param array $children
     *
     * @return array
     */
    private function removeUnnecessaryValues($nodeId, $children)
    {
        $nodeId = is_array($nodeId) ? $nodeId : array($nodeId);

        return array_diff($children, $nodeId);
    }

    public function getDownloadsDaily($numofmonthback)
    {
        $sql = "
                   SELECT 
                                      SUBSTR(`d`.`date_yyyymmdd`,1,6) AS `symbol`
                                      ,SUBSTR(`d`.`date_yyyymmdd`,7,8)*1 AS `date` 
                                      ,`d`.`count` AS `price`
                                      FROM `dwh`.`files_downloads_daily` AS `d`
                                      WHERE STR_TO_DATE(`date_yyyymmdd`,'%Y%m%d' ) >= (DATE_FORMAT(CURDATE(), '%Y-%m-01')- INTERVAL :numofmonthback MONTH)
                                      AND STR_TO_DATE(`date_yyyymmdd`,'%Y%m%d' )< CURDATE()
                    UNION

                    SELECT 
                     concat(SUBSTR(`d`.`date_yyyymmdd`,1,6),' payout') AS `symbol`
                     ,SUBSTR(`d`.`date_yyyymmdd`,7,8)*1 AS `date` 
                     ,`d`.`count` AS `price`
                     FROM `dwh`.`payout_daily` AS `d`
                     WHERE STR_TO_DATE(`date_yyyymmdd`,'%Y%m%d' ) >= (DATE_FORMAT(CURDATE(), '%Y-%m-01')- INTERVAL :numofmonthback MONTH)
                      AND STR_TO_DATE(`date_yyyymmdd`,'%Y%m%d' )< CURDATE()

            ";

        return $this->fetchAll($sql, array("numofmonthback" => $numofmonthback));
    }

    public function getDownloadsUndPayoutsDaily($yyyymm)
    {
        $sql = "
                   select 
                   concat(SUBSTR(d.date_yyyymmdd,1,6),' downloads') as symbol
                   ,SUBSTR(d.date_yyyymmdd,7,8)*1 as date 
                   ,d.count as price
                   from dwh.files_downloads_daily as d
                   where SUBSTR(d.date_yyyymmdd,1,6)=:yyyymm
                   union 
                   select 
                   concat(SUBSTR(d.date_yyyymmdd,1,6),' payouts') as symbol
                   ,SUBSTR(d.date_yyyymmdd,7,8)*1 as date 
                   ,d.count as price
                   from dwh.payout_daily as d
                   where SUBSTR(d.date_yyyymmdd,1,6)=:yyyymm

            ";

        return $this->fetchAll($sql, array("yyyymm" => $yyyymm));

    }

    public function getTopDownloadsPerDate($date)
    {
        $date_start = $date . ' 00:00:00';
        $date_end = $date . ' 23:59:59';
        $sql = "
                  select d.project_id
                  , count(1) as cnt 
                  ,(select p.title from project p where p.project_id = d.project_id) as ptitle
                  ,(select p.created_at from project p where p.project_id = d.project_id) as pcreated_at
                  ,(select c.title from category c, project p where p.project_id = d.project_id and p.project_category_id=c.project_category_id) as ctitle
                  ,(select username from member m , project p where m.member_id = p.member_id and p.project_id = d.project_id) as username                  
                  from dwh.files_downloads d
                  where d.downloaded_timestamp between :date_start and :date_end
                  group by d.project_id
                  order by cnt desc
                  limit 50
            ";

        return $this->fetchAll($sql, array("date_start" => $date_start, "date_end" => $date_end));

    }

    public function getTopDownloadsPerMonth($month, $catid)
    {

        $sd = $month . '-01';
        $date_start = date('Y-m-01', strtotime($sd)) . ' 00:00:00';
        $date_end = date('Y-m-t', strtotime($sd)) . ' 23:59:59';

        if ($catid == 0) {

            $sql = "select d.project_id
                      , sum(d.count) as cnt 
                      ,p.title  as ptitle
                      ,p.created_at as pcreated_at
                      ,(select c.title from category c where d.project_category_id=c.project_category_id) as ctitle
                      ,(select username from member m where m.member_id = p.member_id) as username                  
                      from dwh.files_downloads_project_daily d
                        join project p on d.project_id = p.project_id
                      where d.yyyymm = :month
                      group by d.project_id,d.project_category_id,p.member_id
                      order by cnt desc
                      limit 50";
        } else {
            $ids = $this->fetchChildIds($catid);
            array_push($ids, $catid);
            $idstring = implode(',', $ids);

            $sql = 'select d.project_id
                        , sum(d.count) as cnt 
                        ,p.title  as ptitle
                        ,p.created_at as pcreated_at
                        ,(select c.title from category c where d.project_category_id=c.project_category_id) as ctitle
                        ,(select username from member m where m.member_id = p.member_id) as username                  
                        from dwh.files_downloads_project_daily d
                          join project p on d.project_id = p.project_id
                        where d.yyyymm = :month
                        and d.project_category_id in (' . $idstring . ')
                        group by d.project_id,d.project_category_id,p.member_id
                        order by cnt desc
                        limit 50';
        }

        return $this->fetchAll($sql, array("month" => $month));

    }

    public function getProductMonthly($project_id)
    {
        $sql = "
                select 
                yyyymm as yearmonth
               ,sum(count) as amount
               from dwh.files_downloads_project_daily
               where project_id = :project_id
               group by yyyymm
               limit 100
        ";

        return $this->fetchAll($sql, array("project_id" => $project_id));
    }

    public function getProductDayly($project_id)
    {
        $sql = "
              select yyyymmdd as yearmonth,count as amount 
              from dwh.files_downloads_project_daily
              where project_id = :project_id
              order by yyyymmdd desc
              limit 1000
        ";

        return $this->fetchAll($sql, array("project_id" => $project_id));
    }

    public function getDownloadsDomainStati($begin, $end)
    {
        $date_start = $begin . ' 00:00:00';
        $date_end = $end . ' 23:59:59';
        $sql = "
                  select count(1) as cnt 
                      ,d.referer_domain   
                        ,is_from_own_domain
                      from dwh.files_downloads d
                      where d.downloaded_timestamp  between :date_start and :date_end  
                       group by d.referer_domain,is_from_own_domain   
                      order by is_from_own_domain desc, cnt desc
            ";
        $result = $this->fetchAll($sql, array("date_start" => $date_start, "date_end" => $date_end));

        return $result;
    }

    public function getPayoutCategoryMonthly($yyyymm)
    {
        $sql = "
                          select * from
                          (
                            select project_category_id
                                ,(select title from category as c where c.project_category_id = v.project_category_id) as title
                                ,round(sum(probably_payout_amount)) as amount                                
                                ,sum(v.num_downloads) as num_downloads
                             from member_dl_plings_v as v
                            where yearmonth =:yyyymm
                            group by v.project_category_id
                            order by amount desc
                          ) tmp where amount>0
                        ";
        $result = $this->fetchAll($sql, array("yyyymm" => $yyyymm));

        return $result;
    }

    public function getPayoutCategory($catid)
    {

        if ($catid == 0) {

            $pids = $this->configStoreCategoryRepository->fetchCatIdsForStore(self::DEFAULT_STORE_ID);
            $sql = "
                            select * from
                            (
                                 select
                                  'All' as symbol
                                  ,yearmonth
                                  ,round(sum(probably_payout_amount)) as amount                                  
                                 from member_dl_plings_v as v                          
                                group by v.yearmonth
                                order by yearmonth asc
                            ) tmp where amount>0
                          ";
            $result = $this->_db->fetchAll($sql);
            foreach ($pids as $c) {
                $tmp = self::getPayoutCategorySingle($c);
                foreach ($result as &$row) {
                    $row['amount' . $c] = 0;
                    foreach ($tmp as $t) {
                        if ($t['yearmonth'] == $row['yearmonth']) {
                            $row['amount' . $c] = $t['amount'];
                            break;
                        }
                    }
                }
            }
        } else {
            $result = self::getPayoutCategorySingle($catid);
            $pids = $this->projectCategoryRepository->fetchImmediateChildrenIds($catid);
            foreach ($pids as $c) {
                $tmp = self::getPayoutCategorySingle($c);
                foreach ($result as &$row) {
                    $row['amount' . $c] = 0;
                    foreach ($tmp as $t) {
                        if ($t['yearmonth'] == $row['yearmonth']) {
                            $row['amount' . $c] = $t['amount'];
                            break;
                        }
                    }
                }
            }
        }

        return $result;

    }

    /*
    private function fetchCatIdsForStore($store_id)
    {
        $sql = "
            SELECT csc.project_category_id 
            FROM config_store_category AS csc
            JOIN project_category AS pc ON pc.project_category_id = csc.project_category_id
            WHERE csc.store_id = :store_id
            AND csc.deleted_at IS NULL
             ORDER BY csc.`order`, pc.title
        ";
        $results = $this->_db->fetchAll($sql, array('store_id' => $store_id));
        $values = array_map(function($row) { return $row['project_category_id']; }, $results);
        return $values;
    }
    */

    private function getPayoutCategorySingle($catid)
    {
        $ids = $this->fetchChildIds($catid);

        array_push($ids, $catid);
        $idstring = implode(',', $ids);
        $sql = "
                      select * from
                      (
                           select
                             yearmonth
                              ,(select title from category as c where c.project_category_id = " . $catid . ") as symbol                            
                            ,round(sum(probably_payout_amount)) as amount                            
                           from member_dl_plings_v as v
                          where project_category_id IN (" . $idstring . ")
                          group by v.yearmonth
                          order by yearmonth asc
                      ) tmp where amount>0
                    ";
        $result = $this->fetchAll($sql);

        return $result;
    }

    public function getPayoutyear()
    {
        $sql = "select round(sum(amount)) amount,yearmonth from dwh.member_payout group by yearmonth order by yearmonth";
        $result = $this->fetchAll($sql);

        return $result;
    }

    public function getPayoutOfMember($member_id)
    {
        $sql = "select yearmonth, amount from dwh.member_payout where member_id = :member_id order by yearmonth asc";
        $result = $this->fetchAll($sql, array("member_id" => $member_id));

        return $result;
    }

    public function getProject($project_id)
    {
        $sql = "SELECT * FROM ods_project_v WHERE project_id = :projectId";
        $result = $this->fetchAll($sql, array('projectId' => $project_id));

        return $result;
    }

    public function getProjects($limit = 50)
    {
        $limit = (int)$limit;
        $sql = "SELECT * FROM ods_project_v LIMIT {$limit}";
        $result = $this->fetchAll($sql);

        return $result;
    }

    public function getMember($member_id)
    {
        $sql = "SELECT * FROM ods_member_v WHERE member_id = :memberId";
        $result = $this->fetchAll($sql, array('memberId' => (int)$member_id));

        return $result;
    }

    public function getMembers($limit = 50)
    {
        $sql = "SELECT * FROM ods_member_v LIMIT {$limit}";
        $result = $this->fetchAll($sql);

        return $result;
    }

    public function getNewcomer($yyyymm)
    {
        $yyyymm_vor = $this->getLastYearMonth($yyyymm);
        $sql = "SELECT member_id
                    , (select username from member m where m.member_id = member_payout.member_id) as username
                    , paypal_mail,round(amount,2) as amount FROM member_payout WHERE yearmonth =:yyyymm
                    and member_id not in (select member_id from member_payout where yearmonth =:yyyymm_vor)
                    order by amount desc
                    ";

        return $this->fetchAll($sql, array("yyyymm" => $yyyymm, "yyyymm_vor" => $yyyymm_vor));
    }

    public function getNewloser($yyyymm)
    {
        $yyyymm_vor = $this->getLastYearMonth($yyyymm);
        $sql = "SELECT member_id
                    , (select username from member m where m.member_id = member_payout.member_id) as username
                    , paypal_mail,round(amount,2) as amount FROM member_payout WHERE yearmonth =:yyyymm_vor
                    and member_id not in (select member_id from member_payout where yearmonth =:yyyymm)
                    order by amount desc
                    ";

        return $this->fetchAll($sql, array("yyyymm" => $yyyymm, "yyyymm_vor" => $yyyymm_vor));
    }

    public function getNewprojectWeeklystatsWithoutWallpapers()
    {
        $tmpsql = "select lft, rgt from stat_cat_tree where project_category_id=295";
        $wal = $this->fetchRow($tmpsql);

        $lft = $wal['lft'];
        $rgt = $wal['rgt'];

        $sql = "SELECT YEARWEEK(`created_at`) as yyyykw , count(*) as amount  
                  FROM project p
                  join stat_cat_tree t on p.project_category_id = t.project_category_id                
                  where status=100 and type_id = 1                                     
                  and (t.lft<" . $lft . " or t.rgt>" . $rgt . " )
                  group by  yyyykw    
                  order by yyyykw 
                  desc limit 60";

        return $this->fetchAll($sql);

    }

    public function getNewprojectWeeklystatsWallpapers()
    {

        $tmpsql = "select lft, rgt from stat_cat_tree where project_category_id=295";
        $wal = $this->fetchRow($tmpsql);
        $lft = $wal['lft'];
        $rgt = $wal['rgt'];

        $sql = "SELECT YEARWEEK(`created_at`) as yyyykw , count(*) as amount  
                  FROM project p
                  join stat_cat_tree t on p.project_category_id = t.project_category_id                
                  where status=100 and type_id = 1                                     
                  and (t.lft>" . $lft . " and t.rgt<" . $rgt . " )
                  group by  yyyykw    
                  order by yyyykw 
                  desc limit 60";

        return $this->fetchAll($sql);

    }

    public function getMonthDiff($yyyymm)
    {
        $yyyymm_vor = $this->getLastYearMonth($yyyymm);
        $sql = "
                        select akt.member_id          
                             , (select username from member m where m.member_id = akt.member_id) as username
                             , akt.amount as am_akt
                             , let.amount as am_let
                             , round(akt.amount-let.amount) as am_diff
                             , akt.yearmonth ym_akt
                             , let.yearmonth ym_let
                        from
                        (select member_id, amount,yearmonth from  member_payout where yearmonth = :yyyymm) akt,
                        (select member_id, amount,yearmonth from  member_payout where yearmonth = :yyyymm_vor) let
                        where akt.member_id = let.member_id
                        order by am_diff desc
                    ";

        return $this->fetchAll($sql,array("yyyymm" => $yyyymm, "yyyymm_vor" => $yyyymm_vor));
    }
}