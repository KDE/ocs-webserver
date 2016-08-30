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

class Default_Model_InfoTest extends Zend_Test_PHPUnit_DatabaseTestCase
{
    protected $_connectionMock;

    public function getConnection()
    {
        if (null === $this->_connectionMock) {
            require_once APPLICATION_LIB . '/Local/Application.php';
            $this->bootstrap = new Local_Application(APPLICATION_ENV, Zend_Registry::get('configuration'),
                Zend_Registry::get('cache'));
            $this->bootstrap->bootstrap('db');
            $db = $this->bootstrap->getBootstrap()->getResource('db');
            $this->_connectionMock = $this->createZendDbConnection($db, 'zftest');
            //Zend_Db_Table_Abstract::setDefaultAdapter($db);
        }
        return $this->_connectionMock;
    }

    public function getDataSet()
    {
        //return $this->createMySQLXMLDataSet(realpath(APPLICATION_PATH . '/../tests/_files') . '/initialDataSetMember.xml');
    }

    
}
