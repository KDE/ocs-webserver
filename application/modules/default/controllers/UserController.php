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
    protected $_userName;
    /** @var  Zend_Db_Table_Row */
    protected $_memberSettings;

    public function init()
    {
        parent::init();

        if ($this->hasParam('user_name')) {
            $this->_userName = $this->getParam('user_name');
            $this->_userName = urldecode($this->_userName);

            $modelMember = new Default_Model_Member();
            $this->_memberId = $modelMember->fetchActiveUserByUsername($this->_userName);
        } else {
            $this->_memberId = (int)$this->getParam('member_id');
        }

        $action = $this->getRequest()->getActionName();
        $title = '';
        if ($action == 'index') {
            $title = 'aboutme';
        } else {
            $title = $action;
        }
        $this->view->headTitle($title . ' - ' . $this->getHeadTitle(), 'SET');
    }

    public function indexAction()
    {

        $this->_helper->viewRenderer('aboutme');
        $this->aboutmeAction();
    }

    public function aboutmeAction()
    {

        $tableMember = new Default_Model_Member();
        $tableProject = new Default_Model_Project();
        $earnModel = new Default_Model_StatDownload();
        $helperUserRole = new Backend_View_Helper_UserRole();
        $pageLimit = 500;
        $projectpage = (int)$this->getParam('projectpage', 1);

        $this->view->authMember = $this->_authMember;
        $this->view->member = $tableMember->fetchMemberData($this->_memberId);


        if (null == $this->view->member) {
            $this->redirect("/");
        }
        if ($this->view->member->is_deleted == 1 or $this->view->member->is_active == 0) {
            $this->redirect("/");
        }

        $this->view->headTitle($this->view->member->username . ' - ' . $this->getHeadTitle(), 'SET');
        $this->view->mainProject = $this->view->member->findDependentRowset($tableProject, 'MainProject')->current();

        $this->view->userProjectCategories = $tableProject->getUserCreatingCategorys($this->_memberId);
        $this->view->aboutmeUserInfo = $this->getAboutmeUserInfo($this->_memberId, $this->view->member->username);


        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $amount = $earnModel->getLastMonthEarn($this->_memberId);
            if ($amount && $amount['amount']) {
                $this->view->earnInfo = ' Last month I earned $' . number_format($amount['amount'], 2, '.', '') . '.';
            } else {
                $this->view->earnInfo = ' Last month I earned 0.';
            }
        } else {
            $this->view->earnInfo = '';
        }


        // ajax load more products
        if ($this->getParam('projectpage', null)) {
            $total_records = $tableProject->countAllProjectsForMemberCatFilter($this->_memberId, true, null);
            $this->view->pageLimit = $pageLimit;
            $this->view->projectpage = $projectpage;
            $this->view->total_records = $total_records;

            // get last project category id
            $lastproject = $tableProject->getUserActiveProjects($this->_memberId, 1,
                (($projectpage - 1) * $pageLimit - 1));
            foreach ($lastproject as $value) {
                $this->view->lastcatid = $value['project_category_id'];
            }

            $this->view->userProducts =
                $tableProject->getUserActiveProjects($this->_memberId, $pageLimit, ($projectpage - 1) * $pageLimit);

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer('partials/aboutmeProducts');

            //$this->forward('showmoreproductsajax', 'user', null, $this->getAllParams());
            return;
        } else {

            $total_records = $tableProject->countAllProjectsForMemberCatFilter($this->_memberId, true, null);
            $this->view->pageLimit = $pageLimit;
            $this->view->projectpage = $projectpage;
            $this->view->total_records = $total_records;
            //$this->view->userProducts = $tableProject->fetchAllProjectsForMember($this->_memberId, $pageLimit, ($projectpage - 1) * $pageLimit,true);
            $this->view->userProducts =
                $tableProject->getUserActiveProjects($this->_memberId, $pageLimit, ($projectpage - 1) * $pageLimit);

            $this->view->userFeaturedProducts = $tableProject->fetchAllFeaturedProjectsForMember($this->_memberId);
            $this->view->userCollections = $tableProject->fetchAllCollectionsForMember($this->_memberId);

            $paginationComments = $tableMember->fetchComments($this->_memberId);
            if ($paginationComments) {
                $offset = (int)$this->getParam('page');
                $paginationComments->setItemCountPerPage(50);
                $paginationComments->setCurrentPageNumber($offset);
                $this->view->comments = $paginationComments;
            }

            // favs    Currently no paging...
            $this->view->paramLikePageId = (int)$this->getParam('likepage');
            $model = new Default_Model_DbTable_ProjectFollower();
            $offset = $this->view->paramLikePageId;
            $list = $model->fetchLikesForMember($this->_memberId);
            $list->setItemCountPerPage(1000);
            $list->setCurrentPageNumber($offset);
            $this->view->likes = $list;

            // plings   Currently no paging...
            $plingmodel = new Default_Model_ProjectPlings();
            $offset = null;
            $plist = $plingmodel->fetchPlingsForMember($this->_memberId);
            $plist->setItemCountPerPage(1000);
            $plist->setCurrentPageNumber($offset);
            $this->view->plings = $plist;

            // plings   Currently no paging...
            $plingmodel = new Default_Model_ProjectPlings();
            $offset = null;
            $pslist = $plingmodel->fetchPlingsForSupporter($this->_memberId);
            $pslist->setItemCountPerPage(1000);
            $pslist->setCurrentPageNumber($offset);
            $this->view->supportersplings = $pslist;

            // rated
            $ratemodel = new Default_Model_DbTable_ProjectRating();
            $this->view->rated = $ratemodel->getRatedForMember($this->_memberId);

            $stat = array();
            $stat['cntProducts'] = $total_records;
            if ($this->view->userFeaturedProducts) {
                $cnt = 0;
                foreach ($this->view->userFeaturedProducts as $tmp) {
                    $cnt++;
                }
                $stat['cntFProducts'] = $cnt;
            } else {
                $stat['cntFProducts'] = 0;
            }

            if ($this->view->userCollections) {
                $cnt = 0;
                foreach ($this->view->userCollections as $tmp) {
                    $cnt++;
                }
                $stat['cntCollections'] = $cnt;
            } else {
                $stat['cntCollections'] = 0;
            }

            $stat['cntComments'] = $paginationComments->getTotalItemCount();
            $tblFollower = new Default_Model_DbTable_ProjectFollower();
            $stat['cntLikesHeGave'] = $tblFollower->countLikesHeGave($this->_memberId);
            $stat['cntLikesHeGot'] = $tblFollower->countLikesHeGot($this->_memberId);

            $tblPling = new Default_Model_DbTable_ProjectPlings();
            $stat['cntPlingsHeGave'] = $tblPling->countPlingsHeGave($this->_memberId);
            $stat['cntPlingsHeGot'] = $tblPling->countPlingsHeGot($this->_memberId);

            $donationinfo = $tableMember->fetchSupporterDonationInfo($this->_memberId);            

            if ($donationinfo) {
                $stat['donationIssupporter'] = $donationinfo['issupporter'];
                $stat['donationMax'] = $donationinfo['active_time_max'];
                $stat['donationMin'] = $donationinfo['active_time_min'];
                $stat['donationCnt'] = $donationinfo['cnt'];
            }

            $subscriptioninfo = $tableMember->fetchSupporterSubscriptionInfo($this->_memberId);
            if ($subscriptioninfo) {
                $stat['subscriptionIssupporter'] = true;
                $stat['subscriptionStart'] = $subscriptioninfo['create_time'];
                $stat['subscriptionAmount'] = $subscriptioninfo['amount'];
                $stat['subscriptionPeriod'] = $subscriptioninfo['period'];
                if ($subscriptioninfo['period'] == 'M') {
                    $stat['subscriptionPeriodText'] = 'monthly';
                } else {
                    if ($subscriptioninfo['period'] == 'Y') {
                        $stat['subscriptionPeriodText'] = 'yearly';
                    } else {
                        $stat['subscriptionPeriodText'] = '';
                    }
                }


                $stat['subscriptionPeriodFreq'] = $subscriptioninfo['period_frequency'];
            } else {
                $stat['subscriptionIssupporter'] = false;
            }
            //  $cntmb = $tableMember->fetchCntSupporters($this->_memberId);
            // $stat['cntSupporters'] = $cntmb;
            $stat['userLastActiveTime'] = $tableMember->fetchLastActiveTime($this->_memberId);

            $stat['cntDuplicateSourceurl'] = 0;
            $userRoleName = $helperUserRole->userRole();
            if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
                $stat['cntDuplicateSourceurl'] = $tableProject->getCountProjectsDuplicateSourceurl($this->_memberId);
            }

            $this->view->stat = $stat;
        }
    }

    public function getAboutmeUserInfo($member_id, $username)
    {
        $tableProject = new Default_Model_Project();
        $userProjectCategories = $tableProject->getUserCreatingCategorys($member_id);
        $cnt = sizeof($userProjectCategories);
        $userinfo = '';
        $isAdmin = false;
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $isAdmin = true;
        }
        if ($cnt > 0) {
            $userinfo = "Hi, I am <b>" . $username . "</b> and I create ";
            if ($cnt == 1) {
                $userinfo = $userinfo . ' <b>' . $userProjectCategories[0]['category1'] . '</b>';
                $userinfo = $userinfo . '.';
                /* if($isAdmin)
                 {
                     $userinfo = $userinfo.' ('.$userProjectCategories[0]['cnt'].').';
                 }else{
                     $userinfo = $userinfo.'.';
                 }*/
            } else {
                if ($cnt == 2) {
                    $userinfo = $userinfo . ' <b>' . $userProjectCategories[0]['category1'] . '</b>';
                    /*if($isAdmin)
                    {
                    $userinfo = $userinfo.' ('.$userProjectCategories[0]['cnt'].')';
                    }*/
                    $userinfo = $userinfo . ' and <b>' . $userProjectCategories[1]['category1'] . '</b>';
                    /*if($isAdmin)
                    {
                        $userinfo = $userinfo.'('.$userProjectCategories[1]['cnt'].').';
                    }else{
                        $userinfo = $userinfo.'.';
                    }*/
                    $userinfo = $userinfo . '.';
                } else {
                    if ($cnt == 3) {
                        $userinfo = $userinfo . ' <b>' . $userProjectCategories[0]['category1'] . '</b>';
                        /*if($isAdmin)
                        {
                            $userinfo = $userinfo.' ('.$userProjectCategories[0]['cnt'].')';
                        }*/
                        $userinfo = $userinfo . ',<b> ' . $userProjectCategories[1]['category1'] . '</b>';
                        /* if($isAdmin)
                         {
                             $userinfo = $userinfo.' ('.$userProjectCategories[1]['cnt'].')';
                         }*/
                        $userinfo = $userinfo . ' and <b>' . $userProjectCategories[2]['category1'] . '</b>';
                        /*if($isAdmin)
                        {
                            $userinfo = $userinfo.' ('.$userProjectCategories[2]['cnt'].').';
                        }*/
                        /*else{
                            $userinfo = $userinfo.'.';
                        }*/
                        $userinfo = $userinfo . '.';
                    } else {
                        if ($cnt > 3) {
                            $userinfo = $userinfo . ' <b>' . $userProjectCategories[0]['category1'] . '</b>';
                            /*if($isAdmin)
                            {
                            $userinfo = $userinfo.' ('.$userProjectCategories[0]['cnt'].')';
                            }*/
                            $userinfo = $userinfo . ', <b>' . $userProjectCategories[1]['category1'] . '</b>';
                            /*if($isAdmin)
                            {
                            $userinfo = $userinfo.' ('.$userProjectCategories[1]['cnt'].')';
                            }*/
                            $userinfo = $userinfo . ', <b>' . $userProjectCategories[2]['category1'] . '</b>';
                            /*if($isAdmin)
                            {
                            $userinfo = $userinfo.' ('.$userProjectCategories[2]['cnt'].')';
                            }*/
                            $userinfo = $userinfo . ' and more.';
                        }
                    }
                }
            }
        }else{
            $userinfo = "Hi, I am <b>" . $username . "</b>.";
        }

        $mModel = new Default_Model_Member();
        $supportSections = $mModel->fetchSupporterSectionInfo($member_id);
        if($supportSections && $supportSections['sections'])
        {
            if ($cnt == 0) {
                $userinfo = $userinfo." I support ".$supportSections['sections'].".";
            }else
            {
                $userinfo = $userinfo." I also support ".$supportSections['sections'].".";
            }
        }
        
         if(substr($userinfo, strlen($userinfo)-1) <> ".")
         {
            $userinfo.=".";          
         }
        return $userinfo;
    }

    public function duplicatesAction()
    {
        $tableProject = new Default_Model_Project();
        $pageLimit = 1000;
        $projectpage = 1;
        $total_records = $tableProject->countAllProjectsForMemberCatFilter($this->_memberId, true, null);
        $this->view->pageLimit = $pageLimit;
        $this->view->projectpage = $projectpage;
        $this->view->total_records = $total_records;

        $this->view->userProducts =
            $tableProject->getUserActiveProjectsDuplicatedSourceurl($this->_memberId, $pageLimit,
                ($projectpage - 1) * $pageLimit);

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/aboutmeProducts');

    }

    /**
     * to get an avatar picture you can call
     * /member/avatar/:emailhash/:size
     * or
     * /member/u/:user_name/avatar/size/:size
     *
     * @throws Zend_Exception
     */
    public function avatarAction()
    {
        $this->_helper->layout->disableLayout();

        $size = (int)$this->getParam("size", 200);
        $width = (int)$this->getParam("width", ($size / 2));
        $emailHash = $this->getParam("emailhash", null);
        $username = $this->getParam('user_name', null);

        $avatar = new Default_Model_Avatar();
        $img_url = $avatar->getAvatarUrl($emailHash, $username, $width);

        $this->redirect($img_url);
    }

    public function aboutAction()
    {
        $modelMember = new Default_Model_Member();
        $this->view->member = $modelMember->fetchMember($this->_memberId)->toArray();
        $this->view->currentPageOffset = (int)$this->getParam('page');
    }

    public function showmoreproductsajaxAction()
    {
        $this->_helper->layout->disableLayout();
        $tableProject = new Default_Model_Project();
        $pageLimit = 21;
        $page = (int)$this->getParam('page', 1);
        $total_records = $tableProject->countAllProjectsForMemberCatFilter($this->_memberId, true, null);
        $this->view->pageLimit = $pageLimit;
        $this->view->page = $page;
        $this->view->total_records = $total_records;
        $this->view->userProducts =
            $tableProject->fetchAllProjectsForMember($this->_memberId, $pageLimit, ($page - 1) * $pageLimit, true);
        $this->_helper->viewRenderer('/partials/aboutmeProducts');
    }

    public function userdataajaxAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $resultArray = array();

        header('Access-Control-Allow-Origin: *');

        $this->getResponse()->setHeader('Access-Control-Allow-Origin',
            '*')->setHeader('Access-Control-Allow-Credentials', 'true')
             ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept');

        $userid = $this->getParam('id');

        $modelMember = new Default_Model_Member();
        $user = $modelMember->find($userid)->current();

        if (Zend_Auth::getInstance()->hasIdentity()) {

            $auth = Zend_Auth::getInstance();
            $user = $auth->getStorage()->read();

            $resultArray['member_id'] = $user->member_id;
            $resultArray['username'] = $user->username;
            $resultArray['mail'] = $user->mail;
            $resultArray['avatar'] = $user->profile_image_url;
        } else {
            if (null != $userid && null != $user) {

                $resultArray['member_id'] = $user['member_id'];
                $resultArray['username'] = $user['username'];
                $resultArray['mail'] = $user['mail'];
                $resultArray['avatar'] = $user['profile_image_url'];
            } else {
                $resultArray['member_id'] = null;
                $resultArray['username'] = null;
                $resultArray['mail'] = null;
                $resultArray['avatar'] = null;
            }
        }

        $resultAll = array();
        $resultAll['status'] = "success";
        $resultAll['data'] = $resultArray;

        $this->_helper->json($resultAll);
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
        $where = $memberFollowTable->select()->where('member_id = ?', $this->_memberId)
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

        //create ppload download hash: secret + collection_id + expire-timestamp
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $timestamp = time() + 3600; // one hour valid
        $hash = md5($salt . $timestamp); // order isn't important at all... just do the same when verifying

        $this->view->download_hash = $hash;
        $this->view->download_timestamp = $timestamp;

        $this->view->member_id = null;
        if (null != $this->_authMember && null != $this->_authMember->member_id) {
            $this->view->member_id = $this->_authMember->member_id;
        }

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

    public function collectionsAction()
    {
        $pageLimit = 25;
        $page = (int)$this->getParam('page', 1);

        $this->view->member_id = null;
        if (null != $this->_authMember && null != $this->_authMember->member_id) {
            $this->view->member_id = $this->_authMember->member_id;
        }

        $modelProject = new Default_Model_Collection();
        $userProjects = $modelProject->fetchAllCollectionsForMember($this->_authMember->member_id, $pageLimit,
            ($page - 1) * $pageLimit);

        $paginator = Local_Paginator::factory($userProjects);
        $paginator->setItemCountPerPage($pageLimit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setTotalItemCount($modelProject->countAllCollectionsForMember($this->_authMember->member_id));

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

    public function tooltipAction()
    {
        $this->_helper->layout->disableLayout();
        $info = new Default_Model_Info();
        $data = $info->getTooptipForMember($this->_memberId);
        $this->_helper->json(array('status' => 'ok', 'data' => $data));
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
        $this->view->permaLink = $helpMemberUrl->buildMemberUrl($memberInfo->username);
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

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }
    }
    
    
    public function plingsoldAction()
    {
        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }
    }


    public function plingsajaxAction()
    {
        $this->_helper->layout->disableLayout();

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }

        $year = null;
        if ($this->hasParam('year')) {
            $year = $this->getParam('year');
        }
        $this->view->year = $year;

        $this->_helper->viewRenderer('/plingsajax');
    }

    public function plingsmonthajaxAction()
    {
        $this->_helper->layout->disableLayout();

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }

        $yearmonth = null;
        if ($this->hasParam('yearmonth')) {
            $yearmonth = $this->getParam('yearmonth');
        }
        $section_id = null;
        if ($this->hasParam('section_id')) {
            $section_id = $this->getParam('section_id');
        }
        
        $this->view->yearmonth = $yearmonth;
        $this->view->section_id = $section_id;

        $this->_helper->viewRenderer('/plingsmonthajax');
    }
    
    
    public function plingsajax3Action()
    {
        $this->_helper->layout->disableLayout();

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }

        $year = null;
        if ($this->hasParam('year')) {
            $year = $this->getParam('year');
        }
        $this->view->year = $year;

        $this->_helper->viewRenderer('/plingsajax3');
    }

    public function plingsmonthajax3Action()
    {
        $this->_helper->layout->disableLayout();

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }

        $yearmonth = null;
        if ($this->hasParam('yearmonth')) {
            $yearmonth = $this->getParam('yearmonth');
        }
        $section_id = null;
        if ($this->hasParam('section_id')) {
            $section_id = $this->getParam('section_id');
        }
        
        $this->view->yearmonth = $yearmonth;
        $this->view->section_id = $section_id;

        $this->_helper->viewRenderer('/plingsmonthajax3');
    }
    
    
    public function sectionplingsmonthajaxAction()
    {
        $this->_helper->layout->disableLayout();

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }

        $yearmonth = null;
        if ($this->hasParam('yearmonth')) {
            $yearmonth = $this->getParam('yearmonth');
        }
        $section_id = null;
        if ($this->hasParam('section_id')) {
            $section_id = $this->getParam('section_id');
        }
        
        $this->view->yearmonth = $yearmonth;
        $this->view->section_id = $section_id;

        $this->_helper->viewRenderer('/sectionplingsmonthajax');
    }
    
    
    public function sectionaffiliatesmonthajaxAction()
    {
        $this->_helper->layout->disableLayout();

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }

        $yearmonth = null;
        if ($this->hasParam('yearmonth')) {
            $yearmonth = $this->getParam('yearmonth');
        }
        $section_id = null;
        if ($this->hasParam('section_id')) {
            $section_id = $this->getParam('section_id');
        }
        
        $this->view->yearmonth = $yearmonth;
        $this->view->section_id = $section_id;

        $this->_helper->viewRenderer('/sectionaffiliatesmonthajax');
    }
    
    
    public function sectionaffiliatesmonthdetailajaxAction()
    {
        $this->_helper->layout->disableLayout();

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }

        $yearmonth = null;
        if ($this->hasParam('yearmonth')) {
            $yearmonth = $this->getParam('yearmonth');
        }
        $section_id = null;
        if ($this->hasParam('section_id')) {
            $section_id = $this->getParam('section_id');
        }
        
        $this->view->yearmonth = $yearmonth;
        $this->view->section_id = $section_id;

        $this->_helper->viewRenderer('/sectionaffiliatesmonthdetailajax');
    }
    
    
    public function sectionsajaxAction()
    {
        $this->_helper->layout->disableLayout();

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }

        $year = null;
        if ($this->hasParam('year')) {
            $year = $this->getParam('year');
        }
        $this->view->year = $year;

        $this->_helper->viewRenderer('/sectionsajax');
    }
    
    
    public function affiliatesajaxAction()
    {
        $this->_helper->layout->disableLayout();

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }

        $year = null;
        if ($this->hasParam('year')) {
            $year = $this->getParam('year');
        }
        $this->view->year = $year;

        $this->_helper->viewRenderer('/affiliatesajax');
    }

    public function sectionsmonthajaxAction()
    {
        $this->_helper->layout->disableLayout();

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }

        $yearmonth = null;
        if ($this->hasParam('yearmonth')) {
            $yearmonth = $this->getParam('yearmonth');
        }
        $this->view->yearmonth = $yearmonth;

        $this->_helper->viewRenderer('/sectionsmonthajax');
    }

    public function downloadhistoryAction()
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

        if ($this->view->member) {
            $this->view->paramPageId = (int)$this->getParam('page');

            //TODO do really sql paging instead of Zend_Paginator
            $dhistory = new Default_Model_DbTable_MemberDownloadHistory();
            $offset = $this->view->paramPageId;
            $list = $dhistory->getDownloadhistory($this->view->member->member_id);
            $list->setItemCountPerPage(250);
            $list->setCurrentPageNumber($offset);
            $this->view->downloadhistory = $list;
        } else {
            $this->view->downloadhistory = array();
        }
    }

    public function likesAction()
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

        if ($this->view->member) {
            $this->view->paramPageId = (int)$this->getParam('page');
            $model = new Default_Model_DbTable_ProjectFollower();
            $offset = $this->view->paramPageId;
            $list = $model->fetchLikesForMember($this->view->member->member_id);
            $list->setItemCountPerPage(250);
            $list->setCurrentPageNumber($offset);
            $this->view->likes = $list;
        } else {
            $this->view->likes = array();
        }
    }

    public function supportAction()
    {

        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $tableMember = new Default_Model_Member();
            $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }

        $model = new Default_Model_DbTable_Support();
        $this->view->supporterlist = $model->getSupporterDonationList($this->view->member->member_id);


    }
    
    public function fundingAction()
    {

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);
        
        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }

        $model = new Default_Model_DbTable_Support();
        $this->view->supporterlist = $model->getSupporterDonationList($this->view->member->member_id);


    }

    public function payoutoldAction()
    {

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
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

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }
    }
    
    
    public function affiliatesAction()
    {

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }
    }
    
    public function payout3Action()
    {

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->view->view_member->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }
    }

    public function payouthistoryoldAction()
    {

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
            if ($this->_memberId != $this->_authMember->member_id) {
                throw new Zend_Controller_Action_Exception('no authorization found');
            }
        }

        $model = new Default_Model_StatDownload();
        $resultSet = $model->getPayoutHistory($this->view->member->member_id);

        $this->view->payouthistory = $resultSet;


    }

    public function payouthistoryAction()
    {

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
            if ($this->_memberId != $this->_authMember->member_id) {
                throw new Zend_Controller_Action_Exception('no authorization found');
            }
        }

        $model = new Default_Model_StatDownload();
        $resultSet = $model->getPayoutHistory2($this->view->member->member_id);

        $this->view->payouthistory2 = $resultSet;


    }


    public function _payouthistoryAction()
    {

        $tableMember = new Default_Model_Member();
        $this->view->view_member = $tableMember->fetchMemberData($this->_memberId);

        //backdoor for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
            if ($this->_memberId != $this->_authMember->member_id) {
                throw new Zend_Controller_Action_Exception('no authorization found');
            }
        }

        // these are already payed
        $sql = "SELECT `yearmonth`, `amount` FROM `member_payout` WHERE `member_id` = :member_id ORDER BY `yearmonth` ASC";
        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql,
            array('member_id' => $this->view->member->member_id));


        // there are probably payed last 2 months
        // current month
        $date = new DateTime();
        $ym = $date->format('Ym');
        $is_in = false;
        foreach ($resultSet as $value) {
            if ($ym == $value['yearmonth']) {
                $is_in = true;
                break;
            }
        }

        if (!$is_in) {
            $model = new Default_Model_StatDownload();
            $result = $model->getUserDownloadsForMonth($this->view->member->member_id, $ym);
            $amount = 0;
            foreach ($result as $value) {
                if ($value['is_license_missing_now'] == 1
                    || $value['is_source_missing_now'] == 1
                    || $value['is_pling_excluded_now'] == 1
                ) {
                    continue;
                }
                $amount = $amount + $value['probably_payout_amount'];
            }
            $currentMonth = array('yearmonth' => $ym, 'amount' => $amount);

            // test last month too
            $interval = new DateInterval('P1M');//2 months
            $lastmonthdate = $date->sub($interval);
            $ym = $lastmonthdate->format('Ym');
            $is_in = false;
            foreach ($resultSet as $value) {
                if ($ym == $value['yearmonth']) {
                    $is_in = true;
                    break;
                }
            }
            if (!$is_in) {
                $model = new Default_Model_StatDownload();
                $result = $model->getUserDownloadsForMonth($this->view->member->member_id, $ym);
                $amount = 0;
                foreach ($result as $value) {
                    if ($value['is_license_missing'] == 1
                        || $value['is_source_missing'] == 1
                        || $value['is_pling_excluded'] == 1
                    ) {
                        continue;
                    }
                    $amount = $amount + $value['probably_payout_amount'];
                }
                $lastMonth = array('yearmonth' => $ym, 'amount' => $amount);
                array_push($resultSet, $lastMonth);
            }
            array_push($resultSet, $currentMonth);
        }

        $this->view->payouthistory = $resultSet;


    }


    /**
     * @return Default_Form_Settings
     * @throws Zend_Form_Exception
     */
    private function formPassword()
    {
        $form = new Default_Form_Settings();
        $form->setMethod("POST")->setAttrib("id",
            "settingsPasswordForm")->setAction('/member/' . $this->_memberId . '/changepass');

        $passOld = $form->createElement('password', 'passwordOld')->setLabel('Enter old Password:')->setRequired(true)
                        ->removeDecorator('HtmlTag')->addValidator(new Local_Validate_OldPasswordConfirm())->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors',
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                        'placement'  => false
                    )
                )
            ));

        $pass1 = $form->createElement('password', 'password1')->setLabel('Enter new Password:')->setRequired(true)
                      ->addValidator(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::STRING))->removeDecorator('HtmlTag')
                      ->setDecorators(array(
                          'ViewHelper',
                          'Label',
                          'Errors',
                          array(
                              'ViewScript',
                              array(
                                  'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                                  'placement'  => false
                              )
                          )
                      ));

        $pass2 = $form->createElement('password', 'password2')->setLabel('Re-enter new Password:')->setRequired(true)
                      ->addValidator(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::STRING))->removeDecorator('HtmlTag')
                      ->setDecorators(array(
                          'ViewHelper',
                          'Label',
                          'Errors',
                          array(
                              'ViewScript',
                              array(
                                  'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                                  'placement'  => false
                              )
                          )
                      ));

        $passValid = new Local_Validate_PasswordConfirm($pass2->getValue());
        $pass1->addValidator($passValid);

        $form->addElement($passOld)->addElement($pass1)->addElement($pass2);

        return $form;
    }

    private function fetchMemberId()
    {
        if (false === Zend_Auth::getInstance()->hasIdentity()) {
            return null;
        }

        $auth = Zend_Auth::getInstance()->getIdentity();

        if ($this->_userName == $auth->username) {
            return $auth->member_id;
        }

        if (Default_Model_DbTable_Member::ROLE_ID_ADMIN == $auth->roleId) {
            return $this->_memberId;
        }
    }

}