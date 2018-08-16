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
        'module' => 'default',
        'controller' => 'authorization',
        'action' => 'login'
    );
    private $_noacl = array(
        'module' => 'default',
        'controller' => 'error',
        'action' => 'privileges'
    );
    private $_authRequired = array(
        'module' => 'default',
        'controller' => 'error',
        'action' => 'login'
    );
    private $_authFromCookie = array(
        'module' => 'default',
        'controller' => 'authorization',
        'action' => 'loginfromcookie'
    );

    public function __construct($auth, $acl)
    {
        $this->_auth = $auth;
        $this->_acl = $acl;
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @throws Zend_Exception
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
//        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));
//        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(Zend_Auth::getInstance()->getStorage()->read(), true));

        // all users are guests (at first)
        $role = Default_Plugin_AclRules::ROLENAME_GUEST;

        if ($this->_auth->hasIdentity() && $this->_auth->getIdentity() != null && property_exists($this->_auth->getIdentity(),'roleName'))
        {
            $role = $this->_auth->getIdentity()->roleName;
            if (empty($role)) {
                throw new Zend_Exception('user role is empty in auth identity object');
            }
        }

        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $module = ($request->getModuleName()) ? $request->getModuleName() : "default";

        $resource = $module . '_' . $controller;

        $logMsg = '' . PHP_EOL;
        $logMsg .= "Controller : {$controller}" . PHP_EOL;
        $logMsg .= "Action     : {$action}" . PHP_EOL;
        $logMsg .= "Module     : {$module}" . PHP_EOL;
        $logMsg .= "Resource   : {$resource}" . PHP_EOL;
        $logMsg .= "Role       : {$role}" . PHP_EOL;

        Zend_Registry::get('logger')->info(__METHOD__ . ' - ' . $logMsg . ' ---------- ' . PHP_EOL);

        if (false == $this->_acl->has($resource)) {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - No ACL rule found for ' . print_r($resource,
                    true) . ' : ' . $request->getRequestUri());

            //TODO: send users to error page
            $this->_request->setModuleName($this->_noacl['module']);
            $this->_request->setControllerName($this->_noacl['controller']);
            $this->_request->setActionName($this->_noacl['action']);

            return;
        }

        if (false === $this->_acl->isAllowed($role, $resource, $action)) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - resource not allowed');
            $urlHelper = new Zend_View_Helper_Url();
            $url = $urlHelper->url($this->_request->getParams(), null, true);
            $helperEncryptUrl = new Local_Filter_Url_Encrypt();
            $encryptUrl = $helperEncryptUrl->filter($url);

            //TODO: cleanup code
            if (false === $this->_auth->hasIdentity()) {
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
                $this->getResponse()->setHttpResponseCode(401);
            } elseif ($role == Default_Plugin_AclRules::ROLENAME_COOKIEUSER) {
                $this->_request->setModuleName($this->_authFromCookie['module']);
                $this->_request->setControllerName($this->_authFromCookie['controller']);
                $this->_request->setActionName($this->_authFromCookie['action']);
                $this->_request->setParam('redirect', $encryptUrl);
            } else {
                $this->_request->setModuleName($this->_noacl['module']);
                $this->_request->setControllerName($this->_noacl['controller']);
                $this->_request->setActionName($this->_noacl['action']);
            }
        }

//        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . 'Request ' . print_r($this->_request, true));
    }

}  