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

use Application\Model\Interfaces\ProjectCategoryInterface;
use Application\Model\Service\TagService;
use Exception;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class CategorytagController extends BackendBaseController
{
    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";

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
        $this->layout()->pageTitle = 'Manage Category_Tag';

        return $viewModel;
    }

    public function updateAction()
    {

        $jTableResult = array();
        try {
            $tagsid = $this->params()->fromPost('tags_id', null);
            $tagsgroupdid = (int)$this->params()->fromPost('project_category_id', null);
            $tagmodel = $this->tagService;
            $isvalid = $tagmodel->validateCategoryTags($tagsgroupdid, $tagsid);
            if ($isvalid) {
                $tagmodel->updateTagsPerCategory($tagsgroupdid, $tagsid);
                $jTableResult = array();
                $jTableResult['Result'] = self::RESULT_OK;
            } else {
                $jTableResult = array();
                $jTableResult['Result'] = self::RESULT_ERROR;
                $jTableResult['Message'] = 'duplicated! please check parent/children catgory tags.';
            }

            // $jTableResult['Record'] = $record->toArray();
        } catch (Exception $e) {
            // Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            error_log($e->__toString());
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function listAction()
    {

        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');

        $filter_deleted = (int)$this->params()->fromPost('filter_deleted', 1);

        $records = $this->_model->fetchTreeWithParentIdAndTags($filter_deleted, null);

        $pagination = new Paginator(new ArrayAdapter($records));
        $pagination->setItemCountPerPage($pageSize);
        $pagination->setCurrentPageNumber(($startIndex / $pageSize) + 1);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = (array)$pagination->getCurrentItems();
        $jTableResult['TotalRecordCount'] = count($records);

        return new JsonModel($jTableResult);
    }

    public function treeAction()
    {

        $result = true;
        $cat_id = (int)$this->params()->fromQuery('c');

        try {
            $records = $this->_model->fetchTreeForJTableStores($cat_id);

        } catch (Exception $e) {
            //Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            error_log($e->__toString());
            $result = false;
            $records = array();
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;
        $jTableResult['Options'] = $records;

        return new JsonModel($jTableResult);
    }

    public function tagsallAction()
    {

        $result = true;
        $groupid = 6; // category-tags
        $tagmodel = $this->tagService;
        try {
            $resultRows = $tagmodel->getTagsPerGroup($groupid);
            $resultForSelect = array();
            foreach ($resultRows as $row) {
                $resultForSelect[] = array(
                    'DisplayText' => $row['tag_name'] . '[' . $row['tag_id'] . ']',
                    'Value'       => $row['tag_id'],
                );
            }

        } catch (Exception $e) {
            //Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            error_log($e->__toString());
            $result = false;
            $records = array();
        }

        $jTableResult = array();
        $jTableResult['Result'] = ($result == true) ? self::RESULT_OK : self::RESULT_ERROR;
        $jTableResult['Options'] = $resultForSelect;

        return new JsonModel($jTableResult);
    }

}