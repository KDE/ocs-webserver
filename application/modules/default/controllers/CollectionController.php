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
class CollectionController extends Local_Controller_Action_DomainSwitch
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
        if($action =='add')
        {
          $title = 'add collection';
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
    
    
    private function getCollectionProjects() {
        $project_id = $this->_projectId;
        
        $collectionProjectsTable = new Default_Model_DbTable_CollectionProjects();
        $projectsArray = $collectionProjectsTable->getCollectionProjects($project_id);
        $helperImage = new Default_View_Helper_Image();
        
        $result = array();
        foreach ($projectsArray as $project) {
            $imgUrl = $helperImage->Image($project['image_small'], array('width' => 140, 'height' => 98));
            $project['image_url'] = $imgUrl;
            $result[] = $project;
        }

        return $result;
        
    }
    
    
    
    public function getcollectionprojectsajaxAction() {
        $this->_helper->layout()->disableLayout();
        $project_id = $this->_projectId;
        
        $collectionProjectsTable = new Default_Model_DbTable_CollectionProjects();
        $projectsArray = $collectionProjectsTable->getCollectionProjects($project_id);
        $helperImage = new Default_View_Helper_Image();
        
        $result = array();
        foreach ($projectsArray as $project) {
            $imgUrl = $helperImage->Image($project['image_small'], array('width' => 140, 'height' => 98));
            $project['image_url'] = $imgUrl;
            $result[] = $project;
        }
        $this->_helper->json(array('status' => 'success', 'ResultSize' => count($result), 'projects' => $result));
        //$this->_helper->json($result);

        return;
        
    }
    
    
    public function getprojectsajaxAction() {
        $this->_helper->layout()->disableLayout();
        $member_id = null;
        $identity = Zend_Auth::getInstance()->getStorage()->read();
        if (Zend_Auth::getInstance()->hasIdentity()){
            $member_id = $identity->member_id;
        }
        
        if(!$member_id) {
            $this->_helper->json(array('status' => 'success', 'ResultSize' => 0, 'projects' => array()));
            
        } else {
            $search = null;
            if($this->hasParam('search')) {
                $search = $this->getParam('search');
            }
            $searchAll = false;
            if($this->hasParam('search_all')) {
                $searchAll = $this->getParam('search_all') == 'true';
            }
            if(empty($search)) {
                $this->_helper->json(array('status' => 'success', 'ResultSize' => 0, 'projects' => array()));
                return;
            }
            
            
            $collectionProjectsTable = new Default_Model_DbTable_CollectionProjects();
            if(!$searchAll) {
                $projectsArray = $collectionProjectsTable->getProjectsForMember($this->_projectId, $member_id, $search);
            } else {
                $projectsArray = $collectionProjectsTable->getProjectsForAllMembers($this->_projectId, $member_id, $search);
            }

            $result = array();
            $helperImage = new Default_View_Helper_Image();
            
            foreach ($projectsArray as $project) {
                $imgUrl = $helperImage->Image($project['image_small'], array('width' => 140, 'height' => 98));
                $project['image_url'] = $imgUrl;
                
                $result[] = $project;
            }
            $this->_helper->json(array('status' => 'success', 'ResultSize' => count($result), 'projects' => $result, 'Search' => $search, 'SearchAll' => $searchAll));
            //$this->_helper->json($result);
        }
        

        return;
        
    }
    
    public function searchprojectsajaxAction() {
        $this->_helper->layout()->disableLayout();
        $result = null;
        $search = "";
        if($this->hasParam('search')) {
            $search = $this->getParam('search');
        }
        
        $store->getParam('domain_store_id');
        
        $param = array('q' => $search ,'store'=>$store,'page' => 0, 'count' => 10, 'qf' => 'f', 'fq' => array());

        $modelSearch = new Default_Model_Solr();
        $searchResult = null;
        try {
            $searchResult = $modelSearch->search($param);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__FILE__.'('.__LINE__.') -- params: '.print_r($param, true)."\n".' message: '."\n".$e->getMessage());

            $searchResult = array('hits' => array(), 'highlighting' =>array(),'response' => array('numFound' => 0));
        }
        $pagination = $pagination = $modelSearch->getPagination();
        $products = $searchResult['hits'];
        
        
        $result = array();
        foreach ($products as $project) {
            $result[] = $project;
        }
        $this->_helper->json(array('status' => 'success', 'ResultSize' => count($result), 'projects' => $result));

        return;
        
    }




    public function initJsonForReact(){
            $modelProduct = new Default_Model_Collection();
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

            $tableProject = new Default_Model_Collection();
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

        if (empty($this->_projectId)) {
            $this->redirect('/explore');
        }

        $this->view->paramPageId = (int)$this->getParam('page');
        $this->view->member_id = null;
        if(null != $this->_authMember && null != $this->_authMember->member_id) {
            $this->view->member_id = $this->_authMember->member_id;
        }
        $modelProduct = new Default_Model_Collection();
        $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
        if (empty($productInfo)) {
            //Coukd be a Project
            $modelProduct = new Default_Model_Project();
            $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
            //Check if this is a collection
            if(!empty($productInfo) && $productInfo->type_id != $modelProduct::PROJECT_TYPE_COLLECTION) {
                $this->redirect('/p/'.$this->_projectId);
            }
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }
        
        $this->view->product = $productInfo;
        
        $this->view->collection_projects = $this->getCollectionProjects(); 

        $collection_ids = array();
        foreach ($this->view->collection_projects as $value) {
            if($value['ppload_collection_id']) $collection_ids[] = $value['ppload_collection_id'];
        }

        $filesmodel = new Default_Model_DbTable_PploadFiles();
        $this->view->collection_projects_dls = $filesmodel->fetchAllActiveFilesForCollection($collection_ids); 

        //$this->view->collection_ids = $collection_ids;

        $this->view->headTitle($productInfo->title . ' - ' . $this->getHeadTitle(), 'SET');
        
        $this->view->cat_id = $this->view->product->project_category_id;

        $helperUserIsOwner = new Default_View_Helper_UserIsOwner();
        $helperIsProjectActive = new Default_View_Helper_IsProjectActive();
        if ((false === $helperIsProjectActive->isProjectActive($this->view->product->project_status))
            AND (false === $helperUserIsOwner->UserIsOwner($this->view->product->member_id))
        ) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        if ((APPLICATION_ENV != 'searchbotenv') AND (false == SEARCHBOT_DETECTED)) {
            Default_Model_Views::saveViewCollection($this->_projectId);

            $tablePageViews = new Default_Model_DbTable_StatPageViews();
            $tablePageViews->savePageView($this->_projectId, $this->getRequest()->getClientIp(),
                $this->_authMember->member_id);
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
        $this->view->mode = 'addcollection';
        $this->view->collection_cat_id = Zend_Registry::get('config')->settings->client->default->collection_cat_id;
        
        $form = new Default_Form_Collection(array('member_id' => $this->view->member->member_id));
        $this->view->form = $form;
        
        if ($this->_request->isGet()) {
            return;
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
        $modelProject = new Default_Model_Collection();

        Zend_Registry::get('logger')->info(__METHOD__ . ' - $post: ' . print_r($_POST, true));
        Zend_Registry::get('logger')->info(__METHOD__ . ' _ input values: ' . print_r($values, true));

        $newProject = null;
        try {
            if (isset($values['project_id'])) {
                $newProject = $modelProject->updateCollection($values['project_id'], $values);
            } else {
                $newProject = $modelProject->createCollection($this->_authMember->member_id, $values, $this->_authMember->username);
                //$this->createSystemPlingForNewProject($newProject->project_id);
            }
        } catch (Exception $exc) {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - traceString: ' . $exc->getTraceAsString());
            Zend_Registry::get('logger')->info(__METHOD__ . ' _ Exception: ' . print_r($exc, true));
        }
        
        if (!$newProject) {
            $this->_helper->flashMessenger->addMessage('<p class="text-error">You did not choose a Category in the last level.</p>');
            $this->forward('add');

            return;
        }

        //update the gallery pics
        //$mediaServerUrls = $this->saveGalleryPics($form->gallery->upload->upload_picture);
        //$modelProject->updateGalleryPictures($newProject->project_id, $mediaServerUrls);

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
        /*
        if ($values['is_original']) {
            $modelTags->processTagProductOriginal($newProject->project_id, $values['is_original']);
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
         * 
         */

        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($newProject->project_id, $newProject->member_id, Default_Model_ActivityLog::PROJECT_CREATED, $newProject->toArray());

        //$modelTags->processTagProductOriginal($newProject->project_id);
        
        
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

        $this->redirect('/member/' . $newProject->member_id . '/collections/');
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



    public function editAction()
    {
        if (empty($this->_projectId)) {
            $this->redirect($this->_helper->url('add'));

            return;
        }

        $this->_helper->viewRenderer('add'); // we use the same view as you can see at add a product
        $this->view->mode = 'editcollection';
        $this->view->collection_cat_id = Zend_Registry::get('config')->settings->client->default->collection_cat_id;

        $projectTable = new Default_Model_DbTable_Project();
        $projectModel = new Default_Model_Collection();
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

        $this->view->project_id = $projectData->project_id;
        $this->view->product = $projectData;

        $this->view->member_id = $member->member_id;
        $this->view->member = $member;


        //read the already existing gallery pics and add them to the form
        $sources = $projectModel->getGalleryPictureSources($this->_projectId);
        
        //get the gitlab projects for this user

        //setup form
        $form = new Default_Form_Collection(array('pictures' => $sources, 'member_id' => $this->view->member_id));
        if (false === empty($projectData->image_small)) {
            $form->getElement('image_small_upload')->setRequired(false);
        }
        $form->getElement('preview')->setLabel('Save');

        $form->removeElement('project_id'); // we don't need this field in edit mode

        if ($this->_request->isGet()) {
            $form->populate($projectData->toArray());
           // $form->populate(array('tags' => $modelTags->getTags($projectData->project_id, Default_Model_Tags::TAG_TYPE_PROJECT)));
            //$form->populate(array('tagsuser' => $modelTags->getTagsUser($projectData->project_id, Default_Model_Tags::TAG_TYPE_PROJECT)));
            $form->getElement('image_small')->setValue($projectData->image_small);
            //Bilder voreinstellen
            $form->getElement(self::IMAGE_SMALL_UPLOAD)->setValue($projectData->image_small);
            
            /*
            $licenseTags = $tagTable->fetchLicenseTagsForProject($this->_projectId);
            $licenseTag = null;
            if($licenseTags) {
                $licenseTag = $licenseTags[0]['tag_id'];
            }
            $form->getElement('license_tag_id')->setValue($licenseTag);

            $is_original = $modelTags->isProuductOriginal($projectData->project_id);
            if($is_original){
                $form->getElement('is_original')->checked= true;                
            }
             * 
             */
 
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

        /**
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

        */

        $imageModel = new Default_Model_DbTable_Image();
        try {
            $uploadedSmallImage = $imageModel->saveImage($form->getElement(self::IMAGE_SMALL_UPLOAD));
            $values['image_small'] = $uploadedSmallImage ? $uploadedSmallImage : $values['image_small'];
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ERROR upload productPicture - ' . print_r($e, true));
        }

        // save changes
        $projectData->setFromArray($values);

        /**
        //update the gallery pics
        $pictureSources = array_merge($values['gallery']['online_picture'],
            $this->saveGalleryPics($form->gallery->upload->upload_picture));
        $projectModel->updateGalleryPictures($this->_projectId, $pictureSources);
        */
        
        //If there is no Logo, we take the 1. gallery pic
        if (!isset($projectData->image_small) || $projectData->image_small == '') {
            $projectData->image_small = $pictureSources[0];
        }
        //20180219 ronald: we set the changed_at only by new files or new updates
        //$projectData->changed_at = new Zend_Db_Expr('NOW()');
        $projectData->save();
        
        //$modelTags->processTagProductOriginal($this->_projectId,$values['is_original']);

        
        if($values['tagsuser']) {
            $modelTags->processTagsUser($this->_projectId,implode(',',$values['tagsuser']), Default_Model_Tags::TAG_TYPE_PROJECT);
        }else
        {
            $modelTags->processTagsUser($this->_projectId,null, Default_Model_Tags::TAG_TYPE_PROJECT);
        }
        
        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id, Default_Model_ActivityLog::PROJECT_EDITED, $projectData->toArray());

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
        $this->redirect($helperBuildMemberUrl->buildMemberUrl($member->username, 'collections'));
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
            $projectTable = new Default_Model_Collection();
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
        $tableProject = new Default_Model_Collection();
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
    
    public function updatecollectionprojectsajaxAction() {
        $this->_helper->layout()->disableLayout();

        $this->view->project_id = $this->_projectId;
        $this->view->authMember = $this->_authMember;
        
        
        //save collection products
        $projectIdsString = $this->getParam('collection_project_ids');
        $projectIds = array();
        
        if(!empty($projectIdsString)) {
            $projectIdsString = rtrim($projectIdsString,',');
            $projectIds = explode(',', $projectIdsString);
        }
        
        $modeCollection = new  Default_Model_DbTable_CollectionProjects();
        $modeCollection->setCollectionProjects($this->_projectId, $projectIds);
        
        $this->_helper->json(array(
            'status' => 'ok',
            'msg'   => 'Success.'
        ));
    }

    public function updateAction()
    {

        $this->_helper->layout()->setLayout('flat_ui');

        $this->view->headScript()->setFile('');
        $this->view->headLink()->setStylesheet('');

        $this->_helper->viewRenderer('add');

        $form = new Default_Form_ProjectUpdate();
        $projectTable = new Default_Model_Collection();
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
            $projectUpdateRow->status = Default_Model_Collection::PROJECT_ACTIVE;
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

        $tableProduct = new Default_Model_Collection();
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
            $projectTable = new Default_Model_Collection();
            $projectTable->setStatus(Default_Model_Collection::PROJECT_INACTIVE, $this->_projectId);

            //todo: maybe we have to delete the project data from database otherwise we produce many zombies
            $this->redirect('/member/' . $this->_authMember->member_id . '/collections/');
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

        $projectTable = new Default_Model_Collection();
        $projectTable->setStatus(Default_Model_Collection::PROJECT_ACTIVE, $this->_projectId);

        // add to search index
//        $modelProject = new Default_Model_Collection();
//        $productInfo = $modelProject->fetchProductInfo($this->_projectId);
//        $modelSearch = new Default_Model_Search_Lucene();
//        $modelSearch->addDocument($productInfo->toArray());

        $this->redirect('/member/' . $this->_authMember->member_id . '/products/');
    }

    protected function fetchDataForIndexView()
    {
        $tableProject = new Default_Model_Collection();
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

        $tableProject = new Default_Model_Collection();
        $this->view->supporting = $tableProject->fetchProjectSupporterWithPlings($this->_projectId);
    }



    public function payAction()
    {
        $this->_helper->layout()->disableLayout();
        $tableProject = new Default_Model_Collection();
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

        $tableProduct = new Default_Model_Collection();
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

        $tableProduct = new Default_Model_Collection();
        $tableProduct->setInActive($this->_projectId, $memberId);

        $product = $tableProduct->find($this->_projectId)->current();

        if (isset($product->type_id) && $product->type_id == Default_Model_Collection::PROJECT_TYPE_UPDATE) {
            $parentProduct = $tableProduct->find($product->pid)->current();
            $product->image_small = $parentProduct->image_small;
        }

        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id, Default_Model_ActivityLog::PROJECT_UNPUBLISHED,
            $product->toArray());

        // remove unpublished project from search index
        $modelSearch = new Default_Model_Search_Lucene();
        $modelSearch->deleteDocument($product);

        $this->forward('collections', 'user', 'default', array('member_id' => $memberId));
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

        $tableProduct = new Default_Model_Collection();
        $tableProduct->setActive($this->_authMember->member_id,$this->_projectId);

        $product = $tableProduct->find($this->_projectId)->current();

        if (isset($product->type_id) && $product->type_id == Default_Model_Collection::PROJECT_TYPE_UPDATE) {
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

        $this->forward('collections', 'user', 'default', array('member_id' => $memberId));
        //$this->redirect('/member/'.$memberId.'/products');
    }

   public function loadratingsAction()
   {
        $this->_helper->layout->disableLayout();
        $tableProjectRatings = new Default_Model_DbTable_ProjectRating();            
        $ratings = $tableProjectRatings->fetchRating($this->_projectId);
        $this->_helper->json($ratings);
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
            $tableProduct = new Default_Model_Collection();
            $product = $tableProduct->find($this->_projectId)->current();

            $activityLog = new Default_Model_ActivityLog();
            $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id,
                Default_Model_ActivityLog::PROJECT_FOLLOWED, $product->toArray());
        }

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


        $tableProduct = new Default_Model_Collection();
        $product = $tableProduct->find($this->_projectId)->current();

        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id,
            Default_Model_ActivityLog::PROJECT_UNFOLLOWED, $product->toArray());


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
            $tableProduct = new Default_Model_Collection();
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


        $tableProduct = new Default_Model_Collection();
        $product = $tableProduct->find($this->_projectId)->current();

        $activityLog = new Default_Model_ActivityLog();
        $activityLog->writeActivityLog($this->_projectId, $this->_authMember->member_id,
            Default_Model_ActivityLog::PROJECT_UNFOLLOWED, $product->toArray());

    }

    protected function logActivity($logId)
    {
        $tableProduct = new Default_Model_Collection();
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
            $tableProject = new Default_Model_Collection();
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
        $modelProduct = new Default_Model_Collection();
        $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
        if ($productInfo->claimable != Default_Model_Collection::PROJECT_CLAIMABLE) {
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
            $productModel = new Default_Model_Collection();
            $this->view->product = $productModel->fetchProductDataFromMV($widgetProjectId);
            $this->view->supporting = $productModel->fetchProjectSupporterWithPlings($widgetProjectId);
            $plingModel = new Default_Model_DbTable_Plings();
            $this->view->comments = $plingModel->getCommentsForProject($widgetProjectId, 10);
            $websiteOwner = new Local_Verification_WebsiteProject();
            $this->view->authCode = '<meta name="ocs-site-verification" content="'
                . $websiteOwner->generateAuthCode(stripslashes($this->view->product->link_1)) . '" />';
        }
    }


    public function saveproductAction()
    {
        $form = new Default_Form_Collection();

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
        $formValues['status'] = Default_Model_Collection::PROJECT_INCOMPLETE;

        $modelProject = new Default_Model_Collection();
        $newProject =
            $modelProject->createCollection($this->_authMember->member_id, $formValues, $this->_authMember->username);
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
                ), $this->getAllParams());



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

        $tableProduct = new Default_Model_Collection();
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
            $modelProject = new Default_Model_Collection();
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
            $modelProject = new Default_Model_Collection();
            $modelProject->updateProject($this->_projectId, array('is_gitlab_project' => 0, 'gitlab_project_id' => null, 'show_gitlab_project_issues' => 0, 'use_gitlab_project_readme' => 0));

            $gitProjectIssues = null;
        }

        
        
        return $gitProjectIssues;
    }

}
