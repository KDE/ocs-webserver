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
class GatewayController extends Zend_Controller_Action
{

    public function indexAction()
    {
    }

    /**
     * Official OCS API to receive messages from PayPal.
     */
    public function paypalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // It is really important to receive the information in this way. In some cases Zend can destroy the information
        // when parsing the data
        $rawPostData = file_get_contents('php://input');

        Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process PayPal IPN - ');
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - rawpostdata - ' . print_r($rawPostData, true));
        
        //Switch betwee AdaptivePayment and Masspay
        if (isset($rawPostData['txn_type']) AND ($rawResponse['txn_type'] == 'masspay')) {
            $modelPayPal = new Default_Model_PayPal_MasspayIpnMessage();
            $modelPayPal->processIpn($rawPostData);
        } else {
            $modelPayPal = new Default_Model_PayPal_IpnMessage();
            $modelPayPal->processIpn($rawPostData);
            
        }

    }

    public function dwollaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // It is really important to receive the information in this way. In some cases Zend can destroy the information
        // when parsing the data
        $rawPostData = file_get_contents('php://input');

        Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process Dwolla IPN - ');
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - rawpostdata - ' . print_r($rawPostData, true));

        $modelDwolla = new Default_Model_Dwolla_Callback();
        $modelDwolla->processCallback($rawPostData);
    }

    public function amazonAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // It is really important to receive the information in this way. In some cases Zend can destroy the information
        // when parsing the data
        $rawPostData = file_get_contents('php://input');

//        $modelAmazon = new Default_Model_Amazon_IpnMessage();
//        $modelAmazon->processCallback($rawPostData);

        Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process Amazon IPN - ');
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - rawpostdata - ' . print_r($rawPostData, true));
    }

    public function dwollahookAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // It is really important to receive the information in this way. In some cases Zend can destroy the information
        // when parsing the data
        $rawPostData = file_get_contents('php://input');

        Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process Dwolla IPN - ');
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - rawpostdata - ' . print_r($rawPostData, true));
    }

}
