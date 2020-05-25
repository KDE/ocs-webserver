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

class Default_View_Helper_FetchDomainCategoriesTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        require_once APPLICATION_LIB . '/Local/Application.php';

        // Create application, bootstrap, and run
        $this->bootstrap = new Local_Application(
            APPLICATION_ENV,
            Zend_Registry::get('configuration'),
            Zend_Registry::get('cache')
        );
        $this->bootstrap->bootstrap('db');

        parent::setUp(); // TODO: Change the autogenerated stub
    }

    protected function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
    }


    public function testFetchDomainCategoriesOrdered()
    {
        $helper = new Default_View_Helper_FetchDomainCategories();
        
        $result = $helper->fetchDomainCategoriesOrdered(117);
        $subset = array (
            0 =>
                array (
                    'project_category_id' => '117',
                    'lft' => '114',
                    'rgt' => '115',
                    'title' => 'Beryl/Emerald Themes',
                    'is_active' => '1',
                    'is_deleted' => '0',
                    'orderPos' => '0',
                    'created_at' => '2016-03-04 05:47:28',
                    'changed_at' => NULL,
                    'deleted_at' => NULL,
                ),
        );
        $this->assertArraySubset($subset, $result);
    }
    
}
