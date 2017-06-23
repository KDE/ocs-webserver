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

        $project_id = (int)$this->getParam('project_id');
        $size = $this->getParam('size') ? preg_replace('/[^-a-zA-Z0-9_]/', '', $this->getParam('size')) : null;

        $modelProject = new Default_Model_Project();
        $dataProject = $modelProject->fetchRow(array('project_id = ?' => $project_id));

        if (0 >= count($dataProject->toArray())) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        $this->view->projectId = $dataProject->project_id;
        $this->view->config = array('size' => $size, 'target' => '_blank');

        $this->view->authCode = '';
        if ($dataProject->link_1) {
            $websiteOwner = new Local_Verification_WebsiteProject();
            $this->view->authCode = '<meta name="ocs-site-verification" content="'
                . $websiteOwner->generateAuthCode(stripslashes($dataProject->link_1)) . '" />';
        }
    }

}