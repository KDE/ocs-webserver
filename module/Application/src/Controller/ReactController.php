<?php /** @noinspection ALL */

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

use Application\Controller\Plugin\CurrentUserPlugin;
use Application\Model\Entity\CurrentUser;
use Application\Model\Interfaces\LoginHistoryInterface;
use Application\Model\Interfaces\MemberScoreInterface;
use Application\Model\Interfaces\ProjectCategoryInterface;
use Application\Model\Repository\ActivityLogRepository;
use Application\Model\Repository\BrowseListTypesRepository;
use Application\Model\Repository\CollectionProjectsRepository;
use Application\Model\Repository\CommentsRepository;
use Application\Model\Repository\MediaViewsRepository;
use Application\Model\Repository\PploadFilesRepository;
use Application\Model\Repository\ProjectFollowerRepository;
use Application\Model\Repository\ProjectPlingsRepository;
use Application\Model\Repository\ProjectRatingRepository;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Repository\ReportProductsRepository;
use Application\Model\Repository\SectionSupportRepository;
use Application\Model\Repository\TagsRepository;
use Application\Model\Service\BbcodeService;
use Application\Model\Service\CollectionService;
use Application\Model\Service\HtmlPurifyService;
use Application\Model\Service\Interfaces\InfoServiceInterface;
use Application\Model\Service\Interfaces\MemberServiceInterface;
use Application\Model\Service\Interfaces\ProjectServiceInterface;
use Application\Model\Service\Interfaces\StatDownloadServiceInterface;
use Application\Model\Service\Ocs\Gitlab;
use Application\Model\Service\PploadService;
use Application\Model\Service\ProjectCategoryService;
use Application\Model\Service\ProjectCloneService;
use Application\Model\Service\ProjectPlingsService;
use Application\Model\Service\ProjectService;
use Application\Model\Service\ProjectUpdatesService;
use Application\Model\Service\SectionService;
use Application\Model\Service\SectionSupportService;
use Application\Model\Service\TagConst;
use Application\Model\Service\TagGroupService;
use Application\Model\Service\TagService;
use Application\Model\Service\Util;
use Application\Model\Service\UtilReact;
use Application\View\Helper\AddDefaultScheme;
use Application\View\Helper\BuildExploreUrl;
use Application\View\Helper\CatTitle;
use Application\View\Helper\FetchHeaderData;
use Application\View\Helper\IsSupporter;
use Application\View\Helper\IsSupporterActive;
use Application\View\Helper\ProjectDetailCounts;
use Exception;
use Laminas\Http\Client;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Helper\ServerUrl;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Library\Parsedown;

/**
 * Class ReactController
 *
 * @package Application\Controller
 */
class ReactController extends BaseReactController
{
    const DEFAULT_ORDER = 'latest';
    const TAG_ISORIGINAL = 'original-product';       

    const PAGELIMIT20 = 20;
    const PAGELIMIT50 = 50;
    const PAGELIMIT25 = 25;
    
    private $infoService;
    private $sectionService;
    private $projectService;
    private $memberService;
    private $projectCategoryRepository;
    private $projectRepository;
    private $tagsRepository;
    private $tagService;
    private $sectionSupportRepository;
    private $projectPlingsRepository;
    private $projectFollowerRepository;
    private $projectUpdatesService;
    private $projectRatingRepository;
    private $gitlab;
    private $tagGroupService;
    private $pploadFilesRepository;     
    private $commentsRepository;
    private $collectionService;
    private $mediaViewsRepository;
    private $projectCloneService;
    private $reportProductsRepository;
    private $projectPlingsService;
    private $sectionSupportService;
    private $pploadService;
    private $loginHistoryRepository;
    private $memberScoreRepository;
    private $statDownloadService;
    private $collectionProjectsRepository;
    private $projectCategoryService;
    private $_projectId;
    private $requestUser;
    
    public function __construct(       
        InfoServiceInterface $infoService,
        ProjectServiceInterface $projectService,
        MemberServiceInterface $memberService,
        ProjectCategoryInterface $projectCategoryRepository,
        ProjectRepository $projectRepository,
        SectionService $sectionService,
        TagsRepository $tagsRepository,
        TagService $tagService,
        SectionSupportRepository $sectionSupportRepository,
        ProjectPlingsRepository $projectPlingsRepository,
        ProjectFollowerRepository $projectFollowerRepository,
        ProjectUpdatesService $projectUpdatesService,
        ProjectRatingRepository $projectRatingRepository,
        Gitlab $gitlab,
        TagGroupService $tagGroupService,
        PploadFilesRepository $pploadFilesRepository,
        CommentsRepository $commentsRepository,
        CollectionService $collectionService,
        MediaViewsRepository $mediaViewsRepository,
        ProjectCloneService $projectCloneService,
        ReportProductsRepository $reportProductsRepository,
        ProjectPlingsService $projectPlingsService,
        SectionSupportService $sectionSupportService,
        PploadService $pploadService,
        LoginHistoryInterface $loginHistoryRepository,
        MemberScoreInterface $memberScoreRepository,
        StatDownloadServiceInterface $statDownloadService,
        CollectionProjectsRepository $collectionProjectsRepository,
        ProjectCategoryService $projectCategoryService
        
    ) {
        parent::__construct();
        $this->infoService = $infoService;
        $this->sectionService = $sectionService;
        $this->projectService = $projectService;
        $this->memberService = $memberService;
        $this->projectCategoryRepository = $projectCategoryRepository;
        $this->projectRepository = $projectRepository;
        $this->tagService = $tagService;
        $this->tagsRepository = $tagsRepository;
        $this->sectionSupportRepository = $sectionSupportRepository;
        $this->projectPlingsRepository = $projectPlingsRepository;
        $this->projectFollowerRepository = $projectFollowerRepository;
        $this->projectUpdatesService = $projectUpdatesService;
        $this->projectRatingRepository = $projectRatingRepository;
        $this->gitlab = $gitlab;
        $this->tagGroupService = $tagGroupService;
        $this->pploadFilesRepository = $pploadFilesRepository;
        $this->commentsRepository = $commentsRepository;
        $this->collectionService = $collectionService;
        $this->mediaViewsRepository = $mediaViewsRepository;
        $this->projectCloneService = $projectCloneService;
        $this->reportProductsRepository = $reportProductsRepository;
        $this->projectPlingsService = $projectPlingsService;
        $this->sectionSupportService = $sectionSupportService;
        $this->pploadService = $pploadService;
        $this->loginHistoryRepository = $loginHistoryRepository;
        $this->memberScoreRepository = $memberScoreRepository;
        $this->statDownloadService = $statDownloadService;
        $this->collectionProjectsRepository = $collectionProjectsRepository;
        $this->projectCategoryService = $projectCategoryService;
        
    }

    // handle homepage
    public function homeAction()
    {
        // init layout
        $this->layout()->setTemplate('layout/pling-ui');      
        $headerData = $this->loadHeaderData(null);
        
        $this->layout()->setVariable('headerData',  $headerData);
       
        $auth = $this->currentUser();
        $storeConfig = $this->ocsStore->config;
                
        $viewModel = $this->view;        
        $data=[];        
        $data['header']  = $headerData;
        if ($this->_authMember && $this->_authMember->member_id) {
           
            $authMember = array(
                'member_id'   => (int)$this->_authMember->member_id,
                'username'    => $this->_authMember->username,                                 
            );
            $helperIsSupporter = new IsSupporter($this->memberService);
            $isSupporter = $helperIsSupporter->isSupporter($this->_authMember->member_id);
            $authMember['isSupporter'] = (int)$isSupporter;
            $data['authMember'] = $authMember;     
        }

       


        if ($storeConfig) {
            $viewModel->setVariable('tag_filter', $this->ocsStore->tags);
            $data['tag_filter'] = $this->ocsStore->tags;
            $data['storeConfigIdName'] = $storeConfig->config_id_name;
            if ($storeConfig->is_show_home) {              
                    
                    if ($storeConfig->config_id_name == 'opendesktop') {
                        $this->layout()->noheader = true;
                        if($auth->hasIdentity()){
                            return $this->redirect()->toRoute('application_start');
                        }else{
                            $this->layout()->noheader = true;
                            $viewModel->setVariable('headerData',$data['header'] );     
                            $viewModel->setTemplate('application/home/index-opendesktop');
                            return $viewModel;
                        }
                    }
                                                         
                    if ($storeConfig->config_id_name == 'appimagehub') {
                       
                        //$viewModel->setTemplate('application/home/index-' . $storeConfig->config_id_name.'2');
                        $host = $_SERVER['SERVER_NAME'];

                        $totalProjects = $this->projectService->fetchTotalProjectsCount(true);
                        $viewModel->setVariable('totalProjects', $totalProjects);
                        $data['totalProjects'] = $totalProjects;
                        if (strpos($host, ".cc") > 0 || strpos($host, ".local") > 0 || strpos($host, ".live") > 0) {
                            //$products1 = $this->infoService->getJsonLastProductsForHostStores(15);
                            $products2 = $this->infoService->getJsonLastProductsForHostStores(
                                15, "105,104,98,57,53,52,48,295,158"
                            );

                            $response = array(
                                'products1' => array(
                                    'title'    => 'Latest',
                                    'catIds'   => '104,105,98',
                                    'products' => $products2,
                                ),
                            );

                        } else {
                            //$products1 = $this->infoService->getJsonLastProductsForHostStores(15);
                            $products2 = $this->infoService->getJsonLastProductsForHostStores(
                                15, "391,392,544,526,492,542,450,388,481,6"
                            );

                            $response = array(
                                'products1' => array(
                                    'title'    => 'Latest',
                                    'catIds'   => '391,392,6',
                                    'products' => $products2,
                                ),
                            );
                        }                        
                        //$viewModel->setVariable('data', $response);
                        $data['data']=$response;                                    
                    }
                    else{                                         
                        $data = $this->preparePlingHomeData($data);                                                 
                    }                    
                    $cat_tree = $this->projectCategoryRepository->fetchTreeForView();
                    $viewModel->setVariable('cat_tree', $cat_tree);
                    $data['categories'] = $cat_tree;    
                                      
                    $json = (int)$this->params()->fromQuery('json', 0);
                    if($json==1)
                    {
                        return new JsonModel($data);
                    }else{                    
                        $viewModel->setVariable('homeData',$data);  
                        $viewModel->setVariable('headerData',$data['header'] );                                            
                        return $viewModel;
                    }                  
            }
        }

        $params = array();
        if ($this->params()->fromRoute('store_id')) {
            $params['store_id'] = $this->params()->fromRoute('store_id');
            return $this->redirect()->toRoute('application_browse_react', $params);
        }

        return $this->redirect()->toRoute('application_browse_react', $params);

    }

    // handle explorepage
    public function exploreAction()
    {                            
        $index = null;
        $browseListType = null;                
        $isShowOnlyFavs = (int)$this->getParam('fav');
        $inputCatId = (int)$this->getParam('cat');
        $page = (int)$this->getParam('page', 1);
        $index = $this->getParam('index');  
        
       
        // init layout
        $this->layout()->setTemplate('layout/pling-ui');      
        $headerData = $this->loadHeaderData($inputCatId);
        $this->layout()->setVariable('headerData',  $headerData);
       
        $data=[];
        if ($inputCatId) {
            
            $helperFetchCategory = new CatTitle($this->projectCategoryRepository);
            $catTitle = $helperFetchCategory->catTitle($inputCatId);            
            $data['catTitle'] = $catTitle;
            $data['cat_id'] = $inputCatId;
            $objCat =  $this->projectCategoryRepository->findById($inputCatId);
            $data['cat_showDescription'] =(int) $objCat->show_description;            
            $this->view->setVariable('headTitle', ($catTitle . ' - ' . $this->getHeadTitle()));      
            $this->view->setVariable('cat_id', $inputCatId);    
            $this->view->setVariable('cat_title', $catTitle);                                     
        }
                         
        $data['categories'] = $this->fetchCategoryTree();    
        $catTags = $this->tagService->getTopTagsPerCategory($inputCatId);
        $data['categoriesTopTags'] = $catTags;              
        // load header data
        $fetchHeaderData = new FetchHeaderData($this->sectionService, $this->infoService);
        $header = $fetchHeaderData($inputCatId);
        $data['header']  =  $header ;
       
        $storeCatIds = $GLOBALS['ocs_store_category_list'];
        $filter = array();
        $filter['category'] = $inputCatId ? $inputCatId : $storeCatIds;
        $filter['order'] = preg_replace('/[^-a-zA-Z0-9_]/', '', $this->getParam('ord', self::DEFAULT_ORDER));                

        // removed filter original 20191007
        //$filter['original'] = $inputFilterOriginal == 1 ? self::TAG_ISORIGINAL : null;
        if ($isShowOnlyFavs == 1) {
            if ($this->_authMember->member_id) {
                $filter['favorite'] = $this->_authMember->member_id;      
                $data['isBrowseFavorite'] = true;                    
            } else {
                $this->redirect()->toUrl('/browse');
            }
        } else {
            $isShowOnlyFavs = 0;
        }
        /**
         * please use the new Controller Plugin and there is also a new View-Helper called currentUser which do the same
         * @see \Application\Controller\Plugin\CurrentUserPlugin
         * @see \Application\Model\Entity\CurrentUser
         * @example $this->currentUser()->hasIdentity(); or  $this->currentUser()->isAdmin(); or $this->currentUser()->member_id; or $this->currentUser()->isSupporter;
         */
        if ($this->_authMember && $this->_authMember->member_id) {
            $this->view->setVariable("ocs_user",$this->_authMember);
            $authMember = array(
                'member_id'   => (int)$this->_authMember->member_id,
                'username'    => $this->_authMember->username,
                'roleName'    => $this->_authMember->roleName,
                'isAdmin'     => $this->_authMember->isAdmin()                   
            );
            $helperIsSupporter = new IsSupporter($this->memberService);
            $isSupporter = $helperIsSupporter->isSupporter($this->_authMember->member_id);
            $authMember['isSupporter'] = (int)$isSupporter;
            $data['authMember'] = $authMember;     
        }
      
        $filter['tag'] = $GLOBALS['ocs_config_store_tags'];
        if(!$filter['tag']){
            $filter['tag']=[];
        }

        if (APPLICATION_ENV == "development") {
            $GLOBALS['ocs_log']->debug(__METHOD__ . ' - ' . json_encode($filter));
        }
        
// $tagGroupFilter = $GLOBALS['ocs_config_store_taggroups'];       
        // if (!empty($tagGroupFilter)) {
        //     $filterArray = array();
        //     foreach ($tagGroupFilter as $tagGroupId) {
        //         $inputFilter = $this->getFilterTagFromCookie($tagGroupId);
        //         $filterArray[$tagGroupId] = $inputFilter;
        //     }
        //     $result[] = ['tag_group_filter' => $filterArray];
        // }

        //Insert flexible tag-group-filter
        $tagFilter = $GLOBALS['ocs_config_store_tags'];
        $tagGroupFilter = $GLOBALS['ocs_config_store_taggroups'];
        $tagGroupHelper = new \Application\View\Helper\FetchTagsForTagGroup($this->db);
        $tagBrowseFilter = [];
        $tagGroupArray = array();   
        if(!empty($tagGroupFilter)) {

            $filterArray = array();
            foreach ($tagGroupFilter as $tagGroupId) {
                $inputFilter = $this->getFilterTagFromCookie($tagGroupId);
                $filterArray[$tagGroupId] = $inputFilter;
                if (!empty($inputFilter)) {
                    $filter['tag'][] = $inputFilter;                    
                }
            }  
            //$data['tag_group_filter'] = $filterArray;          
            
            foreach ($tagGroupFilter as $tagGroup ) {
                $tags = $tagGroupHelper->fetchList($tagGroup);
                $select = $filterArray[$tagGroup];
                $tagBrowseFilter[] = ['tagGroup'=>$tagGroup,'values'=>$tags,'select'=>$select];                
            }
            
            $data['tagBrowseFilter'] = $tagBrowseFilter;
                                 
        }
        
        // filter tag
        $tagname = $this->getParam('tag',null);
        $tagId = null;
       
        if($tagname)
        {                        
           $tagObject = $this->tagsRepository->fetchTagByName($tagname);
           $data['categroyTreeTagFitler'] = $tagObject;
           $tagId = $tagObject['tag_id'];     
           $filter['tag'][]=$tagId;      
        }

        /**
         * please use the new view helper currentStore(). Please check it it solves your requirement
         * @see \Application\Model\Entity\CurrentStore currentStore()
         */
        $storeConfig = $GLOBALS['ocs_store']->config;
        $data['storeConfig'] = ['store_id' => $storeConfig->store_id
                                ,'name' => $storeConfig->name
                                ,'is_show_real_domain_as_url' => $storeConfig->is_show_real_domain_as_url
                                ,'browse_list_type' => $storeConfig->browse_list_type
                                ,'package_type' => $storeConfig->package_type
                                ,'config_id_name' => $storeConfig->config_id_name
                            ];
                                        
        if ($index) {
            switch ($index) {
                case 3:
                case 2:
                    $browseListType = 'picture';
                    break;
                case 4:
                    $browseListType = 'phone-pictures';
                    break;
                case 8:
                    $browseListType = 'apps';
                    break;
                case 9:
                    $browseListType = 'icons';
                    break;
            }
        }
     
        if ($storeConfig->browse_list_type) {
            $listTypeTable = new BrowseListTypesRepository($this->db);
            $listType = $listTypeTable->findBrowseListType($storeConfig->browse_list_type);
            if (isset($listType)) {
                $browseListType = $listType->name;
                $index = 2;
            }
        }
       
        //Browse List config in Category set?
        if (!$index && $inputCatId != 0) {
            //Now the list type is in backend categories set
            $cat = $this->projectCategoryRepository->findCategory($inputCatId);

            if (isset($cat) && isset($cat['browse_list_type']) && $cat['browse_list_type'] > 0) {
                $indexListType = $cat['browse_list_type'];
                $listTypeTable = new BrowseListTypesRepository($this->db);
                $listType = $listTypeTable->findBrowseListType($indexListType);

                if (isset($listType)) {
                    $browseListType = $listType->name;
                    $index = 2;
                }
            }
        }

        // my favourite list filter & list layout
        if ($isShowOnlyFavs == 1) {
            $index = 7;
            $browseListType = 'myfav';
        }

        if ($index) {
            // only switch view via index=2 parameter
            $pageLimit = 50;
            if ($index == 7) {
                $pageLimit = 10;
            }            
        }else{
            if ($storeConfig->layout_explore && $storeConfig->isRenderReact()) {
                $pageLimit = 50;
            } else {
                $pageLimit = 10;                              
            }
        }
     
        $requestedElements = $this->fetchRequestedElements($filter, $pageLimit, ($page - 1) * $pageLimit);                
        $data['pageLimit'] = $pageLimit;
       
        foreach ($requestedElements['elements'] as  &$value) {
            $value = UtilReact::cleanProductBrowse($value);
            $value['description'] = Util::truncate(HtmlPurifyService::purify(BbcodeService::renderHtml($value['description'])),300, '...', false, true);                      
            $value['version'] = HtmlPurifyService::purify($value['version']);
            $value['title'] = HtmlPurifyService::purify($value['title']);
            
            if(!$this->_authMember->isAdmin()){
                unset($value['laplace_score_old']);
                unset($value['laplace_score_test']);
                unset($value['major_updated_at']);

                
            }
        }
        
      
        // cleanProductBrowse
        $data['products'] = $requestedElements['elements'];
        $data['filters'] = $filter;
        $this->view->setVariable('filters', $filter);
        $modelInfo = $this->infoService;
        $topprods = $modelInfo->getMostDownloaded(100, $inputCatId, $tagFilter);
        $data['topprods'] = $topprods;                
       
        //$data['templateConfigData'] = $this->templateConfigData;
        $countSupporters = $this->infoService->getCountAllSupporters();
        $data['countSupporters'] = $countSupporters;        
        $supporters = $this->infoService->getNewActiveSupporters(7);
        $data['supporters'] = $supporters;        
        $comments = $modelInfo->getLatestComments(5, $inputCatId, $tagFilter);
        foreach ($comments as &$c) {
            $c['comment_text'] = Util::truncate(HtmlPurifyService::purify($c['comment_text']),200, '...', false, true);                      
            $c['title'] = HtmlPurifyService::purify($c['title']);                
        }       
        $data['comments'] = $comments;
       
        $data['browseListType'] = $browseListType;
        $data['pageLimit'] = $pageLimit;
        $data['index'] = $index;
        $data['showAddProduct'] = 1;
       
        $data['page'] = $page;
        $data['totalcount']  = $requestedElements['total_count'];        
        $data['package_type'] = $GLOBALS['ocs_config_store_tags'];
        $data['tags'] = $tagFilter;
       
        // tabs
        $tabs = [];
        $hb = new BuildExploreUrl($this->request);
        $myParams = array();
        if(strrpos($_SERVER['REQUEST_URI'],'/s/')===false) {
          
        } else {            
            $myParams['store_id'] = $GLOBALS['ocs_store']->config->name;
        }
        $tabs[] = ['order'=>'latest','label'=>'Latest','url'=>$hb->buildExploreUrl($filter['category'], null,'latest',$myParams)];        
        $tabs[] = ['order'=>'rating','label'=>'Rating','url'=>$hb->buildExploreUrl($filter['category'], null,'rating',$myParams)];    
        $tabs[] = ['order'=>'plinged','label'=>'Plinged','url'=>$hb->buildExploreUrl($filter['category'], null,'plinged',$myParams)];         
        if($this->_authMember && $this->_authMember->isAdmin()){
            $tabs[] = ['order'=>'top','label'=>'Score_old','url'=>$hb->buildExploreUrl($filter['category'], null,'top',$myParams)];     
            $tabs[] = ['order'=>'test','label'=>'Score_test','url'=>$hb->buildExploreUrl($filter['category'], null,'test',$myParams)];     
        }
        $data['tabs'] = $tabs;
       
        $right = $this->loadRightDataExplore($inputCatId);
       
        $data['rightsidebarData'] = $right;
      
        $json = (int)$this->params()->fromQuery('json', 0);       
        if($json==1)
        {           
            return new JsonModel($data);
        }else{
            $this->view->setVariable('productBrowseData',$data);  
            return $this->view;
        }          
      
    }

    // handle pagedetail 
    public function detailAction()
    {   
        $this->layout()->setTemplate('layout/pling-ui');   
        $this->_projectId = (int)$this->getParam('project_id');        
        if (empty($this->_projectId)) {
           return $this->redirect()->toRoute('/explore');
        }       

        $productInfo = $this->projectRepository->fetchProductInfo($this->_projectId);
       
        $productInfoArray = $productInfo;
        if (empty($productInfo)) {
            $headerData = $this->loadHeaderData();
            $this->layout()->setVariable('headerData',  $headerData);
            $response = $this->getResponse();
            $response->setStatusCode(404);
            return ;            
        }
        $productInfo = Util::arrayToObject($productInfo);
       
        if ($productInfo->type_id == ProjectRepository::PROJECT_TYPE_COLLECTION) {                                  
                return $this->redirect()->toUrl('/c/' . $this->_projectId);             
        }

        $productInfo = UtilReact::purifyProduct($productInfo);       
     
        // init layout          
        $headerData = $this->loadHeaderData($productInfo->project_category_id);
        $this->layout()->setVariable('headerData',  $headerData);
       
        // load product data
        $product = $this->loadProductDetailData($productInfo);
       
       
        // load header data
        $header = $this->loadHeaderData($productInfo->project_category_id);
        $product['header'] = $header;
        
        // load comments
        $comments = $this->loadCommentsData($this->_projectId,1);
        $product['commentsTab'] = iterator_to_array($comments->getCurrentItems(),true);
        $product['commentsTabCnt'] = $comments->getTotalItemCount();        
      
        // load rightsidebar data
        $rightsidebar = $this->loadRightDataDetail($productInfo);
        $product['rightsidebarData'] = $rightsidebar;
      
      
        // // load files tab
        // $files = $this->loadFilesData($productInfoArray);        
        // $product['filesTab']=$files==null ? []:$files;

        // load files tab per ajax load
        $product['filesTab'] = [];

        
        // load ratings tab
        $ratings = $this->detailLoadRatingsData($this->_projectId);
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
        $commentsMod = $this->loadCommentsModData($this->_projectId,1);
        $product['commentsModTab'] = iterator_to_array($commentsMod->getCurrentItems(),false);

        // load commentsMod
        $commentsLic = $this->loadCommentsLicData($this->_projectId,1);        
        $product['commentsLicTab'] = iterator_to_array($commentsLic->getCurrentItems(),false);

        // load plings
        $plings = $this->loadPlingsData($this->_projectId);        
        $product['plingsTab'] = $plings;

        $categories = $this->fetchCategoryTree();        
        
        $this->view->setVariable('headTitle',($productInfo->title . ' - ' . $this->getHeadTitle()));            
        $this->view->setVariable('categories',$categories);      
        $this->view->setVariable('product',$productInfo);  
               
        $product['categories'] = $categories;

        $topTagsPerCategory = $this->tagService->getTopTagsPerCategory($productInfo->project_category_id);      
        $product['categoriesTopTags'] = $topTagsPerCategory;
        
        $json = (int)$this->params()->fromQuery('json', 0);
        if($json==1)
        {
            return new JsonModel($product);
        }else{
            $this->view->setVariable('productViewData',$product);                         
            return $this->view;
        }   
        
    }

    public function detaillistingAction()
    {
        $this->layout()->setTemplate('layout/pling-ui');   
        $this->_projectId = (int)$this->getParam('project_id');
       
        if (empty($this->_projectId)) {
           return $this->redirect()->toRoute('/explore');
        }

        $productInfo = $this->projectRepository->fetchProductInfo($this->_projectId);
       
        $productInfoArray = $productInfo;
        if (empty($productInfo)) {
          
            $headerData = $this->loadHeaderData();
            $this->layout()->setVariable('headerData',  $headerData);
            $this->getResponse()->setStatusCode(404);            
            return;
        }
        $productInfo = Util::arrayToObject($productInfo);               
        $productInfo = UtilReact::purifyProduct($productInfo);       
     
        // init layout
        $this->layout()->setTemplate('layout/pling-ui');      
        $headerData = $this->loadHeaderData($productInfo->project_category_id);
        $this->layout()->setVariable('headerData',  $headerData);
       
        // load product data
        $product = $this->loadProductDetailData($productInfo);
        
       
        // load header data
        $header = $this->loadHeaderData($productInfo->project_category_id);
        $product['header'] = $header;
        
        // load comments
        $comments = $this->loadCommentsData($this->_projectId,1);
        $product['commentsTab'] = iterator_to_array($comments->getCurrentItems(),true);
        $product['commentsTabCnt'] = $comments->getTotalItemCount();        
  
       
        
        // load rightsidebar data
        $rightsidebar = $this->loadRightDataDetail($productInfo);
        $product['rightsidebarData'] = $rightsidebar;
      
               
       
        // load ratings tab
        $ratings = $this->detailLoadRatingsData($this->_projectId);
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
        
        // load plings
        $plings = $this->loadPlingsData($this->_projectId);        
        $product['plingsTab'] = $plings;

        $categories = $this->fetchCategoryTree();        
        
        $this->view->setVariable('headTitle',($productInfo->title . ' - ' . $this->getHeadTitle()));            
        $this->view->setVariable('categories',$categories);      
        $this->view->setVariable('product',$productInfo);  
               
        $product['categories'] = $categories;

        $topTagsPerCategory = $this->tagService->getTopTagsPerCategory($productInfo->project_category_id);      
        $product['categoriesTopTags'] = $topTagsPerCategory;
        $json = (int)$this->params()->fromQuery('json', 0);
        if($json==1)
        {
            return new JsonModel($product);
        }else{           
            $this->view->setVariable('productViewData',$product);  
            $this->view->setTemplate('application/react/detail');
            return $this->view;
        }   
        
    }

    //  /**
    //  * @return JsonModel
    //  * @noinspection SqlResolve
    //  */
    // public function plingprojectAction()
    // {      
    //     $this->_projectId = (int)$this->getParam('project_id');        
    //     // not allow to pling himself
    //     if (array_key_exists($this->_projectId, $this->_authMember->projects)) {
    //         return new JsonModel(
    //             array(
    //                 'status' => 'error',
    //                 'msg'    => 'not allowed',
    //             )
    //         );
    //     }

    //     // not allow to pling if not supporter
    //     $helperIsSupporter = new IsSupporter($this->memberService);

    //     if (!$helperIsSupporter->isSupporter($this->_authMember->member_id)) {
    //         return new JsonModel(
    //             array(
    //                 'status' => 'error',
    //                 'msg'    => 'become a supporter first please. ',
    //             )
    //         );
    //     }

    //     $projectplings = $this->projectPlingsRepository;

    //     $newVals = array('project_id' => $this->_projectId, 'member_id' => $this->_authMember->member_id);
    //     $sql = sprintf("SELECT * FROM `%s` WHERE `member_id` = %s AND `is_deleted` = 0 AND `project_id` = %d", $projectplings->getName(), $this->_authMember->member_id, $this->_projectId);
    //     $result = $projectplings->fetchRow($sql);

    //     if (null === $result) {
    //         $projectplings->insert($newVals);

    //         $cnt = $projectplings->getPlingsAmount($this->_projectId);

    //         return new JsonModel(
    //             array(
    //                 'status' => 'ok',
    //                 'msg'    => 'Success.',
    //                 'cnt'    => $cnt,
    //                 'action' => 'insert',
    //             )
    //         );
    //     } else {

    //         // delete pling
    //         $projectplings->setDelete($result['project_plings_id']);

    //         $cnt = $projectplings->getPlingsAmount($this->_projectId);

    //         return new JsonModel(
    //             array(
    //                 'status' => 'ok',
    //                 'msg'    => 'Success.',
    //                 'cnt'    => $cnt,
    //                 'action' => 'delete',
    //             )
    //         );
    //     }

    // }
   
    // handle userpage
    public function userAction()
    {
        
        // init layout
        $this->layout()->setTemplate('layout/pling-ui');      
        $headerData = $this->loadHeaderData(null);
        $this->layout()->setVariable('headerData',  $headerData);                    

        $this->initUserRequest();

        if ($this->requestUser->member == null) {
            $this->view->setVariable('headTitle', ($this->getHeadTitle()));
            $this->getResponse()->setStatusCode(404);
            return;
        }       
        $result = $this->prepareUserData($this->view);               
     
        $result['header'] = $headerData;
        $this->view->setVariable('headTitle', ($this->requestUser->member->username . ' - ' . $this->getHeadTitle()));
        $json = (int)$this->params()->fromQuery('json', 0);
        if ($json == 1) {
            return new JsonModel($result);
        } else {
            $this->view->setVariable('memberData', $result);
            return  $this->view;
        }
    }


    // public function loadCommentsAction()
    // {        
    //     $this->_projectId = (int)$this->getParam('project_id');
    //     $this->view->setTerminal(true);
    //     $page = (int)$this->params()->fromQuery('page', 0);
    //     $data = $this->loadCommentsData($this->_projectId, $page);
    //     return new JsonModel($data);
    // }
   
    
    private function loadRelationData($project_id)
    {       
        $serviceProduct = $this->projectService;
        $result = [];
        if ($this->isAdmin()) {   // execute only for admin.
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
    private function loadCommentsModData($project_id,$page)
    {
        
        $commentsTree = $this->commentsRepository->getCommentTreeForProject(
            $this->_projectId, CommentsRepository::COMMENT_TYPE_MODERATOR
        );
        $commentsTree->setItemCountPerPage(500);
        $commentsTree->setCurrentPageNumber($page);
        return $commentsTree;
    }
    private function loadCommentsLicData($project_id,$page)
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

    private function loadLikesData($project_id)
    {        
        $data = $this->projectFollowerRepository->fetchLikesForProject($project_id);
        return $data;
    }
    private function preparePlingHomeData(array $data)
    {
        $comments = $this->infoService->getLatestComments(10);
        foreach ($comments as &$c) {
            $c['comment_text'] = Util::truncate(HtmlPurifyService::purify($c['comment_text']),200, '...', false, true);          
            $c['title'] = HtmlPurifyService::purify($c['title']);                
        }
        $productsCollections = $this->infoService->getLastProductsForHostStores(5, "567");
        $productsThemesGTK = $this->infoService->getLastProductsForHostStores(
            5, "366,363,273,267,138,125,131,153,154,414,133"
        );
        $productsThemesPlasma = $this->infoService->getLastProductsForHostStores(
            5, "365,119,123,266,114,118,349,417,101,100,111,422,423,446,417"
        );
        $productsWindowmanager = $this->infoService->getLastProductsForHostStores(5, "117,267,139,143,142,140,141,144");
        $productsIconsCursors = $this->infoService->getLastProductsForHostStores(5, "386,107");
        $productsApps = $this->infoService->getLastProductsForHostStores(5, 233);
        $productsAddons = $this->infoService->getLastProductsForHostStores(5, "152");
        $productsWallpapersOriginal = $this->infoService->getLastProductsForHostStores(5, "295", null, true);
        $productsWallpapers = $this->infoService->getLastProductsForHostStores(5, "295", null, false);
        $productsArtwork = $this->infoService->getLastProductsForHostStores(5, "158");
        $productsVideos = $this->infoService->getLastProductsForHostStores(5, "518,586");
        $productsBooksComics = $this->infoService->getLastProductsForHostStores(5, "581,39");
        $productsPhone = $this->infoService->getLastProductsForHostStores(5, "491");
        $productsDistors = $this->infoService->getLastProductsForHostStores(5, "404");
        $countSupporters = $this->infoService->getCountAllSupporters();
        // $featureProducts = $this->infoService->getRandFeaturedProduct();
        // $type = 'Featured';
        $json_productsPlinged = $this->infoService->getJsonNewActivePlingProduct(15);
        $response = array(
            'data' => array(
                'title'    => 'Supporters Favourites',                
                'products' => json_decode($json_productsPlinged),
            ),
        );
       
        $data['comments'] = $comments;
        $data['productsCollections'] = $productsCollections;
        $data['productsThemesGTK'] = $productsThemesGTK;
        $data['productsThemesPlasma'] = $productsThemesPlasma;
        $data['productsWindowmanager'] = $productsWindowmanager;
        $data['productsIconsCursors'] = $productsIconsCursors;
        $data['productsApps'] = $productsApps;
        $data['productsAddons'] = $productsAddons;
        $data['productsWallpapersOriginal'] = $productsWallpapersOriginal;
        $data['productsWallpapers'] = $productsWallpapers;
        $data['productsArtwork'] = $productsArtwork;
        $data['productsVideos'] = $productsVideos;
        $data['productsBooksComics'] = $productsBooksComics;
        $data['productsPhone'] = $productsPhone;
        $data['productsDistors'] = $productsDistors;
        $data['countSupporters'] = $countSupporters;
        // $data['featureProducts'] = $featureProducts;
        $data['type'] = 'Featured';
        $data['carouselData'] = $response;
        $supporterslist = $this->infoService->getNewActiveSupportersForSectionAll(9);       
        foreach ($supporterslist as &$value) {
            $value = UtilReact::cleanSupporter($value);
            $value['profile_image_url'] = Util::image($value['profile_image_url'], array('width' => 100, 'height' => 100));           
        }
        $data['supporters'] = $supporterslist;
     
        return $data;
    }

     /**
     * @return array
     */
    private function loadProductDetailData($productInfo)
    {
        $result = array();        
        $project_id = $productInfo->project_id;   
        
        if (!$this->currentUser()->isAdmin()
            and ($productInfo->project_status != ProjectService::PROJECT_ACTIVE)
            and ($productInfo->member_id != $this->currentUser()->member_id))
        {            
            $this->getResponse()->setStatusCode(404);
            return;
        }
     
        $helperIsSupporter = new IsSupporter($this->memberService);
        if (null != $this->_authMember && null != $this->_authMember->member_id) {

            $isModerator = false;
            if ($this->currentUser()->isModerator()) {
                $isModerator = true;
            }
            
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
                'member_id'   => (int)$this->_authMember->member_id,
                'username'    => $this->_authMember->username,
                'roleName'    => $this->_authMember->roleName,
                'isAdmin'     => $this->isAdmin(),
                'isModerator' => $isModerator,
                'isSupporter' => $isSupporter,
                'isSupporterActive' => $isSupporterActive,
                'isPlinged' => $isPlinged,
                'isFollower' =>  $isFollower,
                'supportData' => $supportData,
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

        if($this->tagService->hasCategoryTagGroup($productInfo->project_category_id))
        {            
            $result[] = ['hasCategoryTagGroup' => 1];  
            $tagsCategoryTagGroup = $this->tagService->getTagsFromCategoryTagGroup($productInfo->project_id);            
            $result[] = ['tagsCategoryTagGroup' =>  $tagsCategoryTagGroup];  
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
            $galleryPictures[] =Util::image($p, array('height' => '600'));
        }       
        $result[] = ['pics' => $galleryPictures];
      
        $cntSameSource = $this->projectService->getCountSourceurl($productInfo->source_url);
        $result[] = ['cntSameSource' => $cntSameSource];
        
        if ($this->_authMember && $this->_authMember->member_id) {
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
                    $config = $this->ocsConfig->settings->server->opencode;
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
       
        $product = UtilReact::cleanProductInfoForJson((array)$productInfo);
        $score = $this->projectRatingRepository->getScore($project_id);              
        $product['laplace_score'] = $score;

        if ($this->isAdmin()) {
            $position = $this->infoService->findProductPostion($this->_projectId);  
            $product['position'] = $position;               
        }
        $result[] = ['product' => $product];
        
        if ($productInfo->type_id == ProjectRepository::PROJECT_TYPE_COLLECTION) {
            $collinfo = $this->loadCollectionData($project_id);            
            $result[] =  $collinfo;
        }

        $tmp = [];
        foreach ($result as $key => $object) {
            foreach ($object as $k => $v) {
                $tmp[$k] = $v;
            }
        }
       
        return $tmp;
    }

    private function loadCollectionData($project_id)
    {        
       
        $projectsArray = $this->collectionProjectsRepository->getCollectionProjects($project_id);       
        $result = array();
        foreach ($projectsArray as $project) {          
            $imgUrl = Util::image($project['image_small'], array('width' => 140, 'height' => 98));
            $project['image_url'] = $imgUrl;
            $project['description'] = BbcodeService::renderHtml(HtmlPurifyService::purify($project['description']));    
            $result[] = $project;
        }
        
        $collection_ids = array();
        foreach ($projectsArray as $value) {
            if ($value['ppload_collection_id']) {
                $collection_ids[] = $value['ppload_collection_id'];
            }
        }
        $collection_projects_dls =  $this->pploadFilesRepository->fetchAllActiveFilesForCollection($collection_ids);       
        return ['listing_projects'=>$result, 'listing_dls' => $collection_projects_dls];
    }

    private function fetchCategoryTree()
    {
           // load left categories tree
        $tagGroupFilter = $GLOBALS['ocs_config_store_taggroups'];     
        $isTagGroupFilter = false;  
        $filterArray = array();
        if (!empty($tagGroupFilter)) {            
            foreach ($tagGroupFilter as $tagGroupId) {
                $inputFilter = $this->getFilterTagFromCookie($tagGroupId);
                if($inputFilter){
                        $filterArray[$tagGroupId] = $inputFilter;
                    }
            }   
            if(sizeof($filterArray)>0)$isTagGroupFilter = true;                  
        }              

        if($isTagGroupFilter == true ) {               
            $categories = $this->projectCategoryService->fetchTreeForViewForProjectTagGroupTags(null, $GLOBALS['ocs_config_store_tags'], $filterArray);
        }else{
            $categories = $this->projectCategoryRepository->fetchTreeForView();
        }
        return $categories;
    }

    // private function loadHeaderData($productInfo=null)
    // {  
    //     if($productInfo!=null)
    //     {
    //         $catId = $productInfo->project_category_id;            
    //         $fetchHeaderData = new FetchHeaderData($this->sectionService, $this->infoService);
    //         return $fetchHeaderData($catId);
    //     }else{
    //         $fetchHeaderData = new FetchHeaderData($this->sectionService, $this->infoService);
    //         return $fetchHeaderData(null);
    //     }
    // }

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
   
    private function loadCommentsData($project_id,$page)
    {
                
        $commentsTree = $this->commentsRepository->getCommentTreeForProject($project_id);
        $commentsTree->setItemCountPerPage(self::PAGELIMIT25);
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

    private function loadRightDataExplore($cat_id)
    {       
        $tagFilter = $GLOBALS['ocs_config_store_tags'];
        $result = [];
        $comments = $this->infoService->getLatestComments(5, $cat_id, $tagFilter);
        foreach ($comments as &$c) {
            $c['comment_text'] = Util::truncate(HtmlPurifyService::purify($c['comment_text']),200, '...', false, true);          
            $c['title'] = HtmlPurifyService::purify($c['title']);                
        }
        $result['comments'] = $comments;
        $result['topprods'] = $this->infoService->getMostDownloaded(100, $cat_id, $tagFilter);
        $result['is_startpage'] = false;
        $result['show_git'] = false;  
       
        if($cat_id){
            $catabout = $this->getCategoryAbout($cat_id);                                 
            if($catabout) $result['catabout'] = $catabout;  
        }
        
        $storeabout = $this->getStoreAbout($GLOBALS['ocs_store']->config->store_id);
        if($storeabout) $result['storeabout'] = $storeabout;  

        $storeConfig = $this->ocsStore->config;
        if ($storeConfig->config_id_name == 'kde-store' || $storeConfig->config_id_name == 'kde') {
            $moderators = $this->infoService->getModeratorsList();
            $result['moderators'] = $moderators;
        }        
      
        return $result;
    }    

    private function loadRightDataDetail($productInfo)
    {       
        $section = $this->sectionService->fetchSectionForCategory($productInfo->project_category_id);
        if (!$section) {
            $section = array('name' => 'Test', 'section_id' => '1');
        }    
        $sectionSupporters = $this->infoService->getNewActiveSupportersForSection($section['section_id'], 9);
        
        foreach ($sectionSupporters as &$value) {
            $value = UtilReact::cleanSupporter($value);
            $value['profile_image_url'] = Util::image($value['profile_image_url'], array('width' => 100, 'height' => 100));           
        }
       

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
            $moreProductsOfOtherUsers =  $this->collectionService->fetchMoreCollectionsOfOtherUsr($productInfo, 6);
           

        }else{

            $moreProductsOfUser = $this->projectRepository->fetchMoreProjects($productInfo, 6);
            $moreProductsOfOtherUsers = $this->projectRepository->fetchMoreProjectsOfOtherUsr($productInfo, 6);
            
        }
               
        $countDownloadsToday = 0;
        $countMediaViewsAlltime = 0;        
        // if ($this->isAdmin()) {
        //     $countDownloadsToday = $this->pploadFilesRepository->fetchCountDownloadsTodayForProject(
        //         $productInfo->ppload_collection_id
        //     );
        //     $countMediaViewsAlltime = $this->mediaViewsRepository->fetchCountViewsForProjectAllTime(
        //         $productInfo->project_id
        //     );
        // }

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

        // about store/category 
        $catabout = $this->getCategoryAbout($productInfo->project_category_id);  
        $storeabout = $this->getStoreAbout($GLOBALS['ocs_store']->config->store_id);
        if($catabout){             
            $dataRightSideBar['catabout']= $catabout;
        }
        if($storeabout){
           $dataRightSideBar['storeabout']= $storeabout;            
        }
    
        if (!$this->isAdmin()) {
            unset($dataRightSideBar['isGhnsExcluded']);
            unset($dataRightSideBar['countMediaViewsAlltime']);
            unset($dataRightSideBar['countPageviewsTotal']);
        }

        return $dataRightSideBar;
    }

    private function loadFilesData($productInfo,$file_status=null,$ignore_status_code=null)
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
            if ($this->isAdmin()) {
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
            foreach ($files as &$file) {
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


                if ($this->isAdmin()) {
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
    
    private function loadChangelogsData($project_id)
    {
        
        $updates = $this->projectUpdatesService->fetchProjectUpdates($project_id);
        foreach ($updates as &$update) {
            $update['text'] = BbcodeService::renderHtml(
                HtmlPurifyService::purify(htmlentities($update['text'], ENT_QUOTES | ENT_IGNORE))
            );
            $update = UtilReact::cleanChangelogs($update);
        }

        return $updates;
    }
    private function loadAffiliatesData($project_id)
    {        
        $data = $this->sectionSupportService->fetchAffiliatesForProject($project_id);
        return $data;
    }
    
    private function getAboutUserInfo($member_id, $username)
    {
        $cache_name = __FUNCTION__ . md5($member_id . $username);
        if ($result = $this->projectRepository->readCache($cache_name)) {
            return $result;
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

        $this->projectRepository->writeCache($cache_name, $userinfo, 600);
        return $userinfo;
    }

    private function initUserRequest()
    {
        $this->requestUser = new \stdClass();
        $this->requestUser->member = null;
        $this->requestUser->userName = $this->params()->fromRoute('username', null);
        $this->requestUser->memberId = (int)$this->params()->fromRoute('member_id', null);
        if ($this->requestUser->userName) {
            $this->requestUser->memberId = $this->memberService->fetchActiveUserByUsername($this->requestUser->userName);
        }
        $this->requestUser->member = $this->memberService->fetchMember($this->requestUser->memberId);
    }

    private function prepareUserData(ViewModel $viewModel)
    {
        $result = [];
        $requested_member_id = $this->requestUser->memberId;        
        $member = $this->memberService->fetchMemberData($requested_member_id);        
        if (empty($member)) {
           $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
           return;
        }
        // $lastLoginData = $this->loginHistoryRepository->fetchLastLoginData($requested_member_id);
        $mainProject = $this->projectService->fetchMainProject($requested_member_id);
        $memberScore = $this->memberScoreRepository->fetchScore($requested_member_id);
        $aboutmeUserInfo = $this->getAboutUserInfo($requested_member_id, $this->requestUser->userName);
        $result['isAdmin'] = $this->currentUser()->isAdmin();

        // TODO find out why this not working. above is working around.  because of ip_inet, ipv4_inet, ipv6_inet are BLOB in DB
        // $result['lastLoginData'] = $lastLoginData;
        $earnInfo = '';
        if ($this->currentUser()->isAdmin()) {
            $amoutEarn = $this->statDownloadService->getLastMonthEarn($requested_member_id);
            if ($amoutEarn && $amoutEarn['amount']) {
                $earnInfo = ' Last month I earned $' . number_format($amoutEarn['amount'], 2, '.', '') . '.';
            } else {
                $earnInfo = ' Last month I earned 0.';
            }

            $lastLoginData = $this->loginHistoryRepository->fetchLastLoginData($requested_member_id);
            $result['lastLoginData'] = $lastLoginData;            
            $firstLoginData = $this->loginHistoryRepository->fetchFirstLoginData($requested_member_id);
            $result['firstLoginData'] = $firstLoginData;  
                        
            //computer info
            $tagmodel = $this->tagService;
            $gidsstring = $this->ocsConfig->settings->client->default->tag_group_osuser;
            $gids = explode(",", $gidsstring);
            $data = $tagmodel->getTagGroupsOSUser();
            $data2 = $tagmodel->getTagsOSUser($member->member_id);

            $result['gids'] = $gids;
            $result['data'] = $data;
            $result['data2'] = $data2;
        }
        if($mainProject){
                $result['mainProject'] = UtilReact::cleanMainProject($mainProject->getArrayCopy());        
            }
        $viewModel->setVariable('member', $member);
        $viewModel->setVariable('mainProject', $mainProject);
        // $result['member'] = $member;
        $memberClean = UtilReact::cleanMember($member->getArrayCopy());   
        if (!$this->currentUser()->isAdmin()) {
            unset($memberClean['pling_excluded']);
        }
        
        $isSupporter = false;      
        $helperIsSupporter = new IsSupporter($this->memberService);  
        $isSupporter = $helperIsSupporter->isSupporter($requested_member_id);               
        $isSupporterActive = false;
        $memberClean['isSupporter'] = $isSupporter;
        if ($isSupporter) {
            $helperIsSupporterActive = new IsSupporterActive($this->infoService);
            $isSupporterActive = $helperIsSupporterActive($requested_member_id);
            $memberClean['isSupporterActive'] = $isSupporterActive;
        }
       
        $result['member'] = $memberClean;
        if ($this->currentUser()->isAdmin()) {
            $result['memberScore'] = $memberScore;
            $result['earnInfo'] = $earnInfo;
        }
        $result['aboutmeUserInfo'] = $aboutmeUserInfo;        
        $result['url_gitlab'] = $this->ocsConfig->settings->client->default->url_gitlab;
        $result['url_forum'] = $this->ocsConfig->settings->client->default->url_forum;

        $affiliates = $this->sectionSupportService->fetchAffiliatesForMember($requested_member_id);
        $result['affiliates'] = $affiliates;

        // products
        $total_records = $this->projectService->countAllProjectsForMemberCatFilter($requested_member_id, true, null);
        $userProducts = $this->projectService->getUserActiveProjects($requested_member_id
                                                , self::PAGELIMIT50
                                                , 0);        
        $result['pageLimit'] =  self::PAGELIMIT50;
        $result['projectpage'] = 1;
        $result['total_records'] = $total_records;

        foreach ($userProducts as &$v) {
            $v = UtilReact::cleanUserProducts($v);            
        }
        $result['userProducts'] = UtilReact::productImageSmall200($userProducts);

        $userFeaturedProducts = $this->projectService->fetchAllFeaturedProjectsForMember($requested_member_id);    
        if($userFeaturedProducts)  {
            foreach ($userFeaturedProducts as &$v) {
                $v = UtilReact::cleanUserProducts($v);            
            }
            $result['userFeaturedProducts'] = UtilReact::productImageSmall200($userFeaturedProducts); 
        }      

        $userCollections = $this->projectService->fetchAllCollectionsForMember($requested_member_id);
        if($userCollections){
            foreach ($userCollections as &$v) {
                $v = UtilReact::cleanUserProducts($v);            
            }
            $result['userCollections'] =  UtilReact::productImageSmall200($userCollections);  
        }

        // comments
        $paginationComments = $this->memberService->fetchComments($requested_member_id);
        if ($paginationComments) {
            $paginationComments->setItemCountPerPage(self::PAGELIMIT50);
            $array =  iterator_to_array($paginationComments->getCurrentItems(), true);
            $result['comments'] =  UtilReact::productImageSmall200($array);
        }


        // favs
        $likes = $this->projectFollowerRepository->fetchLikesForMember($requested_member_id);
        $likes->setItemCountPerPage(self::PAGELIMIT20);
        $likes->setCurrentPageNumber(1);
        $array =  iterator_to_array($likes->getCurrentItems(), true);
        $result['likes'] =  UtilReact::productImageSmall200($array);


        // plings
        $plings = $this->projectPlingsService->fetchPlingsForMember($requested_member_id);
        $plings->setItemCountPerPage(self::PAGELIMIT20);
        $plings->setCurrentPageNumber(1);
        $plingsarray = iterator_to_array($plings->getCurrentItems(), true);       
        $result['plings'] = UtilReact::productImageSmall200($plingsarray);


        // plings
        $pslist = $this->projectPlingsService->fetchPlingsForSupporter($requested_member_id);
        $pslist->setItemCountPerPage(self::PAGELIMIT20);
        $pslist->setCurrentPageNumber(1);
        $array = iterator_to_array($pslist->getCurrentItems(), true);        
        $result['supportersplings'] =  UtilReact::productImageSmall200($array);


        // rated
        $rated = $this->projectRatingRepository->getRatedForMember($requested_member_id);
        $ratedlist = new Paginator(new ArrayAdapter($rated));
        $ratedlist->setItemCountPerPage(self::PAGELIMIT20);
        $ratedlist->setCurrentPageNumber(1);
        $array = iterator_to_array($ratedlist->getCurrentItems(), true);
        $result['rated'] =  UtilReact::productImageSmall200($array);


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

            $sectionsCount = $this->memberService->fetchSupporterSectionNr($requested_member_id);
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

        // $stat['cntDuplicateSourceurl'] = 0;

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
                $gitProjects = $this->gitlab->getUserProjects($member->gitlab_user_id);
                $cntGitp = count($gitProjects);
            }
        } catch (Exception $exc) {
            $cntGitp = 0;
        }
        $result['cntGitp'] = $cntGitp;



        return $result;
    }
  

    private function detailLoadRatingsData($project_id)
    {
        $tableProjectRatings = $this->projectRatingRepository;
        $ratings = $tableProjectRatings->fetchRating($project_id);
        foreach ($ratings as &$value) {
            $value = UtilReact::cleanRatings($value);
        }
        return $ratings;
    }
    private function fetchGitlabProject($gitProjectId)
    {
        
        $gitlab = $this->gitlab;

        try {
            $gitProject = $gitlab->getProject($gitProjectId);
        } catch (Exception $exc) {
            //Project is gone
            // $modelProject = $this->projectService;
            // $modelProject->updateProject(
            //     $this->_projectId, array(
            //                          'is_gitlab_project'          => 0,
            //                          'gitlab_project_id'          => null,
            //                          'show_gitlab_project_issues' => 0,
            //                          'use_gitlab_project_readme'  => 0,
            //                      )
            // );
            // $gitProject = null;
            return null;
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
 
    
     /**
     * @param array $inputFilterParams
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     * @throws Exception
     */
    private function fetchRequestedElements($inputFilterParams, $limit = null, $offset = null)
    {
        return $this->projectService->fetchProjectsByFilter($inputFilterParams, $limit, $offset);
    }

    protected function setLayout()
    {        
        $this->layout()->setTemplate('layout/pling-ui');              
    }
  
    private function getFilterTagFromCookie($group)
    {
        $config = $this->ocsConfig;
        $cookieName = $config->settings->session->filter_browse_original . $group;        
        return isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : null;
    }

    
}

  
