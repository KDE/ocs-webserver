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
class HomeController extends Local_Controller_Action_DomainSwitch
{
    public function indexAction()
    {
       
        /** @var Default_Model_ConfigStore $storeConfig */        
        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;

        if ($storeConfig) {
            $this->view->tag_filter = Zend_Registry::isRegistered('config_store_tags') ? Zend_Registry::get('config_store_tags') : null;

            if ($storeConfig->isShowHomepage()) {
                $index = $this->getParam('index');
                if ($index) {
                    if ((int)$index == 1) {
                        $this->_helper->viewRenderer('index-' . $storeConfig->config_id_name . '-test');
                    } else {
                        $this->_helper->viewRenderer('index-' . $storeConfig->config_id_name . '-test' . $index);
                    }

                    $this->view->index = $index;
                } else {

                    if ($storeConfig->config_id_name=='opendesktop' && !Zend_Auth::getInstance()->hasIdentity()){
                        $this->forward('start', 'home', 'default',null);
                    }else{
                        $this->_helper->viewRenderer('index-' . $storeConfig->config_id_name);
                    }                            
                }

                return;
            }

        }
        $params = array('ord' => 'latest');
        if ($this->hasParam('domain_store_id')) {
            $params['domain_store_id'] = $this->getParam('domain_store_id');
        }
        $this->forward('index', 'explore', 'default', $params);
    }

    public function startAction()
    {
        
        if (!Zend_Auth::getInstance()->hasIdentity()){
            $this->forward('index', 'explore', 'default', $params);
        }
        
        /** @var Default_Model_ConfigStore $storeConfig */
        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;

        if ($storeConfig) {
            $this->view->tag_filter = Zend_Registry::isRegistered('config_store_tags') ? Zend_Registry::get('config_store_tags') : null;
            if ($storeConfig->isShowHomepage()) {
                //index-opendesktop-start.phtml view
                $this->_helper->viewRenderer('index-' . $storeConfig->config_id_name . '-start');

                return;
            }
        }
        $params = array('ord' => 'latest');
        if ($this->hasParam('domain_store_id')) {
            $params['domain_store_id'] = $this->getParam('domain_store_id');
        }
        $this->forward('index', 'explore', 'default', $params);
    }

    public function showfeatureajaxAction()
    {
        $this->_helper->layout->disableLayout();
        $modelInfo = new Default_Model_Info();
        $page = (int)$this->getParam('page');
        $type = '';
        if ($page == 0) {
            $featureProducts = $modelInfo->getRandProduct();
            $type = '';

        } elseif ($page == 1) {
            $featureProducts = $modelInfo->getRandFeaturedProduct();
            $type = 'Featured';

        } elseif ($page == 2) {
            $featureProducts = $modelInfo->getRandPlingedProduct();
            $type = 'Plinged';

        }


        $this->view->featureProducts = $featureProducts;
        $this->view->type = $type;
        $this->_helper->viewRenderer('/partials/featuredProducts');
    }


    public function showlastproductsjsonAction()
    {

        $this->_helper->layout->disableLayout();
        $modelInfo = new Default_Model_Info();
        $offset = (int)$this->getParam('offset', 0);
        $limit = (int)$this->getParam('limit', 5);
        $catIds = $this->getParam('catIDs');
        $tags = $this->getParam('ptype');
        $isOriginal = $this->getParam('isoriginal');
        $response = $modelInfo->getJsonLastProductsForHostStores($limit, $catIds, $tags, $isOriginal, $offset);
        $this->_helper->json(Zend_Json::decode($response));
    }


    public function getnewactiveplingedproductjsonAction()
    {

        $this->_helper->layout->disableLayout();
        $modelInfo = new Default_Model_Info();
        $offset = (int)$this->getParam('offset', 0);
        $limit = (int)$this->getParam('limit', 5);
        $response = $modelInfo->getJsonNewActivePlingProduct($limit, $offset);
        $this->_helper->json(Zend_Json::decode($response));
    }


    public function showfeaturejsonAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $modelInfo = new Default_Model_Info();
        $page = (int)$this->getParam('page');
        if ($page == 0) {
            $featureProducts = $modelInfo->getRandProduct();
            $featureProducts->setItemCountPerPage(1);
            $featureProducts->setCurrentPageNumber(1);
        } else {
            $featureProducts = $modelInfo->getFeaturedProductsForHostStores(100);
            if ($featureProducts->getTotalItemCount() > 0) {
                $offset = (int)$this->getParam('page');
                $irandom = rand(1, $featureProducts->getTotalItemCount());
                $featureProducts->setItemCountPerPage(1);
                $featureProducts->setCurrentPageNumber($irandom);
            }
        }

        $item = null;
        foreach ($featureProducts as $i) {
            $item = $i;
            break;
        }
        $helpCategories = new Default_View_Helper_FetchCategoriesForProductAsString();
        $response = array(
            'project_id'        => $item['project_id'],
            'member_id'         => $item['member_id'],
            'username'          => $item['username'],
            'profile_image_url' => $item['profile_image_url'],
            'featured'          => $item['featured'],
            'description'       => $item['description'],
            'title'             => $item['title'],
            'category'          => $helpCategories->fetchCategoriesForProductAsString($item['project_id']),
            'image_small'       => $item['image_small'],
            'laplace_score'     => $item['laplace_score'],
            'count_likes'       => $item['count_likes'],
            'count_dislikes'    => $item['count_dislikes'],
            'changed_at'        => $item['changed_at'],
            'created_at'        => $item['created_at'],
            'count_comments'    => $item['count_comments']
        );
        $this->_helper->json($response);
    }


    public function showspotlightjsonAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $modelInfo = new Default_Model_Info();
        $page = (int)$this->getParam('page');
        $mostplingedcreateor = $modelInfo->getMostPlingedCreators(1, $page);

        $creator = array_pop($mostplingedcreateor);
        $helperImage = new Default_View_Helper_Image();
        $creator['profile_image_url'] = $helperImage->Image($creator['profile_image_url'],
            array('width' => 200, 'height' => 200));
        $products = $modelInfo->getMostPlingedProductsForUser($creator['member_id'], 5, 0);
        foreach ($products as &$p) {
            $p['image_small'] = $helperImage->Image($p['image_small'], array('width' => 200, 'height' => 200));
        }
        $creator['products'] = $products;
        $this->_helper->json($creator);
    }


    public function metamenujsAction()
    {
        $this->_helper->layout()->disableLayout();

        header('Access-Control-Allow-Origin: *');

        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', '*')
             ->setHeader('Access-Control-Allow-Credentials', 'true')
             ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept');

    }

    public function metamenubundlejsAction()
    {
        $this->_helper->layout()->disableLayout();
    }

    public function searchmemberAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $username = $this->getParam('username');
        $results = null;
        if (strlen(trim($username)) > 2) {
            $model = new Default_Model_Member();
            $results = $model->findActiveMemberByName($username);
            $helperImage = new Default_View_Helper_Image();
            foreach ($results as &$value) {
                $avatar = $helperImage->image($value['profile_image_url'],
                    array('width' => 100, 'height' => 100, 'crop' => 2));
                $value['profile_image_url'] = $avatar;
            }
        }
        $this->_helper->json($results);
    }

    public function memberjsonAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $member_id = $this->getParam('member_id');
        $results = null;
        if ($member_id) {
            $info = new Default_Model_Info();
            $commentsOpendeskop = $info->getDiscussionOpendeskop($member_id);
            $results = array('commentsOpendeskop' => $commentsOpendeskop);
        }
        $this->_helper->json($results);
    }


    public function baseurlajaxAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        header('Access-Control-Allow-Origin: *');

        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', '*')
             ->setHeader('Access-Control-Allow-Credentials', 'true')
             ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept');

        $resultArray = array();

        $baseurl = Zend_Registry::get('config')->settings->client->default->baseurl;

        $resultArray['base_url'] = $baseurl;

        $resultAll = array();
        $resultAll['status'] = "success";
        $resultAll['data'] = $resultArray;

        $this->_helper->json($resultAll);
    }

    public function forumurlajaxAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        header('Access-Control-Allow-Origin: *');

        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', '*')
             ->setHeader('Access-Control-Allow-Credentials', 'true')
             ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept');


        $resultArray = array();

        $url_forum = Zend_Registry::get('config')->settings->client->default->url_forum;

        $resultArray['url_forum'] = $url_forum;

        $resultAll = array();
        $resultAll['status'] = "success";
        $resultAll['data'] = $resultArray;

        $this->_helper->json($resultAll);
    }

    public function blogurlajaxAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        header('Access-Control-Allow-Origin: *');

        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', '*')
             ->setHeader('Access-Control-Allow-Credentials', 'true')
             ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept');

        $resultArray = array();

        $url_blog = Zend_Registry::get('config')->settings->client->default->url_blog;

        $resultArray['url_blog'] = $url_blog;

        $resultAll = array();
        $resultAll['status'] = "success";
        $resultAll['data'] = $resultArray;

        $this->_helper->json($resultAll);
    }


    public function storenameajaxAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        header('Access-Control-Allow-Origin: *');

        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', '*')
             ->setHeader('Access-Control-Allow-Credentials', 'true')
             ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept');

        $resultArray = array();

        $sname = Zend_Registry::get('store_host');

        $resultArray['store_name'] = $sname;

        $resultAll = array();
        $resultAll['status'] = "success";
        $resultAll['data'] = $resultArray;

        $this->_helper->json($resultAll);
    }


    public function loginurlajaxAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        header('Access-Control-Allow-Origin: *');

        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', '*')
             ->setHeader('Access-Control-Allow-Credentials', 'true')
             ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept');

        $resultArray = array();

        $url = $this->getParam('url');
        $filterRedirect = new Local_Filter_Url_Encrypt();

        $loginUrl = '/login?redirect=' . $filterRedirect->filter($url);

        $resultArray['login_url'] = $loginUrl;

        $resultAll = array();
        $resultAll['status'] = "success";
        $resultAll['data'] = $resultArray;

        $this->_helper->json($resultAll);
    }


    public function domainsajaxAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        header('Access-Control-Allow-Origin: *');

        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', '*')
             ->setHeader('Access-Control-Allow-Credentials', 'true')
             ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept');


        $resultArray = array();


        $domainobjects = $this->fetchMetaheaderMenuJson();
        $resultArray['domains'] = $domainobjects;

        $storeobjects = $this->fetchStoresForCatTreeJson();
        $resultArray['storefortree'] = $storeobjects;


        $resultAll = array();
        $resultAll['status'] = "success";
        $resultAll['data'] = $resultArray;

        $this->_helper->json($resultAll);
    }

    private function fetchMetaheaderMenuJson()
    {

        $sname = Zend_Registry::get('store_host');
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . md5($sname);

        if (false == ($domainobjects = $cache->load($cacheName))) {
            $tbl = new Default_Model_DbTable_ConfigStore();
            $result = $tbl->fetchDomainObjects();
            // sort Desktop manuelly to the front
            $arrayDesktop = array();
            $arrayRest = array();

            foreach ($result as $obj) {
                $o = $obj['order'];
                $curOrder = floor($obj['order'] / 1000);
                if ($curOrder < 10 or $curOrder > 50) {
                    continue;
                }
                $obj['calcOrder'] = $curOrder;

                $tmp = array();
                $tmp['order'] = $obj['order'];
                $tmp['calcOrder'] = $obj['calcOrder'];
                $tmp['host'] = $obj['host'];
                $tmp['name'] = $obj['name'];
                $tmp['is_show_in_menu'] = $obj['is_show_in_menu'];
                $tmp['is_show_real_domain_as_url'] = $obj['is_show_real_domain_as_url'];

                if ($curOrder == 30) {
                    // Desktop set calcOrder = 9 manuelly put desktop in front
                    $tmp['calcOrder'] = 9;
                    $arrayDesktop[] = $tmp;
                } else {
                    $arrayRest[] = $tmp;
                }
            }
            $domainobjects = array_merge($arrayDesktop, $arrayRest);


            $baseurl = Zend_Registry::get('config')->settings->client->default->baseurl;
            // set group name manully
            foreach ($domainobjects as &$obj) {

                if ($sname == $obj['host']) {
                    $obj['menuactive'] = 1;
                } else {
                    $obj['menuactive'] = 0;
                }

                $order = $obj['order'];
                //OLD: z.b 150001 ende ==1 go real link otherwise /s/$name
                /*$last_char_check = substr($order, -1);
                if($last_char_check=='1')
                {
                    $obj['menuhref'] = $obj['host'];
                }else{
                    $obj['menuhref'] = $baseurl.'/s/'.$obj['name'];
                }
                 *
                 */
                $domainAsUrl = $obj['is_show_real_domain_as_url'];
                if ($domainAsUrl) {
                    $obj['menuhref'] = $obj['host'];
                } else {
                    $obj['menuhref'] = $baseurl . '/s/' . $obj['name'];
                }

                switch ($obj['calcOrder']) {
                    case 9:
                        $obj['menugroup'] = 'Desktops';
                        break;
                    case 10:
                        $obj['menugroup'] = 'Applications';
                        break;
                    case 20:
                        $obj['menugroup'] = 'Addons';
                        break;
                    case 40:
                        $obj['menugroup'] = 'Artwork';
                        break;
                    case 50:
                        $obj['menugroup'] = 'Other';
                        break;
                }

            }

            $cache->save($domainobjects, $cacheName, array(), 28800);
        }

        return Zend_Json::encode($domainobjects);
    }

    private function fetchStoresForCatTreeJson()
    {
        $sname = Zend_Registry::get('store_host');
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . md5($sname);

        if (false == ($domain_objects = $cache->load($cacheName))) {
            $tbl = new Default_Model_DbTable_ConfigStore();
            $result = $tbl->fetchDomainObjects();
            // sort Desktop manually to the front
            $arrayDesktop = array();
            $arrayRest = array();

            foreach ($result as $obj) {
                $tmp = array();
                $tmp['order'] = $obj['order'];
                $tmp['calcOrder'] = $obj['calcOrder'];
                $tmp['host'] = $obj['host'];
                $tmp['name'] = $obj['name'];
                $tmp['is_show_in_menu'] = $obj['is_show_in_menu'];
                $tmp['is_show_real_domain_as_url'] = $obj['is_show_real_domain_as_url'];

                $arrayRest[] = $tmp;
            }
            $domain_objects = array_merge($arrayDesktop, $arrayRest);


            $baseurl = Zend_Registry::get('config')->settings->client->default->baseurl;
            // set group name manully
            foreach ($domain_objects as &$obj) {

                if ($sname == $obj['host']) {
                    $obj['menuactive'] = 1;
                } else {
                    $obj['menuactive'] = 0;
                }

                $order = $obj['order'];
                //OLD: z.b 150001 ende ==1 go real link otherwise /s/$name
                /*$last_char_check = substr($order, -1);
                if($last_char_check=='1')
                {
                    $obj['menuhref'] = $obj['host'];
                }else{
                    $obj['menuhref'] = $baseurl.'/s/'.$obj['name'];
                }
                 *
                 */
                $domainAsUrl = $obj['is_show_real_domain_as_url'];
                if ($domainAsUrl) {
                    $obj['menuhref'] = $obj['host'];
                } else {
                    $obj['menuhref'] = $baseurl . '/s/' . $obj['name'];
                }
            }

            $cache->save($domain_objects, $cacheName, array(), 28800);
        }

        return Zend_Json::encode($domain_objects);
    }

    /**
     * @throws Exception
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     */
    public function redirectmeAction()
    {
        $this->_helper->layout()->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);

        $redir = "/";
        if (isset($_GET['redirect'])) {
            $redir = $_GET['redirect'];
            $filter = new Local_Filter_Url_Decrypt();
            $redir = $filter->filter($redir);

        }
        $this->view->redirect = $redir;
    }

    public function fetchforgitAction()
    {
        $this->_helper->layout()->disableLayout();

        $tableProject = new Default_Model_Project();

        $params = $this->getAllParams();
        $action = 'fetch-products-for-git-project-id';
        if ($this->hasParam('target')) {
            $action = $params['target'];
        }

        if ($action == 'fetch-products-for-git-project-id') {


            $gitProjectId = $params['project_id'];
            $this->view->products = null;

            if (isset($gitProjectId)) {

                $products = $tableProject->fetchAll('status = 100 AND type_id = 1 AND is_gitlab_project = 1 AND gitlab_project_id = ' . $gitProjectId);


                $helperImage = new Default_View_Helper_Image();

                $viewArray = array();
                $viewProjectArray = array();
                foreach ($products as $product) {
                    $viewProjectArray = array();
                    $viewProjectArray['project_id'] = $product['project_id'];
                    $viewProjectArray['title'] = $product['title'];
                    $viewProjectArray['version'] = $product['version'];
                    $viewProjectArray['member_id'] = $product['member_id'];
                    $viewProjectArray['avatar'] = $helperImage->Image($product['image_small'],
                        array('width' => 100, 'height' => 100));
                    $viewArray[] = $viewProjectArray;
                }

                $this->view->viewdata = $viewArray;

            }
        } else {
            $this->view->viewdata = array();
        }


    }

    protected function setLayout()
    {
        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
        if ($storeConfig && $storeConfig->layout_home) {
            $this->_helper->layout()->setLayout($storeConfig->layout_home);
        } else {
            $this->_helper->layout()->setLayout('home_template');
        }
    }

}
