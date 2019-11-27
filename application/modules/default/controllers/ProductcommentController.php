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
class ProductcommentController extends Local_Controller_Action_DomainSwitch
{

    /** @var  Zend_Auth */
    protected $_auth;

    public function init()
    {
        parent::init();

        $this->auth = Zend_Auth::getInstance();
    }

    public function indexAction()
    {
        $this->_helper->layout->disableLayout();
        $project_id = (int)$this->getParam('p');
        $cmodel = new Default_Model_ProjectComments();
        $list = $cmodel->getCommentTreeForProjectList($project_id);          
        $this->_helper->json($list);
    }
    
    

    public function addreplyAction()
    {
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);
        
        header('Access-Control-Allow-Origin: *'); 
        
        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', '*')
             ->setHeader('Access-Control-Allow-Credentials', 'true')
             ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept')
        ;

        $data = array();
        $data['comment_target_id'] = (int)$this->getParam('p');
        $data['comment_parent_id'] = (int)$this->getParam('i');
        $data['comment_member_id'] = (int)$this->_authMember->member_id;
        if($this->getParam('t'))
        {
            $data['comment_type'] =(int)$this->getParam('t');
        }
        
        $data['comment_text'] = Default_Model_HtmlPurify::purify($this->getParam('msg'));
        $tableReplies = new Default_Model_ProjectComments();
        $result = $tableReplies->save($data);
        $status = count($result->toArray()) > 0 ? 'ok' : 'error';
        $message = '';

        $this->view->comments = $this->loadComments((int)$this->getParam('page'), (int)$this->getParam('p'),$this->getParam('t'));
        $this->view->product = $this->loadProductInfo((int)$this->getParam('p'));
        $this->view->member_id = (int)$this->_authMember->member_id;

        $this->updateActivityLog($result, $this->view->product->image_small);

        //Send a notification to the owner        
        $this->sendNotificationToOwner($this->view->product, $data['comment_text'],$data['comment_type']);

        //Send a notification to the parent comment writer
        $this->sendNotificationToParent($this->view->product, $data['comment_text'], $data['comment_parent_id'],$data['comment_type']);

        if ($this->_request->isXmlHttpRequest()) {
            
            if($this->getParam('t') && (int)$this->getParam('t')==30)
            {
                $requestResult = $this->view->render('product/partials/productCommentsUX2.phtml');
            }else
            {
                $requestResult = $this->view->render('product/partials/productCommentsUX1.phtml');
            }            
            $this->_helper->json(array('status' => $status, 'message' => $message, 'data' => $requestResult));
        } else {
            $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
            $url = $helperBuildProductUrl->buildProductUrl($data['comment_target_id']);
            $this->redirect($url);
        }
    }

    private function loadComments($page_offset, $project_id,$comment_type)
    {
        $modelComments = new Default_Model_ProjectComments();
        $paginationComments = $modelComments->getCommentTreeForProject($project_id,$comment_type);
        $paginationComments->setItemCountPerPage(25);
        $paginationComments->setCurrentPageNumber($page_offset);

        return $paginationComments;
    }

    private function loadProductInfo($param)
    {
        $tableProject = new Default_Model_Project();

        return $tableProject->fetchProductInfo($param);
    }

    private function updateActivityLog($data, $image_small)
    {
        if ($data['comment_parent_id']) {
            $activity_type = Default_Model_ActivityLog::PROJECT_COMMENT_REPLY;
        } else {
            $activity_type = Default_Model_ActivityLog::PROJECT_COMMENT_CREATED;
        }

        Default_Model_ActivityLog::logActivity($data['comment_id'], $data['comment_target_id'],
            $data['comment_member_id'], $activity_type,
            array('title' => '', 'description' => $data['comment_text'], 'image_small' => $image_small));
    }

    /**
     * @param Zend_Db_Table_Row_Abstract $product
     * @param string                     $comment
     */
    private function sendNotificationToOwner($product, $comment,$comment_type)
    {
        //Don't send email notification for comments from product owner
        if ($this->_authMember->member_id == $product->member_id) {
            return;
        }

        $productData = new stdClass();
        $productData->mail = $product->mail;
        $productData->username = $product->username;
        $productData->username_sender = $this->_authMember->username;
        $productData->title = $product->title;
        $productData->project_id = $product->project_id;

        $queue = Local_Queue_Factory::getQueue();
        if(!empty($comment_type)&& $comment_type=='30')
        {   
            $command = new Backend_Commands_SendCommentNotification('tpl_user_comment_note_'.$comment_type, $productData, $comment);
        }else
        {
            $command = new Backend_Commands_SendCommentNotification('tpl_user_comment_note', $productData, $comment);
        }        
        $queue->send(serialize($command));
    }

    private function sendNotificationToParent($product, $comment, $parent_id,$comment_type)
    {
        if (0 == $parent_id) {
            return;
        }

        $tableReplies = new Default_Model_ProjectComments();
        $parentComment = $tableReplies->getCommentWithMember($parent_id);
        if (0 == count($parentComment)) {
            return;
        }

        if ($this->_authMember->member_id == $parentComment['member_id']) {
            return;
        }

        // email sent by sendNotificationToOwner already
        if ($product->member_id == $parentComment['member_id']) {
            return;
        }

        $productData = new stdClass();
        $productData->mail = $parentComment['mail'];
        $productData->username = $parentComment['username'];
        $productData->username_sender = $this->_authMember->username;
        $productData->title = $product->title;
        $productData->project_id = $product->project_id;

        $queue = Local_Queue_Factory::getQueue();
        if(!empty($comment_type)&& $comment_type=='30')
        {   
            $command = new Backend_Commands_SendCommentNotification('tpl_user_comment_reply_note_'.$comment_type, $productData, $comment);
        }else
        {
            $command = new Backend_Commands_SendCommentNotification('tpl_user_comment_reply_note', $productData, $comment);
        } 
       
        $queue->send(serialize($command));
    }

    public function addreplyreviewAction()
    {
        $this->_helper->layout->disableLayout();
        $msg = trim($this->getParam('msg'));
        $project_id = (int)$this->getParam('p');
        $comment_id = null;
        $status = 'ok';
        $message = '';
        
        
        
        //Only Supporter can make a review
        if(Zend_Auth::getInstance()->hasIdentity() ) {
            if ($msg != '' && strlen($msg)>0) {

                
                // $data = array();
                // $data['comment_target_id'] = (int)$this->getParam('p');
                // $data['comment_parent_id'] = (int)$this->getParam('i');
                // $data['comment_member_id'] = (int)$this->_authMember->member_id;
                // $data['comment_text'] = Default_Model_HtmlPurify::purify($this->getParam('msg'));
             
                $voteup = (int)$this->getParam('v');

                // negative voting msg length > 5 
                if($voteup ==2 && strlen($msg) < 5)
                {
                    $this->_helper->json(array('status' => 'error', 'message' => ' At least 5 chars. ', 'data' => ''));
                    return;
                }
                $modelRating = new Default_Model_DbTable_ProjectRating();                
                $modelRating->rateForProject($project_id, $this->_authMember->member_id, $voteup, $msg);

               
                $this->view->product = $this->loadProductInfo((int)$this->getParam('p'));
                $this->view->member_id = (int)$this->_authMember->member_id;

                if($this->view->product){                    
                    //Send a notification to the owner
                    $this->sendNotificationToOwner($this->view->product, Default_Model_HtmlPurify::purify($this->getParam('msg')));
                   
                }
            } 
           

            $this->_helper->json(array('status' => $status, 'message' => $message, 'data' => '','laplace_score' =>$this->view->product->laplace_score));
        } else {
            $this->_helper->json(array('status' => 'error', 'message' => 'Only registered members with an active supporting can vote!', 'data' => ''));
        }
        
        
    }

    public function addreplyreviewnewAction()
    {
        $this->_helper->layout->disableLayout();
        $msg = trim($this->getParam('msg'));
        $project_id = (int)$this->getParam('p');
        $comment_id = null;
        $status = 'ok';
        $message = '';
                
                
        if(Zend_Auth::getInstance()->hasIdentity() ) {
            if ($msg != '' && strlen($msg)>0) {                                            
                $score = (int)$this->getParam('s');
                // negative voting msg length > 5 
                if($score < 6 && strlen($msg) < 5)
                {
                    $this->_helper->json(array('status' => 'error', 'message' => ' At least 5 chars. ', 'data' => ''));
                    return;
                }

                $this->view->product = $this->loadProductInfo((int)$this->getParam('p'));
                $this->view->member_id = (int)$this->_authMember->member_id;

                if($this->view->product->member_id==$this->view->member_id)
                {
                    $this->_helper->json(array('status' => 'error', 'message' => ' Not allowed. ', 'data' => ''));
                    return;   
                }
                $modelRating = new Default_Model_DbTable_ProjectRating();                
                $modelRating->scoreForProject($project_id, $this->_authMember->member_id, $score, $msg);
               
                

                if($this->view->product){                    
                    //Send a notification to the owner
                    $this->sendNotificationToOwner($this->view->product, Default_Model_HtmlPurify::purify($this->getParam('msg')));                   
                }
            } 
           

            $this->_helper->json(array('status' => $status, 'message' => $message, 'data' => '','laplace_score' =>$this->view->product->laplace_score));
        } else {
            $this->_helper->json(array('status' => 'error', 'message' => 'Only registered members with an active supporting can vote!', 'data' => ''));
        }
        
        
    }

}