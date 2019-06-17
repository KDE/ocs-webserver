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
class Backend_SectionController extends Local_Controller_Action_Backend
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    const DATA_ID_NAME = 'section_id';

    /** @var Default_Model_DbTable_ConfigStore */
    protected $_model;
    protected $_modelName = 'Default_Model_DbTable_Section';
    protected $_pageTitle = 'Manage Sections';

    public function init()
    {
        $this->_model = new Default_Model_DbTable_Section();

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
            $allParams = $this->getAllParams();
            $resultWalk = array_walk($allParams, function (&$value) {
                $value = strlen($value) == 0 ? null : $value;
            });
            if (false === $resultWalk) {
                throw new Exception('array_walk through input parameters failed.');
            }
            //$newRow = $this->_model->createRow($allParams);
            //$result = $newRow->save();
            $newRow = $this->_model->save($allParams);

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
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $translate = Zend_Registry::get('Zend_Translate');
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $translate->_('Error while processing data.');
        }

        $this->_helper->json($jTableResult);
    }

    protected function initCache($store_id)
    {
        $modelPCat = new Default_Model_ProjectCategory();
        $modelPCat->fetchCategoryTreeForSection($store_id, true);

        $this->_model->fetchAllSectionsAndCategories(true);
        $this->_model->fetchAllSectionsArray(true);
    }

    public function deleteAction()
    {
        $dataId = (int)$this->getParam(self::DATA_ID_NAME, null);

        $this->_model->deleteId($dataId);

        $this->cacheClear($dataId);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        $this->_helper->json($jTableResult);
    }

    protected function cacheClear($store_id)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cache->remove(Default_Model_ProjectCategory::CACHE_TREE_STORE . "_{$store_id}");
        $cache->remove(Default_Model_DbTable_ConfigStore::CACHE_STORE_CONFIG . "_{$store_id}");
        $this->_model->fetchAllSectionsAndCategories(true);
        $this->_model->fetchAllSectionsArray(true);
    }

    public function listAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');
        $filter['name'] = $this->getParam('filter_name');
        $filter['category_id'] = $this->getParam('filter_category_id');

        $select = $this->_model->select()->from($this->_model)->order($sorting)->limit($pageSize, $startIndex)->setIntegrityCheck(false)
        ;

        foreach ($filter as $key => $value) {
            if (false === empty($value)) {
                $select->where("{$key} like ?", $value);
            }
        }

        $reports = $this->_model->fetchAll($select);

        $select = $this->_model->select()->from($this->_model)->setIntegrityCheck(false);
        foreach ($filter as $key => $value) {
            if (false === empty($value)) {
                $select->where("{$key} like ?", $value);
            }
        }
        $reportsAll = $this->_model->fetchAll($select->limit(null, null)->reset('columns')
                                                     ->columns(array('countAll' => new Zend_Db_Expr('count(*)'))));

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $reports->toArray();
        $jTableResult['TotalRecordCount'] = $reportsAll->current()->countAll;

        $this->_helper->json($jTableResult);
    }

    

}