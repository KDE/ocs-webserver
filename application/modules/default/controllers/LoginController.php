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

    public function fpAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $fingerprint = stripslashes(trim($this->getParam('fp')));

        Zend_Registry::set('client_fp', $fingerprint);

        $namespace = new Zend_Session_Namespace();
        $namespace->client_fp = $fingerprint;

        $this->_helper->json(array('status' => 'ok'));
    }
    
    public function ipAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $ip = stripslashes(trim($this->getParam('ip')));
        $ip_type = stripslashes(trim($this->getParam('type')));

        if($ip_type == 'ipv6') {
            Zend_Registry::set('client_ipv6', $ip);
            $namespace = new Zend_Session_Namespace();
            $namespace->client_ipv6 = $ip;
        } else if($ip_type == 'ipv4') {
            Zend_Registry::set('client_ipv4', $ip);
            $namespace = new Zend_Session_Namespace();
            $namespace->client_ipv4 = $ip;
        }

        $this->_helper->json(array('status' => 'ok'));
    }

}