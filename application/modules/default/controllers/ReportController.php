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
        if ((APPLICATION_ENV != 'searchbotenv') AND (false == SEARCHBOT_DETECTED)) {
            $comment_id = (int)$this->getParam('i');
            $project_id = (int)$this->getParam('p');
            $reported_by =
                Zend_Auth::getInstance()->hasIdentity() ? (int)Zend_Auth::getInstance()->getStorage()->read()->member_id
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

        if ((APPLICATION_ENV != 'searchbotenv') AND (false == SEARCHBOT_DETECTED)) {

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
            if (Zend_Auth::getInstance()->hasIdentity()) {
                $reported_by = (int)Zend_Auth::getInstance()->getStorage()->read()->member_id;
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

        if ((APPLICATION_ENV != 'searchbotenv') AND (false == SEARCHBOT_DETECTED)) {

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
                    
                    if (Zend_Auth::getInstance()->hasIdentity()) {
                        $reported_by = (int)Zend_Auth::getInstance()->getStorage()->read()->member_id;
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

    public function flagmodAction()
    {                           
        $this->_helper->layout()->disableLayout();
        $params = $this->getAllParams();
        if ((APPLICATION_ENV != 'searchbotenv') AND (false == SEARCHBOT_DETECTED)) {

                    $project_clone = $this->getParam('p');
                    $text = $this->getParam('t');                    
                    $url = $this->getParam('l'); 
                    $project_id = 0;
                    
                    if (Zend_Auth::getInstance()->hasIdentity()) {
                        $reported_by = (int)Zend_Auth::getInstance()->getStorage()->read()->member_id;
                        $reportProducts = new Default_Model_DbTable_ProjectClone();                 
                        $reportProducts->save(array('project_id' => $project_clone
                        ,'member_id' => $reported_by
                        ,'text' => $text
                        ,'external_link' => $url
                        ,'project_clone_type' =>1
                        ,'project_id_parent' =>$project_id));                             
                    }                                                                                               
        }

        $this->_helper->json(array(
            'status'  => 'ok',
            'message' => '<p>Thank you. The credits have been submitted.</p><p>It can take some time to appear while we verify it.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
            'data'    => $params
        ));
    }

    public function productcloneAction()
    {                   
        $this->_helper->layout()->disableLayout();
        $params = $this->getAllParams();
        if ((APPLICATION_ENV != 'searchbotenv') AND (false == SEARCHBOT_DETECTED)) {

                    $project_clone = $this->getParam('p');
                    $text = $this->getParam('t');
                    $project_id = $this->getParam('pc');
                  
                    if($project_id)
                    {
                        $text = $text . ' '.$project_id;
                    }
                    if(!is_numeric($project_id))
                    {
                        $project_id = 0;
                    }
                    if (Zend_Auth::getInstance()->hasIdentity()) {
                        $reported_by = (int)Zend_Auth::getInstance()->getStorage()->read()->member_id;
                        $reportProducts = new Default_Model_DbTable_ProjectClone();                 
                        $reportProducts->save(array('project_id' => $project_clone, 'member_id' => $reported_by,'text' => $text, 'project_id_parent' =>$project_id));                             
                    }                                                                                               
        }

        $this->_helper->json(array(
            'status'  => 'ok',
            'message' => '<p>Thank you. The credits have been submitted.</p><p>It can take some time to appear while we verify it.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
            'data'    => $params
        ));
    }

  /*  public function memberAction()
    {
    }*/

}