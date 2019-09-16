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
        $this->view->headTitle('Spam - Products','SET');
        
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
                select pp.project_id,pp.status,pp.member_id, pp.created_at, cntfiles,size, m.username, m.paypal_mail,m.created_at as member_since, c.title cat_title
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
                        pling.project p,
                        ppload.ppload_files f
                        where p.ppload_collection_id = f.collection_id
                        group by p.project_id
                        order by p.created_at desc, cntfiles desc
                    )
                    pp 
                    ,member m
                    ,project_category c
                    where pp.member_id = m.member_id
                    and pp.project_category_id = c.project_category_id
                    and cntfiles > 10
        ";
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;
        $printDateSince = new Default_View_Helper_PrintDateSince();
        $filesize = new Default_View_Helper_HumanFilesize();
        $results = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
                
        foreach ($results as &$value) {
            $value['created_at'] = $printDateSince->printDateSince($value['created_at']);    
            $value['size'] = $filesize->humanFilesize($value['size']);               
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
                (select GROUP_CONCAT(reported_by) from reports_comment r where c.comment_id = r.comment_id ) as reportedby,
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
        	";   
        
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;
        $printDateSince = new Default_View_Helper_PrintDateSince();
        $comments = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
        $helperImage = new Default_View_Helper_Image();
        foreach ($comments as &$value) {
            $value['member_since'] = $printDateSince->printDateSince($value['member_since']);
            $value['comment_created_at'] = $printDateSince->printDateSince($value['comment_created_at']);
            $value['avatar'] = $helperImage->Image($value['profile_image_url'], array('width' => '200', 'height' => '200', 'crop' => 2)); 
        }
		
		// $sqlall = "	select count(*) 
		// 			from comments c 
		// 			where c.comment_type=0
		// 			and c.comment_active = 1";         

        //$reportsAll = Zend_Db_Table::getDefaultAdapter()->fetchRow($sqlall);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $comments;
        // $jTableResult['TotalRecordCount'] = array_pop($reportsAll);
        $jTableResult['TotalRecordCount'] = 1000;
        $this->_helper->json($jTableResult);
    }

}