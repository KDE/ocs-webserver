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

        $pageLimit = 500;
        $projectpage = (int)$this->getParam('projectpage', 1);

        $this->view->authMember = $this->_authMember;
        $this->view->member = $tableMember->find($this->_memberId)->current();
        if (null == $this->view->member) {
            $this->redirect("/");
        }
        if ($this->view->member->is_deleted == 1 or $this->view->member->is_active == 0) {
            $this->redirect("/");
        }

        $this->view->mainProject = $this->view->member->findDependentRowset($tableProject, 'MainProject')->current();
        // TODOs

        // $this->view->supportedProjects = $tableMember->fetchSupportedProjects($this->_memberId);

        //Categories
        /*
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
        $this->view->hits = $tableMember->fetchProjectsSupported($this->_memberId);
        */
        // ajax load more products
        if ($this->getParam('projectpage', null)) {
            $total_records = $tableProject->countAllProjectsForMemberCatFilter($this->_memberId, true, null);
            $this->view->pageLimit = $pageLimit;
            $this->view->projectpage = $projectpage;
            $this->view->total_records = $total_records;
            //$this->view->userProducts = $tableProject->fetchAllProjectsForMember($this->_memberId, $pageLimit, ($projectpage - 1) * $pageLimit,true);
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

            $this->view->userFeaturedProducts =
                    $tableProject->fetchAllFeaturedProjectsForMember($this->_memberId);


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
            if($this->view->userFeaturedProducts)
            {                
                $cnt = 0;
                foreach ($this->view->userFeaturedProducts as $tmp) {
                    $cnt++;
                }
                $stat['cntFProducts'] = $cnt;
            }else
            {
                $stat['cntFProducts'] = 0;
            }

            $stat['cntComments'] = $paginationComments->getTotalItemCount();

            // $cntpv = 0;
            // foreach ($this->view->userProducts as $pro) {
            //     $cntpv = $cntpv + $tableProject->fetchProjectViews($pro->project_id);
            // }
            // $stat['cntPageviews'] = $cntpv;

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
            //  $cntmb = $tableMember->fetchCntSupporters($this->_memberId);
            // $stat['cntSupporters'] = $cntmb;
            $stat['userLastActiveTime'] = $tableMember->fetchLastActiveTime($this->_memberId);
            $this->view->stat = $stat;
        }
    }

    public function avatarAction()
    {
        $this->_helper->layout->disableLayout();

        $size = 200;
        if (null != ($this->getParam("size"))) {
            $size = $this->getParam("size");
        }
        $this->view->size = (int)$size / 2;

        $emailHash = null;
        if (null != ($this->getParam("emailhash"))) {
            $emailHash = $this->getParam("emailhash");
        }
        $memberTable = new Default_Model_Member();
        if ($emailHash) {
            $member = $memberTable->findMemberForMailHash($emailHash);

            if ($member) {
                $helperImage = new Default_View_Helper_Image();
                $imgUrl = $helperImage->Image($member['profile_image_url'], array('width' => $size, 'height' => $size));
                $this->view->avatar = $imgUrl;
            } else {
                $this->view->avatar = "";
            }
        }
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
        
        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', '*')
             ->setHeader('Access-Control-Allow-Credentials', 'true')
             ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
             ->setHeader('Access-Control-Allow-Headers', 'origin, content-type, accept')
        ;
        
        
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
            
            
        } else if (null != $userid && null != $user) {
        
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
                                   ->where('follower_id = ?', $this->_authMember->member_id, 'INTEGER')
        ;
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
        //20181009 ronald: change hash from MD5 to SHA512
        //$hash = md5($salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying
        $hash = hash('sha512',$salt . $timestamp); // order isn't important at all... just do the same when verifying

        $this->view->download_hash = $hash;
        $this->view->download_timestamp = $timestamp;

        $this->view->member_id = null;
        if (null != $this->_authMember && null != $this->_authMember->member_id) {
            $this->view->member_id = $this->_authMember->member_id;
        }

        $modelProject = new Default_Model_Project();
        $userProjects = $modelProject->fetchAllProjectsForMember($this->_authMember->member_id, $pageLimit, ($page - 1) * $pageLimit);

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

        //backdore for admins
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $this->view->member = $this->view->view_member;
        } else {
            $this->view->member = $this->_authMember;
        }
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

    /**
     * @return Default_Form_Settings
     * @throws Zend_Form_Exception
     */
    private function formPassword()
    {
        $form = new Default_Form_Settings();
        $form->setMethod("POST")->setAttrib("id", "settingsPasswordForm")->setAction('/member/' . $this->_memberId . '/changepass');

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
            ))
        ;

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
                      ))
        ;

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
                      ))
        ;

        $passValid = new Local_Validate_PasswordConfirm($pass2->getValue());
        $pass1->addValidator($passValid);

        $form->addElement($passOld)->addElement($pass1)->addElement($pass2);

        return $form;
    }

}