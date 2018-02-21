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
class Backend_ReportCommentsController extends Local_Controller_Action_Backend
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";

    /** @var Default_Model_DbTable_ReportComments */
    protected $_model;

    protected $_modelName = 'Default_Model_DbTable_ReportComments';

    /**
     *
     */
    public function init()
    {
        $this->_model = new $this->_modelName();

        $this->view->pageTitle = 'Manage Reported Comments';

        parent::init();
    }

    public function indexAction()
    {

    }

    public function createAction()
    {
        //        $jTableResult = array();
        //        try {
        //            $newRow = $this->_model->createRow($this->getAllParams());
        //            $result = $newRow->save();
        //
        //            $jTableResult['Result'] = self::RESULT_OK;
        //            $jTableResult['Record'] = $newRow->toArray();
        //        } catch (Exception $e) {
        //            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
        //            $translate = Zend_Registry::get('Zend_Translate');
        //            $jTableResult['Result'] = self::RESULT_ERROR;
        //            $jTableResult['Message'] = $translate->_('Error while processing data.');
        //        }
        //
        //        $this->_helper->json($jTableResult);
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
        $reportId = (int)$this->getParam('project_report_id', null);

        $this->_model->setDelete($reportId);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        $this->_helper->json($jTableResult);
    }

    public function listAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');

        $dataModel = new Backend_Model_Reports();

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $dataModel->getReportsForComments($startIndex, $pageSize, $sorting);
        $jTableResult['TotalRecordCount'] = $dataModel->getTotalCountForReportedComments();

        $this->_helper->json($jTableResult);
    }

    public function fetchAction()
    {
        $jTableResult = array();
        try {
            $commentId = (int)$this->getParam('c');

            $model = new Default_Model_ProjectComments();
            $record = $model->getCommentWithMember($commentId);
            $this->view->comment = $record;
            $view = $this->view->render('reportcomments/comment.phtml');

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record;
            $jTableResult['ViewRecord'] = $view;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $translate = Zend_Registry::get('Zend_Translate');
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $translate->_('Error while processing data.');
        }

        $this->_helper->json($jTableResult);
    }

    public function statusAction()
    {
        $jTableResult = array();
        try {

            $commentId = (int)$this->getParam('c');

            $model = new Default_Model_DbTable_Comments();
            $record = $model->find($commentId)->current();
            $record->comment_active = ($record->comment_active ? 0 : 1);
            $record->save();

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

} 