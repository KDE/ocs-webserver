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
        $data['comment_text'] = $this->getParam('msg');

        $tableReplies = new Default_Model_ProjectComments();
        $result = $tableReplies->save($data);
        $status = count($result) > 0 ? 'ok' : 'error';
        $message = '';
        
        

        $this->view->comments = $this->loadComments((int)$this->getParam('page'), (int)$this->getParam('p'));
        $this->view->product = $this->loadProductInfo((int)$this->getParam('p'));
        $this->view->member_id = (int)$this->_authMember->member_id;
        $requestResult = $this->view->render('product/partials/productCommentsUX1.phtml');

        $this->updateActivityLog($result, $this->view->product->image_small);

        //Send a notification to the owner
        //Don't send email notification on own comments
        if($this->_authMember->mail != $this->view->product->mail) {
            $this->sendNotificationToOwner($this->view->product, $data['comment_text']);
        }
        
        //Send a notification to the parent comment writer
        if((int)$this->getParam('i')!=0) {
            $parentCommentArray = (array) $tableReplies->getComment((int)$this->getParam('i'));
            if(count($parentCommentArray)>0) {
                $parentComment = $parentCommentArray[0];

                $parentCommentOwner = $this->loadMemberInfo($parentComment['comment_member_id']);
                if($parentCommentOwner && $parentCommentOwner->mail != $this->view->product->mail && $parentCommentOwner->member_id != $this->_authMember->member_id) {
                    $this->sendNotificationToParent($this->view->product, $parentCommentOwner, $data['comment_text']);
                }
            }
        }
        

        if ($this->_request->isXmlHttpRequest()) {
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
        $paginationComments = $modelComments->getAllCommentsForProject($project_id);
        $paginationComments->setItemCountPerPage(25);
        $paginationComments->setCurrentPageNumber($page_offset);
        return $paginationComments;
    }

    private function loadProductInfo($param)
    {
        $tableProject = new Default_Model_Project();
        return $tableProject->fetchProductInfo($param);
    }
    
    private function loadMemberInfo($memberId)
    {
        $memberTable = new Default_Model_Member();
        return $memberTable->fetchMemberData($memberId);
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

    private function sendNotificationToOwner($product, $comment)
    {
        $newPasMail = new Default_Plugin_SendMail('tpl_user_comment_note');
        $newPasMail->setReceiverMail($product->mail);
        $newPasMail->setReceiverAlias($product->username);

        $newPasMail->setTemplateVar('username', $product->username);
        $newPasMail->setTemplateVar('product_title', $product->title);
        $newPasMail->setTemplateVar('comment_text', $comment);

        $newPasMail->send();
    }
    
    private function sendNotificationToParent($product, $parentCommentOwner, $comment)
    {
        $newPasMail = new Default_Plugin_SendMail('tpl_user_comment_reply_note');
        $newPasMail->setReceiverMail($parentCommentOwner->mail);
        $newPasMail->setReceiverAlias($parentCommentOwner->username);

        $newPasMail->setTemplateVar('username', $parentCommentOwner->username);
        $newPasMail->setTemplateVar('product_title', $product->title);
        $newPasMail->setTemplateVar('comment_text', $comment);

        $newPasMail->send();
    }

}