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

use Application\Model\Entity\ReportComments;
use Application\Model\Interfaces\CommentsInterface;
use Application\Model\Interfaces\ReportCommentsInterface;
use Laminas\View\Model\JsonModel;

class ReportcommentsController extends BackendBaseController
{
    private $commentsRepository;

    public function __construct(
        ReportCommentsInterface $reportCommentsRepository,
        CommentsInterface $commentsRepository
    ) {
        parent::__construct();

        $this->_model = $reportCommentsRepository;
        $this->commentsRepository = $commentsRepository;
        $this->_modelName = ReportComments::class;
        $this->_pageTitle = 'Manage Reported Comments';
        $this->_defaultSorting = 'report_id asc';
    }

    public function deleteAction()
    {
        $reportId = (int)$this->getParam('project_report_id', null);

        $this->_model->setDelete($reportId);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function listAction()
    {
        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');
        $filter_project_id = (int)$this->getParam('filter_project_id', null);
        if ($pageSize == 0) {
            $pageSize = 10;
        }
        $dataModel = $this->_model;

        $where = false == empty($filter_project_id) ? "project.project_id = {$filter_project_id}" : null;
        $records = $dataModel->getReportsForComments($startIndex, $pageSize, $sorting, $where);

        $cnt = $dataModel->getTotalCountForReportedComments($where);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $records;
        $jTableResult['TotalRecordCount'] = $cnt;

        return new JsonModel($jTableResult);
    }

    public function statusAction()
    {
        $jTableResult = array();
        try {

            $commentId = (int)$this->getParam('c');

            $model = $this->commentsRepository;
            $record = $model->fetchById($commentId);
            $record->comment_active = ($record->comment_active ? 0 : 1);
            $model->update(['comment_id' => $commentId, 'comment_active' => $record->comment_active]);

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record;
        } catch (\Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

} 