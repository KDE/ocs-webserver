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

class Local_Search_Provider_Lucene implements Local_Search_ProviderInterface
{

    /** @var  Zend_Search_Lucene */
    protected $_index;
    /** @var Zend_Config */
    protected $config;
    /** @var Zend_Log|Zend_Log_Writer_Abstract */
    protected $logger;

    /**
     * @param array|Zend_config $config
     * @param Zend_Log_Writer_Abstract $logger
     * @throws Exception
     */
    function __construct($config, $logger)
    {
        if (false == isset($config)) {
            throw new Exception(__CLASS__ . ' needs config object in constructor');
        }

        if ($config instanceof Zend_Config) {
            $this->config = $config;
        } elseif (is_array($config)) {
            $this->config = new Zend_Config($config);
        }

        if ($logger instanceof Zend_Log) {
            $this->logger = $logger;
        }

        Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
    }

    /**
     * @throws Zend_Db_Table_Row_Exception
     * @deprecated
     */
    public function createIndex()
    {
        $tableProject = new Default_Model_Project();
        $rowSetProject = $tableProject->fetchAll('status = ' . Default_Model_DbTable_Project::PROJECT_ACTIVE .
            ' AND type_id = ' . Default_Model_DbTable_Project::PROJECT_TYPE_STANDARD, 'created_at desc');

        $tableMember = new Default_Model_Member();
        $tableCategory = new Default_Model_DbTable_ProjectCategory();
        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();

        $this->_index = Zend_Search_Lucene::create($this->config->path);

        /** @var Zend_Db_Table_Row $project */
        foreach ($rowSetProject as $project) {
            $member = $project->findDependentRowset($tableMember, 'Owner')->current();
            $category = $project->findDependentRowset($tableCategory, 'Category')->current();

            if (null != $member->username) {

                $doc = new Zend_Search_Lucene_Document();

                $doc->addField(Zend_Search_Lucene_Field::keyword('project_id', $project->project_id));
                $doc->addField(Zend_Search_Lucene_Field::keyword('member_id', $member->member_id));
                $doc->addField(Zend_Search_Lucene_Field::keyword('project_category_id', $project->project_category_id));

                $doc->addField(Zend_Search_Lucene_Field::text('title', $project->title, 'UTF-8'));
                $doc->addField(Zend_Search_Lucene_Field::text('description', $project->description, 'UTF-8'));
                $doc->addField(Zend_Search_Lucene_Field::text('username', $member->username, 'UTF-8'));
                $doc->addField(Zend_Search_Lucene_Field::text('category', $category->title, 'UTF-8'));

                $isUpdate = ($project->type_id == Default_Model_DbTable_Project::PROJECT_TYPE_UPDATE);
                if ($isUpdate) {
                    $showUrl = $helperBuildProductUrl->buildProductUrl($project->pid) . '#anker_' . $project->project_id;
                    $plingUrl = $helperBuildProductUrl->buildProductUrl($project->pid, 'pling');
                } else {
                    $showUrl = $helperBuildProductUrl->buildProductUrl($project->project_id);
                    $plingUrl = $helperBuildProductUrl->buildProductUrl($project->project_id, 'pling');
                }

                $doc->addField(Zend_Search_Lucene_Field::unIndexed('showUrl', $showUrl));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('plingUrl', $plingUrl));

                $doc->addField(Zend_Search_Lucene_Field::unIndexed('uuid', $project->uuid));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('type_id', $project->type_id));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('pid', $project->pid));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('image_small', $project->image_small));

                $doc->addField(Zend_Search_Lucene_Field::unIndexed('facebook_code', $project->facebook_code));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('twitter_code', $project->twitter_code));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('google_code', $project->google_code));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('link_1', $project->link_1));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('ppload_collection_id',
                    $project->ppload_collection_id));

                $doc->addField(Zend_Search_Lucene_Field::unIndexed('validated', $project->validated));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('amount', $project->amount));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('claimable', $project->claimable));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('claimed_by_member', $project->claimed_by_member));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('created_at', $project->created_at));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('changed_at', $project->changed_at));

                $doc->addField(Zend_Search_Lucene_Field::unIndexed('profile_image_url', $member->profile_image_url));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('paypal_mail', $member->paypal_mail));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('dwolla_id', $member->dwolla_id));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('mail', $member->mail));
                $doc->addField(Zend_Search_Lucene_Field::unIndexed('roleId', $member->roleId));

                $this->_index->addDocument($doc);
            }
        }

        $this->_index->commit();
    }

    /**
     * @param $storeId
     * @param $searchIndexId
     * @deprecated
     */
    public function createStoreSearchIndex($storeId, $searchIndexId)
    {
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));

        $this->initStoreForSearchEngine($searchIndexId);

        $dataPath = $this->config->path;
        $searchIndexEngine = Zend_Search_Lucene::create($dataPath . $searchIndexId);

        $elementsForIndex = $this->fetchElementsForIndex($storeId);

        $this->createSearchIndex($searchIndexEngine, $elementsForIndex);
    }

    private function initStoreForSearchEngine($searchIndexId)
    {
        $dataPath = $this->config->path;

        if (false == file_exists($dataPath)) {
            throw new Exception('DataPath for search engine does not exist or has no rights: ' . $dataPath);
        }
        if (false == is_writable($dataPath)) {
            throw new Exception('DataPath for search engine not writable: ' . $dataPath);
        }
        $pathSearchIndex = $dataPath . DIRECTORY_SEPARATOR . $searchIndexId;
        if (file_exists($pathSearchIndex)) {
            if (false == is_writable($pathSearchIndex)) {
                throw new Exception($dataPath . DIRECTORY_SEPARATOR . $searchIndexId . ' is not writable');
            }
        } else {
            if (false == mkdir($dataPath . $searchIndexId)) {
                throw new Exception($dataPath . $searchIndexId . ' could not created');
            }
        }
    }

    private function fetchElementsForIndex($storeId)
    {
        $storeCategories = $this->fetchCategoriesForStore($storeId);
        return $this->fetchElementsForCategories($storeCategories);
    }

    /**
     * Returns all category ids which stored in database for this storeId. When
     * nothing was found, it returns all main categories in database.
     *
     * @param int $storeId
     * @return array
     */
    private function fetchCategoriesForStore($storeId)
    {
        $modelStoreCategories = new Default_Model_DbTable_ConfigStoreCategory();
        $resultSet = $modelStoreCategories->fetchAllCategoriesForStore($storeId);
        if (count($resultSet) > 0) {
            return $resultSet;
        }
        $modelCategories = new Default_Model_DbTable_ProjectCategory();
        $resultSet = $modelCategories->fetchMainCatIdsOrdered();
        return $resultSet;
    }

    private function fetchElementsForCategories($storeCategories)
    {
        $modelProduct = new Default_Model_Project();
        return $modelProduct->fetchProductsForCategories($storeCategories);
    }

    /**
     * @param Zend_Search_Lucene_Interface $searchIndexEngine
     * @param array $elementsForIndex
     */
    private function createSearchIndex($searchIndexEngine, $elementsForIndex)
    {
        foreach ($elementsForIndex as $element) {
            $doc = $this->createIndexDocument($element);
            $searchIndexEngine->addDocument($doc);
        }
    }

    /**
     * @param array $element
     * @return Zend_Search_Lucene_Document
     */
    protected function createIndexDocument($element)
    {
        $doc = new Zend_Search_Lucene_Document();

        $doc->addField(Zend_Search_Lucene_Field::keyword('project_id', $element['project_id']));
        $doc->addField(Zend_Search_Lucene_Field::keyword('member_id', $element['member_id']));
        $doc->addField(Zend_Search_Lucene_Field::keyword('project_category_id', $element['project_category_id']));

        $doc->addField(Zend_Search_Lucene_Field::text('title', $element['title'], 'UTF-8'));
        $doc->addField(Zend_Search_Lucene_Field::text('description', $element['description'], 'UTF-8'));
        $doc->addField(Zend_Search_Lucene_Field::text('username', $element['username'], 'UTF-8'));
        $doc->addField(Zend_Search_Lucene_Field::text('category', $element['cat_title'], 'UTF-8'));

        $isUpdate = ($element['type_id'] == Default_Model_DbTable_Project::PROJECT_TYPE_UPDATE);
        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        if ($isUpdate) {
            $showUrl = $helperBuildProductUrl->buildProductUrl($element['pid']) . '#anker_' . $element['project_id'];
            $plingUrl = $helperBuildProductUrl->buildProductUrl($element['pid'], 'pling');
        } else {
            $showUrl = $helperBuildProductUrl->buildProductUrl($element['project_id']);
            $plingUrl = $helperBuildProductUrl->buildProductUrl($element['project_id'], 'pling');
        }

        $doc->addField(Zend_Search_Lucene_Field::unIndexed('showUrl', $showUrl));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('plingUrl', $plingUrl));

        $doc->addField(Zend_Search_Lucene_Field::unIndexed('uuid', $element['uuid']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('type_id', $element['type_id']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('pid', $element['pid']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('image_small', $element['image_small']));

        $doc->addField(Zend_Search_Lucene_Field::unIndexed('facebook_code', $element['facebook_code']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('twitter_code', $element['twitter_code']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('google_code', $element['google_code']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('link_1', $element['link_1']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('ppload_collection_id',
            $element['ppload_collection_id']));

        $doc->addField(Zend_Search_Lucene_Field::unIndexed('validated', $element['validated']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('amount', $element['amount']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('claimable', $element['claimable']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('claimed_by_member', $element['claimed_by_member']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('created_at', $element['created_at']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('changed_at', $element['changed_at']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('project_changed_at', $element['project_changed_at']));

        $doc->addField(Zend_Search_Lucene_Field::unIndexed('profile_image_url', $element['profile_image_url']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('paypal_mail', $element['paypal_mail']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('dwolla_id', $element['dwolla_id']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('mail', $element['mail']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('roleId', $element['roleId']));

        $doc->addField(Zend_Search_Lucene_Field::unIndexed('version', $element['version']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('count_likes', $element['count_likes']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('count_dislikes', $element['count_dislikes']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('count_comments', $element['count_comments']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('count_downloads_hive',
            $element['count_downloads_hive']));

        $doc->addField(Zend_Search_Lucene_Field::unIndexed('amount_received', $element['amount_received']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('count_plings', $element['count_plings']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('count_plingers', $element['count_plingers']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('latest_pling', $element['latest_pling']));

        $doc->addField(Zend_Search_Lucene_Field::unIndexed('laplace_score', $element['laplace_score']));

        $doc->addField(Zend_Search_Lucene_Field::unIndexed('source_id', $element['source_id']));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('source_pk', $element['source_pk']));
        return $doc;
    }

}