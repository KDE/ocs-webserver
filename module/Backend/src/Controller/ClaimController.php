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

namespace Backend\Controller;

use Application\Model\Entity\Project;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Service\ProjectService;
use Exception;
use Laminas\Db\Sql\Expression;
use Laminas\View\Model\JsonModel;

class ClaimController extends BackendBaseController
{

    const DATA_ID_NAME = 'member_id';
    private $projectRepository;
    private $projectService;

    public function __construct(
        ProjectRepository $projectRepository,
        ProjectService $projectService
    ) {
        parent::__construct();
        $this->projectRepository = $projectRepository;
        $this->_model = $projectRepository;
        $this->projectService = $projectService;
        $this->_modelName = Project::class;
        $this->_pageTitle = 'Administrate Claimed Products';
    }

    public function listAction()
    {
        $filter['title'] = $this->getParam('filter_title');
        $filter['project_id'] = $this->getParam('filter_project_id');
        $filter['member_id'] = $this->getParam('filter_member_id');
        $filter['claimable'] = 1;
        $jTableResult = $this->prepareListActionJTableResult(null, $filter);

        return new JsonModel($jTableResult);
    }

    public function removeclaimAction()
    {
        $jTableResult = array();
        try {
            $projectId = (int)$this->getParam('project_id');
            $this->projectRepository->update(
                [
                    'project_id' => $projectId,
                    'claimed_by_member' => new Expression('NULL'),
                ]
            );
            $record = $this->projectRepository->findById($projectId);
            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record->toArray();
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function toggleclaimAction()
    {
        $jTableResult = array();
        try {
            $projectId = (int)$this->getParam('project_id');
            $record = $this->projectRepository->findById($projectId);
            $record->claimable = ($record->claimable ? 0 : 1);
            $this->projectRepository->update(['project_id' => $projectId, 'claimable' => $record->claimable]);

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record;
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function transferAction()
    {
        $jTableResult = array();
        try {
            $projectId = (int)$this->getParam('project_id');
            $this->projectService->transferClaimToMember($projectId);
            $record = $this->_model->findById($projectId);
            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record->toArray();
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

}