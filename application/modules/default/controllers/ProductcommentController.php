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
        $this->_helper->viewRenderer->setNoRender(true);
        
        $data = array();
        $data['comment_target_id'] = (int)$this->getParam('p');
        $data['comment_parent_id'] = (int)$this->getParam('i');
        $data['comment_member_id'] = (int)$this->_authMember->member_id;
        $data['comment_text'] = $this->getParam('msg');

        $tableReplies = new Default_Model_ProjectComments();

        $result = $tableReplies->save($data);
        $status = count($result) > 0 ? 'ok' : 'error';
        $message = '';

        $this->updateActivityLog($result);

        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->json(array('status' => $status, 'message' => $message, 'data' => $data));
        } else {
            $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
            $url = $helperBuildProductUrl->buildProductUrl($data['comment_target_id']);
            $this->redirect($url);
        }
    }

    private function updateActivityLog($data)
    {
        if ($data['comment_parent_id']) {
            $activity_type = Default_Model_ActivityLog::PROJECT_COMMENT_REPLY;
        } else {
            $activity_type = Default_Model_ActivityLog::PROJECT_COMMENT_CREATED;
        }

        Default_Model_ActivityLog::logActivity($data['comment_id'], $data['comment_target_id'],$data['comment_member_id'], $activity_type, array('title'=>'','description' => $data['comment_text']));
    }

} 