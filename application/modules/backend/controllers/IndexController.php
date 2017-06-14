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
class Backend_IndexController extends Local_Controller_Action_Backend
{

    public function indexAction()
    {

    }

    public function metaAction()
    {

    }

    public function settingsAction()
    {

    }

    public function filebrowserAction()
    {

    }

    public function getnewmemberstatsAction()
    {

        $this->_helper->layout->disableLayout();

        $memberTable = new Default_Model_DbTable_Member();

        $sel = $memberTable->select()->setIntegrityCheck(false);

        $sel->from($memberTable, array('DATE(`created_at`) as memberdate', 'count(*) as daycount'))
            ->group('memberdate')
            ->order('memberdate DESC')
            ->limit(14);

        $memberData = $memberTable->fetchAll($sel);
        $memberData = $memberData->toArray();

        $memberData = array_reverse($memberData);

        $responseData['results'] = $memberData;
        $this->_helper->json($responseData);
    }

    public function getnewprojectstatsAction()
    {
        $this->_helper->layout->disableLayout();

        $tableProject = new Default_Model_Project();
        $projectApplyData = $tableProject->getStatsForNewProjects();
        $responseData['results'] = array_reverse($projectApplyData);
        $this->_helper->json($responseData);

        return;
    }

}