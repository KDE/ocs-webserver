<?php /** noinspection PhpUndefinedFieldInspection */

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

use Application\Model\Interfaces\LoginHistoryInterface;
use Application\Model\Interfaces\MemberDownloadHistoryInterface;
use Application\Model\Interfaces\MemberScoreInterface;
use Application\Model\Interfaces\PaypalValidStatusInterface;
use Application\Model\Interfaces\ProjectFollowerInterface;
use Application\Model\Interfaces\ProjectPlingsInterface;
use Application\Model\Interfaces\ProjectRatingInterface;
use Application\Model\Interfaces\SupportInterface;
use Application\Model\Repository\MemberDownloadHistoryRepository;
use Application\Model\Repository\PayoutStatusRepository;
use Application\Model\Service\AvatarService;
use Application\Model\Service\Interfaces\CollectionServiceInterface;
use Application\Model\Service\Interfaces\InfoServiceInterface;
use Application\Model\Service\Interfaces\MemberServiceInterface;
use Application\Model\Service\Interfaces\ProjectPlingsServiceInterface;
use Application\Model\Service\Interfaces\ProjectServiceInterface;
use Application\Model\Service\Interfaces\SectionSupportServiceInterface;
use Application\Model\Service\Interfaces\StatDownloadServiceInterface;
use Application\Model\Service\Interfaces\TagServiceInterface;
use Application\Model\Service\Ocs\Gitlab;
use Application\Model\Service\Paginator\CollectionsForMemberAdapter;
use Application\Model\Service\Paginator\DownloadhistoryAdapter;
use Application\Model\Service\Paginator\ProjectsForMemberAdapter;
use Application\Model\Service\SectionService;
use Application\Model\Service\UtilReact;
use Application\View\Helper\FetchHeaderData;
use Exception;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Http\Response;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class UserController
 *
 * @package Application\Controller
 */
class UserController extends BaseController
{
    const PAGELIMIT20 = 20;
    const PAGELIMIT50 = 50;
    const PAGELIMIT25 = 25;

    protected $_pageLimitProjects = 50;
    protected $_pageProjects = 1;
    protected $_pageLimitComments = 50;
    protected $_pageComments = 1;
    protected $_pageLimit = 20;
    /** @var MemberServiceInterface */
    private $memberService;
    /** @var ProjectServiceInterface */
    private $projectService;
    /** @var StatDownloadServiceInterface */
    private $statDownloadService;
    /** @var ProjectPlingsServiceInterface */
    private $projectPlingsService;
    /** @var SectionSupportServiceInterface */
    private $sectionSupportService;
    /** @var InfoServiceInterface */
    private $infoService;
    /** @var ProjectPlingsInterface */
    private $projectPlingsRepository;
    /** @var ProjectRatingInterface */
    private $projectRatingRepository;
    /** @var ProjectFollowerInterface */
    private $projectFollowerRepository;
    /** @var LoginHistoryInterface */
    private $loginHistoryRepository;
    /** @var MemberScoreInterface */
    private $memberScoreRepository;
    /** @var PaypalValidStatusInterface */
    private $paypalValidStatusRepository;
    /** @var SupportInterface */
    private $supportRepository; // for user projects
    /** @var MemberDownloadHistoryRepository */
    private $memberDownloadHistoryRepository;
    /** @var CollectionServiceInterface */
    private $collectionService; // for comments
    /** @var Gitlab */
    private $gitlabService;
    /** @var TagServiceInterface */
    private $tagService;
    /** @var AvatarService */
    private $avatarService;
    /** @var \stdClass */
    private $requestUser;
    /** @var SectionService */
    private $sectionService;

    public function __construct(
        MemberServiceInterface $memberService,
        ProjectServiceInterface $projectService,
        StatDownloadServiceInterface $statDownloadService,
        ProjectPlingsServiceInterface $projectPlingsService,
        ProjectPlingsInterface $projectPlingsRepository,
        ProjectRatingInterface $projectRatingRepository,
        ProjectFollowerInterface $projectFollowerRepository,
        LoginHistoryInterface $loginHistoryRepository,
        MemberScoreInterface $memberScoreRepository,
        SectionSupportServiceInterface $sectionSupportService,
        InfoServiceInterface $infoService,
        PaypalValidStatusInterface $paypalValidStatusRepository,
        SupportInterface $supportRepository,
        MemberDownloadHistoryInterface $memberDownloadHistoryRepository,
        CollectionServiceInterface $collectionService,
        Gitlab $gitlabService,
        TagServiceInterface $tagService,
        AvatarService $avatarService,
        SectionService $sectionService

    ) {
        parent::__construct();
        $this->memberService = $memberService;
        $this->projectService = $projectService;
        $this->statDownloadService = $statDownloadService;
        $this->projectPlingsService = $projectPlingsService;
        $this->projectPlingsRepository = $projectPlingsRepository;
        $this->projectRatingRepository = $projectRatingRepository;
        $this->projectFollowerRepository = $projectFollowerRepository;
        $this->loginHistoryRepository = $loginHistoryRepository;
        $this->memberScoreRepository = $memberScoreRepository;
        $this->sectionSupportService = $sectionSupportService;
        $this->infoService = $infoService;
        $this->paypalValidStatusRepository = $paypalValidStatusRepository;
        $this->supportRepository = $supportRepository;
        $this->memberDownloadHistoryRepository = $memberDownloadHistoryRepository;
        $this->collectionService = $collectionService;
        $this->gitlabService = $gitlabService;
        $this->tagService = $tagService;
        $this->avatarService = $avatarService;
        $this->sectionService = $sectionService;
        $this->requestUser = new \stdClass();
    }

    /**
     * @acl(access=public)
     */
    public function indexAction()
    {
        $this->setLayout();
        $this->layout()->noheader = false;
        //$viewModel = new ViewModel();
        $viewModel = $this->view;

        $this->initUserRequest();

        if ($this->requestUser->member == null) {
            $viewModel->setVariable('headTitle', ($this->getHeadTitle()));

            $this->getResponse()->setStatusCode(404);
            $viewModel->setTemplate('error/404-astronaut.phtml');

            return $viewModel;
        }

        $viewModel = $this->prepareIndex($viewModel);
        $viewModel->setVariable('headTitle', ($this->requestUser->member->username . ' - ' . $this->getHeadTitle()));

        return $viewModel;
    }

    private function initUserRequest()
    {
        $this->requestUser->member = null;
        $this->requestUser->userName = $this->params()->fromRoute('username', null);
        $this->requestUser->memberId = (int)$this->params()->fromRoute('member_id', null);

        if ($this->requestUser->userName) {
            $this->requestUser->memberId = $this->memberService->fetchActiveUserByUsername($this->requestUser->userName);
        }

        $this->requestUser->member = $this->memberService->fetchMember($this->requestUser->memberId);
    }

    protected function getHeadTitle()
    {
        return $GLOBALS['ocs_store']->template['head']['browser_title'];
    }

    private function prepareIndex(ViewModel $viewModel)
    {
        $requested_member_id = $this->requestUser->memberId;
        $member = $this->requestUser->member;
        if (empty($member)) {
            return $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
        }

        $mainProject = $this->projectService->fetchMainProject($requested_member_id);
        $memberScore = $this->memberScoreRepository->fetchScore($requested_member_id);
        $aboutmeUserInfo = $this->getAboutUserInfo($requested_member_id, $this->requestUser->userName);
        $affiliates = $this->sectionSupportService->fetchAffiliatesForMember($requested_member_id);
        // products
        $total_records = $this->projectService->countAllProjectsForMemberCatFilter($requested_member_id, true, null);
        $userProducts = $this->projectService->getUserActiveProjects($requested_member_id, $this->_pageLimitProjects, ($this->_pageProjects - 1) * $this->_pageLimitProjects);
        $userFeaturedProducts = $this->projectService->fetchAllFeaturedProjectsForMember($requested_member_id);
        $userCollections = $this->projectService->fetchAllCollectionsForMember($requested_member_id);
        // comments
        $paginationComments = $this->memberService->fetchComments($requested_member_id);
        // favs
        $likes = $this->projectFollowerRepository->fetchLikesForMember($requested_member_id);
        // plings
        $plings = $this->projectPlingsService->fetchPlingsForMember($requested_member_id);
        // plings
        $pslist = $this->projectPlingsService->fetchPlingsForSupporter($requested_member_id);
        // rated
        $rated = $this->projectRatingRepository->getRatedForMember($requested_member_id);

        $viewModel->setVariable('isAdmin', $this->currentUser()->isAdmin());
        $viewModel->setVariable('mainProject', $mainProject);
        $viewModel->setVariable('member', $member);
        $viewModel->setVariable('memberScore', $memberScore);
        $viewModel->setVariable('aboutmeUserInfo', $aboutmeUserInfo);
        $viewModel->setVariable('url_gitlab', $this->ocsConfig->settings->client->default->url_gitlab);
        $viewModel->setVariable('url_forum', $this->ocsConfig->settings->client->default->url_forum);

        $viewModel->setVariable('affiliates', $affiliates);

        // products
        $viewModel->setVariable('pageLimit', $this->_pageLimitProjects);
        $viewModel->setVariable('projectpage', 1);
        $viewModel->setVariable('total_records', $total_records);
        $viewModel->setVariable('userProducts', $userProducts);

        $viewModel->setVariable('userFeaturedProducts', $userFeaturedProducts);
        $viewModel->setVariable('userCollections', $userCollections);

        // comments
        if ($paginationComments) {
            $paginationComments->setItemCountPerPage($this->_pageLimitComments);
            $paginationComments->setCurrentPageNumber($this->_pageComments);
            $viewModel->setVariable('comments', $paginationComments);
        }

        // favs
        $likes->setItemCountPerPage($this->_pageLimit);
        $likes->setCurrentPageNumber(1);
        $viewModel->setVariable('likes', $likes);

        // plings
        $plings->setItemCountPerPage($this->_pageLimit);
        $plings->setCurrentPageNumber(1);
        $viewModel->setVariable('plings', $plings);

        // plings
        $pslist->setItemCountPerPage($this->_pageLimit);
        $pslist->setCurrentPageNumber(1);
        $viewModel->setVariable('supportersplings', $pslist);

        // rated
        $ratedlist = new Paginator(new ArrayAdapter($rated));
        $ratedlist->setItemCountPerPage($this->_pageLimit);
        $ratedlist->setCurrentPageNumber(1);
        $viewModel->setVariable('rated', $ratedlist);

        // count of github projects
        $cntGitp = 0;
        try {
            if ($member->gitlab_user_id) {
                $gitProjects = $this->gitlabService->getUserProjects($member->gitlab_user_id);
                $cntGitp = count($gitProjects);
            }
        } catch (Exception $exc) {
            $cntGitp = 0;
        }
        $viewModel->setVariable('cntGitp', $cntGitp);

        // collect information for admin
        // =============================
        if ($this->currentUser()->isAdmin()) {

            $earnInfo = '<span style="font-style: italic;"> Last month I earned 0.</span>';
            $amoutEarn = $this->statDownloadService->getLastMonthEarn($requested_member_id);
            if ($amoutEarn && $amoutEarn['amount']) {
                $earnInfo = '<span style="font-style: italic;"> Last month I earned $' . number_format($amoutEarn['amount'], 2, '.', '') . '.</span>';
            }
            $viewModel->setVariable('earnInfo', $earnInfo);

            $lastLoginData = $this->loginHistoryRepository->fetchLastLoginData($requested_member_id);
            $viewModel->setVariable('lastLoginData', $lastLoginData);
            $firstLoginData = $this->loginHistoryRepository->fetchFirstLoginData($requested_member_id);
            $viewModel->setVariable('firstLoginData', $firstLoginData);

            //computer info
            $tagmodel = $this->tagService;
            $gidsstring = $this->ocsConfig->settings->client->default->tag_group_osuser;
            $gids = explode(",", $gidsstring);
            $data = $tagmodel->getTagGroupsOSUser();
            $data2 = $tagmodel->getTagsOSUser($member->member_id);

            $viewModel->setVariable('gids', $gids);
            $viewModel->setVariable('data', $data);
            $viewModel->setVariable('data2', $data2);
            //computer info end                       
        }
        // stat
        $stat = $this->getStats($total_records, count($userFeaturedProducts), count($userCollections), $requested_member_id, $paginationComments->getTotalItemCount());
        $viewModel->setVariable('stat', $stat);
        // =============================

        return $viewModel;
    }

    private function getAboutUserInfo($member_id, $username)
    {
        $cache = new SimpleCacheDecorator($GLOBALS['ocs_cache']);
        $cache_name = __FUNCTION__ . md5($member_id . $username);
        if ($userinfo = $cache->get($cache_name)) {
            return $userinfo;
        }

        $userProjectCategories = $this->projectService->getUserCreatingCategories($member_id);
        $cnt = count($userProjectCategories);
        $userinfo = '';

        if ($cnt > 0) {
            $userinfo = "Hi, I am <b>" . $username . "</b> and I create ";
            if ($cnt == 1) {
                $userinfo = $userinfo . ' <b>' . $userProjectCategories[0]['category1'] . '</b>.';
            } elseif ($cnt == 2) {
                $userinfo = $userinfo . ' <b>' . $userProjectCategories[0]['category1'] . '</b>' . ' and <b>' . $userProjectCategories[1]['category1'] . '</b>.';
            } elseif ($cnt == 3) {
                $userinfo = $userinfo . ' <b>' . $userProjectCategories[0]['category1'] . '</b>' . ', <b>' . $userProjectCategories[1]['category1'] . '</b>' . ' and <b>' . $userProjectCategories[2]['category1'] . '</b>.';
            } else {
                $userinfo = $userinfo . ' <b>' . $userProjectCategories[0]['category1'] . '</b>' . ', <b>' . $userProjectCategories[1]['category1'] . '</b>' . ', <b>' . $userProjectCategories[2]['category1'] . '</b>' . ' and more.';
            }
        } else {
            $userinfo = "Hi, I am <b>" . $username . "</b>.";
        }

        $mModel = $this->memberService;
        $supportSections = $mModel->fetchSupporterSectionInfo($member_id);
        if ($supportSections && $supportSections['sections']) {
            $userinfo = $userinfo . " I " . ($cnt == 0 ? " " : " also ") . "support";
            $sections = explode(",", $supportSections['sections']);
            foreach ($sections as $s) {
                $userinfo .= " <b>" . $s . "</b>, ";
            }

            $userinfo = trim($userinfo);
            $userinfo = substr($userinfo, 0, -1);
        }
        if (substr($userinfo, strlen($userinfo) - 1) <> ".") {
            $userinfo .= ".";
        }

        $cache->set($cache_name, $userinfo, 60);

        return $userinfo;
    }

    /**
     * @param int $total_records
     * @param int $countUserFeaturedProducts
     * @param int $countUserCollections
     * @param int $requested_member_id
     * @param int $countComments
     *
     * @return array
     */
    private function getStats(
        $total_records,
        $countUserFeaturedProducts,
        $countUserCollections,
        $requested_member_id,
        $countComments
    ) {
        $cache = new SimpleCacheDecorator($GLOBALS['ocs_cache']);
        $cache_name = __FUNCTION__ . '_x' . $total_records . $countUserFeaturedProducts . $countUserCollections . $countComments . $requested_member_id . ($this->currentUser()->isAdmin()?'isAdmin':'');
        $hashed_name = hash('haval128,4', $cache_name);
        try {
            if ($stat = $cache->get($hashed_name)) {
                return $stat;
            }
        } catch (InvalidArgumentException $e) {
            $this->ocsLog->err(__METHOD__ . '_' . $e->getMessage());
        }

        $stat = array();
        $stat['cntProducts'] = $total_records;
        $stat['cntFProducts'] = 0;
        $stat['cntCollections'] = 0;
        $stat['cntFProducts'] = $countUserFeaturedProducts;
        $stat['cntCollections'] = $countUserCollections;

        $stat['cntOrinalProducts'] = $this->projectService->getOriginalProjectsForMemberCnt($requested_member_id);
        $stat['cntComments'] = $countComments;
        $stat['cntLikesHeGave'] = $this->projectFollowerRepository->countLikesHeGave($requested_member_id);
        $stat['cntLikesHeGot'] = $this->projectFollowerRepository->countLikesHeGot($requested_member_id);
        $stat['cntPlingsHeGave'] = $this->projectPlingsRepository->countPlingsHeGave($requested_member_id);
        $stat['cntPlingsHeGot'] = $this->projectPlingsRepository->countPlingsHeGot($requested_member_id);
        $donationinfo = $this->memberService->fetchSupporterDonationInfo($requested_member_id);
        if ($donationinfo) {
            $stat['donationIssupporter'] = $donationinfo['issupporter'];
            $stat['donationMax'] = $donationinfo['active_time_max'];
            $stat['donationMin'] = $donationinfo['active_time_min'];
            $stat['donationCnt'] = $donationinfo['cnt'];
           
            $sectionsCount = $this->memberService->fetchSupportersActiveYears($requested_member_id);
            $stat['donationIssupporterSection'] = $sectionsCount;

            $activemonths = $this->infoService->getSupporterActiveMonths($requested_member_id);
            $stat['donationActivemonths'] = $activemonths;
        }

        $subscriptioninfo = $this->memberService->fetchSupporterSubscriptionInfo($requested_member_id);
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

        $stat['userLastActiveTime'] = $this->memberService->fetchLastActiveTime($requested_member_id);

        $stat['cntDuplicateSourceurl'] = 0;

        if ($this->currentUser()->isAdmin()) {
            $stat['cntDuplicateSourceurl'] = $this->projectService->getCountProjectsDuplicateSourceurl($requested_member_id);
            $stat['cntUnpublished'] = $this->projectService->getUnpublishedProjectsForMemberCnt($requested_member_id);
            $stat['cntDeleted'] = $this->projectService->getDeletedProjectsForMemberCnt($requested_member_id);
        }
        try {
            $cache->set($hashed_name, $stat, 600);
        } catch (InvalidArgumentException $e) {
            $this->ocsLog->err(__METHOD__ . '_' . $e->getMessage());
        }

        return $stat;
    }

    /**
     * @return \Laminas\Http\PhpEnvironment\Response|\Laminas\Stdlib\ResponseInterface|JsonModel|ViewModel
     * @acl(access=public)
     */
    public function indexReactAction()
    {
        $this->setLayout();
        $this->layout()->noheader = false;
        $viewModel = new ViewModel();

        $this->initUserRequest();

        if ($this->requestUser->member == null) {
            $viewModel->setVariable('headTitle', ($this->getHeadTitle()));

            return $this->getResponse()->setStatusCode(404);
        }
       
        $result = $this->prepareIndexReact($viewModel);
       
        $fetchHeaderData = new FetchHeaderData($this->sectionService, $this->infoService);
        $header = $fetchHeaderData(null);
        $result['header'] = $header;
        $viewModel->setVariable('headTitle', ($this->requestUser->member->username . ' - ' . $this->getHeadTitle()));
        $json = (int)$this->params()->fromQuery('json', 0);
        if ($json == 1) {
            return new JsonModel($result);
        } else {

            $viewModel->setVariable('memberData', $result);

            return $viewModel;
        }
    }

    private function prepareIndexReact(ViewModel $viewModel)
    {
        $result = [];
        $requested_member_id = $this->requestUser->memberId;        
        $member = $this->memberService->fetchMemberData($requested_member_id);
        
        if (empty($member)) {

            return $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
        }
        $lastLoginData = $this->loginHistoryRepository->fetchLastLoginData($requested_member_id);
        $mainProject = $this->projectService->fetchMainProject($requested_member_id);
        $memberScore = $this->memberScoreRepository->fetchScore($requested_member_id);
        $aboutmeUserInfo = $this->getAboutUserInfo($requested_member_id, $this->requestUser->userName);
        $result['isAdmin'] = $this->currentUser()->isAdmin();

        // TODO find out why this not working. above is working around.  because of ip_inet, ipv4_inet, ipv6_inet are BLOB in DB
        $result['lastLoginData'] = $lastLoginData;
        $earnInfo = '';
        if ($this->currentUser()->isAdmin()) {
            $amoutEarn = $this->statDownloadService->getLastMonthEarn($requested_member_id);
            if ($amoutEarn && $amoutEarn['amount']) {
                $earnInfo = ' Last month I earned $' . number_format($amoutEarn['amount'], 2, '.', '') . '.';
            } else {
                $earnInfo = ' Last month I earned 0.';
            }
        }

        $result['mainProject'] = $mainProject;
        $viewModel->setVariable('member', $member);
        $viewModel->setVariable('mainProject', $mainProject);
        $result['member'] = $member;
        $result['memberScore'] = $memberScore;
        $result['aboutmeUserInfo'] = $aboutmeUserInfo;
        $result['earnInfo'] = $earnInfo;
        $result['url_gitlab'] = $this->ocsConfig->settings->client->default->url_gitlab;
        $result['url_forum'] = $this->ocsConfig->settings->client->default->url_forum;


        $affiliates = $this->sectionSupportService->fetchAffiliatesForMember($requested_member_id);
        $result['affiliates'] = $affiliates;

        // products
        $total_records = $this->projectService->countAllProjectsForMemberCatFilter($requested_member_id, true, null);
        $userProducts = $this->projectService->getUserActiveProjects($requested_member_id, $this->_pageLimitProjects, ($this->_pageProjects - 1) * $this->_pageLimitProjects);

        $result['pageLimit'] = $this->_pageLimitProjects;
        $result['projectpage'] = 1;
        $result['total_records'] = $total_records;
        $result['userProducts'] = $userProducts;

        $userFeaturedProducts = $this->projectService->fetchAllFeaturedProjectsForMember($requested_member_id);
        $userCollections = $this->projectService->fetchAllCollectionsForMember($requested_member_id);
        $result['userFeaturedProducts'] = $userFeaturedProducts;
        $result['userCollections'] = $userCollections;

        // comments
        $paginationComments = $this->memberService->fetchComments($requested_member_id);
        if ($paginationComments) {
            $paginationComments->setItemCountPerPage($this->_pageLimitComments);
            $paginationComments->setCurrentPageNumber($this->_pageComments);
            $result['comments'] = iterator_to_array($paginationComments->getCurrentItems(), true);
        }


        // favs
        $likes = $this->projectFollowerRepository->fetchLikesForMember($requested_member_id);
        $likes->setItemCountPerPage($this->_pageLimit);
        $likes->setCurrentPageNumber(1);
        $result['likes'] = iterator_to_array($likes->getCurrentItems(), true);


        // plings
        $plings = $this->projectPlingsService->fetchPlingsForMember($requested_member_id);
        $plings->setItemCountPerPage($this->_pageLimit);
        $plings->setCurrentPageNumber(1);
        $result['plings'] = iterator_to_array($plings->getCurrentItems(), true);


        // plings
        $pslist = $this->projectPlingsService->fetchPlingsForSupporter($requested_member_id);
        $pslist->setItemCountPerPage($this->_pageLimit);
        $pslist->setCurrentPageNumber(1);
        $result['supportersplings'] = iterator_to_array($pslist->getCurrentItems(), true);


        // rated
        $rated = $this->projectRatingRepository->getRatedForMember($requested_member_id);
        $ratedlist = new Paginator(new ArrayAdapter($rated));
        $ratedlist->setItemCountPerPage($this->_pageLimit);
        $ratedlist->setCurrentPageNumber(1);
        $result['rated'] = iterator_to_array($ratedlist->getCurrentItems(), true);


        // stat
        $stat = array();
        $stat['cntProducts'] = $total_records;
        if ($userFeaturedProducts) {
            $cnt = 0;
            foreach ($userFeaturedProducts as $tmp) {
                $cnt++;
            }
            $stat['cntFProducts'] = $cnt;
        } else {
            $stat['cntFProducts'] = 0;
        }

        if ($userCollections) {
            $cnt = 0;
            foreach ($userCollections as $tmp) {
                $cnt++;
            }
            $stat['cntCollections'] = $cnt;
        } else {
            $stat['cntCollections'] = 0;
        }

        $stat['cntOrinalProducts'] = $this->projectService->getOriginalProjectsForMemberCnt($requested_member_id);
        $stat['cntComments'] = $paginationComments->getTotalItemCount();
        $stat['cntRated'] = count($rated);
        $stat['cntLikesHeGave'] = $this->projectFollowerRepository->countLikesHeGave($requested_member_id);
        $stat['cntLikesHeGot'] = $this->projectFollowerRepository->countLikesHeGot($requested_member_id);
        $stat['cntPlingsHeGave'] = $this->projectPlingsRepository->countPlingsHeGave($requested_member_id);
        $stat['cntPlingsHeGot'] = $this->projectPlingsRepository->countPlingsHeGot($requested_member_id);
        $donationinfo = $this->memberService->fetchSupporterDonationInfo($requested_member_id);
        if ($donationinfo) {
            $stat['donationIssupporter'] = $donationinfo['issupporter'];
            $stat['donationMax'] = $donationinfo['active_time_max'];
            $stat['donationMin'] = $donationinfo['active_time_min'];
            $stat['donationCnt'] = $donationinfo['cnt'];
            
            $sectionsCount = $this->memberService->fetchSupportersActiveYears($requested_member_id);
            $stat['donationIssupporterSection'] = $sectionsCount;

            $activemonths = $this->infoService->getSupporterActiveMonths($requested_member_id);
            $stat['donationActivemonths'] = $activemonths;
        }


        $subscriptioninfo = $this->memberService->fetchSupporterSubscriptionInfo($requested_member_id);

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

        $stat['userLastActiveTime'] = $this->memberService->fetchLastActiveTime($requested_member_id);

        $stat['cntDuplicateSourceurl'] = 0;

        if ($this->currentUser()->isAdmin()) {
            $stat['cntDuplicateSourceurl'] = $this->projectService->getCountProjectsDuplicateSourceurl($requested_member_id);
            $stat['cntUnpublished'] = $this->projectService->getUnpublishedProjectsForMemberCnt($requested_member_id);
            $stat['cntDeleted'] = $this->projectService->getDeletedProjectsForMemberCnt($requested_member_id);
        }
        $result['stat'] = $stat;

        // count of github projects
        $cntGitp = 0;
        try {
            if ($member->gitlab_user_id) {
                $gitProjects = $this->gitlabService->getUserProjects($member->gitlab_user_id);
                $cntGitp = count($gitProjects);
            }
        } catch (Exception $exc) {
            $cntGitp = 0;
        }
        $result['cntGitp'] = $cntGitp;


        //computer info
        $tagmodel = $this->tagService;
        $gidsstring = $this->ocsConfig->settings->client->default->tag_group_osuser;
        $gids = explode(",", $gidsstring);
        $data = $tagmodel->getTagGroupsOSUser();
        $data2 = $tagmodel->getTagsOSUser($member->member_id);

        $result['gids'] = $gids;
        $result['data'] = $data;
        $result['data2'] = $data2;

        return $result;
    }

    /**
     * @throws Exception
     * @acl(access=public)
     */
    public function moreproductsAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }
        $data = [];
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $page = (int)$this->params()->fromPost('projectpage', 1);

        $total_records = $this->projectService->countAllProjectsForMemberCatFilter($this->requestUser->memberId, true, null);
        $userProducts = $this->projectService->getUserActiveProjects($this->requestUser->memberId, $this->_pageLimitProjects, ($page - 1) * $this->_pageLimitProjects);

        $viewModel->setVariable('pageLimit', $this->_pageLimitProjects);
        $viewModel->setVariable('projectpage', $page);
        $viewModel->setVariable('total_records', $total_records);
        $viewModel->setVariable('userProducts', $userProducts);

        $data['pageLimit'] = $this->_pageLimitProjects;
        $data['projectpage'] = $page;
        $data['total_records'] = $total_records;
        $data['userProducts'] = $userProducts;

        if ($page > 1) {
            $lastproject = $this->projectService->getUserActiveProjects(
                $this->requestUser->memberId, 1, (($page - 1) * $this->_pageLimitProjects - 1)
            );
            if ($lastproject && is_array($lastproject)) {
                foreach ($lastproject as $value) {
                    $viewModel->setVariable('lastcatid', $value['project_category_id']);
                    $data['lastcatid'] = $value['project_category_id'];
                }
            }
        }

        $viewModel->setTemplate('/application/user/partials/aboutme-products.phtml');

        return $this->doReturn($viewModel, $data);
    }

    /**
     * @param ViewModel $viewModel
     * @param array     $data
     *
     * @return JsonModel|ViewModel
     */
    private function doReturn(ViewModel $viewModel, array $data)
    {
        $json = (int)$this->params()->fromQuery('j', 0);
        if ($json == 1) {
            return new JsonModel($data);
        }

        return $viewModel;
    }

    /**
     * @return JsonModel|ViewModel
     * @throws Exception
     * @acl(access=public)
     */
    public function morecommentsAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            // $this->getResponse()->setStatusCode(404);
            return new ViewModel();
        }

        $data = [];
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $page = (int)$this->params()->fromRoute('page', 1);

        $paginationComments = $this->memberService->fetchComments($this->requestUser->memberId);
        if ($paginationComments) {
            $paginationComments->setItemCountPerPage($this->_pageLimitComments);
            $paginationComments->setCurrentPageNumber($page);
            $viewModel->setVariable('comments', $paginationComments);
            $data['comments'] = iterator_to_array($paginationComments->getCurrentItems(), true);
        }

        $viewModel->setVariable('member', $this->requestUser->member);
        $data['member'] = $this->requestUser->member;

        $viewModel->setTemplate('/application/user/partials/loopMyComments.phtml');

        return $this->doReturn($viewModel, $data);
    }

    /**
     * @return JsonModel|ViewModel
     * @acl(access=public)
     */
    public function moreratesAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }

        $data = [];
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $page = (int)$this->params()->fromRoute('page', 1);
        $rated = $this->projectRatingRepository->getRatedForMember($this->requestUser->memberId);
        $ratedlist = new Paginator(new ArrayAdapter($rated));
        $ratedlist->setItemCountPerPage($this->_pageLimit);
        $ratedlist->setCurrentPageNumber($page);
        $viewModel->setVariable('rated', $ratedlist);
        $data['rated'] = iterator_to_array($ratedlist->getCurrentItems(), true);
        $data['member'] = $this->requestUser->member;
        $viewModel->setVariable('member', $this->requestUser->member);

        $viewModel->setTemplate('/application/user/partials/loopRated.phtml');

        return $this->doReturn($viewModel, $data);
    }

    /**
     * @return JsonModel|ViewModel
     * @acl(access=public)
     */
    public function morelikesAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }
        $data = [];
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $page = $this->params()->fromRoute('page', 1);
        $likes = $this->projectFollowerRepository->fetchLikesForMember($this->requestUser->memberId);
        $likes->setItemCountPerPage($this->_pageLimit);
        $likes->setCurrentPageNumber($page);
        $data['likes'] = iterator_to_array($likes->getCurrentItems(), true);
        $viewModel->setVariable('likes', $likes);
        $viewModel->setVariable('member', $this->requestUser->member);
        $data['member'] = $this->requestUser->member;


        $viewModel->setTemplate('/application/user/partials/aboutme-likes.phtml');

        return $this->doReturn($viewModel, $data);
    }

    /**
     * @return JsonModel|ViewModel
     * @acl(access=public)
     */
    public function moreplingsAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }

        $data = [];
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $page = $this->params()->fromRoute('page', 1);
        $plings = $this->projectPlingsService->fetchPlingsForMember($this->requestUser->memberId);
        $plings->setItemCountPerPage($this->_pageLimit);
        $plings->setCurrentPageNumber($page);

        $data['rated'] = iterator_to_array($plings->getCurrentItems(), true);
        $data['member'] = $this->requestUser->member;

        $viewModel->setVariable('plings', $plings);
        $viewModel->setVariable('member', $this->requestUser->member);

        $viewModel->setTemplate('/application/user/partials/aboutme-plings.phtml');

        return $this->doReturn($viewModel, $data);

    }

    /**
     * @return JsonModel|ViewModel
     * @acl(access=public)
     */
    public function showoriginalAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }
        $data = [];
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $projectpage = (int)$this->params()->fromPost('projectpage', 1);

        $pageLimit = $this->_pageLimitProjects;
        $total_records = $this->projectService->getOriginalProjectsForMemberCnt($this->requestUser->memberId);
        $viewModel->setVariable('pageLimit', $pageLimit);
        $viewModel->setVariable('projectpage', $projectpage);
        $viewModel->setVariable('total_records', $total_records);

        $data['pageLimit'] = $pageLimit;
        $data['projectpage'] = $projectpage;
        $data['total_records'] = $total_records;

        if ($projectpage > 1) {
            $lastproject = $this->projectService->getOriginalProjectsForMember(
                $this->requestUser->memberId, 1, (($projectpage - 1) * $pageLimit - 1)
            );
            if ($lastproject && is_array($lastproject)) {
                foreach ($lastproject as $value) {
                    $viewModel->setVariable('lastcatid', $value['project_category_id']);
                    $data['lastcatid'] = $value['project_category_id'];
                }
            }
        }
        $userProducts = $this->projectService->getOriginalProjectsForMember(
            $this->requestUser->memberId, $pageLimit, ($projectpage - 1) * $pageLimit
        );
        $viewModel->setVariable('member', $this->requestUser->member);
        $viewModel->setVariable('userProducts', $userProducts);
        $viewModel->setTemplate('/application/user/partials/aboutme-products.phtml');

        $data['member'] = $this->requestUser->member;
        $data['userProducts'] = $userProducts;

        return $this->doReturn($viewModel, $data);
    }

      /**
     * @throws Exception
     * @acl(access=public)
     */
    public function userMoreproductsAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);
            return new JsonModel();
        }
        $data = [];
        $page = (int)$this->params()->fromQuery('page', 1);

        $total_records = $this->projectService->countAllProjectsForMemberCatFilter($this->requestUser->memberId, true, null);
        $userProducts = $this->projectService->getUserActiveProjects($this->requestUser->memberId, self::PAGELIMIT50, ($page - 1) * self::PAGELIMIT50);    
        $userProducts =  UtilReact::productImageSmall200($userProducts);  
        $data['pageLimit'] = self::PAGELIMIT50;
        $data['projectpage'] = $page;
        $data['total_records'] = $total_records;
        $data['userProducts'] = $userProducts;
        return new JsonModel($data);
    }

    /**
     * @return JsonModel|ViewModel
     * @throws Exception
     * @acl(access=public)
     */
    public function userMorecommentsAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);
            return new JsonModel();
        }

        $data = [];
        $page = (int)$this->params()->fromQuery('page', 1);

        $paginationComments = $this->memberService->fetchComments($this->requestUser->memberId);
        if ($paginationComments) {
            $paginationComments->setItemCountPerPage(self::PAGELIMIT50);
            $paginationComments->setCurrentPageNumber($page);
            $array =  iterator_to_array($paginationComments->getCurrentItems(), true);           
            $data['comments'] =  UtilReact::productImageSmall200($array);  

        }
        return new JsonModel($data);
    }


    /**
     * @return JsonModel|ViewModel
     * @acl(access=public)
     */
    public function userMoreratesAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);
            return new JsonModel();
        }

        $data = [];
        $page = (int)$this->params()->fromQuery('page', 1);

        $rated = $this->projectRatingRepository->getRatedForMember($this->requestUser->memberId);
        $ratedlist = new Paginator(new ArrayAdapter($rated));
        $ratedlist->setItemCountPerPage(self::PAGELIMIT20);
        $ratedlist->setCurrentPageNumber($page);        
        $data['rated'] = iterator_to_array($ratedlist->getCurrentItems(), true);
        // $data['member'] = $this->requestUser->member;
        return new JsonModel($data);
    }

    /**
     * @return JsonModel|ViewModel
     * @acl(access=public)
     */
    public function userMorelikesAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);
            return new JsonModel();
        }

        $data = [];
        $page = (int)$this->params()->fromQuery('page', 1);
        $likes = $this->projectFollowerRepository->fetchLikesForMember($this->requestUser->memberId);
        $likes->setItemCountPerPage(self::PAGELIMIT20);
        $likes->setCurrentPageNumber($page);
        $array =  iterator_to_array($likes->getCurrentItems(), true);           
        $data['likes'] =  UtilReact::productImageSmall200($array);  
    
        return new JsonModel($data);
    }

    /**
     * @return JsonModel|ViewModel
     * @acl(access=public)
     */
    public function userMoreplingsAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);
            return new JsonModel();
        }

        $data = [];
        $page = (int)$this->params()->fromQuery('page', 1);
        $plings = $this->projectPlingsService->fetchPlingsForMember($this->requestUser->memberId);
        $plings->setItemCountPerPage(self::PAGELIMIT20);
        $plings->setCurrentPageNumber($page);
        $array =  iterator_to_array($plings->getCurrentItems(), true);           
        $data['plings'] =  UtilReact::productImageSmall200($array);  
     
        return new JsonModel($data);
    }

    /**
     * @return JsonModel|ViewModel
     * @acl(access=public)
     */
    public function userShoworiginalAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);
            return new JsonModel();
        }
        $data = [];
        $page = (int)$this->params()->fromQuery('page', 1);
        
        $total_records = $this->projectService->getOriginalProjectsForMemberCnt($this->requestUser->memberId);
       
        $data['pageLimit'] = self::PAGELIMIT50;
        $data['projectpage'] = $page;
        $data['total_records'] = $total_records;
       
        $userProducts = $this->projectService->getOriginalProjectsForMember(
            $this->requestUser->memberId, self::PAGELIMIT50, ($page - 1) * self::PAGELIMIT50
        );      
      
        $data['userProducts'] =  UtilReact::productImageSmall200($userProducts);  
     
        return new JsonModel($data);
    }

    /**
     * @return JsonModel|ViewModel
     * @acl(access=public)
     */
    public function userMorefeaturedAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);
            return new JsonModel();
        }
        $data = [];
        $page = (int)$this->params()->fromQuery('page', 1);

        $total_records = $this->projectService->fetchAllFeaturedProjectsForMemberCnt($this->requestUser->memberId);
        $userProducts = $this->projectService->fetchAllFeaturedProjectsForMember($this->requestUser->memberId, self::PAGELIMIT50, ($page - 1) * self::PAGELIMIT50);
       
        $userProducts =  UtilReact::productImageSmall200($userProducts);  
     
        $data['pageLimit'] = self::PAGELIMIT50;
        $data['projectpage'] = $page;
        $data['total_records'] = $total_records;
        $data['userProducts'] = $userProducts;
        return new JsonModel($data);
    }

    /**
     * @return JsonModel|ViewModel
     * @acl(access=public)
     */
    public function userMorecollectionsAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);
            return new JsonModel();
        }
        $data = [];
        $page = (int)$this->params()->fromQuery('page', 1);

        $total_records = $this->projectService->fetchAllCollectionsForMemberCnt($this->requestUser->memberId);
        $userProducts = $this->projectService->fetchAllCollectionsForMember($this->requestUser->memberId, self::PAGELIMIT50, ($page - 1) * self::PAGELIMIT50);
        $userProducts =  UtilReact::productImageSmall200($userProducts);  
        $data['pageLimit'] = self::PAGELIMIT50;
        $data['projectpage'] = $page;
        $data['total_records'] = $total_records;
        $data['userProducts'] = $userProducts;
        return new JsonModel($data);
    }

    private function setLayout()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $headerData = $this->loadHeaderData();
        $this->layout()->setVariable('headerData',  $headerData);
    }
    /**
     * @acl(access=private)
     */
    public function productsAction()
    {
        $this->initPrivateUserRequest();
        if (false === $this->currentUser()->hasIdentity()) {

            return $this->getResponse()->setStatusCode(404);
        }

        $data = [];
        $viewModel = new ViewModel();
        $this->setLayout();

        $pageLimit = 15;
        $page = (int)$this->params()->fromRoute('page', 1);
        //create ppload download hash: secret + collection_id + expire-timestamp
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $timestamp = time() + 3600; // one hour valid
        $hash = md5($salt . $timestamp); // order isn't important at all... just do the same when verifying

        $viewModel->setVariable('download_hash', $hash);
        $viewModel->setVariable('download_timestamp', $timestamp);
        $viewModel->setVariable('member_id', null);

        $data['download_hash'] = $hash;
        $data['download_timestamp'] = $timestamp;

        $viewModel->setVariable('member_id', $this->requestUser->memberId);
        $data['member_id'] = $this->requestUser->memberId;

        $viewModel->setVariable('headTitle', ('Products - ' . $this->getHeadTitle()));

        $adapter = new ProjectsForMemberAdapter($this->projectService, $this->requestUser->memberId);
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage($pageLimit);
        $paginator->setCurrentPageNumber($page);
        $viewModel->setVariable('products', $paginator);

        $data['products'] = $paginator->getCurrentItems();


        $viewModel->setVariable('member', $this->requestUser->member);
        $viewModel->setVariable('isAdmin', $this->isAdmin());

        $data['member'] = $this->requestUser->member;
        $data['isAdmin'] = $this->isAdmin();

        return $this->doReturn($viewModel, $data);
    }

    private function initPrivateUserRequest()
    {
        $this->initUserRequest();

        if ($this->currentUser()->isAdmin()) {

            return;
        }

        if (($this->currentUser()->member_id == $this->requestUser->memberId) and ($this->currentUser()->username == $this->requestUser->userName)) {

            return;
        }

        $this->requestUser->memberId = $this->currentUser()->member_id;
        $this->requestUser->userName = $this->currentUser()->username;
        $this->requestUser->member = $this->memberService->fetchMember($this->currentUser()->member_id);
    }

    /**
     * @acl(access=private)
     */
    public function collectionsAction()
    {
        $this->initPrivateUserRequest();
        if (false == $this->currentUser()->hasIdentity()) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }
        $data = [];
        $viewModel = new ViewModel();
        $this->setLayout();
        $pageLimit = 25;
        $page = $this->params()->fromRoute('page', 1);

        $adapter = new CollectionsForMemberAdapter($this->collectionService, $this->requestUser->memberId);
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage($pageLimit);
        $paginator->setCurrentPageNumber($page);
        $viewModel->setVariable('products', $paginator);
        $data['products'] = iterator_to_array($paginator->getCurrentItems(), true);
        $member = $this->memberService->fetchMemberData($this->ocsUser->member_id);
        $viewModel->setVariable('member', $member);
        $viewModel->setVariable('isAdmin', $this->isAdmin());
        $viewModel->setVariable('headTitle', ('Listing - ' . $this->getHeadTitle()));
        $data['member'] = $this->requestUser->member;
        $data['isAdmin'] = $this->isAdmin();

        return $this->doReturn($viewModel, $data);
    }

    /**
     * @acl(access=private)
     */
    public function payoutoldAction()
    {
        $this->initPrivateUserRequest();
        $viewModel = $this->initCommonActionViewModel();
        $downloadYears = $this->statDownloadService->getUserDownloadYears($this->requestUser->memberId);
        $viewModel->setVariable('downloadYears', $downloadYears);

        return $viewModel;
    }

    private function initCommonActionViewModel()
    {
        $this->setLayout();
        $viewModel = new ViewModel();
        $view_member = $this->requestUser->member;
        $viewModel->setVariable('member', $view_member);
        if ($view_member->paypal_valid_status) {
            $paypalValidStatus = $this->paypalValidStatusRepository->findById($view_member->paypal_valid_status);
            $viewModel->setVariable('paypal_valid_status', $paypalValidStatus);
        } else {
            $viewModel->setVariable('paypal_valid_status', null);
        }

        return $viewModel;
    }

    /**
     * @racl(access=private)
     */
    public function payoutAction()
    {
        $this->initPrivateUserRequest();
        $viewModel = $this->initCommonActionViewModel();
        $downloadYears = $this->statDownloadService->getUserDownloadsAndViewsYears($this->requestUser->memberId);
        $viewModel->setVariable('downloadYears', $downloadYears);
        $viewModel->setVariable('headTitle', ('Payout - ' . $this->getHeadTitle()));

        return $viewModel;
    }

    /** @acl(access=private) */
    public function affiliatesAction()
    {
        $this->initPrivateUserRequest();
        $viewModel = $this->initCommonActionViewModel();
        $downloadYears = $this->statDownloadService->getUserAffiliatesYears($this->requestUser->memberId);
        $viewModel->setVariable('downloadYears', $downloadYears);
        $viewModel->setVariable('db', $this->db);
        $viewModel->setVariable('headTitle', ('Affiliates - ' . $this->getHeadTitle()));

        return $viewModel;
    }

    /** @acl(access=private) */
    public function sectionaffiliatesmonthajaxAction()
    {
        $this->initPrivateUserRequest();
        $viewModel = $this->initCommonActionViewModel();
        $viewModel->setTerminal(true);

        $yearmonth = null;
        if ($this->params('yearmonth')) {
            $yearmonth = $this->getParam('yearmonth');
        }
        $section_id = null;
        if ($this->params('section_id')) {
            $section_id = (int)$this->getParam('section_id');
        }
        $viewModel->setVariable('yearmonth', $yearmonth);
        $viewModel->setVariable('section_id', $section_id);
        $viewModel->setVariable('db', $this->db);

        $viewModel->setTemplate('/application/user/sectionaffiliatesmonthajax.phtml');

        return $viewModel;
    }

    public function getParam($string, $default = null)
    {
        $val = $this->params()->fromQuery($string);
        if (null == $val) {
            $val = $this->params()->fromRoute($string, null);
        }
        if (null == $val) {
            $val = $this->params()->fromPost($string, null);
        }
        if (!$val) {
            return $default;
        }

        return $val;
    }

    /** @acl(access=private) */
    public function sectionaffiliatesmonthdetailajaxAction()
    {
        $this->initPrivateUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }

        $viewModel = $this->initCommonActionViewModel();
        $viewModel->setTerminal(true);

        $viewModel->setVariable('view_member', $this->requestUser->member);

        $yearmonth = $this->params()->fromQuery('yearmonth');
        $viewModel->setVariable('yearmonth', $yearmonth);
        $sectionid = $this->params()->fromQuery('section_id');
        $viewModel->setVariable('section_id', $sectionid);
        $viewModel->setVariable('db', $this->db);

        return $viewModel;
    }

    /** @acl(access=private) */
    public function sectionsajaxAction()
    {
        $this->initPrivateUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }

        $viewModel = $this->initCommonActionViewModel();
        $viewModel->setTerminal(true);

        $viewModel->setVariable('view_member', $this->memberService->fetchMemberData($this->requestUser->memberId));

        $year = $this->params()->fromQuery('year');
        $viewModel->setVariable('year', $year);

        $currentYear = date("Y", time());

        if ($year) {
            $currentYear = $year;
        }

        $downloadMonths = $this->statDownloadService->getUserDownloadsAndViewsMonths($viewModel->getVariable('member')->member_id, $currentYear);
        $viewModel->setVariable('downloadMonths', $downloadMonths);

        $payoutStatusRepository = new PayoutStatusRepository($this->db);
        $viewModel->setVariable('payoutStatusRepository', $payoutStatusRepository);

        return $viewModel;
    }

    /** @acl(access=private) */
    public function sectionsmonthajaxAction()
    {
        $this->initPrivateUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }

        $viewModel = $this->initCommonActionViewModel();
        $viewModel->setTerminal(true);

        $viewModel->setVariable('view_member', $this->requestUser->member);

        $yearmonth = $this->params()->fromQuery('yearmonth');
        $viewModel->setVariable('yearmonth', $yearmonth);

        $allAffiliates = $this->statDownloadService->getUserAffiliatesSumForMonth($viewModel->getVariable('member')->member_id, $yearmonth);
        $viewModel->setVariable('allAffiliates', $allAffiliates);

        $allDownloads = $this->statDownloadService->getUserSectionsForDownloadAndViewsForMonth($viewModel->getVariable('member')->member_id, $yearmonth);
        $viewModel->setVariable('allDownloads', $allDownloads);

        return $viewModel;
    }

    /** @acl(access=private) */
    public function sectionplingsmonthajaxAction()
    {
        $this->initPrivateUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }

        $viewModel = $this->initCommonActionViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariable('view_member', $this->requestUser->member);

        $yearmonth = $this->params()->fromQuery('yearmonth');
        $viewModel->setVariable('yearmonth', $yearmonth);
        $section_id = $this->params()->fromQuery('section_id');
        $viewModel->setVariable('section_id', $section_id);

        if ($section_id) {
            $allDownloads = $this->statDownloadService->getUserDownloadsAndViewsForMonthAndSection($viewModel->getVariable('member')->member_id, $yearmonth, $section_id);
        } else {
            $allDownloads = $this->statDownloadService->getUserDownloadsAndViewsForMonth($viewModel->getVariable('member')->member_id, $yearmonth);
        }

        $viewModel->setVariable('allDownloads', $allDownloads);

        return $viewModel;
    }

    /** @acl(access=private) */
    public function sectioncreditsmonthajaxAction()
    {
        $this->initPrivateUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }

        $viewModel = $this->initCommonActionViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariable('view_member', $this->requestUser->member);

        $yearmonth = $this->params()->fromQuery('yearmonth');
        $viewModel->setVariable('yearmonth', $yearmonth);
        $section_id = $this->params()->fromQuery('section_id');
        $viewModel->setVariable('section_id', $section_id);
        $project_id = $this->params()->fromQuery('project_id');
        $viewModel->setVariable('project_id', $project_id);

        $allDownloads = $this->statDownloadService->getUserDownloadsAndViewsForProject($viewModel->getVariable('member')->member_id, $yearmonth, $section_id, $project_id);
        $viewModel->setVariable('allDownloads', $allDownloads);


        return $viewModel;
    }

    /** @acl(access=private) */
    public function payouthistoryoldAction()
    {
        $this->initPrivateUserRequest();
        $viewModel = $this->initCommonActionViewModel();
        $viewModel->setVariable('payouthistory', $this->statDownloadService->getPayoutHistory($this->requestUser->memberId));

        return $viewModel;

    }

    /** @acl(access=private) */
    public function payouthistoryAction()
    {
        $this->initPrivateUserRequest();
        $viewModel = $this->initCommonActionViewModel();
        $viewModel->setVariable('payouthistory2', $this->statDownloadService->getPayoutHistory2($this->requestUser->memberId));
        $viewModel->setVariable('headTitle', ('Payouthistory - ' . $this->getHeadTitle()));

        return $viewModel;
    }

    /** @acl(access=private) */
    public function fundingAction()
    {
        $this->initPrivateUserRequest();
        $viewModel = $this->initCommonActionViewModel();
        $supporterlist = $this->supportRepository->getSupporterDonationList($this->requestUser->memberId);
        $viewModel->setVariable('supporterlist', $supporterlist);
        $viewModel->setVariable('db', $this->db);
        $viewModel->setVariable('headTitle', ('Funding - ' . $this->getHeadTitle()));

        return $viewModel;

    }

    /** @acl(access=private) */
    public function downloadhistoryAction()
    {
        $this->initPrivateUserRequest();
        $viewModel = $this->initCommonActionViewModel();
        $pageLimit = 20;
        if ($viewModel->member) {
            $paramPageId = (int)$this->params()->fromRoute('page', 1);
            $viewModel->setVariable('paramPageId', $paramPageId);

            //$list = $this->memberDownloadHistoryRepository->getDownloadhistory($this->_memberId);
            $adapter = new DownloadhistoryAdapter($this->memberDownloadHistoryRepository, $this->requestUser->memberId);
            $paginator = new Paginator($adapter);
            $paginator->setItemCountPerPage($pageLimit);
            $paginator->setCurrentPageNumber($paramPageId);

            $viewModel->setVariable('headTitle', ('Downloadhistory - ' . $this->getHeadTitle()));

            $viewModel->setVariable('downloadhistory', $paginator);

        } else {
            $list = new Paginator(new ArrayAdapter(array()));
            $viewModel->setVariable('downloadhistory', $list);
        }

        return $viewModel;
    }

    /** @acl(access=private) */
    public function likesAction()
    {
        $this->initPrivateUserRequest();
        $viewModel = $this->initCommonActionViewModel();

        if ($viewModel->member) {
            $paramPageId = (int)$this->params()->fromQuery('page', 1);
            $viewModel->setVariable('paramPageId', $paramPageId);
            $list = $this->projectFollowerRepository->fetchLikesForMember($this->requestUser->memberId);
            $list->setItemCountPerPage(250);
            $list->setCurrentPageNumber($paramPageId);

            $viewModel->setVariable('likes', $list);
        } else {
            $list = new Paginator(new ArrayAdapter(array()));
            $viewModel->setVariable('likes', $list);
        }
        $viewModel->setVariable('headTitle', ('Fan of - ' . $this->getHeadTitle()));

        return $viewModel;
    }

    /** @acl(access=private) */
    public function activitiesAction()
    {
        $this->initPrivateUserRequest();
        $viewModel = $this->initCommonActionViewModel();

        $comments = $this->infoService->getLastCommentsForUsersProjects($this->requestUser->memberId);

        $votes = $this->infoService->getLastVotesForUsersProjects($this->requestUser->memberId);
        $donations = $this->infoService->getLastDonationsForUsersProjects($this->requestUser->memberId);
        $featured = $this->infoService->getFeaturedProductsForUser($this->requestUser->memberId, 100);
        $viewModel->setVariable('comments', $comments);
        $viewModel->setVariable('votes', $votes);
        $viewModel->setVariable('donations', $donations);
        $viewModel->setVariable('featured', $featured);
        $viewModel->setVariable('headTitle', ('Activities - ' . $this->getHeadTitle()));

        return $viewModel;
    }

    /** @acl(access=public) */
    public function tooltipAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }

        $data = $this->infoService->getTooltipForMember($this->requestUser->memberId);
        $viewModel = new JsonModel();
        $viewModel->setVariable('data', $data);

        return $viewModel;
    }

    /** @acl(access=public) */
    public function duplicatesAction()
    {
        $this->initUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }
        $data = [];
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $projectpage = (int)$this->params()->fromPost('projectpage', 1);

        $pageLimit = $this->_pageLimitProjects;
        $total_records = $this->projectService->countAllProjectsForMemberCatFilter($this->requestUser->memberId, true, null);
        $viewModel->setVariable('pageLimit', $pageLimit);
        $viewModel->setVariable('projectpage', $projectpage);
        $viewModel->setVariable('total_records', $total_records);
        $data['pageLimit'] = $pageLimit;
        $data['projectpage'] = $projectpage;
        $data['total_records'] = $total_records;

        if ($projectpage > 1) {
            $lastproject = $this->projectService->getUserActiveProjectsDuplicatedSourceUrl(
                $this->requestUser->memberId, 1, (($projectpage - 1) * $pageLimit - 1)
            );
            if ($lastproject && is_array($lastproject)) {
                foreach ($lastproject as $value) {
                    $viewModel->setVariable('lastcatid', $value['project_category_id']);
                    $data['lastcatid'] = $value['project_category_id'];
                }
            }
        }
        $userProducts = $this->projectService->getUserActiveProjectsDuplicatedSourceUrl(
            $this->requestUser->memberId, $pageLimit, ($projectpage - 1) * $pageLimit
        );
        $viewModel->setVariable('member', $this->requestUser->member);
        $viewModel->setVariable('userProducts', $userProducts);
        $data['member'] = $this->requestUser->member;
        $data['userProducts'] = $userProducts;


        $viewModel->setTemplate('/application/user/partials/aboutme-products.phtml');

        return $this->doReturn($viewModel, $data);
    }

    /** @acl(access=private) */
    public function unpublishedAction()
    {
        $this->initPrivateUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }
        $data = [];
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $projectpage = $this->params()->fromPost('projectpage', 1);

        $pageLimit = $this->_pageLimitProjects;
        $total_records = $this->projectService->getUnpublishedProjectsForMemberCnt($this->requestUser->memberId);
        $viewModel->setVariable('pageLimit', $pageLimit);
        $viewModel->setVariable('projectpage', $projectpage);
        $viewModel->setVariable('total_records', $total_records);
        $data['pageLimit'] = $pageLimit;
        $data['projectpage'] = $projectpage;
        $data['total_records'] = $total_records;
        if ($projectpage > 1) {
            $lastproject = $this->projectService->getUnpublishedProjectsForMember(
                $this->requestUser->memberId, 1, (($projectpage - 1) * $pageLimit - 1)
            );
            if ($lastproject && is_array($lastproject)) {
                foreach ($lastproject as $value) {
                    $viewModel->setVariable('lastcatid', $value['project_category_id']);
                    $data['lastcatid'] = $value['project_category_id'];
                }
            }
        }
        $userProducts = $this->projectService->getUnpublishedProjectsForMember(
            $this->requestUser->memberId, $pageLimit, ($projectpage - 1) * $pageLimit
        );
        $viewModel->setVariable('member', $this->requestUser->member);
        $viewModel->setVariable('userProducts', $userProducts);
        $data['member'] = $this->requestUser->member;
        $data['userProducts'] = $userProducts;
        $viewModel->setTemplate('/application/user/partials/aboutme-products.phtml');

        return $this->doReturn($viewModel, $data);
    }

    /** @acl(access=private) */
    public function deletedAction()
    {
        $this->initPrivateUserRequest();
        if ($this->requestUser->member == null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }
        $data = [];
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $projectpage = $this->params()->fromPost('projectpage', 1);

        $pageLimit = $this->_pageLimitProjects;
        $total_records = $this->projectService->getDeletedProjectsForMemberCnt($this->requestUser->memberId);
        $viewModel->setVariable('pageLimit', $pageLimit);
        $viewModel->setVariable('projectpage', $projectpage);
        $viewModel->setVariable('total_records', $total_records);
        $data['pageLimit'] = $pageLimit;
        $data['projectpage'] = $projectpage;
        $data['total_records'] = $total_records;
        if ($projectpage > 1) {
            $lastproject = $this->projectService->getDeletedProjectsForMember(
                $this->requestUser->memberId, 1, (($projectpage - 1) * $pageLimit - 1)
            );
            if ($lastproject && is_array($lastproject)) {
                foreach ($lastproject as $value) {
                    $viewModel->setVariable('lastcatid', $value['project_category_id']);
                    $data['lastcatid'] = $value['project_category_id'];
                }
            }
        }
        $userProducts = $this->projectService->getDeletedProjectsForMember(
            $this->requestUser->memberId, $pageLimit, ($projectpage - 1) * $pageLimit
        );
        $viewModel->setVariable('member', $this->requestUser->member);
        $viewModel->setVariable('userProducts', $userProducts);
        $data['member'] = $this->requestUser->member;
        $data['userProducts'] = $userProducts;
        $viewModel->setTemplate('/application/user/partials/aboutme-products.phtml');

        return $this->doReturn($viewModel, $data);
    }

        /** @acl(access=private) */
        public function userDuplicatesAction()
        {

            $this->initPrivateUserRequest();
            if ($this->requestUser->member == null) {
                $this->getResponse()->setStatusCode(404);
                return new JsonModel();
            }
            $data = [];
            $page = (int)$this->params()->fromQuery('page', 1);
                
            $total_records = $this->projectService->getCountProjectsDuplicateSourceurl($this->requestUser->memberId);

            $data['pageLimit'] = self::PAGELIMIT50;
            $data['projectpage'] = $page;
            $data['total_records'] = $total_records;
        
            $userProducts = $this->projectService->getUserActiveProjectsDuplicatedSourceUrl(
                $this->requestUser->memberId, self::PAGELIMIT50, ($page - 1) * self::PAGELIMIT50
            );      
            $data['userProducts'] = $userProducts;
            return new JsonModel($data);        
        }

        /** @acl(access=private) */
        public function userDeletedAction()
        {
            $this->initPrivateUserRequest();
            if ($this->requestUser->member == null) {
                $this->getResponse()->setStatusCode(404);
                return new JsonModel();
            }
            $data = [];
            $page = (int)$this->params()->fromQuery('page', 1);
                
            $total_records = $this->projectService->getDeletedProjectsForMemberCnt($this->requestUser->memberId);

            $data['pageLimit'] = self::PAGELIMIT50;
            $data['projectpage'] = $page;
            $data['total_records'] = $total_records;
            $userProducts = $this->projectService->getDeletedProjectsForMember(
                $this->requestUser->memberId, self::PAGELIMIT50, ($page - 1) *  self::PAGELIMIT50
            );
            
            $data['userProducts'] = $userProducts;
            return new JsonModel($data);              
        }

        /** @acl(access=private) */
        public function userUnpublishedAction()
        {
            $this->initPrivateUserRequest();
            if ($this->requestUser->member == null) {
                $this->getResponse()->setStatusCode(404);
                return new JsonModel();
            }
            $data = [];
            $page = (int)$this->params()->fromQuery('page', 1);
                        
            $total_records = $this->projectService->getUnpublishedProjectsForMemberCnt($this->requestUser->memberId);

            $data['pageLimit'] = self::PAGELIMIT50;
            $data['projectpage'] = $page;
            $data['total_records'] = $total_records;      
            $userProducts = $this->projectService->getUnpublishedProjectsForMember(
                $this->requestUser->memberId, self::PAGELIMIT50, ($page - 1) *  self::PAGELIMIT50
            );
            
            $data['userProducts'] = $userProducts;
            return new JsonModel($data);           
        }


    /** @acl(access=public) */
    public function avatarAction()
    {
        $size = $this->getParam("size", null) ? (int)$this->getParam("size", 200) : (int)$this->getParam("s", 200);
//        $width = $this->getParam("width", null) ? $this->getParam("width", null) : $this->getParam("w", null);
//        $width = (is_numeric($width) AND ((int)$width > 0)) ? (int)$width : null;
        $emailHash = $this->getParam("emailhash", null);
        $username = $this->getParam('user_name', null);
        $avatar = $this->avatarService;
        $img_url = $avatar->getAvatarUrl($emailHash, $username, $size, $size);

        return $this->redirect()->toUrl($img_url);
    }

    private function loadHeaderData($catId=null)
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