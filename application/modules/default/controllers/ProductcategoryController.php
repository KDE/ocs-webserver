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
class ProductcategoryController extends Local_Controller_Action_DomainSwitch
{

    const CATEGORY_ID = 'cat_id';

    public function init()
    {
        parent::init();

        $this->auth = Zend_Auth::getInstance();
    }

    public function fetchchildrenAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $identifier = $this->_request->getParam(self::CATEGORY_ID);

        $tabCategories = new Default_Model_DbTable_ProjectCategory();

        if (is_array($identifier)) {
            $result_array = array();
            foreach ($identifier as $element) {
                $result = $tabCategories->fetchImmediateChildren($element, $tabCategories::ORDERED_TITLE);
                $result_array = array_merge($result_array, $result);
            }
            $this->_helper->json($result_array);
        } else {
            $this->_helper->json($tabCategories->fetchImmediateChildren($identifier, $tabCategories::ORDERED_TITLE));
        }
    }

    public function fetchcategoriesAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $tableCategories = new Default_Model_DbTable_ProjectCategory();
        $categories = $tableCategories->fetchTree(true, false, 1);

        $paginator = Zend_Paginator::factory($categories);
        $paginator->setItemCountPerPage(5);
        $paginator->setCurrentPageNumber(1);

        $this->_helper->json((array)$paginator->getCurrentItems());
    }

    public function fetchcategoryproductsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $identifier = $this->_request->getParam(self::CATEGORY_ID);

        $tableProject = new Default_Model_Project();
        $products = $tableProject->fetchProductsByCategory($identifier, 5);


        $this->_helper->json($products);
    }

} 