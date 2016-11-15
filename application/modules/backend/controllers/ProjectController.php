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
class Backend_ProjectController extends Local_Controller_Action_Backend
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    const DATA_ID_NAME = 'project_id';

    const PARAM_FEATURED = 'featured';
    const PARAM_APPROVED = 'approved';
    /** @var Default_Model_Project */
    protected $_model;

    protected $_modelName = 'Default_Model_Project';
    protected $_pageTitle = 'Manage Products';

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
            $filterInput = new Zend_Filter_Input(
                array(
                    '*' => 'StringTrim',
                    'project_id' => 'digits',
                    'member_id' => 'digits',
                    'project_category_id' => 'digits',
                    'status' => 'digits',
                    'pid' => 'digits',
                    'type_id' => 'digits',
                    'creator_id' => 'digits',
                    'validated' => 'digits',
                    'featured' => 'digits',
                    'amount' => 'digits',
                    'claimable' => 'digits',
                    'claimed_by_member' => 'digits',
                ),
                array('*' => array()),
                $this->getAllParams()
            );

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

        $product = $this->_model->find($dataId)->current();

        $identity = Zend_Auth::getInstance()->getIdentity();
        Default_Model_ActivityLog::logActivity($dataId, $dataId, $identity->member_id, Default_Model_ActivityLog::BACKEND_PROJECT_DELETE, $product);

        // this will delete the product and request the ppload for deleting associated files
        $command = new Backend_Commands_DeleteProductExtended($product);
        $command->doCommand();

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        $this->_helper->json($jTableResult);
    }


    public function dofeatureAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $roleName = $identity->roleName;


            if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $roleName) {
                $projectId = (int)$this->getParam(self::DATA_ID_NAME, null);
                $product = $this->_model->find($projectId)->current();

                $featured = (int)$this->getParam(self::PARAM_FEATURED, null);
                $product->featured = $featured;
                $product->save();

                Default_Model_ActivityLog::logActivity($projectId, $projectId, $identity->member_id, Default_Model_ActivityLog::BACKEND_PROJECT_FEATURE, $product);

                $jTableResult = array();
                $jTableResult['Result'] = self::RESULT_OK;

                $this->_helper->json($jTableResult);
            }
        }

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_ERROR;
        $this->_helper->json($jTableResult);
    }

    public function doapproveAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $roleName = $identity->roleName;

            if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $roleName) {
                $projectId = (int)$this->getParam(self::DATA_ID_NAME, null);
                $product = $this->_model->find($projectId)->current();
                $approved = (int)$this->getParam(self::PARAM_APPROVED, null);

                $product->approved = $approved;
                $product->save();

                Default_Model_ActivityLog::logActivity($projectId, $projectId, $identity->member_id, Default_Model_ActivityLog::BACKEND_PROJECT_APPROVED, $product);

                $jTableResult = array();
                $jTableResult['Result'] = self::RESULT_OK;

                $this->_helper->json($jTableResult);
            }
        }
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_ERROR;
        $this->_helper->json($jTableResult);
    }


    public function changecatAction()
    {
        $projectId = (int)$this->getParam(self::DATA_ID_NAME, null);
        $catId = (int)$this->getParam('project_category_id', null);

        $product = $this->_model->find($projectId)->current();
        $product->project_category_id = $catId;
        $product->save();

        $identity = Zend_Auth::getInstance()->getIdentity();
        Default_Model_ActivityLog::logActivity($projectId, $projectId, $identity->member_id, Default_Model_ActivityLog::BACKEND_PROJECT_CAT_CHANGE, $product);

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
        $filter['claimable'] = $this->getParam('filter_claimable');
        $filter['type_id'][1] = $this->getParam('filter_project_page');
        $filter['type_id'][2] = $this->getParam('filter_personal_page');
        $filter['type_id'][3] = $this->getParam('filter_updates');

        $select = $this->_model->select()->order($sorting)->limit($pageSize, $startIndex);

        $metadata = $this->_model->info(Zend_Db_Table_Abstract::METADATA);

        foreach ($filter as $key => $value) {
            if (is_array($value)) {
                $list = '';
                foreach ($value as $element) {
                    if (isset($element)) {
                        $list = $list . ',' . $element;
                    }
                }

                if (empty($list)) {
                    continue;
                }

                $list = substr($list, 1);

                $select->where("{$key} in ({$list})");

                continue;
            }
            if (false === empty($value)) {
                $data_type = $metadata[$key]['DATA_TYPE'];
                if (($data_type == 'varchar') OR ($data_type == 'text')) {
                    $select->where("{$key} like ?", '%' . $value . '%');
                } else {
                    $select->where("{$key} = ?", $value);
                }

            }
        }

        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $roleName = $identity->roleName;

            switch ($roleName) {
                case Default_Plugin_AclRules::ROLENAME_STAFF:
                    $select->where('status >= ?', Default_Model_DbTable_Project::PROJECT_ILLEGAL);
                    break;
            }
        }

        $reports = $this->_model->fetchAll($select);

        $reportsAll = $this->_model->fetchAll($select->limit(null,
            null)->reset('columns')->columns(array('countAll' => new Zend_Db_Expr('count(*)'))));

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $reports->toArray();
        $jTableResult['TotalRecordCount'] = $reportsAll->current()->countAll;

        $this->_helper->json($jTableResult);
    }

    public function togglestatusAction()
    {
        $jTableResult = array();
        try {
            $projectId = (int)$this->getParam('project_id');

            $record = $this->_model->find($projectId)->current();
            $record->status = ($record->status ? 0 : 10);
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