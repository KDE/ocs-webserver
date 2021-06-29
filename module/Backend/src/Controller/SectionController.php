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

use Application\Model\Entity\Section;
use Application\Model\Interfaces\SectionInterface;
use Application\Model\Repository\ConfigStoreRepository;
use Application\Model\Repository\SectionRepository;
use Application\Model\Service\ProjectCategoryService;
use Laminas\View\Model\JsonModel;
use Library\Payment\Exception;

class SectionController extends BackendBaseController
{

    const DATA_ID_NAME = 'section_id';

    public function __construct(
        SectionInterface $sectionRepository
    ) {
        parent::__construct();

        /** @var SectionRepository _model */
        $this->_model = $sectionRepository;
        $this->_modelName = Section::class;
        $this->_pageTitle = 'Manage Sections';
        $this->_defaultSorting = 'section_id asc';
    }


    // public function createAction()
    // {
    //     $jTableResult = array();
    //     try {
    //         $allParams = $this->getAllParams();
    //         $resultWalk = array_walk($allParams, function (&$value) {
    //             $value = strlen($value) == 0 ? null : $value;
    //         });
    //         if (false === $resultWalk) {
    //             throw new Exception('array_walk through input parameters failed.');
    //         }
    //         //$newRow = $this->_model->createRow($allParams);
    //         //$result = $newRow->save();
    //         $newRow = $this->_model->save($allParams);

    //         $jTableResult['Result'] = self::RESULT_OK;
    //         $jTableResult['Record'] = $newRow->toArray();
    //     } catch (Exception $e) {
    //         Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
    //         $translate = Zend_Registry::get('Zend_Translate');
    //         $jTableResult['Result'] = self::RESULT_ERROR;
    //         $jTableResult['Message'] = $translate->_('Error while processing data.');
    //     }

    //     $this->_helper->json($jTableResult);
    // }

    // public function updateAction()
    // {
    //     $jTableResult = array();
    //     try {
    //         $values = $this->getAllParams();

    //         foreach ($values as $key => $value) {
    //             if ($value == '') {
    //                 $values[$key] = new Zend_Db_Expr('NULL');
    //             }
    //         }

    //         $record = $this->_model->save($values);

    //         $jTableResult = array();
    //         $jTableResult['Result'] = self::RESULT_OK;
    //     } catch (Exception $e) {
    //         Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
    //         $translate = Zend_Registry::get('Zend_Translate');
    //         $jTableResult['Result'] = self::RESULT_ERROR;
    //         $jTableResult['Message'] = $translate->_('Error while processing data.');
    //     }

    //     $this->_helper->json($jTableResult);
    // }

    // protected function initCache($store_id)
    // {
    //     $modelPCat = new Default_Model_ProjectCategory();
    //     $modelPCat->fetchCategoryTreeForSection($store_id, true);

    //     $this->_model->fetchAllSectionsAndCategories(true);
    //     $this->_model->fetchAllSectionsArray(true);
    // }

    public function deleteAction()
    {
        $dataId = (int)$this->getParam(self::DATA_ID_NAME, null);

        $this->_model->deleteReal($dataId);

        $this->cacheClear($dataId);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    protected function cacheClear($store_id)
    {
        $cache = $GLOBALS['ocs_cache'];
        $cache->setItem(ProjectCategoryService::CACHE_TREE_STORE . "_{$store_id}", null);
        $cache->setItem(ConfigStoreRepository::CACHE_STORE_CONFIG . "_{$store_id}", null);
        $this->_model->fetchAllSectionsAndCategories(true);
        $this->_model->fetchAllSectionsArray(true);
    }

    public function listAction()
    {
        $filter['name'] = $this->getParam('filter_name');
        $filter['category_id'] = $this->getParam('filter_category_id');
        $jTableResult = $this->prepareListActionJTableResult(null, $filter);

        return new JsonModel($jTableResult);
    }

    public function childlistAction()
    {
        throw \Exception("not implemented");

        $sectionId = (int)$this->getParam('SectionId');

        $modelSponsor = new \Application\Model\Repository\SponsorRepository();
        $resultSet = $modelSponsor->fetchSectionItems($sectionId);
        $numberResults = count($resultSet);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $resultSet;
        $jTableResult['TotalRecordCount'] = $numberResults;

        $this->_helper->json($jTableResult);
    }

    public function childcreateAction()
    {
        throw \Exception("not implemented");

        $jTableResult = array();
        try {
            $sectionId = (int)$this->getParam('section_id');
            $sponsorId = (int)$this->getParam('sponsor_id');
            $percent = $this->getParam('percent_of_sponsoring');
            $modelSponsor = new Default_Model_Sponsor();
            $newRow = $modelSponsor->assignSponsor($sectionId, $sponsorId, $percent);

            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $newRow;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $translate = Zend_Registry::get('Zend_Translate');
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $translate->_('Error while processing data.');
        }

        $this->_helper->json($jTableResult);
    }

    public function childupdateAction()
    {
        throw \Exception("not implemented");

        $jTableResult = array();
        try {
            $sectionSponsorId = (int)$this->getParam('section_sponsor_id');
            $sectionId = (int)$this->getParam('section_id');
            $sponsorId = (int)$this->getParam('sponsor_id');
            $percent = $this->getParam('percent_of_sponsoring');

            $modelSponsor = new Default_Model_Sponsor();
            $modelSponsor->updateSectionSponsor($sectionSponsorId, $sectionId, $sponsorId, $percent);
            $record = $modelSponsor->fetchOneSectionItem($sectionSponsorId);

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            $translate = Zend_Registry::get('Zend_Translate');
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $translate->_('Error while processing data.');
        }

        $this->_helper->json($jTableResult);
    }

    public function childdeleteAction()
    {
        throw \Exception("not implemented");

        $sectionSponsorId = (int)$this->getParam('section_sponsor_id', null);

        $modelSponsor = new Default_Model_Sponsor();
        $modelSponsor->deleteSectionSponsor($sectionSponsorId);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        $this->_helper->json($jTableResult);
    }

}