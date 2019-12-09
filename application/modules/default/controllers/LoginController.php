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
 * Created: 30.11.2018
 */
class LoginController extends Local_Controller_Action_DomainSwitch
{

    public function setthemeAction()
    {
        $this->_helper->layout()->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);

    }

    public function setAction()
    {
        $this->_helper->layout()->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);

    }

    public function fpAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $fp = stripslashes(trim($this->getParam('fp')));
        $ip4 = filter_var(stripslashes(trim($this->getParam('ipv4'))), FILTER_VALIDATE_IP);
        $ip6 = filter_var(stripslashes(trim($this->getParam('ipv6'))), FILTER_VALIDATE_IP);
        $hash = sha1($fp.$ip4.$ip6);
        $request_ip = $this->getRequest()->getClientIp();
        $namespace = new Zend_Session_Namespace();

        if ($namespace->stat_hash === $hash) {
            $this->_helper->json(array('status' => 'ok'));

            return;
        }

        $namespace->client_fp = $fp;
        $namespace->stat_fp = $fp;
        $namespace->stat_ipv4 = $ip4 ? $ip4 : null;
        $namespace->stat_ipv6 = $ip6 ? $ip6 : null;
        $namespace->stat_hash = $hash;
        $namespace->stat_request_ip = $request_ip;
        $namespace->stat_valid = true;

        foreach ($namespace->getIterator()->getArrayCopy() as $key=>$item) {
            Zend_Registry::get('logger')->debug(print_r($key, true).' => '.print_r($item,true));
        };

        $this->_helper->json(array('status' => 'ok'));
    }
    
}