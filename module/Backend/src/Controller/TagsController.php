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

use Application\Model\Entity\TagGroup;
use Application\Model\Interfaces\TagGroupInterface;
use Application\Model\Repository\TagGroupRepository;
use Application\Model\Service\Interfaces\TagGroupServiceInterface;
use Exception;
use Laminas\View\Model\JsonModel;

class TagsController extends BackendBaseController
{
    private $paypalValidStatusRepository;
    private $tagGroupService;
    /** @var TagGroupRepository */
    protected $_model;

    public function __construct(
        TagGroupInterface $tagGroupRepository,
        TagGroupServiceInterface $tagGroupService

    ) {
        parent::__construct();
        $this->tagGroupRepository = $tagGroupRepository;
        $this->tagGroupService = $tagGroupService;
        $this->_model = $tagGroupRepository;
        $this->_modelName = TagGroup::class;
        $this->_pageTitle = 'Manage Tags';
        $this->_defaultSorting = 'group_id asc';
    }

    public function deleteAction()
    {
        $groupId = (int)$this->getParam('group_id', null);

        $this->_model->deleteReal($groupId);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function childlistAction()
    {
        $groupId = (int)$this->getParam('GroupId');

        $modelTagGroup = $this->tagGroupService;
        $resultSet = $modelTagGroup->fetchGroupItems($groupId);
        $numberResults = count($resultSet);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $resultSet;
        $jTableResult['TotalRecordCount'] = $numberResults;

        return new JsonModel($jTableResult);

    }

    public function childcreateAction()
    {
        $jTableResult = array();
        try {
            $groupId = (int)$this->getParam('tag_group_id');
            $tagName = $this->getParam('tag_name');
            $tagFullname = $this->getParam('tag_fullname');
            $tagDescription = $this->getParam('tag_description');
            $is_active = $this->getParam('is_active');
            $modelTagGroup = $this->tagGroupService;
            $newRow = $modelTagGroup->assignGroupTag($groupId, $tagName, $tagFullname, $tagDescription, $is_active);

            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $newRow;
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));

            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function childupdateAction()
    {
        $jTableResult = array();
        try {
            $groupItemId = (int)$this->getParam('tag_group_item_id');
            //$tagId = (int)$this->getParam('tag_id');
            $tagName = $this->getParam('tag_name');

            $tagFullname = $this->getParam('tag_fullname');
            $tagDescription = $this->getParam('tag_description');
            $is_active = $this->getParam('is_active');
            $modelTagGroup = $this->tagGroupService;
            //load tag
            $record = $modelTagGroup->fetchOneGroupItem($groupItemId);
            $tagId = $record['tag_id'];

            $modelTagGroup->updateGroupTag($tagId, $tagName, $tagFullname, $tagDescription, $is_active);
            $record = $modelTagGroup->fetchOneGroupItem($groupItemId);

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

    public function childdeleteAction()
    {
        $groupItemId = (int)$this->getParam('tag_group_item_id', null);

        $modelTagGroup = $this->tagGroupService;
        $modelTagGroup->deleteGroupTag($groupItemId);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

}