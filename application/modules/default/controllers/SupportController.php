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
class SupportController extends Local_Controller_Action_DomainSwitch
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
    }

    public function showAction()
    {
        $this->view->authMember = $this->_authMember;
        $this->_helper->viewRenderer('index');
        $this->indexAction();
    }


    public function donateAction()
    {

        $this->view->authMember = $this->_authMember;

        $request = Zend_Controller_Front::getInstance()->getRequest();

        $httpHost = $this->getRequest()->getHttpHost();
        $this->view->urlPay =  'https://' . $httpHost . '/donation/pay';
        $this->view->amount = (float)$this->getParam('amount', 1);
        $this->view->comment = html_entity_decode(strip_tags($this->getParam('comment'), null), ENT_QUOTES, 'utf-8');
        $this->view->provider =
            mb_strtolower(html_entity_decode(strip_tags($this->getParam('provider'), null), ENT_QUOTES, 'utf-8'),
                'utf-8');

    }
    
    public function payAction()
    {
        $this->_helper->layout()->disableLayout();
        
        //get parameter
        $amount_predefined = (float)$this->getParam('amount_predefined', 1);
        $amount_handish  = (float)$this->getParam('amount_handish', 1);
        
        $amount = 0;
        if(null != ($this->getParam('amount_predefined') && $amount_predefined > 0)) {
            $amount = $amount_predefined;
        } else {
            $amount = $amount_handish;
        }
        
        $comment = Default_Model_HtmlPurify::purify($this->getParam('comment'));
        $paymentProvider =
            mb_strtolower(html_entity_decode(strip_tags($this->getParam('provider'), null), ENT_QUOTES, 'utf-8'),
                'utf-8');
        $httpHost = $this->getRequest()->getHttpHost();
        $config = Zend_Registry::get('config');
        
        $form_url = $config->third_party->paypal->form->endpoint . '/cgi-bin/webscr';
        $ipn_endpoint =  'http://'.$httpHost.'/gateway/paypal';
        $return_url_success =  'http://'.$httpHost.'/donate/paymentok';
        $return_url_cancel =   'http://'.$httpHost.'/donate/paymentcancel';
        $merchantid = $config->third_party->paypal->merchantid;
        
        $this->view->form_endpoint = $form_url;
        $this->view->form_ipn_endpoint = $ipn_endpoint;
        $this->view->form_return_url_ok = $return_url_success;
        $this->view->form_return_url_cancel = $return_url_cancel;
        $this->view->form_merchant = $merchantid;
        $this->view->member_id = $this->_authMember->member_id;
        $this->view->transaction_id = $this->_authMember->member_id . '_' . time();
        $this->view->amount = $amount;
        
        //Add pling
        $modelDonation = new Default_Model_DbTable_Support();
        $donationId = $modelDonation->createNewSupport($this->view->transaction_id, $this->_authMember->member_id, $amount);
        
        
        /**
        $paymentGateway = $this->createPaymentGateway($paymentProvider);
        //Receiver Data
        $opendesktopdata = array();
        $opendesktopdata['mail'] = $config->resources->mail->defaultFrom->email;
        //$opendesktopdata['firstname'] = "";
        $opendesktopdata['lastname'] = $config->resources->mail->defaultFrom->name;
        $opendesktopdata['paypal_mail'] = $config->third_party->paypal->facilitator_fee_receiver;
        $opendesktopdata['project_id'] = 0;
        $opendesktopdata['title'] = $config->resources->mail->defaultFrom->name;
        
        $paymentGateway->getUserDataStore()->generateFromArray($opendesktopdata);

        $requestMessage = 'Thank you for supporting: ' . $paymentGateway->getUserDataStore()->getProductTitle();

        $response = null;
        try {
            $response = $paymentGateway->requestPayment($amount, $requestMessage);
            $this->view->checkoutEndpoint = $paymentGateway->getCheckoutEndpoint();
            $this->view->paymentKey = $response->getPaymentId();
            $this->_helper->viewRenderer->setRender('pay_' . $paymentProvider);
        } catch (Exception $e) {
            throw new Zend_Controller_Action_Exception('payment error', 500, $e);
        }

        if (false === $response->isSuccessful()) {
            throw new Zend_Controller_Action_Exception('payment failure', 500);
        }

        $memberId = $this->_authMember->member_id;

        //Add pling
        $modelDonation = new Default_Model_DbTable_Donation();
        $donationId = $modelDonation->createNewDonationFromResponse($response, $memberId, $amount);

        if (false == empty($comment)) {
            $modelComments = new Default_Model_ProjectComments();
            $dataComment = array(
                'comment_type'      => Default_Model_DbTable_Comments::COMMENT_TYPE_DONATION,
                'comment_target_id' => 0,
                'comment_member_id' => $memberId,
                'comment_pling_id'  => $donationId,
                'comment_text'      => $comment
            );
            $modelComments->save($dataComment);
        }
        **/
    }

    /**
     * @param string $paymentProvider
     *
     * @throws Zend_Controller_Exception
     * @return Local_Payment_GatewayInterface
     */
    protected function createPaymentGateway($paymentProvider)
    {
        $httpHost = $this->getRequest()->getHttpHost();
        /** @var Zend_Config $config */
        $config = Zend_Registry::get('config');
        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        switch ($paymentProvider) {
            case 'paypal':
                $paymentGateway = new Default_Model_PayPal_Gateway($config->third_party->paypal);
                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/paypal');
                //                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/paypal?XDEBUG_SESSION_START=1');
                $paymentGateway->setCancelUrl('http://' . $httpHost . '/donate/paymentcancel');
                $paymentGateway->setReturnUrl('http://' . $httpHost . '/donate/paymentok');
                break;

            case 'dwolla':
                $paymentGateway = new Default_Model_Dwolla_Gateway($config->third_party->dwolla);
                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/dwolla');
                $paymentGateway->setReturnUrl('http://' . $httpHost . '/donate/dwolla');
                break;

            case 'amazon':
                $paymentGateway = new Default_Model_Amazon_Gateway($config->third_party->amazon);
                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/amazon');
                $paymentGateway->setCancelUrl('http://' . $httpHost . '/donate/paymentcancel');
                $paymentGateway->setReturnUrl('http://' . $httpHost . '/donate/paymentok');
                break;

            default:
                throw new Zend_Controller_Exception('No known payment provider found in parameters.');
                break;
        }

        return $paymentGateway;
    }

    public function dwollaAction()
    {
        $modelPling = new Default_Model_DbTable_Plings();
        $plingData = $modelPling->fetchRow(array('payment_reference_key = ?' => $this->getParam('checkoutId')));
        $plingData->payment_transaction_id = (int)$this->getParam('transaction');
        $plingData->save();

        if ($this->_getParam('status') == 'Completed') {
            $this->_helper->viewRenderer('paymentok');
            $this->paymentokAction();
        } else {
            $this->_helper->viewRenderer('paymentcancel');
            $this->paymentcancelAction();
        }
    }

    public function paymentokAction()
    {
        //$this->_helper->layout()->disableLayout();
        $this->view->paymentStatus = 'success';
        $this->view->paymentMessage = 'Payment successful.';
    }

    public function paymentcancelAction()
    {
        //$this->_helper->layout()->disableLayout();
        $this->view->paymentStatus = 'danger';
        $this->view->paymentMessage = 'Payment canceled.';
    }


    /**
     * @param $errors
     *
     * @return array
     */
    protected function getErrorMessages($errors)
    {
        $messages = array();
        foreach ($errors as $element => $row) {
            if (!empty($row) && $element != 'submit') {
                foreach ($row as $validator => $message) {
                    $messages[$element][] = $message;
                }
            }
        }

        return $messages;
    }

    protected function _initResponseHeader()
    {
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";

        $this->getResponse()->setHeader('X-FRAME-OPTIONS', 'SAMEORIGIN',
                true)//            ->setHeader('Last-Modified', $modifiedTime, true)
             ->setHeader('Expires', $expires, true)->setHeader('Pragma', 'no-cache', true)
             ->setHeader('Cache-Control', 'private, no-cache, must-revalidate', true)
        ;
    }


}
