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

class ButtonControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        require_once APPLICATION_LIB . '/Local/Application.php';

        // Create application, bootstrap, and run
        $this->bootstrap = new Local_Application(
            APPLICATION_ENV,
            Zend_Registry::get('configuration'),
            Zend_Registry::get('cache')
        );

        parent::setUp();
    }

    public function tearDown()
    {
        Zend_Controller_Front::getInstance()->resetInstance();
        $this->resetRequest();
        $this->resetResponse();

        $this->request->setPost(array());
        $this->request->setQuery(array());
    }

    public function testIndexAction()
    {
        //$this->request->setParam('project_id', '53');
        $this->dispatch('/button/53');
        $this->assertController('button');
        $this->assertAction('render');
        $this->assertResponseCode(200);
    }

    public function testRenderAction()
    {
        $this->dispatch('/button/53/large');
        $this->assertController('button');
        $this->assertAction('render');
        $this->assertResponseCode(200);
    }

    public function testProjectNotExist()
    {
        $this->dispatch('/button/53/large');
        $this->assertController('button');
        $this->assertAction('render');
        $this->assertResponseCode(200);
    }

    public function testProjectUuidInvalid()
    {
        $this->dispatch('/button/ASf33865d55ed64/9ea05fb80c620a25/large');
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(404);
    }

}
 