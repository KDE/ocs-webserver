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

use Application\Model\Interfaces\ProjectCategoryInterface;
use Application\Model\Repository\CommentsRepository;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Service\InfoService;
use Application\Model\Service\Interfaces\InfoServiceInterface;
use Application\Model\Service\Interfaces\MemberServiceInterface;
use Application\Model\Service\Interfaces\ProjectServiceInterface;
use Application\Model\Service\SectionService;
use Application\Model\Service\Util;
use Application\View\Helper\BuildMemberUrl;
use Application\View\Helper\FetchHeaderData;
use Application\View\Helper\IsSupporter;
use Application\View\Helper\ReadSectionData;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Json\Encoder;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

/**
 * Class HomeController
 *
 * @package Application\Controller
 */
class HomeController extends BaseController
{
    protected $db;
    private $infoService;
    private $sectionService;
    private $projectService;
    private $memberService;
    private $projectCategoryRepository;
    private $projectRepository;

    public function __construct(
        AdapterInterface $db,
        InfoServiceInterface $infoService,
        ProjectServiceInterface $projectService,
        MemberServiceInterface $memberService,
        ProjectCategoryInterface $projectCategoryRepository,
        ProjectRepository $projectRepository,
        SectionService $sectionService
        
    ) {
        parent::__construct();
        $this->infoService = $infoService;
        $this->sectionService = $sectionService;
        $this->projectService = $projectService;
        $this->memberService = $memberService;
        $this->projectCategoryRepository = $projectCategoryRepository;
        $this->projectRepository = $projectRepository;
        $this->db = $db;
    }

    public function indexAction()
    {
        $auth = $this->currentUser();
        $storeConfig = $this->ocsStore->config;
        // $viewModel = new ViewModel();
        $viewModel = $this->view;
        $this->layout()->setTemplate('layout/flat-ui');
        $viewModel->setVariable('storeConfig', $storeConfig);
        $viewModel->setVariable('ocs_user', $GLOBALS['ocs_user']);

        if ($storeConfig) {
            $viewModel->setVariable('tag_filter', $this->ocsStore->tags);
            if ($storeConfig->is_show_home) {
                $index = $this->params()->fromQuery('index');
                if ($index) {
                    if ((int)$index == 1) {
                        $viewModel->setTemplate('application/home/index-' . $storeConfig->config_id_name . '-test');
                    } else {
                        $viewModel->setTemplate(
                            'application/home/index-' . $storeConfig->config_id_name . '-test' . $index
                        );
                    }
                    $viewModel->setVariable('index', $index);

                    return $viewModel;
                } else {

                    if ($storeConfig->config_id_name == 'opendesktop' && $auth->hasIdentity()) {
                        return $this->redirect()->toRoute('application_start');
                    }
                    if ($storeConfig->config_id_name == 'opendesktop') {
                        $this->layout()->noheader = true;
                    }
                    if ($storeConfig->config_id_name == 'appimagehub') {
                        $viewModel->setTemplate('application/home/index-' . $storeConfig->config_id_name);
                        $host = $_SERVER['SERVER_NAME'];

                        $totalProjects = $this->projectService->fetchTotalProjectsCount(true);
                        $viewModel->setVariable('totalProjects', $totalProjects);

                        if (strpos($host, ".cc") > 0 || strpos($host, ".local") > 0 || strpos($host, ".life") > 0) {
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
                        $viewModel->setVariable('data', $response);
                        $cat_tree = $this->projectCategoryRepository->fetchTreeForView();
                        $viewModel->setVariable('cat_tree', $cat_tree);
                    }

                    if ($storeConfig->config_id_name == 'plingcom') {
                        $viewModel = $this->preparePlingCom($viewModel);
                        $viewModel->setTemplate('application/home/index-plingcom');
                    } else {
                        $viewModel->setTemplate('application/home/index-' . $storeConfig->config_id_name);
                    }

                }

                return $viewModel;
            }
        }

        $params = array();
        if ($this->params()->fromRoute('store_id')) {
            $params['store_id'] = $this->params()->fromRoute('store_id');

            return $this->redirect()->toRoute('application_store_browse_param', $params);
        }

        return $this->redirect()->toRoute('application_browse', $params);

    }

    private function prepareHomeRightsideData($is_startpage=true,$comments=null)
    {       
        $data=[];
        if($is_startpage){
            $supporters = $this->infoService->getNewActiveSupportersForSectionAll(9);           
            $mUrl = new BuildMemberUrl();
            foreach ($supporters as &$user) {                             
                $user['profile_image_url'] = Util::image($user['profile_image_url'], array('width' => '25', 'height' => '25', 'crop' => 2));               
                $user = self::cleanMemberForJson($user);                
            }    
            $data['supporters'] = $supporters;
        }       
        $git_url = $GLOBALS['ocs_config']->settings->server->opencode->host;
        $data['git_url'] = $git_url;
        $data['baseurlStore'] = $GLOBALS['ocs_config']->settings->client->default->baseurl_store;           
        $data['ocs_user'] = $GLOBALS['ocs_user'];
        $isSupporter = new IsSupporter($this->memberService);
        $data['issupporter'] = $isSupporter($GLOBALS['ocs_user']->member_id);
        if($this->ocsStore->config=='kde-store' ||$this->ocsStore->config=='kde'  ){
            $moderators = $this->infoService->getModeratorsList();
            foreach ($moderators as &$user) {                
                $user['profile_image_url'] = Util::image($user['profile_image_url'], array('width' => '25', 'height' => '25', 'crop' => 2));
                $user = self::cleanMemberForJson($user);     
            } 
            $data['moderators'] = $moderators;
        }
        if($comments)
        {
            $data['comments'] = $comments;
        }        
        return $data;
    }

    public function indexReactAction()
    {
        $auth = $this->currentUser();
        $storeConfig = $this->ocsStore->config;
        $viewModel = new ViewModel();
        $this->layout()->setTemplate('layout/pling-ui');
        $data=[];
        $fetchHeaderData = new FetchHeaderData($this->sectionService, $this->infoService);
        $header = $fetchHeaderData(null);
        $data['header']  =  $header ;
        if ($storeConfig) {
            $viewModel->setVariable('tag_filter', $this->ocsStore->tags);
            $data['tag_filter'] = $this->ocsStore->tags;
            if ($storeConfig->is_show_home) {              

                    if ($storeConfig->config_id_name == 'opendesktop' && $auth->hasIdentity()) {
                        return $this->redirect()->toRoute('application_start');
                    }
                    if ($storeConfig->config_id_name == 'opendesktop') {
                        $this->layout()->noheader = true;
                    }
                    if ($storeConfig->config_id_name == 'appimagehub') {
                        $viewModel->setTemplate('application/home/index-' . $storeConfig->config_id_name.'2');
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
                        
                        $viewModel->setVariable('data', $response);
                        $data['data']=$response;
                        
                    }

                    $cat_tree = $this->projectCategoryRepository->fetchTreeForView();
                    $viewModel->setVariable('cat_tree', $cat_tree);
                    $data['categories'] = $cat_tree;

                    if ($storeConfig->config_id_name == 'plingcom') {
                        $data = $this->preparePlingComReact($data);
                        $viewModel->setTemplate('application/home/index-plingcom2');
                    } else {
                        $viewModel->setTemplate('application/home/index-' . $storeConfig->config_id_name.'2');
                    }

                $json = (int)$this->params()->fromQuery('json', 0);
                if($json==1)
                {
                    return new JsonModel($data);
                }else{
                    $viewModel->setVariable('homeData',$data);  
                    return $viewModel;
                }  
                
            }
        }

        $params = array();
        if ($this->params()->fromRoute('store_id')) {
            $params['store_id'] = $this->params()->fromRoute('store_id');

            return $this->redirect()->toRoute('application_store_browse_param', $params);
        }

        return $this->redirect()->toRoute('application_browse', $params);

    }

    private function preparePlingCom(ViewModel $viewModel)
    {

        $comments = $this->infoService->getLatestComments(10);
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
        $featureProducts = $this->infoService->getRandFeaturedProduct();
        $type = 'Featured';
        $json_productsPlinged = $this->infoService->getJsonNewActivePlingProduct(15);
        $response = array(
            'productsThemesGTK' => array(
                'title'    => 'Supporters Favourites',
                'catIds'   => '',
                'products' => $json_productsPlinged,
            ),
        );



        $viewModel->setVariable('comments', $comments);
        $viewModel->setVariable('productsCollections', $productsCollections);
        $viewModel->setVariable('productsThemesGTK', $productsThemesGTK);
        $viewModel->setVariable('productsThemesPlasma', $productsThemesPlasma);
        $viewModel->setVariable('productsWindowmanager', $productsWindowmanager);
        $viewModel->setVariable('productsIconsCursors', $productsIconsCursors);
        $viewModel->setVariable('productsApps', $productsApps);
        $viewModel->setVariable('productsAddons', $productsAddons);
        $viewModel->setVariable('productsWallpapersOriginal', $productsWallpapersOriginal);
        $viewModel->setVariable('productsWallpapers', $productsWallpapers);
        $viewModel->setVariable('productsArtwork', $productsArtwork);
        $viewModel->setVariable('productsVideos', $productsVideos);
        $viewModel->setVariable('productsBooksComics', $productsBooksComics);
        $viewModel->setVariable('productsPhone', $productsPhone);
        $viewModel->setVariable('productsDistors', $productsDistors);
        $viewModel->setVariable('countSupporters', $countSupporters);
        $viewModel->setVariable('featureProducts', $featureProducts);
        $viewModel->setVariable('type', 'Featured');
        $viewModel->setVariable('carouselData', Encoder::encode($response));
        $viewModel->setVariable('projectCategoryRepository', $this->projectCategoryRepository);
        $viewModel->setVariable('infoService', $this->infoService);
        $viewModel->setVariable('db', $this->db);

        $rightsidebarData = $this->prepareHomeRightsideData(true,$comments);
        $viewModel->setVariable('rightsidebarData', $rightsidebarData);

        return $viewModel;
    }

    private function preparePlingComReact(array $data)
    {

        $comments = $this->infoService->getLatestComments(10);
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
        $featureProducts = $this->infoService->getRandFeaturedProduct();
        $type = 'Featured';
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
        $data['featureProducts'] = $featureProducts;
        $data['type'] = 'Featured';
        $data['carouselData'] = $response;
        $data['supporters'] = $this->infoService->getNewActiveSupportersForSectionAll(9);
     
        return $data;
    }

    public function startAction()
    {
        if (!$this->identity()) {
            return $this->redirect()->toRoute('application_login');
        }
        $this->setLayout();
        // $viewModel = new ViewModel();
        $viewModel = $this->view;
        $storeConfig = $this->ocsStore->config;
        $viewModel->setVariable('storeConfig', $storeConfig);
        if ($storeConfig->is_show_home) {
            //index-opendesktop-start.phtml view
            $viewModel->setTemplate('application/home/index-' . $storeConfig->config_id_name . '-start');
            $data = $this->prepareStart();
            $viewModel->setVariable('data', $data);

            return $viewModel;
        }
        $params = array('ord' => 'latest', 'action' => 'index');
        if ($this->params()->fromRoute('domain_store_id')) {
            $params['domain_store_id'] = $this->params()->fromRoute('domain_store_id');
        }
        $this->forward()->dispatch('Application\Controller\ExploreController', $params);
    }

    protected function setLayout()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $this->layout()->noheader = true;
    }

    private function prepareStart()
    {
        $config = $this->ocsConfig->settings->client->default;
        $baseurl = $config->baseurl;
        $baseurlStore = $config->baseurl_store;
        $url_forum = $config->url_forum;
        $url_blog = $config->url_blog;
        $url_gitlab = $config->url_gitlab;
        $url_riot = $config->url_riot;
        $url_myopendesktop = $config->url_myopendesktop;
        $url_cloudopendesktop = $config->url_cloudopendesktop;
        $url_musicopendesktop = $config->url_musicopendesktop;
        $url_mastodon = $config->url_mastodon;
        $url_docsopendesktop = $config->url_docsopendesktop;
        $isAdmin = $this->ocsUser->isAdmin();
        $user = array(
            "username"  => $this->ocsUser->username,
            "member_id" => $this->ocsUser->member_id,
            "avatar"    => Util::image(
                $this->ocsUser->profile_image_url, array('width' => 100, 'height' => 100, 'crop' => 2)
            ),
            "isAdmin"   => $isAdmin,
        );
        // get products
        $products = $this->projectService->fetchAllProjectsForMember($this->ocsUser->member_id, 5);
        $parray = array();
        foreach ($products as $p) {
            $tmp = array(
                'project_id'    => $p->project_id,
                'image_small'   => Util::image($p->image_small, array('width' => 200, 'height' => 200)),
                'title'         => $p->title,
                'laplace_score' => $p->laplace_score * 10,
                'cat_title'     => $p->catTitle,
                'updated_at'    => Util::printDate(($p->changed_at == null ? $p->created_at : $p->changed_at)),
            );
            $parray[] = $tmp;
        }

        $comments = $this->infoService->getLastCommentsForUsersProjects($this->ocsUser->member_id);

        $commentsmoderation = $this->infoService->getLastCommentsForUsersProjects(
            $this->ocsUser->member_id, 10, CommentsRepository::COMMENT_TYPE_MODERATOR
        );
        $votes = $this->infoService->getLastVotesForUsersProjects($this->ocsUser->member_id);
        $spams = $this->infoService->getLastSpamProjects($this->ocsUser->member_id);
        $supporterinfo = $this->memberService->fetchSupporterDonationInfo($this->ocsUser->member_id);

        foreach ($comments as &$p) {
            $p['profile_image_url'] = Util::image($p['profile_image_url'], array('width' => 200, 'height' => 200));
            $p['comment_created_at'] = Util::printDate($p['comment_created_at']);
        }
        foreach ($commentsmoderation as &$p) {
            $p['profile_image_url'] = Util::image($p['profile_image_url'], array('width' => 200, 'height' => 200));
            $p['comment_created_at'] = Util::printDate($p['comment_created_at']);
        }
        foreach ($votes as &$p) {
            $p['profile_image_url'] = Util::image($p['profile_image_url'], array('width' => 200, 'height' => 200));
            $p['created_at'] = Util::printDate($p['created_at']);
        }

        foreach ($spams as &$p) {
            $p['image_small'] = Util::image($p['image_small'], array('width' => 200, 'height' => 200));
            $p['updated_at'] = Util::printDate(($p['changed_at'] == null ? $p['created_at'] : $p['changed_at']));
        }
        $response = array(
            'user'                 => $user,
            'products'             => $parray,
            'supporterinfo'        => $supporterinfo,
            'comments'             => $comments,
            'commentsmoderation'   => $commentsmoderation,
            'votes'                => $votes,
            'spams'                => ($spams == null) ? [] : $spams,
            "baseUrl"              => $baseurl,
            "baseUrlStore"         => $baseurlStore,
            "blogUrl"              => $url_blog,
            "forumUrl"             => $url_forum,
            "mastodonUrl"          => $url_mastodon,
            "gitlabUrl"            => $url_gitlab,
            "riotUrl"              => $url_riot,
            "url_myopendesktop"    => $url_myopendesktop,
            "url_cloudopendesktop" => $url_cloudopendesktop,
            "url_musicopendesktop" => $url_musicopendesktop,
            "url_docsopendesktop"  => $url_docsopendesktop,
            "url_mastodon"         => $url_mastodon,
        );

        return Encoder::encode($response);
    }

    public function showfeatureajaxAction()
    {

        $modelInfo = $this->infoService;
        $page = (int)$this->params()->fromRoute('page');
        $type = '';
        if ($page == 0) {
            $featureProducts = $modelInfo->getRandProduct();
            $type = '';

        } elseif ($page == 1) {
            $featureProducts = $modelInfo->getRandFeaturedProduct();
            $type = 'Featured';

        } elseif ($page == 2) {
            $featureProducts = $modelInfo->getRandPlingedProduct();
            $type = 'Plinged';

        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariable('featureProducts', $featureProducts);
        $viewModel->setVariable('type', $type);
        $viewModel->setTemplate('application/home/partials/featured-products');

        return $viewModel;
    }

    public function showfeatureAction()
    {

        $modelInfo = $this->infoService;
        $page = (int)$this->params()->fromQuery('t');
        $type = '';
        if ($page == 0) {
            $featureProducts = $modelInfo->getRandProduct();
            $type = '';

        } elseif ($page == 1) {
            $featureProducts = $modelInfo->getRandFeaturedProduct();
            $type = 'Featured';

        } elseif ($page == 2) {
            $featureProducts = $modelInfo->getRandPlingedProduct();
            $type = 'Plinged';

        }

        $data = [];
        $data['type'] = $type;
        $data['data'] = $featureProducts;
        return new JsonModel($data);
    }

    public function plingsAction()
    {
        $this->setLayout();
        //$viewModel = new ViewModel();  
        $viewModel = $this->view;
        $viewModel->setVariable('listCategories', $this->projectCategoryRepository->fetchTree(true, false)); 
        return $viewModel;
    }

    public function metamenubundlejsAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // public function headerAction()
    // {
    //     $data = [];
    //     $member = $this->currentUser();
        

    //     $userRoleName = $member->roleName;
    //     $isAdmin = false;
    //     if ($member->isAdmin()) {
    //     $isAdmin = true;
    //     }

    //     $jsonmember = null;
    //     if ($member && !empty($member->username)) {
    //         $jsonmember = array(
    //             'member_id'         => $member->member_id,
    //             'username'          => $member->username,
    //             'avatar'            => $member->avatar,
    //             'profile_image_url' => $member->profile_image_url,
    //             'isAdmin'           => $isAdmin,
    //             'role'              => $userRoleName,
    //         );
    //     }

        

    //     $data['auth'] = $jsonmember;        
    //     $config = $this->ocsConfig;       
    //     $baseurl = $config->settings->client->default->baseurl;
    //     $baseurlStore = $config->settings->client->default->baseurl_store;
    //     $data['baseurl'] = $baseurl;
    //     $data['baseurlStore'] = $baseurlStore;
                      
    //     $cat_id = (int)$this->params()->fromQuery('cat_id', 0);    
    //     if($cat_id==0)
    //     {
    //         $project_id = (int)$this->params()->fromQuery('project_id', 0);            
    //         if($project_id>0)
    //         {               
    //             $p = $this->projectRepository->fetchProductInfo($project_id);                
    //             $cat_id = (int)$p['project_category_id'];
    //         }            
    //     }        
    //     $data['cat_id'] = $cat_id;        
    //     if($cat_id>0)
    //     {
    //         $sectionDataService = new ReadSectionData($this->sectionService, $this->infoService);
    //         $sectiondata = $sectionDataService($cat_id);    
          
    //         $data['sectiondata'] = $sectiondata;
    //     }
        
    //     $data['ocsStoreTemplate'] =  $this->getCleanStoreTemplate();        
    //     $data['ocsStoreConfig'] = $this->getCleanStoreConfig();
    //     $data['ocsConfig'] = $this->getCleanConfig();
        
    //     $model = new JsonModel($data);        
    //     return $model;
    // }

    private function getCleanConfig()
    {
        $temp =  $this->ocsConfig->settings->client->default;
      
        $wantedKeys = array(
            'url_forum'           => 0,   
            'url_gitlab' =>0,
            'url_blog' =>0,
            'baseurl' =>0,
            'baseurl_store' =>0,
        );

        $client = array_intersect_key($temp->toArray(), $wantedKeys);

        $temp =  $this->ocsConfig->settings->server;
        $wantedKeys = array(
            'images'           => 0,   
            'videos' =>0,
            'comics' =>0,           
        );
        $server = array_intersect_key($temp->toArray(), $wantedKeys);

        return array_merge($client, $server); ;
    }


    private function getCleanStoreTemplate()
    {
        $temp =  $GLOBALS['ocs_store']->template;
        $wantedKeys = array(
            'header-logo'           => 0,   
            'header-nav-tabs' =>0,
            'header' =>0,
        );
        return array_intersect_key($temp, $wantedKeys);
    }
    private function getCleanStoreConfig()
    {
        $temp =  (array)$this->ocsStore->config;
        $wantedKeys = array(
            'host'           => 0,   
            'name' =>0,
            'is_show_title' =>0,
            'is_show_real_domain_as_url' =>0,            
        );
        return array_intersect_key($temp, $wantedKeys);
    }

    private static function cleanMemberForJson($m)
    {
        $wantedKeys = array(
            'member_id'           => 0,
            'username'             => 0,
            'profile_image_url'        => 0           
        );
        $m = array_intersect_key($m, $wantedKeys);
        return $m;
    }
}
