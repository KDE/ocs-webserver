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

namespace Application\Controller;

use Application\Model\Entity\SolrRequestEntity;
use Application\Model\Entity\SolrResultEntity;
use Application\Model\Interfaces\ProjectCategoryInterface;
use Application\Model\Interfaces\TagsInterface;
use Application\Model\Service\Interfaces\InfoServiceInterface;
use Application\Model\Service\Interfaces\SolrServiceInterface;
use Application\Model\Service\SectionService;
use Application\View\Helper\FetchHeaderData;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

/**
 * Class SearchController
 *
 * @package Application\Controller
 */
class SearchController extends BaseController
{
    /** @var SolrServiceInterface */
    private $solrService;
    /** @var TagsInterface */
    private $tagsRepository;
    /** @var ProjectCategoryInterface */
    private $projectCategoryRepository;
    private $sectionService;
    private $infoService;

    public function __construct(
        SolrServiceInterface $solrService,
        TagsInterface $tagsRepository,
        ProjectCategoryInterface $projectCategoryRepository,
        SectionService $sectionService,
        InfoServiceInterface $infoService
    ) {
        parent::__construct();
        $this->solrService = $solrService;
        $this->tagsRepository = $tagsRepository;
        $this->projectCategoryRepository = $projectCategoryRepository;
        $this->sectionService = $sectionService;
        $this->infoService = $infoService;
    }
    private function setLayoutSearch()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $headerData = $this->loadHeaderData();
        $this->layout()->setVariable('headerData',  $headerData);
    }
    public function indexAction()
    {
        $this->setLayoutSearch();        
        $solrRequestEntity = new SolrRequestEntity();
        $inputFilter = $solrRequestEntity->getInputFilter();

        $params_route = $this->params()->fromRoute();
        $params_query = $this->params()->fromQuery();
        $params_post = $this->params()->fromPost();
        $params = $params_query + $params_route + $params_post;
        $solrRequestEntity->setData($params);
        $results = array(
            'hits'         => array(),
            'pagination'   => null,
            'highlighting' => array(),
            'response'     => array('numFound' => 0),
        );

        if (false === $solrRequestEntity->isValid()) {
            $this->flashMessenger()->addErrorMessage('There was an error. Please check your input and try again.');

            return new ViewModel(['result' => $results]);
        }

        $filterScore = $solrRequestEntity->ls ? 'laplace_score:[' . (int)$solrRequestEntity->ls . ' TO ' . ((int)$solrRequestEntity->ls + 9) . ']' : null;
        $filterCat = $solrRequestEntity->pci ? 'project_category_id:(' . $solrRequestEntity->pci . ')' : null;
        $filterTags = $solrRequestEntity->t ? 'tags:(' . $solrRequestEntity->t . ')' : null;
        $filterPkg = $solrRequestEntity->pkg ? 'package_names:(' . $solrRequestEntity->pkg . ')' : null;
        $filterArch = $solrRequestEntity->arch ? 'arch_names:(' . $solrRequestEntity->arch . ')' : null;
        $filterLic = $solrRequestEntity->lic ? 'license_names:(' . $solrRequestEntity->lic . ')' : null;

        $param = array(
            'q'     => $solrRequestEntity->projectSearchText ? $solrRequestEntity->projectSearchText : $solrRequestEntity->search, // keep the old param name projectSearchText for a while
            'page'  => $solrRequestEntity->page ? $solrRequestEntity->page : 1,
            'count' => 10,
            'qf'    => $solrRequestEntity->f ? $solrRequestEntity->f : null,
            'fq'    => array($filterCat, $filterScore, $filterTags, $filterPkg, $filterArch, $filterLic),
        );

        $results = $this->solrService->search($param);

        $mapCategories = $this->projectCategoryRepository->fetchCatNames();

        if ($this->params()->fromQuery('json', null)) {
            return new JsonModel($results);
        }

        $viewModel = new ViewModel(
            [
                'result'        => $results,
                'searchText'    => $solrRequestEntity->projectSearchText ? $solrRequestEntity->projectSearchText : $solrRequestEntity->search, // keep the old param name projectSearchText for a while
                'pagination'    => $this->solrService->getPagination(),
                'products'      => $results['hits'],
                'highlighting'  => $results['highlighting'],
                'ls'            => $solrRequestEntity->ls,
                'pci'           => $solrRequestEntity->pci,
                't'             => $solrRequestEntity->t,
                'pkg'           => $solrRequestEntity->pkg,
                'arch'          => $solrRequestEntity->arch,
                'lic'           => $solrRequestEntity->lic,
                'page'          => $solrRequestEntity->page,
                'mapCategories' => $mapCategories,
                'params'        => $solrRequestEntity->getValues(),
            ]
        );

        $this->layout()->setVariable('search_count', $this->solrService->getPagination()->count()-1);

        if ($solrRequestEntity->t) {
            $tag = $this->tagsRepository->fetchTagByName($solrRequestEntity->t);
            if ($tag['tag_description']) {
                $viewModel->setVariable('tagDescription', $tag['tag_description']);
            } else {
                $viewModel->setVariable('tagDescription', null);
            }
        } else {
            $viewModel->setVariable('tagDescription', null);
        }

        return $viewModel;
    }

    private function loadHeaderData($catId=null)
    {  
        $fetchHeaderData = new FetchHeaderData($this->sectionService, $this->infoService);
        $headerData = $fetchHeaderData($catId);        
        $headerData['serverUri'] = $_SERVER["REQUEST_URI"];        
        return $headerData;
    }
}