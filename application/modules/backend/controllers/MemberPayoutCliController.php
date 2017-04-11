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

    /**
     * Run php code as cronjob.
     * I.e.:
     * /usr/bin/php /var/www/pling.it/pling/scripts/cron.php -a /backend/member-payout-cli/run/action/payout/context/all >> /var/www/ocs-www/logs/masspay.log $
     *
     * @see Local_Controller_Action_CliInterface::runAction()
     */
    public function runAction() {
        $this->initVars();
        
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
        $payouts = array();
        
        //loop over all payouts, send max 250 per loop
        $i = 0;
        foreach ($allPayouts as $payout) {
            if($i < 250) {
                $payouts[] = $payout;
            } else {
                //start payout with Masspay
                $this->startMassPay($payouts);
                
                //reset counter and array for restart
                $i = 0;
                $payouts = array();
            }
            $i++;
        }
        
        if($i>0) {
            //send request for < 250 payouts
            $this->startMassPay($payouts);
        }
        
    }
    
    private function prepareMasspaymentTable() {
        echo "prepareMasspaymentTable()\n";
        $db = Zend_Db_Table::getDefaultAdapter();

        $sql = "SELECT * FROM stat_dl_payment_last_month s";
        
        if(!$this->_config->third_party->paypal->sandbox->active) {
            $sql.=" WHERE s.num_downloads > 100";
        }

        $stmt = $db->query($sql);
        $payouts = $stmt->fetchAll();
        
        echo "Select ".count($payouts)." payouts. Sql: ".$sql."\n";
        
        //Insert/Update users in table project_rating
        foreach ($payouts as $payout) {
            //Insert item in payment table
            //INSERT IGNORE INTO `pling`.`payout` (`yearmonth`, `member_id`, `amount`) VALUES ('201612', '223978', '181.0500');
            $sql = "INSERT IGNORE INTO `payout` (`yearmonth`, `member_id`, `mail`, `paypal_mail`, `amount`, `num_downloads`) VALUES ('" . $payout['yearmonth'] . "','" . $payout['member_id'] . "','" . $payout['mail'] . "','" . $payout['paypal_mail'] . "'," . $payout['amount'] . "," . $payout['num_downloads'] . ")";
            $stmt = $db->query($sql);
            $stmt->execute();
        }
    }
    
    private function getPayouts() {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT * FROM payout p WHERE p.status = ".$this::$PAYOUT_STATUS_NEW;
        $stmt = $db->query($sql);
        $payouts = $stmt->fetchAll();
        
        $payoutsArray = array();
        
        foreach ($payouts as $payout) {
            $payoutsArray[] = $payout;
        }
        return $payoutsArray;
    }

    private function startMassPay($payoutsArray) {
        if(!$payoutsArray || count($payoutsArray) == 0) {
            throw new Exception("Method startMassPay needs array of payouts.");
        }
        $payoutTable = new Default_Model_DbTable_Payout();
        $log = $this->_logger;

        $log->info('********** Start PayPal Masspay **********\n');
        $log->info(__FUNCTION__);
        $log->debug(APPLICATION_ENV);
        
        echo('********** Start PayPal Masspay **********');
        echo(__FUNCTION__);
        echo(APPLICATION_ENV);
        
        //curl 
        $nvpreq = "METHOD=MassPay&RECEIVERTYPE=EmailAddress&CURRENCYCODE=USD&EMAILSUBJECT=You have a payment from opendesktop.org&VERSION=90&PWD=".$this->_config->third_party->paypal->security->password."&USER=".$this->_config->third_party->paypal->security->userid."&SIGNATURE=".$this->_config->third_party->paypal->security->signature;


        $i = 0;
        foreach ($payoutsArray as $payout) {
            //$mpUrl .= "-d L_EMAIL" . $i . "=" . $payout['paypal_mail'] . "-d L_AMT" . $i . "=" . $payout['amount'] . " -d L_NOTE" . $i . "=Opendesktop.org: Your monthly payout for " . $payout['num_downloads']. " downloads.";
            $nvpreq .= "&L_UNIQUEID".$i."=".$payout['id']."&L_EMAIL" . $i . "=maker@pling.com&L_AMT" . $i . "=" . $payout['amount'];
            $i++;
            //mark payout as requested
            $payoutTable->update(array("status" => $this::$PAYOUT_STATUS_REQUESTED, "timestamp_masspay_start" => new Zend_Db_Expr('Now()')), "id = " . $payout['id']);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSLVERSION,6);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, $this->_config->third_party->paypal->masspay->endpoint);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $nvpreq);
        $response = curl_exec($curl);
        //$response = null;
        
        
        if( !$response)
        {
            echo 'Masspay failed: ' . curl_error($curl) . '(' . curl_errno($curl) .')\n\n\n';
            curl_close($curl);
            return false;
        }
        curl_close($curl);
        
        
        echo "Url: ". $this->_config->third_party->paypal->masspay->endpoint . $nvpreq."\n\n";
        
        echo "\n\nResponse: ".$response."\n\n";
        
        $log->info('********** Finished PayPal Masspay *********');
        $log->info('Response: ' . print_r($response, true));
        echo '********** Finished PayPal Masspay **********' . print_r($response, true)."\n\n";;

        if (false === $this->isSuccessful($response)) {
            throw new Exception('PayPal Masspay request failed. Request response:' . print_r($response, true));
        }

        return $response;
    }
    
    public function isSuccessful($response)
    {
        //return $response['responseEnvelope']['ack'] == 'Success';
        return (strpos($response, 'ACK=Success') != false);
    }

}
