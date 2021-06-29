<?php /** @noinspection PhpUndefinedFieldInspection */

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

namespace Application\Controller;

use Application\Model\PayPal\IpnMessage;
use Application\Model\PayPal\MasspayIpnMessage;
use Application\Model\PayPal\PayoutIpnMessage;
use Application\Model\PayPal\SubscriptionCancelIpnMessage;
use Application\Model\PayPal\SubscriptionPaymentIpnMessage;
use Application\Model\PayPal\SubscriptionSignupIpnMessage;
use Application\Model\PayPal\SupportIpnMessage;
use Application\Model\Repository\PaypalIpnRepository;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 * Class GatewayController
 *
 * @package Application\Controller
 */
class GatewayController extends AbstractActionController
{

    protected $ipnRepository;
    protected $db;
    protected $config;
    protected $logger;

    public function __construct(AdapterInterface $db, PaypalIpnRepository $ipnRepository)
    {
        $this->ipnRepository = $ipnRepository;
        $this->db = $db;
        $this->config = $GLOBALS['ocs_config'];
        $this->logger = $GLOBALS['ocs_log'];
    }

    public function indexAction()
    {
    }

    /**
     * Official OCS API to receive messages from PayPal.
     */
    public function paypalAction()
    {
        $this->view = new ViewModel();
        $this->view->setTemplate('application/gateway/index');
        $this->view->setTerminal(true);

        // It is really important to receive the information in this way. In some cases Zend can destroy the information
        // when parsing the data
        $rawPostData = file_get_contents('php://input');

        $this->logger->info(__METHOD__ . ' - Start Process PayPal IPN - ');

        $ipnArray = $this->_parseRawMessage($rawPostData);

        //Save IPN in DB
        $ipnTable = $this->ipnRepository;
        $ipnTable->addFromIpnMessage($ipnArray, $rawPostData);

        try {
            //Switch betwee AdaptivePayment and Masspay
            if (isset($ipnArray['txn_type']) and ($ipnArray['txn_type'] == 'masspay')) {
                $this->logger->info(__METHOD__ . ' - Start Process Masspay IPN - ');
                $modelPayPal = new MasspayIpnMessage($this->db, $this->config, $this->logger);
                $modelPayPal->processIpn($rawPostData);
            } else {
                if (isset($ipnArray['txn_type']) and ($ipnArray['txn_type'] == 'web_accept')) {
                    $this->logger->info(__METHOD__ . ' - Start Process Support IPN - ');
                    $modelPayPal = new SupportIpnMessage(
                        $this->db, $this->config, $this->logger
                    );
                    $modelPayPal->processIpn($rawPostData);
                } else {
                    if (isset($ipnArray['txn_type']) and ($ipnArray['txn_type'] == 'subscr_signup')) {
                        $this->logger->info(__METHOD__ . ' - Start Process SubscriptionSignup IPN - ');
                        $modelPayPal = new SubscriptionSignupIpnMessage(
                            $this->db, $this->config, $this->logger
                        );
                        $modelPayPal->processIpn($rawPostData);
                    } else {
                        if (isset($ipnArray['txn_type']) and (($ipnArray['txn_type'] == 'subscr_cancel') || ($ipnArray['txn_type'] == 'subscr_failed') || ($ipnArray['txn_type'] == 'recurring_payment_suspended_due_to_max_failed_paym'))) {
                            $this->logger->info(__METHOD__ . ' - Start Process SubscriptionSignupCancel IPN - ');
                            $modelPayPal = new SubscriptionCancelIpnMessage(
                                $this->db, $this->config, $this->logger
                            );
                            $modelPayPal->processIpn($rawPostData);
                        } else {
                            if (isset($ipnArray['txn_type']) and (($ipnArray['txn_type'] == 'subscr_eot'))) {
                                $this->logger->info(__METHOD__ . ' - Subscription Ended Normaly, nothing to do -');
                            } else {
                                if (isset($ipnArray['txn_type']) and ($ipnArray['txn_type'] == 'subscr_payment')) {
                                    $this->logger->info(__METHOD__ . ' - Start Process SubscriptionPayment IPN - ');
                                    $modelPayPal = new SubscriptionPaymentIpnMessage(
                                        $this->db, $this->config, $this->logger
                                    );
                                    $modelPayPal->processIpn($rawPostData);
                                } else {
                                    $this->logger->info(__METHOD__ . ' - Start Process Normal IPN - ');
                                    $modelPayPal = new IpnMessage(
                                        $this->db, $this->config, $this->logger
                                    );
                                    $modelPayPal->processIpn($rawPostData);

                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exc) {
            $this->logger->err($exc->getTraceAsString());
        }

        return $this->view;
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

    /**
     * Official OCS API to receive messages from PayPal.
     */
    public function paypalpayoutAction()
    {
        $this->view = new ViewModel();
        $this->view->setTemplate('application/gateway/index');
        $this->view->setTerminal(true);

        try {
            // It is really important to receive the information in this way. In some cases Zend can destroy the information
            // when parsing the data
            $rawPostData = file_get_contents('php://input');
            $ipnArray = $this->_parseRawMessage($rawPostData);

            //Save IPN in DB
            $ipnTable = $this->ipnRepository;
            $ipnTable->addFromIpnMessage($ipnArray, $rawPostData);

            $this->logger->info(__METHOD__ . ' - Start Process PayPal Payout IPN - ');

            $this->logger->info(__METHOD__ . ' - Start Process Payout IPN - ');
            $modelPayPal = new PayoutIpnMessage($this->db, $this->config, $this->logger);
            $modelPayPal->processIpn($rawPostData);

        } catch (\Exception $exc) {
            //Do nothing...
            $this->logger->info(
                __METHOD__ . ' - Error by Processing PayPal Payout IPN - ExceptionTrace: ' . $exc->getTraceAsString()
            );
        }

        return $this->view;
    }

}
