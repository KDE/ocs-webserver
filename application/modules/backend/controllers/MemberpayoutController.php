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
class Backend_MemberpayoutController extends Local_Controller_Action_Backend
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    const DATA_ID_NAME = 'id';

    /** @var Default_Model_Member */
    protected $_model;

    protected $_modelName = 'Default_Model_DbTable_MemberPayout';
    protected $_pageTitle = 'Manage Payouts';

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
            $resultArray = $record->toArray();
            $resultArray['color'] = '#ffffff';

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $resultArray;
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

        $identity = Zend_Auth::getInstance()->getIdentity();
        Default_Model_ActivityLog::logActivity($dataId, null, $identity->member_id, Default_Model_ActivityLog::BACKEND_USER_DELETE,
            null);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        $this->_helper->json($jTableResult);
    }

    public function listAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');
        $filter['yearmonth'] = $this->getParam('filter_yearmonth');
        if (!$this->getParam('filter_yearmonth')) {
            $filter['yearmonth'] = date("Ym", strtotime("first day of previous month"));
        }
        $filter['status'] = $this->getParam('filter_status');
        $filter['member_id'] = $this->getParam('filter_member_id');
        $filter['paypal_mail'] = $this->getParam('filter_paypal_mail');
        $filter['mail'] = $this->getParam('filter_mail');
        
        
        $sql = " 
                select member_payout.*, payout_status.color
                from member_payout
                join payout_status on payout_status.id = member_payout.`status`

                ";
        

        $select = $this->_model->select()->order($sorting)->limit($pageSize, $startIndex);
        
        $where = " WHERE 1=1 ";
        
        /*
        $select->join('payout_status',
        		'member_payout.status = payout_status.id',
        		array('color')
        );*/
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
                
                $where .= " AND {$key} in ({$list})";

                continue;
            }
            if (false === empty($value)) {
                $data_type = $metadata[$key]['DATA_TYPE'];
                if (($data_type == 'varchar') OR ($data_type == 'text')) {
                    $likeText = "'%".$value."%'";
                    $select->where("{$key} like ?", $likeText);
                    $where .= " AND {$key} like ".$likeText;
                } else {
                    $select->where("{$key} = ?", $value);
                    $where .= " AND {$key} = " . $value;
                }
            }
        }
        $sql .= $where;
        $sql .= " ORDER BY ". $sorting;
        $sql .= " LIMIT ".$startIndex.",".$pageSize;

        $reports = $this->_model->fetchAll($select);
        $reportsReturn = $this->_model->getAdapter()->fetchAll($sql);

        $reportsAll = $this->_model->fetchAll($select->limit(null, null)->reset('columns')
                                                     ->columns(array('countAll' => new Zend_Db_Expr('count(*)'))));

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $reportsReturn;
        $jTableResult['TotalRecordCount'] = $reportsAll->current()->countAll;

        $this->_helper->json($jTableResult);
    }

    public function exportAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');
        $filter['yearmonth'] = $this->getParam('filter_yearmonth');
        if (!$this->getParam('filter_yearmonth')) {
            $filter['yearmonth'] = date("Ym", strtotime("first day of previous month"));
        }
        $filter['status'] = $this->getParam('filter_status');
        $filter['member_id'] = $this->getParam('filter_member_id');
        $filter['paypal_mail'] = $this->getParam('filter_paypal_mail');
        $filter['mail'] = $this->getParam('filter_mail');
        $fields = $this->getParam('field_list');

        $select = $this->_model->select();

        $select->from('member_payout',
                array('member_id as MemberId', 'paypal_mail as PayPalMail', 'amount as Amount', 'status as Status'))->order($sorting)
               ->limit($pageSize, $startIndex)
        ;
        /*
         $select->join('payout_status',
                 'member_payout.status = payout_status.id',
                 array('color')
         );*/
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

        $filename = "Payout_" . $filter['yearmonth'] . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $this->exportFile($reports->toArray());
        //$this->_helper->json($jTableResult);
    }

    function exportFile($records)
    {
        $heading = false;
        if (!empty($records)) {
            foreach ($records as $row) {
                if (!$heading) {
                    // display field/column names as a first row
                    echo implode("\t", array_keys($row)) . "\n";
                    $heading = true;
                }
                echo implode("\t", array_values($row)) . "\n";
            }
        }
        exit;
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

} 