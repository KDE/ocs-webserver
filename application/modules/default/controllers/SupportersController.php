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

class SupportersController extends Local_Controller_Action_DomainSwitch
{


    public function indexAction()
    {                
        $section_id = $this->getParam('id',2);
        $products = self::fetchProducts($section_id);
        $creators = self::fetchCreators($section_id);
        $supporters = self::fetchSupporters($section_id);

        $model = new Default_Model_Section();
        $section = null;
        if($section_id)
        {
            $section = $model->fetchSection($section_id);
            $this->view->section = $section;
            $this->view->section_id = $section_id;
        }
      
        $this->view->supporters = $supporters;
        $this->view->products = $products;
        $this->view->creators = $creators;
       
        $title = 'Section';
        if($section){
            $title = 'Section '.$section['name'];
        }
        $this->view->headTitle($title . ' - ' . $this->getHeadTitle(), 'SET');
    }


    public function topAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);        
        $section_id = $this->getParam('id',null);
        $products = self::fetchProducts($section_id);
        $creators = self::fetchCreators($section_id);
        $this->_helper->json(array('status' => 'ok', 'products' => $products,'creators' => $creators));
    }

    public function fetchCreators($section_id)
    {
        $model = new Default_Model_Section();
        $helpPrintDate = new Default_View_Helper_PrintDate();
        $helperImage = new Default_View_Helper_Image();
       
        $creators = $model->fetchTopPlingedCreatorPerSection($section_id);

        foreach ($creators as &$p) {
          $p['profile_image_url'] = $helperImage->Image($p['profile_image_url'], array('width' => 100, 'height' => 100));

        }

        return $creators;

    }

    public function fetchProducts($section_id)
    {
        $model = new Default_Model_Section();
        $helpPrintDate = new Default_View_Helper_PrintDate();
        $helperImage = new Default_View_Helper_Image();
        
        $products=$model->fetchTopPlingedProductsPerSection($section_id);
        foreach ($products as &$p) {
          $p['image_small'] = $helperImage->Image($p['image_small'], array('width' => 200, 'height' => 200));
          $p['updated_at'] = $helpPrintDate->printDate(($p['changed_at']==null?$p['created_at']:$p['changed_at']));          
        }
        return $products;
    }

    public function fetchSupporters($section_id)
    {

        $info = new Default_Model_Info();
        $helperImage = new Default_View_Helper_Image();
        if($section_id)
        {
            $supporters = $info->getNewActiveSupportersForSection($section_id,1000);
        }else{
            $supporters = $info->getNewActiveSupportersForSectionAll(1000);
        }

        $s= array();
        foreach ($supporters as &$p) {
            
            $s[] = array('profile_image_url' => $helperImage->Image($p['profile_image_url'], array('width' => 100, 'height' => 100)),
                'member_id' => $p['member_id'],
                'username' => $p['username'],
                'section_support_tier' => $p['section_support_tier']
        );

          /*$p['profile_image_url'] = $helperImage->Image($p['profile_image_url'], array('width' => 100, 'height' => 100));*/
        }

        return $s;

    }

    public function topcatAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $model = new Default_Model_Section();
        $helpPrintDate = new Default_View_Helper_PrintDate();
        $helperImage = new Default_View_Helper_Image();
        $cat_id = $this->getParam('cat_id');
        $products=$model->fetchTopPlingedProductsPerCategory($cat_id);
                

        foreach ($products as &$p) {
          $p['image_small'] = $helperImage->Image($p['image_small'], array('width' => 200, 'height' => 200));
          $p['updated_at'] = $helpPrintDate->printDate(($p['changed_at']==null?$p['created_at']:$p['changed_at']));
        }

        $creators = $model->fetchTopPlingedCreatorPerCategory($cat_id);
        foreach ($creators as &$p) {
          $p['profile_image_url'] = $helperImage->Image($p['profile_image_url'], array('width' => 100, 'height' => 100));         

        }
        $this->_helper->json(array('status' => 'ok', 'cat_id'=>$cat_id,'products' => $products,'creators' => $creators));
    }

    public function recentplingsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $section_id = $this->getParam('id',null);
        $model = new Default_Model_Section();
        $helpPrintDate = new Default_View_Helper_PrintDate();
        $helperImage = new Default_View_Helper_Image();
        $products=$model->getNewActivePlingProduct($section_id);
        foreach ($products as &$p) {
          $p['image_small'] = $helperImage->Image($p['image_small'],array('width' => '115', 'height' => '75', 'crop' => 2));
          $p['created_at'] = $helpPrintDate->printDate($p['created_at']);
          $p['profile_image_url'] = $helperImage->Image($p['profile_image_url'], array('width' => 100, 'height' => 100));
        }
        $this->_helper->json(array('status' => 'ok', 'products' => $products));
    }


    protected function setLayout()
    {
        $this->_helper->layout()->setLayout('layout_pling_home');
    }

}
