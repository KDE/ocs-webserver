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

use Application\Controller\BaseController;
use Exception;
use Laminas\Db\Metadata\Source\Factory;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Where;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class BackendBaseController extends BaseController
{
    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";

    protected $_model;
    protected $_pageTitle;
    protected $_modelName;
    protected $_defaultSorting = 'created_at desc';

    public function indexAction()
    {
        $viewModel = new ViewModel();
        $this->layout()->pageTitle = $this->_pageTitle;

        return $viewModel;
    }

    public function listAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');
        if ($sorting == null) {
            $sorting = $this->_defaultSorting;
        }

        $result = $this->getAdapter()->fetchAllRows(null, $sorting, $pageSize, $startIndex);
        $resultCnt = $this->getAdapter()->fetchAllRowsCount();

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $result->toArray();
        $jTableResult['TotalRecordCount'] = $resultCnt;

        return new JsonModel($jTableResult);
    }

    protected function getAdapter()
    {
        return $this->_model;
    }

    public function createAction()
    {
        $jTableResult = array();
        try {

            $data = $this->params()->fromPost();

            $id = $this->_model->insert($data);
            if ($id == 0) {
                // no sequence used 
                $newRow = $data;
            } else {
                $newRow = $this->_model->fetchById($id);
            }
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $newRow;
        } catch (Exception $e) {
            //$this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));            
            error_log($e->__toString());
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
            // $entity = new $this->_modelName();
            // $entity->exchangeArray($this->params()->fromPost());
            // $values = $entity->getArrayCopy();

            foreach ($values as $key => $value) {
                if ($value == '') {
                    $values[$key] = new Expression('NULL');
                }
            }

            $this->_model->update($values);
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

    protected function prepareListActionJTableResult($select = null, $filter = null)
    {
        $offset = (int)$this->getParam('jtStartIndex');
        $limit = (int)$this->getParam('jtPageSize');
        $order = $this->getParam('jtSorting');
        if (!$select) {
            $select = $this->_model->select();
            $select->from(array('m' => $this->_model->getName()));
        }
        $where = $this->prepareWhereFilter($this->_model->getName(), $filter);
        if ($where) {
            $select->where($where);
        }
        if ($order) {
            $select->order($order);
        }
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }
        $reports = $this->_model->fetchAllSelect($select);

        $countSelect = $select->reset('columns')->reset('joins')->reset('limit')->reset('offset')->reset('order')
                              ->columns(array('countAll' => new Expression('count(*)')));
        $joins = $countSelect->joins->getJoins();
        foreach ($joins as $key => $join) {
            //re add join without cols
            $type = $join["type"];
            $countSelect->join($join["name"], $join["on"], [], $type);
        }
        $reportsAll = $this->_model->fetchAllSelect($countSelect);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $reports->toArray();
        $jTableResult['TotalRecordCount'] = $reportsAll->current()->countAll;

        return $jTableResult;
    }

    protected function getParam($string, $default = null)
    {
        $val = $this->params()->fromQuery($string, $default);
        if (null == $val) {
            $val = $this->params()->fromRoute($string, $default);
        }
        if (null == $val) {
            $val = $this->params()->fromPost($string, $default);
        }

        return $val;
    }

    /**
     * @param String $tablename
     * @param array  $filter
     *
     * @return Where
     * @throws Exception
     */
    protected function prepareWhereFilter($tablename, array $filter)
    {
        if ($filter && count($filter) > 0) {

            $metadata = Factory::createSourceFromAdapter($this->db);
            $table = $metadata->getTable($tablename);
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

            return $whereObj;
        } else {
            return null;
        }
    }
}