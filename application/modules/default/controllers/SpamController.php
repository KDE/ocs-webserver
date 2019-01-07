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

    
    public function commentslistAction()
    {
    	
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');     

        if(!isset($sorting))
        {
        	$sorting = 'cntreport desc, comment_created_at desc';
        }        
        $sql = "
    			select 
				comment_id,
				comment_target_id,
				comment_member_id,
				comment_parent_id,
				comment_text,
				comment_created_at,
				(select count(1) from reports_comment r where c.comment_id = r.comment_id and is_deleted is null) cntreport,
				(select GROUP_CONCAT(reported_by) from reports_comment r where c.comment_id = r.comment_id and is_deleted is null) as reportedby
				from comments c
				where c.comment_type=0
				and c.comment_active = 1
				
        	";   
        
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;

        $comments = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);
		
		$sqlall = "	select count(*) 
					from comments c 
					where c.comment_type=0
					and c.comment_active = 1";         

        $reportsAll = Zend_Db_Table::getDefaultAdapter()->fetchRow($sqlall);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $comments;
        $jTableResult['TotalRecordCount'] = array_pop($reportsAll);

        $this->_helper->json($jTableResult);
    }

}