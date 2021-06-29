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
 * */

namespace Application\Controller;

use Application\Model\Service\InfoService;
use Application\Model\Service\SectionService;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Request;

/**
 * Class FundingController
 *
 * @package Application\Controller
 */
class FundingController extends DomainSwitch
{

    protected $_request = null;
    protected $_auth;
    protected $infoService;
    protected $sectionService;
    protected $isAdmin;

    public function __construct(
        AdapterInterface $db,
        array $config,
        Request $request,
        InfoService $infoService,
        SectionService $sectionService
    ) {
        parent::__construct($db, $config, $request);
        $this->init();
        $this->infoService = $infoService;
        $this->sectionService = $sectionService;
        $this->isAdmin = ($this->_authMember and $this->_authMember->roleName == 'admin');
        $this->view->setVariable('isAdmin', $this->isAdmin);
    }

    public function init()
    {
        parent::init();
        $this->_auth = $this->_authMember;
    }

    public function indexAction()
    {
        $this->setLayout();


        $this->view->setVariable('authMember', $this->_authMember);
        $this->view->setVariable('headTitle', 'Funding - ' . $this->getHeadTitle());


        $downloadYears = $this->sectionService->getAllDownloadYears($this->isAdmin);
        $this->view->setVariable('downloadYears', $downloadYears);

        $config = $this->config->settings->client->default;
        $baseurlStore = $config->baseurl_store;
        $this->view->setVariable('baseurlStore', $baseurlStore);

        return $this->view;
    }

    public function plingsajaxAction()
    {
        $this->view->setTerminal(true);

        $year = $this->getParam('year');
        $this->view->setVariable('year', $year);

        $currentYear = date("Y", time());

        if ($year) {
            $currentYear = $year;
        }

        $downloadMonths = $this->sectionService->getAllDownloadMonths($currentYear, $this->isAdmin);
        $this->view->setVariable('downloadMonths', $downloadMonths);

        return $this->view;
    }

    public function plingsmonthajaxAction()
    {
        $this->view->setTerminal(true);

        $yearmonth = $this->getParam('yearmonth');
        $this->view->setVariable('yearmonth', $yearmonth);

        $allSections = $this->sectionService->fetchAllSections();
        $this->view->setVariable('allSections', $allSections);
        $this->view->setVariable('sectionService', $this->sectionService);
        $this->view->setVariable('infoService', $this->infoService);

        return $this->view;
    }

}
