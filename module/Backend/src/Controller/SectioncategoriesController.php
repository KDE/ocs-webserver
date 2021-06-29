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
use Application\Model\Interfaces\SectionCategoryInterface;
use Application\Model\Interfaces\SectionInterface;
use Exception;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\JsonModel;

class SectioncategoriesController extends BackendBaseController
{
    private $sectionCategoryRepository;
    private $projectCategoryRepository;
    private $sectionRepository;

    public function __construct(
        ProjectCategoryInterface $projectCategoryRepository,
        SectionCategoryInterface $sectionCategoryRepository,
        SectionInterface $sectionRepository
    ) {
        parent::__construct();
        $this->_model = $projectCategoryRepository;
        $this->sectionCategoryRepository = $sectionCategoryRepository;
        $this->projectCategoryRepository = $projectCategoryRepository;
        $this->sectionRepository = $sectionRepository;

        $this->_pageTitle = 'Manage Section-Categories';
    }

    public function updateAction()
    {

        $jTableResult = array();
        try {

            $section_id = $this->getParam('section_id', null);
            $tagmodel = $this->sectionCategoryRepository;

            $tagmodel->updateSectionPerCategory((int)$this->getParam('project_category_id', null), $section_id);

            //Update also SubCategories
            $catmodel = $this->projectCategoryRepository;
            $subCats = $catmodel->fetchChildIds((int)$this->getParam('project_category_id', null), true);
            if ($subCats) {
                foreach ($subCats as $cat) {
                    $tagmodel->updateSectionPerCategory($cat, $section_id);
                }
            }


            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            // $jTableResult['Record'] = $record->toArray();
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $this->translate('Error while processing data.');
        }

        return new JsonModel($jTableResult);
    }

    public function listAction()
    {

        $startIndex = (int)$this->getParam('jtStartIndex');
        $pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');
        $filter_deleted = (int)$this->getParam('filter_deleted', 1);

        $records = $this->_model->fetchTreeWithParentIdAndSections($filter_deleted, null);

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
        $cat_id = (int)$this->getParam('c');

        try {
            $records = $this->_model->fetchTreeForJTableSection($cat_id);

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

    public function allsectionsAction()
    {

        $result = true;
        $tagmodel = $this->sectionRepository;
        try {
            $resultRows = $tagmodel->fetchAllSections();
            $resultForSelect = array();
            $resultForSelect[] = array('DisplayText' => '', 'Value' => null);
            foreach ($resultRows as $row) {
                $resultForSelect[] = array(
                    'DisplayText' => $row['name'] . '[' . $row['section_id'] . ']',
                    'Value'       => $row['section_id'],
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