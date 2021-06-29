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

use Application\Model\Interfaces\ProjectInterface;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Service\ActivityLogService;
use Application\Model\Service\Interfaces\ProjectServiceInterface;
use Application\Model\Service\ProjectModerationService;
use Application\Model\Service\TagService;
use Exception;
use Laminas\Db\Metadata\Source\Factory;
use Laminas\Db\Sql\Where;
use Laminas\Filter\StringTrim;
use Laminas\Filter\ToInt;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class ProjectController extends BackendBaseController
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    const DATA_ID_NAME = 'project_id';

    const PARAM_FEATURED = 'featured';
    const PARAM_APPROVED = 'ghns_excluded';
    const PARAM_PLING_EXCLUDED = 'pling_excluded';
    const PARAM_PRODUCT_DANGEROUS = 'product_dangerous';
    const PARAM_PRODUCT_DEPRECATED = 'product_deprecated';

    const PARAM_MSG = 'msg';
    /** @var ProjectRepository */
    protected $_model;

    protected $_modelName = 'Default_Model_Project';
    protected $_pageTitle = 'Manage Products';

    private $projectRepository;
    private $projectService;
    private $tagService;
    private $projectModerationService;

    public function __construct(
        ProjectInterface $projectRepository,
        ProjectServiceInterface $projectService,
        TagService $tagService,
        ProjectModerationService $projectModerationService
    ) {
        parent::__construct();
        $this->projectRepository = $projectRepository;
        $this->projectService = $projectService;
        $this->tagService = $tagService;
        $this->projectModerationService = $projectModerationService;
    }

    public function indexAction()
    {
        $this->layout()->pageTitle = $this->_pageTitle;

        return new ViewModel([]);
    }

    public function listAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');
        if ($pageSize == null) {
            $pageSize = 10;
        }
        $filter['title'] = $this->params()->fromPost('filter_title');
        $filter['project_id'] = $this->params()->fromPost('filter_project_id');
        $filter['member_id'] = $this->params()->fromPost('filter_member_id');
        $filter['claimable'] = $this->params()->fromPost('filter_claimable');
        $filter['type_id'][1] = $this->params()->fromPost('filter_project_page');
        $filter['type_id'][2] = $this->params()->fromPost('filter_personal_page');
        $filter['type_id'][3] = $this->params()->fromPost('filter_updates');

        $metadata = Factory::createSourceFromAdapter($this->db);
        $table = $metadata->getTable('project');
        $columns = $table->getColumns();

        $whereObj = new Where();
        foreach ($filter as $key => $value) {
            if (is_array($value)) {
                $whereObj->in($key, $value);
                continue;
            }
            if (false === empty($value)) {
                $data_type = '';
                foreach ($columns as $column) {

                    if ($column->getName() == $key) {
                        $data_type = $column->getDataType();
                        break;
                    }
                }
                if (($data_type == 'varchar') or ($data_type == 'text')) {
                    $whereObj->like($key, '%' . $value . '%');
                } else {
                    $whereObj->equalTo($key, $value);
                }
            }
        }
        $reports = $this->projectRepository->fetchAllRows($whereObj, $sorting, $pageSize, $startIndex);
        $reportsCount = $this->projectRepository->fetchAllRowsCount($whereObj);


        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $reports->toArray();
        $jTableResult['TotalRecordCount'] = $reportsCount;

        return new JsonModel($jTableResult);
    }

    public function createAction()
    {
        $jTableResult = array();
        try {
            //$newRow = $this->_model->createRow($this->getAllParams());
            //$result = $newRow->save();
            $data = $this->params()->fromPost();
            $project_id = $this->projectRepository->insert($data);
            $project = $this->projectRepository->fetchById($project_id);

            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $project->getArrayCopy();
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function updateAction()
    {
        $jTableResult = array();
        try {
            $data = $this->params()->fromPost();
            $filterStringTrim = new StringTrim();
            foreach ($data as &$value) {
                $value = $filterStringTrim->filter($value);
            }
            $filterToInt = new ToInt();
            $arrayIntName = [
                'project_id',
                'member_id',
                'project_category_id',
                'status',
                'pid',
                'type_id',
                'creator_id',
                'validated',
                'featured',
                'amount',
                'spam_checked',
                'claimable',
                'claimed_by_member',
            ];

            foreach ($data as $key => &$value) {
                if (in_array($key, $arrayIntName)) {
                    $value = $filterToInt->filter($value);
                }

                //    if($key=='project_id'||$key=='member_id'||'')
                //    {
                //     $value = $filterToInt->filter($value);
                //    }
            }

            /*
            //TODO review this.
            $filterInput = new Zend_Filter_Input(array(
                '*'                   => 'StringTrim',
                'project_id'          => 'digits',
                'member_id'           => 'digits',
                'project_category_id' => 'digits',
                'status'              => 'digits',
                'pid'                 => 'digits',
                'type_id'             => 'digits',
                'creator_id'          => 'digits',
                'validated'           => 'digits',
                'featured'            => 'digits',
                'amount'              => 'digits',
                'spam_checked'        => 'digits',
                'claimable'           => 'digits',
                'claimed_by_member'   => 'digits',
            ), array('*' => array()), $this->getAllParams());
            */
            $this->projectRepository->insertOrUpdate($data);

            $id = $data['project_id'];
            $record = $this->projectRepository->fetchById($id);
            //$record = $this->_model->save($filterInput->getEscaped());

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record;
        } catch (Exception $e) {
            //$this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));            
            error_log($e->__toString());
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function deleteAction()
    {
        $identity = $this->ocsUser;
        $dataId = (int)$this->params()->fromQuery(self::DATA_ID_NAME, null);
        if (!$dataId) {
            $dataId = (int)$this->params()->fromPost(self::DATA_ID_NAME, null);
        }

        $this->projectService->setDeleted($identity->member_id, $dataId);
        $product = $this->projectRepository->fetchById($dataId);

        ActivityLogService::logActivity($dataId, $dataId, $identity->member_id, ActivityLogService::BACKEND_PROJECT_DELETE, $product);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    /**
     * @return JsonModel
     * @deprecated
     */
    public function togglestatusAction()
    {
        $jTableResult = array();
        try {
            $projectId = (int)$this->params()->fromPost(self::DATA_ID_NAME, null);
            $record = $this->projectRepository->fetchById($projectId);
            $status = ($record->status ? 0 : 10);
            $record->status = $status;
            $this->projectRepository->update(['project_id' => $projectId, 'status' => $status]);

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record->getArrayCopy();
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function dospamcheckedAction()
    {
        $id = (int)$this->params()->fromQuery(self::DATA_ID_NAME);
        $product = $this->projectRepository->fetchById($id);
        if (empty($product)) {
            $jsonResult = array();
            $jsonResult['Result'] = self::RESULT_ERROR;

            return new JsonModel($jsonResult);
        }

        $checked = (int)$this->params()->fromQuery('checked');
        $this->projectRepository->update(
            array(
                'spam_checked' => $checked,
                'changed_at'   => $product->changed_at,
                'project_id'   => $id,
            )
        );

        $jsonResult = array();
        $jsonResult['Result'] = self::RESULT_OK;
        $jsonResult['spam_checked'] = $checked;

        return new JsonModel($jsonResult);
    }

    public function dofeatureAction()
    {
        $projectId = (int)$this->params()->fromQuery(self::DATA_ID_NAME, null);
        $product = $this->projectRepository->fetchById($projectId);

        $featured = (int)$this->params()->fromQuery(self::PARAM_FEATURED, null);

        $this->projectRepository->update(
            array(
                'featured'   => $featured,
                'changed_at' => $product->changed_at,
                'project_id' => $projectId,
            )
        );


        $identity = $this->ocsUser;
        ActivityLogService::logActivity(
            $projectId, $projectId, $identity->member_id, ActivityLogService::BACKEND_PROJECT_FEATURE, $product
        );

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function doghnsexcludeAction()
    {
        $projectId = (int)$this->params()->fromPost(self::DATA_ID_NAME, null);
        $product = $this->projectRepository->fetchById($projectId);

        $ghns_excluded = (int)$this->params()->fromPost(self::PARAM_APPROVED, null);

        $tableTags = $this->tagService;
        $tableTags->saveGhnsExcludedTagForProject($projectId, $ghns_excluded);


        /** ronald 20180611 now as tag
         * $sql = "UPDATE project SET ghns_excluded = :ghns_excluded WHERE project_id = :project_id";
         * $this->_model->getAdapter()->query($sql, array('ghns_excluded' => $ghns_excluded, 'project_id' => $projectId));
         */

        $identity = $this->ocsUser;
        ActivityLogService::logActivity($projectId, $projectId, $identity->member_id, ActivityLogService::BACKEND_PROJECT_GHNS_EXCLUDED, $product);

        $moderationModel = $this->projectModerationService;

        $note = $this->params()->fromPost(self::PARAM_MSG, null);

        $moderationModel->createModeration(
            $projectId, ProjectModerationService::M_TYPE_GET_HOT_NEW_STUFF_EXCLUDED, $ghns_excluded, $identity->member_id, $note
        );

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function doexcludeAction()
    {
        $projectId = (int)$this->params()->fromQuery(self::DATA_ID_NAME, null);
        $product = $this->projectRepository->fetchById($projectId);

        $exclude = (int)$this->params()->fromQuery(self::PARAM_PLING_EXCLUDED, null);

        $this->projectRepository->update(array('pling_excluded' => $exclude, 'project_id' => $projectId));
        $identity = $this->ocsUser;
        ActivityLogService::logActivity(
            $projectId, $projectId, $identity->member_id, ActivityLogService::BACKEND_PROJECT_PLING_EXCLUDED, $product
        );

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function dodeprecatedAction()
    {
        $projectId = (int)$this->params()->fromQuery(self::DATA_ID_NAME, null);
        $product = $this->projectRepository->fetchById($projectId);

        $deprecated = (int)$this->params()->fromQuery(self::PARAM_PRODUCT_DEPRECATED, null);

        $tableTags = $this->tagService;
        $tableTags->saveDeprecatedModeratorTagForProject($projectId, $deprecated);


        $identity = $this->ocsUser;
        ActivityLogService::logActivity(
            $projectId, $projectId, $identity->member_id, ActivityLogService::BACKEND_PROJECT_DEPRECATED, $product
        );


        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function dodangerousAction()
    {
        $projectId = (int)$this->params()->fromQuery(self::DATA_ID_NAME, null);
        $product = $this->projectRepository->fetchById($projectId);
        $dangerous = (int)$this->params()->fromQuery(self::PARAM_PRODUCT_DANGEROUS, null);

        $tableTags = $this->tagService;
        $tableTags->saveDangerosuTagForProject($projectId, $dangerous);


        $identity = $this->ocsUser;
        ActivityLogService::logActivity(
            $projectId, $projectId, $identity->member_id, ActivityLogService::BACKEND_PROJECT_DANGEROUS, $product
        );


        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function changecatAction()
    {
        $projectId = (int)$this->params()->fromQuery(self::DATA_ID_NAME, null);
        $catId = (int)$this->params()->fromQuery('project_category_id', null);

        $product = $this->projectRepository->fetchById($projectId);
        $this->projectRepository->update(
            array(
                'project_category_id' => $catId,
                'changed_at'          => $product->changed_at,
                'project_id'          => $projectId,
            )
        );

        $identity = $this->ocsUser;
        ActivityLogService::logActivity(
            $projectId, $projectId, $identity->member_id, ActivityLogService::BACKEND_PROJECT_CAT_CHANGE, $product
        );

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

}