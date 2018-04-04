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
class Backend_CategorytagController extends Local_Controller_Action_Backend
{
    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";

    /** @var Default_Model_DbTable_ProjectCategory */
    protected $_model;

    protected $_authMember;

    protected $_modelName = 'Default_Model_DbTable_ProjectCategory';

    public function init()
    {
        parent::init();

        $this->_model = new $this->_modelName();

        $this->view->pageTitle = 'Manage  Category_Tag';
        $this->view->author = $this->_authMember->username;
    }

    public function indexAction()
    {
        
    }

    public function createAction()
    {

        $jTableResult = array();
        try {
            $params = $this->getAllParams();
            if (empty($params['rgt'])) {
                $root = $this->_model->fetchRoot();
                $params['rgt'] = $root->rgt - 1;
            }
            $resultRow = $this->_model->addNewElement($params)->toArray();

            if (false === empty($params['parent'])) {
                $this->_model->moveToParent($resultRow['project_category_id'], (int)$params['parent'], 'bottom');
                $resultRow = $this->_model->fetchElement($resultRow['project_category_id']);
            }

            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $resultRow;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $translate = Zend_Registry::get('Zend_Translate');
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $translate->_('Error while processing data.');
        }

        $this->_helper->json($jTableResult);
    }

    public function updateAction()
    {

        $jTableResult = array();
        try {
            
            //$this->_model->moveToParent((int)$this->getParam('project_category_id', null), (int)$this->getParam('parent', null));            
            //$record = $this->_model->save($this->getAllParams());
            $tagmodel  = new Default_Model_Tags();
            $tagmodel->updateTagsPerCategory((int)$this->getParam('project_category_id', null), $this->getParam('tags_id', null));
            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
           // $jTableResult['Record'] = $record->toArray();
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $translate = Zend_Registry::get('Zend_Translate');
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $translate->_('Error while processing data.');
        }

        $this->_helper->json($jTableResult);
    }


    public function listAction()
    {

        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');
        $filter_deleted = (int)$this->getParam('filter_deleted', 1);

        $records = $this->_model->fetchTreeWithParentIdAndTags($filter_deleted, null);      

        $pagination = Zend_Paginator::factory($records);
        $pagination->setItemCountPerPage($pageSize);
        $pagination->setCurrentPageNumber(($startIndex / $pageSize) + 1);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = (array)$pagination->getCurrentItems();
        $jTableResult['TotalRecordCount'] = count($records);

        $this->_helper->json($jTableResult);
    }

    

    public function treeAction()
    {

        $result = true;
        $cat_id = (int)$this->getParam('c');

        try {
            $records = $this->_model->fetchTreeForJTableStores($cat_id);


        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $result = false;
            $records = array();
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;
        $jTableResult['Options'] = $records;

        $this->_helper->json($jTableResult);
    }


    public function tagsallAction()
    {

        $result = true;
        $groupid = 5; // unsortied
        $tagmodel  = new Default_Model_Tags();
        try {
                $resultRows = $tagmodel->getTagsPerGroup($groupid);
                $resultForSelect = array();
                foreach ($resultRows as $row) {         
                    $resultForSelect[] = array('DisplayText' => $row['tag_name'].'['.$row['tag_id'].']', 'Value' => $row['tag_id']);
                }

        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $result = false;
            $records = array();
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;
        $jTableResult['Options'] = $resultForSelect;

        $this->_helper->json($jTableResult);
    }

  

} 