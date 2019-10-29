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
class ProductController extends Local_Controller_Action_DomainSwitch
{

    const IMAGE_SMALL_UPLOAD = 'image_small_upload';
    const IMAGE_BIG_UPLOAD = 'image_big_upload';
    /**
     * Zend_Controller_Request_Abstract object wrapping the request environment
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request = null;
    /** @var  int */
    protected $_projectId;
    /** @var  int */
    protected $_collectionId;
    /** @var  Zend_Auth */
    protected $_auth;
    /** @var  string */
    protected $_browserTitlePrepend;

    public function init()
    {
        parent::init();
        $this->_projectId = (int)$this->getParam('project_id');
        $this->_collectionId = (int)$this->getParam('collection_id');
        $this->_auth = Zend_Auth::getInstance();
        $this->_browserTitlePrepend = $this->templateConfigData['head']['browser_title_prepend'];

        $action = $this->getRequest()->getActionName();
         $title = $action;
        if($action =='add')
        {
          $title = 'add product';
        }else
        {
          $title = $action;
        }
        $this->view->headTitle($title . ' - ' . $this->getHeadTitle(), 'SET');
    }

    public function ratingAction()
    {
        $this->_helper->layout()->disableLayout();
        if (array_key_exists($this->_projectId, $this->_authMember->projects)) {
            return;
        }
        $userRating = (int)$this->getParam('rate', 0);
        $modelRating = new Default_Model_DbTable_ProjectRating();
        $modelRating->rateForProject($this->_projectId, $this->_authMember->member_id, $userRating);
    }


    public function pploadAction()
    {
        $this->_helper->layout->disableLayout();
        $modelProduct = new Default_Model_Project();
        $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
        //create ppload download hash: secret + collection_id + expire-timestamp
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $collectionID = $productInfo->ppload_collection_id;
        $timestamp = time() + 3600; // one hour valid
        //20181009 ronald: change hash from MD5 to SHA512
        //$hash = md5($salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying
        $hash = hash('sha512',$salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying

        $this->view->download_hash = $hash;
        $this->view->download_timestamp = $timestamp;

        $this->view->product = $productInfo;
        $this->_helper->viewRenderer('/partials/pploadajax');
    }
    
    public function gettaggroupsforcatajaxAction() {
        $this->_helper->layout()->disableLayout();
        
        $catId = null;
        $fileId = null;
        
        if($this->hasParam('file_id')) {
            $fileId = $this->getParam('file_id');
        }
        
        if($this->hasParam('project_cat_id')) {
            $catId = $this->getParam('project_cat_id');
            $catTagModel  = new Default_Model_Tags();
            $catTagGropuModel  = new Default_Model_TagGroup();
            $tagGroups = $catTagGropuModel->fetchTagGroupsForCategory($catId);
            
            $tableTags = new Default_Model_DbTable_Tags();
            
            $result = array();
            $resultGroup = array();
            
            foreach ($tagGroups as $group) {
                $tags = $tableTags->fetchForGroupForSelect($group['tag_group_id']); 
                $selectedTags = null;
                if(!empty($fileId)) {
                    $selectedTags = $catTagModel->getTagsArray($fileId, Default_Model_DbTable_Tags::TAG_TYPE_FILE,$group['tag_group_id']);
                }
                
                $group['tag_list'] = $tags;
                $group['selected_tags'] = $selectedTags;
                $result[] = $group;
            }
            
            $this->_helper->json(array('status' => 'ok', 'ResultSize' => count($tagGroups), 'tag_groups' => $result));

            return;
        }

        $this->_helper->json(array('status' => 'error'));
    }
    
    private function getTagGroupsForCat($fileId) {
        $modelProduct = new Default_Model_Project();
        $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
        $catId = $productInfo->project_category_id;
        
        if(!empty($catId)) {
            $catTagModel  = new Default_Model_Tags();
            $catTagGropuModel  = new Default_Model_TagGroup();
            $tagGroups = $catTagGropuModel->fetchTagGroupsForCategory($catId);
            
            $tableTags = new Default_Model_DbTable_Tags();
            
            $result = array();
            
            foreach ($tagGroups as $group) {
                $tags = $tableTags->fetchForGroupForSelect($group['tag_group_id']); 
                $selectedTags = null;
                if(!empty($fileId)) {
                    $selectedTags = $catTagModel->getTagsArray($fileId, Default_Model_DbTable_Tags::TAG_TYPE_FILE,$group['tag_group_id']);
                }
                
                $group['tag_list'] = $tags;
                $group['selected_tags'] = $selectedTags;
                $result[] = $group;
            }
            
            return $result;
        }
        
        return null;
    }
    
    
    private function getFileDownloadCount($collection_id, $fileId) {
        $modelFiles = new Default_Model_DbTable_PploadFiles();
        
        $countAll = $modelFiles->fetchCountDownloadsForFileAllTime($collection_id, $fileId);
        $countToday = $modelFiles->fetchCountDownloadsForFileToday($collection_id, $fileId);
        
        $count = (int)$countAll+ (int)$countToday;
        return $count;
    }
    
    public function listsamesourceurlAction()
    {        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);                
        $modelProduct = new Default_Model_Project();     
        $productInfo = $modelProduct->fetchProductInfo($this->_projectId);   
        $result = $modelProduct->getSourceUrlProjects($productInfo->source_url);
        $r = '<div class="container containerduplicates">';
        foreach ($result as $value) {
            $r=$r.'<div class="row"><div class="col-lg-2"><a href="/p/'.$value['project_id'].'">'.$value['project_id'].'</a></div>'
                .'<div class="col-lg-4">'.$value['title'].'</div>'
                .'<div class="col-lg-2"><a href="/u/'.$value['username'].'">'.$value['username'].'</a></div>'
                .'<div class="col-lg-2">'.$value['created_at'].'</div>'
                .'<div class="col-lg-2">'.$value['changed_at'].'</div>'
                .'</div>';
        }

        $r = $r.'</div>';
            
  
        /*$response='<ul class="list-group" style="min-height:300px;min-width:1000px; max-height:500px; overflow:auto">';
        foreach ($result as $value) {
            $response=$response.'<li class="list-group-item"><a href="/p/'.$value['project_id'].'">'.$value['project_id'].'</a></li>';
        }
        $response=$response.'</ul>';*/
        echo $r;

    }  
       
 
    
    public function getfilesajaxAction() {
        $this->_helper->layout()->disableLayout();
        
        $collection_id = null;
        $file_status = null;
        $ignore_status_code = null;
        
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        
        if($this->hasParam('status')) {
            $file_status = $this->getParam('status');
        }
        if($this->hasParam('ignore_status_code')) {
            $ignore_status_code = $this->getParam('ignore_status_code');
        }
        
        $filesTable = new Default_Model_DbTable_PploadFiles();
        
        if($this->hasParam('collection_id')) {
            $collection_id = $this->getParam('collection_id');
            $result = array();
            $isForAdmin = false;
            if ($userRoleName == Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN) {
                $isForAdmin = true;
            }
            
            //Load files from DB
            if($ignore_status_code == 0 && $file_status == 'active') {
                $files = $filesTable->fetchAllActiveFilesForProject($collection_id, $isForAdmin);
            } else {
                $files = $filesTable->fetchAllFilesForProject($collection_id, $isForAdmin);
            }
            
            //Check, if the project category has tag-grous
            $modelProduct = new Default_Model_Project();
            $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
            $catTagGropuModel  = new Default_Model_TagGroup();
            $tagGroups = $catTagGropuModel->fetchTagGroupsForCategory($productInfo->project_category_id);

            foreach ($files as $file) {
                //add tag grous, if needed
                if(!empty($tagGroups)) {
                    $groups = $this->getTagGroupsForCat($file['id']);
                    $file['tag_groups'] = $groups;
                }
                
                //Download Counter
                

                //new counter IP based
                $counterUkAll = $file['count_dl_all_uk'];
                $counterNoUkAll = $file['count_dl_all_nouk'];
                $counterUkToday = $file['count_dl_uk_today'];
                $counterNew = 0;
                if(!empty($counterUkAll)) {
                    $counterNew = $counterNew + $counterUkAll;
                }
                if(!empty($counterUkToday)) {
                    $counterNew = $counterNew + $counterUkToday;
                }
                if(!empty($counterNoUkAll)) {
                    $counterNew = $counterNew + $counterNoUkAll;
                }
                $file['downloaded_count_uk'] = $counterNew;


                if ($userRoleName == Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN) {
                    //$file['downloaded_count_live'] = $this->getFileDownloadCount($collection_id, $file['id']);
                    $counterToday = $file['count_dl_today'];
                    $counterAll = $file['count_dl_all'];
                    $counter = 0;
                    if(!empty($counterToday)) {
                        $counter = $counterToday;
                    }
                    if(!empty($counterAll)) {
                        $counter = $counter + $counterAll;
                    }
                    $file['downloaded_count_live'] = $counter;
                } else {
                    unset($file['count_dl_all']);
                    unset($file['count_dl_all_nouk']);
                    unset($file['count_dl_all_uk']);
                    unset($file['count_dl_uk_today']);
                    unset($file['count_dl_today']);
                    unset($file['downloaded_count']);
                }
                $result[] = $file;
            }
            
            $this->_helper->json(array('status' => 'success', 'ResultSize' => count($result), 'files' => $result));

            return;
        }

        $this->_helper->json(array('status' => 'error'));
    }
    
    
    public function getfiletagsajaxAction() {
        $this->_helper->layout()->disableLayout();
        
        $fileId = null;
        
        if($this->hasParam('file_id')) {
            $fileId = $this->getParam('file_id');
            
            $tagModel  = new Default_Model_Tags();
            $fileTags = $tagModel->getFileTags($fileId);
            
            $this->_helper->json(array('status' => 'ok', 'ResultSize' => count($fileTags), 'file_tags' => $fileTags));

            return;
        }

        $this->_helper->json(array('status' => 'error'));
    }

    

    public function initJsonForReact(){
            $modelProduct = new Default_Model_Project();
            $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
            $this->view->product = $productInfo;
            if (empty($this->view->product)) {
                throw new Zend_Controller_Action_Exception('This page does not exist', 404);
            }

             if(null != $this->_authMember) {
                $this->view->authMemberJson = Zend_Json::encode( Default_Model_Member::cleanAuthMemberForJson($this->_authMember) );
            }

            $helpAddDefaultScheme = new Default_View_Helper_AddDefaultScheme();
            $this->view->product->title = Default_Model_HtmlPurify::purify($this->view->product->title);
            $this->view->product->description = Default_Model_BBCode::renderHtml(Default_Model_HtmlPurify::purify($this->view->product->description));
            $this->view->product->version = Default_Model_HtmlPurify::purify($this->view->product->version);
            $this->view->product->link_1 = Default_Model_HtmlPurify::purify($helpAddDefaultScheme->addDefaultScheme($this->view->product->link_1),Default_Model_HtmlPurify::ALLOW_URL);
            $this->view->product->source_url = Default_Model_HtmlPurify::purify($this->view->product->source_url,Default_Model_HtmlPurify::ALLOW_URL);
            $this->view->product->facebook_code = Default_Model_HtmlPurify::purify($this->view->product->facebook_code,Default_Model_HtmlPurify::ALLOW_URL);
            $this->view->product->twitter_code = Default_Model_HtmlPurify::purify($this->view->product->twitter_code,Default_Model_HtmlPurify::ALLOW_URL);
            $this->view->product->google_code = Default_Model_HtmlPurify::purify($this->view->product->google_code,Default_Model_HtmlPurify::ALLOW_URL);
            $this->view->productJson = Zend_Json::encode(Default_Model_Collection::cleanProductInfoForJson($this->view->product) );

            $fmodel =new  Default_Model_DbTable_PploadFiles();
            $files = $fmodel->fetchFilesForProject($this->view->product->ppload_collection_id);

            $salt = PPLOAD_DOWNLOAD_SECRET;
            
            $filesList = array();
            
            foreach ($files as $file) {
                $timestamp = time() + 3600; // one hour valid
                $hash = hash('sha512',$salt . $file['collection_id'] . $timestamp); // order isn't important at all... just do the same when verifying
                $url = PPLOAD_API_URI . 'files/download/id/' . $file['id'] . '/s/' . $hash . '/t/' . $timestamp;
                if(null != $this->_authMember) {
                    $url .= '/u/' . $this->_authMember->member_id;
                }
                $url .= '/lt/filepreview/' . $file['name'];
                $file['url'] = urlencode($url);
                $filesList[] = $file;
            }
            
            $this->view->filesJson = Zend_Json::encode($filesList);
            $this->view->filesCntJson = Zend_Json::encode($fmodel->fetchFilesCntForProject($this->view->product->ppload_collection_id));

            $tableProjectUpdates = new Default_Model_ProjectUpdates();
            $this->view->updatesJson =  Zend_Json::encode($tableProjectUpdates->fetchProjectUpdates($this->_projectId));
            $tableProjectRatings = new Default_Model_DbTable_ProjectRating();
            $ratings = $tableProjectRatings->fetchRating($this->_projectId);
            $cntRatingsActive = 0;
             foreach ($ratings as $p) {
                if($p['rating_active']==1) $cntRatingsActive =$cntRatingsActive+1;
             }
             $this->view->ratingsJson = Zend_Json::encode($ratings);
             $this->view->cntRatingsActiveJson = Zend_Json::encode($cntRatingsActive);

            $identity = Zend_Auth::getInstance()->getStorage()->read();
            if (Zend_Auth::getInstance()->hasIdentity()){
                $ratingOfUserJson = $tableProjectRatings->getProjectRateForUser($this->_projectId,$identity->member_id);
                $this->view->ratingOfUserJson =  Zend_Json::encode($ratingOfUserJson);
            }else{
                $this->view->ratingOfUserJson =  Zend_Json::encode(null);
            }
            $tableProjectFollower = new Default_Model_DbTable_ProjectFollower();
            $likes = $tableProjectFollower->fetchLikesForProject($this->_projectId);
            $this->view->likeJson = Zend_Json::encode($likes);

            $projectplings = new Default_Model_ProjectPlings();
            $plings = $projectplings->fetchPlingsForProject($this->_projectId);
            $this->view->projectplingsJson = Zend_Json::encode($plings);

            $tableProject = new Default_Model_Project();
            $galleryPictures = $tableProject->getGalleryPictureSources($this->_projectId);
            $this->view->galleryPicturesJson = Zend_Json::encode($galleryPictures);

            $tagmodel = new Default_Model_Tags();
            $tagsuser = $tagmodel->getTagsUser($this->_projectId, Default_Model_Tags::TAG_TYPE_PROJECT);
            $tagssystem = $tagmodel->getTagsSystemList($this->_projectId);
            $this->view->tagsuserJson = Zend_Json::encode($tagsuser);
            $this->view->tagssystemJson = Zend_Json::encode($tagssystem);

            $modelComments = new Default_Model_ProjectComments();
            $offset = 0;
            $testComments = $modelComments->getCommentTreeForProjectList($this->_projectId);            
            $this->view->commentsJson = Zend_Json::encode($testComments);

            $modelClone = new Default_Model_ProjectClone();
            $origins =  $modelClone->fetchOrigins($this->_projectId);
            $this->view->originsJson = Zend_Json::encode($origins);
            $related =  $modelClone->fetchRelatedProducts($this->_projectId);
             $this->view->relatedJson = Zend_Json::encode($related);

             $moreProducts = $tableProject->fetchMoreProjects($this->view->product, 8);
             $this->view->moreProductsJson = Zend_Json::encode($moreProducts);

            $moreProducts = $tableProject->fetchMoreProjectsOfOtherUsr($this->view->product, 8);
            $this->view->moreProductsOfOtherUsrJson = Zend_Json::encode($moreProducts);



    }

    public function indexAction()
    {        

        if (!empty($this->_collectionId)) {
            $modelProduct = new Default_Model_Project();
            $productInfo = $modelProduct->fetchProductForCollectionId($this->_collectionId);
            $this->_projectId = $productInfo->project_id;
        }


        if (empty($this->_projectId)) {
            $this->redirect('/explore');
        }

        $this->view->paramPageId = (int)$this->getParam('page');
        $this->view->member_id = null;
        if(null != $this->_authMember && null != $this->_authMember->member_id) {
            $this->view->member_id = $this->_authMember->member_id;
        }
        //        $this->fetchDataForIndexView();        
        
        $modelProduct = new Default_Model_Project();
        $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
        if (empty($productInfo)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }
        
        //Check if this is a collection
        if($productInfo->type_id == $modelProduct::PROJECT_TYPE_COLLECTION) {
            $this->redirect('/c/'.$this->_projectId);
        }
        
        $this->view->product = $productInfo;
        $this->view->headTitle($productInfo->title . ' - ' . $this->getHeadTitle(), 'SET');
        $this->view->cat_id = $this->view->product->project_category_id;

        
        
        //create ppload download hash: secret + collection_id + expire-timestamp
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $collectionID = $this->view->product->ppload_collection_id;
        $timestamp = time() + 3600; // one hour valid
        //20181009 ronald: change hash from MD5 to SHA512
        //$hash = md5($salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying
        $hash = hash('sha512',$salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying

        $this->view->download_hash = $hash;
        $this->view->download_timestamp = $timestamp;

        $helperUserIsOwner = new Default_View_Helper_UserIsOwner();
        $helperIsProjectActive = new Default_View_Helper_IsProjectActive();
        if ((false === $helperIsProjectActive->isProjectActive($this->view->product->project_status))
            AND (false === $helperUserIsOwner->UserIsOwner($this->view->product->member_id))
        ) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        if (APPLICATION_ENV != 'searchbotenv') {
            $tablePageViews = new Default_Model_DbTable_StatPageViews();
            $tablePageViews->savePageView($this->_projectId, $this->getRequest()->getClientIp(),
                $this->_authMember->member_id);
        }
        
        $fmodel =new  Default_Model_DbTable_PploadFiles();
        
        $filesList = array();
            
        if(isset($this->view->product->ppload_collection_id)) {
            $files = $fmodel->fetchFilesForProject($this->view->product->ppload_collection_id);
            if(!empty($files)) {
                foreach ($files as $file) {
                    $timestamp = time() + 3600; // one hour valid
                    $hash = hash('sha512',$salt . $file['collection_id'] . $timestamp); // order isn't important at all... just do the same when verifying
                    $url = PPLOAD_API_URI . 'files/download/id/' . $file['id'] . '/s/' . $hash . '/t/' . $timestamp;
                    if(null != $this->_authMember) {
                        $url .= '/u/' . $this->_authMember->member_id;
                    }
                    $url .= '/lt/filepreview/' . $file['name'];
                    $file['url'] = urlencode($url);
                    
                    //If this file is a video, we have to convert it for preview
                    if(!empty($file['type']) && in_array($file['type'], Backend_Commands_ConvertVideo::$VIDEO_FILE_TYPES) && empty($file['ppload_file_preview_id'])) {
                        $queue = Local_Queue_Factory::getQueue();
                        $command = new Backend_Commands_ConvertVideo($file['collection_id'], $file['id'], $file['type']);
                        $queue->send(serialize($command));
                    }
                    if(!empty($file['url_preview'])) {
                        $file['url_preview'] = urlencode($file['url_preview']);
                    }
                    if(!empty($file['url_thumb'])) {
                        $file['url_thumb'] = urlencode($file['url_thumb']);
                    }
                    
                    $filesList[] = $file;
                }
            }
        }

        $this->view->filesJson = Zend_Json::encode($filesList);

       
        //gitlab
        if($this->view->product->is_gitlab_project) {
            $gitProject = $this->fetchGitlabProject($this->view->product->gitlab_project_id);
            if(null == $gitProject) {
                $this->view->product->is_gitlab_project = 0;
                $this->view->product->show_gitlab_project_issues = 0;
                $this->view->product->use_gitlab_project_readme = 0;
                $this->view->product->gitlab_project_id = null;
            } else {
                $this->view->gitlab_project = $gitProject;

                //show issues?
                if($this->view->product->show_gitlab_project_issues) {
                    $issues = $this->fetchGitlabProjectIssues($this->view->product->gitlab_project_id);
                    $this->view->gitlab_project_issues = $issues;
                    $this->view->gitlab_project_issues_url = $this->view->gitlab_project['web_url'] . '/issues/';
                }

                //show readme.md?
                if($this->view->product->use_gitlab_project_readme && null != $this->view->gitlab_project['readme_url']) {
                    $config = Zend_Registry::get('config')->settings->server->opencode;
                    $readme = $this->view->gitlab_project['web_url'].'/raw/master/README.md?inline=false';       
                    $httpClient = new Zend_Http_Client($readme, array('keepalive' => true, 'strictredirects' => true));
                    $httpClient->resetParameters();
                    $httpClient->setUri($readme);
                    $httpClient->setHeaders('Private-Token', $config->private_token);
                    $httpClient->setHeaders('Sudo', $config->user_sudo);
                    $httpClient->setHeaders('User-Agent', $config->user_agent);
                    $httpClient->setMethod(Zend_Http_Client::GET);

                    $response = $httpClient->request();

                    $body = $response->getRawBody();

                    if (count($body) == 0) {
                        return array();
                    }
                    include_once('Parsedown.php');
                    $Parsedown = new Parsedown();

                    $this->view->readme = $Parsedown->text($body);

                } else {
                    $this->view->readme = null;
                }
            }
        }
        

        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;              
        if($storeConfig->layout_pagedetail && $storeConfig->isRenderReact()){ 
            $this->initJsonForReact();           
            $this->_helper->viewRenderer('index-react');              
        }

    }

    public function showAction()
    {
        $this->view->authMember = $this->_authMember;
        $this->_helper->viewRenderer('index');
        $this->indexAction();
    }



    public function addAction()
    {
        $this->view->member = $this->_authMember;
        $this->view->mode = 'add';
        
        if($this->getParam('catId')){
            $this->view->catId = $this->getParam('catId');
        }
        $form = new Default_Form_Product(array('member_id' => $this->view->member->member_id));
        $this->view->form = $form;
        
        if ($this->_request->isGet()) {
            return;
        }
        
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        $isAdmin = false;
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $isAdmin = true;
        }
        

        if (isset($_POST['cancel'])) { // user cancel function
            $this->redirect('/member/' . $this->_authMember->member_id . '/news/');
        }

        if (false === $form->isValid($_POST)) { // form not valid
            $this->view->form = $form;
            $this->view->error = 1;

            return;
        }

        $values = $form->getValues();

        $imageModel = new Default_Model_DbTable_Image();
        try {
            $values['image_small'] = $imageModel->saveImage($form->getElement(self::IMAGE_SMALL_UPLOAD));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ERROR upload productPicture - ' . print_r($e, true));
        }

        // form was valid, so we can set status to active
        $values['status'] = Default_Model_DbTable_Project::PROJECT_ACTIVE;

        // save new project
        $modelProject = new Default_Model_Project();

        Zend_Registry::get('logger')->info(__METHOD__ . ' - $post: ' . print_r($_POST, true));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - $files: ' . print_r($_FILES, true));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - input values: ' . print_r($values, true));

        $newProject = null;
        try {
            if (isset($values['project_id'])) {
                $newProject = $modelProject->updateProject($values['project_id'], $values);
            } else {
                $newProject = $modelProject->createProject($this->_authMember->member_id, $values, $this->_authMember->username);
                //$this->createSystemPlingForNewProject($newProject->project_id);
            }
        } catch (Exception $exc) {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - traceString: ' . $exc->getTraceAsString());
        }

        if (!$newProject) {
            $this->_helper->flashMessenger->addMessage('<p class="text-error">You did not choose a Category in the last level.</p>');
            $this->forward('add');

            return;
        }

        //update the gallery pics
        $mediaServerUrls = $this->saveGalleryPics($form->gallery->upload->upload_picture);
        $modelProject->updateGalleryPictures($newProject->project_id, $mediaServerUrls);

        //If there is no Logo, we take the 1. gallery pic
        if (!isset($values['image_small']) || $values['image_small'] == '') {
            $values['image_small'] = $mediaServerUrls[0];
            $newProject = $modelProject->updateProject($newProject->project_id, $values);
        }

        //New Project in Session, for AuthValidation (owner)
        $this->_auth->getIdentity()->projects[$newProject->project_id] = array('project_id' => $newProject->project_id);

        $modelTags = new Default_Model_Tags();
        if ($values['tagsuser']) {
            $modelTags->processTagsUser($newProject->project_id, implode(',', $values['tagsuser']),Default_Model_Tags::TAG_TYPE_PROJECT);
        } else {
            $modelTags->processTagsUser($newProject->project_id, null, Default_Model_Tags::TAG_TYPE_PROJECT);
        }
        
        if(!$isAdmin) {
            if ($values['is_original']) {
                $modelTags->processTagProductOriginal($newProject->project_id, $values['is_original']);
            }
        } else {
            $modelTags->processTagProductOriginalOrModification($newProject->_projectId,$values['is_original_or_modification'][0]);
        }

        

        //set license, if needed
        $licenseTag = $form->getElement('license_tag_id')->getValue();
        //only set/update license tags if something was changed
        if ($licenseTag && count($licenseTag) > 0) {
            $modelTags->saveLicenseTagForProject($newProject->project_id, $licenseTag);
            $activityLog = new Default_Model_ActivityLog();
            $activityLog->logActivity($newProject->project_id, $newProject->project_id, $this->_authMember->member_id,Default_Model_ActivityLog::PROJECT_LICENSE_CHANGED, array('title' => 'Set new License Tag', 'description' => 'New TagId: ' . $licenseTag));
        }

        $isGitlabProject = $form->getElement('is_gitlab_project')->getValue();
        $gitlabProjectId = $form->getElement('gitlab_project_id')->getValue();
        if ($isGitlabProject && $gitlabProjectId == 0) {
            $values['gitlab_project_id'] = null;
        }

        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($newProject->project_id, $newProject->member_id, Default_Model_ActivityLog::PROJECT_CREATED, $newProject->toArray());

        // ppload
        $this->processPploadId($newProject);

        try {
            if (100 < $this->_authMember->roleId) {
                if (Default_Model_Spam::hasSpamMarkers($newProject->toArray())) {
                    $tableReportComments = new Default_Model_DbTable_ReportProducts();
                    $tableReportComments->save(array('project_id' => $newProject->project_id, 'reported_by' => 24, 'text' => "System: automatic spam detection"));
                }
                Default_Model_DbTable_SuspicionLog::logProject($newProject, $this->_authMember, $this->getRequest());
            }
        } catch (Zend_Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage());
        }

        $this->redirect('/member/' . $newProject->member_id . '/products/');
    }

    private function saveGalleryPics($form_element)
    {
        $imageModel = new Default_Model_DbTable_Image();

        return $imageModel->saveImages($form_element);
    }


    /**
     * @param $projectData
     *
     * @throws Zend_Exception
     * @throws Zend_Queue_Exception
     */
    protected function createTaskWebsiteOwnerVerification($projectData)
    {
        if (empty($projectData->link_1)) {
            return;
        }
        $checkAuthCode = new Local_Verification_WebsiteProject();
        $authCode = $checkAuthCode->generateAuthCode(stripslashes($projectData->link_1));
        $queue = Local_Queue_Factory::getQueue();
        $command = new Backend_Commands_CheckProjectWebsite($projectData->project_id, $projectData->link_1, $authCode);
        $queue->send(serialize($command));
    }

    /**
     * @param $projectData
     */
    protected function processPploadId($projectData)
    {
        if ($projectData->ppload_collection_id) {
            $pploadApi = new Ppload_Api(array(
                'apiUri'   => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret'   => PPLOAD_SECRET
            ));
            // Update collection information
            $collectionCategory = $projectData->project_category_id;
            if (Default_Model_Project::PROJECT_ACTIVE == $projectData->status) {
                $collectionCategory .= '-published';
            }
            $collectionRequest = array(
                'title'       => $projectData->title,
                'description' => $projectData->description,
                'category'    => $collectionCategory,
                'content_id'  => $projectData->project_id
            );

            $collectionResponse = $pploadApi->putCollection($projectData->ppload_collection_id, $collectionRequest);

            // Store product image as collection thumbnail
            $this->_updatePploadMediaCollectionthumbnail($projectData);
        }
    }

    /**
     * ppload
     */
    protected function _updatePploadMediaCollectionthumbnail($projectData)
    {
        if (empty($projectData->ppload_collection_id)
            || empty($projectData->image_small)
        ) {
            return false;
        }

        $pploadApi = new Ppload_Api(array(
            'apiUri'   => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret'   => PPLOAD_SECRET
        ));

        $filename = sys_get_temp_dir() . '/' . $projectData->image_small;
        if (false === file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        $viewHelperImage = new Default_View_Helper_Image();
        $uri = $viewHelperImage->Image($projectData->image_small, array(
                'width'  => 600,
                'height' => 600
            ));

        file_put_contents($filename, file_get_contents($uri));

        $mediaCollectionthumbnailResponse =
            $pploadApi->postMediaCollectionthumbnail($projectData->ppload_collection_id, array('file' => $filename));

        unlink($filename);

        if (isset($mediaCollectionthumbnailResponse->status)
            && $mediaCollectionthumbnailResponse->status == 'success'
        ) {
            return true;
        }

        return false;
    }

    public function editAction()
    {
        if (empty($this->_projectId)) {
            $this->redirect($this->_helper->url('add'));

            return;
        }

        $this->_helper->viewRenderer('add'); // we use the same view as you can see at add a product
        $this->view->mode = 'edit';

        $projectTable = new Default_Model_DbTable_Project();
        $projectModel = new Default_Model_Project();
        $modelTags = new Default_Model_Tags();
        $tagTable = new Default_Model_DbTable_Tags();

        //check if product with given id exists
        $projectData = $projectTable->find($this->_projectId)->current();
        if (empty($projectData)) {
            $this->redirect($this->_helper->url('add'));

            return;
        }

        $member = null;
        if (isset($this->_authMember) AND (false === empty($this->_authMember->member_id))) {
            $member = $this->_authMember;
        } else {
            throw new Zend_Controller_Action_Exception('no authorization found');
        }

        if (("admin" == $this->_authMember->roleName)) {
            $modelMember = new Default_Model_Member();
            $member = $modelMember->fetchMember($projectData->member_id, false);
        }
        
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        $isAdmin = false;
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $isAdmin = true;
        }


        //set ppload-collection-id in view
        $this->view->ppload_collection_id = $projectData->ppload_collection_id;
        $this->view->project_id = $projectData->project_id;
        $this->view->product = $projectData;

        //create ppload download hash: secret + collection_id + expire-timestamp
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $collectionID = $projectData->ppload_collection_id;
        $timestamp = time() + 3600; // one hour valid
        //20181009 ronald: change hash from MD5 to SHA512
        //$hash = md5($salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying
        $hash = hash('sha512',$salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying

        $this->view->download_hash = $hash;
        $this->view->download_timestamp = $timestamp;
        $this->view->member_id = $member->member_id;
        $this->view->member = $member;


        //read the already existing gallery pics and add them to the form
        $sources = $projectModel->getGalleryPictureSources($this->_projectId);
        
        //get the gitlab projects for this user

        //setup form
        $form = new Default_Form_Product(array('pictures' => $sources, 'member_id' => $this->view->member_id));
        if (false === empty($projectData->image_small)) {
            $form->getElement('image_small_upload')->setRequired(false);
        }
        $form->getElement('preview')->setLabel('Save');

        $form->removeElement('project_id'); // we don't need this field in edit mode

        if ($this->_request->isGet()) {
            $form->populate($projectData->toArray());
           // $form->populate(array('tags' => $modelTags->getTags($projectData->project_id, Default_Model_Tags::TAG_TYPE_PROJECT)));
            $form->populate(array('tagsuser' => $modelTags->getTagsUser($projectData->project_id, Default_Model_Tags::TAG_TYPE_PROJECT)));
            $form->getElement('image_small')->setValue($projectData->image_small);
            //Bilder voreinstellen
            $form->getElement(self::IMAGE_SMALL_UPLOAD)->setValue($projectData->image_small);

            $licenseTags = $tagTable->fetchLicenseTagsForProject($this->_projectId);
            $licenseTag = null;
            if($licenseTags) {
                $licenseTag = $licenseTags[0]['tag_id'];
            }
            $form->getElement('license_tag_id')->setValue($licenseTag);

            if(!$isAdmin) {
                $is_original = $modelTags->isProductOriginal($projectData->project_id);
                if($is_original){
                    $form->getElement('is_original')->checked= true;                
                }
            } else {
                $is_original = $modelTags->isProductOriginal($projectData->project_id);
                $is_modification = $modelTags->isProductModification($projectData->project_id);
                if($is_original){
                    $form->getElement('is_original_or_modification')->setValue(1);                
                } else if($is_modification){
                    $form->getElement('is_original_or_modification')->setValue(2);                
                }
            }
            
 
            $this->view->form = $form;

            return;
        }

        if (isset($_POST['cancel'])) { // user cancel function
            $this->redirect('/member/' . $member->member_id . '/news/');
        }

        if (false === $form->isValid($_POST, $this->_projectId)) { // form not valid
            $this->view->form = $form;
            $this->view->error = 1;

            return;
        }

        $values = $form->getValues();


        //set license, if needed
        $tagList = $modelTags->getTagsArray($this->_projectId, $modelTags::TAG_TYPE_PROJECT, $modelTags::TAG_LICENSE_GROUPID);
        $oldLicenseTagId = null;
        if($tagList && count($tagList) == 1) {
            $oldLicenseTagId = $tagList[0]['tag_id'];
        }

        $licenseTag = $form->getElement('license_tag_id')->getValue();
        //only set/update license tags if something was changed
        if($licenseTag <> $oldLicenseTagId) {
            $modelTags->saveLicenseTagForProject($this->_projectId, $licenseTag);
            $activityLog = new Default_Model_ActivityLog();
            $activityLog->logActivity($this->_projectId, $this->_projectId, $this->_authMember->member_id, Default_Model_ActivityLog::PROJECT_LICENSE_CHANGED, array('title' => 'License Tag', 'description' => 'Old TagId: '.$oldLicenseTagId.' - New TagId: '.$licenseTag));
        }
        
        //gitlab project
        $isGitlabProject = $form->getElement('is_gitlab_project')->getValue();
        $gitlabProjectId = $form->getElement('gitlab_project_id')->getValue();
        if($isGitlabProject && $gitlabProjectId == 0) {
            $values['gitlab_project_id'] = null;
        }



        $imageModel = new Default_Model_DbTable_Image();
        try {
            $uploadedSmallImage = $imageModel->saveImage($form->getElement(self::IMAGE_SMALL_UPLOAD));
            $values['image_small'] = $uploadedSmallImage ? $uploadedSmallImage : $values['image_small'];
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ERROR upload productPicture - ' . print_r($e, true));
        }

        // save changes
        $projectModel->updateProject($this->_projectId, $values);

        //update the gallery pics
        $pictureSources = array_merge($values['gallery']['online_picture'],
            $this->saveGalleryPics($form->gallery->upload->upload_picture));
        $projectModel->updateGalleryPictures($this->_projectId, $pictureSources);

        //If there is no Logo, we take the 1. gallery pic
        if (!isset($projectData->image_small) || $projectData->image_small == '') {
            $projectData->image_small = $pictureSources[0];
        }
        //20180219 ronald: we set the changed_at only by new files or new updates
        //$projectData->changed_at = new Zend_Db_Expr('NOW()');
        $projectData->save();
        
        
        if(!$isAdmin) {
            $modelTags->processTagProductOriginal($this->_projectId,$values['is_original']);
        } else {
            $modelTags->processTagProductOriginalOrModification($this->_projectId,$values['is_original_or_modification'][0]);
        }
        
        

        if($values['tagsuser']) {
            $modelTags->processTagsUser($this->_projectId,implode(',',$values['tagsuser']), Default_Model_Tags::TAG_TYPE_PROJECT);
        }else
        {
            $modelTags->processTagsUser($this->_projectId,null, Default_Model_Tags::TAG_TYPE_PROJECT);
        }

        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id, Default_Model_ActivityLog::PROJECT_EDITED, $projectData->toArray());

        // ppload
        $this->processPploadId($projectData);

        try {
            if (100 < $this->_authMember->roleId) {
                if (Default_Model_Spam::hasSpamMarkers($projectData->toArray())) {
                    $tableReportComments = new Default_Model_DbTable_ReportProducts();
                    $tableReportComments->save(array('project_id' => $projectData->project_id, 'reported_by' => 24, 'text' => "System: automatic spam detection on product edit"));
                }
                Default_Model_DbTable_SuspicionLog::logProject($projectData, $this->_authMember, $this->getRequest());
            }
        } catch (Zend_Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage());
        }

        $helperBuildMemberUrl = new Default_View_Helper_BuildMemberUrl();
        $this->redirect($helperBuildMemberUrl->buildMemberUrl($member->username, 'products'));
    }

    public function getupdatesajaxAction()
    {
        $this->view->authMember = $this->_authMember;
        $tableProject = new Default_Model_ProjectUpdates();

        $updates = $tableProject->fetchProjectUpdates($this->_projectId);

        foreach ($updates as $key => $update) {
            $updates[$key]['title'] = Default_Model_HtmlPurify::purify($update['title']);
            $updates[$key]['text'] = Default_Model_BBCode::renderHtml(Default_Model_HtmlPurify::purify(htmlentities($update['text'], ENT_QUOTES | ENT_IGNORE)));
            $updates[$key]['raw_title'] = $update['title'];
            $updates[$key]['raw_text'] = $update['text'];
        }

        $result['status'] = 'success';
        $result['ResultSize'] = count($updates);
        $result['updates'] = $updates;

        $this->_helper->json($result);
    }

    public function saveupdateajaxAction()
    {
        $filter =
        new Zend_Filter_Input(
            array(
                '*' => 'StringTrim'
            ),
            array(
                '*'         => array(),
                'title'     => array(
                    new Zend_Validate_StringLength(array('min' => 3, 'max' => 200)),
                    'presence' => 'required',
                    'allowEmpty' => false
                ),
                'text'      => array(
                    new Zend_Validate_StringLength(array('min' => 3, 'max' => 16383)),
                    'presence' => 'required',
                    'allowEmpty' => false
                ),
                'update_id' => array('digits', 'allowEmpty' => true)
            ), $this->getAllParams(), array('allowEmpty' => true));

        if ($filter->hasInvalid() OR $filter->hasMissing() OR $filter->hasUnknown()) {
            $result['status'] = 'error';
            $result['messages'] = $filter->getMessages();
            $result['update_id'] = null;

            $this->_helper->json($result);
        }

        $update_id = $filter->getEscaped('update_id');
        $tableProjectUpdates = new Default_Model_ProjectUpdates();

        //Save update
        if (!empty($update_id)) {
            //Update old update
            $updateArray = array();
            $updateArray['title'] = $filter->getUnescaped('title');
            $updateArray['text'] = $filter->getUnescaped('text');
            $updateArray['changed_at'] = new Zend_Db_Expr('Now()');
            $countUpdated = $tableProjectUpdates->update($updateArray, 'project_update_id = ' . $update_id);
        } else {
            //Add new update
            $updateArray = array();
            $updateArray['title'] = $filter->getUnescaped('title');
            $updateArray['text'] = $filter->getUnescaped('text');
            $updateArray['public'] = 1;
            $updateArray['project_id'] = $this->_projectId;
            $updateArray['member_id'] = $this->_authMember->member_id;
            $updateArray['created_at'] = new Zend_Db_Expr('Now()');
            $updateArray['changed_at'] = new Zend_Db_Expr('Now()');
            $rowset = $tableProjectUpdates->save($updateArray);
            $update_id = $rowset->project_update_id;

            //20180219 ronald: we set the changed_at only by new files or new updates
            $projectTable = new Default_Model_Project();
            $projectUpdateRow = $projectTable->find($this->_projectId)->current();
            if (count($projectUpdateRow) == 1) {
                $projectUpdateRow->changed_at = new Zend_Db_Expr('NOW()');
                $projectUpdateRow->save();
            }
        }

        $result['status'] = 'success';
        $result['update_id'] = $update_id;

        $this->_helper->json($result);
    }

    public function deleteupdateajaxAction()
    {
        $this->view->authMember = $this->_authMember;
        $tableProject = new Default_Model_ProjectUpdates();

        $params = $this->getAllParams();
        $project_update_id = $params['update_id'];
        $updateArray = array();
        $updateArray['public'] = 0;
        $updateArray['changed_at'] = new Zend_Db_Expr('Now()');
        $tableProject->update($updateArray, 'project_update_id = ' . $project_update_id);

        $result['status'] = 'success';
        $result['update_id'] = $project_update_id;

        $this->_helper->json($result);
    }

    public function updatesAction()
    {
        $this->view->authMember = $this->_authMember;
        $tableProject = new Default_Model_Project();
        $this->view->product = $tableProject->fetchProductInfo($this->_projectId);
        if (false === isset($this->view->product)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }
        $this->view->relatedProducts = $tableProject->fetchSimilarProjects($this->view->product, 6);
        $this->view->supporter = $tableProject->fetchProjectSupporter($this->_projectId);
        $this->view->product_views = $tableProject->fetchProjectViews($this->_projectId);

        $modelPlings = new Default_Model_DbTable_Plings();
        $this->view->comments = $modelPlings->getCommentsForProject($this->_projectId, 10);

        $tableMember = new Default_Model_Member();
        $this->view->member = $tableMember->fetchMemberData($this->view->product->member_id);

        $this->view->updates = $tableProject->fetchProjectUpdates($this->_projectId);

        $tablePageViews = new Default_Model_DbTable_StatPageViews();
        $tablePageViews->savePageView($this->_projectId, $this->getRequest()->getClientIp(),
            $this->_authMember->member_id);
    }

    public function updateAction()
    {

        $this->_helper->layout()->setLayout('flat_ui');

        $this->view->headScript()->setFile('');
        $this->view->headLink()->setStylesheet('');

        $this->_helper->viewRenderer('add');

        $form = new Default_Form_ProjectUpdate();
        $projectTable = new Default_Model_Project();
        $projectData = null;
        $projectUpdateId = (int)$this->getParam('upid');

        $this->view->member = $this->_authMember;
        $this->view->title = 'Add an update for your product';

        $activityLogType = Default_Model_ActivityLog::PROJECT_ITEM_CREATED;

        if (false === empty($projectUpdateId)) {
            $this->view->title = 'Edit an product update';
            $projectData = $projectTable->find($projectUpdateId)->current();
            $form->populate($projectData->toArray());
            $form->getElement('upid')->setValue($projectUpdateId);
            $activityLogType = Default_Model_ActivityLog::PROJECT_ITEM_EDITED;
        }

        $this->view->form = $form;

        if ($this->_request->isGet()) {
            return;
        }

        if (isset($_POST['cancel'])) { // user cancel function
            $this->_redirect('/member/' . $this->_authMember->member_id . '/news/');
        }

        if (false === $form->isValid($_POST)) { // form not valid
            $this->view->form = $form;
            $this->view->error = 1;

            return;
        }

        $values = $form->getValues();

        $projectUpdateRow = $projectTable->find($values['upid'])->current();

        if (count($projectUpdateRow) == 0) {
            $projectUpdateRow = $projectTable->createRow($values);
            $projectUpdateRow->project_id = $values['upid'];
            $projectUpdateRow->created_at = new Zend_Db_Expr('NOW()');
            $projectUpdateRow->start_date = new Zend_Db_Expr('NOW()');
            $projectUpdateRow->member_id = $this->_authMember->member_id;
            $projectUpdateRow->creator_id = $this->_authMember->member_id;
            $projectUpdateRow->status = Default_Model_Project::PROJECT_ACTIVE;
            $projectUpdateRow->type_id = 2;
            $projectUpdateRow->pid = $this->_projectId;
        } else {
            $projectUpdateRow->setFromArray($values);
            //20180219 ronald: we set the changed_at only by new files or new updates
            //$projectUpdateRow->changed_at = new Zend_Db_Expr('NOW()');
        }

        $lastId = $projectUpdateRow->save();

        //New Project in Session, for AuthValidation (owner)
        $this->_auth->getIdentity()->projects[$lastId] = array('project_id' => $lastId);

        $tableProduct = new Default_Model_Project();
        $product = $tableProduct->find($this->_projectId)->current();
        $activityLogValues = $projectUpdateRow->toArray();
        $activityLogValues['image_small'] = $product->image_small;
        $activityLog = new Default_Model_ActivityLog();
        //$activityLog->writeActivityLog($lastId, $projectUpdateRow->member_id, $activityLogType, $activityLogValues);
        $activityLog->writeActivityLog($lastId, $this->_authMember->member_id, $activityLogType, $activityLogValues);

        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        $urlProjectShow = $helperBuildProductUrl->buildProductUrl($this->_projectId);

        $this->redirect($urlProjectShow);
    }

    public function previewAction()
    {
        $this->view->authMember = $this->_authMember;

        $form = new Default_Form_ProjectConfirm();

        if ($this->_request->isGet()) {
            $form->populate(get_object_vars($this->_authMember));
            $this->view->form = $form;
            $this->fetchDataForIndexView();
            $this->view->preview = $this->view->render('product/index.phtml');

            return;
        }

        if (isset($_POST['save'])) {
            $projectTable = new Default_Model_Project();
            $projectTable->setStatus(Default_Model_Project::PROJECT_INACTIVE, $this->_projectId);

            //todo: maybe we have to delete the project data from database otherwise we produce many zombies
            $this->redirect('/member/' . $this->_authMember->member_id . '/products/');
        }

        if (isset($_POST['back'])) {
            $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
            $this->redirect($helperBuildProductUrl->buildProductUrl($this->_projectId, 'edit'));
        }

        if (false === $form->isValid($_POST)) { // form not valid
            $this->view->form = $form;
            $this->fetchDataForIndexView();
            $this->view->preview = $this->view->render('product/index.phtml');
            $this->view->error = 1;

            return;
        }

        $projectTable = new Default_Model_Project();
        $projectTable->setStatus(Default_Model_Project::PROJECT_ACTIVE, $this->_projectId);

        // add to search index
        $modelProject = new Default_Model_Project();
        $productInfo = $modelProject->fetchProductInfo($this->_projectId);
        $modelSearch = new Default_Model_Search_Lucene();
        $modelSearch->addDocument($productInfo->toArray());

        $this->redirect('/member/' . $this->_authMember->member_id . '/products/');
    }

    protected function fetchDataForIndexView()
    {
        $tableProject = new Default_Model_Project();
        $this->view->product = $tableProject->fetchProductInfo($this->_projectId);
        if (false === isset($this->view->product)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        $desc = $this->view->product->description;
        $newDesc = $this->bbcode2html($desc);
        $this->view->product->description = $newDesc;

        // switch off temporally 02.05.2017
        //$this->view->supporting = $tableProject->fetchProjectSupporterWithPlings($this->_projectId);
        //$orgUpdates = $tableProjectUpdates->fetchLastProjectUpdate($this->_projectId);
        $tableProjectUpdates = new Default_Model_ProjectUpdates();
        $orgUpdates = $tableProjectUpdates->fetchProjectUpdates($this->_projectId);
        $newUpdates = array();
        foreach ($orgUpdates as $update) {
            $desc = $update['text'];
            $newDesc = $this->bbcode2html($desc);
            $update['text'] = $newDesc;
            $newUpdates[] = $update;
        }

        $this->view->updates = $newUpdates;
        // switch off temporally 02.05.2017
        //$this->view->supporter = $tableProject->fetchProjectSupporter($this->_projectId);

        $this->view->galleryPictures = $tableProject->getGalleryPictureSources($this->_projectId);
        $this->view->product_views = $tableProject->fetchProjectViews($this->_projectId);

        $helperFetchCategory = new Default_View_Helper_CatTitle();
        $helperFetchCatParent = new Default_View_Helper_CatParent();

        $this->view->catId = $this->view->product->project_category_id;
        $this->view->catTitle = $helperFetchCategory->catTitle($this->view->product->project_category_id);
        $this->view->catParentId =
            $helperFetchCatParent->getCatParentId(array('project_category_id' => $this->view->product->project_category_id));
        if ($this->view->catParentId) {
            $this->view->catParentTitle = $helperFetchCategory->catTitle($this->view->catParentId);
        }

        $AuthCodeExist = new Local_Verification_WebsiteProject();
        $this->view->websiteAuthCode = $AuthCodeExist->generateAuthCode(stripslashes($this->view->product->link_1));

        // switch off temporally 02.05.2017
        //$modelPlings = new Default_Model_DbTable_Plings();
        //$this->view->plings = $modelPlings->getDonationsForProject($this->_projectId, 10);

        $tableMember = new Default_Model_Member();
        $this->view->member = $tableMember->fetchMemberData($this->view->product->member_id);

        $this->view->more_products = $tableProject->fetchMoreProjects($this->view->product, 8);
        $this->view->more_products_otheruser = $tableProject->fetchMoreProjectsOfOtherUsr($this->view->product, 8);

        $widgetDefaultModel = new Default_Model_DbTable_ProjectWidgetDefault();
        $widgetDefault = $widgetDefaultModel->fetchConfig($this->_projectId);
        $widgetDefault->text->headline = $this->view->product->title;
        //$widgetDefault->amounts->current = $this->view->product->amount_received;
        $widgetDefault->amounts->goal = $this->view->product->amount;
        $widgetDefault->project = $this->_projectId;
        $this->view->widgetConfig = $widgetDefault;

        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        $this->view->permaLink = $helperBuildProductUrl->buildProductUrl($this->_projectId, null, null, true);
        $this->view->urlPay = $helperBuildProductUrl->buildProductUrl($this->_projectId, 'pay');

        $referrerUrl = $this->readExploreUrlFromReferrer();
        if (false === empty($referrerUrl)) {
            $this->view->referrerUrl = $referrerUrl;
        }
    }

    /**
     * transforms a string with bbcode markup into html
     *
     * @param string $txt
     * @param bool   $nl2br
     *
     * @return string
     */
    private function bbcode2html($txt, $nl2br = true, $forcecolor = '')
    {

        if (!empty($forcecolor)) {
            $fc = ' style="color:' . $forcecolor . ';"';
        } else {
            $fc = '';
        }
        $newtxt = htmlspecialchars($txt);
        if ($nl2br) {
            $newtxt = nl2br($newtxt);
        }

        $patterns = array(
            '`\[b\](.+?)\[/b\]`is',
            '`\[i\](.+?)\[/i\]`is',
            '`\[u\](.+?)\[/u\]`is',
            '`\[li\](.+?)\[/li\]`is',
            '`\[strike\](.+?)\[/strike\]`is',
            '`\[url\]([a-z0-9]+?://){1}([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)\[/url\]`si',
            '`\[quote\](.+?)\[/quote\]`is',
            '`\[indent](.+?)\[/indent\]`is'
        );

        $replaces = array(
            '<strong' . $fc . '>\\1</strong>',
            '<em' . $fc . '>\\1</em>',
            '<span style="border-bottom: 1px dotted">\\1</span>',
            '<li' . $fc . ' style="margin-left:20px;">\\1</li>',
            '<strike' . $fc . '>\\1</strike>',
            '<a href="\1\2" rel="nofollow" target="_blank">\1\2</a>',
            '<strong' . $fc
            . '>Quote:</strong><div style="margin:0px 10px;padding:5px;background-color:#F7F7F7;border:1px dotted #CCCCCC;width:80%;"><em>\1</em></div>',
            '<pre' . $fc . '>\\1</pre>'
        );

        $newtxt = preg_replace($patterns, $replaces, $newtxt);

        return ($newtxt);
    }

    protected function readExploreUrlFromReferrer()
    {
        $helperBuildExploreUrl = new Default_View_Helper_BuildExploreUrl();
        $referrerExplore = $helperBuildExploreUrl->buildExploreUrl(null, null, null, null, true);

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();
        if (strpos($request->getHeader('referer'), $referrerExplore) !== false) {
            return $request->getHeader('referer');
        }
    }

    public function plingAction()
    {

        if (empty($this->_projectId)) {
            $this->redirect('/explore');
        }

        $this->view->authMember = $this->_authMember;

        $this->fetchDataForIndexView();
        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        $this->view->urlPay = $helperBuildProductUrl->buildProductUrl($this->_projectId, 'pay');
        $this->view->amount = (float)$this->getParam('amount', 1);
        $this->view->comment = html_entity_decode(strip_tags($this->getParam('comment'), null), ENT_QUOTES, 'utf-8');
        $this->view->provider =
            mb_strtolower(html_entity_decode(strip_tags($this->getParam('provider'), null), ENT_QUOTES, 'utf-8'),
                'utf-8');

        $this->view->headTitle($this->_browserTitlePrepend . $this->view->product->title, 'SET');

        $helperUserIsOwner = new Default_View_Helper_UserIsOwner();
        $helperIsProjectActive = new Default_View_Helper_IsProjectActive();
        if ((false === $helperIsProjectActive->isProjectActive($this->view->product->project_status)) AND (false
                === $helperUserIsOwner->UserIsOwner($this->view->product->member_id))
        ) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        $tableProject = new Default_Model_Project();
        $this->view->supporting = $tableProject->fetchProjectSupporterWithPlings($this->_projectId);
    }



    public function payAction()
    {
        $this->_helper->layout()->disableLayout();
        $tableProject = new Default_Model_Project();
        $project = $tableProject->fetchProductInfo($this->_projectId);

        //get parameter
        $amount = (float)$this->getParam('amount', 1);
        $comment = Default_Model_HtmlPurify::purify($this->getParam('comment'));
        $paymentProvider =
            mb_strtolower(html_entity_decode(strip_tags($this->getParam('provider'), null), ENT_QUOTES, 'utf-8'),
                'utf-8');
        $hideIdentity = (int)$this->getParam('hideId', 0);

        $paymentGateway = $this->createPaymentGateway($paymentProvider);
        $paymentGateway->getUserDataStore()->generateFromArray($project->toArray());

        $requestMessage = 'Thank you for supporting: ' . $paymentGateway->getUserDataStore()->getProductTitle();

        $response = null;
        try {
            $response = $paymentGateway->requestPayment($amount, $requestMessage);
            $this->view->checkoutEndpoint = $paymentGateway->getCheckoutEndpoint();
            $this->view->paymentKey = $response->getPaymentId();
            $this->_helper->viewRenderer->setRender('pay_' . $paymentProvider);
        } catch (Exception $e) {
            throw new Zend_Controller_Action_Exception('payment error', 500, $e);
        }

        if (false === $response->isSuccessful()) {
            throw new Zend_Controller_Action_Exception('payment failure', 500);
        }

        if (empty($this->_authMember->member_id) or ($hideIdentity == 1)) {
            $memberId = 1;
        } else {
            $memberId = $this->_authMember->member_id;
        }

        //Add pling
        $modelPlings = new Default_Model_DbTable_Plings();
        $plingId = $modelPlings->createNewPlingFromResponse($response, $memberId, $project->project_id, $amount);

        if (false == empty($comment)) {
            $modelComments = new Default_Model_ProjectComments();
            $dataComment = array(
                'comment_type'      => Default_Model_DbTable_Comments::COMMENT_TYPE_PLING,
                'comment_target_id' => $project->project_id,
                'comment_member_id' => $memberId,
                'comment_pling_id'  => $plingId,
                'comment_text'      => $comment
            );
            $modelComments->save($dataComment);
        }

        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($this->_projectId, $memberId, Default_Model_ActivityLog::PROJECT_PLINGED,
            $project->toArray());
    }

    /**
     * @param string $paymentProvider
     *
     * @return Local_Payment_GatewayInterface
     * @throws Exception
     * @throws Local_Payment_Exception
     * @throws Zend_Controller_Exception
     * @throws Zend_Exception
     */
    protected function createPaymentGateway($paymentProvider)
    {
        $httpHost = $this->getRequest()->getHttpHost();
        /** @var Zend_Config $config */
        $config = Zend_Registry::get('config');
        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        switch ($paymentProvider) {
            case 'paypal':
                $paymentGateway = new Default_Model_PayPal_Gateway($config->third_party->paypal);
                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/paypal');
                //                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/paypal?XDEBUG_SESSION_START=1');
                $paymentGateway->setCancelUrl($helperBuildProductUrl->buildProductUrl($this->_projectId,
                    'paymentcancel', null, true));
                $paymentGateway->setReturnUrl($helperBuildProductUrl->buildProductUrl($this->_projectId, 'paymentok',
                    null, true));
                break;

            case 'dwolla':
                $paymentGateway = new Default_Model_Dwolla_Gateway($config->third_party->dwolla);
                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/dwolla');
                //                $paymentGateway->setIpnNotificationUrl('http://' . $_SERVER ['HTTP_HOST'] . '/gateway/dwolla?XDEBUG_SESSION_START=1');
                $paymentGateway->setReturnUrl($helperBuildProductUrl->buildProductUrl($this->_projectId, 'dwolla', null,
                    true));
                break;

            case 'amazon':
                $paymentGateway = new Default_Model_Amazon_Gateway($config->third_party->amazon);
                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/amazon');
                //                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/amazon?XDEBUG_SESSION_START=1');
                $paymentGateway->setCancelUrl($helperBuildProductUrl->buildProductUrl($this->_projectId,
                    'paymentcancel', null, true));
                $paymentGateway->setReturnUrl($helperBuildProductUrl->buildProductUrl($this->_projectId, 'paymentok',
                    null, true));
                break;

            default:
                throw new Zend_Controller_Exception('No known payment provider found in parameters.');
                break;
        }

        return $paymentGateway;
    }

    public function dwollaAction()
    {
        $modelPling = new Default_Model_DbTable_Plings();
        $plingData = $modelPling->fetchRow(array('payment_reference_key = ?' => $this->getParam('checkoutId')));
        $plingData->payment_transaction_id = (int)$this->getParam('transaction');
        $plingData->save();

        if ($this->_getParam('status') == 'Completed') {
            $this->_helper->viewRenderer('paymentok');
            $this->paymentokAction();
        } else {
            $this->_helper->viewRenderer('paymentcancel');
            $this->paymentcancelAction();
        }
    }

    public function paymentokAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->view->paymentStatus = 'success';
        $this->view->paymentMessage = 'Payment successful.';
        $this->fetchDataForIndexView();
    }

    public function paymentcancelAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->view->paymentStatus = 'danger';
        $this->view->paymentMessage = 'Payment cancelled.';
        $this->fetchDataForIndexView();
    }

    public function deleteAction()
    {
        $this->_helper->layout()->setLayout('flat_ui');

        $memberId = (int)$this->getParam('m');

        if ((empty($this->_authMember->member_id)) OR (empty($memberId)) OR ($this->_authMember->member_id
                != $memberId)
        ) {
            $this->forward('products', 'user', 'default');

            return;
        }

        $tableProduct = new Default_Model_Project();
        $tableProduct->setDeleted($this->_authMember->member_id,$this->_projectId);

        $product = $tableProduct->find($this->_projectId)->current();

        // delete product from search index
        $modelSearch = new Default_Model_Search_Lucene();
        $modelSearch->deleteDocument($product->toArray());
        //        $command = new Backend_Commands_DeleteProductExtended($product);
        //        $command->doCommand();
        //        $queue = Local_Queue_Factory::getQueue('search');
        //        $command = new Backend_Commands_DeleteProductFromIndex($product->project_id, $product->project_category_id);
        //        $msg = $queue->send(serialize($command));

        // ppload
        // Delete collection
        if ($product->ppload_collection_id) {
            $pploadApi = new Ppload_Api(array(
                'apiUri'   => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret'   => PPLOAD_SECRET
            ));

            $collectionResponse = $pploadApi->deleteCollection($product->ppload_collection_id);
        }

        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id, Default_Model_ActivityLog::PROJECT_DELETED,
            $product->toArray());

        $this->forward('products', 'user', 'default');
    }

    public function unpublishAction()
    {
        $this->_helper->layout()->setLayout('flat_ui');

        $memberId = (int)$this->getParam('m');

        if (
            (empty($this->_authMember->member_id))
            OR
            (empty($memberId))
            OR ($this->_authMember->member_id != $memberId)
        ) {
            return;
        }

        $tableProduct = new Default_Model_Project();
        $tableProduct->setInActive($this->_projectId, $memberId);

        $product = $tableProduct->find($this->_projectId)->current();

        if (isset($product->type_id) && $product->type_id == Default_Model_Project::PROJECT_TYPE_UPDATE) {
            $parentProduct = $tableProduct->find($product->pid)->current();
            $product->image_small = $parentProduct->image_small;
        }

        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id, Default_Model_ActivityLog::PROJECT_UNPUBLISHED,
            $product->toArray());

        // remove unpublished project from search index
        $modelSearch = new Default_Model_Search_Lucene();
        $modelSearch->deleteDocument($product);

        // ppload
        if ($product->ppload_collection_id) {
            $pploadApi = new Ppload_Api(array(
                'apiUri'   => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret'   => PPLOAD_SECRET
            ));
            // Update collection information
            $collectionRequest = array(
                'category' => $product->project_category_id
            );

            $collectionResponse = $pploadApi->putCollection($product->ppload_collection_id, $collectionRequest);
        }

        $this->forward('products', 'user', 'default', array('member_id' => $memberId));
        //$this->redirect('/member/'.$memberId.'/products');
    }

    public function publishAction()
    {
        $memberId = (int)$this->getParam('m');

        if ((empty($this->_authMember->member_id)) OR (empty($memberId)) OR ($this->_authMember->member_id
                != $memberId)
        ) {
            return;
        }

        $tableProduct = new Default_Model_Project();
        $tableProduct->setActive($this->_authMember->member_id,$this->_projectId);

        $product = $tableProduct->find($this->_projectId)->current();

        if (isset($product->type_id) && $product->type_id == Default_Model_Project::PROJECT_TYPE_UPDATE) {
            $parentProduct = $tableProduct->find($product->pid)->current();
            $product->image_small = $parentProduct->image_small;
        }

        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id, Default_Model_ActivityLog::PROJECT_PUBLISHED,
            $product->toArray());

        // add published project to search index
//        $productInfo = $tableProduct->fetchProductInfo($this->_projectId);
//        $modelSearch = new Default_Model_Search_Lucene();
//        $modelSearch->addDocument($productInfo);

        // ppload
        if ($product->ppload_collection_id) {
            $pploadApi = new Ppload_Api(array(
                'apiUri'   => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret'   => PPLOAD_SECRET
            ));
            // Update collection information
            $collectionRequest = array(
                'category' => $product->project_category_id . '-published'
            );

            $collectionResponse = $pploadApi->putCollection($product->ppload_collection_id, $collectionRequest);
        }

        $this->forward('products', 'user', 'default', array('member_id' => $memberId));
        //$this->redirect('/member/'.$memberId.'/products');
    }

   public function loadratingsAction()
   {
        $this->_helper->layout->disableLayout();
        $tableProjectRatings = new Default_Model_DbTable_ProjectRating();            
        $ratings = $tableProjectRatings->fetchRating($this->_projectId);
        $this->_helper->json($ratings);
    }
    
    public function loadfilesjsonAction()
    {
        $this->_helper->layout->disableLayout();
        // $project_id = $this->getParam('pid');
        $modelProject = new Default_Model_Project();
        $files = $modelProject->fetchFilesForProject($this->_projectId);
        $salt = PPLOAD_DOWNLOAD_SECRET;
        foreach ($files as &$file) {
            $timestamp = time() + 3600; // one hour valid
            $hash = hash('sha512',$salt . $file['collection_id'] . $timestamp); // order isn't important at all... just do the same when verifying
            $url = PPLOAD_API_URI . 'files/download/id/' . $file['id'] . '/s/' . $hash . '/t/' . $timestamp;
            if(null != $this->_authMember) {
                $url .= '/u/' . $this->_authMember->member_id;
            }
            $url .= '/lt/filepreview/' . $file['name'];
            $file['url'] = urlencode($url);                    
        }        
        $this->_helper->json($files);
    }
    
    public function loadfirstfilejsonAction()
    {
        $this->_helper->layout->disableLayout();
        // $project_id = $this->getParam('pid');
        $modelProject = new Default_Model_Project();
        $files = $modelProject->fetchFilesForProject($this->_projectId);
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $file = $files[0];
        
        $timestamp = time() + 3600; // one hour valid
        $hash = hash('sha512',$salt . $file['collection_id'] . $timestamp); // order isn't important at all... just do the same when verifying
        $url = PPLOAD_API_URI . 'files/download/id/' . $file['id'] . '/s/' . $hash . '/t/' . $timestamp;
        if(null != $this->_authMember) {
            $url .= '/u/' . $this->_authMember->member_id;
        }
        $url .= '/lt/filepreview/' . $file['name'];
        $file['url'] = urlencode($url);                    

        $this->_helper->json($file);
    }
    
    public function loadinstallinstructionAction()
    {
        $this->_helper->layout->disableLayout();
        $infomodel = new Default_Model_Info();
        $text =  $infomodel->getOCSInstallInstruction();
        

        $this->_helper->json(array(
            'status'  => 'ok',            
            'data'    => $text
        ));
    }

    public function followAction()
    {
        $this->_helper->layout()->disableLayout();
        //        $this->_helper->viewRenderer->setNoRender(true);

        $this->view->project_id = $this->_projectId;
        $this->view->authMember = $this->_authMember;

        if (array_key_exists($this->_projectId, $this->_authMember->projects)) {
            return;
        }

        $projectFollowTable = new Default_Model_DbTable_ProjectFollower();

        $newVals = array('project_id' => $this->_projectId, 'member_id' => $this->_authMember->member_id);
        $where = $projectFollowTable->select()->where('member_id = ?', $this->_authMember->member_id)
                                    ->where('project_id = ?', $this->_projectId, 'INTEGER')
        ;
        $result = $projectFollowTable->fetchRow($where);

        if (null === $result) {
            $projectFollowTable->createRow($newVals)->save();
            $tableProduct = new Default_Model_Project();
            $product = $tableProduct->find($this->_projectId)->current();

            $activityLog = new Default_Model_ActivityLog();
            $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id,
                Default_Model_ActivityLog::PROJECT_FOLLOWED, $product->toArray());
        }


        // ppload
        //Add collection to favorite
        // $projectTable = new Default_Model_DbTable_Project();
        // $projectData = $projectTable->find($this->_projectId)->current();
        // if ($projectData->ppload_collection_id) {
        //     $pploadApi = new Ppload_Api(array(
        //         'apiUri'   => PPLOAD_API_URI,
        //         'clientId' => PPLOAD_CLIENT_ID,
        //         'secret'   => PPLOAD_SECRET
        //     ));
        //
        //     $favoriteRequest = array(
        //         'user_id'       => $this->_authMember->member_id,
        //         'collection_id' => $projectData->ppload_collection_id
        //     );
        //
        //     $favoriteResponse = $pploadApi->postFavorite($favoriteRequest);
        // }

    }

    public function unfollowAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer('follow');

        $this->view->project_id = $this->_projectId;
        $this->view->authMember = $this->_authMember;

        $projectFollowTable = new Default_Model_DbTable_ProjectFollower();

        $projectFollowTable->delete('member_id=' . $this->_authMember->member_id . ' AND project_id='
            . $this->_projectId);


        $tableProduct = new Default_Model_Project();
        $product = $tableProduct->find($this->_projectId)->current();

        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id,
            Default_Model_ActivityLog::PROJECT_UNFOLLOWED, $product->toArray());

        // ppload
        // Delete collection from favorite
        // $projectTable = new Default_Model_DbTable_Project();
        // $projectData = $projectTable->find($this->_projectId)->current();
        // if ($projectData->ppload_collection_id) {
        //     $pploadApi = new Ppload_Api(array(
        //         'apiUri'   => PPLOAD_API_URI,
        //         'clientId' => PPLOAD_CLIENT_ID,
        //         'secret'   => PPLOAD_SECRET
        //     ));
        //
        //     $favoriteRequest = array(
        //         'user_id'       => $this->_authMember->member_id,
        //         'collection_id' => $projectData->ppload_collection_id
        //     );
        //
        //     $favoriteResponse =
        //         $pploadApi->postFavorite($favoriteRequest); // This post call will retrieve existing favorite info
        //     if (!empty($favoriteResponse->favorite->id)) {
        //         $favoriteResponse = $pploadApi->deleteFavorite($favoriteResponse->favorite->id);
        //     }
        // }
    }

    public function followpAction()
    {
        $this->_helper->layout()->disableLayout();
        //        $this->_helper->viewRenderer->setNoRender(true);

        $this->view->project_id = $this->_projectId;
        $this->view->authMember = $this->_authMember;

        if (array_key_exists($this->_projectId, $this->_authMember->projects)) {
            return;
        }

        $projectFollowTable = new Default_Model_DbTable_ProjectFollower();

        $newVals = array('project_id' => $this->_projectId, 'member_id' => $this->_authMember->member_id);
        $where = $projectFollowTable->select()->where('member_id = ?', $this->_authMember->member_id)
                                    ->where('project_id = ?', $this->_projectId, 'INTEGER')
        ;
        $result = $projectFollowTable->fetchRow($where);

        if (null === $result) {
             $projectFollowTable->createRow($newVals)->save();
            $tableProduct = new Default_Model_Project();
            $product = $tableProduct->find($this->_projectId)->current();
            $activityLog = new Default_Model_ActivityLog();
            $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id,
                Default_Model_ActivityLog::PROJECT_FOLLOWED, $product->toArray());
        }
    }

    public function unfollowpAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer('followp');

        $this->view->project_id = $this->_projectId;
        $this->view->authMember = $this->_authMember;

        $projectFollowTable = new Default_Model_DbTable_ProjectFollower();

        $projectFollowTable->delete('member_id=' . $this->_authMember->member_id . ' AND project_id='
            . $this->_projectId);


        $tableProduct = new Default_Model_Project();
        $product = $tableProduct->find($this->_projectId)->current();

        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id,
            Default_Model_ActivityLog::PROJECT_UNFOLLOWED, $product->toArray());

    }

    protected function logActivity($logId)
    {
        $tableProduct = new Default_Model_Project();
        $product = $tableProduct->find($this->_projectId)->current();
        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id,
            $logId, $product->toArray());
    }


    public function followprojectAction()
    {
        $this->_helper->layout()->disableLayout();

        $this->view->project_id = $this->_projectId;
        $this->view->authMember = $this->_authMember;

        // not allow to pling himself
        if (array_key_exists($this->_projectId, $this->_authMember->projects))
        {
             $this->_helper->json(array(
                    'status' => 'error',
                    'msg'   => 'not allowed'
                ));
            return;
        }


        $projectFollowTable = new Default_Model_DbTable_ProjectFollower();

        $newVals = array('project_id' => $this->_projectId, 'member_id' => $this->_authMember->member_id);
        $where = $projectFollowTable->select()->where('member_id = ?', $this->_authMember->member_id)
                                    ->where('project_id = ?', $this->_projectId, 'INTEGER')  ;

        $result = $projectFollowTable->fetchRow($where);

        if (null === $result) {
            $projectFollowTable->createRow($newVals)->save();
            $this->logActivity(Default_Model_ActivityLog::PROJECT_FOLLOWED);
            $cnt = $projectFollowTable->countForProject($this->_projectId);
             $this->_helper->json(array(
                    'status' => 'ok',
                    'msg'   => 'Success.',
                    'cnt'  => $cnt,
                    'action' =>'insert'
                ));
        }else{
            $projectFollowTable->delete('member_id=' . $this->_authMember->member_id . ' AND project_id='
            . $this->_projectId);
            $this->logActivity(Default_Model_ActivityLog::PROJECT_UNFOLLOWED);
            $cnt = $projectFollowTable->countForProject($this->_projectId);
            $this->_helper->json(array(
                    'status' => 'ok',
                    'msg'   => 'Success.',
                    'cnt'  => $cnt,
                    'action' => 'delete'
                ));
        }

    }


    public function plingprojectAction()
    {
        $this->_helper->layout()->disableLayout();

        $this->view->project_id = $this->_projectId;
        $this->view->authMember = $this->_authMember;

        // not allow to pling himself
        if (array_key_exists($this->_projectId, $this->_authMember->projects))
        {
             $this->_helper->json(array(
                    'status' => 'error',
                    'msg'   => 'not allowed'
                ));
            return;
        }

        // not allow to pling if not supporter
        $helperIsSupporter = new Default_View_Helper_IsSupporter();
        if(!$helperIsSupporter->isSupporter($this->_authMember->member_id))
        {
             $this->_helper->json(array(
                    'status' => 'error',
                    'msg'   => 'become a supporter first please. '
                ));
            return;
        }


        $projectplings = new Default_Model_ProjectPlings();

        $newVals = array('project_id' => $this->_projectId, 'member_id' => $this->_authMember->member_id);
        $sql = $projectplings->select()
                ->where('member_id = ?', $this->_authMember->member_id)
                ->where('is_deleted = ?',0)
                ->where('project_id = ?', $this->_projectId, 'INTEGER')
        ;
        $result = $projectplings->fetchRow($sql);

        if (null === $result) {
             $projectplings->createRow($newVals)->save();
             //$this->logActivity(Default_Model_ActivityLog::PROJECT_PLINGED_2);

             $cnt = $projectplings->getPlingsAmount($this->_projectId);
             $this->_helper->json(array(
                    'status' => 'ok',
                    'msg'   => 'Success.',
                    'cnt'  => $cnt,
                    'action' =>'insert'
                ));
        }else{

            // delete pling
            $projectplings->setDelete($result->project_plings_id);
            //$this->logActivity(Default_Model_ActivityLog::PROJECT_DISPLINGED_2);

             $cnt = $projectplings->getPlingsAmount($this->_projectId);
            $this->_helper->json(array(
                    'status' => 'ok',
                    'msg'   => 'Success.',
                    'cnt'  => $cnt,
                    'action' => 'delete'
                ));
        }

    }

/**

    public function unplingprojectAction()
    {
        $this->_helper->layout()->disableLayout();

        $projectplings = new Default_Model_ProjectPlings();
        $pling = $projectplings->getPling($this->_projectId,$this->_authMember->member_id);

        if($pling)
        {
            $projectplings->setDelete($pling->project_plings_id);
            $cnt = count($projectplings->getPlings($this->_projectId));
             $this->_helper->json(array(
                    'status' => 'ok',
                    'deleted' => $pling->project_plings_id,
                    'msg'   => 'Success. ',
                     'cnt'  => $cnt
                ));

             $tableProduct = new Default_Model_Project();
            $product = $tableProduct->find($this->_projectId)->current();

            $activityLog = new Default_Model_ActivityLog();
            $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id,
            Default_Model_ActivityLog::PROJECT_DISPLINGED_2, $product->toArray());
        }else{
             $this->_helper->json(array(
                    'status' => 'error',
                    'msg'   => 'not existing.'
                ));
        }


    }
**/

    public function followsAction()
    {
        $projectFollowTable = new Default_Model_Member();

        $memberId = $this->_authMember->member_id;
        $this->view->productList = $projectFollowTable->fetchFollowedProjects($memberId);

        $projectArray = $this->generateFollowedProjectsViewData($this->view->productList);

        $this->view->productArray['followedProjects'] = $projectArray;
    }

    /**
     * @param $list
     *
     * @return array
     */
    protected function generateFollowedProjectsViewData($list)
    {
        $viewArray = array();

        if (count($list) == 0) {
            return $viewArray;
        }

        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        foreach ($list as $element) {
            $arr = array();
            $arr['id'] = $element->project_id;
            $arr['name'] = $element->title;
            $arr['image'] = $element->image_small;
            $arr['url'] = $helperBuildProductUrl->buildProductUrl($element->project_id);
            $arr['urlUnFollow'] = $helperBuildProductUrl->buildProductUrl($element->project_id, 'unfollow');
            #$arr['showUrlUnFollow'] = $this->view->isMember;

            $viewArray[] = $arr;
        }

        return $viewArray;
    }

    public function verifycodeAction()
    {
        $this->_helper->layout()->disableLayout();

        if ($this->_request->isXmlHttpRequest()) {
            $tabProject = new Default_Model_DbTable_Project();
            $dataProject = $tabProject->find($this->_projectId)->current();
            $this->createTaskWebsiteOwnerVerification($dataProject);
            $this->view->message = 'Your product page is stored for validation.';

            return;
        }

        $this->view->message = 'This service is not available at the moment. Please try again later.';
    }

    /**
     * @throws Zend_Controller_Action_Exception
     * @deprecated
     */
    public function fetchAction()
    {
        $this->_helper->layout()->disableLayout();

        if ($this->_request->isXmlHttpRequest()) {
            $this->view->authMember = $this->_authMember;

            $this->fetchDataForIndexView();
            $tableProject = new Default_Model_Project();
            $this->view->supporting = $tableProject->fetchProjectSupporterWithPlings($this->_projectId);

            if (false === isset($this->view->product)) {
                throw new Zend_Controller_Action_Exception('This page does not exist', 404);
            }

            $helperUserIsOwner = new Default_View_Helper_UserIsOwner();
            $helperIsProjectActive = new Default_View_Helper_IsProjectActive();
            if ((false === $helperIsProjectActive->isProjectActive($this->view->product->project_status)) AND (false
                    === $helperUserIsOwner->UserIsOwner($this->view->product->member_id))
            ) {
                throw new Zend_Controller_Action_Exception('This page does not exist', 404);
            }

            $tablePageViews = new Default_Model_DbTable_StatPageViews();
            $tablePageViews->savePageView($this->_projectId, $this->getRequest()->getClientIp(),
                $this->_authMember->member_id);
        }

        $this->_helper->json(get_object_vars($this->view));
    }

    public function claimAction()
    {
        $modelProduct = new Default_Model_Project();
        $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
        if ($productInfo->claimable != Default_Model_Project::PROJECT_CLAIMABLE) {
            throw new Zend_Controller_Action_Exception('Method not available', 404);
        }
        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        if (empty($productInfo->claimed_by_member)) {
            $modelProduct->setClaimedByMember($this->_authMember->member_id, $this->_projectId);

            $claimMail = new Default_Plugin_SendMail('tpl_mail_claim_product');
            $claimMail->setTemplateVar('sender', $this->_authMember->mail);
            $claimMail->setTemplateVar('productid', $productInfo->project_id);
            $claimMail->setTemplateVar('producttitle', $productInfo->title);
            $claimMail->setTemplateVar('userid', $this->_authMember->member_id);
            $claimMail->setTemplateVar('username', $this->_authMember->username);
            $claimMail->setTemplateVar('usermail', $this->_authMember->mail);
            $claimMail->setReceiverMail(array('contact@opendesktop.org'));
            $claimMail->send();

            $claimMailConfirm = new Default_Plugin_SendMail('tpl_mail_claim_confirm');
            $claimMailConfirm->setTemplateVar('sender', 'contact@opendesktop.org');
            $claimMailConfirm->setTemplateVar('producttitle', $productInfo->title);
            $claimMailConfirm->setTemplateVar('productlink', 'http://' . $this->getRequest()->getHttpHost()
                . $helperBuildProductUrl->buildProductUrl($productInfo->project_id));
            $claimMailConfirm->setTemplateVar('username', $this->_authMember->username);
            $claimMailConfirm->setReceiverMail($this->_authMember->mail);
            $claimMailConfirm->send();
        }

        $this->_helper->viewRenderer('index');
        $this->indexAction();
    }

    public function makerconfigAction()
    {
        $this->_helper->layout()->disableLayout();

        $widgetProjectId = (int)$this->getParam('project_id');
        if (false == isset($widgetProjectId)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }
        $widgetDefaultModel = new Default_Model_DbTable_ProjectWidgetDefault();
        $widgetDefault = $widgetDefaultModel->fetchConfig($widgetProjectId);
        if (!isset($widgetDefault)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        } else {
            $this->view->widgetConfig = $widgetDefault;
            $productModel = new Default_Model_Project();
            $this->view->product = $productModel->fetchProductDataFromMV($widgetProjectId);
            $this->view->supporting = $productModel->fetchProjectSupporterWithPlings($widgetProjectId);
            $plingModel = new Default_Model_DbTable_Plings();
            $this->view->comments = $plingModel->getCommentsForProject($widgetProjectId, 10);
            $websiteOwner = new Local_Verification_WebsiteProject();
            $this->view->authCode = '<meta name="ocs-site-verification" content="'
                . $websiteOwner->generateAuthCode(stripslashes($this->view->product->link_1)) . '" />';
        }
    }

    /**
     * ppload
     */
    public function addpploadfileAction()
    {
        $this->_helper->layout()->disableLayout();
        $log = Zend_Registry::get('logger');
        $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . '**********' . "\n");

        $projectTable = new Default_Model_DbTable_Project();
        $projectData = $projectTable->find($this->_projectId)->current();

        $error_text = '';

        // Add file to ppload collection
        if (!empty($_FILES['file_upload']['tmp_name'])
            && $_FILES['file_upload']['error'] == UPLOAD_ERR_OK
        ) {
            $tmpFilename = dirname($_FILES['file_upload']['tmp_name']) . '/' . basename($_FILES['file_upload']['name']);
            $log->debug(__CLASS__ . '::' . __FUNCTION__ . '::' . print_r($tmpFilename, true) . "\n");
            move_uploaded_file($_FILES['file_upload']['tmp_name'], $tmpFilename);

            $pploadApi = new Ppload_Api(array(
                'apiUri'   => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret'   => PPLOAD_SECRET
            ));

            $fileRequest = array(
                'file'     => $tmpFilename,
                'owner_id' => $this->_authMember->member_id
            );
            
            //Admins can upload files for users
            $helperUserRole = new Backend_View_Helper_UserRole();
            $userRoleName = $helperUserRole->userRole();
            if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
                $member_id = $projectData->member_id;
                $fileRequest = array(
                    'file'     => $tmpFilename,
                    'owner_id' => $member_id
                );
            }
            
            if ($projectData->ppload_collection_id) {
                // Append to existing collection
                $fileRequest['collection_id'] = $projectData->ppload_collection_id;
            }
            //if (isset($_POST['file_description'])) {
            //	$fileRequest['description'] = mb_substr($_POST['file_description'], 0, 140);
            //}
            $fileResponse = $pploadApi->postFile($fileRequest);
            $log->debug(__CLASS__ . '::' . __FUNCTION__ . '::' . print_r($fileResponse, true) . "\n");

            unlink($tmpFilename);

            if (!empty($fileResponse->file->collection_id)) {
                if (!$projectData->ppload_collection_id) {
                    // Save collection ID
                    $projectData->ppload_collection_id = $fileResponse->file->collection_id;
                    //20180219 ronald: we set the changed_at only by new files or new updates
                    if((int)$this->_authMember->member_id==(int)$projectData->member_id)
                    {
                        $projectData->changed_at = new Zend_Db_Expr('NOW()');
                    } else {
                        $log->info('********** ' . __CLASS__ . '::' . __FUNCTION__ . ' Project ChangedAt is not set: Auth-Member ('.$this->_authMember->member_id.') != Project-Owner ('.$projectData->member_id.'): **********' . "\n");
                    }    
                    $projectData->ghns_excluded = 0;
                    $projectData->save();

                    $activityLog = new Default_Model_ActivityLog();
                    $activityLog->writeActivityLog($this->_projectId, $projectData->member_id,
                        Default_Model_ActivityLog::PROJECT_EDITED, $projectData->toArray());
                    // Update profile information
                    $memberTable = new Default_Model_DbTable_Member();
                    $memberSettings = $memberTable->find($this->_authMember->member_id)->current();
                    $mainproject = $projectTable->find($memberSettings->main_project_id)->current();
                    $profileName = '';
                    if ($memberSettings->firstname
                        || $memberSettings->lastname
                    ) {
                        $profileName = trim($memberSettings->firstname . ' ' . $memberSettings->lastname);
                    } else {
                        if ($memberSettings->username) {
                            $profileName = $memberSettings->username;
                        }
                    }
                    $profileRequest = array(
                        'owner_id'    => $this->_authMember->member_id,
                        'name'        => $profileName,
                        'email'       => $memberSettings->mail,
                        'homepage'    => $memberSettings->link_website,
                        'description' => $mainproject->description
                    );
                    $profileResponse = $pploadApi->postProfile($profileRequest);
                    // Update collection information
                    $collectionCategory = $projectData->project_category_id;
                    if (Default_Model_Project::PROJECT_ACTIVE == $projectData->status) {
                        $collectionCategory .= '-published';
                    }
                    $collectionRequest = array(
                        'title'       => $projectData->title,
                        'description' => $projectData->description,
                        'category'    => $collectionCategory,
                        'content_id'  => $projectData->project_id
                    );
                    $collectionResponse =
                        $pploadApi->putCollection($projectData->ppload_collection_id, $collectionRequest);
                    // Store product image as collection thumbnail
                    $this->_updatePploadMediaCollectionthumbnail($projectData);
                } else {
                    //20180219 ronald: we set the changed_at only by new files or new updates
                    if((int)$this->_authMember->member_id==(int)$projectData->member_id)
                    {
                        $projectData->changed_at = new Zend_Db_Expr('NOW()');
                    } else {
                        $log->info('********** ' . __CLASS__ . '::' . __FUNCTION__ . ' Project ChangedAt is not set: Auth-Member ('.$this->_authMember->member_id.') != Project-Owner ('.$projectData->member_id.'): **********' . "\n");
                    }                      
                    $projectData->ghns_excluded = 0;
                    $projectData->save();                    
                }
                
                //If this file is a video, we have to convert it for preview
                if(!empty($fileResponse->file->type) && in_array($fileResponse->file->type, Backend_Commands_ConvertVideo::$VIDEO_FILE_TYPES)) {
                    $queue = Local_Queue_Factory::getQueue();
                    $command = new Backend_Commands_ConvertVideo($projectData->ppload_collection_id, $fileResponse->file->id, $fileResponse->file->type);
                    $queue->send(serialize($command));
                }

                $this->_helper->json(array(
                    'status' => 'ok',
                    'file'   => $fileResponse->file
                ));

                return;
            }
        }

        $log->debug('********** END ' . __CLASS__ . '::' . __FUNCTION__ . '**********' . "\n");
        $this->_helper->json(array('status' => 'error', 'error_text' => $error_text));
    }

    /**
     * ppload
     */
    public function updatepploadfileAction()
    {
        $this->_helper->layout()->disableLayout();
        $log = Zend_Registry::get('logger');
        $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . '**********' . "\n");

        $projectTable = new Default_Model_DbTable_Project();
        $projectData = $projectTable->find($this->_projectId)->current();

        $error_text = '';

        // Update a file in ppload collection
        if (!empty($_POST['file_id'])) {
            $pploadApi = new Ppload_Api(array(
                'apiUri'   => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret'   => PPLOAD_SECRET
            ));

            $fileResponse = $pploadApi->getFile($_POST['file_id']);
            if (isset($fileResponse->file->collection_id)
                && $fileResponse->file->collection_id == $projectData->ppload_collection_id
            ) {
                $fileRequest = array();
                $tmpFilename = '';
                if (!empty($_FILES['file_upload']['tmp_name'])
                    && $_FILES['file_upload']['error'] == UPLOAD_ERR_OK
                ) {
                    $tmpFilename = dirname($_FILES['file_upload']['tmp_name']) . '/' . basename($_FILES['file_upload']['name']);
                    $log->debug(__CLASS__ . '::' . __FUNCTION__ . '::' . print_r($tmpFilename, true) . "\n");
                    move_uploaded_file($_FILES['file_upload']['tmp_name'], $tmpFilename);
                    $fileRequest['file'] = $tmpFilename;
                    
                    //20180219 ronald: we set the changed_at only by new files or new updates
                    if((int)$this->_authMember->member_id==(int)$projectData->member_id)
                    {
                        $projectData->changed_at = new Zend_Db_Expr('NOW()');
                    } else {
                        $log->info('********** ' . __CLASS__ . '::' . __FUNCTION__ . ' Project ChangedAt is not set: Auth-Member ('.$this->_authMember->member_id.') != Project-Owner ('.$projectData->member_id.'): **********' . "\n");
                    }                      
                    $projectData->ghns_excluded = 0;
                    $projectData->save();  
                    
                }
                if (isset($_POST['file_description'])) {
                    $fileRequest['description'] = mb_substr($_POST['file_description'], 0, 140);
                }
                if (isset($_POST['file_category'])) {
                    $fileRequest['category'] = $_POST['file_category'];
                }
                if (isset($_POST['file_tags'])) {
                    $fileRequest['tags'] = $_POST['file_tags'];
                }
                if (isset($_POST['ocs_compatible'])) {
                    $fileRequest['ocs_compatible'] = $_POST['ocs_compatible'];
                }
                if (isset($_POST['file_version'])) {
                    $fileRequest['version'] = $_POST['file_version'];
                }

                $fileResponse = $pploadApi->putFile($_POST['file_id'], $fileRequest);
                $log->debug(__CLASS__ . '::' . __FUNCTION__ . '::' . print_r($fileResponse, true) . "\n");

                if ($tmpFilename) {
                    unlink($tmpFilename);
                }

                if (isset($fileResponse->status)
                    && $fileResponse->status == 'success'
                ) {
                    $this->_helper->json(array(
                        'status' => 'ok',
                        'file'   => $fileResponse->file
                    ));

                    return;
                } else {
                    $error_text .= 'Response: $pploadApi->putFile(): ' . json_encode($fileResponse)
                        . '; $fileResponse->status: ' . $fileResponse->status;
                }
            } else {
                $error_text .= 'PPload Response: ' . json_encode($fileResponse)
                    . '; fileResponse->file->collection_id: ' . $fileResponse->file->collection_id
                    . ' != $projectData->ppload_collection_id: ' . $projectData->ppload_collection_id;
            }
        } else {
            $error_text .= 'No CollectionId or no FileId. CollectionId: ' . $projectData->ppload_collection_id
                . ', FileId: ' . $_POST['file_id'];
        }

        $log->debug('********** END ' . __CLASS__ . '::' . __FUNCTION__ . '**********' . "\n");
        $this->_helper->json(array('status' => 'error', 'error_text' => $error_text));
    }

    public function updatefiletagAction()
    {
        $this->_helper->layout()->disableLayout();

        $error_text = '';

        // Update a file information in ppload collection
        if (!empty($_POST['file_id'])) {
            $tagId = null;
            if (isset($_POST['tag_id'])) {
                $tagId = $_POST['tag_id'];
            }
            $tagGroupId = null;
            if (isset($_POST['tag_group_id'])) {
                $tagGroupId = $_POST['tag_group_id'];
            }

            //set architecture
            $modelTags = new Default_Model_Tags();
            $modelTags->saveFileTagForProjectAndTagGroup($this->_projectId, $_POST['file_id'], $tagId, $tagGroupId);

            $this->_helper->json(array('status' => 'ok'));

            return;
        } else {
            $error_text .= 'No FileId. , FileId: ' . $_POST['file_id'];
        }

        $this->_helper->json(array('status' => 'error', 'error_text' => $error_text));
    }
    
    public function deletefiletagAction()
    {
        $this->_helper->layout()->disableLayout();

        $error_text = '';

        // Update a file information in ppload collection
        if (!empty($_POST['file_id'])) {
            $tagId = null;
            if (isset($_POST['tag_id'])) {
                $tagId = $_POST['tag_id'];
            }

            //set architecture
            $modelTags = new Default_Model_Tags();
            $modelTags->deleteFileTagForProject($this->_projectId, $_POST['file_id'], $tagId);

            $this->_helper->json(array('status' => 'ok'));

            return;
        } else {
            $error_text .= 'No FileId. , FileId: ' . $_POST['file_id'];
        }

        $this->_helper->json(array('status' => 'error', 'error_text' => $error_text));
    }


    public function updatecompatibleAction()
    {
        $this->_helper->layout()->disableLayout();

        $error_text = '';

        // Update a file information in ppload collection
        if (!empty($_POST['file_id'])) {
            $typeId = null;
            if (isset($_POST['is_compatible'])) {
                $is_compatible = $_POST['is_compatible'];
            }

            return;
        } else {
            $error_text .= 'No FileId. , FileId: ' . $_POST['file_id'];
        }

        $this->_helper->json(array('status' => 'error', 'error_text' => $error_text));
    }

    public function startdownloadAction() {
        $this->_helper->layout()->disableLayout();

        /**
         * Save Download-Data in Member_Download_History
         */
        $file_id = $this->getParam('file_id');
        $file_type = $this->getParam('file_type');
        $file_name = $this->getParam('file_name');
        $file_size = $this->getParam('file_size');
        $projectId = $this->_projectId;
        
        $this->redirect('/dl?file_id='.$file_id.'&file_type='.$file_type.'&file_name='.$file_name.'&file_size='.$file_size.'&project_id='.$projectId);
        
        
//        if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
/*            if(isset($file_id) && isset($projectId) && isset($memberId)) {
                $memberDlHistory = new Default_Model_DbTable_MemberDownloadHistory();
                $data = array('project_id' => $projectId, 'member_id' => $memberId, 'file_id' => $file_id, 'file_type' => $file_type, 'file_name' => $file_name, 'file_size' => $file_size);
                $memberDlHistory->createRow($data)->save();
            }

            $url = urldecode($urltring);
            $this->redirect($url);
 * 
 */
//        } else {
//            $this->redirect('/ads?file_id='.$file_id);
//        }
    }

    /**
     * ppload
     */
    public function deletepploadfileAction()
    {
        $this->_helper->layout()->disableLayout();

        $projectTable = new Default_Model_DbTable_Project();
        $projectData = $projectTable->find($this->_projectId)->current();

        $error_text = '';

        // Delete file from ppload collection
        if (!empty($_POST['file_id'])) {
            $pploadApi = new Ppload_Api(array(
                'apiUri'   => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret'   => PPLOAD_SECRET
            ));

            $fileResponse = $pploadApi->getFile($_POST['file_id']);
            if (isset($fileResponse->file->collection_id)
                && $fileResponse->file->collection_id == $projectData->ppload_collection_id
            ) {
                $fileResponse = $pploadApi->deleteFile($_POST['file_id']);
                if (isset($fileResponse->status)
                    && $fileResponse->status == 'success'
                ) {

                    $this->_helper->json(array('status' => 'ok'));

                    return;
                } else {
                    $error_text .= 'Response: $pploadApi->putFile(): ' . json_encode($fileResponse);
                }
            }
        }

        $this->_helper->json(array('status' => 'error', 'error_text' => $error_text));
    }

    /**
     * ppload
     */
    public function deletepploadfilesAction()
    {
        $this->_helper->layout()->disableLayout();

        $projectTable = new Default_Model_DbTable_Project();
        $projectData = $projectTable->find($this->_projectId)->current();

        // Delete all files in ppload collection
        if ($projectData->ppload_collection_id) {
            $pploadApi = new Ppload_Api(array(
                'apiUri'   => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret'   => PPLOAD_SECRET
            ));

            $filesRequest = array(
                'collection_id' => $projectData->ppload_collection_id,
                'perpage'       => 1000
            );

            $filesResponse = $pploadApi->getFiles($filesRequest);

            if (isset($filesResponse->status)
                && $filesResponse->status == 'success'
            ) {
                foreach ($filesResponse->files as $file) {
                    $fileResponse = $pploadApi->deleteFile($file->id);
                    if (!isset($fileResponse->status)
                        || $fileResponse->status != 'success'
                    ) {
                        $this->_helper->json(array('status' => 'error'));

                        return;
                    }
                }
            }

            $this->_helper->json(array('status' => 'ok'));

            return;
        }

        $this->_helper->json(array('status' => 'error'));
    }

    /**
     * ppload
     */
    /*public function deletepploadcollectionAction()
    {
        $this->_helper->layout()->disableLayout();

        $projectTable = new Default_Model_DbTable_Project();
        $projectData = $projectTable->find($this->_projectId)->current();

        // Delete ppload collection
        if ($projectData->ppload_collection_id) {
            $pploadApi = new Ppload_Api(array(
                'apiUri' => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret' => PPLOAD_SECRET
            ));

            $collectionResponse = $pploadApi->deleteCollection($projectData->ppload_collection_id);

            if (isset($collectionResponse->status)
                && $collectionResponse->status == 'success'
            ) {
                $projectData->ppload_collection_id = null;
                $projectData->changed_at = new Zend_Db_Expr('NOW()');
                $projectData->save();

                $activityLog = new Default_Model_ActivityLog();
                $activityLog->writeActivityLog(
                    $this->_projectId,
                    $projectData->member_id,
                    Default_Model_ActivityLog::PROJECT_EDITED,
                    $projectData->toArray()
                );

                $this->_helper->json(array('status' => 'ok'));
                return;
            }
        }

        $this->_helper->json(array('status' => 'error'));
    }*/

    public function saveproductAction()
    {
        $form = new Default_Form_Product();

        // we don't need to test a file which doesn't exist in this case. The Framework stumbles if $_FILES is empty.
        if ($this->_request->isXmlHttpRequest() AND (count($_FILES) == 0)) {
            $form->removeElement('image_small_upload');
            //            $form->removeElement('image_big_upload');
            $form->removeSubForm('gallery');
            $form->removeElement('project_id'); //(workaround: Some Browsers send "0" in some cases.)
        }

        if (false === $form->isValid($_POST)) {
            $errors = $form->getMessages();
            $messages = $this->getErrorMessages($errors);
            $this->_helper->json(array('status' => 'error', 'messages' => $messages));
        }

        $formValues = $form->getValues();
        $formValues['status'] = Default_Model_Project::PROJECT_INCOMPLETE;

        $modelProject = new Default_Model_Project();
        $newProject =
            $modelProject->createProject($this->_authMember->member_id, $formValues, $this->_authMember->username);
        //$this->createSystemPlingForNewProject($newProject->project_id);
        //New Project in Session, for AuthValidation (owner)
        $this->_auth->getIdentity()->projects[$newProject->project_id] = array('project_id' => $newProject->project_id);

        $this->_helper->json(array('status' => 'ok', 'project_id' => $newProject->project_id));
    }


    protected function createPling($member_id,$project_id)
    {
            $projectplings = new Default_Model_ProjectPlings();
            $newVals = array('project_id' =>$project_id, 'member_id' => $member_id);
            $sql = $projectplings->select()
                    ->where('member_id = ?', $this->_authMember->member_id)
                    ->where('is_deleted = ?',0)
                    ->where('project_id = ?', $this->_projectId, 'INTEGER');
            $result = $projectplings->fetchRow($sql);
             if (null === $result) {
                 $projectplings->createRow($newVals)->save();
             }
    }



    /**
     * @param $errors
     *
     * @return array
     */
    protected function getErrorMessages($errors)
    {
        $messages = array();
        foreach ($errors as $element => $row) {
            if (!empty($row) && $element != 'submit') {
                foreach ($row as $validator => $message) {
                    $messages[$element][] = $message;
                }
            }
        }

        return $messages;
    }

    public function searchAction()
    {                
        // Filter-Parameter
        $params = $this->getAllParams();
        $filterInput =
            new Zend_Filter_Input(
                array(
                    '*' => 'StringTrim',
                    'projectSearchText' => array(new Zend_Filter_Callback('stripslashes'),'StripTags'),
                    'page' => 'digits',
                    'pci' => 'digits',
                    'ls'  => 'digits',
                    't' => array(new Zend_Filter_Callback('stripslashes'),'StripTags'),
                    'pkg'=> array(new Zend_Filter_Callback('stripslashes'),'StripTags'),
                    'lic'=> array(new Zend_Filter_Callback('stripslashes'),'StripTags'),
                    'arch'=> array(new Zend_Filter_Callback('stripslashes'),'StripTags')

                ),
                array(
                    'projectSearchText' => array(
                        new Zend_Validate_StringLength(array('min' => 3, 'max' => 100)),
                        'presence' => 'required'
                    ),
                    'page'              => array('digits', 'default' => '1'),
                    'f'                 => array(
                        new Zend_Validate_StringLength(array('min' => 3, 'max' => 100)),
                        //new Zend_Validate_InArray(array('f'=>'tags')),
                        'allowEmpty' => true
                    ),
                    'pci'               => array('digits',
                        'allowEmpty' => true
                    ),
                    'ls'                => array('digits',
                        'allowEmpty' => true
                    ),
                    't'                 => array(new Zend_Validate_StringLength(array('min' => 3, 'max' => 100)),
                        'allowEmpty' => true
                    ),
                    'pkg'                 => array(new Zend_Validate_StringLength(array('min' => 3, 'max' => 100)),
                        'allowEmpty' => true
                    ),
                    'lic'                 => array(new Zend_Validate_StringLength(array('min' => 3, 'max' => 100)),
                        'allowEmpty' => true
                    ),
                    'arch'                 => array(new Zend_Validate_StringLength(array('min' => 3, 'max' => 100)),
                        'allowEmpty' => true)
                ), $params);

        
        

        if ($filterInput->hasInvalid()) {
            $this->_helper->flashMessenger->addMessage('<p class="text-error">There was an error. Please check your input and try again.</p>');
            return;
        }



        $this->view->searchText = $filterInput->getEscaped('projectSearchText');
        $this->view->page = $filterInput->getEscaped('page');
        $this->view->searchField = $filterInput->getEscaped('f');
        $this->view->pci = $filterInput->getEscaped('pci');
        $this->view->ls = $filterInput->getEscaped('ls');
        $this->view->t = $filterInput->getEscaped('t');
        $this->view->pkg = $filterInput->getEscaped('pkg');
        $this->view->arch = $filterInput->getEscaped('arch');
        $this->view->lic = $filterInput->getEscaped('lic');
        $this->view->store = $this->getParam('domain_store_id');

        if(isset($params['isJson']))
        {
            $this->_helper->layout()->disableLayout();
            $filterScore = $this->view->ls ? 'laplace_score:['.$this->view->ls.' TO '.($this->view->ls+9).']':null;
            $filterCat = $this->view->pci ? 'project_category_id:('.$this->view->pci.')' : null;
            $filterTags = $this->view->t ? 'tags:('.$this->view->t.')' : null;
            $filterPkg = $this->view->pkg ? 'package_names:('.$this->view->pkg.')' : null;
            $filterArch = $this->view->arch ? 'arch_names:('.$this->view->arch.')' : null;
            $filterLic = $this->view->lic ? 'license_names:('.$this->view->lic.')' : null;
            // $param = array('q' => $this->view->searchText ,'store'=>$this->view->store,'page' => $this->view->page
            // , 'count' => 10, 'qf' => $this->view->searchField, 'fq' => array($filterCat, $filterScore, $filterTags,$filterPkg,$filterArch,$filterLic));
            
            $param = array('q' => 'test','store'=>null,'page' => 1
            , 'count' => 10);
            $viewHelperImage = new Default_View_Helper_Image();
        
            $modelSearch = new Default_Model_Solr();
            try {
                $result = $modelSearch->search($param);
                $products = $result['hits'];          
                
                // var_dump($products);
                // die;
                $ps=array();
                foreach ($products as $p) {
                    $img = $viewHelperImage->Image($p->image_small, array(
                        'width'  => 50,
                        'height' => 50
                    ));
                    $ps[] =array('description'=>$p->description
                        ,'title' =>$p->title
                        ,'project_id' =>$p->project_id
                        ,'member_id'=>$p->member_id
                        ,'username' => $p->username
                        ,'laplace_score' =>$p->laplace_score
                        ,'score' =>$p->score
                        ,'image_small' =>$img);
                }
                
                $this->_helper->json(array(
                    'status' => 'ok',
                    'products' => $ps,
                    'q' =>$param
                ));
            } catch (Exception $e) {
                $this->_helper->json(array(
                    'status' => 'err',
                    'msg' => 'Not Found! Try again.'
                ));
            }            
                
        }

        
    }

    
    
    /**
     * @param $memberId
     *
     * @throws Zend_Db_Table_Exception
     */
    protected function setViewDataForMyProducts($memberId)
    {
        $tableMember = new Default_Model_Member();
        $this->view->member = $tableMember->find($memberId)->current();

        $tableProduct = new Default_Model_Project();
        $this->view->products = $tableProduct->fetchAllProjectsForMember($memberId);
    }

    protected function _initResponseHeader()
    {
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";

        $this->getResponse()->setHeader('X-FRAME-OPTIONS', 'ALLOWALL',
                true)//            ->setHeader('Last-Modified', $modifiedTime, true)
             ->setHeader('Expires', $expires, true)->setHeader('Pragma', 'no-cache', true)
             ->setHeader('Cache-Control', 'private, no-cache, must-revalidate', true)
        ;
    }

    /**
     * @param $hits
     *
     * @return array
     */
    protected function generateProjectsArrayForView($hits)
    {
        $viewArray = array();
        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        /** @var $hit Zend_Search_Lucene_Search_QueryHit */
        foreach ($hits as $hit) {
            $project = $hit->getDocument();
            if (null != $project->username) {
                $isUpdate = ($project->type_id == 2);
                if ($isUpdate) {
                    $showUrl =
                        $helperBuildProductUrl->buildProductUrl($project->pid) . '#anker_' . $project->project_id;
                    $plingUrl = $helperBuildProductUrl->buildProductUrl($project->pid, 'pling');
                } else {
                    $showUrl = $helperBuildProductUrl->buildProductUrl($project->project_id);
                    $plingUrl = $helperBuildProductUrl->buildProductUrl($project->project_id, 'pling');
                }
                $projectArr = array(
                    'score'        => $hit->score,
                    'id'           => $project->project_id,
                    'type_id'      => $project->type_id,
                    'title'        => $project->title,
                    'description'  => $project->description,
                    'image'        => $project->image_small,
                    'plings'       => 0,
                    'urlGoal'      => $showUrl,
                    'urlPling'     => $plingUrl,
                    'showUrlPling' => ($project->paypal_mail != null),
                    'member'       => array(
                        'name'  => $project->username,
                        'url'   => 'member/' . $project->member_id,
                        'image' => $project->profile_image_url,
                        'id'    => $project->member_id
                    )
                );
                $viewArray[] = $projectArr;
            }
        }

        return $viewArray;
    }


    protected function setLayout()
    {
        $layoutName = 'flat_ui_template';
        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;      
        if($storeConfig  && $storeConfig->layout_pagedetail)
        {
             $this->_helper->layout()->setLayout($storeConfig->layout_pagedetail);
        }else{
            $this->_helper->layout()->setLayout($layoutName);
        }        
    }
    
    
    private function fetchGitlabProject($gitProjectId)
    {
        $gitlab = new Default_Model_Ocs_Gitlab(); 
        
        try {
            $gitProject = $gitlab->getProject($gitProjectId);
        } catch (Exception $exc) {
            //Project is gone
            $modelProject = new Default_Model_Project();
            $modelProject->updateProject($this->_projectId, array('is_gitlab_project' => 0, 'gitlab_project_id' => null, 'show_gitlab_project_issues' => 0, 'use_gitlab_project_readme' => 0));
            $gitProject = null;
        }
        return $gitProject;
    }
    
    private function fetchGitlabProjectIssues($gitProjectId)
    {
        $gitlab = new Default_Model_Ocs_Gitlab();
        
        try {
            $gitProjectIssues = $gitlab->getProjectIssues($gitProjectId);
        } catch (Exception $exc) {
            //Project is gone
            $modelProject = new Default_Model_Project();
            $modelProject->updateProject($this->_projectId, array('is_gitlab_project' => 0, 'gitlab_project_id' => null, 'show_gitlab_project_issues' => 0, 'use_gitlab_project_readme' => 0));

            $gitProjectIssues = null;
        }

        
        
        return $gitProjectIssues;
    }
    

    public function startvideoajaxAction() {
        $this->_helper->layout()->disableLayout();
        
        $collection_id = null;
        $file_id = null;
        $memberId = $this->_authMember->member_id;
        $media_view_type_id = $this->getParam('type_id');
        if(!$media_view_type_id)
        {
            // default
            $media_view_type_id = Default_Model_DbTable_MediaViews::MEDIA_TYPE_VIDEO;            
        }
        
        if($this->hasParam('collection_id') && $this->hasParam('file_id')) {
            $collection_id = $this->getParam('collection_id');
            $file_id = $this->getParam('file_id');
            $id = null;
            
            //Log media view
            try {
                $mediaviewsTable = new Default_Model_DbTable_MediaViews();
                $id = $mediaviewsTable->getNewId();
                $data = array('media_view_id' => $id, 'media_view_type_id' => $media_view_type_id, 'project_id' => $this->_projectId, 'collection_id' => $collection_id, 'file_id' => $file_id, 'start_timestamp' => new Zend_Db_Expr ('Now()'), 'ip' => $this->getRealIpAddr(), 'referer' => $this->getReferer());
                if(!empty($memberId)) {
                   $data['member_id'] = $memberId;
                }
                $data['source'] = 'OCS-Webserver';

                $mediaviewsTable->createRow($data)->save();

            } catch (Exception $exc) {
                //echo $exc->getTraceAsString();
                $errorLog = Zend_Registry::get('logger');
                $errorLog->err(__METHOD__ . ' - ' . $exc->getMessage() . ' ---------- ' . PHP_EOL);
            }
            
            
            $this->_helper->json(array('status' => 'success', 'MediaViewId' => $id));

            return;
        }

        $this->_helper->json(array('status' => 'error'));
    }
    
    public function stopvideoajaxAction() {
        $this->_helper->layout()->disableLayout();
        
        $view_id = null;
        
        if($this->hasParam('media_view_id')) {
            $view_id = $this->getParam('media_view_id');
            
            //Log media view stop
            try {
                $mediaviewsTable = new Default_Model_DbTable_MediaViews();
                $data = array('stop_timestamp' => new Zend_Db_Expr ('Now()'));
                $mediaviewsTable->update($data, 'media_view_id = '. $view_id);
            } catch (Exception $exc) {
                //echo $exc->getTraceAsString();
                $errorLog = Zend_Registry::get('logger');
                $errorLog->err(__METHOD__ . ' - ' . $exc->getMessage() . ' ---------- ' . PHP_EOL);
            }
            $this->_helper->json(array('status' => 'success', 'MediaViewId' => $view_id));

            return;
        }

        $this->_helper->json(array('status' => 'error'));
    }
    
    function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        {
          $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        {
          $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
          $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    function getReferer()
    {
        $referer = null;
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        }
        return $referer;
    }

}
