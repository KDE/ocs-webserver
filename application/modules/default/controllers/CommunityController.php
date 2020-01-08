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
class CommunityController extends Local_Controller_Action_DomainSwitch
{

    public function indexAction()
    {
        // $allDomainCatIds =
        //     Zend_Registry::isRegistered('store_category_list') ? Zend_Registry::get('store_category_list') : null;

        // $modelCategories = new Default_Model_DbTable_ProjectCategory();
        // if (isset($allDomainCatIds)) {
        //     $this->view->categories = $allDomainCatIds;
        // } else {
        //     $this->view->categories = $modelCategories->fetchMainCatIdsOrdered();
        // }        
        $this->view->noheader = true;
    }

    public function indexreactAction()
    {
        $tableMembers = new Default_Model_Project();        
        $modelInfo = new Default_Model_Info();
        $countProjects = $tableMembers->fetchTotalProjectsCount(false);
        $countActiveMembers = $modelInfo->countTotalActiveMembers();   
        $isadmin = 0;
        if(Zend_Auth::getInstance()->hasIdentity() AND Zend_Auth::getInstance()->getIdentity()->roleName == 'admin') {
            $isadmin = 1;
        }

        $json_data = array(
            'status'     => 'ok',                            
            'data'       => array(
                'countProjects' => $countProjects,
                'countActiveMembers' => $countActiveMembers,
                'isadmin' => $isadmin
            )
        );       
        $this->view->json_data = $json_data;
    }


    public function getjsonAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $event = $this->getParam('e'); 
        $modelInfo = new Default_Model_Info();
        switch($event) {
              case 'supporters':            
                $json_data = array(
                    'status'     => 'ok',                            
                    'data'       => $modelInfo->getNewActiveSupportersForSectionAll(100)
                );  
                $this->view->json_data = $json_data;
                break;
              case 'newmembers':
                $json_data = array(
                    'status'     => 'ok',                            
                    'data'       => $modelInfo->getNewActiveMembers(100)
                );  
                $this->view->json_data = $json_data;
                break;
              case 'topmembers':
                $json_data = array(
                    'status'     => 'ok',                            
                    'data'       => $modelInfo->getTopScoreUsers(100)
                );  
                $this->view->json_data = $json_data;
                break;
               case 'plingedprojects':
                $json_data = array(
                    'status'     => 'ok',                            
                    'data'       => $modelInfo->getNewActivePlingProduct(100)
                );  
                $this->view->json_data = $json_data;
                break;
                case 'mostplingedcreators':
                 $pageLimit = 100;
                 $page = (int)$this->getParam('page', 1);                            
                 $nopage = (int)$this->getParam('nopage', 0);                                             
                 $json_data = array(
                     'status'     => 'ok',     
                     'pageLimit'  => $pageLimit, 
                     'page'       => $page,
                     'nopage'     => $nopage,
                     'totalcount' => $modelInfo->getMostPlingedCreatorsTotalCnt(),
                     'data'       => $modelInfo->getMostPlingedCreators($pageLimit, ($page - 1) * $pageLimit)
                 );  
                 $this->view->json_data = $json_data;
                 break;
                 case 'mostplingedproducts':
                  $pageLimit = 100;
                  $page = (int)$this->getParam('page', 1);                            
                  $nopage = (int)$this->getParam('nopage', 0);                                             
                  $json_data = array(
                      'status'     => 'ok',     
                      'pageLimit'  => $pageLimit, 
                      'page'       => $page,
                      'nopage'     => $nopage,
                      'totalcount' => $modelInfo->getMostPlingedProductsTotalCnt(),
                      'data'       => $modelInfo->getMostPlingedProducts($pageLimit, ($page - 1) * $pageLimit)
                  );  
                  $this->view->json_data = $json_data;
                  break;
                  case 'toplistmembers':
                   $pageLimit = 100;
                   $page = (int)$this->getParam('page', 1);                            
                   $nopage = (int)$this->getParam('nopage', 0);                                             
                   $json_data = array(
                       'status'     => 'ok',     
                       'pageLimit'  => $pageLimit, 
                       'page'       => $page,
                       'nopage'     => $nopage,
                       'totalcount' => 1000,
                       'data'       => $modelInfo->getTopScoreUsers($pageLimit, ($page - 1) * $pageLimit)
                   );  
                   $this->view->json_data = $json_data;
                   break;
          default:
           
        } 

        $this->_helper->json($this->view->json_data);      
        
    }

    public function supportersAction()
    {
        $this->_helper->layout->disableLayout();
        $modelInfo = new Default_Model_Info();
        $this->view->supporters = $modelInfo->getSupporters(100);
    }
    public function newmembersAction()
    {
        $this->_helper->layout->disableLayout();
        $modelInfo = new Default_Model_Info();
        $this->view->users = $modelInfo->getNewActiveMembers(100);
    }
    public function topmembersAction()
    {
        $this->_helper->layout->disableLayout();
        $modelInfo = new Default_Model_Info();
        $this->view->users = $modelInfo->getTopScoreUsers(100);
    }
    public function plingedprojectsAction()
    {
        $this->_helper->layout->disableLayout();
        $modelInfo = new Default_Model_Info();
        $this->view->projects = $modelInfo->getNewActivePlingProduct(100);
    }
    public function mostplingedcreatorsAction()
    {
        $this->_helper->layout->disableLayout();
        $modelInfo = new Default_Model_Info();        
        $pageLimit = 100;
        $page = (int)$this->getParam('page', 1);                            
        $nopage = (int)$this->getParam('nopage', 0);                            
        $modelInfo = new Default_Model_Info(); 
        $this->view->page =$page;       
        $this->view->nopage =$nopage;       
        $this->view->pageLimit =$pageLimit;
        $this->view->totalcount = $modelInfo->getMostPlingedCreatorsTotalCnt();        
        $this->view->users = $modelInfo->getMostPlingedCreators($pageLimit, ($page - 1) * $pageLimit);

    }
    public function mostplingedproductsAction()
    {
        $this->_helper->layout->disableLayout();
        $pageLimit = 100;
        $page = (int)$this->getParam('page', 1);                            
        $nopage = (int)$this->getParam('nopage', 0);                            
        $modelInfo = new Default_Model_Info(); 
        $this->view->page =$page;       
        $this->view->nopage =$nopage;       
        $this->view->pageLimit =$pageLimit;
        $this->view->totalcount = $modelInfo->getMostPlingedProductsTotalCnt();        
        $this->view->projects = $modelInfo->getMostPlingedProducts($pageLimit, ($page - 1) * $pageLimit);
    }

    public function toplistmembersAction()
    {
        $this->_helper->layout->disableLayout();
        $modelInfo = new Default_Model_Info();        
        $pageLimit = 100;
        $page = (int)$this->getParam('page', 1);                            
        $nopage = (int)$this->getParam('nopage', 0);                            
        $modelInfo = new Default_Model_Info(); 
        $this->view->page =$page;       
        $this->view->nopage =$nopage;       
        $this->view->pageLimit =$pageLimit;
        $this->view->totalcount = 1000;      
        $this->view->users = $modelInfo->getTopScoreUsers($pageLimit, ($page - 1) * $pageLimit);
    }

}