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

use Application\Model\Entity\Comments;
use Application\Model\Interfaces\CommentsInterface;
use Exception;
use Laminas\Db\Sql\Where;
use Laminas\View\Model\JsonModel;

class CommentsController extends BackendBaseController
{

    const IDENTIFIER = 'comment_id';

    public function __construct(
        CommentsInterface $commentsRepository
    ) {
        parent::__construct();

        $this->_model = $commentsRepository;
        $this->_modelName = Comments::class;
        $this->_pageTitle = 'Manage Comments';
        $this->_defaultSorting = 'comment_id desc';
    }

    public function listAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');
        if ($sorting == null) {
            $sorting = $this->_defaultSorting;
        }

        $filter['comment_target_id'] = trim($this->params()->fromPost('filter_comment_target_id'));
        $filter['comment_member_id'] = trim($this->params()->fromPost('filter_comment_member_id'));
        $filter['comment_type'] = trim($this->params()->fromPost('filter_comment_type'));

        $sqlWhere = new Where();
        foreach ($filter as $key => $item) {
            if ($item == null) continue;
            $sqlWhere->equalTo($key, (int)$item);
        }

        $result = $this->_model->fetchAllRows($sqlWhere, $sorting, $pageSize, $startIndex);
        $resultCnt = $this->getAdapter()->fetchAllRowsCount($sqlWhere);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $result->toArray();
        $jTableResult['TotalRecordCount'] = $resultCnt;

        return new JsonModel($jTableResult);
    }

    public function updateAction()
    {
        $jTableResult = array();
        try {

            $comment_id = (int)$this->getParam(self::IDENTIFIER);
            $comment_text = $this->getParam('comment_text');

            $this->_model->update(['comment_text' => $comment_text, 'comment_id' => $comment_id]);

            $values = $this->_model->fetchById($comment_id);
            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $values;
        } catch (Exception $e) {
            // $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));            
            error_log($e->__toString());
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function deleteAction()
    {
        $reportId = (int)$this->getParam(self::IDENTIFIER, null);

        $this->_model->update(['comment_active' => 0, 'comment_id' => $reportId]);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }


    // function utf8_encode_recursive($array)
    // {
    //     $result = array();
    //     foreach ($array as $key => $value) {
    //         if (is_array($value)) {
    //             $result[$key] = $this->utf8_encode_recursive($value);
    //         } else {
    //             if (is_string($value)) {
    //                 $result[$key] = utf8_encode($value);
    //             } else {
    //                 $result[$key] = $value;
    //             }
    //         }
    //     }

    //     return $result;
    // }

    public function statusAction()
    {
        $jTableResult = array();
        try {
            $commentId = (int)$this->getParam('c');

            $model = $this->_model;
            $record = $model->fetchById($commentId);
            $active = 1;
            if ($record->comment_active == 0) {
                $active = 1;
            } else {
                $active = 0;
            }
            $record->comment_active = $active;
            // $record->save();
            $model->update(['comment_active' => $active, 'comment_id' => $commentId]);

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

} 