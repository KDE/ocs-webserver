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
 *
 * Created: 31.07.2017
 */

class Statistics_DataController extends Zend_Controller_Action
{

    /** @var  Zend_Config */
    protected $db_config;

    public function init()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->setDefaultContext('json');
        $this->db_config = Zend_Registry::get('config')->settings->dwh;
    }

    public function projectAction()
    {
        $modelData = new Statistics_Model_Data($this->db_config->toArray());
        $id = (int) $this->getParam('p');
        try {
            $result = $modelData->getProject($id);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->error($e->getMessage());
            return $this->_helper->json->sendJson(array(
                'status' => 'error',
                'msg' => 'error while processing request',
                'data' => ''
            ));
        }
        if ($result) {
            $msg = array(
                'status' => 'ok',
                'msg' => '',
                'data' => $result
            );
            return $this->_helper->json->sendJson($msg);
        }
        return $this->_helper->json->sendJson(array(
            'status' => 'not found',
            'msg' => 'data with given id could not be found.',
            'data' => ''
        ));
    }

    public function projectsAction()
    {
        /*
        $modelData = new Statistics_Model_Data($this->db_config->toArray());
        $limit = (int) $this->getParam('l');
        try {
            $result = $modelData->getProjects($limit);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->error($e->getMessage());
            return $this->_helper->json->sendJson(array(
                'status' => 'error',
                'msg' => 'error while processing request',
                'data' => ''
            ));
        }
        if ($result) {
            $msg = array(
                'status' => 'ok',
                'msg' => '',
                'data' => $result
            );
            return $this->_helper->json->sendJson($msg);
        }
        return $this->_helper->json->sendJson(array(
            'status' => 'not found',
            'msg' => 'data with given id could not be found.',
            'data' => ''
        ));
        */
        $result = array();
        $result[] = array('memberdate'=>'07-26', 'daycount'=>'88');
        $result[] = array('memberdate'=>'07-27', 'daycount'=>'84');
        $result[] = array('memberdate'=>'07-28', 'daycount'=>'101');
        $result[] = array('memberdate'=>'07-29', 'daycount'=>'96');
        $result[] = array('memberdate'=>'07-30', 'daycount'=>'66');
        $result[] = array('memberdate'=>'07-31', 'daycount'=>'110');
        $result[] = array('memberdate'=>'08-01', 'daycount'=>'90');
        $result[] = array('memberdate'=>'08-02', 'daycount'=>'81');
        $result[] = array('memberdate'=>'08-03', 'daycount'=>'81');
        $result[] = array('memberdate'=>'08-04', 'daycount'=>'85');
        $result[] = array('memberdate'=>'08-05', 'daycount'=>'72');
        $result[] = array('memberdate'=>'08-06', 'daycount'=>'61');
        $result[] = array('memberdate'=>'08-07', 'daycount'=>'64');
        $result[] = array('memberdate'=>'08-08', 'daycount'=>'84');   
        $result[] = array('memberdate'=>'08-09', 'daycount'=>'81');      
        
        return $this->_helper->json->sendJson($result);
    }

    public function memberAction()
    {

        $result = array(
                                    ["projectdate"=>"July 26th","daycount"=>"29"]
                                    ,["projectdate"=>"July 27th","daycount"=>"34"]
                                    ,["projectdate"=>"July 28th","daycount"=>"32"]
                                    ,["projectdate"=>"July 29th","daycount"=>"26"]
                                    ,["projectdate"=>"July 30th","daycount"=>"13"]
                                    ,["projectdate"=>"July 31st","daycount"=>"33"]
                                    ,["projectdate"=>"August 1st","daycount"=>"25"]
                                    ,["projectdate"=>"August 2nd","daycount"=>"30"]
                                    ,["projectdate"=>"August 3rd","daycount"=>"31"]
                                    ,["projectdate"=>"August 4th","daycount"=>"31"]
                                    ,["projectdate"=>"August 5th","daycount"=>"22"]
                                    ,["projectdate"=>"August 6th","daycount"=>"12"]
                                    ,["projectdate"=>"August 7th","daycount"=>"13"]
                                    ,["projectdate"=>"August 8th","daycount"=>"35"]
                                    ,["projectdate"=>"August 9th","daycount"=>"31"]
                                );
        return $this->_helper->json->sendJson($result);

        /*
        $modelData = new Statistics_Model_Data($this->db_config->toArray());
        $id = (int) $this->getParam('m');
        try {
            $result = $modelData->getMember($id);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->error($e->getMessage());
            return $this->_helper->json->sendJson(array(
                'status' => 'error',
                'msg' => 'error while processing request',
                'data' => ''
            ));
        }
        if ($result) {
            $msg = array(
                'status' => 'ok',
                'msg' => '',
                'data' => $result
            );
            return $this->_helper->json->sendJson($msg);
        }
        return $this->_helper->json->sendJson(array(
            'status' => 'not found',
            'msg' => 'data with given id could not be found.',
            'data' => ''
        ));
        */
    }

    public function membersAction()
    {
        $modelData = new Statistics_Model_Data($this->db_config->toArray());
        $limit = (int) $this->getParam('l');
        try {
            $result = $modelData->getMembers($limit);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->error($e->getMessage());
            return $this->_helper->json->sendJson(array(
                'status' => 'error',
                'msg' => 'error while processing request',
                'data' => ''
            ));
        }
        if ($result) {
            $msg = array(
                'status' => 'ok',
                'msg' => '',
                'data' => $result
            );
            return $this->_helper->json->sendJson($msg);
        }
        return $this->_helper->json->sendJson(array(
            'status' => 'not found',
            'msg' => 'data with given id could not be found.',
            'data' => ''
        ));
    }

    public function newprojectsstatAction()
    {
        // last two year for example
            $result=array(100,200,50,60,80,70,100,200,50,60,80,70);
           $msg = array(
                'status' => 'ok',
                'msg' => '',
                'data' => $result
            );
            return $this->_helper->json->sendJson($msg);
    }
}