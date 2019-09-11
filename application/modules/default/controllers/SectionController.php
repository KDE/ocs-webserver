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

class SectionController extends Local_Controller_Action_DomainSwitch
{


    public function indexAction()
    {
        $isAdmin = false;
        if(Zend_Auth::getInstance()->hasIdentity() AND Zend_Auth::getInstance()->getIdentity()->roleName == 'admin') {
            $isAdmin = true;
        }
        
        $section_id = $this->getParam('id',null);
        $model = new Default_Model_Section();
        $helpPrintDate = new Default_View_Helper_PrintDate();
        $helperImage = new Default_View_Helper_Image();
        $sectionStats = $model->fetchSectionStatsLastMonth($section_id);
        $products=$model->fetchTopProductsPerSection($section_id);
        foreach ($products as &$p) {
          $p['image_small'] = $helperImage->Image($p['image_small'], array('width' => 200, 'height' => 200));
          $p['updated_at'] = $helpPrintDate->printDate(($p['changed_at']==null?$p['created_at']:$p['changed_at']));
          if($isAdmin) {
            $p['probably_payout_amount'] = '($' . number_format($p['probably_payout_amount'], 2, '.', '') . ')';
          } else {
            $p['probably_payout_amount'] = '';  
          }
          if($sectionStats['factor'] != null) {
              $amount = (double)$p['probably_payout_amount'];
              $factor = (double)$sectionStats['factor'];
              $amount = $amount * $factor;
            //$p['probably_payout_amount_factor'] = number_format($amount, 2, '.', '');
            $p['probably_payout_amount_factor'] = '' . number_format($p['probably_payout_amount'], 2, '.', '');
            $p['section_factor'] = $factor;
          } else {
            $p['probably_payout_amount_factor'] = number_format($p['probably_payout_amount'], 2, '.', '');
            $p['section_factor'] = null;
          }
          
        }

        $creators = $model->fetchTopCreatorPerSection($section_id);
        $info = new Default_Model_Info();
        foreach ($creators as &$p) {
          $p['profile_image_url'] = $helperImage->Image($p['profile_image_url'], array('width' => 100, 'height' => 100));
          if($isAdmin) {
            $p['probably_payout_amount'] = '($'.number_format($p['probably_payout_amount'], 2, '.', '').')';
          } else {
            $p['probably_payout_amount'] = '';
          }
          
          if($sectionStats['factor'] != null) {
              $amount = (double)$p['probably_payout_amount'];
              $factor = (double)$sectionStats['factor'];
              $amount = $amount * $factor;
            //$p['probably_payout_amount_factor'] = number_format($amount, 2, '.', '');
            $p['probably_payout_amount_factor'] = ''. number_format($amount, 2, '.', '');
            $p['section_factor'] = $factor;
          } else {
            $p['probably_payout_amount_factor'] = number_format($p['probably_payout_amount'], 2, '.', '');
            $p['section_factor'] = null;
          }
          
        }

        $section = null;        
        if($section_id)
        {
            $section = $model->fetchSection($section_id);
            $this->view->section = $section;
            $this->view->section_id = $section_id; 
            $supporters = $info->getNewActiveSupportersForSection($section_id,1000);
        }else{
            $supporters = $info->getNewActiveSupportersForSectionAll(1000);
        }
        foreach ($supporters as &$p) {
          $p['profile_image_url'] = $helperImage->Image($p['profile_image_url'], array('width' => 100, 'height' => 100));                  
        }
        
        $amount = $model->fetchProbablyPayoutLastMonth($section_id);
        
        $amount_factor = $amount;
        if($sectionStats['factor'] != null) {
            $amount_factor = $amount * $sectionStats['factor'];
        }
        $this->view->supporters = $supporters;
        $this->view->products = $products;
        $this->view->creators = $creators;
        if($isAdmin) {
            $this->view->probably_payout_amount = '('.number_format($amount, 2, '.', '').')';
        } else {
            $this->view->probably_payout_amount = '';
        }
        $this->view->probably_payout_amount_factor = number_format($amount_factor, 2, '.', '');
//        $this->view->probably_payout_goal = round($amount+500,-2);
        $goal = ceil( $amount_factor / 500 ) * 500;

        $this->view->probably_payout_goal = ($goal ==0 ? 500: $goal);
        

        $title = 'Section';
        if($section){
            $title = 'Section '.$section['name'];
        }
        $this->view->headTitle($title . ' - ' . $this->getHeadTitle(), 'SET');
    }

    // deprecated...
    public function topAction()
    {        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $model = new Default_Model_Section();
        $helpPrintDate = new Default_View_Helper_PrintDate();
        $helperImage = new Default_View_Helper_Image();
        $section_id = $this->getParam('section_id');
        $products=$model->fetchTopProductsPerSection($section_id);
    
        foreach ($products as &$p) {
          
          $p['image_small'] = $helperImage->Image($p['image_small'], array('width' => 200, 'height' => 200));
          $p['updated_at'] = $helpPrintDate->printDate(($p['changed_at']==null?$p['created_at']:$p['changed_at']));
            $p['probably_payout_amount'] = number_format($p['probably_payout_amount'], 2, '.', '');
        }

        $creators = $model->fetchTopCreatorPerSection($section_id);

        $info = new Default_Model_Info();
        
        foreach ($creators as &$p) {
          $p['profile_image_url'] = $helperImage->Image($p['profile_image_url'], array('width' => 100, 'height' => 100));
          $p['probably_payout_amount'] = number_format($p['probably_payout_amount'], 2, '.', '');
        } 
        $this->_helper->json(array('status' => 'ok', 'products' => $products,'creators' => $creators));        
    }

    public function topcatAction()
    {        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $model = new Default_Model_Section();
        $helpPrintDate = new Default_View_Helper_PrintDate();
        $helperImage = new Default_View_Helper_Image();
        $cat_id = $this->getParam('cat_id');
        $products=$model->fetchTopProductsPerCategory($cat_id);
    
        foreach ($products as &$p) {
          $p['image_small'] = $helperImage->Image($p['image_small'], array('width' => 200, 'height' => 200));
          $p['updated_at'] = $helpPrintDate->printDate(($p['changed_at']==null?$p['created_at']:$p['changed_at']));
           $p['probably_payout_amount'] = number_format($p['probably_payout_amount'], 2, '.', '');
        }

        $creators = $model->fetchTopCreatorPerCategory($cat_id);
        foreach ($creators as &$p) {
          $p['profile_image_url'] = $helperImage->Image($p['profile_image_url'], array('width' => 100, 'height' => 100));
             $p['probably_payout_amount'] = number_format($p['probably_payout_amount'], 2, '.', '');
          
        }
        $this->_helper->json(array('status' => 'ok', 'cat_id'=>$cat_id,'products' => $products,'creators' => $creators));        
    }


    protected function setLayout()
    {
        $this->_helper->layout()->setLayout('layout_pling_home');
    }
    
}
