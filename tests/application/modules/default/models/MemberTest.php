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

class Default_Model_MemberTest extends Zend_Test_PHPUnit_DatabaseTestCase
{

    protected $_connectionMock;

    public function getDataSet()
    {
        return $this->createMySQLXMLDataSet(realpath(APPLICATION_PATH . '/../tests/_files') . '/initialDataSetMember.xml');
    }

    public function testDatabaseCanBeRead()
    {
        $ds = new Zend_Test_PHPUnit_Db_DataSet_QueryDataSet($this->getConnection());
        $ds->addTable('member', 'SELECT * FROM member');
        $expected = $this->createMySQLXMLDataSet(realpath(APPLICATION_PATH . '/../tests/_files') . '/selectDataSetMember.xml');
        $this->assertDataSetsEqual($expected, $ds);
    }

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

    public function testFetchNewMembers()
    {
        $modelMember = new Default_Model_Member();
        $results = $modelMember->fetchNewActiveMembers(50);
        $this->assertTrue(50 == $results->count());
    }

    public function testFetchNewMembersNullCount()
    {
        $modelMember = new Default_Model_Member();
        $results = $modelMember->fetchNewActiveMembers(null);
        $this->assertTrue(1 == $results->count());
    }

    public function testActivateMemberFromVerification()
    {
        $modelMember = new Default_Model_Member();
        $results = $modelMember->activateMemberFromVerification(9);
        $changedMemberData = $modelMember->find(9)->current();
        $this->assertTrue(Default_Model_Member::MEMBER_ACTIVE == $changedMemberData->is_active);
        $this->assertTrue(Default_Model_Member::MEMBER_MAIL_CHECKED == $changedMemberData->mail_checked);
        $this->assertTrue(Default_Model_Member::MEMBER_NOT_DELETED == $changedMemberData->is_deleted);
    }

    public function testFetchMemberData()
    {
        $modelMember = new Default_Model_Member();
        $results = $modelMember->fetchMemberData(24);
        $this->assertTrue(24 == $results->member_id);
    }

    public function testFetchMemberFromHive()
    {
        $modelMember = new Default_Model_Member();
        $results = $modelMember->fetchMemberFromHiveUserName('ruebe');
        $this->assertTrue(40 == $results['member_id']);
    }

    public function testMemberFollowUsers()
    {
        $modelMember = new Default_Model_Member();
        $results = $modelMember->fetchFollowedMembers(22);
        $this->assertTrue(4 == count($results));
    }

}
