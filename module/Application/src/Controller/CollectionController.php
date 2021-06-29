<?php /** @noinspection PhpUndefinedFieldInspection */
/** @noinspection PhpUnused */

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

namespace Application\Controller;

use Application\Form\Collection;
use Application\Model\Repository\ActivityLogRepository;
use Application\Model\Repository\CollectionProjectsRepository;
use Application\Model\Repository\CommentsRepository;
use Application\Model\Repository\ImageRepository;
use Application\Model\Repository\PlingsRepository;
use Application\Model\Repository\PploadFilesRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Repository\ProjectFollowerRepository;
use Application\Model\Repository\ProjectPlingsRepository;
use Application\Model\Repository\ProjectRatingRepository;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Repository\ProjectUpdatesRepository;
use Application\Model\Repository\ReportProductsRepository;
use Application\Model\Repository\StatPageViewsRepository;
use Application\Model\Repository\SuspicionLogRepository;
use Application\Model\Service\BbcodeService;
use Application\Model\Service\CollectionService;
use Application\Model\Service\HtmlPurifyService;
use Application\Model\Service\InfoService;
use Application\Model\Service\Interfaces\MemberServiceInterface;
use Application\Model\Service\ProjectPlingsService;
use Application\Model\Service\ProjectService;
use Application\Model\Service\ProjectUpdatesService;
use Application\Model\Service\SectionService;
use Application\Model\Service\SolrService;
use Application\Model\Service\SpamService;
use Application\Model\Service\TagConst;
use Application\Model\Service\TagService;
use Application\Model\Service\Util;
use Application\Model\Service\ViewsService;
use Application\View\Helper\BuildProductUrl;
use Application\View\Helper\CatTitle;
use Application\View\Helper\FetchHeaderData;
use Application\View\Helper\Image;
use Exception;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Http\PhpEnvironment\RemoteAddress;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Json\Encoder;
use Laminas\Validator\StringLength;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

/**
 * Class CollectionController
 *
 * @package Application\Controller
 */
class CollectionController extends DomainSwitch
{

    const IMAGE_SMALL_UPLOAD = 'image_small_upload';
    const IMAGE_BIG_UPLOAD = 'image_big_upload';

    /**
     * Zend_Controller_Request_Abstract object wrapping the request environment
     */
    protected $_request = null;

    /** @var  int */
    protected $_projectId;

    /** @var  int */
    protected $_collectionId;

    protected $_auth;

    /** @var  string */
    protected $_browserTitlePrepend;
    protected $isAdmin = false;
    protected $projectRepository;
    protected $projectCategoryRepository;
    protected $infoService;
    protected $tagService;
    protected $tagGroupService;
    protected $memberService;
    protected $projectService;
    protected $gitlab;
    protected $activityLog;
    protected $mailer;
    protected $collectionProjectsRepository;
    protected $searchService;
    protected $collectionService;
    protected $pploadRepository;
    protected $statPageViewsRepository;
    protected $imageRepository;
    private $sectionService;
    /**
     * @var array
     */
    private $configArray;

    public function __construct(
        AdapterInterface $db,
        array $config,
        Request $request,
        ProjectRepository $projectRepository,
        InfoService $infoService,
        TagService $tagService,
        MemberServiceInterface $memberService,
        ProjectService $projectService,
        ActivityLogRepository $activityLog,
        CollectionProjectsRepository $collectionProjectsRepository,
        SolrService $searchService,
        CollectionService $collectionService,
        PploadFilesRepository $pploadRepository,
        StatPageViewsRepository $statPageViewsRepository,
        ImageRepository $imageRepository,
        ProjectCategoryRepository $projectCategoryRepository,
        SectionService $sectionService
    ) {
        parent::__construct($db, $config, $request);
        parent::init();
        $this->projectRepository = $projectRepository;
        $this->infoService = $infoService;
        $this->tagService = $tagService;
        $this->memberService = $memberService;
        $this->projectService = $projectService;
        $this->activityLog = $activityLog;
        $this->collectionProjectsRepository = $collectionProjectsRepository;
        $this->searchService = $searchService;
        $this->collectionService = $collectionService;
        $this->pploadRepository = $pploadRepository;
        $this->statPageViewsRepository = $statPageViewsRepository;
        $this->imageRepository = $imageRepository;
        $this->projectCategoryRepository = $projectCategoryRepository;
        $this->sectionService = $sectionService;
        $this->_authMember = $this->view->getVariable('ocs_user');
        $this->configArray = $config;
        $this->view->setVariable('prodCatRepo', $this->projectCategoryRepository);
    }

    public function getcollectionprojectsajaxAction()
    {
        $this->initVars();
        $project_id = $this->_projectId;

        $collectionProjectsTable = $this->collectionProjectsRepository;
        $projectsArray = $collectionProjectsTable->getCollectionProjects($project_id);
        $helperImage = new Image();

        $result = array();
        foreach ($projectsArray as $project) {
            $imgUrl = $helperImage->Image($project['image_small'], array('width' => 140, 'height' => 98));
            $project['image_url'] = $imgUrl;
            $result[] = $project;
        }

        return new JsonModel(
            array('status' => 'success', 'ResultSize' => count($result), 'projects' => $result)
        );
    }

    public function initVars()
    {
        $this->_projectId = (int)$this->params('project_id', null);
        if (null == $this->_projectId) {
            $this->_projectId = (int)$this->params()->fromRoute('project_id', null);
        }

        $this->_collectionId = (int)$this->params('collection_id', null);
        $this->_browserTitlePrepend = $this->templateConfigData['head']['browser_title_prepend'];

        $action = $this->getEvent()->getRouteMatch()->getParam('action', 'index');
        $title = $action;
        if ($action == 'add') {
            $title = 'add product';
        } else {
            $title = $action;
        }
        $this->view->setVariable('headTitle', ($title . ' - ' . $this->getHeadTitle()));

        $userRoleName = $this->_authMember->roleName;
        $authUserMemberId = null;
        if (!empty($this->_authMember)) {
            $authUserMemberId = $this->_authMember->member_id;
        }
        $this->isAdmin = false;
        if ('admin' == $userRoleName) {
            $this->isAdmin = true;
        }
    }

    public function getprojectsajaxAction()
    {
        $this->initVars();
        $member_id = null;
        $identity = $this->_authMember;
        if ($this->_authMember->member_id) {
            $member_id = $identity->member_id;
        }

        if (!$member_id) {
            $resultJson = new JsonModel(array('status' => 'success', 'ResultSize' => 0, 'projects' => array()));
        } else {
            $search = $this->getParam('search');
            $searchAll = $this->getParam('search_all') == 'true';

            if (empty($search)) {
                $resultJson = new JsonModel(array('status' => 'success', 'ResultSize' => 0, 'projects' => array()));

                return $resultJson;
            }


            $collectionProjectsTable = $this->collectionProjectsRepository;
            if (!$searchAll) {
                $projectsArray = $collectionProjectsTable->getProjectsForMember($this->_projectId, $member_id, $search);
            } else {
                $projectsArray = $collectionProjectsTable->getProjectsForAllMembers(
                    $this->_projectId, $member_id, $search
                );
            }

            $result = array();
            $helperImage = new Image();

            foreach ($projectsArray as $project) {
                $imgUrl = $helperImage->Image($project['image_small'], array('width' => 140, 'height' => 98));
                $project['image_url'] = $imgUrl;

                $result[] = $project;
            }
            $resultJson = new JsonModel(
                array(
                    'status'     => 'success',
                    'ResultSize' => count($result),
                    'projects'   => $result,
                    'Search'     => $search,
                    'SearchAll'  => $searchAll,
                )
            );
            //$resultJson = new JsonModel($result);
        }


        return $resultJson;
    }

    public function indexAction()
    {
        $this->initVars();
        $this->setLayout();

        if (empty($this->_projectId)) {
            $this->redirect()->toUrl('/explore');
        }

        $this->view->setVariable('paramPageId', (int)$this->getParam('page'));
        $this->view->setVariable('isAdmin', $this->isAdmin);
        $this->view->setVariable('ocs_user', $this->_authMember);

        $isModerator = false;
        if ('moderator' == $this->_authMember->roleName) {
            $isModerator = true;
        }
        $this->view->setVariable('isModerator', $isModerator);


        $this->view->setVariable('paramPageId', (int)$this->params('page', null));
        $this->view->setVariable('member_id', null);
        if (null != $this->_authMember && null != $this->_authMember->member_id) {
            $this->view->setVariable('member_id', $this->_authMember->member_id);
        }

        $modelProduct = $this->collectionService;
        $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
        $productInfoArray = $productInfo;

        if (empty($productInfo)) {
            //Could be a Project
            $modelProduct = $this->projectRepository;
            $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
            $productInfoArray = $productInfo;

            //Check if this is a collection
            if (!empty($productInfo) && $productInfo['type_id'] == $modelProduct::PROJECT_TYPE_STANDARD) {
                $this->redirect()->toUrl('/p/' . $this->_projectId);
            }
        }

        if (empty($productInfo)) {
//            throw new Exception('This page does not exist', 404);
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        } else {
            $productInfo = Util::arrayToObject($productInfo);
        }

        $headerData = $this->loadHeaderDataWithCatid($productInfo->project_category_id);        
        $this->layout()->setVariable('headerData',  $headerData);

        $this->view->setVariable('product', $productInfo);

        $this->view->setVariable('collection_projects', $this->getCollectionProjects());

        $collection_ids = array();
        foreach ($this->getCollectionProjects() as $value) {
            if ($value['ppload_collection_id']) {
                $collection_ids[] = $value['ppload_collection_id'];
            }
        }

        $filesmodel = $this->pploadRepository;
        $this->view->setVariable(
            'collection_projects_dls', $filesmodel->fetchAllActiveFilesForCollection($collection_ids)
        );

        $updatesTable = new ProjectUpdatesService($this->db);
        $updates = $updatesTable->fetchProjectUpdates($productInfo->project_id);
        $this->view->setVariable('updates', $updates);

        $tableProjectRatings = new ProjectRatingRepository($this->db);
        $this->view->setVariable('ratings', $tableProjectRatings->fetchRating($productInfo->project_id));
        $scoreOld = $tableProjectRatings->getScoreOld($productInfo->project_id);
        $this->view->setVariable('scoreOld', $scoreOld);

        $score2 = $tableProjectRatings->getScore($productInfo->project_id);
        $this->view->setVariable('score2', $score2);

        $tableProject = $this->collectionService;

        $moreProducts = $tableProject->fetchMoreCollections($productInfo, 6);
        $this->view->setVariable('moreProductsOfUser', $moreProducts);

        $moreProductsOfOtherUsers = $tableProject->fetchMoreCollectionsOfOtherUsr($productInfo, 6);
        $this->view->setVariable('moreProductsOfOtherUsers', $moreProductsOfOtherUsers);


        $this->view->setVariable('projectCategoryRepository', $this->projectCategoryRepository);

        if ($this->_authMember->member_id) {
            $this->view->setVariable(
                'ratingOfUser', $tableProjectRatings->getProjectRateForUser(
                $productInfo->project_id, $this->_authMember->member_id
            )
            );
            $this->view->setVariable('userRoleName', $this->_authMember->roleName);
        }

        $tableProjectFollower = new ProjectFollowerRepository($this->db);
        $this->view->setVariable('likes', $tableProjectFollower->fetchLikesForProject($productInfo->project_id));

        $projectplings = new ProjectPlingsService($this->db);
        $this->view->setVariable('projectplings', $projectplings->fetchPlingsForProject($productInfo->project_id));

        $projectplingsTable = new ProjectPlingsRepository($this->db);
        $cntplings = $projectplingsTable->getPlingsAmount($productInfo->project_id);
        $this->view->setVariable('cntplings', $cntplings);

        $this->view->setVariable(
            'productJson', Encoder::encode(CollectionService::cleanProductInfoForJson($productInfoArray))
        );

        $commentModel = new CommentsRepository($this->db);
        $commentsTree = $commentModel->getCommentTreeForProject($productInfo->project_id);
        $commentsTree->setItemCountPerPage(25);
        $commentsTree->setCurrentPageNumber($this->view->getVariable('paramPageId'));
        $this->view->setVariable('commentsTree', $commentsTree);

        $tmp = $projectplingsTable->getPling($productInfo->project_id, $this->_authMember->member_id);
        $isPlinged = false;
        if ($tmp) {
            $isPlinged = true;
        }
        $this->view->setVariable('isPlinged', $isPlinged);

        //$this->view->collection_ids = $collection_ids;

        $this->view->setVariable('headTitle', $productInfo->title . ' - ' . $this->getHeadTitle());

        $this->view->setVariable('cat_id', $productInfo->project_category_id);

        if (!$this->isAdmin and ($this->view->getVariable(
                    'product'
                )->project_status != ProjectService::PROJECT_ACTIVE) and ($this->view->getVariable(
                    'product'
                )->member_id != $this->_authMember->member_id)) {
            //throw new Exception('This page does not exist', 404);
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }

        if ((APPLICATION_ENV != 'searchbotenv') and (false == SEARCHBOT_DETECTED)) {

            ViewsService::saveViewCollection($this->_projectId);

            if (DomainSwitch::get_ip_address()) {
                $tablePageViews = new StatPageViewsRepository($this->db);
                $tablePageViews->savePageView(
                    $this->_projectId, DomainSwitch::get_ip_address(), $this->_authMember->member_id
                );
            }
        }

        $storeConfig = $GLOBALS['ocs_store']->config;
        if ($storeConfig->layout_pagedetail && $storeConfig->isRenderReact()) {
            $this->initJsonForReact();
            $this->view->setTemplate('index-react');
        }

        return $this->view;
    }

    /*
     * Deprecated
     */

    private function getCollectionProjects()
    {
        $project_id = $this->_projectId;

        $collectionProjectsTable = $this->collectionProjectsRepository;
        $projectsArray = $collectionProjectsTable->getCollectionProjects($project_id);
        $helperImage = new Image();

        $result = array();
        foreach ($projectsArray as $project) {
            $imgUrl = $helperImage->Image($project['image_small'], array('width' => 140, 'height' => 98));
            $project['image_url'] = $imgUrl;
            $result[] = $project;
        }

        //$resultJson = new JsonModel($result);
        return $result;
    }

   
    /**
     * @return void | ViewModel
     * @throws Exception
     */
    public function addAction()
    {
        $this->initVars();
        $this->setLayout();
        $headerData = $this->loadHeaderDataWithCatid();        
        $this->layout()->setVariable('headerData',  $headerData);
        $this->view->setVariable('headTitle', ('Add - ' . $this->getHeadTitle()));

        $this->view->setVariable('member', $this->_authMember);
        $this->view->setVariable('mode', 'addcollection');
        $this->view->setVariable('collection_cat_id', $this->config->settings->client->default->collection_cat_id);

        $form = new Collection(
            $this->imageRepository, $this->projectCategoryRepository, array('member_id' => $this->view->member_id)
        );
        $this->view->setVariable('form', $form);

        if ($this->_request->isGet()) {
            return $this->view;
        }

        if (isset($_POST['cancel'])) { // user cancel function
            $this->redirect()->toUrl('/member/' . $this->_authMember->member_id . '/news/');
        }

        $data = array_merge_recursive($this->params()->fromPost(), $this->params()->fromFiles());
        $form->setData($data);

        if (false === $form->isValid()) { // form not valid
            $this->view->setVariable('form', $form);
            $this->view->setVariable('error', 1);

            return $this->view;
        }

        $values = $form->getData();

        if (isset($values['image_small_upload'])) {
            $imageModel = $this->imageRepository;
            try {
                $uploadedSmallImage = $imageModel->saveImage($values[self::IMAGE_SMALL_UPLOAD]);
                $values['image_small'] = $uploadedSmallImage ? $uploadedSmallImage : $values['image_small'];
            } catch (Exception $e) {
                $GLOBALS['ocs_log']->err(__METHOD__ . ' - ERROR upload productPicture - ' . print_r($e, true));
            }
        }

        // form was valid, so we can set status to active
        $values['status'] = ProjectService::PROJECT_ACTIVE;

        // save new project
        $modelProject = $this->collectionService;

        $GLOBALS['ocs_log']->info(__METHOD__ . ' - $post: ' . print_r($_POST, true));
        $GLOBALS['ocs_log']->info(__METHOD__ . ' _ input values: ' . print_r($values, true));


        $updateValues = $values;
        //remove not needed elements
        unset($updateValues['tagsuser']);
        unset($updateValues['image_small_upload']);
        unset($updateValues['cancel']);
        unset($updateValues['preview']);
        $collection_cat_id = $this->config->settings->client->default->collection_cat_id;
        $updateValues['project_category_id'] = $collection_cat_id;

        $newProject = null;
        try {
            if (isset($updateValues['project_id'])) {
                $newProject = $modelProject->updateCollection($values['project_id'], $updateValues);
            } else {
                $newProject = $modelProject->createCollection(
                    $this->_authMember->member_id, $updateValues, $this->_authMember->username
                );
            }
        } catch (Exception $exc) {
            $GLOBALS['ocs_log']->warn(__METHOD__ . ' - traceString: ' . $exc->getTraceAsString());
        }

        if (!$newProject) {
            return $this->view;
        }

        //New Project in Session, for AuthValidation (owner)
        $this->_authMember->projects[$newProject->project_id] = array('project_id' => $newProject->project_id);
        $GLOBALS['ocs_user'] = $this->_authMember;


        $modelTags = $this->tagService;
        if ($values['tagsuser']) {
            $modelTags->processTagsUser(
                $newProject->project_id, implode(',', $values['tagsuser']), TagConst::TAG_TYPE_PROJECT
            );
        } else {
            $modelTags->processTagsUser($newProject->project_id, null, TagConst::TAG_TYPE_PROJECT);
        }


        $activityLog = $this->activityLog;
        $activityLog->writeActivityLog(
            $newProject->project_id, $newProject->member_id, $activityLog::PROJECT_CREATED, $newProject->getArrayCopy()
        );


        try {
            if (100 < $this->_authMember->roleId) {
                $spamService = new SpamService($this->db);
                if ($spamService->hasSpamMarkers($newProject->getArrayCopy())) {
                    $tableReportComments = new ReportProductsRepository($this->db);
                    $tableReportComments->insert(
                        array(
                            'project_id'  => $newProject->project_id,
                            'reported_by' => 24,
                            'text'        => "System: automatic spam detection",
                        )
                    );
                }
                SuspicionLogRepository::logProject($newProject, $this->_authMember, $this->getRequest());
            }
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err($e->getMessage());
        }

        $this->redirect()->toUrl('/member/' . $newProject->member_id . '/collections/');
    }

    /**
     * @return void|ViewModel
     * @throws Exception
     */
    public function editAction()
    {
        $this->initVars();
        $this->setLayout();

        $this->view->setVariable('headTitle', ('Edit - ' . $this->getHeadTitle()));

        if (empty($this->_projectId)) {
            $this->redirect()->toUrl('/collection/add');

            return;
        }

        $this->view->setTemplate('application/collection/add'); // we use the same view as you can see at add a product
        $this->view->setVariable('mode', 'editcollection');
        $this->view->setVariable('collection_cat_id', $this->config->settings->client->default->collection_cat_id);

        $projectTable = $this->projectRepository;
        $modelTags = $this->tagService;

        //check if product with given id exists
        $projectData = $projectTable->findById($this->_projectId);
        
        if (empty($projectData)) {
            $this->redirect()->toUrl('/collection/add');

            return;
        }
        $headerData = $this->loadHeaderDataWithCatid($projectData->project_category_id);        
        $this->layout()->setVariable('headerData',  $headerData);

        $headerData = $this->loadHeaderDataWithCatid($projectData->project_category_id);        
        $this->layout()->setVariable('headerData',  $headerData);

        $member = null;
        if (isset($this->_authMember) and (false === empty($this->_authMember->member_id))) {
            $member = $this->_authMember;
        } else {
            throw new Exception('no authorization found');
        }

        $isAdmin = false;

        if (("admin" == $this->_authMember->roleName)) {
            $modelMember = $this->memberService;
            $member = $modelMember->fetchMemberData($projectData->member_id, false);
            $isAdmin = true;
        }
        $this->view->setVariable('isAdmin', $isAdmin);

        $this->view->setVariable('project_id', $projectData->project_id);
        $this->view->setVariable('product', $projectData);

        $this->view->setVariable('member_id', $member->member_id);
        $this->view->setVariable('member', $member);

        $form = new Collection(
            $this->imageRepository, $this->projectCategoryRepository, array('member_id' => $this->view->member_id)
        );


        //setup form
        if (false === empty($projectData->image_small) && $form->has('image_small_upload')) {
            $form->get('image_small_upload')->setAttribute('required', false);
        }
        $form->get('preview')->setLabel('Save');

        //$form->removeElement('project_id'); // we don't need this field in edit mode

        if ($this->_request->isGet()) {
            $form->populateValues($projectData->getArrayCopy());
            $form->populateValues(
                array(
                    'tagsuser' => $modelTags->getTagsUser($projectData->project_id, TagConst::TAG_TYPE_PROJECT),
                )
            );
            $form->get('image_small')->setValue($projectData->image_small);
            //Bilder voreinstellen
            $form->get(self::IMAGE_SMALL_UPLOAD)->setValue($projectData->image_small);

            $this->view->setVariable('form', $form);

            return $this->view;
        }

        if (isset($_POST['cancel'])) { // user cancel function
            $this->redirect()->toUrl('/member/' . $member->member_id . '/news/');
        }

        $data = array_merge_recursive($this->params()->fromPost(), $this->params()->fromFiles());

        $form->setData($data);
        if (false === $form->isValid()) { // form not valid
            $this->view->setVariable('form', $form);
            $this->view->setVariable('error', 1);

            return $this->view;
        }

        $values = $form->getData();

        if (isset($values['image_small_upload'])) {
            $imageModel = new ImageRepository($this->db, $this->configArray);
            try {
                $uploadedSmallImage = $imageModel->saveImage($values[self::IMAGE_SMALL_UPLOAD]);
                $values['image_small'] = $uploadedSmallImage ? $uploadedSmallImage : $values['image_small'];
            } catch (Exception $e) {
                $GLOBALS['ocs_log']->err(__METHOD__ . ' - ERROR upload productPicture - ' . print_r($e, true));
            }
        }

        $updateValues = $values;

        //remove not needed elements
        unset($updateValues['tagsuser']);
        unset($updateValues['preview']);
        unset($updateValues['image_small_upload']);
        unset($updateValues['cancel']);

        $updateValues['project_id'] = $this->_projectId;


        // save changes
        $this->projectService->updateProject($this->_projectId, $updateValues);


        if ($values['tagsuser']) {
            $modelTags->processTagsUser(
                $this->_projectId, implode(',', $values['tagsuser']), TagConst::TAG_TYPE_PROJECT
            );
        } else {
            $modelTags->processTagsUser($this->_projectId, null, TagConst::TAG_TYPE_PROJECT);
        }

        $activityLog = $this->activityLog;
        $activityLog->writeActivityLog(
            $this->_projectId, $this->_authMember->member_id, $activityLog::PROJECT_EDITED, $projectData->getArrayCopy()
        );

        try {
            if (100 < $this->_authMember->roleId) {
                $spam = new SpamService($this->db);
                if ($spam->hasSpamMarkers($projectData->getArrayCopy())) {
                    $tableReportComments = new ReportProductsRepository($this->db);
                    $tableReportComments->insertOrUpdate(
                        array(
                            'project_id'  => $projectData->project_id,
                            'reported_by' => 24,
                            'text'        => "System: automatic spam detection on product edit",
                        )
                    );
                }
                SuspicionLogRepository::logProject($projectData, $this->_authMember, $this->getRequest());
            }
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err($e->getMessage());
        }

        $this->redirect()->toUrl('/member/' . $projectData->member_id . '/collections/');
    }

    private function loadHeaderDataWithCatid($catId=null)
    {  
       
        $fetchHeaderData = new FetchHeaderData($this->sectionService, $this->infoService);      
        $headerData = $fetchHeaderData($catId);                
        $headerData['serverUri'] = $_SERVER["REQUEST_URI"];    
        if($catId){
            $helperFetchCategory = new CatTitle($this->projectCategoryRepository);
            $catTitle = $helperFetchCategory->catTitle($catId); 
            $headerData['catTitle'] = $catTitle;
        }
        return $headerData;
    }
    
    public function getupdatesajaxAction()
    {
        $this->initVars();

        $this->view->setVariable('authMember', $this->_authMember);
        $tableProject = new ProjectUpdatesService($this->db);

        $updates = $tableProject->fetchProjectUpdates($this->_projectId);

        foreach ($updates as $key => $update) {
            $updates[$key]['title'] = HtmlPurifyService::purify($update['title']);
            $updates[$key]['text'] = BbcodeService::renderHtml(
                HtmlPurifyService::purify(htmlentities($update['text'], ENT_QUOTES | ENT_IGNORE))
            );
            $updates[$key]['raw_title'] = $update['title'];
            $updates[$key]['raw_text'] = $update['text'];
        }

        $result['status'] = 'success';
        $result['ResultSize'] = count($updates);
        $result['updates'] = $updates;

        return new JsonModel($result);
    }

    public function saveupdateajaxAction()
    {
        $this->initVars();

        $title = $this->getParam('title');
        $text = $this->getParam('text');
        $update_id = $this->getParam('update_id');

        $msg = array();

        $validatorTitle = new StringLength(array('min' => 3, 'max' => 200));
        if (!$validatorTitle->isValid($title)) {
            $msg[] = "Please add a title with min 3 chars and max 200 chars.";
        }
        $validatorText = new StringLength(array('min' => 3, 'max' => 16383));
        if (!$validatorText->isValid($text)) {
            $msg[] = "Please add a text with min 3 chars and max 16383 chars.";
        }

        if (count($msg) > 0) {
            $result['status'] = 'error';
            $result['messages'] = $msg;
            $result['update_id'] = null;

            return new JsonModel($result);
        }

        $tableProjectUpdates = new ProjectUpdatesRepository($this->db);

        //Save update
        if (!empty($update_id)) {
            //Update old update
            $updateArray = array();
            $updateArray['title'] = $title;
            $updateArray['text'] = $text;
            $updateArray['changed_at'] = new Expression('Now()');
            $countUpdated = $tableProjectUpdates->update($updateArray, 'project_update_id = ' . $update_id);
        } else {
            //Add new update
            $updateArray = array();
            $updateArray['title'] = $title;
            $updateArray['text'] = $text;
            $updateArray['public'] = 1;
            $updateArray['project_id'] = $this->_projectId;
            $updateArray['member_id'] = $this->_authMember->member_id;
            $updateArray['created_at'] = new Expression('Now()');
            $updateArray['changed_at'] = new Expression('Now()');
            $update_id = $tableProjectUpdates->insertOrUpdate($updateArray);

            //20180219 ronald: we set the changed_at only by new files or new updates
            $projectTable = $this->projectRepository;
            $projectUpdateRow = $projectTable->findById($this->_projectId);
            if ($projectUpdateRow) {
                $projectUpdateRow->changed_at = new Expression('NOW()');
                $projectTable->update($projectUpdateRow->getArrayCopy());
            }
        }

        $result['status'] = 'success';
        $result['update_id'] = $update_id;

        return new JsonModel($result);
    }

    public function deleteupdateajaxAction()
    {
        $this->view->authMember = $this->_authMember;
        $tableProject = new ProjectUpdatesRepository($this->db);

        $project_update_id = $this->getParam('update_id');
        $updateArray = array();
        $updateArray['public'] = 0;
        $updateArray['changed_at'] = new Expression('Now()');
        $tableProject->update($updateArray, 'project_update_id = ' . $project_update_id);

        $result['status'] = 'success';
        $result['update_id'] = $project_update_id;

        return new JsonModel($result);
    }

    public function updatesAction()
    {
        $this->initVars();

        $this->view->setVariable('authMember', $this->_authMember);
        $tableProject = $this->projectRepository;
        $serviceProject = $this->projectService;
        $serviceProjectUpdates = $this->getEvent()->getApplication()->getServiceManager()->get(
            ProjectUpdatesService::class
        );
        $this->view->setVariable('product', $tableProject->fetchProductInfo($this->_projectId));
        if (false === isset($this->view->product)) {
            //throw new \Exception('This page does not exist', 404);
            $this->getResponse()->setStatusCode(404);

            return;
        }
        $this->view->setVariable('relatedProducts', $tableProject->fetchSimilarProjects($this->view->product, 6));
        $this->view->setVariable('supporter', $serviceProject->fetchProjectSupporter($this->_projectId));
        $this->view->setVariable('product_views', $tableProject->fetchProjectViews($this->_projectId));

        $modelPlings = new PlingsRepository($this->db);
        $this->view->setVariable('comments', $modelPlings->getCommentsForProject($this->_projectId, 10));

        $tableMember = $this->memberService;
        $this->view->setVariable('member', $tableMember->fetchMemberData($this->view->product->member_id));

        $this->view->setVariable('updates', $serviceProjectUpdates->fetchProjectUpdates($this->_projectId));

        $tablePageViews = new StatPageViewsRepository($this->db);
        $remote = new RemoteAddress;
        $tablePageViews->savePageView(
            $this->_projectId, $remote->setUseProxy(true)->getIpAddress(), $this->_authMember->member_id
        );
    }

    public function updatecollectionprojectsajaxAction()
    {
        $this->initVars();

        $this->view->setVariable('project_id', $this->_projectId);
        $this->view->setVariable('authMember', $this->_authMember);
        
        //save collection products
        $projectIdsString = $this->getParam('collection_project_ids');
    
        $projectIds = array();

        if (!empty($projectIdsString)) {
            $projectIdsString = rtrim($projectIdsString, ',');
            $projectIds = explode(',', $projectIdsString);
            if(count($projectIds)>100)
            {
                $projectIds = array_slice($projectIds, 0, 100, true);
            }            
            $modeCollection = $this->collectionProjectsRepository;
            $modeCollection->setCollectionProjects($this->_projectId, $projectIds);
        }

        return new JsonModel(
            array(
                'status' => 'ok',
                'msg'    => 'Success.',
            )
        );
    }

    public function deleteAction()
    {
        $this->setLayout();
        $this->initVars();

        $memberId = (int)$this->getParam('m');

        if ((empty($this->_authMember->member_id)) or (empty($memberId)) or ($this->_authMember->member_id != $memberId)) {
            $this->redirect()->toUrl('member/' . $this->_authMember->member_id . '/collections');

            return;
        }

        $tableProduct = $this->collectionService;
        $tableProduct->setDeleted($this->_authMember->member_id, $this->_projectId);

        $product = $this->collectionProjectsRepository->findById($this->_projectId);
        $headerData = $this->loadHeaderDataWithCatid($product->project_category_id);        
        $this->layout()->setVariable('headerData',  $headerData);

        $activityLog = $this->activityLog;
        $activityLog->writeActivityLog(
            $this->_projectId, $this->_authMember->member_id, $activityLog::PROJECT_DELETED, $product->getArrayCopy()
        );

        $this->redirect()->toUrl('member/' . $this->_authMember->member_id . '/collections');
    }

    /**
     * @return void|Response
     * @throws Exception
     */
    public function unpublishAction()
    {
        $this->setLayout();
        $this->initVars();

        $memberId = (int)$this->getParam('m');

        if ((empty($this->_authMember->member_id)) or (empty($memberId)) or ($this->_authMember->member_id != $memberId)) {
            return;
        }

        $tableProduct = $this->collectionService;
        $tableProduct->setInActive($this->_projectId, $memberId);

        $product = $this->projectRepository->findById($this->_projectId);

        if (isset($product->type_id) && $product->type_id == CollectionService::PROJECT_TYPE_UPDATE) {
            $parentProduct = $this->projectRepository->findById($product->pid);
            $product->image_small = $parentProduct->image_small;
        }

        $activityLog = $this->activityLog;
        $activityLog->writeActivityLog(
            $this->_projectId, $this->_authMember->member_id, $activityLog::PROJECT_UNPUBLISHED,
            $product->getArrayCopy()
        );


        return $this->redirect()->toRoute(
            'application_user', [
                                  'action'   => 'collections',
                                  'username' => $this->_authMember->username,
                              ]
        );
    }

    /**
     * @return void|Response
     * @throws Exception
     */
    public function publishAction()
    {
        $this->initVars();
        $memberId = (int)$this->getParam('m');

        if ((empty($this->_authMember->member_id)) or (empty($memberId)) or ($this->_authMember->member_id != $memberId)) {
            return;
        }

        $tableProduct = $this->collectionService;
        $tableProduct->setActive($this->_authMember->member_id, $this->_projectId);

        $product = $this->projectRepository->findById($this->_projectId);

        if (isset($product->type_id) && $product->type_id == CollectionService::PROJECT_TYPE_UPDATE) {
            $parentProduct = $this->projectRepository->findById($product->pid);
            $product->image_small = $parentProduct->image_small;
        }

        $activityLog = $this->activityLog;
        $activityLog->writeActivityLog(
            $this->_projectId, $this->_authMember->member_id, $activityLog::PROJECT_PUBLISHED, $product->getArrayCopy()
        );

        return $this->redirect()->toRoute(
            'application_user', [
                                  'action'   => 'collections',
                                  'username' => $this->_authMember->username,
                              ]
        );
    }

    public function loadratingsAction()
    {
        $this->initVars();

        $tableProjectRatings = new ProjectRatingRepository($this->db);
        $ratings = $tableProjectRatings->fetchRating($this->_projectId);

        return new JsonModel($ratings);
    }

    public function loadinstallinstructionAction()
    {
        $infomodel = $this->infoService;
        $text = $infomodel->getOCSInstallInstruction();


        return new JsonModel(
            array(
                'status' => 'ok',
                'data'   => $text,
            )
        );
    }

    public function followprojectAction()
    {
        $this->view->setTerminal(true);

        $this->initVars();

        $this->view->project_id = $this->_projectId;
        $this->view->authMember = $this->_authMember;

        // not allow to pling himself
        if (array_key_exists($this->_projectId, $this->_authMember->projects)) {
            return new JsonModel(
                array(
                    'status' => 'error',
                    'msg'    => 'not allowed',
                )
            );
        }


        $projectFollowTable = new ProjectFollowerRepository($this->db);

        $newVals = array('project_id' => $this->_projectId, 'member_id' => $this->_authMember->member_id);

        $sql = sprintf(
            "SELECT * FROM %s WHERE `member_id` = %s AND `project_id` = %d", $projectFollowTable->getName(),
            $this->_authMember->member_id, $this->_projectId
        );
        $result = $projectFollowTable->fetchRow($sql);

        if (null === $result) {
            $projectFollowTable->insert($newVals);
            $this->logActivity(ActivityLogRepository::PROJECT_FOLLOWED);
            $cnt = $projectFollowTable->countForProject($this->_projectId);

            return new JsonModel(
                array(
                    'status' => 'ok',
                    'msg'    => 'Success.',
                    'cnt'    => $cnt,
                    'action' => 'insert',
                )
            );
        } else {
            $projectFollowTable->deleteReal($result[$projectFollowTable->getKey()]);
            $this->logActivity(ActivityLogRepository::PROJECT_UNFOLLOWED);
            $cnt = $projectFollowTable->countForProject($this->_projectId);

            return new JsonModel(
                array(
                    'status' => 'ok',
                    'msg'    => 'Success.',
                    'cnt'    => $cnt,
                    'action' => 'delete',
                )
            );
        }

    }

    protected function logActivity($logId)
    {
        $tableProduct = $this->projectRepository;
        $product = $tableProduct->findById($this->_projectId);
        $activityLog = $this->activityLog;
        $activityLog->writeActivityLog(
            $this->_projectId, $this->_authMember->member_id, $logId, $product->getArrayCopy()
        );
    }

    public function followsAction()
    {
        $projectFollowTable = $this->memberService;

        $memberId = $this->_authMember->member_id;
        $this->view->productList = $projectFollowTable->fetchFollowedProjects($memberId);

        $projectArray = $this->generateFollowedProjectsViewData($this->view->productList);

        $this->view->setVariable('productArray', array('followedProjects' => $projectArray));

        return $this->view;
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

        $helperBuildProductUrl = new BuildProductUrl();
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

    public function saveproductAction()
    {
        $this->initVars();


        $form = new Collection(
            $this->imageRepository, $this->projectCategoryRepository, array('member_id' => $this->view->member_id)
        );

        // we don't need to test a file which doesn't exist in this case. The Framework stumbles if $_FILES is empty.
        if ($this->_request->isXmlHttpRequest() and (count($_FILES) == 0)) {
            $form->remove('image_small_upload');
            //            $form->removeElement('image_big_upload');
            $form->remove('project_id'); //(workaround: Some Browsers send "0" in some cases.)
        }

        $data = array_merge_recursive($this->params()->fromPost(), $this->params()->fromFiles());

        $form->setData($data);

        if (false === $form->isValid()) {
            $errors = $form->getMessages();
            $messages = $this->getErrorMessages($errors);

            return new JsonModel(array('status' => 'error', 'messages' => $messages));
        }

        $formValues = $form->getData();
        $formValues['status'] = CollectionService::PROJECT_INCOMPLETE;
        $collection_cat_id = $this->config->settings->client->default->collection_cat_id;
        $formValues['project_category_id'] = $collection_cat_id;
        $formValues['type_id'] = 3;

        //remove not needed elements
        unset($formValues['tagsuser']);
        unset($formValues['preview']);
        unset($formValues['image_small_upload']);
        unset($formValues['cancel']);
        unset($formValues['project_id']);

        $modelProject = $this->collectionService;
        $newProject = $modelProject->createCollection(
            $this->_authMember->member_id, $formValues, $this->_authMember->username
        );
        //New Project in Session, for AuthValidation (owner)
        $this->_authMember->projects[$newProject->project_id] = array('project_id' => $newProject->project_id);

        return new JsonModel(array('status' => 'ok', 'project_id' => $newProject->project_id));
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

    protected function _initResponseHeader()
    {
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";

        $this->getResponse()->setMetadata(
            'X-FRAME-OPTIONS', 'ALLOWALL', true
        )//            ->setHeader('Last-Modified', $modifiedTime, true)
             ->setMetadata('Expires', $expires, true)->setMetadata('Pragma', 'no-cache', true)->setMetadata(
            'Cache-Control', 'private, no-cache, must-revalidate', true
        );
    }

    /**
     * transforms a string with bbcode markup into html
     *
     * @param string $txt
     * @param bool   $nl2br
     * @param string $forcecolor
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
            '`\[indent](.+?)\[/indent\]`is',
        );

        $replaces = array(
            '<strong' . $fc . '>\\1</strong>',
            '<em' . $fc . '>\\1</em>',
            '<span style="border-bottom: 1px dotted">\\1</span>',
            '<li' . $fc . ' style="margin-left:20px;">\\1</li>',
            '<strike' . $fc . '>\\1</strike>',
            '<a href="\1\2" rel="nofollow" target="_blank">\1\2</a>',
            '<strong' . $fc . '>Quote:</strong><div style="margin:0px 10px;padding:5px;background-color:#F7F7F7;border:1px dotted #CCCCCC;width:80%;"><em>\1</em></div>',
            '<pre' . $fc . '>\\1</pre>',
        );

        $newtxt = preg_replace($patterns, $replaces, $newtxt);

        return ($newtxt);
    }

}
