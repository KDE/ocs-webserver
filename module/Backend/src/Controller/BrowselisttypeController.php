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

use Application\Model\Entity\ReportProducts;
use Application\Model\Interfaces\BrowseListTypesInterface;
use Exception;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class BrowselisttypeController extends BackendBaseController
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";

    /** @var ReportProducts */
    protected $_model;

    protected $_modelName = 'Default_Model_DbTable_BrowseListType';

    private $browseListTypesRepository;

    public function __construct(
        BrowseListTypesInterface $browseListTypesRepository

    ) {
        parent::__construct();
        $this->browseListTypesRepository = $browseListTypesRepository;
        $this->_model = $browseListTypesRepository;
    }

    public function indexAction()
    {
        $this->layout()->pageTitle = 'Manage Browse-List-Types';

        return new ViewModel([]);
    }

    public function listAction()
    {

        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');


        $result = $this->_model->fetchAllRows(null, $sorting, $pageSize, $startIndex);
        $resultCnt = $this->_model->fetchAllRowsCount();

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $result->toArray();
        $jTableResult['TotalRecordCount'] = $resultCnt;

        return new JsonModel($jTableResult);

    }

    public function createAction()
    {
        $jTableResult = array();
        try {
            $params = $this->params()->fromPost();
            $id = $this->_model->insert($params);
            $newRow = $this->_model->fetchById($id);

            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $newRow;
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

            $values = $this->params()->fromPost();
            if (!isset($values['is_active'])) {
                $values['is_active'] = 0;
            }
            //$record = $this->_model->save($values);
            //$record = $this->_model->save($this->getAllParams());
            $this->_model->update($values);

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $values;
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));

            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function deleteAction()
    {
        $browse_list_type_id = (int)$this->params()->fromPost('browse_list_type_id', null);

        $this->_model->setIsDeleted($browse_list_type_id);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function allbrowselisttypesAction()
    {

        $result = true;
        $tagmodel = $this->_model;

        try {
            $resultRows = $tagmodel->fetchAllRows();
            $resultForSelect = array();
            $resultForSelect[] = array('DisplayText' => '', 'Value' => null);
            foreach ($resultRows as $row) {
                $resultForSelect[] = array(
                    'DisplayText' => $row['name'] . '[' . $row['browse_list_type_id'] . ']',
                    'Value'       => $row['browse_list_type_id'],
                );
            }

        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $result = false;
            $records = array();
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;
        $jTableResult['Options'] = $resultForSelect;

        return new JsonModel($jTableResult);
    }

}