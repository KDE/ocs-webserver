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
    
    public function getPayout($yyyymm){
        
        $sql = "SELECT * FROM dwh.member_payout where yearmonth = :yyyymm order by amount desc";
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

    public function getTopDownloadsPerDate($date){
            $date_start =$date.' 00:00:00';
            $date_end =$date.' 23:59:59';    
            $sql = "
                  select d.project_id
                  , count(1) as cnt 
                  ,(select p.title from project p where p.project_id = d.project_id) as ptitle
                  ,(select p.created_at from project p where p.project_id = d.project_id) as pcreated_at
                  ,(select c.title from category c, project p where p.project_id = d.project_id and p.project_category_id=c.project_category_id) as ctitle
                  from dwh.files_downloads d
                  where downloaded_timestamp :date_start and :date_end
                  group by project_id
                  order by cnt desc
                  limit 50
            ";       
           
            $result = $this->_db->fetchAll($sql,array("date_start"=>$date_start,"date_end"=>$date_end));
            return $result;             
    }


    public function getPayoutCategoryMonthly($yyyymm){
            $sql = "
                            select project_category_id
                                ,(select title from category as c where c.project_category_id = v.project_category_id) as title
                                ,round(sum(probably_payout_amount)) as amount
                                ,count(*) anzahlproject
                                ,sum(probably_payout_amount)/count(*) avgamount
                                ,sum(v.num_downloads) as num_downloads
                             from member_dl_plings_v as v
                            where yearmonth =:yyyymm
                            group by v.project_category_id
                            order by amount desc
                        ";
            $result = $this->_db->fetchAll($sql, array("yyyymm"=>$yyyymm));
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
        $sql = "select * from dwh.member_payout where member_id = :member_id order by yearmonth desc";
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