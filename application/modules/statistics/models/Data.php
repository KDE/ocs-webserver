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
 * Created: 31.07.2017
 */

class Statistics_Model_Data
{

    const DEFAULT_STORE_ID = 22; //opendesktop

    /** @var Zend_Db_Adapter_Pdo_Abstract */
    protected $_db;

    public  function __construct($options)  {
        if (isset($options['db'])) {
            $this->initDbAdapter($options['db']);
        } else {
            throw new Exception('configuration parameter for database connection needed');
        }
    }

    private function initDbAdapter($db)
    {
        $adapter = $db['adapter'];
        $params = $db['params'];
        //$default = (int)(isset($params['isDefaultTableAdapter']) && $params['isDefaultTableAdapter']
        //    || isset($params['default']) && $params['default']);
        unset($params['adapter'], $params['default'], $params['isDefaultTableAdapter']);
        $adapter = Zend_Db::factory($adapter, $params);
        $this->_db = $adapter;
    }

    


    public function getPayoutgroupbyamountProduct()
    {
       $sql="select gm as x
            , count(1) as y
            from
            (
            select 
            m.project_id,
            round(sum(m.credits_plings)/100,2) AS probably_payout_amount,
            floor(sum(m.credits_plings/100)/10)*10 gm
            from micro_payout m
            where m.paypal_mail is not null and m.paypal_mail <> '' and (m.paypal_mail regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$')            
            and m.yearmonth = DATE_FORMAT(CURRENT_DATE() - INTERVAL 4 MONTH, '%Y%m')  and m.is_license_missing = 0 and m.is_source_missing=0 and m.is_pling_excluded = 0 
            and m.is_member_pling_excluded=0
            GROUP BY m.project_id
            ) t
            group by gm
            order by gm asc
            ";
          $result = $this->_db->fetchAll($sql);          
          return $result;  
    }


    public function getPayoutgroupbyamountMember()
    {
       $sql="select gm as x
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
            group by gm
            order by gm asc
            ";
          $result = $this->_db->fetchAll($sql);
          return $result;  
    }

    public function getNewmemberstats(){
          $sql = "SELECT DATE(`created_at`) as memberdate , count(*) as daycount FROM dwh.ods_member_v  group by  memberdate    order by memberdate desc limit 30";
          $result = $this->_db->fetchAll($sql);
          return $result;  
    }
   
    public function getNewprojectstats(){        
          $sql = "SELECT DATE(`created_at`) as projectdate , count(*) as daycount  FROM dwh.ods_project_v   where status>=40 group by  projectdate    order by projectdate desc limit 30";
          $result = $this->_db->fetchAll($sql);
          return $result;  
    }

    public function getNewprojectWeeklystats(){        
          $sql = "SELECT YEARWEEK(`created_at`) as yyyykw , count(*) as amount  
                  FROM project p
                  join stat_cat_tree t on p.project_category_id = t.project_category_id                
                  where status=100 and type_id = 1 
                  group by  yyyykw    
                  order by yyyykw 
                  desc limit 60";
          $result = $this->_db->fetchAll($sql);
          return $result;  
    }
    public function getNewprojectWeeklystatsWithoutWallpapers(){       

               $tmpsql = "select lft, rgt from stat_cat_tree where project_category_id=295";
               $wal = $this->_db->fetchRow($tmpsql);            
               $lft = $wal['lft'];
               $rgt = $wal['rgt'];

          $sql = "SELECT YEARWEEK(`created_at`) as yyyykw , count(*) as amount  
                  FROM project p
                  join stat_cat_tree t on p.project_category_id = t.project_category_id                
                  where status=100 and type_id = 1                                     
                  and (t.lft<".$lft." or t.rgt>".$rgt." )
                  group by  yyyykw    
                  order by yyyykw 
                  desc limit 60";
          $result = $this->_db->fetchAll($sql);
          return $result;  
    }

    public function getNewprojectWeeklystatsWallpapers(){    

              $tmpsql = "select lft, rgt from stat_cat_tree where project_category_id=295";
               $wal = $this->_db->fetchRow($tmpsql);            
               $lft = $wal['lft'];
               $rgt = $wal['rgt'];

          $sql = "SELECT YEARWEEK(`created_at`) as yyyykw , count(*) as amount  
                  FROM project p
                  join stat_cat_tree t on p.project_category_id = t.project_category_id                
                  where status=100 and type_id = 1                                     
                  and (t.lft>".$lft." and t.rgt<".$rgt." )
                  group by  yyyykw    
                  order by yyyykw 
                  desc limit 60";
          $result = $this->_db->fetchAll($sql);
          return $result;  
    }
    
    public function getPayout($yyyymm){
        
        $sql = "SELECT * ,
          (select username from member m where m.member_id = p.member_id) username
        FROM dwh.member_payout p where yearmonth = :yyyymm order by amount desc";
        $result = $this->_db->fetchAll($sql, array("yyyymm"=>$yyyymm));
        return $result;  
    }

    public function getPayoutMemberPerCategory($yyyymm,$catid){
        
        $modelProjectCategories = new Default_Model_DbTable_ProjectCategory();
        $ids = $modelProjectCategories->fetchChildIds($catid);
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
                                    where project_category_id IN (".$idstring.") and v.yearmonth= :yyyymm
                                    group by v.member_id
                                    order by amount desc
                                ) tmp where amount>0
        ";
        $result = $this->_db->fetchAll($sql, array("yyyymm"=>$yyyymm));
        return $result;  
    }

    public function getNewcomer($yyyymm){
        $yyyymm_vor = $this->getLastYearMonth($yyyymm);
        $sql = "SELECT member_id
                    , (select username from member m where m.member_id = member_payout.member_id) as username
                    , paypal_mail,round(amount,2) as amount FROM member_payout WHERE yearmonth =:yyyymm
                    and member_id not in (select member_id from member_payout where yearmonth =:yyyymm_vor)
                    order by amount desc
                    ";        
        $result = $this->_db->fetchAll($sql, array("yyyymm"=>$yyyymm, "yyyymm_vor"=>$yyyymm_vor));
        return $result;  
    }

    public function getNewloser($yyyymm){
        $yyyymm_vor = $this->getLastYearMonth($yyyymm);
        $sql = "SELECT member_id
                    , (select username from member m where m.member_id = member_payout.member_id) as username
                    , paypal_mail,round(amount,2) as amount FROM member_payout WHERE yearmonth =:yyyymm_vor
                    and member_id not in (select member_id from member_payout where yearmonth =:yyyymm)
                    order by amount desc
                    ";        
        $result = $this->_db->fetchAll($sql, array("yyyymm"=>$yyyymm, "yyyymm_vor"=>$yyyymm_vor));
        return $result;  
    }

    public function getMonthDiff($yyyymm){
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
            $result = $this->_db->fetchAll($sql, array("yyyymm"=>$yyyymm, "yyyymm_vor"=>$yyyymm_vor));

            return $result;  
    }

/*
    public function getDownloadsDaily($numofmonthback){
        $sql = "
                   select 
                                      SUBSTR(d.date_yyyymmdd,1,6) as symbol
                                      ,SUBSTR(d.date_yyyymmdd,7,8)*1 as date 
                                      ,d.count as price
                                      from dwh.files_downloads_daily as d
                                      where STR_TO_DATE(date_yyyymmdd,'%Y%m%d' ) >= (DATE_FORMAT(CURDATE(), '%Y-%m-01')- INTERVAL :numofmonthback MONTH)
                                      and STR_TO_DATE(date_yyyymmdd,'%Y%m%d' )< CURDATE()
                                      order by date_yyyymmdd asc
            ";
        $result = $this->_db->fetchAll($sql,array("numofmonthback"=>$numofmonthback));
        return $result;  
    }
*/

    public function getDownloadsDaily($numofmonthback){
        $sql = "
                   select 
                                      SUBSTR(d.date_yyyymmdd,1,6) as symbol
                                      ,SUBSTR(d.date_yyyymmdd,7,8)*1 as date 
                                      ,d.count as price
                                      from dwh.files_downloads_daily as d
                                      where STR_TO_DATE(date_yyyymmdd,'%Y%m%d' ) >= (DATE_FORMAT(CURDATE(), '%Y-%m-01')- INTERVAL :numofmonthback MONTH)
                                      and STR_TO_DATE(date_yyyymmdd,'%Y%m%d' )< CURDATE()
                    union

                    select 
                     concat(SUBSTR(d.date_yyyymmdd,1,6),' payout') as symbol
                     ,SUBSTR(d.date_yyyymmdd,7,8)*1 as date 
                     ,d.count as price
                     from dwh.payout_daily as d
                     where STR_TO_DATE(date_yyyymmdd,'%Y%m%d' ) >= (DATE_FORMAT(CURDATE(), '%Y-%m-01')- INTERVAL :numofmonthback MONTH)
                      and STR_TO_DATE(date_yyyymmdd,'%Y%m%d' )< CURDATE()

            ";
        $result = $this->_db->fetchAll($sql,array("numofmonthback"=>$numofmonthback));
        return $result;  
    }

    public function getDownloadsUndPayoutsDaily($yyyymm){
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
        $result = $this->_db->fetchAll($sql,array("yyyymm"=>$yyyymm));
        return $result;  
    }


/**
                  ,(select count(1) from dwh.files_downloads dd where dd.project_id = d.project_id 
              and dd.downloaded_timestamp between  :date_start and :date_end
              and dd.referer like 'https://www.google%') as cntGoogle
*/

    public function getTopDownloadsPerDate($date){
            $date_start =$date.' 00:00:00';
            $date_end =$date.' 23:59:59';    
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
           
            $result = $this->_db->fetchAll($sql,array("date_start"=>$date_start,"date_end"=>$date_end));
            return $result;             
    }

    public function getTopDownloadsPerMonth($month,$catid){

            $sd = $month.'-01';
            $date_start =date('Y-m-01', strtotime($sd)).' 00:00:00';
            $date_end =date('Y-m-t', strtotime($sd)).' 23:59:59';    
            
            if($catid==0)
            {

              // $sql = "
              //         select d.project_id
              //           , count(1) as cnt 
              //           ,(select p.title from project p where p.project_id = d.project_id) as ptitle
              //           ,(select p.created_at from project p where p.project_id = d.project_id) as pcreated_at
              //           ,(select c.title from category c where d.project_category_id=c.project_category_id) as ctitle
              //           ,(select username from member m where m.member_id = d.member_id) as username                  
              //           from dwh.files_downloads d
              //           where d.yyyymm = :month
              //           group by d.project_id,project_category_id,member_id
              //           order by cnt desc
              //           limit 50
              // ";
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
                   
            }else
            {
                $modelProjectCategories = new Default_Model_DbTable_ProjectCategory();
                $ids = $modelProjectCategories->fetchChildIds($catid);
                array_push($ids, $catid);            
                $idstring = implode(',', $ids); 
                // $sql = '
                //         select d.project_id
                //         , count(1) as cnt 
                //         ,(select p.title from project p where p.project_id = d.project_id) as ptitle
                //         ,(select p.created_at from project p where p.project_id = d.project_id) as pcreated_at
                //         ,(select c.title from category c where d.project_category_id=c.project_category_id) as ctitle
                //         ,(select username from member m where m.member_id = d.member_id) as username                  
                //         from dwh.files_downloads d
                //         where d.yyyymm = :month
                //         and d.project_category_id in ('.$idstring.')
                //         group by d.project_id,project_category_id,member_id
                //         order by cnt desc
                //         limit 50
                // ';       
                $sql = 'select d.project_id
                        , sum(d.count) as cnt 
                        ,p.title  as ptitle
                        ,p.created_at as pcreated_at
                        ,(select c.title from category c where d.project_category_id=c.project_category_id) as ctitle
                        ,(select username from member m where m.member_id = p.member_id) as username                  
                        from dwh.files_downloads_project_daily d
                          join project p on d.project_id = p.project_id
                        where d.yyyymm = :month
                        and d.project_category_id in ('.$idstring.')
                        group by d.project_id,d.project_category_id,p.member_id
                        order by cnt desc
                        limit 50';
            }
           
            $result = $this->_db->fetchAll($sql,array("month"=>$month));
            return $result;             
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
        $result = $this->_db->fetchAll($sql,array("project_id"=>$project_id));
        return $result;   
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
        $result = $this->_db->fetchAll($sql,array("project_id"=>$project_id));        
        return  array_reverse($result);   
    }
    
 
    public function getDownloadsDomainStati($begin, $end){
            $date_start =$begin.' 00:00:00';
            $date_end =$end.' 23:59:59';    
            $sql = "
                  select count(1) as cnt 
                      ,d.referer_domain   
                        ,is_from_own_domain
                      from dwh.files_downloads d
                      where d.downloaded_timestamp  between :date_start and :date_end  
                       group by d.referer_domain,is_from_own_domain   
                      order by is_from_own_domain desc, cnt desc
            ";                  
            $result = $this->_db->fetchAll($sql,array("date_start"=>$date_start,"date_end"=>$date_end));
            return $result;             
    }


    public function getPayoutCategoryMonthly($yyyymm){
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
            $result = $this->_db->fetchAll($sql, array("yyyymm"=>$yyyymm));
            return $result;  
    }



    private function getPayoutCategorySingle($catid)
    {

        $modelProjectCategories = new Default_Model_DbTable_ProjectCategory();
        $ids = $modelProjectCategories->fetchChildIds($catid);
        array_push($ids, $catid);            
        $idstring = implode(',', $ids);
        // Zend_Registry::get('logger')->info(__METHOD__ . ' - ===================================' );
        // Zend_Registry::get('logger')->info(__METHOD__ . ' - ' . $idstring);
        $sql = "
                      select * from
                      (
                           select
                             yearmonth
                              ,(select title from category as c where c.project_category_id = ".$catid.") as symbol                            
                            ,round(sum(probably_payout_amount)) as amount                            
                           from member_dl_plings_v as v
                          where project_category_id IN (".$idstring.")
                          group by v.yearmonth
                          order by yearmonth asc
                      ) tmp where amount>0
                    ";
        $result = $this->_db->fetchAll($sql);
        return $result;
    }

     public function getPayoutCategory_($catid){

          if($catid==0)
          {                          
              $pids = array(152, 233,158, 148,491,445,295);
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
              foreach ($pids as $catid) {
                  $t = self::getPayoutCategorySingle($catid);    
                  $result = array_merge($result, $t);
              }                                        
          }
          else
          {
                $result = self::getPayoutCategorySingle($catid);                
          }
            
          return $result;  
   
    }

    public function getPayoutCategory($catid){

          if($catid==0)
          {                          
              // $pids = array(152, 233,158,404, 148,491,445,295);
              $modelCategoryStore = new Default_Model_DbTable_ConfigStoreCategory();
              $pids = $modelCategoryStore->fetchCatIdsForStore(self::DEFAULT_STORE_ID);
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
                     $row['amount'.$c] = 0;
                     foreach ($tmp as $t) {                    
                        if($t['yearmonth']==$row['yearmonth'])
                        {                          
                          $row['amount'.$c] = $t['amount'];
                          break;
                        }
                     }
                  }
              }                                        
          }
          else
          {
                $result = self::getPayoutCategorySingle($catid);                
                $modelCategoriesTable = new Default_Model_DbTable_ProjectCategory();
                $pids = $modelCategoriesTable->fetchImmediateChildrenIds($catid);
                foreach ($pids as $c) {
                  $tmp = self::getPayoutCategorySingle($c);                                 
                  foreach ($result as &$row) {                                                                            
                     $row['amount'.$c] = 0;
                     foreach ($tmp as $t) {                    
                        if($t['yearmonth']==$row['yearmonth'])
                        {                          
                          $row['amount'.$c] = $t['amount'];
                          break;
                        }
                     }
                  }
                } 
          }
            
          return $result;  
    
    }

    public function _getPayoutCategory($catid){

          if($catid==0)
          {                          
              $pids = array(152, 233,158,404, 148,491,445,295);
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
                     $row['amount'.$c] = 0;
                     foreach ($tmp as $t) {                    
                        if($t['yearmonth']==$row['yearmonth'])
                        {                          
                          $row['amount'.$c] = $t['amount'];
                          break;
                        }
                     }
                  }
              }                                        
          }
          else
          {
                $result = self::getPayoutCategorySingle($catid);                
          }
            
          return $result;  
   
    }


    public function getLastYearMonth($yyyymm){
        $aktdate = strval($yyyymm).'01';
        $fmt = 'Ymd';
        $d = DateTime::createFromFormat($fmt,  $aktdate);
        $d->modify( 'last day of previous month' );     
        return $d->format( 'Ym' );
    }

    public function getPayoutyear(){    
        $sql = "select round(sum(amount)) amount,yearmonth from dwh.member_payout group by yearmonth order by yearmonth";
        $result = $this->_db->fetchAll($sql);
        return $result;  
    }


    public function getPayoutOfMember($member_id){       
        //$sql = "select yearmonth, amount from dwh.member_payout where member_id = :member_id order by yearmonth asc";
      $sql = "select yearmonth, amount from dwh.member_payout where member_id = :member_id order by yearmonth asc";
        $result = $this->_db->fetchAll($sql, array("member_id"=>$member_id));
        return $result;  
    }
    
    public function getProject($project_id)
    {
        $sql = "SELECT * FROM ods_project_v WHERE project_id = :projectId";
        $result = $this->_db->fetchAll($sql, array('projectId' => $project_id));
        return $result;
    }

    public function getProjects($limit = 50)
    {
        $limit = (int)$limit;
        $sql = "SELECT * FROM ods_project_v LIMIT {$limit}";
        $result = $this->_db->fetchAll($sql);
        return $result;
    }

    public function getMember($member_id)
    {
        $sql = "SELECT * FROM ods_member_v WHERE member_id = :memberId";
        $result = $this->_db->fetchAll($sql, array('memberId' => (int)$member_id));
        return $result;
    }
    

    public function getMembers($limit = 50)
    {
        $sql = "SELECT * FROM ods_member_v";
        $sql = $this->_db->limit($sql, (int)$limit);
        $result = $this->_db->fetchAll($sql);
        return $result;
    }

}