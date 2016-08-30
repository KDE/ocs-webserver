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

class CategoriesController extends Local_Controller_Action_DomainSwitch
{

    public function indexAction()
    {

    }

    public function aboutAction()
    {
        $config = Zend_Registry::get('config');
        $static_config = $config->settings->static;

        $pageId = (int)$this->getParam('page');

        if (false === is_int($pageId)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }
        
        if ((0 >= $pageId) and (false === file_exists($static_config->include_path . 'category_about/' . $pageId . '.phtml'))) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        } else {
            $this->view->page = $static_config->include_path . 'category_about/' . $pageId . '.phtml';
        }
    }

}