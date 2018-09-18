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
class Backend_TagsController extends Local_Controller_Action_Backend
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";

    /** @var Default_Model_DbTable_ReportProducts */
    protected $_model;

    protected $_modelName = 'Default_Model_DbTable_TagGroup';

    /**
     *
     */
    public function init()
    {
        $this->_model = new $this->_modelName();

        $this->view->pageTitle = 'Manage Tags';

        parent::init();
    }

    public function indexAction()
    {

    }

    public function listAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');

        $select = $this->_model->select()->order($sorting)->limit($pageSize, $startIndex);
        $result = $this->_model->fetchAll($select);

        $resultAll = $this->_model->getAdapter()->fetchRow('SELECT count(*) FROM ' . $this->_model->info('name'));

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $result->toArray();
        $jTableResult['TotalRecordCount'] = array_pop($resultAll);

        $this->_helper->json($jTableResult);
    }

    public function createAction()
    {
        $jTableResult = array();
        try {
            $newRow = $this->_model->createRow($this->getAllParams());
            $newRow->save();

            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $newRow->toArray();
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
            $record = $this->_model->save($this->getAllParams());

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record->toArray();
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $translate = Zend_Registry::get('Zend_Translate');
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $translate->_('Error while processing data.');
        }

        $this->_helper->json($jTableResult);
    }

    public function deleteAction()
    {
        $groupId = (int)$this->getParam('group_id', null);

        $this->_model->delete(array('group_id = ?' => $groupId));

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        $this->_helper->json($jTableResult);
    }

    public function childlistAction()
    {
        $groupId = (int)$this->getParam('GroupId');

        $modelTagGroup = new Default_Model_TagGroup();
        $resultSet = $modelTagGroup->fetchGroupItems($groupId);
        $numberResults = count($resultSet);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $resultSet;
        $jTableResult['TotalRecordCount'] = $numberResults;

        $this->_helper->json($jTableResult);
    }

    public function childcreateAction()
    {
        $jTableResult = array();
        try {
            $groupId = (int)$this->getParam('tag_group_id');
            $tagName = $this->getParam('tag_name');
            $tagFullname = $this->getParam('tag_fullname');
            $tagDescription = $this->getParam('tag_description');
            $is_active = $this->getParam('is_active');
            $modelTagGroup = new Default_Model_TagGroup();
            $newRow = $modelTagGroup->assignGroupTag($groupId, $tagName,$tagFullname,$tagDescription,$is_active);

            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $newRow;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $translate = Zend_Registry::get('Zend_Translate');
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $translate->_('Error while processing data.');
        }

        $this->_helper->json($jTableResult);
    }

    public function childupdateAction()
    {
        $jTableResult = array();
        try {
            $groupItemId = (int)$this->getParam('tag_group_item_id');
            //$tagId = (int)$this->getParam('tag_id');
            $tagName = $this->getParam('tag_name');

            $tagFullname = $this->getParam('tag_fullname');            
            $tagDescription = $this->getParam('tag_description');
            $is_active = $this->getParam('is_active');
            $modelTagGroup = new Default_Model_TagGroup();
            //load tag
            $record = $modelTagGroup->fetchOneGroupItem($groupItemId);
            $tagId = $record['tag_id'];
            
            $modelTagGroup->updateGroupTag($tagId, $tagName,$tagFullname,$tagDescription,$is_active);
            $record = $modelTagGroup->fetchOneGroupItem($groupItemId);

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $translate = Zend_Registry::get('Zend_Translate');
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $translate->_('Error while processing data.');
        }

        $this->_helper->json($jTableResult);
    }

    public function childdeleteAction()
    {
        $groupItemId = (int)$this->getParam('tag_group_item_id', null);

        $modelTagGroup = new Default_Model_TagGroup();
        $modelTagGroup->deleteGroupTag($groupItemId);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        $this->_helper->json($jTableResult);
    }

}