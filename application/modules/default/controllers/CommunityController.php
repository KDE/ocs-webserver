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
        $allDomainCatIds =
            Zend_Registry::isRegistered('store_category_list') ? Zend_Registry::get('store_category_list') : null;

        $modelCategories = new Default_Model_DbTable_ProjectCategory();
        if (isset($allDomainCatIds)) {
            $this->view->categories = $allDomainCatIds;
        } else {
            $this->view->categories = $modelCategories->fetchMainCatIdsOrdered();
        }
        
    }

    public function supportersAction()
    {
        $this->_helper->layout->disableLayout();
        $modelInfo = new Default_Model_Info();
        $this->view->supporters = $modelInfo->getNewActiveSupporters(100);
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

}