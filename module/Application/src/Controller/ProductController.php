<?php
/** @noinspection PhpUnused */
/** @noinspection PhpUndefinedFieldInspection */
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpUnusedLocalVariableInspection */

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

use Application\Form\ProductForm;
use Application\Form\Validators\UploadLogoOrGallery;
use Application\Model\Repository\ActivityLogRepository;
use Application\Model\Repository\CollectionProjectsRepository;
use Application\Model\Repository\CommentsRepository;
use Application\Model\Repository\ImageRepository;
use Application\Model\Repository\MediaViewsRepository;
use Application\Model\Repository\MemberExternalIdRepository;
use Application\Model\Repository\MemberRepository;
use Application\Model\Repository\PlingsRepository;
use Application\Model\Repository\PploadFilesRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Repository\ProjectFollowerRepository;
use Application\Model\Repository\ProjectPlingsRepository;
use Application\Model\Repository\ProjectRatingRepository;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Repository\ProjectUpdatesRepository;
use Application\Model\Repository\ReportProductsRepository;
use Application\Model\Repository\SectionSupportRepository;
use Application\Model\Repository\StatPageViewsRepository;
use Application\Model\Repository\SuspicionLogRepository;
use Application\Model\Repository\TagsRepository;
use Application\Model\Repository\VideoRepository;
use Application\Model\Service\ActivityLogService;
use Application\Model\Service\BbcodeService;
use Application\Model\Service\CollectionService;
use Application\Model\Service\EmailBuilder;
use Application\Model\Service\HtmlPurifyService;
use Application\Model\Service\InfoService;
use Application\Model\Service\Mailer;
use Application\Model\Service\MemberService;
use Application\Model\Service\Ocs\Gitlab;
use Application\Model\Service\PploadService;
use Application\Model\Service\ProjectCategoryService;
use Application\Model\Service\ProjectCloneService;
use Application\Model\Service\ProjectPlingsService;
use Application\Model\Service\ProjectService;
use Application\Model\Service\ProjectTagRatingsService;
use Application\Model\Service\ProjectUpdatesService;
use Application\Model\Service\SectionService;
use Application\Model\Service\SectionSupportService;
use Application\Model\Service\SpamService;
use Application\Model\Service\TagConst;
use Application\Model\Service\TagGroupService;
use Application\Model\Service\TagService;
use Application\Model\Service\Util;
use Application\Model\Service\UtilReact;
use Application\Model\Service\Verification\WebsiteProject;
use Application\Model\Service\ViewsService;
use Application\View\Helper\AddDefaultScheme;
use Application\View\Helper\BuildProductUrl;
use Application\View\Helper\CatTitle;
use Application\View\Helper\FetchHeaderData;
use Application\View\Helper\Image;
use Application\View\Helper\IsSupporter;
use Application\View\Helper\IsSupporterActive;
use Application\View\Helper\ProjectDetailCounts;
use Exception;
use JobQueue\Jobs\CheckProjectWebsite;
use JobQueue\Jobs\ConvertVideo;
use JobQueue\Jobs\CreateTorrent;
use JobQueue\Jobs\ExtractComic;
use JobQueue\Jobs\JobBuilder;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Http\Client;
use Laminas\Http\PhpEnvironment\RemoteAddress;
use Laminas\Http\Request;
use Laminas\Json\Encoder;
use Laminas\Mvc\Exception\RuntimeException;
use Laminas\Validator\StringLength;
use Laminas\View\Helper\ServerUrl;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Library\Parsedown;
use Library\Ppload\PploadApi;

/**
 * Class ProductController
 *
 * @package Application\Controller
 */
class ProductController extends DomainSwitch
{

    const IMAGE_SMALL_UPLOAD = 'image_small_upload';
    const IMAGE_BIG_UPLOAD = 'image_big_upload';
    const CNT_COMMENTS_PER_PAGE = 25;
    protected $configArray;
    /**
     * Zend_Controller_Request_Abstract object wrapping the request environment
     *
     */
    protected $_request = null;
    /** @var  int */
    protected $_projectId;
    /** @var  int */
    protected $_collectionId;
    /** @var  string */
    protected $_browserTitlePrepend;

    protected $_authMember;

    protected $isAdmin = false;

    protected $projectRepository;
    protected $projectCategoryRepository;
    protected $projectCategoryService;
    protected $infoService;
    protected $tagService;
    protected $tagGroupService;
    protected $memberService;
    protected $projectService;
    protected $gitlab;
    protected $activityLog;
    protected $mailer;
    protected $emailbuilder;
    protected $websiteProject;
    protected $videoRepository;
    protected $projectPlingsRepository;
    protected $sectionSupportService;
    protected $projectRatingRepository;
    protected $sectionSupportRepository;
    protected $sectionService;
    protected $tagsRepository;
    /** @var ProjectPlingsService */
    private $projectPlingsService;
    /** @var ProjectFollowerRepository */
    private $projectFollowerRepository;
    /**
     * @var PploadFilesRepository
     */
    private $pploadFilesRepository;
    /**
     * @var ProjectUpdatesService
     */
    private $projectUpdatesService;
    /**
     * @var CollectionService
     */
    private $collectionService;
    /**
     * @var CommentsRepository
     */
    private $commentsRepository;
    /**
     * @var ProjectCloneService
     */
    private $projectCloneService;
    /**
     * @var ProjectTagRatingsService
     */
    private $projectTagRatingsService;
    /**
     * @var MediaViewsRepository
     */
    private $mediaViewsRepository;

    private $reportProductsRepository;
    private $pploadService;
    private $collectionProjectsRepository;

    /**
     * ProductController constructor.
     *
     * @param AdapterInterface             $db
     * @param array                        $config
     * @param Request                      $request
     * @param InfoService                  $infoService
     * @param TagService                   $tagService
     * @param TagGroupService              $tagGroupService
     * @param MemberService                $memberService
     * @param ProjectService               $projectService
     * @param Gitlab                       $gitlab
     * @param ActivityLogRepository        $activityLog
     * @param Mailer                       $mailer
     * @param EmailBuilder                 $emailbuilder
     * @param WebsiteProject               $websiteProject
     * @param VideoRepository              $videoRepository
     * @param ProjectCategoryRepository    $projectCategoryRepository
     * @param ProjectRepository            $projectRepository
     * @param ProjectCategoryService       $projectCategoryService
     * @param ProjectPlingsRepository      $projectPlingsRepository
     * @param ProjectPlingsService         $projectPlingsService
     * @param ProjectFollowerRepository    $projectFollowerRepository
     * @param SectionSupportService        $sectionSupportService
     * @param PploadFilesRepository        $pploadFilesRepository
     * @param ProjectUpdatesService        $projectUpdatesService
     * @param ProjectRatingRepository      $projectRatingRepository
     * @param SectionSupportRepository     $sectionSupportRepository
     * @param CollectionService            $collectionService
     * @param CommentsRepository           $commentsRepository
     * @param SectionService               $sectionService
     * @param ProjectCloneService          $projectCloneService
     * @param ProjectTagRatingsService     $projectTagRatingsService
     * @param TagsRepository               $tagsRepository
     * @param MediaViewsRepository         $mediaViewsRepository
     * @param ReportProductsRepository     $reportProductsRepository
     * @param PploadService                $pploadService
     * @param CollectionProjectsRepository $collectionProjectsRepository
     *
     */
    public function __construct(
        AdapterInterface $db,
        array $config,
        Request $request,
        InfoService $infoService,
        TagService $tagService,
        TagGroupService $tagGroupService,
        MemberService $memberService,
        ProjectService $projectService,
        Gitlab $gitlab,
        ActivityLogRepository $activityLog,
        Mailer $mailer,
        EmailBuilder $emailbuilder,
        WebsiteProject $websiteProject,
        VideoRepository $videoRepository,
        ProjectCategoryRepository $projectCategoryRepository,
        ProjectRepository $projectRepository,
        ProjectCategoryService $projectCategoryService,
        ProjectPlingsRepository $projectPlingsRepository,
        ProjectPlingsService $projectPlingsService,
        ProjectFollowerRepository $projectFollowerRepository,
        SectionSupportService $sectionSupportService,
        PploadFilesRepository $pploadFilesRepository,
        ProjectUpdatesService $projectUpdatesService,
        ProjectRatingRepository $projectRatingRepository,
        SectionSupportRepository $sectionSupportRepository,
        CollectionService $collectionService,
        CommentsRepository $commentsRepository,
        SectionService $sectionService,
        ProjectCloneService $projectCloneService,
        ProjectTagRatingsService $projectTagRatingsService,
        TagsRepository $tagsRepository,
        MediaViewsRepository $mediaViewsRepository,
        ReportProductsRepository $reportProductsRepository,
        PploadService $pploadService,
        CollectionProjectsRepository $collectionProjectsRepository
    ) {
        parent::__construct($db, $config, $request);
        parent::init();
        $this->projectCategoryRepository = $projectCategoryRepository;
        $this->projectRepository = $projectRepository;
        $this->projectCategoryService = $projectCategoryService;
        $this->infoService = $infoService;
        $this->tagService = $tagService;
        $this->tagGroupService = $tagGroupService;
        $this->memberService = $memberService;
        $this->projectService = $projectService;
        $this->gitlab = $gitlab;
        $this->activityLog = $activityLog;
        $this->mailer = $mailer;
        $this->websiteProject = $websiteProject;
        $this->videoRepository = $videoRepository;
        $this->configArray = $config;
        $this->emailbuilder = $emailbuilder;
        $this->projectPlingsRepository = $projectPlingsRepository;
        $this->projectPlingsService = $projectPlingsService;
        $this->projectFollowerRepository = $projectFollowerRepository;
        $this->sectionSupportService = $sectionSupportService;
        $this->pploadFilesRepository = $pploadFilesRepository;
        $this->projectUpdatesService = $projectUpdatesService;
        $this->projectRatingRepository = $projectRatingRepository;
        $this->sectionSupportRepository = $sectionSupportRepository;
        $this->collectionService = $collectionService;
        $this->commentsRepository = $commentsRepository;
        $this->sectionService = $sectionService;
        $this->projectCloneService = $projectCloneService;
        $this->projectTagRatingsService = $projectTagRatingsService;
        $this->tagsRepository = $tagsRepository;
        $this->mediaViewsRepository = $mediaViewsRepository;
        $this->reportProductsRepository = $reportProductsRepository;
        $this->pploadService = $pploadService;
        $this->collectionProjectsRepository = $collectionProjectsRepository;
    }

    


    public function loadRightAction()
    {
        $this->initVars();
        $this->view->setTerminal(true);
        $productInfo = $this->projectRepository->fetchProductInfo($this->_projectId);
        $productInfo = Util::arrayToObject($productInfo);
        $dataRightSideBar = $this->loadRightData($productInfo);

        return new JsonModel($dataRightSideBar);
    }

    public function initVars()
    {
        $this->_projectId = (int)$this->params('project_id', null);
        if (null == $this->_projectId) {
            $this->_projectId = (int)$this->params()->fromRoute('project_id', null);
        }

        // $this->_collectionId = (int)$this->params('collection_id', null);
        $this->_browserTitlePrepend = $this->templateConfigData['head']['browser_title_prepend'];

        $action = $this->getEvent()->getRouteMatch()->getParam('action', 'index');
        $title = $action;
        if ($action == 'add') {
            $title = 'add product';
        }
        $this->view->setVariable('headTitle', ($title . ' - ' . $this->getHeadTitle()));

        $userRoleName = $this->_authMember->roleName;

        $this->isAdmin = false;
        if ('admin' == $userRoleName) {
            $this->isAdmin = true;
        }
    }

    private function loadRightData($productInfo)
    {

        $section = $this->sectionService->fetchSectionForCategory($productInfo->project_category_id);
        if (!$section) {
            $section = array('name' => 'Test', 'section_id' => '1');
        }
        $sectionSupporters = $this->infoService->getNewActiveSupportersForSection($section['section_id'], 9);        

        $supportboxData = array(
            "catId"                  => (int)$productInfo->project_category_id,
            "section"                => $section,
            "supporters"             => $sectionSupporters,
            "project_id"             => (int)$productInfo->project_id,
            "project_title"          => $productInfo->title,
            "ppload_collection_id"   => $productInfo->ppload_collection_id,
            "project_category_id"    => $productInfo->project_category_id,
            "project_category_title" => $productInfo->cat_title,
            "baseurl_store"          => $GLOBALS['ocs_config']->baseurl_store,
        );
       

        $cntCollections = $this->collectionService->countAllCollectionsForProject($productInfo->project_id);
        $moreCollectionProducts = array();
        if ($cntCollections > 0) {
            $moreCollectionProducts = $this->collectionService->fetchAllCollectionsForProject(
                $productInfo->project_id, 6
            );
        }

        if ($productInfo->type_id == ProjectRepository::PROJECT_TYPE_COLLECTION) {
            $moreProductsOfUser = $this->collectionService->fetchMoreCollections($productInfo, 6);
            $moreProductsOfOtherUsers = $this->collectionService->fetchMoreCollectionsOfOtherUsr($productInfo, 6);
        } else {
            $moreProductsOfUser = $this->projectRepository->fetchMoreProjects($productInfo, 6);
            $moreProductsOfOtherUsers = $this->projectRepository->fetchMoreProjectsOfOtherUsr($productInfo, 6);
        }


        $countDownloadsToday = 0;
        $countMediaViewsAlltime = 0;
        if ($this->isAdmin) {
            $countDownloadsToday = $this->pploadFilesRepository->fetchCountDownloadsTodayForProject(
                $productInfo->ppload_collection_id
            );
            $countMediaViewsAlltime = $this->mediaViewsRepository->fetchCountViewsForProjectAllTime(
                $productInfo->project_id
            );
        }

        $countDownloadsTodayUk = $this->pploadFilesRepository->fetchCountDownloadsTodayForProjectNew(
            $productInfo->ppload_collection_id
        );
        $countMediaViewsToday = $this->mediaViewsRepository->fetchCountViewsTodayForProject($productInfo->project_id);

        $projectDetailCountsHelper = new ProjectDetailCounts($this->projectRepository);
        $countPageviews = $projectDetailCountsHelper($this->_projectId);
        $countPageviewsTotal = 0;
        $countPageviewsToday = $countPageviews[0]['count_views'];
        if (sizeof($countPageviews) == 2) {
            $countPageviewsTotal = $countPageviews[1]['count_views'];
        }

        // related products
        $origins = $this->projectCloneService->fetchOrigins($this->_projectId);
        $related = $this->projectCloneService->fetchRelatedProducts($this->_projectId);
        $relatednew = array();
        foreach ($related as $r) {
            $rid = $r['project_id'];
            $bflag = false;
            foreach ($origins as $o) {
                $oid = $o['project_id'];
                if ($rid == $oid) {
                    $bflag = true;
                    break;
                }
            }
            if (!$bflag) {
                $relatednew[] = $r;
            }
        }
        $systemTags = $this->tagService->getTagsSystemList($this->_projectId);
        
        $projectaffiliates = $this->sectionSupportService->fetchAffiliatesForProject($this->_projectId);
        $countSpamReports = $this->reportProductsRepository->countSpamForProject($this->_projectId);
        $countMisuseReports = $this->reportProductsRepository->countMisuseForProject($this->_projectId);
        $cntProjectPlings = $this->projectPlingsService->fetchPlingsCntForProject($this->_projectId);

        $tagsArray = $this->tagService->getTagsArray(
            $this->_projectId, TagConst::TAG_TYPE_PROJECT, TagConst::TAG_GHNS_EXCLUDED_GROUPID
        );
        $isGhnsExcluded = false;
        if (isset($tagsArray) && (count($tagsArray) == 1)) {
            $isGhnsExcluded = true;
        }
        $moreProductsOfUserArray = $moreProductsOfUser->toArray();
        $moreProductsOfOtherUsersArray = $moreProductsOfOtherUsers->toArray();

        $dataRightSideBar = array(
            'isGhnsExcluded'           => $isGhnsExcluded,
            'cntProjectaffiliates'     => (int)count($projectaffiliates),
            'cntProjectPlings'         => (int)$cntProjectPlings,
            'dataSupportbox'           => $supportboxData,
            'cntCollections'           => (int)$cntCollections,
            'moreProductsBasedon'      => $origins,
            'moreProductsVariants'     => $relatednew,
            'moreProductsOfUser'       => $moreProductsOfUserArray,
            'moreProductsOfOtherUsers' => $moreProductsOfOtherUsersArray,
            'moreCollectionProducts'   => $moreCollectionProducts,
            'countDownloadsToday'      => (int)$countDownloadsToday,
            'countDownloadsTodayUk'    => (int)$countDownloadsTodayUk,
            'countMediaViewsToday'     => (int)$countMediaViewsToday,
            'countMediaViewsAlltime'   => (int)$countMediaViewsAlltime,
            'countPageviews24h'        => (int)$countPageviewsToday,
            'countPageviewsTotal'      => (int)$countPageviewsTotal,
            'countSpamReports'         => (int)$countSpamReports,
            'countMisuseReports'       => (int)$countMisuseReports,
            'systemTags'               => $systemTags,
        );
       
        if (!$this->isAdmin) {
            unset($dataRightSideBar['isGhnsExcluded']);
            unset($dataRightSideBar['countMediaViewsAlltime']);
            unset($dataRightSideBar['countPageviewsTotal']);
        }

        return $dataRightSideBar;
    }

   
    /**
     * @return JsonModel
     * @throws Exception
     * @noinspection SqlResolve
     */
    public function followprojectAction()
    {
        $this->view->setTerminal(true);

        $this->initVars();

        $this->view->project_id = $this->_projectId;
        $this->view->authMember = $this->_authMember;

        $userProjects = $this->_authMember->projects;
        if (null == $userProjects) {
            $userProjects = array();
        }

        // not allow to pling himself
        if (array_key_exists($this->_projectId, $userProjects)) {
            return new JsonModel(
                array(
                    'status' => 'error',
                    'msg'    => 'not allowed',
                )
            );
        }


        $projectFollowTable = $this->projectFollowerRepository;

        $newVals = array('project_id' => $this->_projectId, 'member_id' => $this->_authMember->member_id);

        $sql = sprintf(
            "SELECT * FROM `%s` WHERE `member_id` = %s AND `project_id` = %d", $projectFollowTable->getName(), $this->_authMember->member_id, $this->_projectId
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

    /**
     * @param $logId
     *
     * @throws Exception
     */
    protected function logActivity($logId)
    {
        $tableProduct = $this->projectRepository;
        $product = $tableProduct->findById($this->_projectId);
        $activityLog = $this->activityLog;
        $activityLog->writeActivityLog(
            $this->_projectId, $this->_authMember->member_id, $logId, $product->getArrayCopy()
        );
    }

    /**
     * @return JsonModel|ViewModel
     * @throws Exception
     * @noinspection SqlResolve
     */
    public function unfollowAction()
    {
        $this->initVars();
        $this->view->setTerminal(true);
        $this->view->setTemplate('application/product/follow');

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


        $projectFollowTable = $this->projectFollowerRepository;
        $sql = sprintf("SELECT * FROM `%s` WHERE `member_id` = %s AND `project_id` = %d", $projectFollowTable->getName(), $this->_authMember->member_id, $this->_projectId);
        $result = $projectFollowTable->fetchRow($sql);

        $projectFollowTable->deleteReal($result[$projectFollowTable->getKey()]);
        $this->logActivity(ActivityLogRepository::PROJECT_UNFOLLOWED);

        return $this->view;
    }

    public function followAction()
    {
        $this->initVars();
        $this->view->setTerminal(true);
        $this->view->setTemplate('application/product/follow');

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


        $projectFollowTable = $this->projectFollowerRepository;

        $newVals = array('project_id' => $this->_projectId, 'member_id' => $this->_authMember->member_id);

        $projectFollowTable->insert($newVals);
        $this->logActivity(ActivityLogRepository::PROJECT_FOLLOWED);
        $cnt = $projectFollowTable->countForProject($this->_projectId);

        return $this->view;

        // return new JsonModel(
        //     array(
        //         'status' => 'ok',
        //         'msg'    => 'Success.',
        //         'cnt'    => $cnt,
        //         'action' => 'insert',
        //     )
        // );
    }

    /**
     * @return JsonModel
     * @noinspection SqlResolve
     */
    public function plingprojectAction()
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

        // not allow to pling if not supporter
        $helperIsSupporter = new IsSupporter($this->memberService);

        if (!$helperIsSupporter->isSupporter($this->_authMember->member_id)) {
            return new JsonModel(
                array(
                    'status' => 'error',
                    'msg'    => 'become a supporter first please. ',
                )
            );
        }


        $projectplings = $this->projectPlingsRepository;

        $newVals = array('project_id' => $this->_projectId, 'member_id' => $this->_authMember->member_id);
        $sql = sprintf("SELECT * FROM `%s` WHERE `member_id` = %s AND `is_deleted` = 0 AND `project_id` = %d", $projectplings->getName(), $this->_authMember->member_id, $this->_projectId);
        $result = $projectplings->fetchRow($sql);

        if (null === $result) {
            $projectplings->insert($newVals);

            $cnt = $projectplings->getPlingsAmount($this->_projectId);

            return new JsonModel(
                array(
                    'status' => 'ok',
                    'msg'    => 'Success.',
                    'cnt'    => $cnt,
                    'action' => 'insert',
                )
            );
        } else {

            // delete pling
            $projectplings->setDelete($result['project_plings_id']);

            $cnt = $projectplings->getPlingsAmount($this->_projectId);

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

    public function startdownloadAction()
    {
        $this->view->setTerminal(true);

        $this->initVars();

        /**
         * Save Download-Data in Member_Download_History
         */
        $file_id = $this->getParam('file_id');
        $file_type = $this->getParam('file_type');
        $file_name = $this->getParam('file_name');
        $file_size = $this->getParam('file_size');
        $projectId = $this->_projectId;

        return $this->redirect()->toUrl(
            '/dl?file_id=' . $file_id . '&file_type=' . $file_type . '&file_name=' . $file_name . '&file_size=' . $file_size . '&project_id=' . $projectId
        );

    }

    public function getfilesajaxAction()
    {
        //$this->_helper->layout()->disableLayout();
        $this->view->setTerminal(true);

        $this->initVars();

        $collection_id = null;
        $file_status = null;
        $ignore_status_code = null;

        if ($this->getParam('status')) {
            $file_status = $this->getParam('status');
        }
        if ($this->getParam('ignore_status_code')) {
            $ignore_status_code = $this->getParam('ignore_status_code');
        }

        $filesTable = $this->pploadFilesRepository;

        if ($this->getParam('collection_id')) {

            $collection_id = $this->getParam('collection_id');
            $result = array();
            $isForAdmin = false;
            if ($this->isAdmin) {
                $isForAdmin = true;
            }

            //Load files from DB
            if ($ignore_status_code == 0 && $file_status == 'active') {
                $files = $filesTable->fetchAllActiveFilesForProject($collection_id, $isForAdmin);
            } else {
                $files = $filesTable->fetchAllFilesForProject($collection_id, $isForAdmin);
            }

            //Check, if the project category has tag-grous
            $modelProduct = $this->projectRepository;
            $productInfo = $modelProduct->fetchProductInfo($this->_projectId);

            $catTagGropuModel = $this->tagGroupService;
            $tagGroups = $catTagGropuModel->fetchTagGroupsForCategory($productInfo['project_category_id']);

            foreach ($files as $file) {
                //add tag grous, if needed
                if (!empty($tagGroups)) {
                    $groups = $this->getTagGroupsForCat($file['id'], $productInfo);
                    $file['tag_groups'] = $groups;
                }

                //Download Counter
                //new counter IP based
                $counterUkAll = $file['count_dl_all_uk'];
                $counterNoUkAll = $file['count_dl_all_nouk'];
                $counterUkToday = $file['count_dl_uk_today'];
                $counterNew = 0;
                if (!empty($counterUkAll)) {
                    $counterNew = $counterNew + $counterUkAll;
                }
                if (!empty($counterUkToday)) {
                    $counterNew = $counterNew + $counterUkToday;
                }
                if (!empty($counterNoUkAll)) {
                    $counterNew = $counterNew + $counterNoUkAll;
                }
                $file['downloaded_count_uk'] = $counterNew;


                if ($this->isAdmin) {
                    //$file['downloaded_count_live'] = $this->getFileDownloadCount($collection_id, $file['id']);
                    $counterToday = $file['count_dl_today'];
                    $counterAll = $file['count_dl_all'];
                    $counter = 0;
                    if (!empty($counterToday)) {
                        $counter = $counterToday;
                    }
                    if (!empty($counterAll)) {
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

            return new JsonModel(
                array('status' => 'success', 'ResultSize' => count($result), 'files' => $result)
            );
        }

        return new JsonModel(array('status' => 'error'));
    }

    /**
     * @param $fileId
     * @param $productInfo
     *
     * @return array|null
     */
    private function getTagGroupsForCat($fileId, $productInfo)
    {
        $catId = $productInfo['project_category_id'];

        if (!empty($catId)) {
            $catTagModel = $this->tagService;
            $catTagGropuModel = $this->tagGroupService;
            $tagGroups = $catTagGropuModel->fetchTagGroupsForCategory($catId);

            $tableTags = $this->tagsRepository;

            $result = array();

            foreach ($tagGroups as $group) {
                $tags = $tableTags->fetchForGroupForSelect($group['tag_group_id']);
                $selectedTags = null;
                if (!empty($fileId)) {
                    $selectedTags = $catTagModel->getTagsArray(
                        $fileId, TagConst::TAG_TYPE_FILE, $group['tag_group_id']
                    );
                }

                $group['tag_list'] = $tags;
                $group['selected_tags'] = $selectedTags;
                $result[] = $group;
            }

            return $result;
        }

        return null;
    }

    public function pploadAction()
    {
        $this->initVars();

        $modelProduct = $this->projectRepository;
        $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
        //create ppload download hash: secret + collection_id + expire-timestamp
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $collectionID = $productInfo['ppload_collection_id'];
        $timestamp = time() + 3600; // one hour valid
        //20181009 ronald: change hash from MD5 to SHA512
        //$hash = md5($salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying
        $hash = hash(
            'sha512', $salt . $collectionID . $timestamp
        ); // order isn't important at all... just do the same when verifying

        $this->view->setVariable('download_hash', $hash);
        $this->view->setVariable('download_timestamp', $timestamp);

        $this->view->setVariable('product', $productInfo);
        $this->view->setTemplate('/partials/pploadajax');

        //TODO: check this. where is the view template?
        return $this->view;
    }

    public function gettaggroupsforcatajaxAction()
    {
        $this->view->setTerminal(true);

        $catId = null;
        $fileId = $this->getParam('file_id');
        $catId = $this->getParam('project_cat_id');

        if (!empty($catId)) {
            $catTagModel = $this->tagService;
            $catTagGropuModel = $this->tagGroupService;
            $tagGroups = $catTagGropuModel->fetchTagGroupsForCategory($catId);

            $tableTags = $this->tagsRepository;

            $result = array();
            //$resultGroup = array();

            foreach ($tagGroups as $group) {
                $tags = $tableTags->fetchForGroupForSelect($group['tag_group_id']);
                $selectedTags = null;
                if (!empty($fileId)) {
                    $selectedTags = $catTagModel->getTagsArray(
                        $fileId, TagConst::TAG_TYPE_FILE, $group['tag_group_id']
                    );
                }

                $group['tag_list'] = $tags;
                $group['selected_tags'] = $selectedTags;
                $result[] = $group;
            }

            return new JsonModel(
                array('status' => 'ok', 'ResultSize' => count($tagGroups), 'tag_groups' => $result)
            );
        }


        return new JsonModel(array('status' => 'error', 'param_catId' => $catId, 'param_fileId' => $fileId));
    }

    public function getfiletagsajaxAction()
    {
        $this->view->setTerminal(true);

        $fileId = null;

        if ($this->getParam('file_id')) {
            $fileId = $this->getParam('file_id');

            $tagModel = $this->tagService;
            $fileTags = $tagModel->getFileTags($fileId);

            return new JsonModel(
                array('status' => 'ok', 'ResultSize' => count($fileTags), 'file_tags' => $fileTags)
            );
        }

        return new JsonModel(array('status' => 'error'));
    }

    public function listsamesourceurlAction()
    {
        $this->initVars();
        $this->view->setTerminal(true);

        $productInfo = $this->projectRepository->fetchProductInfo($this->_projectId);
        $result = $this->projectService->getSourceUrlProjects($productInfo['source_url']);

        $r = '<div class="container containerduplicates">';
        foreach ($result as $value) {
            $r = $r . '<div class="row"><div class="col-lg-2"><a href="/p/' . $value['project_id'] . '">' . $value['project_id'] . '</a></div>' . '<div class="col-lg-4">' . $value['title'] . '</div>' . '<div class="col-lg-2"><a href="/u/' . $value['username'] . '">' . $value['username'] . '</a></div>' . '<div class="col-lg-2">' . $value['created_at'] . '</div>' . '<div class="col-lg-2">' . $value['changed_at'] . '</div>' . '</div>';
        }

        $r = $r . '</div>';


        /*$response='<ul class="list-group" style="min-height:300px;min-width:1000px; max-height:500px; overflow:auto">';
        foreach ($result as $value) {
            $response=$response.'<li class="list-group-item"><a href="/p/'.$value['project_id'].'">'.$value['project_id'].'</a></li>';
        }
        $response=$response.'</ul>';*/
        $this->view->setVariable('data', $r);

        //echo $r;

        return $this->view;

    }

    public function addAction()
    {
        $this->initVars();
        $this->setLayout();
        $headerData = $this->loadHeaderData();
        $this->layout()->setVariable('headerData',  $headerData);
        
        $this->view->setVariable('headTitle', ('Add - ' . $this->getHeadTitle()));
        $this->view->setVariable('member', $this->_authMember);
        $this->view->setVariable('mode', 'add');
        $this->view->setVariable('prodCatRepo', $this->projectCategoryRepository);

        if ($this->getParam('catId')) {
            $this->view->setVariable('catId', (int)$this->getParam('catId'));
        }
        //get service manager
        $serviceManager = $this->getEvent()->getApplication()->getServiceManager();

        //get the gitlab projects for this user
        $tags_repository = $this->tagsRepository;
        $project_category_service = $this->projectCategoryService;
        $project_repository = $this->projectRepository;
        /** @var MemberExternalIdRepository $member_external_id_repository */
        $member_external_id_repository = $serviceManager->get(MemberExternalIdRepository::class); //new MemberExternalIdRepository($this->db);
        /** @var ImageRepository $image_repository */
        $image_repository = $serviceManager->get(ImageRepository::class); //new ImageRepository($this->db, $this->configArray);

        /** @var ProductForm $form */
        $form = $serviceManager->get('FormElementManager')->get(ProductForm::class);
        $this->view->setVariable('form', $form);

        if ($this->_request->isGet()) {
            return $this->view;
        }

        if (isset($_POST['cancel'])) { // user cancel function
            $this->redirect()->toUrl('/u/' . $this->_authMember->username . '/news/');
        }

        $filterEmptyUploads = new \Application\Form\Filter\RemoveEmptyFileUploads();
        $uploads = $filterEmptyUploads->filter($this->params()->fromFiles());
        $data = array_merge_recursive($this->params()->fromPost(), $uploads);

        $form->setData($data);
        if (false === $form->isValid()) { // form not valid
            $this->view->setVariable('form', $form);
            $this->view->setVariable('error', 1);
            $GLOBALS['ocs_log']->debug(__METHOD__ . '(' . __LINE__ . ')' . ' - ' . json_encode($form->getMessages()));

//          experimental feature for showing the temp file uploads in form
            $values = $form->getInputFilter()->getValue('upload_picture');
            $sources = array_column($values, 'tmp_name');
            //$sources = array_map(function($element){return str_replace($_SERVER['DOCUMENT_ROOT'], '', $element);}, $sources);
            $form->setOnlineGalleryImageSources($sources);

            return $this->view;
        }
        // we check if minimum one product logo or minimum one gallery image has been uploaded
        $validLogoOrGallery = new UploadLogoOrGallery();
        if (false === $validLogoOrGallery->isValid($form->get('image_small_upload')->getValue(), $form->getData())) {
            $form->setMessages(['image_small_upload' => $validLogoOrGallery->getMessages()]);
            $this->view->setVariable('form', $form);
            $this->view->setVariable('error', 1);
            $GLOBALS['ocs_log']->debug(__METHOD__ . '(' . __LINE__ . ')' . ' - ' . json_encode($form->getMessages()));

            return $this->view;
        }

        $filteredValues = $form->getInputFilter()->getValues();

        if (isset($filteredValues['image_small_upload'])) {
            try {
                $uploadedSmallImage = $image_repository->saveImage($filteredValues[self::IMAGE_SMALL_UPLOAD]);
                $filteredValues['image_small'] = $uploadedSmallImage ? $uploadedSmallImage : $filteredValues['image_small'];
            } catch (Exception $e) {
                $GLOBALS['ocs_log']->err(__METHOD__ . ' - ERROR upload productPicture - ' . print_r($e, true));
            }
        }

        // form was valid, so we can set status to active
        $filteredValues['status'] = ProjectService::PROJECT_ACTIVE;

        $GLOBALS['ocs_log']->info(__METHOD__ . ' - $post: ' . print_r($_POST, true));
        $GLOBALS['ocs_log']->info(__METHOD__ . ' - $files: ' . print_r($_FILES, true));
        $GLOBALS['ocs_log']->info(__METHOD__ . ' - filtered values: ' . print_r($filteredValues, true));

        $newProject = null;
        $updateValues = $filteredValues;

        //remove not needed elements
        unset($updateValues['tags']);
        unset($updateValues['tagsuser']);
        unset($updateValues['preview']);
        unset($updateValues['image_small_upload']);
        unset($updateValues['cancel']);
        unset($updateValues['license_tag_id']);
        unset($updateValues['is_original_or_modification']);
        unset($updateValues['gallery']);
        unset($updateValues['upload_picture']);
        unset($updateValues['online_picture']);

        // save new project
        $project_service = $this->projectService;
        try {
            if (false === empty($updateValues['project_id'])) {
                $newProject = $project_service->updateProject($filteredValues['project_id'], $updateValues);
            } else {
                $newProject = $project_service->createProject(
                    $this->_authMember->member_id, $updateValues, $this->_authMember->username
                );
            }
        } catch (Exception $exc) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - traceString: ' . $exc->getTraceAsString());
        }

        if (!$newProject) {
            //$this->view->getVariable('flashMessenger')->addMessage('<p class="text-error">You did not choose a Category in the last level.</p>');
            //$this->forward()->dispatch('add');

            //return $this->view;
            throw new RuntimeException('Unknown error. Please try again later.');
        }

        //update the gallery pics
        $galleryPicsUpload = isset($filteredValues['upload_picture']) ? $filteredValues['upload_picture'] : null;
        $galleryPicsOnline = isset($filteredValues['online_picture']) ? $filteredValues['online_picture'] : null;

        $pictureSources = array();
        $galleryPicsUploadSources = array();
        if (isset($galleryPicsOnline)) {
            $pictureSources = $galleryPicsOnline;
        }

        if (isset($galleryPicsUpload)) {
            $galleryPicsUploadSources = $image_repository->saveImages($galleryPicsUpload);
            $pictureSources = $pictureSources + $galleryPicsUploadSources;
        }

//        if (isset($galleryPicsOnline) && isset($galleryPicsUploadSources)) {
//            $pictureSources = array_merge_recursive($galleryPicsOnline, $galleryPicsUploadSources);
//        }
        $project_service->updateGalleryPictures($newProject->project_id, $pictureSources);

        //If there is no Logo, we take the 1. gallery pic
        if ((!isset($newProject->image_small) || $newProject->image_small == '') && count($pictureSources) > 0) {
            $newProject->image_small = $pictureSources[0];
            $project_repository->update(
                array(
                    'project_id'  => $newProject->project_id,
                    'image_small' => $pictureSources[0],
                )
            );
        }

        //New Project in Session, for AuthValidation (owner)
        $this->_authMember->projects[$newProject->project_id] = array('project_id' => $newProject->project_id);
        $GLOBALS['ocs_user'] = $this->_authMember;


        $modelTags = $this->tagService;
        if ($filteredValues['tagsuser']) {
            $modelTags->processTagsUser(
                $newProject->project_id, implode(',', $filteredValues['tagsuser']), TagConst::TAG_TYPE_PROJECT
            );
        } else {
            $modelTags->processTagsUser($newProject->project_id, null, TagConst::TAG_TYPE_PROJECT);
        }

        $modelTags->processTagProductOriginalOrModification(
            $newProject->project_id, $filteredValues['is_original_or_modification']
        );

        //set license, if needed
        $licenseTag = $filteredValues['license_tag_id'];
        //only set/update license tags if something was changed
        if ($licenseTag && count($licenseTag) > 0) {
            $modelTags->saveLicenseTagForProject($newProject->project_id, $licenseTag);
            $activityLog = $this->activityLog;
            //@formatter:off
            $this->activityLog->logActivity(
                $newProject->project_id
                , $newProject->project_id
                , $this->_authMember->member_id
                , $activityLog::PROJECT_LICENSE_CHANGED
                , array('title'       => 'Set new License Tag',
                        'description' => 'New TagId: ' . $licenseTag,
                )
            );
            //@formatter:on
        }

        //gitlab project
        $isGitlabProject = $filteredValues['is_gitlab_project'];
        $gitlabProjectId = $filteredValues['gitlab_project_id'];
        if ($isGitlabProject && ($gitlabProjectId == 0)) {
            $filteredValues['gitlab_project_id'] = null;
        }

        $activityLog = $this->activityLog;
        $activityLog->writeActivityLog(
            $newProject->project_id, $newProject->member_id, $activityLog::PROJECT_CREATED, $newProject->getArrayCopy()
        );

        // ppload
        $this->processPploadId($newProject);

        try {
            if (100 < $this->_authMember->roleId) {
                $spamService = new SpamService($this->db);
                if ($spamService->hasSpamMarkers($newProject->getArrayCopy())) {
                    $messages = json_encode($spamService->getMessages());
                    $tableReportComments = $this->reportProductsRepository;
                    $tableReportComments->insert(
                        array(
                            'project_id'  => $newProject->project_id,
                            'reported_by' => 24,
                            'text'        => "System: automatic spam detection on product add {$messages}",
                        )
                    );
                }
                SuspicionLogRepository::logProject($newProject, $this->_authMember, $this->getRequest());
            }
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err($e->getMessage());
        }

        return $this->redirect()->toUrl('/member/' . $newProject->member_id . '/products/');
    }

    /**
     *
     */
    protected function setLayout()
    {
        $layoutName = 'layout/flat-ui';
        $storeConfig = $GLOBALS['ocs_store']->config;
        if ($storeConfig && $storeConfig->layout_pagedetail) {
            $this->layout()->setTemplate($storeConfig->layout_pagedetail);
        } else {
            $this->layout()->setTemplate($layoutName);
        }               
        
    }

    /**
     * @param $projectData
     */
    protected function processPploadId($projectData)
    {
        if ($projectData->ppload_collection_id) {
            $pploadApi = new PploadApi(
                array(
                    'apiUri'   => PPLOAD_API_URI,
                    'clientId' => PPLOAD_CLIENT_ID,
                    'secret'   => PPLOAD_SECRET,
                )
            );
            // Update collection information
            $collectionCategory = $projectData->project_category_id;
            if (ProjectRepository::PROJECT_ACTIVE == $projectData->status) {
                $collectionCategory .= '-published';
            }
            $collectionRequest = array(
                'title'       => $projectData->title,
                'description' => $projectData->description,
                'category'    => $collectionCategory,
                'content_id'  => $projectData->project_id,
            );

            $pploadApi->putCollection($projectData->ppload_collection_id, $collectionRequest);

            // Store product image as collection thumbnail
            $this->_updatePploadMediaCollectionthumbnail($projectData);
        }
    }

    /**
     * ppload
     *
     * @param $projectData
     *
     * @return bool
     */
    protected function _updatePploadMediaCollectionthumbnail($projectData)
    {
        if (empty($projectData->ppload_collection_id) || empty($projectData->image_small)) {
            return false;
        }

        $pploadApi = new PploadApi(
            array(
                'apiUri'   => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret'   => PPLOAD_SECRET,
            )
        );

        $filename = sys_get_temp_dir() . '/' . $projectData->image_small;
        if (false === file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        $viewHelperImage = new Image();
        $uri = $viewHelperImage->Image(
            $projectData->image_small, array(
                                         'width'  => 600,
                                         'height' => 600,
                                     )
        );

        file_put_contents($filename, file_get_contents($uri));

        $mediaCollectionthumbnailResponse = $pploadApi->postMediaCollectionthumbnail(
            $projectData->ppload_collection_id, array('file' => $filename)
        );

        unlink($filename);

        if (isset($mediaCollectionthumbnailResponse->status) && $mediaCollectionthumbnailResponse->status == 'success') {
            return true;
        }

        return false;
    }

    public function editAction()
    {
        if (false === $this->currentUser()->hasIdentity()) {
            throw new RuntimeException("unauthorized access");
        }

        $this->initVars();
        $this->setLayout();
       
       
        if (empty($this->_projectId)) {
            return $this->redirect()->toRoute('application_product_add');
        }

        $this->view->setVariable('headTitle', ('Edit - ' . $this->getHeadTitle()));
        $this->view->setTemplate('application/product/add'); // we use the same view as you can see at add a product
        $this->view->setVariable('mode', 'edit');
        $this->view->setVariable('prodCatRepo', $this->projectCategoryRepository);

        //get service manager
        $serviceManager = $this->getEvent()->getApplication()->getServiceManager();

        $projectTable = $this->projectRepository;
        $projectModel = $this->projectService;
        $modelTags = $this->tagService;
        $project_category_service = $this->projectCategoryService;
        /** @var TagsRepository $tags_repository */
        $tags_repository = $serviceManager->get(TagsRepository::class); //new TagsRepository($this->db, $this->cache);
        /** @var ImageRepository $image_repository */
        $image_repository = $serviceManager->get(ImageRepository::class);
        /** @var MemberExternalIdRepository $member_external_id_repository */
        $member_external_id_repository = $serviceManager->get(MemberExternalIdRepository::class); //new MemberExternalIdRepository($this->db);

        //check if product with given id exists
        $projectData = $projectTable->findById($this->_projectId);

        if ($projectData->type_id == ProjectRepository::PROJECT_TYPE_COLLECTION) {
            return $this->redirect()->toUrl('/c/' . $projectData->project_id . '/edit');
        }
        
        $headerData = $this->loadHeaderDataWithCatid($projectData->project_category_id);        
        $this->layout()->setVariable('headerData',  $headerData);
       
        $member = $this->currentUser();
        $memberIsAdmin = $this->currentUser()->isAdmin();

        if ($memberIsAdmin) {
            $modelMember = $this->memberService;
            $member = $modelMember->fetchMemberData($projectData->member_id, false);
        }
        $this->view->setVariable('isAdmin', $memberIsAdmin);

        //set ppload-collection-id in view
        $this->view->setVariable('ppload_collection_id', $projectData->ppload_collection_id);
        $this->view->setVariable('project_id', $projectData->project_id);
        $this->view->setVariable('product', $projectData);

        //create ppload download hash: secret + collection_id + expire-timestamp
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $collectionID = $projectData->ppload_collection_id;
        $timestamp = time() + 3600; // one hour valid
        //20181009 ronald: change hash from MD5 to SHA512
        //$hash = md5($salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying
        $hash = hash('sha512', $salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying

        $this->view->setVariable('download_hash', $hash);
        $this->view->setVariable('download_timestamp', $timestamp);
        $this->view->setVariable('member_id', $member->member_id);
        $this->view->setVariable('member', $member);

        //read the already existing gallery pics and add them to the form
        $sources = $projectModel->getGalleryPictureSources($this->_projectId);

        //@formatter:off
        //setup form
        $formManager = $serviceManager->get('FormElementManager');
        /** @var ProductForm $form */
        $form = $formManager->get(ProductForm::class);
        $form->setOnlineGalleryImageSources($sources);
        $form->setMemberId($member->member_id);
        //@formatter:on

        if (false === empty($projectData->image_small) && $form->has('image_small_upload')) {
            $form->get('image_small_upload')->setAttribute('required', false);
        }
        $form->get('preview')->setLabel('Save');

        if ($this->_request->isGet()) {
            $form->populateValues($projectData->getArrayCopy());
            $form->populateValues(
                array(
                    'tagsuser' => $modelTags->getTagsUser($projectData->project_id, TagConst::TAG_TYPE_PROJECT),
                )
            );

            $form->get('image_small')->setValue($projectData->image_small);
            $form->get(self::IMAGE_SMALL_UPLOAD)->setValue($projectData->image_small);

            $tagmodel = $this->tagService;
            $systemTags = $tagmodel->getTagsCategory($this->_projectId, TagConst::TAG_TYPE_PROJECT);
            $this->view->setVariable('systemTags', $systemTags);


            $licenseTags = $tags_repository->fetchLicenseTagsForProject($this->_projectId);
            $licenseTag = null;
            if ($licenseTags) {
                $licenseTag = $licenseTags[0]['tag_id'];
            }
            $form->get('license_tag_id')->setValue($licenseTag);

            $is_original = $modelTags->isProductOriginal($projectData->project_id);
            $is_modification = $modelTags->isProductModification($projectData->project_id);
            if ($is_original) {
                $form->get('is_original_or_modification')->setValue(1);
            } else {
                if ($is_modification) {
                    $form->get('is_original_or_modification')->setValue(2);
                }
            }

            $this->view->setVariable('form', $form);

            return $this->view;
        }

        if (isset($_POST['cancel'])) { // user cancel function
            $this->redirect()->toUrl('/u/' . $member->username . '/news/');
        }

        $filterEmptyUploads = new \Application\Form\Filter\RemoveEmptyFileUploads();
        $uploads = $filterEmptyUploads->filter($this->params()->fromFiles());
        $data = array_merge_recursive($this->params()->fromPost(), $uploads);

        $form->setData($data);
        if (false === $form->isValid()) { // form not valid
            $this->view->setVariable('form', $form);
            $this->view->setVariable('error', 1);
            $GLOBALS['ocs_log']->debug(__METHOD__ . '(' . __LINE__ . ')' . ' - ' . json_encode($form->getMessages()));

            return $this->view;
        }

        $values = $form->getData();
        $filteredValues = $form->getInputFilter()->getValues();

        //set license, if needed
        $tagList = $modelTags->getTagsArray(
            $this->_projectId, TagConst::TAG_TYPE_PROJECT, TagConst::TAG_LICENSE_GROUPID
        );
        $oldLicenseTagId = null;
        if ($tagList && count($tagList) == 1) {
            $oldLicenseTagId = $tagList[0]['tag_id'];
        }

        $licenseTag = $form->get('license_tag_id')->getValue();
        //only set/update license tags if something was changed
        if ($licenseTag <> $oldLicenseTagId) {
            $modelTags->saveLicenseTagForProject($this->_projectId, $licenseTag);
            //@formatter:off
            ActivityLogService::logActivity(
                $this->_projectId
                , $this->_projectId
                , $this->_authMember->member_id
                , ActivityLogService::PROJECT_LICENSE_CHANGED
                , array(
                     'title'       => 'License Tag',
                     'description' => 'Old TagId: ' . $oldLicenseTagId . ' - New TagId: ' . $licenseTag,
                 )
            );
            //@formatter:on
        }

        //gitlab project
        $isGitlabProject = $filteredValues['is_gitlab_project'];
        $gitlabProjectId = $filteredValues['gitlab_project_id'];
        if ($isGitlabProject && ($gitlabProjectId == 0)) {
            $filteredValues['gitlab_project_id'] = null;
        }
        if ($isGitlabProject == 0 && $gitlabProjectId != null) {
            $filteredValues['is_gitlab_project'] = 0;
            $filteredValues['gitlab_project_id'] = null;
            $filteredValues['show_gitlab_project_issues'] = 0;
            $filteredValues['use_gitlab_project_readme'] = 0;
        }

        if (isset($filteredValues['image_small_upload'])) {
            try {
                $uploadedSmallImage = $image_repository->saveImage($filteredValues[self::IMAGE_SMALL_UPLOAD]);
                $filteredValues['image_small'] = $uploadedSmallImage ? $uploadedSmallImage : $filteredValues['image_small'];
            } catch (Exception $e) {
                $GLOBALS['ocs_log']->err(__METHOD__ . ' - ERROR upload productPicture - ' . print_r($e, true));
            }
        }

        $updateValues = $filteredValues;

        //remove not needed elements
        unset($updateValues['tags']);
        unset($updateValues['tagsuser']);
        unset($updateValues['preview']);
        unset($updateValues['image_small_upload']);
        unset($updateValues['cancel']);
        unset($updateValues['license_tag_id']);
        unset($updateValues['is_original_or_modification']);
        unset($updateValues['gallery']);
        unset($updateValues['upload_picture']);
        unset($updateValues['online_picture']);

        $updateValues['project_id'] = $this->_projectId;
        if ($isGitlabProject == 1 && $updateValues['show_gitlab_project_issues'] === null) {
            $updateValues['show_gitlab_project_issues'] = 0;
        }
        if ($isGitlabProject == 1 && $updateValues['use_gitlab_project_readme'] === null) {
            $updateValues['use_gitlab_project_readme'] = 0;
        }

        if (array_key_exists('is_gitlab_project', $updateValues) && $updateValues['is_gitlab_project'] === null) {
            unset($updateValues['is_gitlab_project']);
        }
        if (array_key_exists('show_gitlab_project_issues', $updateValues) && $updateValues['show_gitlab_project_issues'] === null) {
            unset($updateValues['show_gitlab_project_issues']);
        }
        if (array_key_exists('use_gitlab_project_readme', $updateValues) && $updateValues['use_gitlab_project_readme'] === null) {
            unset($updateValues['use_gitlab_project_readme']);
        }


        // save changes
        $projectModel->updateProject($this->_projectId, $updateValues);

        //update the gallery pics
        $galleryPicsUpload = isset($filteredValues['upload_picture']) ? $filteredValues['upload_picture'] : null;
        $galleryPicsOnline = isset($filteredValues['online_picture']) ? $filteredValues['online_picture'] : null;

        $pictureSources = array();
        $galleryPicsUploadSources = array();
        if (isset($galleryPicsOnline)) {
            $pictureSources = $galleryPicsOnline;
        }

        if (isset($galleryPicsUpload)) {
            $galleryPicsUploadSources = $image_repository->saveImages($galleryPicsUpload);
            $pictureSources = $pictureSources + $galleryPicsUploadSources;
        }

//        if (isset($galleryPicsOnline) && isset($galleryPicsUploadSources)) {
//            $pictureSources = array_merge_recursive($galleryPicsOnline, $galleryPicsUploadSources);
//        }
        $projectModel->updateGalleryPictures($this->_projectId, $pictureSources);

        //If there is no Logo, we take the 1. gallery pic
        if ((!isset($projectData->image_small) || $projectData->image_small == '') && count($pictureSources) > 0) {
            $projectData->image_small = $pictureSources[0];
            //20180219 ronald: we set the changed_at only by new files or new updates
            //$projectData->changed_at = new \Laminas\Db\Sql\Expression('NOW()');
            $projectTable->update(array('project_id' => $this->_projectId, 'image_small' => $pictureSources[0]));
        }

        $modelTags->processTagProductOriginalOrModification(
            $this->_projectId, $filteredValues['is_original_or_modification']
        );


        if ($filteredValues['tagsuser']) {
            $modelTags->processTagsUser(
                $this->_projectId, implode(',', $filteredValues['tagsuser']), TagConst::TAG_TYPE_PROJECT
            );
        } else {
            $modelTags->processTagsUser($this->_projectId, null, TagConst::TAG_TYPE_PROJECT);
        }

        ActivityLogService::logActivity(
            $this->_projectId, $this->_projectId, $this->_authMember->member_id, ActivityLogService::PROJECT_EDITED, $projectData->getArrayCopy()
        );

        // ppload
        $this->processPploadId($projectData);

        try {
            if (100 < $this->_authMember->roleId) {
                $spam = new SpamService($this->db);
                if ($spam->hasSpamMarkers($projectData->getArrayCopy())) {
                    $messages = json_encode($spam->getMessages());
                    $tableReportComments = $this->reportProductsRepository;
                    $tableReportComments->insert(
                        array(
                            'project_id'  => $projectData->project_id,
                            'reported_by' => 24,
                            'text'        => "System: automatic spam detection on product edit {$messages}",
                        )
                    );
                }
                SuspicionLogRepository::logProject($projectData, $this->_authMember, $this->getRequest());
            }
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err($e->getMessage());
        }

        return $this->redirect()->toUrl('/member/' . $projectData->member_id . '/products/');
    }

    public function getupdatesajaxAction()
    {
        $this->initVars();

        $this->view->setVariable('authMember', $this->_authMember);
        $tableProject = $this->projectUpdatesService;

        $updates = $tableProject->fetchProjectUpdates($this->_projectId);

        foreach ($updates as $key => $update) {
            $updates[$key]['title'] = HtmlPurifyService::purify($update['title']);
            $updates[$key]['text'] = BbcodeService::renderHtml(
                HtmlPurifyService::purify(
                    htmlentities(
                        $update['text'], ENT_QUOTES | ENT_IGNORE
                    )
                )
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
        $this->view->setVariable('product', $tableProject->fetchProductInfo($this->_projectId));
        if (false === isset($this->view->product)) {
            //throw new \Exception('This page does not exist', 404);
            $this->getResponse()->setStatusCode(404);

            return;
        }
        $this->view->setVariable('relatedProducts', $tableProject->fetchSimilarProjects($this->view->product, 6));
        $this->view->setVariable('supporter', $this->projectService->fetchProjectSupporter($this->_projectId));
        // $this->view->setVariable('product_views', $tableProject->fetchProjectViews($this->_projectId));

        $modelPlings = new PlingsRepository($this->db);
        $this->view->setVariable('comments', $modelPlings->getCommentsForProject($this->_projectId, 10));

        $tableMember = $this->memberService;
        $this->view->setVariable('member', $tableMember->fetchMemberData($this->view->product->member_id));

        $modelProjectUpdates = $this->projectUpdatesService;
        $this->view->setVariable('updates', $modelProjectUpdates->fetchProjectUpdates($this->_projectId));

        $tablePageViews = new StatPageViewsRepository($this->db);
        $remote = new RemoteAddress();
        $tablePageViews->savePageView(
            $this->_projectId, $remote->setUseProxy(true)->getIpAddress(), $this->_authMember->member_id
        );
    }

    public function deleteAction()
    {
        $this->initVars();

        $memberId = (int)$this->getParam('m');

        if ((empty($this->_authMember->member_id)) or (empty($memberId)) or ($this->_authMember->member_id != $memberId)) {
            $this->redirect()->toUrl('/member/' . $memberId . '/products');

            return;
        }

        $serviceProduct = $this->projectService;
        $serviceProduct->setDeleted($this->_authMember->member_id, $this->_projectId);

        $tableProduct = $this->projectRepository;
        $product = $tableProduct->findById($this->_projectId);

        // ppload
        // Delete collection
        if ($product->ppload_collection_id) {
            $pploadApi = new PploadApi(
                array(
                    'apiUri'   => PPLOAD_API_URI,
                    'clientId' => PPLOAD_CLIENT_ID,
                    'secret'   => PPLOAD_SECRET,
                )
            );

            $collectionResponse = $pploadApi->deleteCollection($product->ppload_collection_id);
        }

        //TODO: remove from Searchresults?

        $activityLog = new ActivityLogRepository($this->db);
        $activityLog->writeActivityLog(
            $this->_projectId, $this->_authMember->member_id, ActivityLogRepository::PROJECT_DELETED, $product->getArrayCopy()
        );

        //$resultJson = new JsonModel(array('status' => 'success'));
        //return $resultJson;
        return $this->redirect()->toRoute(
            'application_user', [
                                  'action'   => 'products',
                                  'username' => $this->_authMember->username,
                              ]
        );
    }

    public function unpublishAction()
    {
        $this->initVars();

        $memberId = (int)$this->getParam('m');

        if ((empty($this->_authMember->member_id)) or (empty($memberId)) or ($this->_authMember->member_id != $memberId)) {
            return;
        }

        $serviceProduct = $this->projectService;
        $serviceProduct->setInActive($this->_projectId, $memberId);

        $tableProduct = $this->projectRepository;
        $product = $tableProduct->findById($this->_projectId);

        if (isset($product->type_id) && $product->type_id == $serviceProduct::PROJECT_TYPE_UPDATE) {
            $parentProduct = $tableProduct->findById($product->pid);
            $product->image_small = $parentProduct->image_small;
        }

        //TODO: remove from Searchresults?

        $activityLog = new ActivityLogRepository($this->db);
        $activityLog->writeActivityLog(
            $this->_projectId, $this->_authMember->member_id, ActivityLogRepository::PROJECT_UNPUBLISHED, $product->getArrayCopy()
        );

        // ppload
        if ($product->ppload_collection_id) {
            $pploadApi = new PploadApi(
                array(
                    'apiUri'   => PPLOAD_API_URI,
                    'clientId' => PPLOAD_CLIENT_ID,
                    'secret'   => PPLOAD_SECRET,
                )
            );
            // Update collection information
            $collectionRequest = array(
                'category' => $product->project_category_id,
            );

            $collectionResponse = $pploadApi->putCollection($product->ppload_collection_id, $collectionRequest);
        }

        return $this->redirect()->toRoute(
            'application_user', [
                                  'action'   => 'products',
                                  'username' => $this->_authMember->username,
                              ]
        );

    }

    public function publishAction()
    {
        $this->initVars();

        $memberId = (int)$this->getParam('m');

        if ((empty($this->_authMember->member_id)) or (empty($memberId)) or ($this->_authMember->member_id != $memberId)) {
            return;
        }

        $serviceProduct = $this->projectService;
        $serviceProduct->setActive($this->_authMember->member_id, $this->_projectId);

        $tableProduct = $this->projectRepository;
        $product = $tableProduct->findById($this->_projectId);

        if (isset($product->type_id) && $product->type_id == $serviceProduct::PROJECT_TYPE_UPDATE) {
            $parentProduct = $tableProduct->findById($product->pid);
            $product->image_small = $parentProduct->image_small;
        }

        $activityLog = new ActivityLogRepository($this->db);
        $activityLog->writeActivityLog(
            $this->_projectId, $this->_authMember->member_id, $activityLog::PROJECT_PUBLISHED, $product->getArrayCopy()
        );

        // ppload
        if ($product->ppload_collection_id) {
            $pploadApi = new PploadApi(
                array(
                    'apiUri'   => PPLOAD_API_URI,
                    'clientId' => PPLOAD_CLIENT_ID,
                    'secret'   => PPLOAD_SECRET,
                )
            );
            // Update collection information
            $collectionRequest = array(
                'category' => $product->project_category_id . '-published',
            );

            $collectionResponse = $pploadApi->putCollection($product->ppload_collection_id, $collectionRequest);
        }

        return $this->redirect()->toRoute(
            'application_user', [
                                  'action'   => 'products',
                                  'username' => $this->_authMember->username,
                              ]
        );
    }

    public function loadtagratingAction()
    {
        $this->initVars();

        $category_id = $this->getParam('gid');
        $model = $this->projectTagRatingsService;
        $ratingsLabel = $model->getCategoryTagRatings($category_id);
        $ratingsValue = null;
        if ($ratingsLabel != null && sizeof($ratingsLabel) > 0) {
            $ratingsValue = $model->getProjectTagRatings($this->_projectId);
        }

        return new JsonModel(
            array(
                'status' => 'ok',
                'labels' => $ratingsLabel,
                'values' => $ratingsValue,
            )
        );
    }

    public function votetagratingAction()
    {
        $this->initVars();

        $vote = $this->getParam('vote');
        $tag_id = $this->getParam('tid');
        $msg = $this->getParam('msg');
        if (strlen($msg) < 1) {
            return new JsonModel(
                array(
                    'status' => 'error',
                    'msg'    => 'Please add a comment.',
                )
            );
        }


        $model = $this->projectTagRatingsService;
        if ($this->_authMember->member_id) {
            $checkVote = $model->checkIfVote($this->_authMember->member_id, $this->_projectId, $tag_id);
            if (!$checkVote) {
                $model->doVote($this->_authMember->member_id, $this->_projectId, $tag_id, $vote, $msg);
            } else {
                if ($checkVote['vote'] == $vote) {
                    $model->removeVote($checkVote['tag_rating_id']);
                } else {
                    $model->removeVote($checkVote['tag_rating_id']);
                    $model->doVote($this->_authMember->member_id, $this->_projectId, $tag_id, $vote, $msg);
                }
            }

            $resultJson = new JsonModel(
                array(
                    'status' => 'ok',
                )
            );
        } else {
            $resultJson = new JsonModel(
                array(
                    'status' => 'error',
                    'msg'    => 'Login please',
                )
            );
        }

        return $resultJson;
    }

    public function loadfilesjsonAction()
    {
        $this->initVars();

        $modelProject = $this->projectService;
        $files = $modelProject->fetchFilesForProject($this->_projectId);
        $salt = PPLOAD_DOWNLOAD_SECRET;

        $productInfo = $this->projectRepository->fetchProductInfo($this->_projectId);
        $cat = $this->projectCategoryRepository->fetchActive($productInfo['project_category_id']);
        $xdg_type = null;
        if (count($cat)) {
            $xdg_type = $cat[0]['xdg_type'];
        }
        foreach ($files as &$file) {
            $timestamp = time() + 3600; // one hour valid

            $payload = array('id' => $file['id'], 'u' => $this->_authMember->member_id, 'lt' => 'filepreview');
            $pploadService = $this->pploadService;
            $url = $pploadService->createDownloadUrlJwt($file['collection_id'], $file['name'], $payload);
            $file['url'] = urlencode($url);
            $file['isInstall'] = $xdg_type && $file['ocs_compatible'];
        }

        return new JsonModel($files);
    }

    public function loadfirstfilejsonAction()
    {
        $this->initVars();

        $modelProject = $this->projectService;
        $files = $modelProject->fetchFilesForProject($this->_projectId);
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $file = $files[0];

        $timestamp = time() + 3600; // one hour valid

        $payload = array('id' => $file['id'], 'u' => $this->_authMember->member_id, 'lt' => 'filepreview');
        $pploadService = $this->pploadService;
        $url = $pploadService->createDownloadUrlJwt($file['collection_id'], $file['name'], $payload);

        $file['url'] = urlencode($url);

        return new JsonModel($file);
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

    public function verifycodeAction()
    {
        $this->initVars();

        if ($this->_request->isXmlHttpRequest()) {
            $tabProject = $this->projectRepository;
            $dataProject = $tabProject->findById($this->_projectId);
            $this->createTaskWebsiteOwnerVerification($dataProject);
            $this->view->setVariable('message', 'Your product page is stored for validation.');
        } else {
            $this->view->setVariable('message', 'This service is not available at the moment. Please try again later.');
        }

        return $this->view;
    }

    /**
     * @param $projectData
     *
     */
    protected function createTaskWebsiteOwnerVerification($projectData)
    {
        if (empty($projectData->link_1)) {
            return;
        }

        $checkAuthCode = $this->websiteProject;
        $authCode = $checkAuthCode->generateAuthCode(stripslashes($projectData->link_1));

        //@formatter:off
        JobBuilder::getJobBuilder()
                  ->withJobClass(CheckProjectWebsite::class)
                  ->withParam('project_id', $projectData->project_id)
                  ->withParam('websiteUrl', $projectData->link_1)
                  ->withParam('authCode', $authCode)
                  ->withParam('websiteProject', $this->websiteProject)
                  ->build();
        //@formatter:on
    }

    public function claimAction()
    {
        $this->initVars();

        $modelProduct = $this->projectRepository;
        $serviceProduct = $this->projectService;
        $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
        if ($productInfo['claimable'] != $modelProduct::PROJECT_CLAIMABLE) {
            throw new Exception('Method not available', 404);
        }
        $helperBuildProductUrl = new BuildProductUrl();
        if (empty($productInfo['claimed_by_member'])) {
            $serviceProduct->setClaimedByMember($this->_authMember->member_id, $this->_projectId);

            $claimMail = $this->mailer;
            $claimMail->setTemplate('tpl_mail_claim_product');
            $claimMail->setTemplateVar('sender', $this->_authMember->mail);
            $claimMail->setTemplateVar('productid', $productInfo['project_id']);
            $claimMail->setTemplateVar('producttitle', $productInfo['title']);
            $claimMail->setTemplateVar('userid', $this->_authMember->member_id);
            $claimMail->setTemplateVar('username', $this->_authMember->username);
            $claimMail->setTemplateVar('usermail', $this->_authMember->mail);
            $claimMail->setReceiverMail(array('contact@opendesktop.org'));
            $claimMail->send();

            $claimMailConfirm = $this->mailer;
            $claimMailConfirm->setTemplate('tpl_mail_claim_confirm');
            $claimMailConfirm->setTemplateVar('sender', 'contact@opendesktop.org');
            $claimMailConfirm->setTemplateVar('producttitle', $productInfo['title']);
            $claimMailConfirm->setTemplateVar(
                'productlink', 'http://' . $this->getRequest()->getUri()
                                                ->getHost() . $helperBuildProductUrl->buildProductUrl(
                                 $productInfo['project_id']
                             )
            );
            $claimMailConfirm->setTemplateVar('username', $this->_authMember->username);
            $claimMailConfirm->setReceiverMail($this->_authMember->mail);
            $claimMailConfirm->send();
        }

        //$this->_helper->viewRenderer('index');
        $this->view->setTemplate('index');
        $this->indexAction();
    }

    public function indexAction()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $this->initVars();

        if (empty($this->_projectId)) {
            $this->redirect()->toRoute('/explore');
        }

        $this->view->setVariable('isAdmin', $this->isAdmin);

        $isModerator = false;
        if ($this->currentUser()->isModerator()) {
            $isModerator = true;
        }
        $this->view->setVariable('isModerator', $isModerator);

        $this->view->setVariable('paramPageId', (int)$this->params('page', null));
        $this->view->setVariable('member_id', null);
        if (null != $this->_authMember && null != $this->_authMember->member_id) {
            $this->view->setVariable('member_id', $this->_authMember->member_id);
        }

        $modelProduct = $this->projectRepository;
        $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
      
        
        $productInfoArray = $productInfo;
        $productInfoArray['embed_code'] = '';

        if (empty($productInfo)) {
            $this->getResponse()->setStatusCode(404);

            return;
        }

        $productInfo = Util::arrayToObject($productInfo);
        $productInfo->embed_code = '';

        //Check if this is a collection
        if ($productInfo->type_id == $modelProduct::PROJECT_TYPE_COLLECTION) {
            $this->redirect()->toUrl('/c/' . $this->_projectId);
        }

        $this->view->setVariable('infoService', $this->infoService);
        $this->view->setVariable('projectCategoryService', $this->projectCategoryService);

        $sectionTable = $this->sectionService;
        $this->view->setVariable('sectionTable', $sectionTable);

        $sectionSupportTable = $this->sectionSupportRepository;
        $this->view->setVariable('sectionSupportTable', $sectionSupportTable);

        $tableCollection = $this->collectionService;
        $cntCollections = $tableCollection->countAllCollectionsForProject($productInfo->project_id);
        $this->view->setVariable('cntCollections', $cntCollections);

        $moreCollectionProducts = array();
        if ($cntCollections > 0) {
            $moreCollectionProducts = $tableCollection->fetchAllCollectionsForProject($productInfo->project_id, 6);
        }

        $this->view->setVariable('moreCollectionProducts', $moreCollectionProducts);

        $modelCloneService = $this->projectCloneService;
        $origins = $modelCloneService->fetchOrigins($productInfo->project_id);
        $this->view->setVariable('origins', $origins);

        $systemTags = $this->tagService->getTagsSystemList($productInfo->project_id);
        $this->view->setVariable('systemTags', $systemTags);

        if ($this->tagService->hasCategoryTagGroup($productInfo->project_category_id)) {
            $this->view->setVariable('hasCategoryTagGroup', 1);
            $tagsCategoryTagGroup = $this->tagService->getTagsFromCategoryTagGroup($productInfo->project_id);
            $this->view->setVariable('tagsCategoryTagGroup', $tagsCategoryTagGroup);
        }

        $isMod = $this->tagService->isProductModification($productInfo->project_id);
        $this->view->setVariable('isMod', $isMod);

        //Get EbookInfo, if this is a ebook
        if ($this->tagService->isProductEbook($productInfo->project_id)) {
            $authorTags = $this->tagService->getTagsEbookAuthor($productInfo->project_id);
            $editorTags = $this->tagService->getTagsEbookEditor($productInfo->project_id);
            $illuTags = $this->tagService->getTagsEbookIllustrator($productInfo->project_id);
            $transTags = $this->tagService->getTagsEbookTranslator($productInfo->project_id);
            $shelfTags = $this->tagService->getTagsEbookShelf($productInfo->project_id);
            $subjectTags = $this->tagService->getTagsEbookSubject($productInfo->project_id);
            $langTags = $this->tagService->getTagsEbookLanguage($productInfo->project_id);

            $this->view->setVariable('authorTags', $authorTags);
            $this->view->setVariable('editorTags', $editorTags);
            $this->view->setVariable('illuTags', $illuTags);
            $this->view->setVariable('transTags', $transTags);
            $this->view->setVariable('shelfTags', $shelfTags);
            $this->view->setVariable('subjectTags', $subjectTags);
            $this->view->setVariable('langTags', $langTags);

        }


        $catId = $productInfo->project_category_id;
        $this->view->setVariable('cat_id', $catId);
        $section = null;

        if ($catId && $catId > 0) {
            $section = $sectionTable->fetchSectionForCategory($catId);
        }

        if (!$section) {
            $section = array('name' => 'Test', 'section_id' => '1');
        }
        $this->view->setVariable('section', $section);

        $isSupporter = false;
        if ($this->_authMember) {
            $isSupporter = $sectionTable->isMemberSectionSupporter(
                $section['section_id'], $this->_authMember->member_id
            );
        }
        $this->view->setVariable('isSupporter', $isSupporter);

        $support = array();
        if ($isSupporter) {
            $support = $sectionSupportTable->fetchLatestSectionSupportForMember(
                $section['section_id'], $this->_authMember->member_id
            );
        }
        $this->view->setVariable('supportData', $support);

        $supporters = $this->infoService->getNewActiveSupportersForSection($section['section_id'], 9);
        $this->view->setVariable('sectionSupporters', $supporters);

        $relatedVariants = $modelCloneService->fetchRelatedProducts($productInfo->project_id);
        $this->view->setVariable('relatedVariants', $relatedVariants);

        $tableProject = $this->projectRepository;
        $moreProducts = $tableProject->fetchMoreProjects($productInfo, 6);
        $this->view->setVariable('moreProductsOfUser', $moreProducts);

        $moreProductsOfOtherUsers = $tableProject->fetchMoreProjectsOfOtherUsr($productInfo, 6);
        $this->view->setVariable('moreProductsOfOtherUsers', $moreProductsOfOtherUsers);

        $this->view->setVariable('product', $productInfo);
        $this->view->setVariable('headTitle', ($productInfo->title . ' - ' . $this->getHeadTitle()));

        $updatesTable = $this->projectUpdatesService;
        $updates = $updatesTable->fetchProjectUpdates($productInfo->project_id);
        $this->view->setVariable('updates', $updates);

        $tableProjectRatings = $this->projectRatingRepository;
        $this->view->setVariable('ratings', $tableProjectRatings->fetchRating($productInfo->project_id));
        $scoreOld = $tableProjectRatings->getScoreOld($productInfo->project_id);
        $this->view->setVariable('scoreOld', $scoreOld);

        $score2 = $tableProjectRatings->getScore($productInfo->project_id);
        $this->view->setVariable('score2', $score2);

        $this->view->setVariable('projectCategoryRepository', $this->projectCategoryRepository);

        $tagGroupsForCategory = $this->tagGroupService->fetchTagGroupsForCategory($productInfo->project_category_id);
        $this->view->setVariable('tagGroupsForCategory', $tagGroupsForCategory);

        $tagCloud = $this->tagService->getTopTagsPerCategory($productInfo->project_category_id);
        $this->view->setVariable('tagCloud', $tagCloud);

        $commentModel = $this->commentsRepository;
        $cntModeration = $commentModel->fetchCommentsWithTypeProjectCount(
            CommentsRepository::COMMENT_TYPE_MODERATOR, $productInfo->project_id
        );
        $this->view->setVariable('cntModeration', $cntModeration);

        $cntLicensing = $commentModel->fetchCommentsWithTypeProjectCount(
            CommentsRepository::COMMENT_TYPE_LICENSING, $productInfo->project_id
        );
        $this->view->setVariable('cntLicensing', $cntLicensing);

        $commentsTree = $commentModel->getCommentTreeForProject($productInfo->project_id);
        $commentsTree->setItemCountPerPage(25);
        $commentsTree->setCurrentPageNumber($this->view->getVariable('paramPageId'));
        $this->view->setVariable('commentsTree', $commentsTree);

        $commentsTreeModeration = $commentModel->getCommentTreeForProject(
            $productInfo->project_id, CommentsRepository::COMMENT_TYPE_MODERATOR
        );
        $commentsTreeModeration->setItemCountPerPage(500);
        $commentsTreeModeration->setCurrentPageNumber($this->view->getVariable('paramPageId'));
        $this->view->setVariable('commentsTreeModeration', $commentsTreeModeration);

        $commentsTreeLicensing = $commentModel->getCommentTreeForProject(
            $productInfo->project_id, CommentsRepository::COMMENT_TYPE_LICENSING
        );
        $commentsTreeLicensing->setItemCountPerPage(500);
        $commentsTreeLicensing->setCurrentPageNumber($this->view->getVariable('paramPageId'));
        $this->view->setVariable('commentsTreeLicensing', $commentsTreeLicensing);

        $tableProjectFollower = $this->projectFollowerRepository;
        $this->view->setVariable('likes', $tableProjectFollower->fetchLikesForProject($productInfo->project_id));

        $projectplings = $this->projectPlingsService;
        $this->view->setVariable('projectplings', $projectplings->fetchPlingsForProject($productInfo->project_id));

        $projectplingsTable = $this->projectPlingsRepository;
        $cntplings = $projectplingsTable->getPlingsAmount($productInfo->project_id);
        $this->view->setVariable('cntplings', $cntplings);

        $tmp = $projectplingsTable->getPling($productInfo->project_id, $this->_authMember->member_id);
        $isPlinged = false;
        if ($tmp) {
            $isPlinged = true;
        }
        $this->view->setVariable('isPlinged', $isPlinged);


        $projectsupport = $this->sectionSupportService;
        $this->view->setVariable(
            'projectaffiliates', $projectsupport->fetchAffiliatesForProject($productInfo->project_id)
        );

        $tagmodel = $this->tagService;
        $tagsuser = $tagmodel->getTagsUser($productInfo->project_id, TagsRepository::TAG_TYPE_PROJECT);
        $this->view->setVariable('tagsuser', $tagsuser);

        $isProductDeprecatedModerator = $tagmodel->isProductDeprecatedModerator($productInfo->project_id);
        $this->view->setVariable('isProductDeprecatedModerator', $isProductDeprecatedModerator);

        $this->view->setVariable(
            'productJson', Encoder::encode(CollectionService::cleanProductInfoForJson($productInfoArray))
        );

        $filesTable = $this->pploadFilesRepository;
        $countFiles = $filesTable->fetchFilesCntForProject($productInfo->ppload_collection_id);
        $this->view->setVariable('countFiles', $countFiles);

        $mediaViewsTable = new MediaViewsRepository($this->db);

        $countDownloadsToday = 0;
        $countMediaViewsAlltime = 0;
        if ($this->isAdmin) {
            //$countDownloadsToday = $filesTable->fetchCountDownloadsTodayForProject($productInfo->ppload_collection_id);
            //$countMediaViewsAlltime = $mediaViewsTable->fetchCountViewsForProjectAllTime($productInfo->project_id);
        }
        $this->view->setVariable('countDownloadsToday', $countDownloadsToday);
        $this->view->setVariable('countMediaViewsAlltime', $countMediaViewsAlltime);

        $countDownloadsTodayUk = $filesTable->fetchCountDownloadsTodayForProjectNew($productInfo->ppload_collection_id);
        $countMediaViewsToday = $mediaViewsTable->fetchCountViewsTodayForProject($productInfo->project_id);
        $this->view->setVariable('countDownloadsTodayUk', $countDownloadsTodayUk);
        $this->view->setVariable('countMediaViewsToday', $countMediaViewsToday);

        $serviceProduct = $this->projectService;
        // $product_views = $serviceProduct->fetchProjectViews($productInfo->project_id);
        // $this->view->setVariable('product_views', $product_views);

        $tagsArray = $tagmodel->getTagsArray(
            $productInfo->project_id, TagConst::TAG_TYPE_PROJECT, TagConst::TAG_GHNS_EXCLUDED_GROUPID
        );
        $isGhnsExcluded = false;
        if (isset($tagsArray) && (count($tagsArray) == 1)) {
            $isGhnsExcluded = true;
        }
        $this->view->setVariable('isGhnsExcluded', $isGhnsExcluded);


        //unused
        //$reportsTable = new ReportProductsRepository($this->db);
        //$repCount = $reportsTable->countSpamForProject($productInfo->project_id);

        $pics = $tableProject->getGalleryPictureSources($productInfo->project_id);
        $this->view->setVariable('pics', $pics);

        $cntSameSource = $serviceProduct->getCountSourceurl($productInfo->source_url);

        $this->view->setVariable('cntSameSource', $cntSameSource);

        if ($this->_authMember) {
            $this->view->setVariable(
                'ratingOfUser', $tableProjectRatings->getProjectRateForUser(
                $productInfo->project_id, $this->_authMember->member_id
            )
            );
            $this->view->setVariable('userRoleName', $this->_authMember->roleName);
        }

        $tagGroupFilter = $GLOBALS['ocs_config_store_taggroups'];

        if (!empty($tagGroupFilter)) {
            $filterArray = array();
            foreach ($tagGroupFilter as $tagGroupId) {
                $inputFilter = $this->getFilterTagFromCookie($tagGroupId);
                $filterArray[$tagGroupId] = $inputFilter;
            }
            $this->view->setVariable('tag_group_filter', $filterArray);
        }

        //create ppload download hash: secret + collection_id + expire-timestamp
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $collectionID = $this->view->getVariable('product')->ppload_collection_id;
        $timestamp = time() + 3600; // one hour valid
        //20181009 ronald: change hash from MD5 to SHA512
        //$hash = md5($salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying
        $hash = hash(
            'sha512', $salt . $collectionID . $timestamp
        ); // order isn't important at all... just do the same when verifying

        $this->view->setVariable('download_hash', $hash);
        $this->view->setVariable('download_timestamp', $timestamp);

        //@formatter:off
        if (!$this->currentUser()->isAdmin()
            and ($this->view->getVariable('product')->project_status != ProjectService::PROJECT_ACTIVE)
            and ($this->view->getVariable('product')->member_id != $this->currentUser()->member_id))
        {
            //throw new Exception('This page does not exist', 404);
            $this->getResponse()->setStatusCode(404);

            return;
        }
        //@formatter:on

        if ((APPLICATION_ENV != 'searchbotenv') and (false == SEARCHBOT_DETECTED)) {

            ViewsService::saveViewProduct($this->_projectId);

            if (DomainSwitch::get_ip_address()) {
                $tablePageViews = new StatPageViewsRepository($this->db);
                $tablePageViews->savePageView(
                    $this->_projectId, DomainSwitch::get_ip_address(), $this->_authMember->member_id
                );
            }
        }

        $fmodel = $this->pploadFilesRepository;

        $filesList = array();

        if (isset($this->view->getVariable('product')->ppload_collection_id)) {
            $files = $fmodel->fetchFilesForProject($this->view->getVariable('product')->ppload_collection_id);
            if (!empty($files)) {
                foreach ($files as $file) {
                    $timestamp = time() + 3600; // one hour valid
                    $hash = hash(
                        'sha512', $salt . $file['collection_id'] . $timestamp
                    ); // order isn't important at all... just do the same when verifying

                    $payload = array('id' => $file['id'], 'u' => $this->_authMember->member_id, 'lt' => 'filepreview');
                    $pploadService = $this->pploadService;
                    $url = $pploadService->createDownloadUrlJwt($file['collection_id'], $file['name'], $payload);

                    $file['url'] = urlencode($url);

                    //If this file is a video, we have to convert it for preview
                    /*if (!empty($file['type']) && in_array($file['type'], Backend_Commands_ConvertVideo::$VIDEO_FILE_TYPES) && empty($file['ppload_file_preview_id'])) {
                        $queue = Local_Queue_Factory::getQueue();
                        $command = new Backend_Commands_ConvertVideo($file['collection_id'], $file['id'], $file['type']);
                        $queue->send(serialize($command));
                    }*/
                    //@formatter:off
                    if (!empty($file['type'])
                        && in_array($file['type'], ConvertVideo::$VIDEO_FILE_TYPES)
                        && empty($file['ppload_file_preview_id']))
                    {
                        JobBuilder::getJobBuilder()
                                  ->withJobClass(ConvertVideo::class)
                                  ->withParam('collectionId', $file['collection_id'])
                                  ->withParam('fileId', $file['id'])
                                  ->withParam('fileType', $file['type'])
                                  ->build();
                    }
                    //@formatter:on
                    if (!empty($file['url_preview'])) {
                        $file['url_preview'] = urlencode($file['url_preview']);
                    }
                    if (!empty($file['url_thumb'])) {
                        $file['url_thumb'] = urlencode($file['url_thumb']);
                    }

                    $filesList[] = $file;
                }
            }
        }

        $this->view->setVariable('filesJson', Encoder::encode($filesList));


        //gitlab
        if ($this->view->getVariable('product')->is_gitlab_project) {
            $gitProject = $this->fetchGitlabProject($this->view->getVariable('product')->gitlab_project_id);
            if (null == $gitProject) {
                $this->view->getVariable('product')->is_gitlab_project = 0;
                $this->view->getVariable('product')->show_gitlab_project_issues = 0;
                $this->view->getVariable('product')->use_gitlab_project_readme = 0;
                $this->view->getVariable('product')->gitlab_project_id = null;
            } else {
                $this->view->setVariable('gitlab_project', $gitProject);

                //show issues?
                if ($this->view->getVariable('product')->show_gitlab_project_issues) {
                    $issues = $this->fetchGitlabProjectIssues($this->view->getVariable('product')->gitlab_project_id);
                    $this->view->setVariable('gitlab_project_issues', $issues);
                    $this->view->setVariable(
                        'gitlab_project_issues_url', $this->view->getVariable('gitlab_project')->web_url . '/issues/'
                    );
                }

                //show readme.md?
                if ($this->view->getVariable('product')->use_gitlab_project_readme && null != $this->view->getVariable(
                        'gitlab_project'
                    )->readme_url) {
                    $config = $this->config->settings->server->opencode;
                    $token = $config->private_token;
                    $sudo = $config->user_sudo;
                    $agent = $config->user_agent;
                    $readme = $this->view->getVariable(
                            'gitlab_project'
                        )->web_url . '/raw/master/README.md?inline=false';

                    $httpClient = new Client($readme, array('keepalive' => true, 'strictredirects' => true));
                    $httpClient->resetParameters();
                    $httpClient->setUri($readme);
                    //$httpClient->setHeaders(array('Private-Token' => $config->private_token, 'Sudo' => $config->user_sudo, 'User-Agent' => $config->user_agent));
                    $httpClient->setHeaders(
                        array(
                            'Private-Token' => $token,
                            'Sudo'          => $sudo,
                            'User-Agent'    => $agent,
                        )
                    );
                    $httpClient->setMethod('GET');

                    $response = $httpClient->send();

                    $body = $response->getBody();
                    if (empty($body)) {
                        return array();
                    }

                    $Parsedown = new Parsedown();
                    $this->view->readme = $Parsedown->text($body);

                } else {
                    $this->view->readme = null;
                }
            }
        }

        // if admin calc postion
        if ($this->isAdmin) {
            //  $position = $this->infoService->findProductPostion($this->_projectId);
            //  $this->view->setVariable('position',$position);
            $this->view->setVariable('position', '');
        }

        // products related
        if ($this->isAdmin) {   // execute only for admin.
            $pc = $this->projectCloneService;
            $cntRelatedProducts = 0;
            $ancesters = $pc->fetchAncestorsIds($this->_projectId);
            //$siblings = $pc->fetchSiblings($this->_projectId);
            //$parents = $pc->fetchParentIds($this->_projectId);
            if ($ancesters && strlen($ancesters) > 0) {
                $parents = $pc->fetchParentLevelRelatives($this->_projectId);
            } else {
                $parents = $pc->fetchParentIds($this->_projectId);
            }
            if ($parents && strlen($parents) > 0) {
                $siblings = $pc->fetchSiblingsLevelRelatives($parents, $this->_projectId);
            } else {
                $siblings = null;
            }
            $childrens = $pc->fetchChildrensIds($this->_projectId);
            $childrens2 = null;
            $childrens3 = null;
            if (strlen($childrens) > 0) {
                $childrens2 = $pc->fetchChildrensChildrenIds($childrens);
                if (strlen($childrens2) > 0) {
                    $childrens3 = $pc->fetchChildrensChildrenIds($childrens2);
                }
            }

            $this->view->setVariable('related_ancesters', null);
            $this->view->setVariable('related_siblings', null);
            $this->view->setVariable('related_parents', null);
            $this->view->setVariable('related_children', null);
            // $this->view->related_children2 = null;
            // $this->view->related_children3 = null;
            if ($ancesters && strlen($ancesters) > 0) {
                $pts = $serviceProduct->fetchProjects($ancesters);
                $this->view->setVariable('related_ancesters', sizeof($pts) == 0 ? null : $pts);
                $cntRelatedProducts += sizeof($pts);
            }
            if ($siblings && strlen($siblings) > 0) {
                $pts = $serviceProduct->fetchProjects($siblings);
                $this->view->setVariable('related_siblings', sizeof($pts) == 0 ? null : $pts);
                $cntRelatedProducts += sizeof($pts);
            }
            if ($parents && strlen($parents) > 0) {
                $pts = $serviceProduct->fetchProjects($parents);
                $this->view->setVariable('related_parents', sizeof($pts) == 0 ? null : $pts);
                $cntRelatedProducts += sizeof($pts);
            }
            if ($childrens && strlen($childrens) > 0) {
                $pts = $serviceProduct->fetchProjects($childrens);
                $this->view->setVariable('related_children', sizeof($pts) == 0 ? null : $pts);
                $cntRelatedProducts += sizeof($pts);
            }

            $this->view->setVariable('cntRelatedProducts', $cntRelatedProducts);
            //$this->view->setVariable('cntRelatedProducts', 0);
        } else {
            $this->view->setVariable('cntRelatedProducts', 0);
        }

        $storeConfig = $this->view->getVariable('ocs_store')->config;

        // about store/category
        $catabout = $this->getCategoryAbout($productInfo->project_category_id);
        $storeabout = $this->getStoreAbout($GLOBALS['ocs_store']->config->store_id);
        if ($catabout) {
            $this->view->setVariable('catabout', $catabout);
        }
        if ($storeabout) {
            $this->view->setVariable('storeabout', $storeabout);
        }

        //TODO
        // todo what????
        if ($storeConfig->layout_pagedetail && $storeConfig->isRenderReact()) {
            $this->initJsonForReact();
            $this->view->setTemplate('index-react');
        }

        return $this->view;

    }

    private function getFilterTagFromCookie($group)
    {
        $config = $this->config;
        $cookieName = $config->settings->session->filter_browse_original . $group;

        return isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : null;
    }

    private function fetchGitlabProject($gitProjectId)
    {

        $gitlab = $this->gitlab;

        try {
            $gitProject = $gitlab->getProject($gitProjectId);
        } catch (Exception $exc) {
            //Project is gone
            $modelProject = $this->projectService;
            $modelProject->updateProject(
                $this->_projectId, array(
                                     'is_gitlab_project'          => 0,
                                     'gitlab_project_id'          => null,
                                     'show_gitlab_project_issues' => 0,
                                     'use_gitlab_project_readme'  => 0,
                                 )
            );
            $gitProject = null;
        }

        return $gitProject;
    }

    private function fetchGitlabProjectIssues($gitProjectId)
    {
        $gitlab = $this->gitlab;

        try {
            $gitProjectIssues = $gitlab->getProjectIssues($gitProjectId);
        } catch (Exception $exc) {
            //Project is gone
            $modelProject = $this->projectService;
            $modelProject->updateProject(
                $this->_projectId, array(
                                     'is_gitlab_project'          => 0,
                                     'gitlab_project_id'          => null,
                                     'show_gitlab_project_issues' => 0,
                                     'use_gitlab_project_readme'  => 0,
                                 )
            );

            $gitProjectIssues = null;
        }


        return $gitProjectIssues;
    }

    public function initJsonForReact()
    {
        $modelProduct = $this->projectRepository;
        $productInfo = $this->view->getVariable('product');
        $isSupporter = false;
        //$this->view->setVariable('product', $productInfo);
        if (empty($productInfo)) {
            //throw new Exception('This page does not exist', 404);
            $this->getResponse()->setStatusCode(404);

            return;
        }

        if (null != $this->_authMember) {
            $this->view->setVariable(
                'authMemberJson', Encoder::encode(MemberService::cleanAuthMemberForJson($this->_authMember))
            );
        }

        $helpAddDefaultScheme = new AddDefaultScheme();

        $productInfo->title = HtmlPurifyService::purify($productInfo->title);
        $productInfo->description = BbcodeService::renderHtml(HtmlPurifyService::purify($productInfo->description));
        $productInfo->version = HtmlPurifyService::purify($productInfo->version);
        $productInfo->link_1 = HtmlPurifyService::purify(
            $helpAddDefaultScheme->addDefaultScheme($productInfo->link_1), HtmlPurifyService::ALLOW_URL
        );
        $productInfo->source_url = HtmlPurifyService::purify(
            $productInfo->source_url, HtmlPurifyService::ALLOW_URL
        );
        $productInfo->facebook_code = HtmlPurifyService::purify(
            $productInfo->facebook_code, HtmlPurifyService::ALLOW_URL
        );
        $productInfo->twitter_code = HtmlPurifyService::purify(
            $productInfo->twitter_code, HtmlPurifyService::ALLOW_URL
        );
        $productInfo->google_code = HtmlPurifyService::purify(
            $productInfo->google_code, HtmlPurifyService::ALLOW_URL
        );
        $this->view->setVariable('product', $productInfo);
        $this->view->setVariable(
            'productJson', Encoder::encode(CollectionService::cleanProductInfoForJson($productInfo))
        );

        $fmodel = $this->pploadFilesRepository;
        $files = $fmodel->fetchFilesForProject($productInfo->ppload_collection_id);

        $salt = PPLOAD_DOWNLOAD_SECRET;

        $filesList = array();

        foreach ($files as $file) {
            $timestamp = time() + 3600; // one hour valid

            $payload = array('id' => $file['id'], 'u' => $this->_authMember->member_id, 'lt' => 'filepreview');
            $pploadService = $this->pploadService;
            $url = $pploadService->createDownloadUrlJwt($file['collection_id'], $file['name'], $payload);

            $file['url'] = urlencode($url);
            $filesList[] = $file;
        }

        $this->view->setVariable('filesJson', Encoder::encode($filesList));
        $this->view->setVariable(
            'filesCntJson', Encoder::encode(
            $fmodel->fetchFilesCntForProject($this->view->product->ppload_collection_id)
        )
        );

        $tableProjectUpdates = $this->projectUpdatesService;
        $this->view->setVariable(
            'updatesJson', Encoder::encode($tableProjectUpdates->fetchProjectUpdates($this->_projectId))
        );
        $tableProjectRatings = $this->projectRatingRepository;
        $ratings = $tableProjectRatings->fetchRating($this->_projectId);
        $cntRatingsActive = 0;
        foreach ($ratings as $p) {
            if ($p['rating_active'] == 1) {
                $cntRatingsActive = $cntRatingsActive + 1;
            }
        }
        $this->view->setVariable('ratingsJson', Encoder::encode($ratings));
        $this->view->setVariable('cntRatingsActiveJson', Encoder::encode($cntRatingsActive));

        $identity = $this->_authMember->read();
        if ($this->_authMember) {
            $ratingOfUserJson = $tableProjectRatings->getProjectRateForUser($this->_projectId, $identity->member_id);
            $this->view->setVariable('ratingOfUserJson', Encoder::encode($ratingOfUserJson));
        } else {
            $this->view->setVariable('ratingOfUserJson', Encoder::encode(null));
        }
        $this->view->setVariable('isSupporter', $isSupporter);

        $section = $this->sectionService->fetchSectionForCategory($productInfo->project_category_id);
        $support = array();
        if ($isSupporter) {
            $support = $this->sectionSupportRepository->fetchLatestSectionSupportForMember(
                $section['section_id'], $this->_authMember->member_id
            );
        }
        $this->view->setVariable('supportData', $support);

        $supporters = $this->infoService->getNewActiveSupportersForSection($section['section_id'], 9);
        $this->view->setVariable('sectionSupporters', $supporters);

        /** @var ProjectCloneService $modelCloneService */
        $modelCloneService = $this->getEvent()->getApplication()->getServiceManager()->get(ProjectCloneService::class);
        $relatedVariants = $modelCloneService->fetchRelatedProducts($productInfo->project_id);
        $this->view->setVariable('relatedVariants', $relatedVariants);

        $tableProject = $this->projectRepository;
        $moreProducts = $tableProject->fetchMoreProjects($productInfo, 6);
        $this->view->setVariable('moreProductsOfUser', $moreProducts);

        $moreProductsOfOtherUsers = $tableProject->fetchMoreProjectsOfOtherUsr($productInfo, 6);
        $this->view->setVariable('moreProductsOfOtherUsers', $moreProductsOfOtherUsers);

        $this->view->setVariable('product', $productInfo);
        $this->view->setVariable('headTitle', ($productInfo->title . ' - ' . $this->getHeadTitle()));

        $updatesTable = $this->projectUpdatesService;
        $updates = $updatesTable->fetchProjectUpdates($productInfo->project_id);
        $this->view->setVariable('updates', $updates);

        $tableProjectRatings = $this->projectRatingRepository;
        $this->view->setVariable('ratings', $tableProjectRatings->fetchRating($productInfo->project_id));
        $scoreOld = $tableProjectRatings->getScoreOld($productInfo->project_id);
        $this->view->setVariable('scoreOld', $scoreOld);

        $score2 = $tableProjectRatings->getScore($productInfo->project_id);
        $this->view->setVariable('score2', $score2);

        $this->view->setVariable('projectCategoryRepository', $this->projectCategoryRepository);

        $tagGroupsForCategory = $this->tagGroupService->fetchTagGroupsForCategory($productInfo->project_category_id);
        $this->view->setVariable('tagGroupsForCategory', $tagGroupsForCategory);


        $commentModel = $this->commentsRepository;
        $cntModeration = $commentModel->fetchCommentsWithTypeProjectCount(
            CommentsRepository::COMMENT_TYPE_MODERATOR, $productInfo->project_id
        );
        $this->view->setVariable('cntModeration', $cntModeration);

        $cntLicensing = $commentModel->fetchCommentsWithTypeProjectCount(
            CommentsRepository::COMMENT_TYPE_LICENSING, $productInfo->project_id
        );
        $this->view->setVariable('cntLicensing', $cntLicensing);

        $commentsTree = $commentModel->getCommentTreeForProject($productInfo->project_id);
        $commentsTree->setItemCountPerPage(25);
        $commentsTree->setCurrentPageNumber($this->view->getVariable('paramPageId'));
        $this->view->setVariable('commentsTree', $commentsTree);

        $commentsTreeModeration = $commentModel->getCommentTreeForProject(
            $productInfo->project_id, CommentsRepository::COMMENT_TYPE_MODERATOR
        );
        $commentsTreeModeration->setItemCountPerPage(500);
        $commentsTreeModeration->setCurrentPageNumber($this->view->getVariable('paramPageId'));
        $this->view->setVariable('commentsTreeModeration', $commentsTreeModeration);

        $commentsTreeLicensing = $commentModel->getCommentTreeForProject(
            $productInfo->project_id, CommentsRepository::COMMENT_TYPE_LICENSING
        );
        $commentsTreeLicensing->setItemCountPerPage(500);
        $commentsTreeLicensing->setCurrentPageNumber($this->view->getVariable('paramPageId'));
        $this->view->setVariable('commentsTreeLicensing', $commentsTreeLicensing);

        $tableProjectFollower = $this->projectFollowerRepository;
        $this->view->setVariable('likes', $tableProjectFollower->fetchLikesForProject($productInfo->project_id));

        $projectplings = $this->projectPlingsService;
        $this->view->setVariable('projectplings', $projectplings->fetchPlingsForProject($productInfo->project_id));

        $projectplingsTable = $this->projectPlingsRepository;
        $cntplings = $projectplingsTable->getPlingsAmount($productInfo->project_id);
        $this->view->setVariable('cntplings', $cntplings);

        $tmp = $projectplingsTable->getPling($productInfo->project_id, $this->_authMember->member_id);
        $isPlinged = false;
        if ($tmp) {
            $isPlinged = true;
        }
        $this->view->setVariable('isPlinged', $isPlinged);


        $projectsupport = $this->sectionSupportService;
        $this->view->setVariable(
            'projectaffiliates', $projectsupport->fetchAffiliatesForProject($productInfo->project_id)
        );

        $tagmodel = $this->tagService;
        $tagsuser = $tagmodel->getTagsUser($productInfo->project_id, TagsRepository::TAG_TYPE_PROJECT);
        $this->view->setVariable('tagsuser', $tagsuser);

        $isProductDeprecatedModerator = $tagmodel->isProductDeprecatedModerator($productInfo->project_id);
        $this->view->setVariable('isProductDeprecatedModerator', $isProductDeprecatedModerator);

        $this->view->setVariable(
            'productJson', Encoder::encode(CollectionService::cleanProductInfoForJson($productInfo))
        );

        $filesTable = $this->pploadFilesRepository;
        $countFiles = $filesTable->fetchFilesCntForProject($productInfo->ppload_collection_id);
        $this->view->setVariable('countFiles', $countFiles);

        $mediaViewsTable = new MediaViewsRepository($this->db);

        $countDownloadsToday = 0;
        $countMediaViewsAlltime = 0;
        if ($this->isAdmin) {
            $countDownloadsToday = $filesTable->fetchCountDownloadsTodayForProject($productInfo->ppload_collection_id);
            $countMediaViewsAlltime = $mediaViewsTable->fetchCountViewsForProjectAllTime($productInfo->project_id);
        }
        $this->view->setVariable('countDownloadsToday', $countDownloadsToday);
        $this->view->setVariable('countMediaViewsAlltime', $countMediaViewsAlltime);

        $countDownloadsTodayUk = $filesTable->fetchCountDownloadsTodayForProjectNew($productInfo->ppload_collection_id);
        $countMediaViewsToday = $mediaViewsTable->fetchCountViewsTodayForProject($productInfo->project_id);
        $this->view->setVariable('countDownloadsTodayUk', $countDownloadsTodayUk);
        $this->view->setVariable('countMediaViewsToday', $countMediaViewsToday);

        $serviceProduct = $this->projectService;
        // $product_views = $serviceProduct->fetchProjectViews($productInfo->project_id);
        // $this->view->setVariable('product_views', $product_views);

        $tagsArray = $tagmodel->getTagsArray(
            $productInfo->project_id, TagConst::TAG_TYPE_PROJECT, TagConst::TAG_GHNS_EXCLUDED_GROUPID
        );
        $isGhnsExcluded = false;
        if (isset($tagsArray) && (count($tagsArray) == 1)) {
            $isGhnsExcluded = true;
        }
        $this->view->setVariable('isGhnsExcluded', $isGhnsExcluded);


        //unused
        //$reportsTable = new ReportProductsRepository($this->db);
        //$repCount = $reportsTable->countSpamForProject($productInfo->project_id);

        $pics = $tableProject->getGalleryPictureSources($productInfo->project_id);
        $this->view->setVariable('pics', $pics);

        $cntSameSource = $serviceProduct->getCountSourceurl($productInfo->source_url);

        $this->view->setVariable('cntSameSource', $cntSameSource);

        if ($this->_authMember) {
            $this->view->setVariable(
                'ratingOfUser', $tableProjectRatings->getProjectRateForUser(
                $productInfo->project_id, $this->_authMember->member_id
            )
            );
            $this->view->setVariable('userRoleName', $this->_authMember->roleName);
        }

        $tagGroupFilter = $GLOBALS['ocs_config_store_taggroups'];

        if (!empty($tagGroupFilter)) {
            $filterArray = array();
            foreach ($tagGroupFilter as $tagGroupId) {
                $inputFilter = $this->getFilterTagFromCookie($tagGroupId);
                $filterArray[$tagGroupId] = $inputFilter;
            }
            $this->view->setVariable('tag_group_filter', $filterArray);
        }

        //create ppload download hash: secret + collection_id + expire-timestamp
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $collectionID = $this->view->getVariable('product')->ppload_collection_id;
        $timestamp = time() + 3600; // one hour valid
        //20181009 ronald: change hash from MD5 to SHA512
        //$hash = md5($salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying
        $hash = hash(
            'sha512', $salt . $collectionID . $timestamp
        ); // order isn't important at all... just do the same when verifying

        $this->view->setVariable('download_hash', $hash);
        $this->view->setVariable('download_timestamp', $timestamp);

        //@formatter:off
        if (!$this->currentUser()->isAdmin()
            and ($this->view->getVariable('product')->project_status != ProjectService::PROJECT_ACTIVE)
            and ($this->view->getVariable('product')->member_id != $this->currentUser()->member_id))
        {
            //throw new Exception('This page does not exist', 404);
            $this->getResponse()->setStatusCode(404);

            return;
        }
        //@formatter:on

        if ((APPLICATION_ENV != 'searchbotenv') and (false == SEARCHBOT_DETECTED)) {

            ViewsService::saveViewProduct($this->_projectId);

            if (DomainSwitch::get_ip_address()) {
                $tablePageViews = new StatPageViewsRepository($this->db);
                $tablePageViews->savePageView(
                    $this->_projectId, DomainSwitch::get_ip_address(), $this->_authMember->member_id
                );
            }
        }

        $fmodel = $this->pploadFilesRepository;

        $filesList = array();

        if (isset($this->view->getVariable('product')->ppload_collection_id)) {
            $files = $fmodel->fetchFilesForProject($this->view->getVariable('product')->ppload_collection_id);
            if (!empty($files)) {
                foreach ($files as $file) {
                    $timestamp = time() + 3600; // one hour valid
                    $hash = hash(
                        'sha512', $salt . $file['collection_id'] . $timestamp
                    ); // order isn't important at all... just do the same when verifying

                    $payload = array('id' => $file['id'], 'u' => $this->_authMember->member_id, 'lt' => 'filepreview');
                    $pploadService = $this->pploadService;
                    $url = $pploadService->createDownloadUrlJwt($file['collection_id'], $file['name'], $payload);

                    $file['url'] = urlencode($url);

                    //If this file is a video, we have to convert it for preview
                    /*if (!empty($file['type']) && in_array($file['type'], Backend_Commands_ConvertVideo::$VIDEO_FILE_TYPES) && empty($file['ppload_file_preview_id'])) {
                        $queue = Local_Queue_Factory::getQueue();
                        $command = new Backend_Commands_ConvertVideo($file['collection_id'], $file['id'], $file['type']);
                        $queue->send(serialize($command));
                    }*/
                    //@formatter:off
                    if (!empty($file['type'])
                        && in_array($file['type'], ConvertVideo::$VIDEO_FILE_TYPES)
                        && empty($file['ppload_file_preview_id']))
                    {
                        JobBuilder::getJobBuilder()
                                  ->withJobClass(ConvertVideo::class)
                                  ->withParam('collectionId', $file['collection_id'])
                                  ->withParam('fileId', $file['id'])
                                  ->withParam('fileType', $file['type'])
                                  ->build();
                    }
                    //@formatter:on
                    if (!empty($file['url_preview'])) {
                        $file['url_preview'] = urlencode($file['url_preview']);
                    }
                    if (!empty($file['url_thumb'])) {
                        $file['url_thumb'] = urlencode($file['url_thumb']);
                    }

                    $filesList[] = $file;
                }
            }
        }

        $this->view->setVariable('filesJson', Encoder::encode($filesList));


        //gitlab
        if ($this->view->getVariable('product')->is_gitlab_project) {
            $gitProject = $this->fetchGitlabProject($this->view->getVariable('product')->gitlab_project_id);
            if (null == $gitProject) {
                $this->view->getVariable('product')->is_gitlab_project = 0;
                $this->view->getVariable('product')->show_gitlab_project_issues = 0;
                $this->view->getVariable('product')->use_gitlab_project_readme = 0;
                $this->view->getVariable('product')->gitlab_project_id = null;
            } else {
                $this->view->setVariable('gitlab_project', $gitProject);

                //show issues?
                if ($this->view->getVariable('product')->show_gitlab_project_issues) {
                    $issues = $this->fetchGitlabProjectIssues($this->view->getVariable('product')->gitlab_project_id);
                    $this->view->setVariable('gitlab_project_issues', $issues);
                    $this->view->setVariable(
                        'gitlab_project_issues_url', $this->view->getVariable('gitlab_project')->web_url . '/issues/'
                    );
                }

                //show readme.md?
                if ($this->view->getVariable('product')->use_gitlab_project_readme && null != $this->view->getVariable(
                        'gitlab_project'
                    )->readme_url) {
                    $config = $this->config->settings->server->opencode;
                    $token = $config->private_token;
                    $sudo = $config->user_sudo;
                    $agent = $config->user_agent;
                    $readme = $this->view->getVariable(
                            'gitlab_project'
                        )->web_url . '/raw/master/README.md?inline=false';

                    $httpClient = new Client($readme, array('keepalive' => true, 'strictredirects' => true));
                    $httpClient->resetParameters();
                    $httpClient->setUri($readme);
                    //$httpClient->setHeaders(array('Private-Token' => $config->private_token, 'Sudo' => $config->user_sudo, 'User-Agent' => $config->user_agent));
                    $httpClient->setHeaders(
                        array(
                            'Private-Token' => $token,
                            'Sudo'          => $sudo,
                            'User-Agent'    => $agent,
                        )
                    );
                    $httpClient->setMethod('GET');

                    $response = $httpClient->send();

                    $body = $response->getBody();
                    if (empty($body)) {
                        return array();
                    }

                    $Parsedown = new Parsedown();
                    $this->view->readme = $Parsedown->text($body);

                } else {
                    $this->view->readme = null;
                }
            }
        }

        // products related
        if ($this->isAdmin) {   // execute only for admin.
            $pc = $this->projectCloneService;
            $cntRelatedProducts = 0;
            $ancesters = $pc->fetchAncestorsIds($this->_projectId);
            //$siblings = $pc->fetchSiblings($this->_projectId);
            //$parents = $pc->fetchParentIds($this->_projectId);
            if ($ancesters && strlen($ancesters) > 0) {
                $parents = $pc->fetchParentLevelRelatives($this->_projectId);
            } else {
                $parents = $pc->fetchParentIds($this->_projectId);
            }
            if ($parents && strlen($parents) > 0) {
                $siblings = $pc->fetchSiblingsLevelRelatives($parents, $this->_projectId);
            } else {
                $siblings = null;
            }
            $childrens = $pc->fetchChildrensIds($this->_projectId);
            $childrens2 = null;
            $childrens3 = null;
            if (strlen($childrens) > 0) {
                $childrens2 = $pc->fetchChildrensChildrenIds($childrens);
                if (strlen($childrens2) > 0) {
                    $childrens3 = $pc->fetchChildrensChildrenIds($childrens2);
                }
            }

            $this->view->setVariable('related_ancesters', null);
            $this->view->setVariable('related_siblings', null);
            $this->view->setVariable('related_parents', null);
            $this->view->setVariable('related_children', null);
            // $this->view->related_children2 = null;
            // $this->view->related_children3 = null;
            if ($ancesters && strlen($ancesters) > 0) {
                $pts = $serviceProduct->fetchProjects($ancesters);
                $this->view->setVariable('related_ancesters', sizeof($pts) == 0 ? null : $pts);
                $cntRelatedProducts += sizeof($pts);
            }
            if ($siblings && strlen($siblings) > 0) {
                $pts = $serviceProduct->fetchProjects($siblings);
                $this->view->setVariable('related_siblings', sizeof($pts) == 0 ? null : $pts);
                $cntRelatedProducts += sizeof($pts);
            }
            if ($parents && strlen($parents) > 0) {
                $pts = $serviceProduct->fetchProjects($parents);
                $this->view->setVariable('related_parents', sizeof($pts) == 0 ? null : $pts);
                $cntRelatedProducts += sizeof($pts);
            }
            if ($childrens && strlen($childrens) > 0) {
                $pts = $serviceProduct->fetchProjects($childrens);
                $this->view->setVariable('related_children', sizeof($pts) == 0 ? null : $pts);
                $cntRelatedProducts += sizeof($pts);
            }

            $this->view->setVariable('cntRelatedProducts', $cntRelatedProducts);
        } else {
            $this->view->setVariable('cntRelatedProducts', 0);
        }

        $storeConfig = $this->view->getVariable('ocs_store')->config;

        //TODO
        // todo what????
        if ($storeConfig->layout_pagedetail && $storeConfig->isRenderReact()) {
            $this->initJsonForReact();
            $this->view->setTemplate('index-react');
        }

        return $this->view;

    }

    public function loadAdminInfoAction()
    {
        $this->initVars();
        $this->view->setTerminal(true);
        $modelProduct = $this->projectRepository;
        $productInfo = $modelProduct->fetchProductInfo($this->_projectId);
        $productInfo = Util::arrayToObject($productInfo);

        if ($this->isAdmin) {
            $countDownloadsToday = $this->pploadFilesRepository->fetchCountDownloadsTodayForProject($productInfo->ppload_collection_id);
            $countMediaViewsAlltime = $this->mediaViewsRepository->fetchCountViewsForProjectAllTime($productInfo->project_id);

            return new JsonModel(
                [
                    'cntDlToday'           => $countDownloadsToday,
                    'cntMediaViewsAlltime' => $countMediaViewsAlltime,
                ]
            );
        }

        return new JsonModel();
    }

    public function indexReactAction()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $this->initVars();
        if (empty($this->_projectId)) {
            $this->redirect()->toRoute('/explore');
        }

        $productInfo = $this->projectRepository->fetchProductInfo($this->_projectId);
        $productInfoArray = $productInfo;
        if (empty($productInfo)) {
            $this->getResponse()->setStatusCode(404);

            return;
        }
        $productInfo = Util::arrayToObject($productInfo);

        // load product data
        $product = $this->loadProductData($this->_projectId);

        // load header data
        $header = $this->loadHeaderData($productInfo);
        $product['header'] = $header;

        // load comments
        $comments = $this->loadCommentsData($this->_projectId, 1);
        $product['commentsTab'] = iterator_to_array($comments->getCurrentItems(), true);
        $product['commentsTabCnt'] = $comments->getTotalItemCount();

        // load rightsidebar data
        $rightsidebar = $this->loadRightData($productInfo);
        $product['rightsidebar'] = $rightsidebar;

        // load files tab
        $files = $this->loadFilesData($productInfoArray);
        $product['filesTab'] = $files == null ? [] : $files;

        // load ratings tab
        $ratings = $this->loadRatingsData($this->_projectId);
        $product['ratingsTab'] = $ratings;

        // load changelogs
        $changelogs = $this->loadChangelogsData($this->_projectId);
        $product['changelogsTab'] = $changelogs;

        // load affiliates
        $affiliates = $this->loadAffiliatesData($this->_projectId);
        $product['affiliatesTab'] = $affiliates;

        // load fans
        $fans = $this->loadLikesData($this->_projectId);
        $product['fansTab'] = $fans;

        // load relations
        $relations = $this->loadRelationData($this->_projectId);
        $product['relationshipTab'] = $relations;

        // load commentsMod
        $commentsMod = $this->loadCommentsModData($this->_projectId, 1);
        $product['commentsModTab'] = iterator_to_array($commentsMod->getCurrentItems(), false);

        // load commentsMod
        $commentsLic = $this->loadCommentsLicData($this->_projectId, 1);
        $product['commentsLicTab'] = iterator_to_array($commentsLic->getCurrentItems(), false);

        // load plings
        $plings = $this->loadPlingsData($this->_projectId);
        $product['plingsTab'] = $plings;


        // load left categories tree
        $isTagGroupFilter = false;
        if ($product['tag_group_filter']) {
            foreach ($product['tag_group_filter'] as $value) {
                if ($value != null && $value != "0") {
                    $isTagGroupFilter = true;
                }
            }
        }
        if ($isTagGroupFilter == true) {
            $filter = $GLOBALS['ocs_config_store_tags'];
            $categories = $this->projectCategoryService->fetchTreeForViewForProjectTagGroupTags(null, $filter, $product['tag_group_filter']);
        } else {
            $categories = $this->projectCategoryRepository->fetchTreeForView();
        }


        $this->view->setVariable('headTitle', ($productInfo->title . ' - ' . $this->getHeadTitle()));
        $this->view->setVariable('categories', $categories);
        $this->view->setVariable('product', $productInfo);

        $product['categories'] = $categories;

        $topTagsPerCategory = $this->tagService->getTopTagsPerCategory($productInfo->project_category_id);
        $product['categoriesTopTags'] = $topTagsPerCategory;

        $json = (int)$this->params()->fromQuery('json', 0);
        if ($json == 1) {
            return new JsonModel($product);
        } else {
            $this->view->setVariable('productViewData', $product);

            return $this->view;
        }
    }

    /**
     * @return array
     */
    private function loadProductData($project_id)
    {
        $result = array();

        $productInfo = $this->projectRepository->fetchProductInfo($project_id);
        if (empty($productInfo)) {
            return ['status' => 'err', 'msg' => $project_id . ' not existing.'];
        }
        $productInfoArray = $productInfo;
        $productInfo = Util::arrayToObject($productInfo);

        if (!$this->currentUser()
                  ->isAdmin() and ($productInfo->project_status != ProjectService::PROJECT_ACTIVE) and ($productInfo->member_id != $this->currentUser()->member_id)) {
            return ['status' => 'err', 'msg' => $project_id . ' not existing.'];
        }

        if (null != $this->_authMember && null != $this->_authMember->member_id) {
            $isModerator = false;
            if ($this->currentUser()->isModerator()) {
                $isModerator = true;
            }
            $helperIsSupporter = new IsSupporter($this->memberService);
            $isSupporter = $helperIsSupporter->isSupporter($this->_authMember->member_id);
            $isSupporterActive = false;
            $supportData = array();
            if ($isSupporter) {
                $helperIsSupporterActive = new IsSupporterActive($this->infoService);
                $isSupporterActive = $helperIsSupporterActive($this->_authMember->member_id);

                $supportData = $this->sectionSupportRepository->fetchLatestSectionSupportForProject(
                    $project_id, $this->_authMember->member_id
                );
            }
            $isPlinged = false;

            $tmp = $this->projectPlingsRepository->getPling($project_id, $this->_authMember->member_id);
            if ($tmp) {
                $isPlinged = true;
            }
            $isFollower = false;

            $isFollower = $this->projectFollowerRepository->isFollower(
                $this->_authMember->member_id, $project_id
            );

            $authMember = array(
                'member_id'         => (int)$this->_authMember->member_id,
                'username'          => $this->_authMember->username,
                'roleName'          => $this->_authMember->roleName,
                'isAdmin'           => $this->isAdmin,
                'isModerator'       => $isModerator,
                'isSupporter'       => $isSupporter,
                'isSupporterActive' => $isSupporterActive,
                'isPlinged'         => $isPlinged,
                'isFollower'        => $isFollower,
                'supportData'       => $supportData,
            );
            $result[] = ['authMember' => $authMember];

        } else {

            $result[] = ['authMember' => null];
        }

        $cat = $this->projectCategoryRepository->fetchActive($productInfo->project_category_id);
        $xdg_type = null;
        if (count($cat)) {
            $xdg_type = $cat[0]['xdg_type'];
        }
        $result[] = ['xdg_type' => $xdg_type];


        $cntTabInfo = $this->projectRepository->fetchProjectInfoTabCnt($project_id);
        $cntCollections = $cntTabInfo['cntCollections'];

        $moreCollectionProducts = array();
        if ($cntCollections > 0) {
            $moreCollectionProducts = $this->collectionService->fetchAllCollectionsForProject($project_id, 6);
        }
        $result[] = ['moreCollectionProducts' => $moreCollectionProducts];

        $is_original = $this->tagService->isProductOriginal($project_id);
        $result[] = ['isProductOriginal' => $is_original];

        $systemTags = $this->tagService->getTagsSystemList($project_id);
        $result[] = ['systemTags' => $systemTags];

        if ($this->tagService->hasCategoryTagGroup($productInfo->project_category_id)) {
            $result[] = ['hasCategoryTagGroup' => 1];
            $tagsCategoryTagGroup = $this->tagService->getTagsFromCategoryTagGroup($productInfo->project_id);
            $result[] = ['tagsCategoryTagGroup' => $tagsCategoryTagGroup];
        }


        $isMod = $this->tagService->isProductModification($project_id);
        $result[] = ['isMod' => $isMod];

        //Get EbookInfo, if this is a ebook
        if ($this->tagService->isProductEbook($project_id)) {
            $authorTags = $this->tagService->getTagsEbookAuthor($project_id);
            $editorTags = $this->tagService->getTagsEbookEditor($project_id);
            $illuTags = $this->tagService->getTagsEbookIllustrator($project_id);
            $transTags = $this->tagService->getTagsEbookTranslator($project_id);
            $shelfTags = $this->tagService->getTagsEbookShelf($project_id);
            $subjectTags = $this->tagService->getTagsEbookSubject($project_id);
            $langTags = $this->tagService->getTagsEbookLanguage($project_id);

            $result[] = ['authorTags' => $authorTags];
            $result[] = ['editorTags' => $editorTags];
            $result[] = ['illuTags' => $illuTags];
            $result[] = ['transTags' => $transTags];
            $result[] = ['shelfTags' => $shelfTags];
            $result[] = ['subjectTags' => $subjectTags];
            $result[] = ['langTags' => $langTags];
        }


        $catId = $productInfo->project_category_id;

        $section = array('name' => 'Test', 'section_id' => '1');
        if ($catId && $catId > 0) {
            $s = $this->sectionService->fetchSectionForCategory($catId);
            $section['section_id'] = $s['section_id'];
            $section['name'] = $s['name'];
            $section['description'] = $s['description'];
        }
        $result[] = ['section' => $section];

        $updates = $this->projectUpdatesService->fetchProjectUpdates($project_id);
        if (count($updates) > 0) {
            $lastupdate = [];
            $lastupdate['title'] = $updates[0]['title'];
            $lastupdate['created_at'] = $updates[0]['created_at'];
            $lastupdate['text'] = BbcodeService::renderHtml(
                HtmlPurifyService::purify(htmlentities($updates[0]['text'], ENT_QUOTES | ENT_IGNORE))
            );
            $result[] = ['updatesLast' => $lastupdate];
        }

        $result[] = ['ratings' => $this->projectRatingRepository->fetchRating($project_id)];

        $tagGroupsForCategory = $this->tagGroupService->fetchTagGroupsForCategory($productInfo->project_category_id);
        $result[] = ['tagGroupsForCategory' => $tagGroupsForCategory];

        $tagsuser = $this->tagService->getTagsUser($project_id, TagsRepository::TAG_TYPE_PROJECT);
        $result[] = ['tagsuser' => $tagsuser];

        $isProductDeprecatedModerator = $this->tagService->isProductDeprecatedModerator($project_id);
        $result[] = ['isProductDeprecatedModerator' => $isProductDeprecatedModerator];

        $countFiles = $this->pploadFilesRepository->fetchFilesCntForProject($productInfo->ppload_collection_id);
        $cntTabInfo['cntFiles'] = (int)$countFiles;
        $result[] = ['tabCnt' => $cntTabInfo];
        $tagsArray = $this->tagService->getTagsArray(
            $project_id, TagConst::TAG_TYPE_PROJECT, TagConst::TAG_GHNS_EXCLUDED_GROUPID
        );
        $isGhnsExcluded = false;
        if (isset($tagsArray) && (count($tagsArray) == 1)) {
            $isGhnsExcluded = true;
        }
        $result[] = ['isGhnsExcluded' => $isGhnsExcluded];

        $pics = $this->projectService->getGalleryPictureSources($project_id);
        if (sizeof($pics) == 0) {
            array_push($pics, $productInfo->image_small);
        }
        $galleryPictures = array();
        foreach ($pics as $p) {
            $galleryPictures[] = Util::image($p, array('height' => '600'));
        }
        $result[] = ['pics' => $galleryPictures];

        $cntSameSource = $this->projectService->getCountSourceurl($productInfo->source_url);
        $result[] = ['cntSameSource' => $cntSameSource];

        if ($this->_authMember) {
            $result[] = [
                'ratingOfUser' => $this->projectRatingRepository->getProjectRateForUser(
                    $productInfo->project_id, $this->_authMember->member_id
                ),
            ];
        }

        $tagGroupFilter = $GLOBALS['ocs_config_store_taggroups'];

        if (!empty($tagGroupFilter)) {
            $filterArray = array();
            foreach ($tagGroupFilter as $tagGroupId) {
                $inputFilter = $this->getFilterTagFromCookie($tagGroupId);
                $filterArray[$tagGroupId] = $inputFilter;
            }
            $result[] = ['tag_group_filter' => $filterArray];
        }


        //gitlab
        if ($productInfo->is_gitlab_project) {
            $gitProject = $this->fetchGitlabProject($productInfo->gitlab_project_id);
            if (null == $gitProject) {
                $productInfo->is_gitlab_project = 0;
                $productInfo->show_gitlab_project_issues = 0;
                $productInfo->use_gitlab_project_readme = 0;
                $productInfo->gitlab_project_id = null;
            } else {

                $result[] = ['gitlab_project' => $gitProject];
                //show issues?
                if ($productInfo->show_gitlab_project_issues) {
                    $issues = $this->fetchGitlabProjectIssues($productInfo->gitlab_project_id);
                    $result[] = ['gitlagitlab_project_issuesb_project' => $issues];
                    $result[] = ['gitlab_project_issues_url' => $gitProject->web_url . '/issues/'];
                }

                //show readme.md?
                if ($productInfo->use_gitlab_project_readme && null != $gitProject->readme_url) {
                    $config = $this->config->settings->server->opencode;
                    $token = $config->private_token;
                    $sudo = $config->user_sudo;
                    $agent = $config->user_agent;
                    $readme = $gitProject->web_url . '/raw/master/README.md?inline=false';

                    $httpClient = new Client($readme, array('keepalive' => true, 'strictredirects' => true));
                    $httpClient->resetParameters();
                    $httpClient->setUri($readme);
                    //$httpClient->setHeaders(array('Private-Token' => $config->private_token, 'Sudo' => $config->user_sudo, 'User-Agent' => $config->user_agent));
                    $httpClient->setHeaders(
                        array(
                            'Private-Token' => $token,
                            'Sudo'          => $sudo,
                            'User-Agent'    => $agent,
                        )
                    );
                    $httpClient->setMethod('GET');

                    $response = $httpClient->send();

                    $body = $response->getBody();

                    if (empty($body)) {
                        return array();
                    }
                    $Parsedown = new Parsedown();
                    $result[] = ['readme' => $Parsedown->text($body)];

                } else {
                    $result[] = ['readme' => null];
                }
            }
        }


        $memberInfo = [];
        $isSupporter = false;
        $isSupporter = $helperIsSupporter->isSupporter($productInfo->member_id);
        //$isSupporter = $this->sectionService->isMemberSectionSupporter($section['section_id'], $productInfo->member_id);
        $isSupporterActive = false;
        $memberInfo['isSupporter'] = $isSupporter;
        if ($isSupporter) {
            $helperIsSupporterActive = new IsSupporterActive($this->infoService);
            $isSupporterActive = $helperIsSupporterActive($productInfo->member_id);
            $memberInfo['isSupporterActive'] = $isSupporterActive;

        }

        $result[] = ['member' => $memberInfo];

        $product = CollectionService::cleanProductInfoForJson($productInfoArray);
        $product['title'] = strip_tags(HtmlPurifyService::purify($product['title']));
        $product['description'] = BbcodeService::renderHtml(HtmlPurifyService::purify($product['description']));
        $product['version'] = strip_tags(HtmlPurifyService::purify($product['version']));
        $helperAddDefaultScheme = new AddDefaultScheme();
        $product['link_1'] = HtmlPurifyService::purify(
            $helperAddDefaultScheme($product['link_1']), HtmlPurifyService::ALLOW_URL
        );
        $product['source_url'] = HtmlPurifyService::purify($product['source_url'], HtmlPurifyService::ALLOW_URL);
        $product['facebook_code'] = HtmlPurifyService::purify($product['facebook_code'], HtmlPurifyService::ALLOW_URL);
        $product['twitter_code'] = HtmlPurifyService::purify($product['twitter_code'], HtmlPurifyService::ALLOW_URL);
        $product['google_code'] = HtmlPurifyService::purify($product['google_code'], HtmlPurifyService::ALLOW_URL);


        // about store/category
        $catabout = $this->getCategoryAbout($productInfo->project_category_id);
        $storeabout = $this->getStoreAbout($GLOBALS['ocs_store']->config->store_id);
        if ($catabout) {
            $product['catabout'] = $catabout;
        }
        if ($storeabout) {
            $product['storeabout'] = $storeabout;
        }

        if ($this->isAdmin) {
            $position = $this->infoService->findProductPostion($this->_projectId);
            $product['position'] =  $position;
           
        }
        $result[] = ['product' => $product];

        if ($productInfo->type_id == ProjectRepository::PROJECT_TYPE_COLLECTION) {
            $collinfo = $this->loadCollectionData($project_id);
            $result[] = $collinfo;
        }

        $tmp = [];
        foreach ($result as $key => $object) {
            foreach ($object as $k => $v) {
                $tmp[$k] = $v;
            }
        }

        return $tmp;
    }

    /**
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     * start react asyncro load pagedtail.
     */
    private function loadCollectionData($project_id)
    {
        $result = array();
        $projectsArray = $this->collectionProjectsRepository->getCollectionProjects($project_id);
        $helperImage = new Image();

        $projects = array();
        foreach ($projectsArray as $project) {
            $imgUrl = $helperImage->Image($project['image_small'], array('width' => 140, 'height' => 98));
            $project['image_url'] = $imgUrl;
            $projects[] = $project;
        }
        $result['collection_projects'] = $projects;

        $collection_ids = array();
        foreach ($projects as $value) {
            if ($value['ppload_collection_id']) {
                $collection_ids[] = $value['ppload_collection_id'];
            }
        }

        $collection_projects_dls = $this->pploadFilesRepository->fetchAllActiveFilesForCollection($collection_ids);
        $result['collection_projects_dls'] = $collection_projects_dls;

        return $result;
    }

    private function loadHeaderData($productInfo=null)
    {        
        $catId = null;
        if($productInfo){
            $catId = $productInfo->project_category_id;
        } 
        $fetchHeaderData = new FetchHeaderData($this->sectionService, $this->infoService);
        return $fetchHeaderData($catId);
    }

    private function loadCommentsData($project_id, $page)
    {

        $commentsTree = $this->commentsRepository->getCommentTreeForProject($project_id);
        $commentsTree->setItemCountPerPage(self::CNT_COMMENTS_PER_PAGE);
        $commentsTree->setCurrentPageNumber($page);
        $list = $commentsTree->getCurrentItems();
        foreach ($list as &$value) {
            if ((int)$value['comment']['issupporter'] > 0) {
                $issupporterActive = $this->infoService->isMemberActiveSupporter($value['comment']['member_id']);
                $value['comment']['issupporterActive'] = $issupporterActive;
            } else {
                $value['comment']['issupporterActive'] = false;
            }
        }

        return $commentsTree;
    }

    private function loadFilesData($productInfo, $file_status = null, $ignore_status_code = null)
    {
        $cat = $this->projectCategoryRepository->fetchActive($productInfo['project_category_id']);
        $xdg_type = null;
        if (count($cat)) {
            $xdg_type = $cat[0]['xdg_type'];
        }
        $filesTable = $this->pploadFilesRepository;
        $collection_id = $productInfo['ppload_collection_id'];
        $result = [];
        if ($collection_id) {
            $isForAdmin = false;
            if ($this->isAdmin) {
                $isForAdmin = true;
            }

            //Load files from DB
            if ($ignore_status_code == 0 && $file_status == 'active') {
                $files = $filesTable->fetchAllActiveFilesForProject($collection_id, $isForAdmin);
            } else {
                $files = $filesTable->fetchAllFilesForProject($collection_id, $isForAdmin);
            }

            //Check, if the project category has tag-grous
            $tagGroups = $this->tagGroupService->fetchTagGroupsForCategory($productInfo['project_category_id']);
            $serverUrlhelper = new ServerUrl();
            $serverUrl = $serverUrlhelper->__invoke(true);
            foreach ($files as $file) {
                $file = UtilReact::cleanFile($file);
                //add tag grous, if needed
                if (!empty($tagGroups)) {
                    $groups = $this->getTagGroupsForCat($file['id'], $productInfo);
                    $file['tag_groups'] = $groups;
                }

                $payload = array('id' => $file['id'], 'u' => $this->_authMember->member_id, 'lt' => 'filepreview');
                $pploadService = $this->pploadService;
                $url = $pploadService->createDownloadUrlJwt($file['collection_id'], $file['name'], $payload);
                $file['url'] = urlencode($url);
                $file['isInstall'] = $xdg_type && $file['ocs_compatible'];

                //Download Counter
                //new counter IP based
                $counterUkAll = $file['count_dl_all_uk'];
                $counterNoUkAll = $file['count_dl_all_nouk'];
                $counterUkToday = $file['count_dl_uk_today'];
                $counterNew = 0;
                if (!empty($counterUkAll)) {
                    $counterNew = $counterNew + $counterUkAll;
                }
                if (!empty($counterUkToday)) {
                    $counterNew = $counterNew + $counterUkToday;
                }
                if (!empty($counterNoUkAll)) {
                    $counterNew = $counterNew + $counterNoUkAll;
                }
                $file['downloaded_count_uk'] = $counterNew;


                if ($this->isAdmin) {
                    $counterToday = $file['count_dl_today'];
                    $counterAll = $file['count_dl_all'];
                    $counter = 0;
                    if (!empty($counterToday)) {
                        $counter = $counterToday;
                    }
                    if (!empty($counterAll)) {
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


            return $result;
        }

    }

    private function loadRatingsData($project_id)
    {
        $tableProjectRatings = $this->projectRatingRepository;
        $ratings = $tableProjectRatings->fetchRating($project_id);
        foreach ($ratings as &$value) {
            $value = self::cleanRatings($value);
        }

        return $ratings;
    }

    private static function cleanRatings(array $ratings)
    {
        if (empty($ratings)) {
            return $ratings;
        }

        $unwantedKeys = array(
            'project_id'    => 0,
            'user_like'     => 0,
            'user_dislike'  => 0,
            'score_test'    => 0,
            'comment_id'    => 0,
            'rating_active' => 0,
            'source_id'     => 0,
            'source_pk'     => 0,
        );

        $ratings = array_diff_key($ratings, $unwantedKeys);

        return $ratings;
    }

    private function loadChangelogsData($project_id)
    {

        $updates = $this->projectUpdatesService->fetchProjectUpdates($project_id);
        foreach ($updates as &$update) {
            $update['text'] = BbcodeService::renderHtml(
                HtmlPurifyService::purify(htmlentities($update['text'], ENT_QUOTES | ENT_IGNORE))
            );
            $update = self::cleanChangelogs($update);
        }

        return $updates;
    }

    private static function cleanChangelogs(array $changelog)
    {
        if (empty($changelog)) {
            return $changelog;
        }
        $wantedKeys = array(
            'title'      => 0,
            'created_at' => 0,
            'text'       => 0,
        );

        $changelog = array_intersect_key($changelog, $wantedKeys);

        return $changelog;
    }

    private function loadAffiliatesData($project_id)
    {
        $data = $this->sectionSupportService->fetchAffiliatesForProject($project_id);

        return $data;
    }

    private function loadLikesData($project_id)
    {
        $data = $this->projectFollowerRepository->fetchLikesForProject($project_id);

        return $data;
    }

    private function loadRelationData($project_id)
    {

        $serviceProduct = $this->projectService;
        $result = [];
        if ($this->isAdmin) {   // execute only for admin.
            $pc = $this->projectCloneService;
            $cntRelatedProducts = 0;
            $ancesters = $pc->fetchAncestorsIds($this->_projectId);
            //$siblings = $pc->fetchSiblings($this->_projectId);
            //$parents = $pc->fetchParentIds($this->_projectId);
            if ($ancesters && strlen($ancesters) > 0) {
                $parents = $pc->fetchParentLevelRelatives($project_id);
            } else {
                $parents = $pc->fetchParentIds($project_id);
            }
            if ($parents && strlen($parents) > 0) {
                $siblings = $pc->fetchSiblingsLevelRelatives($parents, $project_id);
            } else {
                $siblings = null;
            }
            $childrens = $pc->fetchChildrensIds($project_id);
            $childrens2 = null;
            $childrens3 = null;
            if (strlen($childrens) > 0) {
                $childrens2 = $pc->fetchChildrensChildrenIds($childrens);
                if (strlen($childrens2) > 0) {
                    $childrens3 = $pc->fetchChildrensChildrenIds($childrens2);
                }
            }

            // $this->view->related_children2 = null;
            // $this->view->related_children3 = null;
            if ($ancesters && strlen($ancesters) > 0) {
                $pts = $serviceProduct->fetchProjects($ancesters);
                $result['related_ancesters'] = sizeof($pts) == 0 ? null : $pts;
                $cntRelatedProducts += sizeof($pts);
            }
            if ($siblings && strlen($siblings) > 0) {
                $pts = $serviceProduct->fetchProjects($siblings);
                $result['related_siblings'] = sizeof($pts) == 0 ? null : $pts;
                $cntRelatedProducts += sizeof($pts);
            }
            if ($parents && strlen($parents) > 0) {
                $pts = $serviceProduct->fetchProjects($parents);
                $result['related_parents'] = sizeof($pts) == 0 ? null : $pts;
                $cntRelatedProducts += sizeof($pts);
            }
            if ($childrens && strlen($childrens) > 0) {
                $pts = $serviceProduct->fetchProjects($childrens);
                $result['related_children'] = sizeof($pts) == 0 ? null : $pts;
                $cntRelatedProducts += sizeof($pts);
            }
            $result['cntRelatedProducts'] = $cntRelatedProducts;
        } else {
            $result['cntRelatedProducts'] = 0;
        }

        return $result;
    }

    private function loadCommentsModData($project_id, $page)
    {

        $commentsTree = $this->commentsRepository->getCommentTreeForProject(
            $this->_projectId, CommentsRepository::COMMENT_TYPE_MODERATOR
        );
        $commentsTree->setItemCountPerPage(500);
        $commentsTree->setCurrentPageNumber($page);

        return $commentsTree;
    }

    private function loadCommentsLicData($project_id, $page)
    {

        $commentsTree = $this->commentsRepository->getCommentTreeForProject(
            $this->_projectId, CommentsRepository::COMMENT_TYPE_LICENSING
        );
        $commentsTree->setItemCountPerPage(500);
        $commentsTree->setCurrentPageNumber($page);

        return $commentsTree;
    }

    private function loadPlingsData($project_id)
    {
        $data = $this->projectPlingsService->fetchPlingsForProject($project_id);

        return $data;
    }

    /**
     * ppload
     */
    public function addpploadfileAction()
    {
        $this->initVars();

        $log = $GLOBALS['ocs_log'];
        $log->debug('**********' . __METHOD__ . '**********' . "\n");

        $projectTable = $this->projectRepository;
        $projectData = $projectTable->findById($this->_projectId);

        $error_text = '';

        // Add file to ppload collection
        if (!empty($_FILES['file_upload']['tmp_name']) && $_FILES['file_upload']['error'] == UPLOAD_ERR_OK) {
            $tmpFilename = dirname($_FILES['file_upload']['tmp_name']) . '/' . basename($_FILES['file_upload']['name']);
            $log->debug(__METHOD__ . '::' . print_r($tmpFilename, true) . "\n");
            move_uploaded_file($_FILES['file_upload']['tmp_name'], $tmpFilename);

            $pploadApi = new PploadApi(
                array(
                    'apiUri'   => PPLOAD_API_URI,
                    'clientId' => PPLOAD_CLIENT_ID,
                    'secret'   => PPLOAD_SECRET,
                )
            );

            $fileRequest = array(
                'file'     => $tmpFilename,
                'owner_id' => $this->_authMember->member_id,
            );

            //Admins can upload files for users
            $userRoleName = $this->_authMember->roleName;
            if ('admin' == $userRoleName) {
                $member_id = $projectData->member_id;
                $fileRequest = array(
                    'file'     => $tmpFilename,
                    'owner_id' => $member_id,
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
            $log->debug(__METHOD__ . '::' . print_r($fileResponse, true) . "\n");

            unlink($tmpFilename);

            if (!empty($fileResponse->file->collection_id)) {
                if (!$projectData->ppload_collection_id) {
                    // Save collection ID
                    $projectData->ppload_collection_id = $fileResponse->file->collection_id;
                    //20180219 ronald: we set the changed_at only by new files or new updates
                    if ((int)$this->_authMember->member_id == (int)$projectData->member_id) {
                        $projectData->changed_at = new Expression('NOW()');
                    } else {
                        $log->info(
                            '********** ' . __METHOD__ . ' Project ChangedAt is not set: Auth-Member (' . $this->_authMember->member_id . ') != Project-Owner (' . $projectData->member_id . '): **********' . "\n"
                        );
                    }
                    $projectData->ghns_excluded = 0;
                    $projectTable->update($projectData->getArrayCopy());


                    $activityLog = $this->activityLog;
                    $activityLog->writeActivityLog(
                        $this->_projectId, $projectData->member_id, $activityLog::PROJECT_EDITED, $projectData->getArrayCopy()
                    );
                    // Update profile information
                    $memberTable = new MemberRepository($this->db);
                    $memberSettings = $memberTable->findById($this->_authMember->member_id);
                    $mainproject = $projectTable->findById($memberSettings->main_project_id);
                    $profileName = '';
                    if ($memberSettings->firstname || $memberSettings->lastname) {
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
                        'description' => $mainproject->description,
                    );
                    $profileResponse = $pploadApi->postProfile($profileRequest);
                    // Update collection information
                    $collectionCategory = $projectData->project_category_id;
                    if ($projectTable::PROJECT_ACTIVE == $projectData->status) {
                        $collectionCategory .= '-published';
                    }
                    $collectionRequest = array(
                        'title'       => $projectData->title,
                        'description' => $projectData->description,
                        'category'    => $collectionCategory,
                        'content_id'  => $projectData->project_id,
                    );
                    $collectionResponse = $pploadApi->putCollection(
                        $projectData->ppload_collection_id, $collectionRequest
                    );
                    // Store product image as collection thumbnail
                    $this->_updatePploadMediaCollectionthumbnail($projectData);
                } else {
                    //20180219 ronald: we set the changed_at only by new files or new updates
                    if ((int)$this->_authMember->member_id == (int)$projectData->member_id) {
                        $projectData->changed_at = new Expression('NOW()');
                    } else {
                        $log->info(
                            __METHOD__ . ' -  Project ChangedAt is not set: Auth-Member (' . $this->_authMember->member_id . ') != Project-Owner (' . $projectData->member_id . '): **********' . "\n"
                        );
                    }
                    $projectData->ghns_excluded = 0;
                    $projectTable->update($projectData->getArrayCopy());
                }

                //@formatter:off
                //If this file is a video, we have to convert it for preview
                if (!empty($fileResponse->file->type)
                    && in_array($fileResponse->file->type, ConvertVideo::$VIDEO_FILE_TYPES))
                {
                    JobBuilder::getJobBuilder()
                              ->withJobClass(ConvertVideo::class)
                              ->withParam('collectionId', $projectData->ppload_collection_id)
                              ->withParam('fileId', $fileResponse->file->id)
                              ->withParam('fileType', $fileResponse->file->type)
                              ->build();
                }
                //@formatter:on

                //If this file is bigger than XXX MB (see application.ini), then create a webtorrent file
                $config = $GLOBALS['ocs_config'];
                $minFileSize = $config->settings->server->torrent->media->min_filesize;
                $log->debug(
                    __METHOD__ . ' -  Create new Job CreateTorrent, if file size: ' . $fileResponse->file->size . ' >= min file size: ' . $minFileSize . ' **********' . "\n"
                );
                //@formatter:off
                if (!empty($fileResponse->file->size) && $fileResponse->file->size >= $minFileSize) {
                    JobBuilder::getJobBuilder()
                              ->withJobClass(CreateTorrent::class)
                              ->withParam('file', $fileResponse->file)
                              ->build();
                }

                //If this is a cbr or cbz comic archive, then start an extracting job
                if ($this->endsWith($fileResponse->file->name, '.cbr')
                    || $this->endsWith($fileResponse->file->name, '.cbz'))
                {
                    JobBuilder::getJobBuilder()
                              ->withJobClass(ExtractComic::class)
                              ->withParam('file', $fileResponse->file)
                              ->build();
                }
                //@formatter:on

                return new JsonModel(
                    array(
                        'status' => 'ok',
                        'file'   => $fileResponse->file,
                    )
                );
            }
        }

        $log->debug('********** END ' . __METHOD__ . '**********' . "\n");

        return new JsonModel(array('status' => 'error', 'error_text' => $error_text));
    }

    private function endsWith($haystack, $needle)
    {
        return $needle === "" || substr(strtolower($haystack), -strlen($needle)) === strtolower($needle);
    }

    /**
     * ppload
     */
    public function updatepploadfileAction()
    {
        $this->initVars();

        $log = $GLOBALS['ocs_log'];
        $log->debug('**********' . __METHOD__ . '**********' . "\n");

        $projectTable = $this->projectRepository;
        $projectData = $projectTable->findById($this->_projectId);

        $error_text = '';

        // Update a file in ppload collection
        if (!empty($_POST['file_id'])) {
            $pploadApi = new PploadApi(
                array(
                    'apiUri'   => PPLOAD_API_URI,
                    'clientId' => PPLOAD_CLIENT_ID,
                    'secret'   => PPLOAD_SECRET,
                )
            );

            $fileResponse = $pploadApi->getFile($_POST['file_id']);
            if (isset($fileResponse->file->collection_id) && $fileResponse->file->collection_id == $projectData->ppload_collection_id) {
                $fileRequest = array();
                $tmpFilename = '';
                if (!empty($_FILES['file_upload']['tmp_name']) && $_FILES['file_upload']['error'] == UPLOAD_ERR_OK) {
                    $tmpFilename = dirname($_FILES['file_upload']['tmp_name']) . '/' . basename(
                            $_FILES['file_upload']['name']
                        );
                    $log->debug(__METHOD__ . '::' . print_r($tmpFilename, true) . "\n");
                    move_uploaded_file($_FILES['file_upload']['tmp_name'], $tmpFilename);
                    $fileRequest['file'] = $tmpFilename;

                    //20180219 ronald: we set the changed_at only by new files or new updates
                    if ((int)$this->_authMember->member_id == (int)$projectData->member_id) {
                        $projectData->changed_at = new Expression('NOW()');
                    } else {
                        $log->info(
                            '********** ' . __METHOD__ . ' Project ChangedAt is not set: Auth-Member (' . $this->_authMember->member_id . ') != Project-Owner (' . $projectData->member_id . '): **********' . "\n"
                        );
                    }
                    $projectData->ghns_excluded = 0;
                    $projectTable->update($projectData->getArrayCopy());
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
                $log->debug(__METHOD__ . '::' . print_r($fileResponse, true) . "\n");

                if ($tmpFilename) {
                    unlink($tmpFilename);
                }

                if (isset($fileResponse->status) && $fileResponse->status == 'success') {
                    //If this file is bigger than XXX MB (see application.ini), then create a webtorrent file
                    /*
                    $config = $this->config;
                    $minFileSize = $config->torrent->media->min_filesize;
                    if (!empty($fileResponse->file->size) && $fileResponse->file->size >= $minFileSize) {
                        $queue = Local_Queue_Factory::getQueue();
                        $command = new Backend_Commands_CreateTorrent($fileResponse->file);
                        $queue->send(serialize($command));
                    }
                    */
                    $config = $GLOBALS['ocs_config'];
                    $minFileSize = $config->settings->server->torrent->media->min_filesize;
                    $log->debug(
                        '********** ' . __METHOD__ . ' Create new Job CreateTorrent, if file size: ' . $fileResponse->file->size . ' >= min file size: ' . $minFileSize . ' **********' . "\n"
                    );
                    //@formatter:off
                    if (!empty($fileResponse->file->size) && $fileResponse->file->size >= $minFileSize) {
                        JobBuilder::getJobBuilder()
                                  ->withJobClass(CreateTorrent::class)
                                  ->withParam('file', $fileResponse->file)
                                  ->build();
                    }
                    //@formatter:on

                    return new JsonModel(
                        array(
                            'status' => 'ok',
                            'file'   => $fileResponse->file,
                        )
                    );
                } else {
                    $error_text .= 'Response: $pploadApi->putFile(): ' . json_encode(
                            $fileResponse
                        ) . '; $fileResponse->status: ' . $fileResponse->status;
                }
            } else {
                $error_text .= 'PPload Response: ' . json_encode(
                        $fileResponse
                    ) . '; fileResponse->file->collection_id: ' . $fileResponse->file->collection_id . ' != $projectData->ppload_collection_id: ' . $projectData->ppload_collection_id;
            }
        } else {
            $error_text .= 'No CollectionId or no FileId. CollectionId: ' . $projectData->ppload_collection_id . ', FileId: ' . $_POST['file_id'];
        }

        $log->debug('********** END ' . __METHOD__ . '**********' . "\n");

        return new JsonModel(array('status' => 'error', 'error_text' => $error_text));
    }

    public function updatefiletagAction()
    {
        $this->initVars();

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
            $modelTags = $this->tagService;
            $modelTags->saveFileTagForProjectAndTagGroup($this->_projectId, $_POST['file_id'], $tagId, $tagGroupId);

            return new JsonModel(array('status' => 'ok'));
        } else {
            $error_text .= 'No FileId. , FileId: ' . $_POST['file_id'];
        }

        return new JsonModel(array('status' => 'error', 'error_text' => $error_text));
    }

    public function deletefiletagAction()
    {
        $this->initVars();

        $error_text = '';

        // Update a file information in ppload collection
        if (!empty($_POST['file_id'])) {
            $tagId = null;
            if (isset($_POST['tag_id'])) {
                $tagId = $_POST['tag_id'];
            }

            //set architecture
            $modelTags = $this->tagService;
            $modelTags->deleteFileTagForProject($this->_projectId, $_POST['file_id'], $tagId);

            return new JsonModel(array('status' => 'ok'));
        } else {
            $error_text .= 'No FileId. , FileId: ' . $_POST['file_id'];
        }

        return new JsonModel(array('status' => 'error', 'error_text' => $error_text));
    }

    public function updatecompatibleAction()
    {
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

        return new JsonModel(array('status' => 'error', 'error_text' => $error_text));
    }

    /**
     * ppload
     */
    public function deletepploadfileAction()
    {
        $this->initVars();

        $projectTable = $this->projectRepository;
        $projectData = $projectTable->findById($this->_projectId);

        $error_text = '';

        // Delete file from ppload collection
        if (!empty($_POST['file_id'])) {
            $pploadApi = new PploadApi(
                array(
                    'apiUri'   => PPLOAD_API_URI,
                    'clientId' => PPLOAD_CLIENT_ID,
                    'secret'   => PPLOAD_SECRET,
                )
            );

            $fileResponse = $pploadApi->getFile($_POST['file_id']);
            if (isset($fileResponse->file->collection_id) && $fileResponse->file->collection_id == $projectData->ppload_collection_id) {
                $fileResponse = $pploadApi->deleteFile($_POST['file_id']);
                if (isset($fileResponse->status) && $fileResponse->status == 'success') {

                    return new JsonModel(array('status' => 'ok'));
                } else {
                    $error_text .= 'Response: $pploadApi->putFile(): ' . json_encode($fileResponse);
                }
            }
        }

        return new JsonModel(array('status' => 'error', 'error_text' => $error_text));
    }

    /**
     * ppload
     */
    public function deletepploadfilesAction()
    {
        $this->initVars();

        $projectTable = $this->projectRepository;
        $projectData = $projectTable->findById($this->_projectId);

        // Delete all files in ppload collection
        if ($projectData->ppload_collection_id) {
            $pploadApi = new PploadApi(
                array(
                    'apiUri'   => PPLOAD_API_URI,
                    'clientId' => PPLOAD_CLIENT_ID,
                    'secret'   => PPLOAD_SECRET,
                )
            );

            $filesRequest = array(
                'collection_id' => $projectData->ppload_collection_id,
                'perpage'       => 1000,
            );

            $filesResponse = $pploadApi->getFiles($filesRequest);

            if (isset($filesResponse->status) && $filesResponse->status == 'success') {
                foreach ($filesResponse->files as $file) {
                    $fileResponse = $pploadApi->deleteFile($file->id);
                    if (!isset($fileResponse->status) || $fileResponse->status != 'success') {
                        return new JsonModel(array('status' => 'error'));
                    }
                }
            }

            return new JsonModel(array('status' => 'ok'));
        }

        return new JsonModel(array('status' => 'error'));
    }

    public function saveproductAction()
    {
        //get service manager
        $serviceManager = $this->getEvent()->getApplication()->getServiceManager();

        //setup form
        $formManager = $serviceManager->get('FormElementManager');
        /** @var ProductForm $form */
        $form = $formManager->get(ProductForm::class);

        // we don't need to test a file which doesn't exist in this case. The framework stumbles if $_FILES is empty.
        if ($this->_request->isXmlHttpRequest() and (count($_FILES) == 0)) {
            $form->remove('image_small_upload');
            //$form->removeElement('image_big_upload');
            $form->remove('gallery');
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
        $formValues['status'] = ProjectService::PROJECT_INCOMPLETE;

        //remove not needed elements
        unset($formValues['tags']);
        unset($formValues['tagsuser']);
        unset($formValues['preview']);
        unset($formValues['image_small_upload']);
        unset($formValues['cancel']);
        unset($formValues['license_tag_id']);
        unset($formValues['is_original_or_modification']);
        unset($formValues['gallery']);
        unset($formValues['project_id']);
        unset($formValues['upload_picture']);
        unset($formValues['online_picture']);

        $modelProject = $this->projectService;
        $newProject = $modelProject->createProject(
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

    public function startmediaviewajaxAction()
    {
        return $this->startvideoajaxAction();
    }

    public function startvideoajaxAction()
    {
        $this->initVars();

        $this->view->setTerminal(true);

        $collection_id = null;
        $file_id = null;
        $memberId = $this->_authMember->member_id;
        $media_view_type_id = (int)$this->getParam('type_id', 1);
        $stat_object_view_type_id = ViewsService::OBJECT_TYPE_MEDIA_VIDEO;

        if ($media_view_type_id == 1) {
            $stat_object_view_type_id = ViewsService::OBJECT_TYPE_MEDIA_VIDEO;
        } else {
            if ($media_view_type_id == 2) {
                $stat_object_view_type_id = ViewsService::OBJECT_TYPE_MEDIA_MUSIC;
            } else {
                if ($media_view_type_id == 3) {
                    $stat_object_view_type_id = ViewsService::OBJECT_TYPE_MEDIA_BOOK;
                }
            }
        }

        if ($this->getParam('collection_id') && $this->getParam('file_id')) {
            $collection_id = (int)$this->getParam('collection_id');
            $file_id = (int)$this->getParam('file_id');
            $id = null;

            //Log media view
            try {
                $this->saveMediaView($file_id, $stat_object_view_type_id);

                $mediaviewsTable = new MediaViewsRepository($this->db);
                $id = $mediaviewsTable->getNewId();
                $data = array(
                    'media_view_id'      => $id,
                    'media_view_type_id' => $media_view_type_id,
                    'project_id'         => $this->_projectId,
                    'collection_id'      => $collection_id,
                    'file_id'            => $file_id,
                    'start_timestamp'    => new Expression ('Now()'),
                    'ip'                 => $this->getRealIpAddr(),
                    'referer'            => $this->getReferer(),
                );
                if (!empty($memberId)) {
                    $data['member_id'] = $memberId;
                }
                $data['source'] = 'OCS-Webserver';

                $mediaviewsTable->insert($data);

            } catch (Exception $exc) {
                $errorLog = $GLOBALS['ocs_log'];
                $errorLog->err(__METHOD__ . ' - ' . $exc->getMessage() . ' ---------- ' . PHP_EOL);
            }

            return new JsonModel(array('status' => 'success', 'MediaViewId' => $id));
        }

        return new JsonModel(array('status' => 'error'));
    }

    /**
     * @param int $file_id
     * @param int $media_view_type_id
     */
    protected function saveMediaView($file_id, $media_view_type_id)
    {
        switch ($media_view_type_id) {
            case ViewsService::OBJECT_TYPE_MEDIA_VIDEO:
                ViewsService::saveViewVideo($file_id);
                break;
            case ViewsService::OBJECT_TYPE_MEDIA_MUSIC:
                ViewsService::saveViewMusic($file_id);
                break;
            case ViewsService::OBJECT_TYPE_MEDIA_BOOK:
                ViewsService::saveViewBook($file_id);
                break;
            default:
                error_log(__METHOD__ . ' :: no mediatype found for value: ' . $media_view_type_id);
        }
    }

    /**
     * @return string|null
     */
    public function getRealIpAddr()
    {
        $ip = null;
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        {
            list($ip) = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']); // this could be an array
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * @return string|null
     */
    public function getReferer()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_REFERRER'])) {
            return $_SERVER['HTTP_X_FORWARDED_REFERRER'];
        }
        if (!empty($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        }

        return null;
    }

    public function stopmediaviewajaxAction()
    {
        return $this->stopvideoajaxAction();
    }

    public function stopvideoajaxAction()
    {
        $this->view->setTerminal(true);

        $view_id = null;

        if ($this->getParam('media_view_id')) {
            $view_id = $this->getParam('media_view_id');

            //Log media view stop
            try {
                $mediaviewsTable = new MediaViewsRepository($this->db);
                $data = array('stop_timestamp' => new Expression('Now()'));
                $mediaviewsTable->update($data, 'media_view_id = ' . $view_id);
            } catch (Exception $exc) {
                //echo $exc->getTraceAsString();
                $errorLog = $GLOBALS['ocs_log'];
                $errorLog->err(__METHOD__ . ' - ' . $exc->getMessage() . ' ---------- ' . PHP_EOL);
            }

            return new JsonModel(array('status' => 'success', 'MediaViewId' => $view_id));
        }

        return new JsonModel(array('status' => 'error'));
    }

    public function loadProductAction()
    {
        $this->initVars();


        $product = $this->loadProductData($this->_projectId);


        return new JsonModel($product);

    }

    public function loadFilesAction()
    {
        $this->initVars();
        $collection_id = null;
        $file_status = null;
        $ignore_status_code = null;

        if ($this->getParam('status')) {
            $file_status = $this->getParam('status');
        }
        if ($this->getParam('ignore_status_code')) {
            $ignore_status_code = $this->getParam('ignore_status_code');
        }
        $productInfo = $this->projectRepository->fetchProductInfo($this->_projectId);
        $data = $this->loadFilesData($productInfo, $file_status, $ignore_status_code);

        return new JsonModel($data);
    }

    public function loadChangelogsAction()
    {
        $this->initVars();
        $updates = $this->loadChangelogsData($this->_projectId);

        return new JsonModel($updates);
    }

    // public function loadCommentsAction()
    // {
    //     $this->initVars();
    //     $page = (int)$this->params()->fromQuery('page', 0);
    //     $data = $this->loadCommentsData($this->_projectId, $page);

    //     return new JsonModel($data);
    // }

    public function loadCommentsAction()
    {        
        $this->_projectId = (int)$this->getParam('project_id');
        $this->view->setTerminal(true);
        $page = (int)$this->params()->fromQuery('page', 0);
        $data = $this->loadCommentsData($this->_projectId, $page);
        return new JsonModel($data);
    }

    public function loadCommentsModAction()
    {
        $this->initVars();
        $page = (int)$this->params()->fromQuery('page', 0);
        $data = $this->loadCommentsModData($this->_projectId, $page);

        return new JsonModel($data);
    }

    public function loadCommentsLicAction()
    {
        $this->initVars();
        $page = (int)$this->params()->fromQuery('page', 0);
        $data = $this->loadCommentsLicData($this->_projectId, $page);

        return new JsonModel($data);
    }

    public function loadRatingsAction()
    {
        $this->initVars();
        $ratings = $this->loadRatingsData($this->_projectId);

        return new JsonModel($ratings);
    }

    public function loadPlingsAction()
    {
        $this->initVars();
        $data = $this->loadPlingsData($this->_projectId);

        return new JsonModel($data);
    }

    public function loadAffiliatesAction()
    {
        $this->initVars();
        $data = $this->loadAffiliatesData($this->_projectId);

        return new JsonModel($data);
    }

    public function loadLikesAction()
    {
        $this->initVars();
        $data = $this->loadLikesData($this->_projectId);

        return new JsonModel($data);
    }

    public function loadRelationAction()
    {
        $this->initVars();
        $data = $this->loadRelationData($this->_projectId);

        return new JsonModel($data);
    }

    /**
     * @param $memberId
     *
     */
    protected function setViewDataForMyProducts($memberId)
    {
        $tableMember = new MemberRepository($this->db);
        $this->view->setVariable('member', $tableMember->findById($memberId));

        $tableProduct = $this->projectService;
        $this->view->setVariable('products', $tableProduct->fetchAllProjectsForMember($memberId));
    }

    protected function _initResponseHeader()
    {
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";

        $this->getResponse()->setMetadata(
            'X-FRAME-OPTIONS', 'ALLOWALL', true
        )//            ->setHeader('Last-Modified', $modifiedTime, true)
             ->setMetadata('Expires', $expires, true)->setMetadata('Pragma', 'no-cache', true)->setMetadata(
            'Cache-Control', 'private, max-age=0, no-cache, no-store, must-revalidate', true
        );
    }

    /**
     * @param $hits
     *
     * @return array
     * @deprecated
     */
    protected function generateProjectsArrayForView($hits)
    {
        $viewArray = array();
        $helperBuildProductUrl = new BuildProductUrl();
        foreach ($hits as $hit) {
            $project = $hit->getDocument();
            if (null != $project->username) {
                $isUpdate = ($project->type_id == 2);
                if ($isUpdate) {
                    $showUrl = $helperBuildProductUrl->buildProductUrl(
                            $project->pid
                        ) . '#anker_' . $project->project_id;
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
                        'id'    => $project->member_id,
                    ),
                );
                $viewArray[] = $projectArr;
            }
        }

        return $viewArray;
    }

    /**
     * @param $form_element
     *
     * @return array
     * @throws Exception
     */
    private function saveGalleryPics($form_element)
    {

        $imageModel = new ImageRepository($this->db, $this->configArray);

        return $imageModel->saveImages($form_element);
    }

    /**
     * @param $collection_id
     * @param $fileId
     *
     * @return int
     */
    private function getFileDownloadCount($collection_id, $fileId)
    {
        $modelFiles = $this->pploadFilesRepository;

        $countAll = $modelFiles->fetchCountDownloadsForFileAllTime($collection_id, $fileId);
        $countToday = $modelFiles->fetchCountDownloadsForFileToday($collection_id, $fileId);

        $count = (int)$countAll + (int)$countToday;

        return $count;
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
   
}