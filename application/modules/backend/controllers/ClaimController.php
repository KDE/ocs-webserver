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

class Backend_ClaimController extends Local_Controller_Action_Backend
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    const DATA_ID_NAME = 'member_id';

    /** @var Default_Model_Project */
    protected $_model;

    protected $_modelName = 'Default_Model_Project';
    protected $_pageTitle = 'Administrate Claimed Products';

    public function init()
    {
        $this->_model = new $this->_modelName();

        $this->view->pageTitle = $this->_pageTitle;

        parent::init();
    }

    public function indexAction()
    {

    }

    public function createAction()
    {
        $jTableResult = array();
        try {
            $newRow = $this->_model->createRow($this->getAllParams());
            $result = $newRow->save();

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
            $filterInput = new Zend_Filter_Input(array(
                '*'                   => 'StringTrim',
                'project_id'          => 'digits',
                'member_id'           => 'digits',
                'project_category_id' => 'digits',
                'status'              => 'digits',
                'pid'                 => 'digits',
                'type_id'             => 'digits',
                'creator_id'          => 'digits',
                'validated'           => 'digits',
                'featured'            => 'digits',
                'amount'              => 'digits',
                'claimable'           => 'digits',
                'claimed_by_member'   => 'digits',
            ), array('*' => array()), $this->getAllParams());

            $record = $this->_model->save($filterInput->getEscaped());

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
        $dataId = (int)$this->getParam(self::DATA_ID_NAME, null);

        $this->_model->setDeleted($dataId);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        $this->_helper->json($jTableResult);
    }

    public function listAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');
        $filter['title'] = $this->getParam('filter_title');
        $filter['project_id'] = $this->getParam('filter_project_id');
        $filter['member_id'] = $this->getParam('filter_member_id');

        $select = $this->_model->select()->order($sorting)->limit($pageSize, $startIndex)->where('claimable = 1');
        foreach ($filter as $key => $value) {
            if (is_array($value)) {
                $list = '';
                foreach ($value as $element) {
                    if (isset($element)) {
                        $list = $list . ',' . $element;
                    }
                }
                $list = substr($list, 1);

                $select->where("{$key} in ({$list})");

                continue;
            }
            if ((false === empty($value)) AND is_numeric($value)) {
                $select->where("{$key} = ?", $value);
            } else {
                if ((false === empty($value)) AND is_string($value)) {
                    $select->where("{$key} like ?", '%' . $value . '%');
                }
            }
        }

        $reports = $this->_model->fetchAll($select);

        $reportsAll = $this->_model->fetchAll($select->limit(null, null));

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $reports->toArray();
        $jTableResult['TotalRecordCount'] = $reportsAll->count();

        $this->_helper->json($jTableResult);
    }

    public function removeclaimAction()
    {
        $jTableResult = array();
        try {
            $projectId = (int)$this->getParam('project_id');

            $record = $this->_model->find($projectId)->current();
            $record->claimed_by_member = new Zend_Db_Expr('NULL');
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

    public function toggleclaimAction()
    {
        $jTableResult = array();
        try {
            $projectId = (int)$this->getParam('project_id');

            $record = $this->_model->find($projectId)->current();
            $record->claimable = ($record->claimable ? 0 : 1);
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

    public function transferAction()
    {
        $jTableResult = array();
        try {
            $projectId = (int)$this->getParam('project_id');

            $this->_model->transferClaimToMember($projectId);
            $record = $this->_model->find($projectId)->current();

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

    public function fieldAction()
    {
        $jTableResult = array();
        try {
            $projectId = (int)$this->getParam('project_id');
            $nameField = $this->getParam('fieldname');
            $valueField = $this->getParam('fieldvalue');

            $record = $this->_model->find($projectId)->current();
            $record->claimable = ($record->claimable ? 0 : 1);
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

    public function memberinfoAction()
    {
        $jTableResult = array();
        try {
            $memberId = (int)$this->getParam('member_id');

            $modelMember = new  Default_Model_Member();
            $record = $modelMember->find($memberId)->current();
            $this->view->member = $record;
            $view = $this->view->render('claim/member.phtml');

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record->toArray();
            $jTableResult['ViewRecord'] = $view;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $translate = Zend_Registry::get('Zend_Translate');
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $translate->_('Error while processing data.');
        }

        $this->_helper->json($jTableResult);
    }

} 