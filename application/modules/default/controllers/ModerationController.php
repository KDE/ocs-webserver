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
class ModerationController extends Local_Controller_Action_DomainSwitch
{

    /** @var Default_Model_Project */
    protected $_model;
    const DATA_ID_NAME = 'project_id';
    const DATA_NOTE = 'note';
    const DATA_VALUE = 'value';
    protected $_modelName = 'Default_Model_ProjectModeration';
    protected $_pageTitle = 'Moderate GHNS excluded';
    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    public function init()
    {
        $this->_model = new $this->_modelName();
        $this->view->pageTitle = $this->_pageTitle;
        parent::init();
    }


    public function indexAction()
    {
        $this->view->page = (int)$this->getParam('page', 1);        
	}
	
	

    public function listAction()
    {
    	$startIndex = (int)$this->getParam('jtStartIndex');
    	$pageSize = (int)$this->getParam('jtPageSize');
    	$sorting = $this->getParam('jtSorting');
    	if($sorting==null)
    	{
    		$sorting = 'created_at desc';
    	}
    	$filter['member_id'] = $this->getParam('filter_member_id');

    	$mod = new Default_Model_ProjectModeration();    
    	$reports = $mod->getList($filter['member_id'],$sorting,(int)$pageSize,$startIndex);
    	//$reports = $mod->getList();

    	$totalRecordCount = $mod->getTotalCount($filter);
    	
    	$jTableResult = array();
    	$jTableResult['Result'] = self::RESULT_OK;
    	$jTableResult['Records'] = $reports;
    	$jTableResult['TotalRecordCount'] = $totalRecordCount;

    	$this->_helper->json($jTableResult);
    }

    public function updateAction()
    {
    	$dataId = (int)$this->getParam(self::DATA_ID_NAME, null);
    	$note = $this->getParam(self::DATA_NOTE, null);
    	$value = $this->getParam(self::DATA_VALUE, null);
    	if($value==null)
    	{
    		$value = 0;
    	}
    	if($value==0)
    	{
		$tableTags = new Default_Model_Tags();
		$tableTags->saveGhnsExcludedTagForProject($dataId, 0);
    	}

    	$auth = Zend_Auth::getInstance();
        	$identity = $auth->getIdentity();
    	$mod = new Default_Model_ProjectModeration();   
    	//createModeration($project_id,$project_moderation_type_id, $is_set, $userid,$note)
    	$mod->createModeration($dataId,Default_Model_ProjectModeration::M_TYPE_GET_HOT_NEW_STUFF_EXCLUDED, $value, $identity->member_id,$note);
    	$jTableResult = array();
        	$jTableResult['Result'] = self::RESULT_OK;                		
        	$this->_helper->json($jTableResult);
    }


	public function productmoderationAction()
    {		
		$this->view->headTitle('Product Moderation','SET');   
    }

	public function listmoderationAction()
    {
    	$startIndex = (int)$this->getParam('jtStartIndex');
    	$pageSize = (int)$this->getParam('jtPageSize');
    	$sorting = $this->getParam('jtSorting');
    	if($sorting==null)
    	{
    		$sorting = 'comment_created_at desc';
    	}
    	$mod = new Default_Model_ProjectComments();    
    	$comments = $mod->fetchCommentsWithType(Default_Model_DbTable_Comments::COMMENT_TYPE_MODERATOR,$sorting,(int)$pageSize,$startIndex);    			
		$printDateSince = new Default_View_Helper_PrintDateSince();        
        $helperImage = new Default_View_Helper_Image();
        foreach ($comments as &$value) {            
            $value['comment_created_at'] = $printDateSince->printDateSince($value['comment_created_at']);
			$value['profile_image_url'] = $helperImage->Image($value['profile_image_url'], array('width' => '200', 'height' => '200', 'crop' => 2)); 
			$value['image_small'] = $helperImage->Image($value['image_small'], array('width' => '100', 'height' => '100', 'crop' => 2)); 
        }
		
		
		$totalRecordCount = $mod->fetchCommentsWithTypeCount(Default_Model_DbTable_Comments::COMMENT_TYPE_MODERATOR);    	
    	$jTableResult = array();
    	$jTableResult['Result'] = self::RESULT_OK;
    	$jTableResult['Records'] = $comments;
    	$jTableResult['TotalRecordCount'] = $totalRecordCount;
    	$this->_helper->json($jTableResult);
    }

      

}