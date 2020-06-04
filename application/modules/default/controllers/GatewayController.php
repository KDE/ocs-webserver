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
    public function paypalpayoutAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        try {
            // It is really important to receive the information in this way. In some cases Zend can destroy the information
            // when parsing the data
            $rawPostData = file_get_contents('php://input');
            $ipnArray = $this->_parseRawMessage($rawPostData);

            //Save IPN in DB
            $ipnTable = new Default_Model_DbTable_PaypalIpn();
            $ipnTable->addFromIpnMessage($ipnArray, $rawPostData);

            Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process PayPal Payout IPN - ');

            Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process Payout IPN - ');
            $modelPayPal = new Default_Model_PayPal_PayoutIpnMessage();
            $modelPayPal->processIpn($rawPostData);

        } catch (Exception $exc) {
            //Do nothing...
            Zend_Registry::get('logger')->info(__METHOD__ . ' - Error by Processing PayPal Payout IPN - ExceptionTrace: '. $exc->getTraceAsString());
        }
            
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
        
        $ipnArray = $this->_parseRawMessage($rawPostData);
        
        //Save IPN in DB
        $ipnTable = new Default_Model_DbTable_PaypalIpn();
        $ipnTable->addFromIpnMessage($ipnArray, $rawPostData);
        
        //Switch betwee AdaptivePayment and Masspay
        if (isset($ipnArray['txn_type']) AND ($ipnArray['txn_type'] == 'masspay')) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process Masspay IPN - ');
            $modelPayPal = new Default_Model_PayPal_MasspayIpnMessage();
            $modelPayPal->processIpn($rawPostData);
        } else if (isset($ipnArray['txn_type']) AND ($ipnArray['txn_type'] == 'web_accept')) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process Support IPN - ');
            $modelPayPal = new Default_Model_PayPal_SupportIpnMessage();
            $modelPayPal->processIpn($rawPostData);
        } else if (isset($ipnArray['txn_type']) AND ($ipnArray['txn_type'] == 'subscr_signup')) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process SubscriptionSignup IPN - ');
            $modelPayPal = new Default_Model_PayPal_SubscriptionSignupIpnMessage();
            $modelPayPal->processIpn($rawPostData);
        } else if (isset($ipnArray['txn_type']) AND (($ipnArray['txn_type'] == 'subscr_cancel') || ($ipnArray['txn_type'] == 'subscr_failed'))) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process SubscriptionSignupCancel IPN - ');
            $modelPayPal = new Default_Model_PayPal_SubscriptionCancelIpnMessage();
            $modelPayPal->processIpn($rawPostData);
        } else if (isset($ipnArray['txn_type']) AND (($ipnArray['txn_type'] == 'subscr_eot'))) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - Subscription Ended Normaly, nothing to do -');
        } else if (isset($ipnArray['txn_type']) AND ($ipnArray['txn_type'] == 'subscr_payment')) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process SubscriptionPayment IPN - ');
            $modelPayPal = new Default_Model_PayPal_SubscriptionPaymentIpnMessage();
            $modelPayPal->processIpn($rawPostData);
        } else{
            Zend_Registry::get('logger')->info(__METHOD__ . ' - Start Process Normal IPN - ');
            $modelPayPal = new Default_Model_PayPal_IpnMessage();
            $modelPayPal->processIpn($rawPostData);
            
        }

    }
    
    private function _parseRawMessage($raw_post)
    {
        //log_message('error', "testing");
        if (empty($raw_post)) {
            return array();
        } # else:
        $parsedPost = array();
        $pairs = explode('&', $raw_post);
        foreach ($pairs as $pair) {
            list($key, $value) = explode('=', $pair, 2);
            $key = urldecode($key);
            $value = urldecode($value);
            # This is look for a key as simple as 'return_url' or as complex as 'somekey[x].property'
//            preg_match('/(\w+)(?:\[(\d+)\])?(?:\.(\w+))?/', $key, $key_parts);
            preg_match('/(\w+)(?:(?:\[|\()(\d+)(?:\]|\)))?(?:\.(\w+))?/', $key, $key_parts);
            switch (count($key_parts)) {
                case 4:
                    # Original key format: somekey[x].property
                    # Converting to $post[somekey][x][property]
                    if (false === isset($parsedPost[$key_parts[1]])) {
                        if (empty($key_parts[2]) && '0' != $key_parts[2]) {
                            $parsedPost[$key_parts[1]] = array($key_parts[3] => $value);
                        } else {
                            $parsedPost[$key_parts[1]] = array($key_parts[2] => array($key_parts[3] => $value));
                        }
                    } else {
                        if (false === isset($parsedPost[$key_parts[1]][$key_parts[2]])) {
                            if (empty($key_parts[2]) && '0' != $key_parts[2]) {
                                $parsedPost[$key_parts[1]][$key_parts[3]] = $value;
                            } else {
                                $parsedPost[$key_parts[1]][$key_parts[2]] = array($key_parts[3] => $value);
                            }
                        } else {
                            $parsedPost[$key_parts[1]][$key_parts[2]][$key_parts[3]] = $value;
                        }
                    }
                    break;
                case 3:
                    # Original key format: somekey[x]
                    # Converting to $post[somekey][x]
                    if (!isset($parsedPost[$key_parts[1]])) {
                        $parsedPost[$key_parts[1]] = array();
                    }
                    $parsedPost[$key_parts[1]][$key_parts[2]] = $value;
                    break;
                default:
                    # No special format
                    $parsedPost[$key] = $value;
                    break;
            }
            #switch
        }
        #foreach

        return $parsedPost;
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
