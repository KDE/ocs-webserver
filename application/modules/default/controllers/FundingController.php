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
class FundingController extends Local_Controller_Action_DomainSwitch
{

    /**
     * Zend_Controller_Request_Abstract object wrapping the request environment
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request = null;
    /** @var  int */
    /** @var  Zend_Auth */
    protected $_auth;

    public function init()
    {
        parent::init();
        $this->_auth = Zend_Auth::getInstance();
    }

    public function indexAction()
    {
        $this->view->authMember = $this->_authMember;
        $this->view->headTitle('Funding - ' . $this->getHeadTitle(), 'SET');
    }

}
