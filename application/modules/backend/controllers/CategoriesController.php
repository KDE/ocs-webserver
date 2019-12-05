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
class Backend_CategoriesController extends Local_Controller_Action_Backend
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

        $this->view->pageTitle = 'Manage Product Categories';
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
            $this->_model->moveToParent((int)$this->getParam('project_category_id', null), (int)$this->getParam('parent', null));
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
        $identifier = (int)$this->getParam('project_category_id', null);

        $this->_model->setCategoryDeleted($identifier);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        $this->_helper->json($jTableResult);
    }

    public function listAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');
        $filter_deleted = (int)$this->getParam('filter_deleted', 1);

        $records = $this->_model->fetchTreeWithParentId($filter_deleted, null);

        $pagination = Zend_Paginator::factory($records);
        $pagination->setItemCountPerPage($pageSize);
        $pagination->setCurrentPageNumber(($startIndex / $pageSize) + 1);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = (array)$pagination->getCurrentItems();
        $jTableResult['TotalRecordCount'] = count($records);

        $this->_helper->json($jTableResult);
    }

    public function moveelementAction()
    {
        $params = $this->getAllParams();
        $newPosition = $params['record']['lft'];

        switch ($params['direction']) {
            case 'up':
                $sibling = $this->_model->findPreviousSibling($params['record']);
                if (null == $sibling) {
                    $newPosition = $params['record']['lft'];
                } else {
                    $newPosition = (int)$sibling['lft'];
                }
                break;
            case 'down':
                $sibling = $this->_model->findNextSibling($params['record']);
                if (null == $sibling) {
                    $newPosition = $params['record']['lft'];
                } else {
                    $newPosition = (int)$sibling['rgt'] + 1;
                }
                break;
            default:
                ;
        }

        $jTableResult = array();
        if (count($sibling) == 0) {
            $jTableResult['Result'] = self::RESULT_ERROR;
            $this->_helper->json($jTableResult);
        }

        $element = $this->_model->fetchRow('lft = ' . $params['record']['lft']);

        $result = $this->_model->moveTo($element->toArray(), $newPosition);

        $jTableResult['Result'] = $result == true ? self::RESULT_OK : self::RESULT_ERROR;
        $jTableResult['Record'] = $element->toArray();

        $this->_helper->json($jTableResult);
    }

    public function dragdropAction()
    {
        $params = $this->getAllParams();

        if ($params['data']['lft'] <= $params['newPosition'] And $params['data']['rgt'] >= $params['newPosition']) {
            $result = false;
        } else {
            $result = $this->_model->moveTo($params['data'], $params['newPosition']);
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;

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

    public function createaboutAction()
    {
        $cat_id = (int)$this->getParam('c');
        $config = Zend_Registry::get('config');
        $static_config = $config->settings->static;
        $include_path = $static_config->include_path . 'category_about/';
        try {
            if (touch($include_path . '/' . $cat_id . '.phtml')) {
                $result = true;
            } else {
                $result = false;
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $result = false;
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;

        $this->_helper->json($jTableResult);
    }

    public function fetchtagratinggroupsAction()
    {
        $result = true;
        
        $tagmodel  = new Default_Model_Tags();
        try {
                $resultRows = $tagmodel->getAllTagGroupsForStoreFilter();
                $resultForSelect = array();
                $resultForSelect[] = array('DisplayText' => '', 'Value' => null);
                foreach ($resultRows as $row) {         
                    $resultForSelect[] = array('DisplayText' => $row['group_name'], 'Value' => $row['group_id']);
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

    public function readaboutAction()
    {
        $cat_id = (int)$this->getParam('c');
        $config = Zend_Registry::get('config');
        $static_config = $config->settings->static;
        $include_path = $static_config->include_path . 'category_about/';
        $filecontent = '';
        $result = true;

        try {
            if (file_exists($include_path . '/' . $cat_id . '.phtml')) {
                $filecontent = file_get_contents($include_path . '/' . $cat_id . '.phtml');
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $result = false;
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;
        $jTableResult['c'] = $cat_id;
        $jTableResult['CatAbout'] = $filecontent;

        $this->_helper->json($jTableResult);
    }

    public function saveaboutAction()
    {
        $cat_id = (int)$this->getParam('c');
        $cat_about = $this->getParam('ca');

        $config = Zend_Registry::get('config');
        $static_config = $config->settings->static;
        $include_path = $static_config->include_path . 'category_about/';

        try {
            file_put_contents($include_path . '/' . $cat_id . '.phtml', $cat_about);
            $result = true;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $result = false;
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;

        $this->_helper->json($jTableResult);
    }

} 