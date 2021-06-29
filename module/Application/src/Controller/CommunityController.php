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

use Application\Model\Service\InfoService;
use Application\Model\Service\ProjectService;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Request;
use Laminas\View\Model\JsonModel;

/**
 * Class CommunityController
 *
 * @package Application\Controller
 */
class CommunityController extends DomainSwitch
{

    protected $infoService;
    protected $projectService;

    public function __construct(
        AdapterInterface $db,
        array $config,
        Request $request,
        InfoService $infoService,
        ProjectService $projectService
    ) {
        parent::__construct($db, $config, $request);
        parent::init();
        $this->infoService = $infoService;
        $this->projectService = $projectService;
    }

    public function indexAction()
    {
        $this->setLayout();
        $this->layout()->noheader = true;

        // $allDomainCatIds =
        //     Zend_Registry::isRegistered('store_category_list') ? Zend_Registry::get('store_category_list') : null;
        // $modelCategories = new Default_Model_DbTable_ProjectCategory();
        // if (isset($allDomainCatIds)) {
        //     $this->view->categories = $allDomainCatIds;
        // } else {
        //     $this->view->categories = $modelCategories->fetchMainCatIdsOrdered();
        // }        
        $tableProj = $this->projectService;
        $modelInfo = $this->infoService;
        $countProjects = $tableProj->fetchTotalProjectsCount(false);
        $this->view->setVariable('countProjects', $countProjects);
        $countActiveMembers = $modelInfo->countTotalActiveMembers();
        $this->view->setVariable('countActiveMembers', $countActiveMembers);

        return $this->view;
    }

    public function indexreactAction()
    {
        $tableProj = $this->projectService;
        $modelInfo = $this->infoService;
        $countProjects = $tableProj->fetchTotalProjectsCount(false);
        $countActiveMembers = $modelInfo->countTotalActiveMembers();
//        $isadmin = 0;
//        if ($this->_authMember and $this->_authMember->member_id and $this->_authMember->roleName == 'admin') {
//            $isadmin = 1;
//        }

        $json_data = array(
            'status' => 'ok',
            'data'   => array(
                'countProjects'      => $countProjects,
                'countActiveMembers' => $countActiveMembers,
                'isadmin'            => $this->currentUser()->isAdmin(),
            ),
        );

        return new JsonModel($json_data);
    }

    public function getjsonAction()
    {
        $event = $this->getParam('e');
        $modelInfo = $this->infoService;
        switch ($event) {
            case 'supporters':
                $json_data = array(
                    'status' => 'ok',
                    'data'   => $modelInfo->getNewActiveSupportersForSectionAll(100),
                );
                break;
            case 'newmembers':
                $json_data = array(
                    'status' => 'ok',
                    'data'   => $modelInfo->getNewActiveMembers(100),
                );
                break;
            case 'topmembers':
                $json_data = array(
                    'status' => 'ok',
                    'data'   => $modelInfo->getTopScoreUsers(100),
                );
                break;
            case 'plingedprojects':
                $json_data = array(
                    'status' => 'ok',
                    'data'   => $modelInfo->getNewActivePlingProduct(100),
                );
                break;
            case 'mostplingedcreators':
                $pageLimit = 100;
                $page = (int)$this->getParam('page', 1);
                if ($page <= 0) {
                    $page = 1;
                }
                $nopage = (int)$this->getParam('nopage', 0);
                $json_data = array(
                    'status'     => 'ok',
                    'pageLimit'  => $pageLimit,
                    'page'       => $page,
                    'nopage'     => $nopage,
                    'totalcount' => $modelInfo->getMostPlingedCreatorsTotalCnt(),
                    'data'       => $modelInfo->getMostPlingedCreators($pageLimit, ($page - 1) * $pageLimit),
                );
                break;
            case 'mostplingedproducts':
                $pageLimit = 100;
                $page = (int)$this->getParam('page', 1);
                if ($page <= 0) {
                    $page = 1;
                }
                $nopage = (int)$this->getParam('nopage', 0);
                $json_data = array(
                    'status'     => 'ok',
                    'pageLimit'  => $pageLimit,
                    'page'       => $page,
                    'nopage'     => $nopage,
                    'totalcount' => $modelInfo->getMostPlingedProductsTotalCnt(),
                    'data'       => $modelInfo->getMostPlingedProducts($pageLimit, ($page - 1) * $pageLimit),
                );
                break;
            case 'toplistmembers':
                $pageLimit = 100;
                $page = (int)$this->getParam('page', 1);
                if ($page <= 0) {
                    $page = 1;
                }
                $nopage = (int)$this->getParam('nopage', 0);
                $json_data = array(
                    'status'     => 'ok',
                    'pageLimit'  => $pageLimit,
                    'page'       => $page,
                    'nopage'     => $nopage,
                    'totalcount' => 1000,
                    'data'       => $modelInfo->getTopScoreUsers($pageLimit, ($page - 1) * $pageLimit),
                );
                break;
            default:
        }

        return new JsonModel($json_data);
    }

    public function supportersAction()
    {
        $this->view->setTerminal(true);
        $modelInfo = $this->infoService;
        $this->view->setVariable('supporters', $modelInfo->getSupporters(300));

        return $this->view;
    }

    public function newmembersAction()
    {
        $this->view->setTerminal(true);
        $modelInfo = $this->infoService;
        $this->view->setVariable('users', $modelInfo->getNewActiveMembers(100));

        return $this->view;
    }

    public function topmembersAction()
    {
        $this->view->setTerminal(true);
        $modelInfo = $this->infoService;
        $this->view->setVariable('users', $modelInfo->getTopScoreUsers(100));

        return $this->view;
    }

    public function plingedprojectsAction()
    {
        $this->view->setTerminal(true);
        $modelInfo = $this->infoService;
        $this->view->setVariable('projects', $modelInfo->getNewActivePlingProduct(100));

        return $this->view;
    }

    public function mostplingedcreatorsAction()
    {
        $this->view->setTerminal(true);
        $modelInfo = $this->infoService;
        $pageLimit = 100;
        $page = (int)$this->getParam('page', 1);
        if ($page <= 0) {
            $page = 1;
        }
        $nopage = (int)$this->getParam('nopage', 0);
        $this->view->setVariable('page', $page);
        $this->view->setVariable('nopage', $nopage);
        $this->view->setVariable('pageLimit', $pageLimit);
        $this->view->setVariable('totalcount', $modelInfo->getMostPlingedCreatorsTotalCnt());
        $this->view->setVariable('users', $modelInfo->getMostPlingedCreators($pageLimit, ($page - 1) * $pageLimit));

        return $this->view;
    }

    public function mostplingedproductsAction()
    {
        $this->view->setTerminal(true);
        $pageLimit = 100;
        $page = (int)$this->getParam('page', 1);
        if ($page <= 0) {
            $page = 1;
        }
        $nopage = (int)$this->getParam('nopage', 0);
        $modelInfo = $this->infoService;
        $this->view->setVariable('page', $page);
        $this->view->setVariable('nopage', $nopage);
        $this->view->setVariable('pageLimit', $pageLimit);
        $this->view->setVariable('totalcount', $modelInfo->getMostPlingedProductsTotalCnt());
        $this->view->setVariable('projects', $modelInfo->getMostPlingedProducts($pageLimit, ($page - 1) * $pageLimit));

        return $this->view;
    }

    public function toplistmembersAction()
    {
        $this->view->setTerminal(true);
        $modelInfo = $this->infoService;
        $pageLimit = 100;
        $page = (int)$this->getParam('page', 1);
        if ($page <= 0) {
            $page = 1;
        }
        $nopage = (int)$this->getParam('nopage', 0);
        $this->view->setVariable('page', $page);
        $this->view->setVariable('nopage', $nopage);
        $this->view->setVariable('pageLimit', $pageLimit);
        $this->view->setVariable('totalcount', 1000);
        $this->view->setVariable('users', $modelInfo->getTopScoreUsers($pageLimit, ($page - 1) * $pageLimit));

        return $this->view;
    }

}
