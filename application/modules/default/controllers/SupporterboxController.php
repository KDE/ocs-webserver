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
class SupporterboxController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->_helper->viewRenderer('render');
        $this->renderAction();
    }

    public function renderAction()
    {
        $this->_helper->layout->disableLayout();
        $productUuid = $this->getParam('project_uuid');

        if (false == isset($productUuid)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        $projectModel = new Default_Model_Project();
        $projectRow = $projectModel->fetchRow(array('uuid = ?' => $productUuid));

        if (!isset($projectRow)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        } else {
            $plingModel = new Default_Model_DbTable_Plings();
            $this->view->product = $projectRow;
            $this->view->supporters = $plingModel->getProjectSupporters($projectRow->project_id, 8, true);
            $this->view->nrOfSupporters = $plingModel->getCountSupporters($projectRow->project_id);
            $websiteOwner = new Local_Verification_WebsiteAuthCodeExist();
            $this->view->authCode = '<meta name="ocs-site-verification" content="' . $websiteOwner->generateAuthCode(stripslashes($projectRow->link_1)) . '" />';
        }
    }

}