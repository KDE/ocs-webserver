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
class ReportController extends Zend_Controller_Action
{

    public function commentAction()
    {
        $this->_helper->layout()->disableLayout();
        if (APPLICATION_ENV != 'searchbotenv') {
            $comment_id = (int)$this->getParam('i');
            $project_id = (int)$this->getParam('p');
            $reported_by =
                Default_Model_Auth_User::getInstance()->hasIdentity() ? (int)Default_Model_Auth_User::getInstance()->getIdentity()->member_id
                    : 0;
            
            $clientIp = null;
            $clientIp2 = null;
            if(isset($_SERVER['REMOTE_ADDR'])) {
                $clientIp = $_SERVER['REMOTE_ADDR'];
            }
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $clientIp2 = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }

            $tableReportComments = new Default_Model_DbTable_ReportComments();
            
            $commentReportArray = $tableReportComments->fetchAll('comment_id = ' . $comment_id . ' AND user_ip = "' . $clientIp . '"');
            
            if(isset($commentReportArray) && count($commentReportArray) > 0) {
                $this->_helper->json(array(
                    'status'  => 'ok',
                    'message' => '<p>You have already submitted a report for this comment.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
                    'data'    => array()
                ));
            } else {
                $tableReportComments->save(array('project_id'  => $project_id,
                                                 'comment_id'  => $comment_id,
                                                 'reported_by' => $reported_by,
                                                 'user_ip' => $clientIp,
                                                 'user_ip2' => $clientIp2
                ));
                $this->_helper->json(array(
                    'status'  => 'ok',
                    'message' => '<p>Thank you for helping us to keep these sites SPAM-free.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
                    'data'    => array()
                ));
            }

        }
        
    }

    public function productAction()
    {
        $this->_helper->layout()->disableLayout();

        if (APPLICATION_ENV != 'searchbotenv') {

            $session = new Zend_Session_Namespace();
            $reportedProducts = isset($session->reportedProducts) ? $session->reportedProducts : array();
            $project_id = (int)$this->getParam('p');
            if (in_array($project_id, $reportedProducts)) {
                $this->_helper->json(array(
                    'status'  => 'ok',
                    'message' => '<p>Thank you, but you have already reported this product.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
                    'data'    => array()
                ));
            }
            $reported_by = 0;
            if (Default_Model_Auth_User::getInstance()->hasIdentity()) {
                $reported_by = (int)Default_Model_Auth_User::getInstance()->getIdentity()->member_id;
            }

            $modelProduct = new Default_Model_Project();
            $productData = $modelProduct->fetchRow(array('project_id = ?' => $project_id, 'status' => Default_Model_DbTable_Project::PROJECT_ACTIVE));


            if (empty($productData)) {
                $this->_helper->json(array(
                    'status'  => 'ok',
                    'message' => '<p>Thank you for helping us to keep these sites SPAM-free.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
                    'data'    => array()
                ));
            }

            if ($productData->spam_checked == 0) {
                $tableReportComments = new Default_Model_DbTable_ReportProducts();
                $tableReportComments->save(array('project_id' => $project_id, 'reported_by' => $reported_by));
            }
            $session->reportedProducts[] = $project_id;
        }

        $this->_helper->json(array(
            'status'  => 'ok',
            'message' => '<p>Thank you for helping us to keep these sites SPAM-free.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
            'data'    => array()
        ));
    }

    public function productfraudAction()
    {   
        $report_type = 1;

        $this->_helper->layout()->disableLayout();

        if (APPLICATION_ENV != 'searchbotenv') {

                    $session = new Zend_Session_Namespace();
                    $reportedFraudProducts = isset($session->reportedFraudProducts) ? $session->reportedFraudProducts : array();
                    $project_id = (int)$this->getParam('p');
                    $text = $this->getParam('t');
                    if (in_array($project_id, $reportedFraudProducts)) {
                        $this->_helper->json(array(
                            'status'  => 'ok',
                            'message' => '<p>Thank you, but you have already reported this product.</p><div class="modal-footer">
                                                    <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                                </div>',
                            'data'    => array()
                        ));
                    }
                    
                    if (Default_Model_Auth_User::getInstance()->hasIdentity()) {
                        $reported_by = (int)Default_Model_Auth_User::getInstance()->getIdentity()->member_id;
                        $reportProducts = new Default_Model_DbTable_ReportProducts();
                        $reportProducts->save(array('project_id' => $project_id, 'reported_by' => $reported_by,'text' => $text, 'report_type' =>$report_type));
                    }
                    
                    $session->reportedFraudProducts[] = $project_id;
        }

        $this->_helper->json(array(
            'status'  => 'ok',
            'message' => '<p>Thank you for reporting the misuse.</p><p>We will try to verify the reason for this case.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
            'data'    => array()
        ));
    }


    public function productcloneAction()
    {   
        $report_type = 1;

        $this->_helper->layout()->disableLayout();

        if (APPLICATION_ENV != 'searchbotenv') {

                    $project_id = (int)$this->getParam('p');
                    $text = $this->getParam('t');
                    $project_clone = $this->getParam('pc');
                  
                    if($project_clone)
                    {
                        $text = $text . ' '.$project_clone;
                    }
                    if(!is_numeric($project_clone))
                    {
                        $project_clone = 0;
                    }
                    if (Default_Model_Auth_User::getInstance()->hasIdentity()) {
                        $reported_by = (int)Default_Model_Auth_User::getInstance()->getIdentity()->member_id;
                        $reportProducts = new Default_Model_DbTable_ProjectClone();                 
                        $reportProducts->save(array('project_id' => $project_clone, 'member_id' => $reported_by,'text' => $text, 'project_id_parent' =>$project_id));                             
                    }                                                                                               
        }

        $this->_helper->json(array(
            'status'  => 'ok',
            'message' => '<p>Thank you. The credits have been submitted.</p><p>It can take some time to appear while we verify it.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
            'data'    => array()
        ));
    }

  /*  public function memberAction()
    {
    }*/

}