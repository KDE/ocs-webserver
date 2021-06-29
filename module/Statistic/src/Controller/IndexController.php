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

namespace Statistic\Controller;

use Application\Model\Interfaces\ConfigStoreCategoryInterface;
use Application\Model\Interfaces\ProjectCategoryInterface;
use Application\Model\Interfaces\ProjectPlingsInterface;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Service\Interfaces\ProjectCategoryServiceInterface;
use DateTime;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Statistic\Model\Interfaces\DataStatiDwhInterface;
use Statistic\Model\Interfaces\DataStatiInterface;
use Statistic\Model\Repository\DataStatiDwhRepository;

class IndexController extends AbstractActionController
{

    private $dataRepository;
    private $dwhRepository;
    private $projectCategoryService;
    private $projectPlingsRepository;
    private $projectCategoryRepository;
    private $configStoreCategoryRepository;

    public function __construct(
        DataStatiInterface $data,
        DataStatiDwhInterface $dwhdata,
        ProjectCategoryServiceInterface $projectCategoryService,
        ProjectPlingsInterface $projectPlingsRepository,
        ProjectCategoryInterface $projectCategoryRepository,
        ConfigStoreCategoryInterface $configStoreCategoryRepository
    ) {
        $this->dataRepository = $data;
        $this->dwhRepository = $dwhdata;
        $this->projectCategoryService = $projectCategoryService;
        $this->projectPlingsRepository = $projectPlingsRepository;
        $this->projectCategoryRepository = $projectCategoryRepository;
        $this->configStoreCategoryRepository = $configStoreCategoryRepository;
    }

    // Add the following method:
    public function indexAction()
    {
        return new ViewModel([]);
    }

    public function newMembersProjectsAction()
    {
        return new ViewModel([]);
    }

    public function newMembersProjectsJsonAction()
    {
        $result = array();
        $m = $this->dwhRepository->getNewmemberstats();
        $p = $this->dwhRepository->getNewprojectstats();
        foreach ($m as $member) {
            $date = $member['memberdate'];
            $t = array(
                'date'     => $date,
                'members'  => $member['daycount'],
                'projects' => 0,
            );
            foreach ($p as $project) {
                $d = $project['projectdate'];
                if ($d == $date) {
                    $t['projects'] = $project['daycount'];
                    break;
                }
            }
            $result [] = $t;
        }

        return $this->sendJson(array_reverse($result));
    }

    private function sendJson($result)
    {
        $data = array(
            'status'  => 'ok',
            'msg'     => '',
            'results' => $result,
        );
        $viewModel = new JsonModel();
        $viewModel->setVariable('data', $data);

        return $viewModel;
    }

    public function newProductsWeeklyAction()
    {
    }

    public function newProductsWeeklyJsonAction()
    {

        $listwallpapers = $this->dwhRepository->getNewprojectWeeklystatsWallpapers();
        $listNowallpapers = $this->dwhRepository->getNewprojectWeeklystatsWithoutWallpapers();


        $map1 = array();
        foreach ($listwallpapers as $value) {
            $map1[$value['yyyykw']] = $value['amount'];
        }

        $map2 = array();
        foreach ($listNowallpapers as $value) {
            $map2[$value['yyyykw']] = $value['amount'];
        }

        $datetime = new DateTime();
        $result = array();
        for ($i = 1; $i < 60; $i++) {
            $w = '-1 week';
            $datetime->modify($w);
            $month = $datetime->format('YW');

            $value1 = 0;
            if (isset($map1[$month])) {
                $value1 = $map1[$month];
            }
            $value2 = 0;
            if (isset($map2[$month])) {
                $value2 = $map2[$month];
            }
            $result[] = array(
                'yyyykw'             => $month,
                'amountnowallpapers' => $value2,
                'amountwallpapers'   => $value1,
            );
        }

        return $this->sendJson(array_reverse($result));

    }

    public function downloadsDomainAction()
    {
    }

    public function downloadsDomainJsonAction()
    {
        $dateBegin = $this->params()->fromQuery('dateBegin', '');
        $dateEnd = $this->params()->fromQuery('dateEnd', '');
        $result = $this->dwhRepository->getDownloadsDomainStati($dateBegin, $dateEnd);

        return $this->sendJson($result);
    }

    public function downloadsPayoutsDailyAction()
    {
    }

    public function getdownloadsdailyAction()
    {
        $numofmonthback = $this->params()->fromQuery('numofmonthback', '3');

        return $this->sendJson($this->dwhRepository->getDownloadsDaily($numofmonthback));
    }

    public function productPerDayAction()
    {
    }

    public function gettopdownloadsperdateAction()
    {
        $date = $this->params()->fromQuery('date', '');

        return $this->sendJson($this->dwhRepository->getTopDownloadsPerDate($date));
    }

    public function gettopdownloadspermonthAction()
    {

        $month = $this->params()->fromQuery('month', '');
        $catid = $this->params()->fromQuery('catid', '');

        return $this->sendJson($this->dwhRepository->getTopDownloadsPerMonth($month, $catid));
    }

    public function newcomerAction()
    {
        $yyyymm = $this->params()->fromRoute('yyyymm', '1');

        return $this->sendJson($this->dwhRepository->getNewcomer($yyyymm));

    }

    public function newloserAction()
    {
        $yyyymm = $this->params()->fromRoute('yyyymm', '1');

        return $this->sendJson($this->dwhRepository->getNewloser($yyyymm));
    }

    public function monthdiffAction()
    {
        $yyyymm = $this->params()->fromRoute('yyyymm', '1');

        return $this->sendJson($this->dwhRepository->getMonthDiff($yyyymm));

    }

    public function productPerMonthAction()
    {
        $categories = $this->projectCategoryService->fetchTreeForView();

        return new ViewModel(['categories' => $categories]);
    }

    public function payoutMemberAction()
    {
        $plings = $this->projectPlingsRepository->getAllPlingListReceived();
        $plingsGiven = $this->projectPlingsRepository->getAllPlingListGiveout();

        return new ViewModel(['plings' => $plings, 'plingsGiven' => $plingsGiven]);
    }

    public function payoutNewcomerAction()
    {
    }

    public function payoutNewloserAction()
    {
    }

    public function payoutMonthDiffAction()
    {
    }

    public function payoutCategoriesAction()
    {
    }

    public function getpayoutcategorymonthlyAction()
    {
        $yyyymm = $this->params()->fromRoute('yyyymm', '201708');

        return $this->sendJson($this->dwhRepository->getPayoutCategoryMonthly($yyyymm));
    }

    public function payoutCategoryMonthlyAction()
    {
        $categories = $this->projectCategoryService->fetchTreeForView();

        return new ViewModel(['categories' => $categories]);

    }

    public function payoutGroupbyAmountAction()
    {
        $products = $this->dataRepository->getPayoutgroupbyamountProduct();
        $member = $this->dataRepository->getPayoutgroupbyamountMember();

        return new ViewModel(['products' => $products, 'member' => $member,]);
    }

    public function getpayoutgroupbyamountAction()
    {
        $data = $this->dataRepository->getPayoutgroupbyamountProduct();

        return $this->sendJson($data);
    }

    public function getpayoutgroupbyamountmemberAction()
    {

        return $this->sendJson($this->dataRepository->getPayoutgroupbyamountMember());
    }

    public function getproductmonthlyAction()
    {
        $project_id = $this->params()->fromRoute('project_id');

        return $this->sendJson($this->dwhRepository->getProductMonthly($project_id));
    }

    public function getproductdaylyAction()
    {
        $project_id = $this->params()->fromRoute('project_id');

        return $this->sendJson($this->dwhRepository->getProductDayly($project_id));
    }

    public function getpayoutyearAction()
    {
        return $this->sendJson($this->dwhRepository->getPayoutyear());
    }

    public function getpayoutAction()
    {
        $yyyymm = $this->params()->fromRoute('yyyymm', '201708');
        $data = $this->dwhRepository->getPayout($yyyymm);
        $plings = $this->projectPlingsRepository->getAllPlingListReceived();
        foreach ($data as &$d) {
            $d['plings'] = 0;
            foreach ($plings as $p) {
                if ($p['member_id'] == $d['member_id']) {
                    $d['plings'] = $p['plings'];
                    break;
                }
            }
        }

        return $this->sendJson($data);
    }

    public function getpayoutmemberpercategoryAction()
    {

        $yyyymm = $this->params()->fromQuery('yyyymm', '201708');
        $catid = $this->params()->fromQuery('catid', 0);

        return $this->sendJson($this->dwhRepository->getPayoutMemberPerCategory($yyyymm, $catid));
    }

    public function getpayoutcategoryAction()
    {

        $catid = (int)$this->params()->fromQuery('catid', 0);

        $result = $this->dwhRepository->getPayoutCategory($catid);

        if ($catid == 0) {
            $pids = $this->configStoreCategoryRepository->fetchCatIdsForStore(DataStatiDwhRepository::DEFAULT_STORE_ID);
        } else {
            $pids = $this->projectCategoryRepository->fetchImmediateChildrenIds($catid, ProjectCategoryRepository::ORDERED_TITLE);
        }

        if ($pids) {
            $pidsname = $this->projectCategoryService->fetchCatNamesForID($pids);
        } else {
            $pidsname = [];
        }


        $msg = array(
            'status'   => 'ok',
            'msg'      => '',
            'pids'     => $pids,
            'pidsname' => $pidsname,
            'results'  => $result,
        );

        $viewModel = new JsonModel();
        $viewModel->setVariable('data', $msg);

        return $viewModel;
    }

    public function topDownloadsControllingAction()
    {
        $datum = $this->params()->fromQuery('datum', '20210124');
        $data = $this->dataRepository->getTopDownloads($datum);
        $datumlist = $this->dataRepository->getTopDownloadsDatum();
        return new ViewModel(['data' => $data,'datum'=>$datum,'datumlist'=>$datumlist]);
    }

    public function topDownloadsControllingJsonAction()
    {
        $dateBegin = $this->params()->fromQuery('dateBegin', '');
        $dateEnd = $this->params()->fromQuery('dateEnd', '');
        $result = $this->dwhRepository->getDownloadsDomainStati($dateBegin, $dateEnd);

        return $this->sendJson($result);
    }
}