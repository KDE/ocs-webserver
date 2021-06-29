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

namespace Backend\Controller;

use Application\Model\Entity\ConfigStoreCategory;
use Application\Model\Repository\ConfigStoreCategoryRepository;
use Application\Model\Repository\ConfigStoreCategoryTagRepository;
use Application\Model\Repository\ConfigStoreRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Service\ProjectCategoryService;
use Exception;
use JobQueue\Jobs\InitCacheStoreCategories;
use JobQueue\Jobs\JobBuilder;
use Laminas\Db\Sql\Expression;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class StorecategoriesController extends BackendBaseController
{

    const DATA_ID_NAME = 'store_category_id';
    private $configStoreCategoryRepository;
    private $configStoreCategoryTagRepository;
    private $projectCategoryService;
    private $configStoreRepository;
    private $projectCategoryRepository;

    public function __construct(
        ConfigStoreCategoryRepository $configStoreCategoryRepository,
        ConfigStoreCategoryTagRepository $configStoreCategoryTagRepository,
        ConfigStoreRepository $configStoreRepository,
        ProjectCategoryService $projectCategoryService,
        ProjectCategoryRepository $projectCategoryRepository
    ) {
        parent::__construct();
        $this->configStoreCategoryRepository = $configStoreCategoryRepository;
        $this->_model = $configStoreCategoryRepository;
        $this->_modelName = ConfigStoreCategory::class;
        $this->_pageTitle = 'Manage Store Categories';
        $this->configStoreCategoryTagRepository = $configStoreCategoryTagRepository;
        $this->configStoreRepository = $configStoreRepository;
        $this->projectCategoryService = $projectCategoryService;
        $this->projectCategoryRepository = $projectCategoryRepository;

    }

    public function indexAction()
    {
        $viewModel = new ViewModel();
        $this->layout()->pageTitle = $this->_pageTitle;
        $viewModel->setVariable('hostnames', $this->configStoreRepository->fetchHostnamesForJTable());

        return $viewModel;
    }

    // public function listAction_()
    // {
    //     $filter['store_id'] = $this->getParam('filter_hostname');
    //     $jTableResult = $this->prepareListActionJTableResult(null, $filter);

    //     return new JsonModel($jTableResult);
    // }

    public function listAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');
        $store_id = $this->getParam('filter_hostname');
        $sql = "SELECT 
                c.*,
                s.host,
                p.title as cat_title,
                (select group_concat( tag_id) tag_id from pling.config_store_category_tag ct where ct.config_store_category_id = c.store_category_id order by tag_id) as tag_id,
                (select group_concat( ta.tag_name) tag_name from pling.config_store_category_tag ct inner join tag ta on ct.tag_id = ta.tag_id
                    where ct.config_store_category_id = c.store_category_id order by ta.tag_id) tag_name
                FROM pling.config_store_category c
                inner join pling.config_store s on c.store_id = s.store_id
                inner join pling.project_category p on c.project_category_id = p.project_category_id
                ";
        
        if($store_id)
        {

            $sql .= " where c.store_id =".$store_id;
            
        } 
        
        if($sorting)
        {
            $sql .= " order by ".$sorting;
        }
        
        $sql .= " limit ".$pageSize . " offset ".$startIndex;
      
        $results = $this->_model->fetchAll($sql, null, true);

        $sqlAll = "select count(1) as cnt FROM pling.config_store_category c ";
        if($store_id)
        {
            $sqlAll .= " where store_id =".$store_id;
        }
        $count = $this->_model->fetchRow($sqlAll);
        
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $results;
        $jTableResult['TotalRecordCount'] =(int)$count['cnt'];
        return new JsonModel($jTableResult);
    }

    public function createAction()
    {
        $jTableResult = array();
        try {

            $data = $this->params()->fromPost();
            $id = $this->_model->insert($data);
            $newRow = $this->_model->fetchById($id);
            $this->initCache($newRow->store_id);
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $newRow;
        } catch (Exception $e) {
            //$this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));            
            error_log($e->__toString());
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function savetagsAction()
    {
        $jTableResult = array();
        try {

            $data = $this->params()->fromPost();
            $category = $data['c'];
            $store = $data['s'];
            $configstore = $this->configStoreCategoryRepository->findOneBy(['store_id'=>$store,'project_category_id'=>$category]);
            // remove old insert new
            $sql = "delete from config_store_category_tag where config_store_category_id=".$configstore->store_category_id;
            $this->configStoreCategoryTagRepository->query($sql);
            $tags = $data['t'];
            if($tags && strlen(trim($tags))>0)
            {
                $tagsarray = explode(",", $tags);
                foreach ($tagsarray as $t) {
                    $this->configStoreCategoryTagRepository->insert(['config_store_category_id'=>$configstore->store_category_id,'tag_id'=>$t]);
                }
            }            
            $jTableResult['Result'] = self::RESULT_OK;
            
        } catch (Exception $e) {                     
            error_log($e->__toString());
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    protected function initCache($store_id)
    {
        $modelPCat = $this->projectCategoryService;
        $modelPCat->fetchCategoryTreeForStore($store_id, true);
        $modelConfigStore = $this->configStoreRepository;
        $modelConfigStore->fetchConfigForStore($store_id, true);
        $modelConfigStore->fetchAllStoresAndCategories(true);
        $modelConfigStore->fetchAllStoresConfigArray(true);
    }

    public function initcacheAction()
    {
        $modelConfigStore = $this->configStoreRepository;
        $allStoresCat = $modelConfigStore->fetchAllStoresAndCategories(true);
        $allStoresConfig = $modelConfigStore->fetchAllStoresConfigArray(true);

        $modelPCat = $this->projectCategoryService;
        foreach ($allStoresConfig as $config) {
            $modelPCat->fetchCategoryTreeForStore($config['store_id'], true);
            $modelConfigStore->fetchConfigForStore($config['store_id'], true);
        }

        return new JsonModel(['status' => self::RESULT_OK]);
    }

    public function updateAction()
    {
        $jTableResult = array();
        try {

            $values = $this->params()->fromPost();
            // $entity = new $this->_modelName();
            // $entity->exchangeArray($this->params()->fromPost());
            // $values = $entity->getArrayCopy();

            foreach ($values as $key => $value) {
                if ($value == '') {
                    $values[$key] = new Expression('NULL');
                }
            }

            $this->_model->update($values);
            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $values;
            $this->initCache($values['store_id']);
        } catch (Exception $e) {
            // $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            error_log($e->__toString());
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $this->translate('Error while processing data.');
        }

        return new JsonModel($jTableResult);

    }

    public function deleteAction()
    {
        $dataId = (int)$this->getParam(self::DATA_ID_NAME, null);

        $row = $this->_model->findById($dataId);
        $this->_model->deleteId($dataId);

        $this->initCache($row->store_id);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function treeAction()
    {
        $result = true;
        $model = $this->projectCategoryRepository;
        $cat_id = (int)$this->getParam('c');

        try {
            $records = $model->fetchTreeForCategoryStores($cat_id);
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $result = false;
            $records = array();
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;
        $jTableResult['Options'] = $records;

        return new JsonModel($jTableResult);
    }

    /**
     * @param $inputParams
     *
     * @return array
     */
    protected function prepareEmptyValues($inputParams)
    {
        return array_map(
            function ($value) {
                return empty($value) ? new Expression('NULL') : $value;
            }, $inputParams
        );
    }

    protected function createJobInitCache($storeId)
    {
        JobBuilder::getJobBuilder()->withJobClass(InitCacheStoreCategories::class)->withParam('storeId', $storeId)
                  ->withParam('projectCategoryService', $this->projectCategoryService)
                  ->withParam('configStoreRepository', $this->configStoreRepository)->build();
        // $queue = Local_Queue_Factory::getQueue();
        // $command = new Backend_Commands_InitCacheStoreCategories($storeId);
        // $msg = $queue->send(serialize($command));
        // $this->ocsLog->info(__METHOD__ . ' - ' . print_r($msg, true));
    }

    protected function cacheClear($store_id)
    {

        $cache = $GLOBALS['ocs_cache'];
        $cache->setItem(ProjectCategoryService::CACHE_TREE_STORE . "_{$store_id}", null);
        $cache->setItem(ConfigStoreRepository::CACHE_STORE_CONFIG . "_{$store_id}", null);

        $modelConfigStore = $this->configStoreRepository;
        $modelConfigStore->fetchAllStoresAndCategories(true);
        $modelConfigStore->fetchAllStoresConfigArray(true);
    }

}