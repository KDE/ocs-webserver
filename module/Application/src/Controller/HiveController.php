<?php /** @noinspection PhpUnused */

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

use Application\Model\Service\MemberService;
use Application\Model\Service\ProjectService;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Request;

/**
 * Class HiveController
 *
 * @package Application\Controller
 */
class HiveController extends DomainSwitch
{
    protected $projectService;
    protected $memberService;
    protected $db;
    protected $config;
    protected $logger;

    public function __construct(
        AdapterInterface $db,
        array $config,
        Request $request,
        ProjectService $projectService,
        MemberService $memberService
    ) {
        parent::__construct($db, $config, $request);
        parent::init();

        $this->logger = $GLOBALS['ocs_log'];
        $this->projectService = $projectService;
        $this->memberService = $memberService;
    }

    public function showAction()
    {
        $this->view->setTerminal(true);

        $contentId = (int)$this->getParam('content', null);
        $projectData = $this->projectService->fetchActiveBySourcePk($contentId);

        if (empty($projectData)) {
            return $this->getResponse()->setStatusCode(404);
        }

        return $this->redirect()->toUrl('/p/' . $projectData[0]['project_id']);
    }

    public function usersearchAction()
    {
        $this->view->setTerminal(true);

        $username = $this->getParam('username') ? preg_replace(
            '/[^-a-zA-Z0-9_]/', '', $this->getParam('username')
        ) : null;

        $userData = $this->memberService->fetchActiveHiveUserByUsername($username);

        if (!$userData) {
            return $this->getResponse()->setStatusCode(404);
        }

        return $this->redirect()->toUrl('/u/' . $username);
    }

}