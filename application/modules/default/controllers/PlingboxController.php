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
class PlingboxController extends Zend_Controller_Action
{


    public function indexAction()
    {   
            $member_id = $this->getParam('memberid');
         
            $membermodel = new Default_Model_DbTable_Member();
            $this->view->member =  $membermodel->find($member_id)->current();        

             if (empty($this->view->member )) {
                throw new Zend_Controller_Action_Exception('This page does not exist', 404);
            }

            $plingModel = new Default_Model_ProjectPlings();
            $this->view->supporters = $plingModel->fetchPlingsForSupporter($member_id);
            

            // $this->view->authCode = '';
            // if ($productRow->link_1) {
            //     $websiteOwner = new Local_Verification_WebsiteProject();
            //     $this->view->authCode = '<meta name="ocs-site-verification" content="' . $websiteOwner->generateAuthCode(stripslashes($productRow->link_1)) . '" />';
            // }            
    }

   

}