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
class Backend_UserController extends Local_Controller_Action_Backend
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    const DATA_ID_NAME = 'member_id';

    /** @var Default_Model_Member */
    protected $_model;

    protected $_modelName = 'Default_Model_Member';
    protected $_pageTitle = 'Manage Users';

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
            $values = $this->getAllParams();

            foreach ($values as $key => $value) {
                if ($value == '') {
                    $values[$key] = new Zend_Db_Expr('NULL');
                }
            }

            $record = $this->_model->save($values);

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
        if($this->hasParam(self::DATA_ID_NAME)) {
            $memberId = (int)$this->getParam(self::DATA_ID_NAME, null); 
        } else {
            $memberId = (int)$this->getParam('c', null); 
        }

        $this->_model->setDeleted($memberId);

        $identity = Zend_Auth::getInstance()->getIdentity();

        try {
            Default_Model_ActivityLog::logActivity($memberId, null, $identity->member_id, Default_Model_ActivityLog::BACKEND_USER_DELETE,
                null);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        $this->_helper->json($jTableResult);
    }

    public function listAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');
        $filter['member_id'] = $this->getParam('filter_member_id');
        $filter['lastname'] = $this->getParam('filter_lastname');
        $filter['firstname'] = $this->getParam('filter_firstname');
        $filter['username'] = $this->getParam('filter_username');
        $filter['mail'] = $this->getParam('filter_mail');

        $select = $this->_model->select()->order($sorting)->limit($pageSize, $startIndex);
        //        foreach ($filter as $key => $value) {
        //            if (false === empty($value)) {
        //                $select->where("{$key} like ?", $value);
        //            }
        //        }
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

        $reports = $this->_model->fetchAll($select);

        $reportsAll = $this->_model->fetchAll($select->limit(null, null)->reset('columns')
                                                     ->columns(array('countAll' => new Zend_Db_Expr('count(*)'))));

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $reports->toArray();
        $jTableResult['TotalRecordCount'] = $reportsAll->current()->countAll;

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
            $view = $this->view->render('user/member.phtml');

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

    public function exportAction()
    {
        $jTableResult = array();
        try {
            $memberId = (int)$this->getParam('c');

            $modelMember = new  Default_Model_Member();
            $record = $modelMember->fetchMemberData($memberId, false);

            $modelOpenCode = new Default_Model_Ocs_Gitlab();
            $modelOpenCode->createUserFromArray($record->toArray(), true);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - opencode : ' . implode(PHP_EOL." - ", $modelOpenCode->getMessages()));

            $modelIdent = new Default_Model_Ocs_Ldap();
            $modelIdent->addUserFromArray($record->toArray(), true);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - ldap : ' . implode(PHP_EOL." - ", $modelIdent->getMessages()));

            $modelId = new Default_Model_Ocs_OAuth();
            $modelId->createUserFromArray($record->toArray(), true);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - oauth : ' . implode(PHP_EOL." - ", $modelId->getMessages()));

            $modelForum = new Default_Model_Ocs_Forum();
            $modelForum->createUserFromArray($record->toArray(), true);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - forum : ' . implode(PHP_EOL." - ", $modelForum->getMessages()));

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            //$jTableResult['Record'] = $record->toArray();
            $jTableResult['Message'] = "OK";
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - (Line ' . $e->getLine() . ') ' . $e->getMessage());
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $e->getMessage();
        }

        $this->_helper->json($jTableResult);
    }
    
    
    public function reactivateAction()
    {
        $memberId = (int)$this->getParam('c');

        $this->_model->setActivated($memberId);

        $identity = Zend_Auth::getInstance()->getIdentity();

        try {
            Default_Model_ActivityLog::logActivity($memberId, null, $identity->member_id, Default_Model_ActivityLog::BACKEND_USER_UNDELETE,
                null);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        $this->_helper->json($jTableResult);
    }

} 