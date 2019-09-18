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
class ErrorController extends Local_Controller_Action_DomainSwitch
{

    protected $error_401_msg = "<p>Sorry, but you are not authorized to view this page.
Either no authentication was provided, it was invalid, or this page is not meant for your eyes.

Still no luck? Search for whatever is missing, or take a look around the rest of our site. </p>";

    protected $error_403_msg = "<p>Sorry, but you cannot access this page.
Even if you have authentication, you are still not allowed to access this page. It's not meant for your eyes - ever!</p>

<p>Still no luck? Search for whatever is missing, or take a look around the rest of our site. </p>";


    protected $error_404_msg = "<p>We're sorry.

Unfortunately the page you were looking for could not be found. It may be temporarily unavailable, moved or no longer exist.

Check the URL you entered for any mistakes and try again. Still no luck? Search for whatever is missing, or take a look around the rest of our site. </p>";


    protected $error_500_msg = "<p>We're sorry.

Unfortunately the page you were looking for could not be found. It may be temporarily unavailable, moved or no longer exist.

Check the URL you entered for any mistakes and try again. Still no luck? Search for whatever is missing, or take a look around the rest of our site. </p>";


    protected $error_503_msg = "<p>Sorry, but our servers are currently unavailable.
We're probably overloaded or down for maintenance.</p>

<p>Refresh the page or try again later - this is only temporary.
Still no luck? Search for whatever is missing, or take a look around the rest of our site. </p>";

    public function errorAction()
    {
        $errors = $this->getParam('error_handler');
        $this->getResponse()->clearBody();

        switch ($errors->type) {
            case Default_Plugin_ErrorHandler::EXCEPTION_NO_ACL_RULE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action or route not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = $this->error_404_msg;
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = $this->error_500_msg;
                break;
        }

        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
            $this->view->request = $errors->request;
        }

        $errorLog = Zend_Registry::get('logger');

        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'undefined';
        $storeHost = Zend_Registry::isRegistered('store_host') ? Zend_Registry::get('store_host') : 'undefined';

        if ($errors->exception->getCode() == 404) {
            $errorInfo = array(
                'REQUEST_URI'  => $_SERVER['REQUEST_URI'],
                'MESSAGES'     => $errors->exception->getMessage(),
                'HOST'         => $_SERVER['HTTP_HOST'],
                'STORE_HOST'   => Zend_Registry::isRegistered('store_host') ? Zend_Registry::get('store_host') : 'undefined',
                'USER_AGENT'   => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'undefined',
                'ENVIRONMENT'  => APPLICATION_ENV,
                'REMOTE_ADDR'  => $_SERVER['REMOTE_ADDR'],
                'FORWARDED_IP' => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : 'undefined',

            );
            $errorLog->err(__METHOD__ . ' - ' . json_encode($errorInfo));

            return;
        }

        $errorMsg = '' . PHP_EOL;
        $errorMsg .= 'MESSAGE::     ' . $errors->exception->getMessage() . PHP_EOL;
        $errorMsg .= 'HOST::        ' . $_SERVER['HTTP_HOST'] . PHP_EOL;
        $errorMsg .= 'USER_AGENT::  ' . $userAgent . PHP_EOL;
        $errorMsg .= 'REQUEST_URI:: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
        $errorMsg .= 'ENVIRONMENT:: ' . APPLICATION_ENV . PHP_EOL;
        $errorMsg .= 'STORE_HOST::  ' . $storeHost . PHP_EOL;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $errorMsg .= 'FORWARDED_IP::' . $_SERVER['HTTP_X_FORWARDED_FOR'] . PHP_EOL;
        } else {
            $errorMsg .= 'REMOTE_ADDR:: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
        }

        if (isset($errors->exception->xdebug_message)) {
            $errorMsg .= 'XDEBUG_MESSAGE::' . $errors->exception->xdebug_message . PHP_EOL;
        } else {
            $errorMsg .= 'TRACE_STRING::' . $errors->exception->getTraceAsString() . PHP_EOL;
        }
        $errorLog->err(__METHOD__ . ' - ' . $errorMsg . PHP_EOL);
    }

    public function privilegesAction()
    {
        $this->getResponse()->setHttpResponseCode(403);
    }

    public function loginAction()
    {
        $this->getResponse()
             ->setHttpResponseCode(401);
        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $loginUri = $request->getParam('redirect') ? '/login/redirect/' . $request->getParam('redirect') : '/login';
            $this->_helper->json(array(
                'status'    => 'error',
                'title'     => '',
                'message'   => 'Login Required',
                'code'      => 401,
                'login_url' => $loginUri
            ));
        }
    }

    protected function _initResponseHeader()
    {
        parent::_initResponseHeader(); // TODO: Change the autogenerated stub
        $this->getResponse()
             ->clearHeaders(array('Expires', 'Pragma', 'Cache-Control'))
             ->setHeader('Pragma', 'no-cache', true)
             ->setHeader('Cache-Control', 'private, no-cache, must-revalidate', true);
    }

}
