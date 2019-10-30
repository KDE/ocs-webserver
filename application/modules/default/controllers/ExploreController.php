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
    const DEFAULT_ORDER = 'latest';
    const TAG_ISORIGINAL = 'original-product';

    /** @var  string */
    protected $_browserTitlePrepend;

    public function init()
    {
        parent::init();
        $this->_auth = Zend_Auth::getInstance();
    }

    public function categoriesAction()
    {
        // Filter-Parameter
        $inputFilterParams['category'] = (int)$this->getParam('cat', null);
        $inputFilterParams['filter'] = (int)$this->getParam('fil', null);
        $inputFilterParams['order'] = preg_replace('/[^-a-zA-Z0-9_]/', '', $this->getParam('ord', self::DEFAULT_ORDER));
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
                    'title'    => $child['title'],
                    'key'      => $child['project_category_id'],
                    'href'     => $helperBuildExploreUrl->buildExploreUrl($inputParams['category'], $child['project_category_id'],
                        $inputParams['order']),
                    'target'   => '_top',
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
                    'title'    => $child['title'],
                    'key'      => $child['project_category_id'],
                    'folder'   => true,
                    'lazy'     => true,
                    'selected' => $nodeSelectedState,
                    'expanded' => $nodeExpandedState,
                    'href'     => $helperBuildExploreUrl->buildExploreUrl($inputParams['category'], $child['project_category_id'],
                        $inputParams['order']),
                    'target'   => '_top',
                    'children' => $nodeChildren
                );
            }
        }

        return $result;
    }

    /**
     * @throws Zend_Cache_Exception
     * @throws Zend_Db_Select_Exception
     * @throws Zend_Exception
     * @throws Zend_Loader_PluginLoader_Exception
     * @throws Zend_Paginator_Exception
     */
    public function indexAction()
    {
        // Filter-Parameter
        /*$inputFilterOriginal = $this->getParam('filteroriginal', $this->getFilterOriginalFromCookie());
        $this->storeFilterOriginalInCookie($inputFilterOriginal);
        $this->view->inputFilterOriginal = $inputFilterOriginal;
*/
        
        $isShowOnlyFavs = (int)$this->getParam('fav', 0);
        
        
        $inputCatId = (int)$this->getParam('cat', null);
        if ($inputCatId) {
//            $this->view->isFilterCat = true;
//            $this->view->filterCat = $inputCatId;
            $this->view->catabout = $this->getCategoryAbout($inputCatId);

            $helperFetchCategory = new Default_View_Helper_CatTitle();
            $catTitle = $helperFetchCategory->catTitle($inputCatId);

            //$this->view->headTitle($catTitle . ' - ' . $_SERVER['HTTP_HOST'], 'SET');
            $this->view->headTitle($catTitle . ' - ' . $this->getHeadTitle(), 'SET');
        }

        $this->view->cat_id = $inputCatId;
        $this->view->showAddProduct = 1;
        
        $storeCatIds = Zend_Registry::isRegistered('store_category_list') ? Zend_Registry::get('store_category_list') : null;

        $filter = array();
        $filter['category'] = $inputCatId ? $inputCatId : $storeCatIds;
        $filter['order'] = preg_replace('/[^-a-zA-Z0-9_]/', '', $this->getParam('ord', self::DEFAULT_ORDER));
        // removed filter original 20191007
        //$filter['original'] = $inputFilterOriginal == 1 ? self::TAG_ISORIGINAL : null;
        
        if($isShowOnlyFavs == 1) {
            if(null != $this->_authMember) {
                $filter['favorite'] = $this->_authMember->member_id;
            }
        }
        
        $filter['tag'] = Zend_Registry::isRegistered('config_store_tags') ?  Zend_Registry::get('config_store_tags') : null;
        if (APPLICATION_ENV == "development") {
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . json_encode($filter));
        }
        
        $tagFilter  = Zend_Registry::isRegistered('config_store_tags') ? Zend_Registry::get('config_store_tags') : null;
        
        
        $tagGroupFilter  = Zend_Registry::isRegistered('config_store_taggroups') ? Zend_Registry::get('config_store_taggroups') : null;
        if(!empty($tagGroupFilter)) {
            $filterArray = array();
            foreach ($tagGroupFilter as $tagGroupId) {
                $inputFilter = $this->getFilterTagFromCookie($tagGroupId);
                $filterArray[$tagGroupId] = $inputFilter;
                if(!empty($inputFilter)) {
                    $filter['tag'][] = $inputFilter;
                }
            }
            $this->view->tag_group_filter = $filterArray;
        }
        
        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;

        $page = (int)$this->getParam('page', 1);

        $index = null;
        $browseListType = null;
        
        //Old: index as Param
        $index = $this->getParam('index');
        
        if($index) {
            if($index == 2) {
                $browseListType = 'picture';
            }
            if($index == 3) {
                $browseListType = 'music';
            }
            if($index == 4) {
                $browseListType = 'phone-pictures';
            }
            
        }
        
        if($storeConfig->browse_list_type) {
            $listTypeTable = new Default_Model_DbTable_BrowseListType();
            $listType = $listTypeTable->findBrowseListType($storeConfig->browse_list_type);
            if(isset($listType)) {
               $browseListType =  $listType['name'];
               $index = 2;
            }
        }

        //Browse List config in Category set?
        if(!$index) {
            //Now the list type is in backend categories set
            $tableCat = new Default_Model_DbTable_ProjectCategory();
            $cat = $tableCat->findCategory($this->view->cat_id);
            if(isset($cat) && isset($cat['browse_list_type'])) {
                $indexListType = $cat['browse_list_type'];
                $listTypeTable = new Default_Model_DbTable_BrowseListType();
                $listType = $listTypeTable->findBrowseListType($indexListType);
                if(isset($listType)) {
                   $browseListType =  $listType['name'];
                   $index = 2;
                }
            }
        }
        
        // my favourite list filter & list layout
        if($isShowOnlyFavs == 1 && null != $this->_authMember) {
            $index=7;
            $browseListType =  'myfav';
        }
        
        if($index)
        {
            // only switch view via index=2 parameter
            $pageLimit = 50;
            $requestedElements = $this->fetchRequestedElements($filter, $pageLimit, ($page - 1) * $pageLimit);
            $this->view->productsJson = Zend_Json::encode($requestedElements['elements']);
            $this->view->filtersJson = Zend_Json::encode($filter);
            $this->view->cat_idJson = Zend_Json::encode($inputCatId);
            $modelInfo = new Default_Model_Info();
            $topprods = $modelInfo->getMostDownloaded(100, $inputCatId, $tagFilter);
            $this->view->topprodsJson = Zend_Json::encode($topprods);
            $comments = $modelInfo->getLatestComments(5, $inputCatId, $tagFilter);
            $this->view->commentsJson = Zend_Json::encode($comments);
            $modelCategory = new Default_Model_ProjectCategory();
            $this->view->categoriesJson = Zend_Json::encode($modelCategory->fetchTreeForView());
            $this->view->browseListType = $browseListType;
            
            $this->view->pageLimit = $pageLimit;
                  
            /*
            // temperately when index=3 return product files too... in the future could be replaced by category parameter.
            if($index==3 || $browseListType == 'music')
            {
                $modelProject = new Default_Model_Project();
                $files = $modelProject->fetchFilesForProjects($requestedElements['elements']);
                $salt = PPLOAD_DOWNLOAD_SECRET;
                foreach ($files as &$file) {
                    $timestamp = time() + 3600; // one hour valid
                    $hash = hash('sha512',$salt . $file['collection_id'] . $timestamp); // order isn't important at all... just do the same when verifying
                    $url = PPLOAD_API_URI . 'files/download/id/' . $file['id'] . '/s/' . $hash . '/t/' . $timestamp;
                    if(null != $this->_authMember) {
                        $url .= '/u/' . $this->_authMember->member_id;
                    }
                    $url .= '/lt/filepreview/' . $file['name'];
                    $file['url'] = urlencode($url);                    
                }
            } else {
                $modelProject = new Default_Model_Project();
                $files = $modelProject->fetchFilesForProjects($requestedElements['elements']);
                $salt = PPLOAD_DOWNLOAD_SECRET;
                foreach ($files as &$file) {
                    $timestamp = time() + 3600; // one hour valid
                    $hash = hash('sha512',$salt . $file['collection_id'] . $timestamp); // order isn't important at all... just do the same when verifying
                    $url = PPLOAD_API_URI . 'files/download/id/' . $file['id'] . '/s/' . $hash . '/t/' . $timestamp;
                    if(null != $this->_authMember) {
                        $url .= '/u/' . $this->_authMember->member_id;
                    }
                    $url .= '/lt/filepreview/' . $file['name'];
                    $file['url'] = urlencode($url);                    
                }
            }
             * 
             */

            $this->_helper->viewRenderer('index-react'.$index);

        }
        else if ($storeConfig->layout_explore && $storeConfig->isRenderReact()) {
            $pageLimit = 50;
            $requestedElements = $this->fetchRequestedElements($filter, $pageLimit, ($page - 1) * $pageLimit);
            $this->view->productsJson = Zend_Json::encode($requestedElements['elements']);
            $this->view->filtersJson = Zend_Json::encode($filter);
            $this->view->cat_idJson = Zend_Json::encode($inputCatId);
            $modelInfo = new Default_Model_Info();
            $topprods = $modelInfo->getMostDownloaded(100, $inputCatId, $tagFilter);
            $this->view->topprodsJson = Zend_Json::encode($topprods);
            $comments = $modelInfo->getLatestComments(5, $inputCatId, $tagFilter);
            $this->view->commentsJson = Zend_Json::encode($comments);
            $modelCategory = new Default_Model_ProjectCategory();
            $this->view->categoriesJson = Zend_Json::encode($modelCategory->fetchTreeForView());
            $this->_helper->viewRenderer('index-react');
            $this->view->pageLimit = $pageLimit;
            
        } else {
            $pageLimit = 10;
            $requestedElements = $this->fetchRequestedElements($filter, $pageLimit, ($page - 1) * $pageLimit);
            $this->view->pageLimit = $pageLimit;
            
        }
        if($storeConfig) {
            $this->view->storeabout = $this->getStoreAbout($storeConfig->store_id);
        }

        $paginator = Local_Paginator::factory($requestedElements['elements']);
        $paginator->setItemCountPerPage($pageLimit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setTotalItemCount($requestedElements['total_count']);

        $this->view->products = $paginator;
        $this->view->totalcount = $requestedElements['total_count'];
        $this->view->filters = $filter;
        $this->view->page = $page;
        $this->view->package_type = Zend_Registry::isRegistered('config_store_tags') ? Zend_Registry::get('config_store_tags') : null;
        $this->view->tags = $tagFilter;
    }

    

    /**
     * @param $inputCatId
     *
     * @return string|null
     * @throws Zend_Exception
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
     *
     * @return array
     * @throws Zend_Cache_Exception
     * @throws Zend_Db_Select_Exception
     * @throws Zend_Exception
     */
    private function fetchRequestedElements($inputFilterParams, $limit = null, $offset = null)
    {
        $modelProject = new Default_Model_Project();
        $requestedElements = $modelProject->fetchProjectsByFilter($inputFilterParams, $limit, $offset);

        return $requestedElements;
    }

    /**
     * @throws Zend_Exception
     * @throws Zend_Paginator_Exception
     */
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
        $filterInput = new Zend_Filter_Input(array('*' => 'StringTrim', 'projectSearchText' => 'StripTags', 'page' => 'digits'), array(
                'projectSearchText' => array(
                    new Zend_Validate_StringLength(array('min' => 3, 'max' => 100)),
                    'presence' => 'required'
                ),
                'page'              => 'digits'
            ), $this->getAllParams());

        if ($filterInput->hasInvalid()) {
            $this->_helper->viewRenderer('searchError');
            $this->view->messages = $filterInput->getMessages();

            return;
        }

        $inputFilterParams['projectSearchText'] = $filterInput->getUnescaped('projectSearchText');
        $page = (int)$filterInput->getEscaped('page');

        $config = Zend_Registry::get('config');
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());

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
     *
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

    protected function _initResponseHeader()
    {
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";

        $this->getResponse()
             ->setHeader('X-FRAME-OPTIONS', 'ALLOWALL', true)
//           ->setHeader('Last-Modified', $modifiedTime, true)
             ->setHeader('Expires', $expires, true)->setHeader('Pragma', 'no-cache', true)
             ->setHeader('Cache-Control', 'private, no-cache, must-revalidate', true)
        ;
    }

    /**
     * @param Zend_Config $static_config
     * @return string|null
     */
    protected function getStoreAbout($storeId)
    {
        $config = Zend_Registry::get('config');
        $static_config = $config->settings->static;

        $include_path_cat = $static_config->include_path . 'store_about/' . $storeId . '.phtml';
        if (file_exists($include_path_cat)) {
            return $include_path_cat;
        }

        return null;
    }

    /**
     * @param array $elements
     *
     * @return array with additional info's
     * @deprecated
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

    protected function setLayout()
    {
        $layoutName = 'flat_ui_template';
        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;      
        if($storeConfig  && $storeConfig->layout_explore)
        {
             $this->_helper->layout()->setLayout($storeConfig->layout_explore);
        }else{
            $this->_helper->layout()->setLayout($layoutName);
        }        
    }

   /* private function storeFilterOriginalInCookie($inputFilterOriginal)
    {
        $storedInCookie = $this->getFilterOriginalFromCookie();

        if (isset($inputFilterOriginal) AND ($inputFilterOriginal != $storedInCookie)) {
                $config = Zend_Registry::get('config');
                $cookieName = $config->settings->session->filter_browse_original;
                $remember_me_seconds = $config->settings->session->remember_me->cookie_lifetime;
                $cookieExpire = time() + $remember_me_seconds;
                setcookie($cookieName, $inputFilterOriginal, $cookieExpire, '/');
        }
    }

    private function getFilterOriginalFromCookie()
    {
        $config = Zend_Registry::get('config');
        $cookieName = $config->settings->session->filter_browse_original;

        $storedInCookie = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : NULL;

        return $storedInCookie;
    }*/
    
    
    private function storeFilterTagInCookie($group, $tag)
    {
        $storedInCookie = $this->getFilterTagFromCookie($group);

        if (isset($tag) AND ($tag != $storedInCookie)) {
                $config = Zend_Registry::get('config');
                $cookieName = $config->settings->session->filter_browse_original.$group;
                $remember_me_seconds = $config->settings->session->remember_me->cookie_lifetime;
                $cookieExpire = time() + $remember_me_seconds;
                setcookie($cookieName, $tag, $cookieExpire, '/');
        }
    }

    private function getFilterTagFromCookie($group)
    {
        $config = Zend_Registry::get('config');
        $cookieName = $config->settings->session->filter_browse_original.$group;

        $storedInCookie = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : NULL;

        return $storedInCookie;
    }
    
    
    public function savetaggroupfilterAction()
    {
        // Filter-Parameter
        $tagGroupId = (int)$this->getParam('group_id', null);
        $tagId = (int)$this->getParam('tag_id', null);
        
        $this->storeFilterTagInCookie($tagGroupId, $tagId);

        $response = array();
        $response['Result'] = 'OK';
        $this->_helper->json($response);
    }

}