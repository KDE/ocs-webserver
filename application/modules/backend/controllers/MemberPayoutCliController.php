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
 * */
class Backend_MemberPayoutCliController extends Local_Controller_Action_CliAbstract {

    public static $ACTION_PAYOUT = "payout";
    public static $CONTEXT_ALL = "all";
    public static $NVP_MODULE_ADAPTIVE_PAYMENT = "/AdaptivePayments";
    public static $NVP_ACTION_PAY = "/Pay";
    
    public static $PAYOUT_STATUS_NEW = 0;
    public static $PAYOUT_STATUS_REQUESTED = 1;
    public static $PAYOUT_STATUS_PROCESSED = 10;
    public static $PAYOUT_STATUS_COMPLETED = 100;
    public static $PAYOUT_STATUS_DENIED = 30;
    public static $PAYOUT_STATUS_ERROR = 99;
    
    
    /** @var Zend_Config */
    protected $_config;
    
    /** @var \Zend_Log */
    protected $_logger;
    
    public $headers;
    
    public function initHeaders(){
        echo "Start initHeaders";
        
        $this->headers = array(
            "X-PAYPAL-SECURITY-USERID: ".$this->_config->third_party->paypal->security->userid ,
            "X-PAYPAL-SECURITY-PASSWORD: ".$this->_config->third_party->paypal->security->password ,
            "X-PAYPAL-SECURITY-SIGNATURE: ".$this->_config->third_party->paypal->security->signature ,
            "X-PAYPAL-REQUEST-DATA-FORMAT: NV",
            "X-PAYPAL-RESPONSE-DATA-FORMAT: NV",
            "X-PAYPAL-APPLICATION-ID: ".$this->_config->third_party->paypal->application->id,
        );
        
    }

    /**
     * Run php code as cronjob.
     * I.e.:
     * /usr/bin/php /var/www/pling.it/pling/scripts/cron.php -a /backend/member-payout-cli/run/action/payout/context/all >> /var/www/ocs-www/logs/masspay.log $
     *
     * @see Local_Controller_Action_CliInterface::runAction()
     */
    public function runAction() {
        $this->initVars();
        
        $this->initHeaders();
        
        echo "Start runAction\n";
        echo "AppEnv: " . APPLICATION_ENV . "\n";
        echo "SandboxActive: " . $this->_config->third_party->paypal->sandbox->active . "\n";
        echo "Endpoint: " . $this->_config->third_party->paypal->masspay->endpoint . "\n";
        echo "Test: " . $this->_config->third_party->paypal->test . "\n";

        $action = $this->getParam('action');
        $context = $this->getParam('context');

        echo "action: " . $action . "\n";
        echo "context: " . $context . "\n";
        if (isset($action) && $action == $this::$ACTION_PAYOUT && isset($context) && $context == $this::$CONTEXT_ALL) {
            $this->payoutMembers();
        }
    }

    public function initVars() {
        //init
        $this->_config = Zend_Registry::get('config');
        $this->_logger = Zend_Registry::get('logger');
    }

    private function payoutMembers() {
        echo "payoutMembers()\n";
        
        //Select all members for payout and write them in the payout table, ignore allways inserted members
        $this->prepareMasspaymentTable();
        
        //get payouts
        $allPayouts = $this->getPayouts();
        
        //send request for < 250 payouts
        $this->startMassPay($allPayouts);
        
    }
    
    private function prepareMasspaymentTable() {
        echo "prepareMasspaymentTable()\n";
        $db = Zend_Db_Table::getDefaultAdapter();

        $sql = "SELECT * FROM stat_dl_payment_last_month s WHERE s.amount >= 1";
        
        $stmt = $db->query($sql);
        $payouts = $stmt->fetchAll();
        
        echo "Select ".count($payouts)." payouts. Sql: ".$sql."\n";
        
        //Insert/Update users in table project_rating
        foreach ($payouts as $payout) {
            //Insert item in payment table
            //INSERT IGNORE INTO `pling`.`payout` (`yearmonth`, `member_id`, `amount`) VALUES ('201612', '223978', '181.0500');
            $sql = "INSERT IGNORE INTO `member_payout` (`yearmonth`, `member_id`, `mail`, `paypal_mail`, `amount`, `num_downloads`, `created_at`) VALUES ('" . $payout['yearmonth'] . "','" . $payout['member_id'] . "','" . $payout['mail'] . "','" . $payout['paypal_mail'] . "'," . $payout['amount'] . "," . $payout['num_downloads'] . ", NOW()" . ")";
            $stmt = $db->query($sql);
            $stmt->execute();
        }
    }
    
    private function getPayouts() {
        echo "getPayouts";
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT * FROM member_payout p WHERE p.status = ".$this::$PAYOUT_STATUS_NEW;
        $stmt = $db->query($sql);
        $payouts = $stmt->fetchAll();
        
        $payoutsArray = array();
        
        foreach ($payouts as $payout) {
            $payoutsArray[] = $payout;
        }
        return $payoutsArray;
    }

    private function startMassPay($payoutsArray) {
        echo "startMassPay";
        if(!$payoutsArray || count($payoutsArray) == 0) {
            throw new Exception("Method startMassPay needs array of payouts.");
        }
        $payoutTable = new Default_Model_DbTable_MemberPayout();
        $log = $this->_logger;

        $log->info('********** Start PayPal Masspay **********\n');
        $log->info(__FUNCTION__);
        $log->debug(APPLICATION_ENV);
        
        echo('********** Start PayPal Masspay **********');
        echo(__FUNCTION__);
        echo(APPLICATION_ENV);
        
        foreach ($payoutsArray as $payout) {
            $amount = $payout['amount'];
            $mail = $payout['paypal_mail'];
            $id = $payout['id'];
            if($this->_config->third_party->paypal->sandbox->active) {
                $mail = "paypal-buyer@pling.com";
            }
            
            $result = $this->sendPayout($mail, $amount, $id);
            
            echo "Result: " . print_r($result->getRawMessage());
            $payKey = $result->getPaymentId();
            
            
            //mark payout as requested
            $payoutTable->update(array("payment_reference_key" => $payKey, "status" => $this::$PAYOUT_STATUS_REQUESTED, "timestamp_masspay_start" => new Zend_Db_Expr('Now()')), "id = " . $payout['id']);
            
        }

        return true;
    }
    
    public function isSuccessful($response)
    {
        //return $response['responseEnvelope']['ack'] == 'Success';
        return (strpos($response, 'ACK=Success') != false);
    }
    
    private function sendPayout($receiverMail, $amount, $trackingId)
    {
        $paymentGateway = $this->createPaymentGateway("paypal");
        $response = null;
        try {
            $response = $paymentGateway->requestPaymentForPayout($this->_config->third_party->paypal->facilitator_fee_receiver, $receiverMail, $amount, $trackingId);
        } catch (Exception $e) {
            throw new Zend_Controller_Action_Exception('payment error', 500, $e);
        }

        if (false === $response->isSuccessful()) {
            throw new Zend_Controller_Action_Exception('payment failure', 500);
        }
        
        return $response;
    }

    /**
     * @param string $paymentProvider
     *
     * @throws Zend_Controller_Exception
     * @return Local_Payment_GatewayInterface
     */
    protected function createPaymentGateway($paymentProvider)
    {
        $httpHost = "www.opendesktop.org";
        if($this->_config->third_party->paypal->sandbox->active) {
            $httpHost = "www.pling.cc";
        }
        /** @var Zend_Config $config */
        $config = Zend_Registry::get('config');
        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        switch ($paymentProvider) {
            case 'paypal':
                $paymentGateway = new Default_Model_PayPal_Gateway($config->third_party->paypal);
                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/paypalpayout');
//                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/paypal?XDEBUG_SESSION_START=1');
                $paymentGateway->setCancelUrl('http://' . $httpHost);
                $paymentGateway->setReturnUrl('http://' . $httpHost);
                break;

            case 'dwolla':
                $paymentGateway = new Default_Model_Dwolla_Gateway($config->third_party->dwolla);
                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/dwolla');
//                $paymentGateway->setIpnNotificationUrl('http://' . $_SERVER ['HTTP_HOST'] . '/gateway/dwolla?XDEBUG_SESSION_START=1');
                $paymentGateway->setReturnUrl($helperBuildProductUrl->buildProductUrl($this->_projectId, 'dwolla', null,
                    true));
                break;

            case 'amazon':
                $paymentGateway = new Default_Model_Amazon_Gateway($config->third_party->amazon);
                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/amazon');
//                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/amazon?XDEBUG_SESSION_START=1');
                $paymentGateway->setCancelUrl($helperBuildProductUrl->buildProductUrl($this->_projectId,
                    'paymentcancel', null, true));
                $paymentGateway->setReturnUrl($helperBuildProductUrl->buildProductUrl($this->_projectId, 'paymentok',
                    null, true));
                break;

            default:
                throw new Zend_Controller_Exception('No known payment provider found in parameters.');
                break;
        }

        return $paymentGateway;
    }
    

}
