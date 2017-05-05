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
class ExploreController extends Local_Controller_Action_DomainSwitch
{

    /** @var  string */
    protected $_browserTitlePrepend;

    public function init()
    {
        parent::init();
        $this->_auth = Zend_Auth::getInstance();
    }
    
    protected function _initResponseHeader()
    {
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";

        $this->getResponse()
            ->setHeader('X-FRAME-OPTIONS', 'SAMEORIGIN', true)
//            ->setHeader('Last-Modified', $modifiedTime, true)
            ->setHeader('Expires', $expires, true)
            ->setHeader('Pragma', 'no-cache', true)
            ->setHeader('Cache-Control', 'private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true);
    }

    public function categoriesAction()
    {
        // Filter-Parameter
        $inputFilterParams['category'] = (int)$this->getParam('cat', null);
        $inputFilterParams['filter'] = (int)$this->getParam('fil', null);
        $inputFilterParams['order'] = $this->getParam('ord', 'latest');
        $inputFilterParams['selected'] = (int)$this->getParam('sel', $inputFilterParams['category']);

        $modelCategories = new Default_Model_DbTable_ProjectCategory();
        $children = $modelCategories->fetchImmediateChildren($inputFilterParams['selected']);
        $selChild = $modelCategories->fetchElement($inputFilterParams['filter']);

        $response = $this->generateResponseMsg($children, $inputFilterParams, $selChild);
        $this->_helper->json($response);
    }

    private function generateResponseMsg($children, $inputParams, $selChild)
    {
        $result = array();

        if (count($children) == 0) {
            return $result;
        }

        $helperBuildExploreUrl = new Default_View_Helper_BuildExploreUrl();
        foreach ($children as $child) {
            $nodeSelectedState = ($inputParams['filter'] == $child['project_category_id']) ? true : false;
            if (1 == ($child['rgt'] - $child['lft'])) {
                $result[] = array(
                    'title' => $child['title'],
                    'key' => $child['project_category_id'],
                    'href' => $helperBuildExploreUrl->buildExploreUrl($inputParams['category'],
                        $child['project_category_id'], $inputParams['order']),
                    'target' => '_top',
                    'selected' => $nodeSelectedState
                );
            } else {
                $nodeHasChildren = (1 == ($child['rgt'] - $child['lft'])) ? false : true;
                $nodeIsSelectedSubCat = (($selChild['lft'] > $child['lft']) AND ($selChild['rgt'] < $child['rgt'])) ? true : false;
                $nodeExpandedState = false;
                $nodeChildren = null;
                if ($nodeHasChildren AND $nodeIsSelectedSubCat) {
                    $nodeExpandedState = true;
                    $modelCategories = new Default_Model_DbTable_ProjectCategory();
                    $immChildren = $modelCategories->fetchImmediateChildren($child['project_category_id']);
                    $nodeChildren = $this->generateResponseMsg($immChildren, $inputParams, $selChild);
                }
                $result[] = array(
                    'title' => $child['title'],
                    'key' => $child['project_category_id'],
                    'folder' => true,
                    'lazy' => true,
                    'selected' => $nodeSelectedState,
                    'expanded' => $nodeExpandedState,
                    'href' => $helperBuildExploreUrl->buildExploreUrl($inputParams['category'],
                        $child['project_category_id'], $inputParams['order']),
                    'target' => '_top',
                    'children' => $nodeChildren
                );
            }
        }
        return $result;
    }

    public function indexAction()
    {
        $storeCatIds = Zend_Registry::isRegistered('store_category_list') ? Zend_Registry::get('store_category_list') : null;
        $this->view->categories = $storeCatIds;
        
        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
        $storePackageTypeIds = null;
        if($storeConfig) {
            $storePackageTypeIds = $storeConfig['package_type'];
            $this->view->package_type = $storePackageTypeIds;
        }
        // Filter-Parameter
        $inputCatId = (int)$this->getParam('cat', null);

        if ($inputCatId) {
            $this->view->isFilterCat = true;
            $this->view->filterCat = $inputCatId;
            $this->view->catabout = $this->getCategoryAbout($inputCatId);

            $helperFetchCategory = new Default_View_Helper_CatTitle();
            $catTitle = $helperFetchCategory->catTitle($inputCatId);

            $this->view->headTitle($catTitle . ' - ' . $_SERVER['HTTP_HOST'], 'SET');
        }

        $this->view->cat_id = $inputCatId;

        $filter['category'] = $inputCatId ? $inputCatId : $storeCatIds;
        $filter['order'] = $this->getParam('ord', 'latest');
        if($storePackageTypeIds) {
            $filter['package_type'] = $storePackageTypeIds;
        }

        $page = (int)$this->getParam('page', 1);
        $pageLimit = 10;

        $requestedElements = $this->fetchRequestedElements($filter, $pageLimit, ($page - 1) * $pageLimit);

        $paginator = Local_Paginator::factory($requestedElements['elements']);
        $paginator->setItemCountPerPage($pageLimit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setTotalItemCount($requestedElements['total_count']);

        $this->view->products = $paginator;
        $this->view->totalcount = $requestedElements['total_count'];
        $this->view->filters = $filter;
        $this->view->page = $page;
    }

    /**
     * @param $inputCatId
     * @return string|null
     */
    protected function getCategoryAbout($inputCatId)
    {
        $config = Zend_Registry::get('config');
        $static_config = $config->settings->static;

        $include_path_cat = $static_config->include_path . 'category_about/' . $inputCatId . '.phtml';
        if (file_exists($include_path_cat)) {
            return $include_path_cat;
        }

        return null;
    }

    /**
     * @param array $inputFilterParams
     * @param int $limit
     * @param int $offset
     * @return array
     */
    private function fetchRequestedElements($inputFilterParams, $limit = null, $offset = null)
    {
        $modelProject = new Default_Model_Project();
        $requestedElements = $modelProject->fetchProjectsByFilter($inputFilterParams, $limit, $offset);
        return $requestedElements;
    }

    public function carouselAction()
    {
        $tableProject = new Default_Model_Project();
        $tableCategories = new Default_Model_DbTable_ProjectCategory();

        $this->view->whatsHot = array('Popular' => 'week', 'New' => 'new');
        $categories = $tableCategories->fetchTree(25, 0, true, false, 1);
        $this->view->categories = array_merge(array(
            array(
                'title' => 'Remove filter',
                'project_category_id' => '',
                'rgt' => '0',
                'lft' => '0'
            )
        ),
            $categories);

        $requestedFilter['ranking'] = array_search($this->getParam('ranking', 'week'), $this->view->whatsHot);
        $requestedFilter['category'] = $this->getParam('category',
            null) ? $tableCategories->find($this->getParam('category',
            null))->current()->title : 'Categories';
        $this->view->requestedFilter = $requestedFilter;

        // Filter-Parameter
        $inputFilterParams['ranking'] = $this->getParam('ranking', 'week');
        $inputFilterParams['category'] = $this->getParam('category', null);

        $page = (int)$this->getParam('page', 1);

        $today = new DateTime();

        $goals = $tableProject->fetchElementsByFilter($inputFilterParams, null /*$today->format(DateTime::ISO8601)*/);
        $paginator = Zend_Paginator::factory($goals);
        $paginator->setItemCountPerPage(21);
        $paginator->setCurrentPageNumber($page);

        $this->view->products = $paginator;
        $this->view->inputFilter = $inputFilterParams;
        $this->view->page = $page + 1;
    }

    public function pageAction()
    {
        $this->_helper->layout->disableLayout();
        $tableProject = new Default_Model_Project();

        // Filter-Parameter
        $inputFilterParams['ranking'] = $this->getParam('ranking', 'week');
        $inputFilterParams['category'] = $this->getParam('category', null);

        $page = (int)$this->getParam('page', 1);

        $today = new DateTime();

        $goals = $tableProject->fetchElementsByFilter($inputFilterParams, null /*$today->format(DateTime::ISO8601)*/);
        $paginator = Zend_Paginator::factory($goals);
        $paginator->setItemCountPerPage(40);

        $this->view->products = $paginator->setCurrentPageNumber($page);
        $this->view->inputFilter = $inputFilterParams;
        $this->view->page = $page + 1;
    }

    public function searchAction()
    {
        ini_set('memory_limit', '3072M');

        $allDomainCatIds = Zend_Registry::isRegistered('store_category_list') ? Zend_Registry::get('store_category_list') : null;
        if (count($allDomainCatIds) == 0) {
            $allDomainCatIds = null;
        }

        if (isset($allDomainCatIds)) {
            $this->view->categories = $allDomainCatIds;
        } else {
            $modelCategories = new Default_Model_DbTable_ProjectCategory();
            $this->view->categories = $modelCategories->fetchMainCatIdsOrdered();
        }

        // Filter-Parameter
        $filterInput = new Zend_Filter_Input(
            array('*' => 'StringTrim', 'projectSearchText' => 'StripTags', 'page' => 'digits'),
            array(
                'projectSearchText' => array(
                    new Zend_Validate_StringLength(array('min' => 3, 'max' => 100)),
                    'presence' => 'required'
                ),
                'page' => 'digits'
            ),
            $this->getAllParams()
        );

        if ($filterInput->hasInvalid()) {
            $this->_helper->viewRenderer('searchError');
            $this->view->messages = $filterInput->getMessages();
            return;
        }

        $inputFilterParams['projectSearchText'] = $filterInput->getUnescaped('projectSearchText');
        $page = (int)$filterInput->getEscaped('page');

        $config = Zend_Registry::get('config');
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive()
        );

        $dataPath = $config->settings->search->path;
        $dataPath .= $this->getNameForStoreClient() . DIRECTORY_SEPARATOR;
        $index = Zend_Search_Lucene::open($dataPath);
        try {
            $hits = $index->find($inputFilterParams['projectSearchText'] . '*');
        } catch (Zend_Search_Lucene_Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $hits = array();
        }

        if (count($hits) == 0) {
            $this->_helper->viewRenderer('searchError');
            $this->view->inputFilter = $inputFilterParams;
            $this->view->searchText = $inputFilterParams['projectSearchText'];
            return;
        }

        $results = $this->copyToArray($hits);
        $paginator = Zend_Paginator::factory($results);
        $paginator->setDefaultItemCountPerPage(10);
        $paginator->setCurrentPageNumber($page);

        $this->view->hitsCount = count($hits);
        $this->view->hits = $paginator;
        $this->view->page = $page;
        $this->view->inputFilter = $inputFilterParams;
        $this->view->searchText = $inputFilterParams['projectSearchText'];
    }

    /**
     * @param array $hits
     * @return array
     */
    protected function copyToArray($hits)
    {
        $returnArray = array();
        /** @var $hit Zend_Search_Lucene_Search_QueryHit */
        foreach ($hits as $hit) {
            $returnArray[] = $hit->getDocument();
        }
        return $returnArray;
    }

    /**
     * @return string|null
     * @throws Zend_Exception
     */
    protected function getStoreAbout($static_config)
    {
        $include_path = $static_config->include_path . 'store_about/' . $this->view->filterStore . '.phtml';
        if (file_exists($include_path)) {
            return $include_path;
        }
        return null;
    }

    /**
     * @param array $elements
     * @return array with additional info's
     */
    private function fetchAdditionalData($elements)
    {
        $modelProject = new Default_Model_Project();
        $requestedElements = Array();

        foreach ($elements as $project) {
            $info = $modelProject->fetchProductInfo($project['project_id']);
            $requestedElements[] = $info;
        }
        return $requestedElements;
    }

}