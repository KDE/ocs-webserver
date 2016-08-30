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
class HiveController extends Local_Controller_Action_DomainSwitch
{

    public function showAction()
    {
        $modelProject = new Default_Model_Project();
        $projectData = $modelProject->fetchActiveBySourcePk((int)$this->getParam('content'));

        if (!$projectData) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }
        $params = $this->getAllParams();
        $params['project_id'] = $projectData['project_id'];

        $this->forward('show', 'product', 'default', $params);
    }

    public function usersearchAction()
    {
        $username = $this->getParam('username') ? preg_replace('/[^-a-zA-Z0-9_]/', '',  $this->getParam('username')) : null;
        
        $modelUser = new Default_Model_Member();
        $userData = $modelUser->fetchActiveHiveUserByUsername($username);

        if (!$userData) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }
        $params = $this->getAllParams();
        $params['member_id'] = $userData['member_id'];

        $this->forward('index', 'user', 'default', $params);
    }

}