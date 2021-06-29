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
 * */

namespace Application\Controller;


use Application\Model\Repository\BrowseListTypesRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Repository\TagsRepository;
use Application\Model\Service\BbcodeService;
use Application\Model\Service\HtmlPurifyService;
use Application\Model\Service\InfoService;
use Application\Model\Service\MemberService;
use Application\Model\Service\ProjectCategoryService;
use Application\Model\Service\ProjectService;
use Application\Model\Service\SectionService;
use Application\Model\Service\TagService;
use Application\Model\Service\Util;
use Application\View\Helper\BuildExploreUrl;
use Application\View\Helper\CatTitle;
use Application\View\Helper\FetchHeaderData;
use Application\View\Helper\IsSupporter;
use Exception;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Request;
use Laminas\Json\Encoder;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Library\Tools\ParseDomain;

/**
 * Class ExploreController
 *
 * @package Application\Controller
 */
class ExploreController extends DomainSwitch
{

    const DEFAULT_ORDER = 'latest';
    const TAG_ISORIGINAL = 'original-product';

    /** @var  string */
    protected $_browserTitlePrepend;
    protected $projectCategoryRepository;
    protected $projectCategoryService;
    protected $infoService;
    protected $viewRenderer;
    protected $tagService;
    protected $memberService;
    protected $sectionService;
    protected $tagsRepository;
    /**
     * DbtestController constructor.
     *
     * @param AdapterInterface $db
     * @param array            $config
     * @param Request          $request
     * @param                  $viewRenderer
     * @param InfoService      $infoService
     * @param TagService      $tagService
     * @param MemberService      $memberService
     * @param SectionService      $sectionService
     * @param TagsRepository      $tagsRepository
     */
    public function __construct(
        AdapterInterface $db,
        array $config,
        Request $request,
        $viewRenderer,
        InfoService $infoService,
        TagService $tagService,
        MemberService $memberService,
        SectionService $sectionService,
        TagsRepository $tagsRepository
    ) {
        parent::__construct($db, $config, $request);
        parent::init();
        $this->projectCategoryRepository = new ProjectCategoryRepository($this->db, $this->cache);
        $this->projectCategoryService = new ProjectCategoryService($this->db, $config);
        $this->infoService = $infoService;
        $this->viewRenderer = $viewRenderer;
        $this->tagService = $tagService;
        $this->memberService = $memberService;
        $this->sectionService = $sectionService;
        $this->tagsRepository = $tagsRepository;
    }

    public function init()
    {
        //parent::init();
        //$this->_auth = Zend_Auth::getInstance();
    }

    public function categoriesAction()
    {
        // Filter-Parameter
        $inputFilterParams['category'] = (int)$this->params('cat', null);
        $inputFilterParams['filter'] = (int)$this->params('fil', null);
        $inputFilterParams['order'] = preg_replace('/[^-a-zA-Z0-9_]/', '', $this->params('ord', self::DEFAULT_ORDER));
        $inputFilterParams['selected'] = (int)$this->params('sel', $inputFilterParams['category']);

        $modelCategories = $this->projectCategoryRepository;
        $children = $modelCategories->fetchImmediateChildren($inputFilterParams['selected']);
        $selChild = $modelCategories->fetchElement($inputFilterParams['filter']);

        $response = $this->generateResponseMsg($children, $inputFilterParams, $selChild);

        return new JsonModel($response);
    }

    private function generateResponseMsg($children, $inputParams = null, $selChild = null)
    {
        $result = array();

        if (count($children) == 0) {
            return $result;
        }

        $helperBuildExploreUrl = new BuildExploreUrl($this->request);
        foreach ($children as $child) {
            $nodeSelectedState = $inputParams['filter'] == $child['project_category_id'];
            if (1 == ($child['rgt'] - $child['lft'])) {
                $result[] = array(
                    'title'    => $child['title'],
                    'key'      => $child['project_category_id'],
                    'href'     => $helperBuildExploreUrl->buildExploreUrl(
                        $inputParams['category'], $child['project_category_id'], $inputParams['order']
                    ),
                    'target'   => '_top',
                    'selected' => $nodeSelectedState,
                );
            } else {
                $nodeHasChildren = (1 == ($child['rgt'] - $child['lft'])) ? false : true;
                $nodeIsSelectedSubCat = (($selChild['lft'] > $child['lft']) and ($selChild['rgt'] < $child['rgt']));
                $nodeExpandedState = false;
                $nodeChildren = null;
                if ($nodeHasChildren and $nodeIsSelectedSubCat) {
                    $nodeExpandedState = true;
                    $modelCategories = $this->projectCategoryRepository;
                    $immChildren = $modelCategories->fetchImmediateChildren($child['project_category_id']);
                    $nodeChildren = $this->generateResponseMsg($immChildren, $inputParams, $selChild);
                }
                $result[] = array(
                    'title'    => $child['title'],
                    'key'      => $child['project_category_id'],
                    'folder'   => true,
                    'lazy'     => true,
                    'selected' => $nodeSelectedState,
                    'expanded' => $nodeExpandedState,
                    'href'     => $helperBuildExploreUrl->buildExploreUrl(
                        $inputParams['category'], $child['project_category_id'], $inputParams['order']
                    ),
                    'target'   => '_top',
                    'children' => $nodeChildren,
                );
            }
        }

        return $result;
    }

    /**
     * @return array|ViewModel
     * @throws Exception
     */
    public function indexAction()
    {

        $this->layout()->setTemplate('layout/flat-ui');

        // Filter-Parameter
        /* $inputFilterOriginal = $this->params('filteroriginal', $this->getFilterOriginalFromCookie());
          $this->storeFilterOriginalInCookie($inputFilterOriginal);
          $this->view->inputFilterOriginal = $inputFilterOriginal;
         */

        $isShowOnlyFavs = (int)$this->params('fav');


        $this->view->setVariable('projectCategoryRepository', $this->projectCategoryRepository);
        $this->view->setVariable('projectCategoryService', $this->projectCategoryService);
        $this->view->setVariable('BuildExploreUrl', new BuildExploreUrl($this->request));
        $this->view->setVariable('infoService', $this->infoService);
        $this->view->setVariable('phpRenderer', $this->viewRenderer);

        $inputCatId = (int)$this->params('cat');        

        if ($inputCatId) {
//            $this->view->isFilterCat = true;
//            $this->view->filterCat = $inputCatId;
            $this->view->setVariable('catabout', $this->getCategoryAbout($inputCatId));

            $helperFetchCategory = new CatTitle($this->projectCategoryRepository);
            $catTitle = $helperFetchCategory->catTitle($inputCatId);

            //$this->view->headTitle($catTitle . ' - ' . $_SERVER['HTTP_HOST'], 'SET');
            $this->view->setVariable('headTitle', ($catTitle . ' - ' . $this->getHeadTitle()));
        }
        
        $tagCloud = $this->tagService->getTopTagsPerCategory($inputCatId);
        $this->view->setVariable('tagCloud', $tagCloud);
        $this->view->setVariable('cat_id', $inputCatId);
        $this->view->setVariable('showAddProduct', 1);

        $storeCatIds = $GLOBALS['ocs_store_category_list'];

        $filter = array();
        $filter['category'] = $inputCatId ? $inputCatId : $storeCatIds;
        $filter['order'] = preg_replace('/[^-a-zA-Z0-9_]/', '', $this->params('ord', self::DEFAULT_ORDER));
        // removed filter original 20191007
        //$filter['original'] = $inputFilterOriginal == 1 ? self::TAG_ISORIGINAL : null;


        if ($isShowOnlyFavs == 1) {
            if ($this->_authMember->member_id) {
                $filter['favorite'] = $this->_authMember->member_id;
                $this->view->authMember = $this->_authMember;
            } else {
                $this->redirect()->toUrl('/browse');
            }
        } else {
            $isShowOnlyFavs = 0;
        }


        $filter['tag'] = $GLOBALS['ocs_config_store_tags'];       
        if(!$filter['tag']){
            $filter['tag']=[];
        }

        if (APPLICATION_ENV == "development") {
            $GLOBALS['ocs_log']->debug(__METHOD__ . ' - ' . json_encode($filter));
        }

        $tagFilter = $GLOBALS['ocs_config_store_tags'];

        $tagGroupFilter = $GLOBALS['ocs_config_store_taggroups'];

        if (!empty($tagGroupFilter)) {
            $filterArray = array();
            foreach ($tagGroupFilter as $tagGroupId) {
                $inputFilter = $this->getFilterTagFromCookie($tagGroupId);
                $filterArray[$tagGroupId] = $inputFilter;
                if (!empty($inputFilter)) {
                    $filter['tag'][] = $inputFilter;
                }
            }
            $this->view->setVariable('tag_group_filter', $filterArray);
        }

        // filter tag
        $tagname = $this->getParam('tag',null);
        $tagId = null;
        if($tagname)
        {                        
           $tagObject = $this->tagsRepository->fetchTagByName($tagname);
           $tagId = $tagObject['tag_id'];     
           $filter['tag'][]=$tagId;      
           $this->view->setVariable('tag', $tagObject);
        }
              
        $storeConfig = $GLOBALS['ocs_store']->config;

        $page = (int)$this->params('page', 1);

        $index = null;
        $browseListType = null;

        //Old: index as Param
        $index = $this->params('index');

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
        if (!$index && $this->view->getVariable('cat_id') != 0) {
            //Now the list type is in backend categories set
            $cat = $this->projectCategoryRepository->findCategory($this->view->getVariable('cat_id'));

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
            $requestedElements = $this->fetchRequestedElements($filter, $pageLimit, ($page - 1) * $pageLimit);
            $this->view->setVariable('productsJson', Encoder::encode($requestedElements['elements']));
            $this->view->setVariable('filtersJson', Encoder::encode($filter));            
            $this->view->setVariable('cat_idJson', Encoder::encode($inputCatId));
            $modelInfo = $this->infoService;
            $topprods = $modelInfo->getMostDownloaded(100, $inputCatId, $tagFilter);
            $this->view->setVariable('topprodsJson', Encoder::encode($topprods));
            $comments = $modelInfo->getLatestComments(5, $inputCatId, $tagFilter);
            $this->view->setVariable('commentsJson', Encoder::encode($comments));
            $this->view->setVariable(
                'categoriesJson', Encoder::encode($this->projectCategoryRepository->fetchTreeForView())
            );
            $this->view->setVariable('templateConfigData', $this->templateConfigData);

            $countSupporters = $this->infoService->getCountAllSupporters();
            $this->view->setVariable('countSupporters', $countSupporters);
            $supporters = $this->infoService->getNewActiveSupporters(7);
            $this->view->setVariable('supporters', $supporters);
            $comments = $modelInfo->getLatestComments(7, $inputCatId, $tagFilter);
            $this->view->setVariable('comments', $comments);
            $this->view->setVariable('topprods', $topprods);

            if ($this->view->getVariable('cat_id') != 0) {
                $modelCategory = new ProjectCategoryRepository($this->db, $this->cache);
                $dataCategory = $modelCategory->findById($this->view->getVariable('cat_id'));
                $this->view->setVariable('dataCategory', $dataCategory);
            }

            $this->view->setVariable('browseListType', $browseListType);

            $this->view->setVariable('pageLimit', $pageLimit);

            $this->view->setTemplate('application/explore/index-react' . $index);
        } else {
            if ($storeConfig->layout_explore && $storeConfig->isRenderReact()) {
                $pageLimit = 50;
                $requestedElements = $this->fetchRequestedElements($filter, $pageLimit, ($page - 1) * $pageLimit);
                $this->view->setVariable('productsJson', Encoder::encode($requestedElements['elements']));
                $this->view->setVariable('filtersJson', Encoder::encode($filter));
                $this->view->setVariable('cat_idJson', Encoder::encode($inputCatId));
                $modelInfo = $this->infoService;
                $topprods = $modelInfo->getMostDownloaded(100, $inputCatId, $tagFilter);
                $this->view->setVariable('topprodsJson', Encoder::encode($topprods));
                $comments = $modelInfo->getLatestComments(5, $inputCatId, $tagFilter);
                $this->view->setVariable('commentsJson', Encoder::encode($comments));
                $modelCategory = $this->projectCategoryRepository;
                $this->view->setVariable('categoriesJson', Encoder::encode($modelCategory->fetchTreeForView()));

                $this->view->setTemplate('application/explore/index-react');

                $this->view->setVariable('pageLimit', $pageLimit);
            } else {
                $pageLimit = 10;
                $requestedElements = $this->fetchRequestedElements($filter, $pageLimit, ($page - 1) * $pageLimit);
                $this->view->setVariable('pageLimit', $pageLimit);
            }
        }
        if ($storeConfig) {
            $this->view->setVariable('storeabout', $this->getStoreAbout($storeConfig->store_id));
        }

        $paginator = new Paginator(new ArrayAdapter($requestedElements['elements']));
        $paginator->setItemCountPerPage($pageLimit);
        $paginator->setCurrentPageNumber($page);
        //$paginator->setTotalItemCount($requestedElements['total_count']);

        $this->view->setVariable('products', $paginator);
        $this->view->setVariable('totalcount', $requestedElements['total_count']);
        $this->view->setVariable('filters', $filter);
        $this->view->setVariable('page', $page);
        $this->view->setVariable('package_type', $GLOBALS['ocs_config_store_tags']);
        $this->view->setVariable('tags', $tagFilter);

        return $this->view;
    }

    // /**
    //  * @param $inputCatId
    //  *
    //  * @return string|null
    //  */
    // protected function getCategoryAbout($inputCatId)
    // {
    //     $config = $this->config;
    //     $static_config = $config->settings->static;

    //     $include_path_cat = $static_config->include_path . '/category_about/' . $inputCatId . '.phtml';

    //     if (file_exists($include_path_cat)) {
    //         return $include_path_cat;
    //     }

    //     return null;
    // }

    private function getFilterTagFromCookie($group)
    {
        $config = $this->config;
        $cookieName = $config->settings->session->filter_browse_original . $group;

        return isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : null;
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
        $modelProject = new ProjectService($this->db);

        return $modelProject->fetchProjectsByFilter($inputFilterParams, $limit, $offset);
    }

   

    public function savetaggroupfilterAction()
    {
        // Filter-Parameter
        $tagGroupId = (int)$this->getParam('group_id');
        $tagId = (int)$this->getParam('tag_id');

        $this->storeFilterTagInCookie($tagGroupId, $tagId);

        $filtertag = $this->getFilterTagFromCookie($tagGroupId);

        $response = array();
        $response['Result'] = 'OK';
        $response['Set TagGroup'] = $tagGroupId;
        $response['Set TagId'] = $tagId;
        $response['Cookie TagId'] = $filtertag;

        return new JsonModel($response);
    }

    private function storeFilterTagInCookie($group, $tag)
    {
        $storedInCookie = $this->getFilterTagFromCookie($group);

        if (isset($tag) and ($tag != $storedInCookie)) {
            $config = $this->config;
            $cookieName = $config->settings->session->filter_browse_original . $group;
            $remember_me_seconds = $config->settings->session->cookie_lifetime;
            $cookieExpire = time() + $remember_me_seconds;
            $cookie_domain = ParseDomain::get_domain($_SERVER['HTTP_HOST']);
            $cookie_params = session_get_cookie_params();
            //setcookie($cookieName, $tag, $cookieExpire, '/');
            setcookie(
                $cookieName, $tag, time() + $cookieExpire, $cookie_params['path'], $cookie_domain,
                $cookie_params['secure'], true
            );
        }
    }

    /**
     * @param array $hits
     *
     * @return array
     * @deprecated
     */
    protected function copyToArray($hits)
    {
        $returnArray = array();
        foreach ($hits as $hit) {
            $returnArray[] = $hit->getDocument();
        }

        return $returnArray;
    }

    protected function _initResponseHeader()
    {
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";

        $this->getResponse()->setMetadata('X-FRAME-OPTIONS', 'ALLOWALL', true)
//           ->setHeader('Last-Modified', $modifiedTime, true)
             ->setMetadata('Expires', $expires, true)->setMetadata('Pragma', 'no-cache', true)->setMetadata(
                'Cache-Control', 'private, no-cache, must-revalidate', true
            );
    }

    protected function setLayout()
    {
        $layoutName = 'flat_ui_template';
        $storeConfig = $GLOBALS['ocs_store']->config;

        if ($storeConfig && $storeConfig->layout_explore) {
            $this->layout($storeConfig->layout_explore);
        } else {
            $this->layout($layoutName);
        }
    }

}
