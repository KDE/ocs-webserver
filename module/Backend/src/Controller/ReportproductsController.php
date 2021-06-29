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
 **/

namespace Backend\Controller;

use Application\Model\Entity\ReportProducts;
use Application\Model\Interfaces\ProjectInterface;
use Application\Model\Interfaces\ReportProductsInterface;
use Application\Model\Repository\ProjectRepository;
use Exception;
use Laminas\View\Model\JsonModel;

/**
 * Class ReportproductsController
 *
 * @package Backend\Controller
 */
class ReportproductsController extends BackendBaseController
{

    private $projectRepository;

    public function __construct(
        ReportProductsInterface $reportProductsRepository,
        ProjectInterface $projectRepository


    ) {
        parent::__construct();

        $this->_model = $reportProductsRepository;
        $this->projectRepository = $projectRepository;
        $this->_modelName = ReportProducts::class;
        $this->_pageTitle = 'Manage Reported Products';
        $this->_defaultSorting = 'report_id asc';
    }

    public function listAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');
        $filterDeleted = (int)$this->getParam('filter_deleted', 0);

        $dataModel = $this->_model;

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $dataModel->getReportsForProjects($startIndex, $pageSize, $sorting, $filterDeleted);
        $jTableResult['TotalRecordCount'] = $dataModel->getTotalCountForReportedProject($filterDeleted);

        return new JsonModel($jTableResult);

    }

    public function deleteAction()
    {
        // here report_id => project_id cos delete per report_id not existing
        $reportId = (int)$this->getParam('report_id', null);
        $this->_model->doDelete($reportId);
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function deletereportsAction()
    {
        $projectId = (int)$this->getParam('p', null);
        $this->_model->doDelete($projectId);
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function nospamAction()
    {
        $projectId = (int)$this->getParam('p', null);
        $this->_model->doDelete($projectId);

        $modelProducts = $this->projectRepository;
        $modelProducts->setSpamChecked($projectId, ProjectRepository::PROJECT_SPAM_CHECKED);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function validatereportAction()
    {
        $reportId = (int)$this->getParam('r', null);

        $dataModel = $this->_model;

        $result = $dataModel->setReportAsValid($reportId);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function deletereportAction()
    {
        $reportId = (int)$this->getParam('r', null);

        $dataModel = $this->_model;

        $result = $dataModel->setReportAsDeleted($reportId);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function validatemisuseAction()
    {

        $projectId = (int)$this->getParam('p', null);
        $dataModel = $this->_model;
        $dataModel->doDelete($projectId);
        $dataModel->saveNewFraud($projectId, $this->ocsUser);
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function statusAction()
    {
        $jTableResult = array();
        try {
            $projectId = (int)$this->getParam('c');

            $model = $this->projectRepository;
            $record = $model->fetchById($projectId);
            $record->status = ($record->status > 40 ? ProjectRepository::PROJECT_FAULTY : ProjectRepository::PROJECT_ACTIVE);
            $model->update(['project_id' => $projectId, 'status' => $record->status]);


            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record;
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));

            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $this->translate('Error while processing data.');
        }

        return new JsonModel($jTableResult);
    }

} 