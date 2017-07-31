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
    }

    public function memberAction()
    {
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

}