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
class SpamController extends Local_Controller_Action_DomainSwitch
{
	const RESULT_OK = "OK";
	const RESULT_ERROR = "ERROR";
	const IDENTIFIER = 'comment_id';
	
    public function indexAction()
    {
        $this->forward('list', 'spam', 'default', $this->getAllParams());
    }

    public function listAction()
    {
    	$this->view->headTitle('Spam','SET');
        $this->view->page = (int)$this->getParam('page', 1);
    }

    public function commentsAction()
    {	
    	$this->view->headTitle('Spam - Comments','SET');
    	
    }
     public function productAction()
    {   
        $this->view->headTitle('Spam - Products-10-files','SET');
        
    }

    public function newproductAction()
    {   
        $this->view->headTitle('Spam - new products < 2 months','SET');
        
    }
    public function unpublishedproductAction()
    {
        $this->view->headTitle('Spam - Unpublished Products','SET');
    }

    public function paypalAction()
    {   
        $this->view->headTitle('Spam - Paypal','SET');
    }
    public function mdsumAction()
    {   
        $this->view->headTitle('Md5sum - Duplicated','SET');
    }


    public function deletecommentAction()
    {
        $commentId = (int)$this->getParam(self::IDENTIFIER, null);

        $model = new Default_Model_DbTable_Comments();
        $record = $model->find($commentId)->current();
        $record->comment_active = 0;
        $record->save();

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Record'] = $record->toArray();
        $this->_helper->json($jTableResult);
    }

    public function deletereportsAction()
    {
        $commentId = (int)$this->getParam(self::IDENTIFIER, null);              
	    $sql = '
	            UPDATE reports_comment
	            SET is_deleted = 1
	            WHERE comment_id = :comment_id';
	    Zend_Db_Table::getDefaultAdapter()->query($sql, array('comment_id' => $commentId))->execute();        
        
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Record'] = array();
        $this->_helper->json($jTableResult);
    }

    public function paypallistAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');     

        if(!isset($sorting))
        {
            $sorting = ' paypal_mail ';
        }        

        $sql = "
                    select a.*, 
                    (       select sum(d.credits_plings)/100  amount
                            from micro_payout d
                            where d.member_id in (a.ids)
                            and d.yearmonth= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')
                            and d.is_pling_excluded = 0 
                            and d.is_license_missing = 0
                            and d.is_member_pling_excluded = 0
                            and d.is_source_missing = 0
                            ) as amount
                    from
                    (
                        select paypal_mail, GROUP_CONCAT(member_id) ids, GROUP_CONCAT(username) names
                        , count(1) cnt 
                        , GROUP_CONCAT(m.is_deleted) is_deleted    
                        ,max(m.created_at) as created_at    
                        ,sum(m.is_deleted) as sum_is_deleted
                        from member m
                        where  m.paypal_mail is not null and m.paypal_mail <> '' and (m.paypal_mail regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') 
                        group by paypal_mail   
                        order by m.created_at desc
                    ) a
                    where  cnt > 1 and cnt>sum_is_deleted
                    
        
                    
                ";
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;
        
        $results = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);                        

        $sqlall = "  select count(1) cnt from
                    (
                        select paypal_mail, GROUP_CONCAT(member_id) ids, GROUP_CONCAT(username) names
                        , count(1) cnt 
                        , GROUP_CONCAT(m.is_deleted) is_deleted    
                        ,max(m.created_at) as created_at    
                        ,sum(m.is_deleted) as sum_is_deleted
                        from member m
                        where  m.paypal_mail is not null and m.paypal_mail <> '' and (m.paypal_mail regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') 
                        group by paypal_mail  
                    ) a
                    where  cnt > 1 and cnt>sum_is_deleted";         

        $reportsAll = Zend_Db_Table::getDefaultAdapter()->fetchRow($sqlall);


        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $results;        
        $jTableResult['TotalRecordCount'] = $reportsAll['cnt'];
        $this->_helper->json($jTableResult);

    }

    public function mdsumlistAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');     

        if(!isset($sorting))
        {
            $sorting = ' changed_at desc ';
        }        

        $sql = "
                    select 
                    * 
                    from 
                    (
                        select f.owner_id as member_id,m.username, f.md5sum, COUNT(1) cnt, GROUP_CONCAT(distinct p.project_id) as projects
                        , count(distinct p.project_id) cntProjects
                        ,max(p.changed_at) as changed_at
                        from  ppload.ppload_files f
                        join project p on f.collection_id = p.ppload_collection_id
                        join member m on f.owner_id = m.member_id and m.is_deleted=0 and m.is_active = 1
                        where f.md5sum is not null
                        group by f.md5sum 
                        having count(1)>1
                    ) t
                    where cntProjects>1                                    
                ";
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;
        
        $results = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);                        

        $sqlall = " select 
                        count(1) as cnt
                        from 
                        (
                            select f.owner_id as member_id,m.username, f.md5sum, COUNT(1) cnt, GROUP_CONCAT(distinct p.project_id) as projects
                            , count(distinct p.project_id) cntProjects
                            from  ppload.ppload_files f
                            join project p on f.collection_id = p.ppload_collection_id
                            join member m on f.owner_id = m.member_id and m.is_deleted=0 and m.is_active = 1
                            where f.md5sum is not null
                            group by f.md5sum 
                            having count(1)>1
                        ) t
                        where cntProjects>1
                  ";         

        $reportsAll = Zend_Db_Table::getDefaultAdapter()->fetchRow($sqlall);


        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $results;        
        $jTableResult['TotalRecordCount'] = $reportsAll['cnt'];
        $this->_helper->json($jTableResult);

    }

    public function newproductlistAction()
    {   
        $filter['filter_section'] = $this->getParam('filter_section');

        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');     

        if(!isset($sorting))
        {
            $sorting = ' earn desc, created_at desc';
        }        

        $sql = "
                select ss.section_id, ss.name as section_name, pp.project_id,pp.status,pp.member_id, pp.created_at, m.username, m.paypal_mail,m.created_at as member_since, c.title cat_title,c.lft, c.rgt
                ,(select sum(probably_payout_amount) amount
                from member_dl_plings pl
                where pl.project_id=pp.project_id
                and pl.member_id = pp.member_id
                and pl.yearmonth= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')
                and pl.is_pling_excluded = 0 
                and pl.is_license_missing = 0
                ) as earn                    
                from
                project pp                    
                ,member m
                ,project_category c
                ,section_category s
                ,section ss
                where pp.member_id = m.member_id 
                and pp.project_category_id = c.project_category_id and m.is_deleted=0 and m.is_active = 1
                and s.project_category_id = c.project_category_id
                and s.section_id = ss.section_id
                and pp.created_at > (CURRENT_DATE() - INTERVAL 2 MONTH)                                            
                                        
        ";
        if($filter['filter_section'])
        {
            $sql.=" and ss.section_id = ".$filter['filter_section'];
        }
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;
        $printDateSince = new Default_View_Helper_PrintDateSince();
        $filesize = new Default_View_Helper_HumanFilesize();
        $results = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
                
        $tmpsql = "select lft, rgt from project_category where project_category_id=295";
        $wal =Zend_Db_Table::getDefaultAdapter()->fetchRow($tmpsql);            
        $lft = $wal['lft'];
        $rgt = $wal['rgt'];
        foreach ($results as &$value) {
            $value['created_at'] = $printDateSince->printDateSince($value['created_at']);                
            if($value['earn'] && $value['earn']>0)
            {
                 $value['earn'] = number_format($value['earn'] , 2, '.', '');
            }             
            if($value['lft'] >= $lft && $value['rgt'] <= $rgt)
            {
                $value['is_wallpaper'] = 1;
            }else{
                $value['is_wallpaper'] = 0;
            }
        }

        $sqlTotal = "select count(1) as cnt
                from
                project pp                    
                ,member m
                ,project_category c
                ,section_category s
                ,section ss
                where pp.member_id = m.member_id 
                and pp.project_category_id = c.project_category_id and m.is_deleted=0 and m.is_active = 1
                and s.project_category_id = c.project_category_id
                and s.section_id = ss.section_id
                and pp.created_at > (CURRENT_DATE() - INTERVAL 2 MONTH)   ";

        if($filter['filter_section'])
        {
            $sqlTotal.=" and ss.section_id = ".$filter['filter_section'];
        }
        $resultsCnt = Zend_Db_Table::getDefaultAdapter()->fetchRow($sqlTotal);
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $results;        
        $jTableResult['TotalRecordCount'] = $resultsCnt['cnt'];
        $this->_helper->json($jTableResult);

    }


    public function unpublishedproductlistAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');     

        if(!isset($sorting))
        {
            $sorting = ' unpublished_time desc';
        }        

        $sql = "
                    select pp.project_id,pp.title,pp.status,pp.member_id, pp.created_at, m.username, m.paypal_mail,m.created_at as member_since, c.title cat_title,c.lft, c.rgt
                    ,(select sum(probably_payout_amount) amount
                    from member_dl_plings 
                    where member_id=pp.member_id
                    and yearmonth= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')
                    and is_pling_excluded = 0 
                    and is_license_missing = 0
                    ) as earn ,
                    (SELECT max(time) FROM pling.activity_log l where l.activity_type_id = 9 and project_id = pp.project_id) as unpublished_time
                    ,(
                        select  sum(m.credits_plings)/100 AS probably_payout_amount from micro_payout m
                        where m.project_id=pp.project_id 
                        and m.paypal_mail is not null 
                        and m.paypal_mail <> '' and (m.paypal_mail regexp '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') 
                        and m.yearmonth = DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')
                    ) as probably_payout_amount
                    from project pp                    
                    join member m on pp.member_id = m.member_id and m.is_deleted=0 and m.is_active = 1
                    join project_category c on pp.project_category_id = c.project_category_id        
                    where pp.status = 40 
                    
                                        
        ";
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;
        $printDateSince = new Default_View_Helper_PrintDateSince();
        $filesize = new Default_View_Helper_HumanFilesize();
        $results = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
                
        $tmpsql = "select lft, rgt from project_category where project_category_id=295";
        $wal =Zend_Db_Table::getDefaultAdapter()->fetchRow($tmpsql);            
        $lft = $wal['lft'];
        $rgt = $wal['rgt'];
        foreach ($results as &$value) {
            $value['created_at'] = $printDateSince->printDateSince($value['created_at']);   
            $value['unpublished_time'] = $printDateSince->printDateSince($value['unpublished_time']);              
            if($value['earn'] && $value['earn']>0)
            {
                 $value['earn'] = number_format($value['earn'] , 2, '.', '');
            }             
            if($value['lft'] >= $lft && $value['rgt'] <= $rgt)
            {
                $value['is_wallpaper'] = 1;
            }else{
                $value['is_wallpaper'] = 0;
            }
        }

        $sqltotal = "select count(1) as cnt from
                        project pp                                         
                    where pp.status = 40 ";
        $resultsCnt = Zend_Db_Table::getDefaultAdapter()->fetchRow($sqltotal);
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $results;        
        $jTableResult['TotalRecordCount'] = $resultsCnt['cnt'];
        $this->_helper->json($jTableResult);

    }

    public function productfilesAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');     

        if(!isset($sorting))
        {
            $sorting = ' created_at desc';
        }        

        $sql = "
                select pp.project_id,pp.status,pp.member_id, pp.created_at, cntfiles,size, m.username, m.paypal_mail,m.created_at as member_since, c.title cat_title,c.lft, c.rgt
                    ,(select sum(probably_payout_amount) amount
                    from member_dl_plings 
                    where member_id=pp.member_id
                    and yearmonth= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')
                    and is_pling_excluded = 0 
                    and is_license_missing = 0
                    ) as earn,
                    m.is_deleted 
                    from
                    (
                        select 
                        p.project_id,
                        p.created_at,
                        p.changed_at,
                        p.member_id,    
                        p.status,
                        p.project_category_id,
                        count(1) cntfiles,
                        sum(size) size
                        from 
                        project p,
                        ppload.ppload_files f
                        where p.ppload_collection_id = f.collection_id
                        group by p.project_id
                        order by p.created_at desc, cntfiles desc
                    )
                    pp 
                    ,member m
                    ,project_category c
                    where pp.member_id = m.member_id
                    and pp.project_category_id = c.project_category_id and m.is_deleted=0 and m.is_active = 1
                    and cntfiles > 10
        ";
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;
        $printDateSince = new Default_View_Helper_PrintDateSince();
        $filesize = new Default_View_Helper_HumanFilesize();
        $results = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
                
        $tmpsql = "select lft, rgt from project_category where project_category_id=295";
        $wal =Zend_Db_Table::getDefaultAdapter()->fetchRow($tmpsql);            
        $lft = $wal['lft'];
        $rgt = $wal['rgt'];
        foreach ($results as &$value) {
            $value['created_at'] = $printDateSince->printDateSince($value['created_at']);    
            $value['size'] = $filesize->humanFilesize($value['size']);  
            if($value['earn'] && $value['earn']>0)
            {
                 $value['earn'] = number_format($value['earn'] , 2, '.', '');
            }             
            if($value['lft'] >= $lft && $value['rgt'] <= $rgt)
            {
                $value['is_wallpaper'] = 1;
            }else{
                $value['is_wallpaper'] = 0;
            }
        }

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $results;        
        $jTableResult['TotalRecordCount'] = 1000;
        $this->_helper->json($jTableResult);

    }
    public function commentslistAction()
    {
    	
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');     
        $filter_year = $this->getParam('filter_year', date("Y"));     

        if(!isset($sorting))
        {
        	$sorting = ' comment_created_at desc';
        }        
        $sql = "
    			select 
                comment_id,
                comment_target_id,
                comment_member_id,
                comment_parent_id,
                comment_text,
                comment_created_at,
                (select count(1) from reports_comment r where c.comment_id = r.comment_id ) cntreport,
                (select GROUP_CONCAT(distinct reported_by) from reports_comment r where c.comment_id = r.comment_id order by created_at desc ) as reportedby,
                  (
                  SELECT count(1) AS count FROM comments c2
                  where c2.comment_target_id <> 0 and c2.comment_member_id = c.comment_member_id and c2.comment_active = 1 
                  ) as cntComments,
                  m.created_at member_since,
                  m.username,
                  (select count(1) from project p where p.status=100 and p.member_id=m.member_id and p.type_id = 1 and p.is_deleted=0) cntProjects,
                  m.profile_image_url,
                  (select description from project p where p.type_id=0 and p.member_id = c.comment_member_id) aboutme
                from comments c
                join member m on c.comment_member_id = m.member_id and m.is_active = 1 and m.is_deleted = 0
                where c.comment_type=0
                and c.comment_active = 1 
                and DATE_FORMAT(c.comment_created_at, '%Y') = :filter_year
        	";   
        
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;
        $printDateSince = new Default_View_Helper_PrintDateSince();
        $comments = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('filter_year' =>$filter_year));
        $helperImage = new Default_View_Helper_Image();
        foreach ($comments as &$value) {
            $value['member_since'] = $printDateSince->printDateSince($value['member_since']);
            $value['comment_created_at'] = $printDateSince->printDateSince($value['comment_created_at']);
            $value['avatar'] = $helperImage->Image($value['profile_image_url'], array('width' => '200', 'height' => '200', 'crop' => 2)); 
        }
		
		$sqlall = "	select count(1) 
                    from comments c 
                    join member m on c.comment_member_id = m.member_id and m.is_active = 1 and m.is_deleted = 0
					where c.comment_type=0
					and c.comment_active = 1 and DATE_FORMAT(c.comment_created_at, '%Y') = :filter_year";         

        $reportsAll = Zend_Db_Table::getDefaultAdapter()->fetchRow($sqlall,array('filter_year' =>$filter_year));

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $comments;
        $jTableResult['TotalRecordCount'] = array_pop($reportsAll);
        //$jTableResult['TotalRecordCount'] = 1000;
        $this->_helper->json($jTableResult);
    }

}