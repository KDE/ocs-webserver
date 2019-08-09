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
class Backend_StoreController extends Local_Controller_Action_Backend
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    const DATA_ID_NAME = 'store_id';

    /** @var Default_Model_DbTable_ConfigStore */
    protected $_model;
    protected $_modelName = 'Default_Model_DbTable_ConfigStore';
    protected $_pageTitle = 'Manage Store Config';

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

    public function initcacheAction()
    {
        $allStoresCat = $this->_model->fetchAllStoresAndCategories(true);
        $allStoresConfig = $this->_model->fetchAllStoresConfigArray(true);

        $modelPCat = new Default_Model_ProjectCategory();
        foreach ($allStoresConfig as $config) {
            $modelPCat->fetchCategoryTreeForStore($config['store_id'], true);
            $this->_model->fetchConfigForStore($config['store_id'], true);
        }
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

            // patch checkbox is_show_title get no parameter when is_show_title = 0
            if (!isset($values['is_show_title'])) {
                $values['is_show_title'] = 0;
            }
            if (!isset($values['is_show_git_projects'])) {
                $values['is_show_git_projects'] = 0;
            }
            if (!isset($values['is_show_blog_news'])) {
                $values['is_show_blog_news'] = 0;
            }
            if (!isset($values['is_show_forum_news'])) {
                $values['is_show_forum_news'] = 0;
            }
            if (!isset($values['is_show_in_menu'])) {
                $values['is_show_in_menu'] = 0;
            }
            if (!isset($values['is_show_real_domain_as_url'])) {
                $values['is_show_real_domain_as_url'] = 0;
            }
            if (!isset($values['cross_domain_login'])) {
                $values['cross_domain_login'] = 0;
            }
            if (!isset($values['is_client'])) {
                $values['is_client'] = 0;
            }

            $record = $this->_model->save($values);

            $this->initCache($record->store_id);

            $tagsid = $this->getParam('tags_id', null);
            $tagmodel = new Default_Model_Tags();
            $tagmodel->updateTagsPerStore($values['store_id'], $tagsid);

            $groupsid = $this->getParam('groups_id', null);
            $groupmodel = new Default_Model_TagGroup();
            $groupmodel->updateTagGroupsPerStore($values['store_id'], $groupsid);

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
        $modelPCat->fetchCategoryTreeForStore($store_id, true);

        $this->_model->fetchConfigForStore($store_id, true);
        $this->_model->fetchAllStoresAndCategories(true);
        $this->_model->fetchAllStoresConfigArray(true);
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
        $this->_model->fetchAllStoresAndCategories(true);
        $this->_model->fetchAllStoresConfigArray(true);
    }

    public function listAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');
        $filter['hostname'] = $this->getParam('filter_hostname');
        $filter['category_id'] = $this->getParam('filter_category_id');

        $select = $this->_model->select()->from($this->_model, array(
            '*',
            'groups_id' => new Zend_Db_Expr('(SELECT GROUP_CONCAT(CASE WHEN `tag`.`tag_fullname` IS NULL THEN `tag`.`tag_name` ELSE `tag`.`tag_fullname` END)
                FROM `config_store_tag`,`tag`            
                WHERE `tag`.`tag_id` = `config_store_tag`.`tag_id` AND `config_store_tag`.`store_id` = `config_store`.`store_id`        
                GROUP BY `config_store_tag`.`store_id`) AS `tags_name`,
                (SELECT GROUP_CONCAT(`tag`.`tag_id`)
                FROM `config_store_tag`,`tag`            
                WHERE `tag`.`tag_id` = `config_store_tag`.`tag_id` AND `config_store_tag`.`store_id` = `config_store`.`store_id`        
                GROUP BY `config_store_tag`.`store_id`) AS `tags_id`,
                (SELECT GROUP_CONCAT(`tag_group`.`group_name`)
                FROM `config_store_tag_group`,`tag_group`            
                WHERE `tag_group`.`group_id` = `config_store_tag_group`.`tag_group_id` AND `config_store_tag_group`.`store_id` = `config_store`.`store_id`        
                GROUP BY `config_store_tag_group`.`store_id`) AS `groups_name`,
                (SELECT GROUP_CONCAT(`tag_group`.`group_id`)
                FROM `config_store_tag_group`,`tag_group`            
                WHERE `tag_group`.`group_id` = `config_store_tag_group`.`tag_group_id` AND `config_store_tag_group`.`store_id` = `config_store`.`store_id`        
                GROUP BY `config_store_tag_group`.`store_id`)')
        ))->order($sorting)->limit($pageSize, $startIndex)->setIntegrityCheck(false);
        
        $select->joinLeft('browse_list_types', 'browse_list_types.browse_list_type_id = config_store.browse_list_type',
                     array('browse_list_type_name' => 'name'));

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

    public function hostnamesAction()
    {
        $result = true;
        $id = (int)$this->getParam('c');

        try {
            $records = $this->_model->fetchHostnamesForJTable($id);
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

    public function loadstoreconfigAction()
    {
        $jTableResult = array();
        try {
            $configStoreId = $this->getParam('c');

            $modelConfig = new Backend_Model_ClientFileConfig($configStoreId);
            $modelConfig->loadClientConfig();
            if ($modelConfig->getDefaultConfigLoaded()) {
                $this->view->defaultConfigLoaded = true;
            }
            $form = $modelConfig->getForm();
            $this->view->formConfig = $form;
            $view = $this->view->render('store/configform.phtml');

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['ViewRecord'] = $view;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $translate = Zend_Registry::get('Zend_Translate');
            $this->getResponse()->setHttpResponseCode(500);
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $translate->_('Error while processing data.');
        }

        $this->_helper->json($jTableResult);
    }

    public function savestoreconfigAction()
    {
        $jTableResult = array();
        try {
            $clientName = $this->getParam('clientname');
            unset($_POST['clientname']);
            $modelConfig = new Backend_Model_ClientFileConfig($clientName);
            $modelConfig->saveClientConfig($_POST, $clientName);

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

    public function tagsallAction()
    {

        $result = true;
        $tagmodel = new Default_Model_Tags();
        try {
            $resultRows = $tagmodel->getAllTagsForStoreFilter();
            $resultForSelect = array();
            $resultForSelect[] = array('DisplayText' => '', 'Value' => '');
            foreach ($resultRows as $row) {
                $resultForSelect[] = array(
                    'DisplayText' => $row['tag_name'] . '[' . $row['tag_id'] . ']',
                    'Value'       => $row['tag_id']
                );
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


    public function alltaggroupsAction()
    {

        $result = true;
        $tagmodel = new Default_Model_TagGroup();

        try {
            $resultRows = $tagmodel->fetchAllGroups();
            $resultForSelect = array();
            $resultForSelect[] = array('DisplayText' => '', 'Value' => '');
            foreach ($resultRows as $row) {
                $resultForSelect[] =
                    array(
                        'DisplayText' => $row['group_name'] . '[' . $row['group_id'] . ']',
                        'Value'       => $row['group_id']
                    );
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


    public function createaboutAction()
    {
        $store_id = (int)$this->getParam('c');
        $config = Zend_Registry::get('config');
        $static_config = $config->settings->static;
        $include_path = $static_config->include_path . 'store_about/';
        try {
            if (touch($include_path . '/' . $store_id . '.phtml')) {
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

    public function readaboutAction()
    {
        $store_id = (int)$this->getParam('c');
        $config = Zend_Registry::get('config');
        $static_config = $config->settings->static;
        $include_path = $static_config->include_path . 'store_about/';
        $filecontent = '';
        $result = true;

        try {
            if (file_exists($include_path . '/' . $store_id . '.phtml')) {
                $filecontent = file_get_contents($include_path . '/' . $store_id . '.phtml');
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $result = false;
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;
        $jTableResult['c'] = $store_id;
        $jTableResult['CatAbout'] = $filecontent;

        $this->_helper->json($jTableResult);
    }

    public function saveaboutAction()
    {
        $store_id = (int)$this->getParam('c');
        $cat_about = $this->getParam('ca');

        $config = Zend_Registry::get('config');
        $static_config = $config->settings->static;
        $include_path = $static_config->include_path . 'store_about/';

        try {
            file_put_contents($include_path . '/' . $store_id . '.phtml', $cat_about);
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