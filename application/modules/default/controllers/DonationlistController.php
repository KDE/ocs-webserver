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
class DonationlistController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->_helper->viewRenderer('render');
        $this->renderAction();
    }

    /**
     * code for external website:
     * <iframe src="http://domain/donationlist/4a68aadb173644389ac419de963b331f/" width="????px" height="????px" frameBorder=0 scrolling="no" allowTransparency="true" seamless></iframe>
     */
    public function renderAction()
    {
        $this->_helper->layout->disableLayout();

        $filterInput = new Zend_Filter_Input(
            array('*' => array('StringTrim', 'StripTags')),
            array('project_id' => array('Alnum', 'presence' => 'required')),
            $this->getAllParams()
        );

        if (false == $filterInput->isValid('project_id')) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        $projectId = $filterInput->getEscaped('project_id');
        $projectTable = new Default_Model_Project();
        $projectInfo = $projectTable->fetchProductInfo($projectId);

        if (false == isset($projectInfo)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        } 

        $this->view->project = $projectInfo;

        $modelPlings = new Default_Model_Pling();
        $this->view->donations = $modelPlings->getDonationsForProject($projectId);

        $websiteOwner = new Local_Verification_WebsiteAuthCodeExist();
        $this->view->authCode = '<meta name="ocs-site-verification" content="' . $websiteOwner->generateAuthCode(stripslashes($projectInfo->link_1)) . '" />';
    }

} 