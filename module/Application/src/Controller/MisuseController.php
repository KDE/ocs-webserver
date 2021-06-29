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
 *
 * Created: 31.05.2017
 */

namespace Application\Controller;

use Application\Model\Interfaces\ReportProductsInterface;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\ViewModel;

/**
 * Class MisuseController
 *
 * @package Application\Controller
 */
class MisuseController extends BaseController
{

    private $reportProductsRepository;

    public function __construct(
        ReportProductsInterface $reportProductsRepository
    ) {
        parent::__construct();

        $this->reportProductsRepository = $reportProductsRepository;

    }

    public function indexAction()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $this->layout()->noheader = 1;
        $viewModel = new ViewModel();
        $itemCountPerPage = 10;
        $page = $this->params()->fromRoute('page', 1);
        $candidateProducts = $this->reportProductsRepository->fetchMisuseCandidate();

        $list = new Paginator(new ArrayAdapter($candidateProducts->toArray()));
        $list->setItemCountPerPage($itemCountPerPage);
        $list->setCurrentPageNumber($page);
        $rownum = 1 + (($page - 1) * $itemCountPerPage);
        foreach ($list as &$l) {
            $l['rownum'] = $rownum++;
        }
        $viewModel->setVariable('products', $list);

        return $viewModel;
    }

}