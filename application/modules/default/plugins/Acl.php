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
class Default_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{

    /** @var Zend_Auth */
    private $_auth;
    /** @var Zend_Acl */
    private $_acl;
    private $_noauth = array(
        'module'     => 'default',
        'controller' => 'authorization',
        'action'     => 'login'
    );
    private $_noacl = array(
        'module'     => 'default',
        'controller' => 'error',
        'action'     => 'privileges'
    );
    private $_authRequired = array(
        'module'     => 'default',
        'controller' => 'error',
        'action'     => 'login'
    );
    private $_authFromCookie = array(
        'module'     => 'default',
        'controller' => 'authorization',
        'action'     => 'loginfromcookie'
    );

    public function __construct($auth, $acl)
    {
        $this->_auth = $auth;
        $this->_acl = $acl;
    }

    /**
     * @param Zend_Controller_Request_Http $request
     *
     * @throws Zend_Exception
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $role       = $this->readUserRole();
        $controller = $request->getControllerName();
        $action     = $request->getActionName();
        $module     = ($request->getModuleName()) ? $request->getModuleName() : "default";

        $resource   = $module . '_' . $controller;

        // check controller/action exists
        $front = Zend_Controller_Front::getInstance();
        $dispatcher = $front->getDispatcher();
        if (false === $dispatcher->isDispatchable($request)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        // check acl rule exists
        if (false == $this->_acl->has($resource)) {
            throw new Zend_Acl_Exception("No ACL rule defined for Module:{$module}, Controller:{$controller}, Action:{$action}");
        }

        // check access right
        if ($this->_acl->isAllowed($role, $resource, $action)) {
            return;
        }

        //access not allowed. test some conditions

        //check user authentication status

        //user is not logged in
        if (false === $this->_auth->hasIdentity()) {
            $this->getResponse()->setHttpResponseCode(401);
            $encryptUrl = $this->getRequestUrlEncrypted();
            if (false == $this->_request->isXmlHttpRequest()) {
                $this->_request->setParam('redirect', $encryptUrl);
                $this->_request->setModuleName($this->_noauth['module']);
                $this->_request->setControllerName($this->_noauth['controller']);
                $this->_request->setActionName($this->_noauth['action']);
            } else {
                $this->_request->setParam('redirect', $encryptUrl);
                $this->_request->setModuleName($this->_authRequired['module']);
                $this->_request->setControllerName($this->_authRequired['controller']);
                $this->_request->setActionName($this->_authRequired['action']);
            }

            return;
        }

        //user has only the remember_me cookie (deprecated since cookieuser has same rights like feuser)
        if ($role == Default_Plugin_AclRules::ROLENAME_COOKIEUSER) {
            $encryptUrl = $this->getRequestUrlEncrypted();
            $this->_request->setModuleName($this->_authFromCookie['module']);
            $this->_request->setControllerName($this->_authFromCookie['controller']);
            $this->_request->setActionName($this->_authFromCookie['action']);
            $this->_request->setParam('redirect', $encryptUrl);

            return;
        }

        //default behavior. user has no access rights
        $this->_request->setModuleName($this->_noacl['module']);
        $this->_request->setControllerName($this->_noacl['controller']);
        $this->_request->setActionName($this->_noacl['action']);
    }

    /**
     * @return mixed
     * @throws Zend_Exception
     */
    private function readUserRole()
    {
        // all users are guests by default
        $role = Default_Plugin_AclRules::ROLENAME_GUEST;

        $identity = $this->_auth->getIdentity();

        if (empty($identity)) {
            return $role;
        }

        if (false === property_exists($identity, 'roleName')) {
            throw new Zend_Exception('property "roleName" does not exist');
        }

        $role = $this->_auth->getIdentity()->roleName;

        if (empty($role)) {
            throw new Zend_Exception('user role is empty in auth identity object');
        }

        return $role;
    }

    /**
     * @return mixed
     * @throws Zend_Filter_Exception
     */
    private function getRequestUrlEncrypted()
    {
        $urlHelper = new Zend_View_Helper_Url();
        $url = $urlHelper->url($this->_request->getParams(), null, true);
        $helperEncryptUrl = new Local_Filter_Url_Encrypt();
        $encryptUrl = $helperEncryptUrl->filter($url);

        return $encryptUrl;
    }

}  