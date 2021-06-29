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

use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Interfaces\ProjectCategoryInterface;
use Application\Model\Service\TagService;
use Exception;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class CategoriesController extends BackendBaseController
{
    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";

    /** @var ProjectCategoryRepository */
    protected $_model;

    protected $_authMember;

    protected $_modelName = 'Default_Model_DbTable_ProjectCategory';

    private $projectCategoryRepository;
    private $tagService;

    public function __construct(
        ProjectCategoryInterface $projectCategoryRepository,
        TagService $tagService

    ) {
        parent::__construct();
        $this->projectCategoryRepository = $projectCategoryRepository;
        $this->tagService = $tagService;
        $this->_model = $projectCategoryRepository;
    }

    public function indexAction()
    {
        $viewModel = new ViewModel();
        $this->layout()->pageTitle = 'Manage Product Categories';
        $viewModel->setVariable('pageTitle', 'Manage Product Categories');
        $viewModel->setVariable('author', $this->ocsUser->username);

        return $viewModel;
    }

    public function createAction()
    {
        $jTableResult = array();
        try {

            $params = $this->params()->fromPost();

            if (empty($params['rgt'])) {
                $root = $this->_model->fetchRoot();
                $params['rgt'] = $root->rgt - 1;
            }

            $resultRow = $this->_model->addNewElement($params);

            if (false === empty($params['parent'])) {
                $this->_model->moveToParent($resultRow['project_category_id'], (int)$params['parent'], 'bottom');
                $resultRow = $this->_model->fetchElement($resultRow['project_category_id']);
            }

            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $resultRow;
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

            $allparams = $this->params()->fromPost();
            $project_category_id = (int)$this->params()->fromPost('project_category_id', null);
            $parent = (int)$this->params()->fromPost('parent', null);
            $this->_model->moveToParent($project_category_id, $parent);

            //$record = $this->_model->save($this->getAllParams());
            unset($allparams['parent']);
            $this->_model->update($allparams);
            $record = $this->_model->fetchById($project_category_id);

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record;
        } catch (Exception $e) {

            error_log($e->__toString());
            //$this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));

            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function deleteAction()
    {
        $identifier = (int)$this->params()->fromPost('project_category_id', null);

        $this->_model->setCategoryDeleted($identifier);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);

    }

    public function listAction()
    {

        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');

        $filter_deleted = (int)$this->params()->fromPost('filter_deleted', 1);

        $records = $this->_model->fetchTreeWithParentId($filter_deleted, null);

        $pagination = new Paginator(new ArrayAdapter($records));
        $pagination->setItemCountPerPage($pageSize);
        $pagination->setCurrentPageNumber(($startIndex / $pageSize) + 1);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = (array)$pagination->getCurrentItems();
        $jTableResult['TotalRecordCount'] = count($records);

        return new JsonModel($jTableResult);
    }

    public function moveelementAction()
    {

        $params = $this->params()->fromPost();
        $newPosition = $params['record']['lft'];

        switch ($params['direction']) {
            case 'up':
                $sibling = $this->_model->findPreviousSibling($params['record']);
                if (null == $sibling) {
                    $newPosition = $params['record']['lft'];
                } else {
                    $newPosition = (int)$sibling['lft'];
                }
                break;
            case 'down':
                $sibling = $this->_model->findNextSibling($params['record']);
                if (null == $sibling) {
                    $newPosition = $params['record']['lft'];
                } else {
                    $newPosition = (int)$sibling['rgt'] + 1;
                }
                break;
            default:
        }


        $jTableResult = array();
        if (count($sibling) == 0) {
            $jTableResult['Result'] = self::RESULT_ERROR;

            return new JsonModel($jTableResult);

        }

        $element = $this->_model->fetchAllRows('lft = ' . $params['record']['lft'])->current();

        $result = $this->_model->moveTo($element, $newPosition);

        $jTableResult['Result'] = $result == true ? self::RESULT_OK : self::RESULT_ERROR;
        $jTableResult['Record'] = $element;

        return new JsonModel($jTableResult);

    }

    public function dragdropAction()
    {
        $params = $this->params()->fromPost();

        if ($params['data']['lft'] <= $params['newPosition'] and $params['data']['rgt'] >= $params['newPosition']) {
            $result = false;
        } else {
            $result = $this->_model->moveTo($params['data'], $params['newPosition']);
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;

        return new JsonModel($jTableResult);

    }

    public function treeAction()
    {
        $result = true;
        $cat_id = (int)$this->params()->fromQuery('c');
        try {
            $records = $this->projectCategoryRepository->fetchTreeForJTableStores($cat_id);
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $result = false;
            $records = array();
        }
        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;
        $jTableResult['Options'] = $records;

        return new JsonModel($jTableResult);
    }

    /**
     *  never used
     *
     * public function createaboutAction()
     * {
     * $cat_id = (int)$this->getParam('c');
     * $config = Zend_Registry::get('config');
     * $static_config = $config->settings->static;
     * $include_path = $static_config->include_path . 'category_about/';
     * try {
     * if (touch($include_path . '/' . $cat_id . '.phtml')) {
     * $result = true;
     * } else {
     * $result = false;
     * }
     * } catch (Exception $e) {
     * $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
     * $result = false;
     * }
     *
     * $jTableResult = array();
     * $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;
     *
     * $this->_helper->json($jTableResult);
     * }
     */
    public function fetchtagratinggroupsAction()
    {
        $result = true;

        $tagmodel = $this->tagService;
        try {
            $resultRows = $tagmodel->getAllTagGroupsForStoreFilter();
            $resultForSelect = array();
            $resultForSelect[] = array('DisplayText' => '', 'Value' => null);
            foreach ($resultRows as $row) {
                $resultForSelect[] = array('DisplayText' => $row['group_name'], 'Value' => $row['group_id']);
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

    public function readaboutAction()
    {
        $cat_id = (int)$this->params()->fromPost('c');
        $config = $this->ocsConfig;
        $static_config = $config->settings->static;
        $include_path = $static_config->include_path . '/category_about/';

        $filecontent = '';
        $result = true;

        try {
            if (file_exists($include_path . '/' . $cat_id . '.phtml')) {
                $filecontent = file_get_contents($include_path . '/' . $cat_id . '.phtml');
            }
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $result = false;
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;
        $jTableResult['c'] = $cat_id;
        $jTableResult['CatAbout'] = $filecontent;

        return new JsonModel($jTableResult);

    }

    public function saveaboutAction()
    {
        $cat_id = (int)$this->params()->fromPost('c');
        $cat_about = $this->params()->fromPost('ca');

        $config = $this->ocsConfig;
        $static_config = $config->settings->static;
        $include_path = $static_config->include_path . '/category_about/';

        try {
            file_put_contents($include_path . '/' . $cat_id . '.phtml', $cat_about);
            $result = true;
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $result = false;
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;

        return new JsonModel($jTableResult);
    }

} 