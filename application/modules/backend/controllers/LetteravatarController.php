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
class Backend_LetteravatarController extends Local_Controller_Action_Backend
{

     /** @var Default_Model_Project */
    protected $_model;
    const DATA_ID_NAME = 'member_id';    
    const DATA_AVATAR_TYPE_ID = 'avatar_type_id';
    protected $_modelName = 'Default_Model_Member';
    protected $_pageTitle = 'Users with avatar type unknown';
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
            $sorting = 'member_id desc';
        }       
        
        $reports = $this->_model ->getMembersAvatarUnknown($sorting,(int)$pageSize,$startIndex);        
        $totalRecordCount = $this->_model ->getMembersAvatarUnknownTotalCount();

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $reports;
        $jTableResult['TotalRecordCount'] = $totalRecordCount;

        $this->_helper->json($jTableResult);
    }

    public function updateAction()
    {
        $member_id = (int)$this->getParam(self::DATA_ID_NAME, null);
        //$typeId = $this->getParam(self::DATA_AVATAR_TYPE_ID, null);
        $typeId = 2; // user uploaded

        if($member_id)
        {
            $this->_model->updateAvatarTypeId($member_id,$typeId);
        }

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;                      
        $this->_helper->json($jTableResult);
    }

} 