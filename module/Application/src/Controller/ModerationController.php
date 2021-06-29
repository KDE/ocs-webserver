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
 *
 * Created: 31.05.2017
 */

namespace Application\Controller;

use Application\Model\Interfaces\CommentsInterface;
use Application\Model\Repository\CommentsRepository;
use Application\Model\Repository\ProjectCloneRepository;
use Application\Model\Service\Interfaces\ProjectModerationServiceInterface;
use Application\Model\Service\Interfaces\TagServiceInterface;
use Application\Model\Service\ProjectCloneService;
use Application\Model\Service\ProjectModerationService;
use Application\Model\Service\Util;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

/**
 * Class ModerationController
 *
 * @package Application\Controller
 */
class ModerationController extends BaseController
{

    const DATA_ID_NAME = 'project_id';
    const DATA_NOTE = 'note';
    const DATA_VALUE = 'value';
    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    protected $_model;
    protected $_modelName = 'Default_Model_ProjectModeration';
    protected $_pageTitle = 'Moderate GHNS excluded';
    private $commentsRepository;
    private $projectModerationService;
    private $tagService;
    private $projectCloneService;
    private $projectCloneRepository;

    public function __construct(
        CommentsInterface $commentsRepository,
        ProjectModerationServiceInterface $projectModerationService,
        TagServiceInterface $tagService,
        ProjectCloneService $projectCloneService,
        ProjectCloneRepository $projectCloneRepository

    ) {
        parent::__construct();
        $this->commentsRepository = $commentsRepository;
        $this->projectModerationService = $projectModerationService;
        $this->tagService = $tagService;
        $this->projectCloneService = $projectCloneService;
        $this->projectCloneRepository = $projectCloneRepository;
    }

    public function indexAction()
    {
        $viewModel = $this->initFlatUiViewModel();
        $mlist = $this->projectModerationService->getMembers();
        $viewModel->setVariable('mlist', $mlist);

        return $viewModel;
    }

    private function initFlatUiViewModel()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $this->layout()->noheader = true;
        $viewModel = new ViewModel();
        $viewModel->setVariable('isAdmin', $this->isAdmin());

        return $viewModel;
    }

    public function listAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');
        if ($sorting == null) {
            $sorting = 'created_at desc';
        }
        $filter['member_id'] = $this->params()->fromPost('filter_member_id');

        $reports = $this->projectModerationService->getList(
            $filter['member_id'], $sorting, (int)$pageSize, $startIndex
        );

        $totalRecordCount = $this->projectModerationService->getTotalCount($filter);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $reports;
        $jTableResult['TotalRecordCount'] = $totalRecordCount;

        return new JsonModel($jTableResult);
    }

    public function updateAction()
    {
        $dataId = (int)$this->params()->fromPost(self::DATA_ID_NAME, null);
        $note = $this->params()->fromPost(self::DATA_NOTE, null);
        $value = $this->params()->fromPost(self::DATA_VALUE, null);

        if ($value == null) {
            $value = 0;
        }
        if ($value == 0) {
            $this->tagService->saveGhnsExcludedTagForProject($dataId, 0);
        }


        $identity = $this->ocsUser;
        $mod = $this->projectModerationService;
        $mod->createModeration(
            $dataId, ProjectModerationService::M_TYPE_GET_HOT_NEW_STUFF_EXCLUDED, $value, $identity->member_id, $note
        );
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);

    }

    public function productmoderationAction()
    {
        return $this->initFlatUiViewModel();
    }

    public function listmoderationAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = (int)$this->params()->fromQuery('jtSorting');
        if ($sorting == null) {
            $sorting = 'comment_created_at desc';
        }

        $comments = $this->commentsRepository->fetchCommentsWithType(
            CommentsRepository::COMMENT_TYPE_MODERATOR, $sorting, (int)$pageSize, $startIndex
        );


        foreach ($comments as &$value) {
            $value['comment_created_at'] = Util::printDateSince($value['comment_created_at']);
            $value['profile_image_url'] = Util::image(
                $value['profile_image_url'], array('width' => '200', 'height' => '200', 'crop' => 2)
            );
            $value['image_small'] = Util::image(
                $value['image_small'], array('width' => '100', 'height' => '100', 'crop' => 2)
            );
        }
        $totalRecordCount = $this->commentsRepository->fetchCommentsWithTypeCount(
            CommentsRepository::COMMENT_TYPE_MODERATOR
        );

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $comments;
        $jTableResult['TotalRecordCount'] = $totalRecordCount;

        return new JsonModel($jTableResult);
    }

    public function indexcreditsAction()
    {
        $pageLimit = 10;
        $viewModel = $this->initFlatUiViewModel();
        $credits = $this->projectCloneService->fetchCredits();
        $paginator = new Paginator(new ArrayAdapter($credits));
        $paginator->setItemCountPerPage($pageLimit);
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page', 1));
        $viewModel->setVariable('paginator', $paginator);
        $viewModel->setVariable('page', $this->params()->fromRoute('page', 1));

        return $viewModel;
    }

    public function deletecreditAction()
    {
        $id = (int)$this->params()->fromQuery('id');
        $this->projectCloneRepository->setDelete($id);

        return new JsonModel(
            array(
                'status'  => 'ok',
                'message' => 'deleted',
                'data'    => array(),
            )
        );
    }

    public function validcreditAction()
    {
        $id = (int)$this->params()->fromQuery('id');
        $this->projectCloneRepository->setValid($id);

        return new JsonModel(
            array(
                'status'  => 'ok',
                'message' => 'deleted',
                'data'    => array(),
            )
        );

    }

    public function editcreditAction()
    {

        $id = (int)$this->params()->fromQuery('id');
        $text = $this->params()->fromQuery('t');
        $project_id_parent = (int)$this->params()->fromQuery('p'); // original
        $link = $this->params()->fromQuery('l');

        $arr = array(
            'text'             => $text,
            'project_id_parent'       => $project_id_parent,
            'project_clone_id' => $id,
        );
        if ($link) {
            $arr['external_link'] = $link;
        }

        $this->projectCloneRepository->update($arr);

        return new JsonModel(
            array(
                'status'  => 'ok',
                'message' => 'updated',
                'data'    => array(),
            )
        );

    }

    public function indexmodsAction()
    {
        $pageLimit = 10;
        $viewModel = $this->initFlatUiViewModel();
        $credits = $this->projectCloneService->fetchMods();
        $paginator = new Paginator(new ArrayAdapter($credits));
        $paginator->setItemCountPerPage($pageLimit);
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page', 1));
        $viewModel->setVariable('paginator', $paginator);
        $viewModel->setVariable('page', $this->params()->fromRoute('page', 1));

        return $viewModel;

    }

}