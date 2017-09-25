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
class UserController extends Local_Controller_Action_DomainSwitch
{

    protected $_memberId;

    public function init()
    {
        parent::init();
        $this->_memberId = (int)$this->getParam('member_id');
    }

    public function indexAction()
    {
        $this->_helper->viewRenderer('aboutme');
        $this->aboutmeAction();
    }

    public function aboutAction()
    {
        $modelMember = new Default_Model_Member();
        $this->view->member = $modelMember->fetchMember($this->_memberId)->toArray();
        $this->view->currentPageOffset = (int)$this->getParam('page');
    }

    public function aboutmeAction()
    {
        $tableMember = new Default_Model_Member();
        $tableProject = new Default_Model_Project();

        $this->view->authMember = $this->_authMember;
        $this->view->member = $tableMember->find($this->_memberId)->current();
        if (null == $this->view->member) {
            $this->redirect("/");
        }
        if ($this->view->member->is_deleted == 1 or $this->view->member->is_active == 0) {
            $this->redirect("/");
        }
        $this->view->mainProject = $this->view->member->findDependentRowset($tableProject, 'MainProject')->current();
        $this->view->supportedProjects = $tableMember->fetchSupportedProjects($this->_memberId);
        //Categories
        $catArray = array();
        foreach ($this->view->supportedProjects as $pro) {
            $catArray[$pro->catTitle] = array();
        }

        $helperProductUrl = new Default_View_Helper_BuildProductUrl();
        foreach ($this->view->supportedProjects as $pro) {
            $projArr = array();
            $projArr['name'] = $pro->title;
            $projArr['image'] = $pro->image_small;
            $projArr['url'] = $helperProductUrl->buildProductUrl($pro->project_id);
            $projArr['sumAmount'] = $pro->sumAmount;
            array_push($catArray[$pro->catTitle], $projArr);
        }
        $this->view->supportingTeaser = $catArray;

        $this->view->followedProducts = $tableMember->fetchFollowedProjects($this->_memberId, null);

        $tableProject = new Default_Model_Project();
        $this->view->userProducts = $tableProject->fetchAllProjectsForMember($this->_memberId, null, null, true);

        $this->view->hits = $tableMember->fetchProjectsSupported($this->_memberId);

        $paginationComments = $tableMember->fetchComments($this->_memberId);

        if ($paginationComments) {
            $offset = (int)$this->getParam('page');
            $paginationComments->setItemCountPerPage(15);
            $paginationComments->setCurrentPageNumber($offset);
            $this->view->comments = $paginationComments;
        }


        $stat = array();
        $stat['cntProducts'] = count($this->view->userProducts);
        $stat['cntComments'] = $paginationComments->getTotalItemCount();
        $cntpv = 0;
        foreach ($this->view->userProducts as $pro) {
            $cntpv = $cntpv + $tableProject->fetchProjectViews($pro->project_id);
        }
        $stat['cntPageviews'] = $cntpv;

        $cntmb = $tableMember->fetchCntSupporters($this->_memberId);
        $stat['cntSupporters'] = $cntmb;
        $stat['userLastActiveTime'] = $tableMember->fetchLastActiveTime($this->_memberId);

        $this->view->stat = $stat;
    }

    public function followsAction()
    {
        $this->redirect($this->_helper->url('follows', 'member', null, $this->getAllParams()));
    }

    public function followAction()
    {
        $this->_helper->layout->disableLayout();

        $this->view->authMember = $this->_authMember;
        $this->view->member_id = $this->_memberId;

        if ($this->_memberId == $this->_authMember->member_id) {
            return;
        }

        $memberFollowTable = new Default_Model_DbTable_MemberFollower();

        $newVals = array('member_id' => $this->_memberId, 'follower_id' => (int)$this->_authMember->member_id);
        $where = $memberFollowTable->select()
            ->where('member_id = ?', $this->_memberId)
            ->where('follower_id = ?', $this->_authMember->member_id, 'INTEGER');
        $result = $memberFollowTable->fetchRow($where);
        if (null === $result) {
            $memberFollowTable->createRow($newVals)->save();
        }
    }

    public function unfollowAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('follow');

        $memberFollowTable = new Default_Model_DbTable_MemberFollower();

        $memberFollowTable->delete('member_id=' . $this->_memberId . ' AND follower_id=' . $this->_authMember->member_id);

        $this->view->authMember = $this->_authMember;
        $this->view->member_id = $this->_memberId;
    }

    public function newsAction()
    {
        $this->productsAction();
        $this->render('products');
    }

    public function productsAction()
    {
        $pageLimit = 25;
        $page = (int)$this->getParam('page', 1);

        $modelProject = new Default_Model_Project();
        $userProjects = $modelProject->fetchAllProjectsForMember($this->_authMember->member_id, $pageLimit,
            ($page - 1) * $pageLimit);

        $paginator = Local_Paginator::factory($userProjects);
        $paginator->setItemCountPerPage($pageLimit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setTotalItemCount($modelProject->countAllProjectsForMember($this->_authMember->member_id));

        $this->view->products = $paginator;
        $modelMember = new Default_Model_Member();
        $this->view->member = $modelMember->fetchMemberData($this->_authMember->member_id);
    }

    public function activitiesAction()
    {
        $modelInfo = new Default_Model_Info();
        $this->view->member = $this->_authMember;
        $this->view->comments = $modelInfo->getLastCommentsForUsersProjects($this->_authMember->member_id);
        $this->view->votes = $modelInfo->getLastVotesForUsersProjects($this->_authMember->member_id);
        $this->view->donations = $modelInfo->getLastDonationsForUsersProjects($this->_authMember->member_id);
    }

    public function settingsAction()
    {
        $this->_helper->layout()->setLayout('settings');
    }

    public function reportAction()
    {
        $this->_helper->layout->disableLayout();

        $this->_helper->viewRenderer('product/add');

        $this->forward('report', 'product', null, $this->getAllParams());
    }

    public function paymentsAction()
    {
        $this->view->headScript()->setFile('');
        $this->view->headLink()->setStylesheet('');

        $member_id = $this->_authMember->member_id;
        $this->view->member = $this->_authMember;

        $tableMember = new Default_Model_Member();
        $this->view->hits = $tableMember->fetchPlingedProjects($member_id);
    }

    public function incomeAction()
    {
        $this->view->member = $this->_authMember;
        $tableMember = new Default_Model_Member();
        $modelPlings = new Default_Model_Pling();
        $this->view->donations = $modelPlings->fetchRecentDonationsForUser($this->_authMember->member_id);
    }

    public function shareAction()
    {
        $this->_helper->layout->disableLayout();

        $modelProduct = new Default_Model_Member();
        $memberInfo = $modelProduct->fetchMemberData($this->_memberId);
        $form = new Default_Form_ProjectShare();
        $form->setAction('/member/' . $this->_memberId . '/share/');

//        $helperBaseUrl = new Default_View_Helper_BaseUrl();
//        $helperServerUrl = new Zend_View_Helper_ServerUrl();
        $helpMemberUrl = new Default_View_Helper_BuildMemberUrl();
        $this->view->permaLink = $helpMemberUrl->buildMemberUrl($this->_memberId);
//        $this->view->permaLink = $helperServerUrl->serverUrl() . $helperBaseUrl->baseUrl() . '/member/' . $this->_memberId . '/';
        if ($this->_request->isGet()) {
            $this->view->form = $form;
            $this->renderScript('product/share.phtml');
            return;
        }

        if (false === $form->isValid($_POST)) { // form not valid
            $this->view->form = $form;
            $dummy = $this->view->render('product/share.phtml');
            $this->_helper->json(array('status' => 'ok', 'message' => $dummy));
            return;
        }

        $values = $form->getValues();

        if (empty($memberInfo->firstname) and empty($memberInfo->lastname)) {
            $username = $memberInfo->username;
        } else {
            $username = $memberInfo->firstname . ' ' . $memberInfo->lastname;
        }

        $shareMail = new Default_Plugin_SendMail('tpl_social_mail_user');
        $shareMail->setTemplateVar('sender', $values['sender_mail']);
        $shareMail->setTemplateVar('username', $username);
        $shareMail->setTemplateVar('permalink', $this->view->permaLink);
        $shareMail->setTemplateVar('permalinktext', '<a href="' . $this->view->permaLink . '">View user\'s page</a>');
        $shareMail->setReceiverMail($values['mail']);
        $shareMail->send();

        $this->_helper->json(array('status' => 'ok', 'redirect' => $this->view->permaLink));
    }

    
    public function plingsAction()
    {

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);
        
        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;
        
        //backdore for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }
        
    }
    
    public function payoutAction()
    {

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);
        
        //backdore for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }
        
    }
}