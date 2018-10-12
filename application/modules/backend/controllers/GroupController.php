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
 * Created: 12.10.2018
 */

class Backend_GroupController extends Local_Controller_Action_Backend
{

    public function newgroupAction()
    {
        $this->_helper->layout()->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->_request;
        $header = $this->getHeaders();
        Zend_Registry::get('logger')->info(__METHOD__ . ' - gitlab event data header: ' . implode(";;",$header));
        $body = $request->getRawBody();
        Zend_Registry::get('logger')->info(__METHOD__ . ' - gitlab event data body: ' . $body);
        $json = Zend_Json::decode($request);
        Zend_Registry::get('logger')->info(__METHOD__ . ' - gitlab event data decoded json: ' . implode(";;",$json));

        //$this->_helper->json(array('status'=> 'ok', 'msg'=>'success'));
    }

    private function getHeaders()
    {
        if (false === function_exists('getallheaders'))
        {
            $headers = [];
            foreach ($_SERVER as $name => $value)
            {
                if (substr($name, 0, 5) == 'HTTP_')
                {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        }

        return getallheaders();
    }

}