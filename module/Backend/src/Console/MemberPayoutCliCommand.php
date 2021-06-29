<?php /** @noinspection PhpUndefinedFieldInspection */

/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Backend\Console;

use Application\Model\PayPal\Gateway;
use Application\Model\Repository\MemberPayoutRepository;
use Application\View\Helper\BuildProductUrl;
use Exception;
use Laminas\Config\Config;
use Laminas\Db\Sql\Expression;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Log\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MemberPayoutCliCommand
 *
 * @package Backend\Console
 */
class MemberPayoutCliCommand extends Command
{
    // the name of the command (the part after "scripts/console")
    public static $ACTION_PAYOUT = "payout";
    public static $CONTEXT_ALL = "all";
    public static $CONTEXT_PREPARE = "prepare";
    public static $NVP_MODULE_ADAPTIVE_PAYMENT = "/AdaptivePayments";
    public static $NVP_ACTION_PAY = "/Pay";
    public static $PAYOUT_STATUS_NEW = 0;
    public static $PAYOUT_STATUS_REQUESTED = 1;
    public static $PAYOUT_STATUS_PROCESSED = 10;
    public static $PAYOUT_STATUS_COMPLETED = 100;
    public static $PAYOUT_STATUS_DENIED = 30;
    public static $PAYOUT_STATUS_ERROR = 99;
    public static $PAYOUT_STATUS_PAYPAL_API_ERROR = 999;
    protected static $defaultName = 'app:payout';
    public $headers;
    /** @var Config */
    protected $_config;
    /** @var Logger */
    protected $_logger;

    protected $db;

    public function __construct()
    {
        parent::__construct();

        $this->db = GlobalAdapterFeature::getStaticAdapter();
    }

    public function isSuccessful($response)
    {
        //return $response['responseEnvelope']['ack'] == 'Success';
        return (strpos($response, 'ACK=Success') != false);
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Starts the Payout for all payouts with status 0 or 999.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Starts the Payout for all payouts with status 0 or 999.')
            ->addArgument('action', InputArgument::REQUIRED, 'The action name.')
            ->addArgument('context', InputArgument::REQUIRED, 'The context name.');
    }

    /**
     * Run php code as cronjob.
     * I.e.:
     * php scripts/application.php app:payout payout all
     *
     * @see CliInterface::runAction()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initVars();

        $this->initHeaders();

        echo "Start runAction\n";
        //echo "AppEnv: " . APPLICATION_ENV . "\n";
        //echo "SandboxActive: " . $this->_config->third_party->paypal->sandbox->active . "\n";
        //echo "Endpoint: " . $this->_config->third_party->paypal->masspay->endpoint . "\n";
        //echo "Test: " . $this->_config->third_party->paypal->test . "\n";

        $action = $input->getArgument('action');
        $context = $input->getArgument('context');

        //echo "action: " . $action . "\n";
        //echo "context: " . $context . "\n";

        if (isset($action) && $action == $this::$ACTION_PAYOUT && isset($context) && $context == $this::$CONTEXT_ALL) {
            $this->payoutMembers();
        } else {
            if (isset($action) && $action == $this::$ACTION_PAYOUT && isset($context) && $context == $this::$CONTEXT_PREPARE) {
                $this->prepareMasspaymentTable();
            }
        }
    }

    public function initVars()
    {
        //init
        $this->_config = $GLOBALS['ocs_config'];
        $this->_logger = $GLOBALS['ocs_log'];
    }

    public function initHeaders()
    {
        echo "Start initHeaders";

        $this->headers = array(
            "X-PAYPAL-SECURITY-USERID: " . $this->_config->third_party->paypal->security->userid,
            "X-PAYPAL-SECURITY-PASSWORD: " . $this->_config->third_party->paypal->security->password,
            "X-PAYPAL-SECURITY-SIGNATURE: " . $this->_config->third_party->paypal->security->signature,
            "X-PAYPAL-REQUEST-DATA-FORMAT: NV",
            "X-PAYPAL-RESPONSE-DATA-FORMAT: NV",
            "X-PAYPAL-APPLICATION-ID: " . $this->_config->third_party->paypal->application->id,
        );
    }

    private function payoutMembers()
    {
        echo "payoutMembers()\n";

        //Select all members for payout and write them in the payout table, ignore allways inserted members
        //$this->prepareMasspaymentTable();

        //get payouts
        $allPayouts = $this->getPayouts();

        //send request for < 250 payouts
        $this->startMassPay($allPayouts);
    }

    private function getPayouts()
    {
        echo "getPayouts\n";
        $db = $this->db;
        $sql = "SELECT * FROM member_payout p WHERE p.status = " . $this::$PAYOUT_STATUS_NEW . " OR p.status = " . $this::$PAYOUT_STATUS_PAYPAL_API_ERROR;
        $stmt = $db->query($sql);
        $payouts = $stmt->execute();

        $payoutsArray = array();

        foreach ($payouts as $payout) {
            $payoutsArray[] = $payout;
        }

        return $payoutsArray;
    }

    private function startMassPay($payoutsArray)
    {
        echo "startMassPay\n\n";
        if (!$payoutsArray || count($payoutsArray) == 0) {
            echo "Nothing to do...\n\n";
            die;
            //throw new Exception("Method startMassPay needs array of payouts.");
        }
        $payoutTable = new MemberPayoutRepository($this->db);
        $log = $this->_logger;

        $log->info('********** Start PayPal Masspay **********\n');
        $log->info(__FUNCTION__);
        $log->debug(APPLICATION_ENV);

        echo('********** Start PayPal Masspay **********'. PHP_EOL);
        //echo(__FUNCTION__);
        //echo(APPLICATION_ENV);

        foreach ($payoutsArray as $payout) {
            $amount = $payout['amount'];
            $mail = $payout['paypal_mail'];
            $id = $payout['id'];
            $yearmonth = $payout['yearmonth'];
            /*if($this->_config->third_party->paypal->sandbox->active) {
                $mail = "paypal-buyer@pling.com";
            }*/

            $result = $this->sendPayout($mail, $amount, $id, $yearmonth);

            if (null != $result) {

                //echo "Result: " . print_r($result->getRawMessage());
                $payKey = $result->getPaymentId();

                $successful = $result->isSuccessful();

                //echo $payKey."\n";
                //echo $successful."\n";

                if ($successful) {
                    echo "Payout Successful". PHP_EOL;
                    //mark payout as requested
                    $payoutTable->update(
                        array(
                            "payment_reference_key"   => $payKey,
                            "status"                  => $this::$PAYOUT_STATUS_REQUESTED,
                            "timestamp_masspay_start" => new Expression('Now()'),
                            "payment_raw_error"       => '',
                            "payment_status"          => '',
                        ), "id = " . $payout['id']
                    );
                } else {
                    echo "Payout Not Successful".PHP_EOL;
                    //mark payout as failed
                    $payoutTable->update(
                        array(
                            "payment_reference_key"   => $payKey,
                            "status"                  => $this::$PAYOUT_STATUS_PAYPAL_API_ERROR,
                            "timestamp_masspay_start" => new Expression('Now()'),
                            "payment_raw_error"       => print_r($result->getRawMessage(), true),
                            "payment_status"          => 'ERROR',
                        ), "id = " . $payout['id']
                    );
                }
            } else {

                echo "Result: PayPal-API-Error". PHP_EOL;
                //mark payout as 999 = API-Error
                $payoutTable->update(
                    array(
                        "status"                  => $this::$PAYOUT_STATUS_PAYPAL_API_ERROR,
                        "timestamp_masspay_start" => new Expression('Now()'),
                    ), "id = " . $payout['id']
                );
            }
        }

        return true;
    }

    private function sendPayout($receiverMail, $amount, $trackingId, $yearmonth)
    {
        $paymentGateway = $this->createPaymentGateway("paypal");
        $response = null;
        try {
            $response = $paymentGateway->requestPaymentForPayout(
                $this->_config->third_party->paypal->facilitator_fee_receiver, $receiverMail, $amount, $trackingId, $yearmonth
            );
        } catch (Exception $e) {
            $log = $this->_logger;
            $log->info('Exception: payment error. Message: ' . print_r($e));
            //echo('Exception: payment error. Message: ' . print_r($e));

            //throw new Zend_Controller_Action_Exception('payment error', 500, $e);

            //Set status to 999 (or we set the original paypal error code)
            //mark payout as requested
            return null;
        }

        return $response;
    }

    /**
     * @param string $paymentProvider
     *
     * @return Gateway
     * @throws Exception
     */
    protected function createPaymentGateway($paymentProvider)
    {
        $httpHost = "www.opendesktop.org";
        if ($this->_config->third_party->paypal->sandbox->active) {
            $httpHost = "www.pling.cc";
        }
        /** @var Config $config */
        $config = $GLOBALS['ocs_config'];
        $helperBuildProductUrl = new BuildProductUrl();
        switch ($paymentProvider) {
            case 'paypal':
                $paymentGateway = new Gateway($this->db, $config->third_party->paypal, $this->_logger);
                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/paypalpayout');
                //                $paymentGateway->setIpnNotificationUrl('http://' . $httpHost . '/gateway/paypal?XDEBUG_SESSION_START=1');
                $paymentGateway->setCancelUrl('http://' . $httpHost);
                $paymentGateway->setReturnUrl('http://' . $httpHost);
                break;

            default:
                throw new Exception('No known payment provider found in parameters.');
                break;
        }

        return $paymentGateway;
    }

    private function prepareMasspaymentTable()
    {
        //echo "prepareMasspaymentTable()\n";
        $db = $this->db;

        $sql = "SELECT * FROM `stat_dl_payment_last_month` `s` WHERE `s`.`amount` >= 1";

        $stmt = $db->query($sql);
        $payouts = $stmt->fetchAll();

        //echo "Select " . count($payouts) . " payouts. Sql: " . $sql . "\n";

        //Insert/Update users in table project_rating
        foreach ($payouts as $payout) {
            //Insert item in payment table
            //INSERT IGNORE INTO `pling`.`payout` (`yearmonth`, `member_id`, `amount`) VALUES ('201612', '223978', '181.0500');
            $sql = "INSERT IGNORE INTO `member_payout` (`yearmonth`, `member_id`, `mail`, `paypal_mail`, `amount`, `num_downloads`, `created_at`) VALUES ('" . $payout['yearmonth'] . "','" . $payout['member_id'] . "','" . $payout['mail'] . "','" . $payout['paypal_mail'] . "'," . $payout['amount'] . "," . $payout['num_downloads'] . ", NOW()" . ")";
            $stmt = $db->query($sql);
            $stmt->execute();
        }
    }
}