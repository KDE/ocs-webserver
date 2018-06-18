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


$this->_helper->viewRenderer('index-appimagehub');

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

    protected function setLayout()
    {
        $this->_helper->layout()->setLayout('home_template');
    }

}
