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
class ButtonController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->_helper->viewRenderer('render');
        $this->renderAction();
    }

    /**
     * code for external website:
     * <iframe src="http://domain/button/926004/small|large/" width="204px" height="60px" frameBorder=0 scrolling="no" allowTransparency="true" seamless></iframe>
     */
    public function renderAction()
    {
        $this->_helper->layout->disableLayout();

        $filterInput = new Zend_Filter_Input(
            array('*' => 'StringTrim', 'project_id' => 'Alnum', 'size' => 'Alpha'),
            array('project_id' => array('Alnum', 'presence' => 'required'), 'size' => 'Alpha'),
            $this->getAllParams()
        );

        if (false == $filterInput->isValid('project_id')) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        $modelProject = new Default_Model_Project();
        $dataProject = $modelProject->fetchRow(array('project_id = ?' => (int) $filterInput->getEscaped('project_id')));

        if (0 >= count($dataProject->toArray())) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        $this->view->projectId = $dataProject->project_id;
        $this->view->config = array('size' => stripslashes($filterInput->getEscaped('size')), 'target' => '_blank');

        $this->view->authCode = '';
        if ($dataProject->link_1) {
            $websiteOwner = new Local_Verification_WebsiteAuthCodeExist();
            $this->view->authCode = '<meta name="ocs-site-verification" content="' . $websiteOwner->generateAuthCode(stripslashes($dataProject->link_1)) . '" />';
        }
    }

    /**
     * @deprecated 
     */
    public function translateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $filterInput = new Zend_Filter_Input(
            array('*' => 'StringTrim', 'project_uuid' => 'Alnum'),
            array('project_uuid' => array('Alnum', 'presence' => 'required')),
            $this->getAllParams()
        );

        $projectTable = new Default_Model_Project();
        $rowSet = $projectTable->fetchRow('uuid = "' . $filterInput->getEscaped('project_uuid') . '"');

        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        $url = $helperBuildProductUrl->buildProductUrl($rowSet->project_id);
        $this->redirect($url);
    }

}
