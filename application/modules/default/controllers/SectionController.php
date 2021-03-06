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

        $section_id = (int)$this->getParam('id',null);

        $products = self::fetchProducts($section_id, $isAdmin);
        $creators = self::fetchCreators($section_id, $isAdmin);
        $supporters = self::fetchSupporters($section_id);

        $model = new Default_Model_Section();
        $section = null;
        if($section_id)
        {
            $section = $model->fetchSection($section_id);
            $this->view->section = $section;
            $this->view->section_id = $section_id;
        }

        $amount = $model->fetchProbablyPayoutLastMonth($section_id);
        $amount_factor = $amount;
        $sectionStats = $model->fetchSectionStatsLastMonth($section_id);
        if($sectionStats['factor'] != null) {
            $amount_factor = $amount * $sectionStats['factor'];
        }

        $this->view->supporters = $supporters;
        $this->view->products = $products;
        $this->view->creators = $creators;
        if($isAdmin) {
            $this->view->probably_payout_amount = number_format($amount, 2, '.', '');
        }else{
            $this->view->probably_payout_amount = -1;
        }
        $this->view->probably_payout_amount_factor = number_format($amount_factor, 2, '.', '');
        $goal = ceil( $amount_factor / 500 ) * 500;
        $this->view->probably_payout_goal = ($goal ==0 ? 500: $goal);

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
        $isAdmin = false;
        if(Zend_Auth::getInstance()->hasIdentity() AND Zend_Auth::getInstance()->getIdentity()->roleName == 'admin') {
            $isAdmin = true;
        }
        $section_id = (int)$this->getParam('id',null);
        $products = self::fetchProducts($section_id, $isAdmin);
        $creators = self::fetchCreators($section_id, $isAdmin);
        $this->_helper->json(array('status' => 'ok', 'products' => $products,'creators' => $creators));
    }

    public function fetchCreators($section_id,$isAdmin)
    {
        $model = new Default_Model_Section();
        $helpPrintDate = new Default_View_Helper_PrintDate();
        $helperImage = new Default_View_Helper_Image();
        $sectionStats = $model->fetchSectionStatsLastMonth($section_id);

        $creators = $model->fetchTopCreatorPerSection($section_id);

        foreach ($creators as &$p) {
          $p['profile_image_url'] = $helperImage->Image($p['profile_image_url'], array('width' => 100, 'height' => 100));

          $p['probably_payout_amount_factor'] = number_format($p['probably_payout_amount']*($sectionStats['factor']?$sectionStats['factor']:1), 2, '.', '');

          $p['section_factor'] = $sectionStats['factor'];

          if($isAdmin) {
            $p['probably_payout_amount'] = number_format($p['probably_payout_amount'], 2, '.', '');
          }else{
            $p['probably_payout_amount'] = -1;
          }
        }

        return $creators;

    }

    public function fetchProducts($section_id, $isAdmin)
    {
        $model = new Default_Model_Section();
        $helpPrintDate = new Default_View_Helper_PrintDate();
        $helperImage = new Default_View_Helper_Image();
        $sectionStats = $model->fetchSectionStatsLastMonth($section_id);
        $products=$model->fetchTopProductsPerSection($section_id);
        foreach ($products as &$p) {
          $p['image_small'] = $helperImage->Image($p['image_small'], array('width' => 200, 'height' => 200));
          $p['updated_at'] = $helpPrintDate->printDate(($p['changed_at']==null?$p['created_at']:$p['changed_at']));

          $p['probably_payout_amount_factor'] = number_format($p['probably_payout_amount']*($sectionStats['factor']?$sectionStats['factor']:1), 2, '.', '');

          $p['section_factor'] = $sectionStats['factor'];

          if($isAdmin) {
            $p['probably_payout_amount'] =  number_format($p['probably_payout_amount'], 2, '.', '') ;
          }else{
            $p['probably_payout_amount'] = -1;
          }
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
        $products=$model->fetchTopProductsPerCategory($cat_id);
        $section = $model->fetchSectionForCategory($cat_id);
        $sectionStats = $model->fetchSectionStatsLastMonth($section['section_id']);
        $isAdmin = false;
        if(Zend_Auth::getInstance()->hasIdentity() AND Zend_Auth::getInstance()->getIdentity()->roleName == 'admin') {
            $isAdmin = true;
        }

        foreach ($products as &$p) {
          $p['image_small'] = $helperImage->Image($p['image_small'], array('width' => 200, 'height' => 200));
          $p['updated_at'] = $helpPrintDate->printDate(($p['changed_at']==null?$p['created_at']:$p['changed_at']));

            $p['probably_payout_amount_factor'] = number_format($p['probably_payout_amount']*($sectionStats['factor']?$sectionStats['factor']:1), 2, '.', '');

          $p['section_factor'] = $sectionStats['factor'];

          if($isAdmin) {
            $p['probably_payout_amount'] =  number_format($p['probably_payout_amount'], 2, '.', '') ;
          }else{
            $p['probably_payout_amount'] = -1;
          }
        }

        $creators = $model->fetchTopCreatorPerCategory($cat_id);
        foreach ($creators as &$p) {
          $p['profile_image_url'] = $helperImage->Image($p['profile_image_url'], array('width' => 100, 'height' => 100));
          $p['probably_payout_amount_factor'] = number_format($p['probably_payout_amount']*($sectionStats['factor']?$sectionStats['factor']:1), 2, '.', '');

          $p['section_factor'] = $sectionStats['factor'];

          if($isAdmin) {
            $p['probably_payout_amount'] = number_format($p['probably_payout_amount'], 2, '.', '');
          }else{
            $p['probably_payout_amount'] = -1;
          }

        }
        $this->_helper->json(array('status' => 'ok', 'cat_id'=>$cat_id,'products' => $products,'creators' => $creators));
    }


    protected function setLayout()
    {
        $this->_helper->layout()->setLayout('layout_pling_home');
    }

}
