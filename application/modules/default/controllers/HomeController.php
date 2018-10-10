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
            $this->view->package_type = $storeConfig->package_type;
            if($storeConfig->isShowHomepage())
            {
                 $this->_helper->viewRenderer('index-' . $storeConfig->config_id_name);
                 return;
            }
        }
        $params = array('ord' => 'latest');
        if ($this->hasParam('domain_store_id')) {
            $params['domain_store_id'] = $this->getParam('domain_store_id');
        }
        $this->forward('index', 'explore', 'default', $params);        
    }


    public function indexAction_()
    {
        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
        $storePackageTypeIds = null;
        if ($storeConfig) {
            $this->view->package_type = $filter['package_type'] = $storeConfig['package_type'];
        }

        Zend_Registry::get('logger')->debug('*** SHOW_HOME_PAGE: ' . getenv('SHOW_HOME_PAGE'));
        /**
         *  The SHOW_HOME_PAGE environment var will be set in apache .htaccess for some specific host names
         *  e.g.
         *  SetEnvIfNoCase Host opendesktop\.org$ SHOW_HOME_PAGE
         */
        if (false == $this->hasParam('domain_store_id') AND getenv('SHOW_HOME_PAGE')) {
            $this->_helper->viewRenderer('index-' . $this->getNameForStoreClient());
            return;
        }

        // forward is the faster way, but you have no influence to the url. On redirect the url changes.
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
         if($page==0){
                $featureProducts = $modelInfo->getRandProduct();
                $featureProducts->setItemCountPerPage(1);
                $featureProducts->setCurrentPageNumber(1);
            }else{
                $featureProducts = $modelInfo->getFeaturedProductsForHostStores(100);
                if($featureProducts->getTotalItemCount() > 0){
                    $offset = (int)$this->getParam('page');
                    $irandom = rand(1,$featureProducts->getTotalItemCount());
                    $featureProducts->setItemCountPerPage(1);
                    $featureProducts->setCurrentPageNumber($irandom);
                }
            }


        if ($featureProducts->getTotalItemCount() > 0) {
            $this->view->featureProducts = $featureProducts;
            $this->_helper->viewRenderer('/partials/featuredProducts');
            // $this->_helper->json($featureProducts);
        }
    }
    
    
    public function metamenujsAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        header('Access-Control-Allow-Origin: *'); 
        
        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', '*')
             ->setHeader('Access-Control-Allow-Credentials', 'true')
             ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept')
        ;
        
        
        $baseurl = Zend_Registry::get('config')->settings->client->default->baseurl;
        $url_forum = Zend_Registry::get('config')->settings->client->default->url_forum;
        $url_blog = Zend_Registry::get('config')->settings->client->default->url_blog;

        $sname = Zend_Registry::get('store_host');
        $json_menu = $this->fetchMetaheaderMenuJson();
        
        echo '<script>';
        echo '    var domains = '.$json_menu.';';
        //echo '    var user = '.$json_user.';';
        echo '    var baseUrl = '."$baseurl".';';
        echo '    var blogUrl = '."$url_blog".';';
        //echo '    var loginUrl = '."$loginUrl".';';
        echo '    var sName = '."$sname".';';
        echo '</script>';
        
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
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept')
        ;
        
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
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept')
        ;
        
        
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
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept')
        ;
        
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
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept')
        ;
        
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
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept')
        ;
        
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
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept')
        ;
        
        
        $resultArray = array();
        
        
        $domainobjects = $this->fetchMetaheaderMenuJson();

        $resultArray['domains'] = $domainobjects;
        
        $resultAll = array();
        $resultAll['status'] = "success";
        $resultAll['data'] = $resultArray;
        
        $this->_helper->json($resultAll);
    }
    

    protected function setLayout()
    {
        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;      
        if($storeConfig  && $storeConfig->layout_home)
        {
             $this->_helper->layout()->setLayout($storeConfig->layout_home);
        }else{
            $this->_helper->layout()->setLayout('home_template');
        }        
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
            $arrayRest =  array();  
           
            foreach ($result as $obj) {
                $o =  $obj['order'];   
                $curOrder = floor($obj['order']/1000);      
                if($curOrder<10 or $curOrder>50) continue;
                $obj['calcOrder'] = $curOrder;              

                $tmp = array();
                $tmp['order'] = $obj['order'];
                $tmp['calcOrder'] = $obj['calcOrder'];
                $tmp['host'] = $obj['host'];
                $tmp['name'] = $obj['name'];                

                if($curOrder==30) {
                    // Desktop set calcOrder = 9 manuelly put desktop in front                    
                    $tmp['calcOrder'] = 9;
                    $arrayDesktop[] = $tmp;    
                }else{
                    $arrayRest[] = $tmp;    
                }                        
            }
            $domainobjects = array_merge($arrayDesktop, $arrayRest);

            
            $baseurl = Zend_Registry::get('config')->settings->client->default->baseurl;
            // set group name manully
            foreach ($domainobjects as &$obj) {

                    if($sname == $obj['host']){
                        $obj['menuactive'] = 1;
                    }else{
                        $obj['menuactive'] = 0;
                    }

                    $order =  $obj['order'];
                     // z.b 150001 ende ==1 go real link otherwise /s/$name
                    $last_char_check = substr($order, -1);
                    if($last_char_check=='1')
                    {
                        $obj['menuhref'] = $obj['host'];
                    }else{
                        $obj['menuhref'] = $baseurl.'/s/'.$obj['name'];
                    }

                    switch ($obj['calcOrder']) {
                        case 9:
                            $obj['menugroup']='Desktops';
                            break;
                        case 10:
                            $obj['menugroup']='Applications';
                            break;
                        case 20:
                            $obj['menugroup']='Addons';
                            break;
                        case 40:
                            $obj['menugroup']='Artwork';
                            break;                       
                        case 50:
                        $obj['menugroup']='Other';
                        break;
                    }
                         
            }

            $cache->save($domainobjects, $cacheName, array(), 28800);
        }
        return  Zend_Json::encode($domainobjects);
    }

}
