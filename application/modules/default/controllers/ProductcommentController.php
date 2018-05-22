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

    public function addreplyAction()
    {
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);

        $data = array();
        $data['comment_target_id'] = (int)$this->getParam('p');
        $data['comment_parent_id'] = (int)$this->getParam('i');
        $data['comment_member_id'] = (int)$this->_authMember->member_id;
        $data['comment_text'] = Default_Model_HtmlPurify::purify($this->getParam('msg'));

        $tableReplies = new Default_Model_ProjectComments();
        $result = $tableReplies->save($data);
        $status = count($result->toArray()) > 0 ? 'ok' : 'error';
        $message = '';

        $this->view->comments = $this->loadComments((int)$this->getParam('page'), (int)$this->getParam('p'));
        $this->view->product = $this->loadProductInfo((int)$this->getParam('p'));
        $this->view->member_id = (int)$this->_authMember->member_id;

        $this->updateActivityLog($result, $this->view->product->image_small);

        //Send a notification to the owner
        $this->sendNotificationToOwner($this->view->product, $data['comment_text']);

        //Send a notification to the parent comment writer
        $this->sendNotificationToParent($this->view->product, $data['comment_text'], $data['comment_parent_id']);

        if ($this->_request->isXmlHttpRequest()) {
            $requestResult = $this->view->render('product/partials/productCommentsUX1.phtml');
            $this->_helper->json(array('status' => $status, 'message' => $message, 'data' => $requestResult));
        } else {
            $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
            $url = $helperBuildProductUrl->buildProductUrl($data['comment_target_id']);
            $this->redirect($url);
        }
    }

    private function loadComments($page_offset, $project_id)
    {
        $modelComments = new Default_Model_ProjectComments();
        $paginationComments = $modelComments->getCommentTreeForProject($project_id);
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
    private function sendNotificationToOwner($product, $comment)
    {
        //Don't send email notification for comments from product owner
        if ($this->_authMember->member_id == $product->member_id) {
            return;
        }

        $productData = new stdClass();
        $productData->mail = $product->mail;
        $productData->username = $product->username;
        $productData->title = $product->title;
        $productData->project_id = $product->project_id;

        $queue = Local_Queue_Factory::getQueue();
        $command = new Backend_Commands_SendCommentNotification('tpl_user_comment_note', $productData, $comment);
        $queue->send(serialize($command));
    }

    private function sendNotificationToParent($product, $comment, $parent_id)
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

        $productData = new stdClass();
        $productData->mail = $parentComment['mail'];
        $productData->username = $parentComment['username'];
        $productData->title = $product->title;
        $productData->project_id = $product->project_id;

        $queue = Local_Queue_Factory::getQueue();
        $command = new Backend_Commands_SendCommentNotification('tpl_user_comment_reply_note', $productData, $comment);
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
                // only vote then return
                $data = array();
                $data['comment_target_id'] = (int)$this->getParam('p');
                $data['comment_parent_id'] = (int)$this->getParam('i');
                $data['comment_member_id'] = (int)$this->_authMember->member_id;

                $data['comment_text'] = Default_Model_HtmlPurify::purify($this->getParam('msg'));

                $tableReplies = new Default_Model_ProjectComments();
                $result = $tableReplies->save($data);

                $voteup = (int)$this->getParam('v');
                $modelRating = new Default_Model_DbTable_ProjectRating();                
                $modelRating->rateForProject($project_id, $this->_authMember->member_id, $voteup, $result->comment_id);

                $status = count($result->toArray()) > 0 ? 'ok' : 'error';

                $this->view->product = $this->loadProductInfo((int)$this->getParam('p'));
                $this->view->member_id = (int)$this->_authMember->member_id;

                if($this->view->product){
                    $this->updateActivityLog($result, $this->view->product->image_small);

                    //Send a notification to the owner
                    $this->sendNotificationToOwner($this->view->product, $data['comment_text']);

                    //Send a notification to the parent comment writer
                    $this->sendNotificationToParent($this->view->product, $data['comment_text'], $data['comment_parent_id']);
                }
            } 
            // 14.05.18 not allowed anymore
            // else {
            //     $voteup = (int)$this->getParam('v');
            //     $modelRating = new Default_Model_DbTable_ProjectRating();
            //     $modelRating->rateForProject($project_id, $this->_authMember->member_id, $voteup);
            // }

            $this->_helper->json(array('status' => $status, 'message' => $message, 'data' => ''));
        } else {
            $this->_helper->json(array('status' => 'error', 'message' => 'Only registered members with an active supporting can vote!', 'data' => ''));
        }
        
        
    }

}