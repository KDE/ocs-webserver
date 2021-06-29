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

namespace Library\Payment\PayPal\SubscriptionCancel;

use Application\Model\Repository\SupportRepository;
use Exception;
use Library\Payment\PayPal\Base;
use Library\Payment\PayPal\PaymentInterface;

/**
 * Class Ipn
 *
 * @package Library\Payment\PayPal\SubscriptionCancel
 */
abstract class Ipn extends Base
{

    const VERIFIED = 'VERIFIED';

    /** @var  array */
    protected $_dataIpn;
    /** @var  PaymentInterface */
    protected $_ipnMessage;

    protected $_dataRaw;

    /**
     * @param $rawData
     *
     * @throws Exception
     */
    public function processIpn($rawData)
    {
        if (false === $this->verifyIpnOrigin($rawData)) {
            $this->_logger->err(' ' . __FUNCTION__ . '::Abort Subscription IPN processing. IPN not verified: ' . $rawData);
            $this->_logger->info(' ' . __FUNCTION__ . '::Abort Subscription IPN processing. IPN not verified: ' . $rawData);

            return;
        }

        $this->_dataRaw = $rawData;
        $this->_dataIpn = $this->_parseRawMessage($rawData);
        $this->_ipnMessage = \Library\Payment\PayPal\Response::buildResponse($this->_dataIpn);

        if (false === $this->validateTransaction()) {
            $this->_logger->err(' ' . __FUNCTION__ . '::Abort Subscription IPN processing. Transaction not valid:' . $rawData);

            return;
        }

        $this->processSubscriptionCancel();
    }

    /**
     * @param string $rawDataIpn
     *
     * @return bool
     */
    public function verifyIpnOrigin($rawDataIpn)
    {
        $raw_post_array = explode('&', $rawDataIpn);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                $myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }
        // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        // Step 2: POST IPN data back to PayPal to validate
        $url = $this->_config->ipn->endpoint . '/webscr';
        //$ch = curl_init('https://ipnpb.paypal.com/cgi-bin/webscr');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        // In wamp-like environments that do not come bundled with root authority certificates,
        // please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set
        // the directory path of the certificate as shown below:
        // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
        if (!($res = curl_exec($ch))) {
            // error_log("Got " . curl_error($ch) . " when processing IPN data");
            $this->_logger->err(" " . __FUNCTION__ . "Got " . curl_error($ch) . " when processing IPN data");
            $this->_logger->info(" " . __FUNCTION__ . "Got " . curl_error($ch) . " when processing IPN data");
            curl_close($ch);
            exit;
        }
        curl_close($ch);

        if (strcmp($res, "VERIFIED") == 0) {
            // The IPN is verified, process it
            return true;
        } else {
            if (strcmp($res, "INVALID") == 0) {
                // IPN invalid, log for manual investigation
                return false;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function validateTransaction()
    {
        // Make sure the receiver email address is one of yours and the
        // amount of money is correct

        //return $this->_checkEmail() AND $this->_checkTxnId() AND $this->_checkAmount();
        return true;
    }

    protected function processSubscriptionCancel()
    {
        $this->_logger->info(' ' . __FUNCTION__ . ' IPN: ' . print_r($this->_ipnMessage, true));

        $this->_processSubscriptionCancel();

        $this->_logger->info(' ' . __FUNCTION__ . ' DONE');
    }

    /**
     * Transaction/Payment completed.
     *
     * For Mass Payments, this means that all of your payments have been claimed,
     * or after a period of 30 days, unclaimed payments have been returned to you.
     */
    protected function _processSubscriptionCancel()
    {
        $this->_logger->info(' ' . __FUNCTION__ . ' set Status');
        $Table = new SupportRepository($this->db);
        $Table->deactivateSupportSubscriptionSignupFromResponse($this->_ipnMessage);
    }

    public function getCharset($rawDataIpn)
    {
        $matches = array();

        preg_match('|charset=(.*?)\&|', $rawDataIpn, $matches);

        return $matches[1];
    }

    /**
     * Check email address for validity.
     * Override this method to make sure you are the one being paid.
     * Throw an Exception if data is invalid or other things go wrong.
     *
     * $this->_dataIpn['receiver_email'] = The email who is about to receive payment.
     */
    protected function _checkEmail()
    {
        // check that receiver_email is your Primary PayPal email

        $this->_logger->info('Not doing _checkEmail(' . $this->_dataIpn['receiver_email'] . ')');

        return false;
    }

    /**
     * Check txnId has not already been used.
     * Override this method to ensure txnId is not a duplicate.
     * Throw an Exception if data is invalid or other things go wrong.
     *
     * $this->_dataIpn['txn_id'] = The transaction ID from paypal.
     */
    protected function _checkTxnId()
    {
        // check that txn_id has not been previously processed

        $this->_logger->info('Not doing _checkTxnId(' . $this->_ipnMessage->getTransactionId() . ')');

        return false;
    }

    /**
     * Check that the amount/currency is correct for item_id.
     * You should override this method to ensure the amount is correct.
     * Throw an Exception if data is invalid or other things go wrong.
     *
     * $this->_dataIpn['item_number'] = The item number
     * $this->_dataIpn['mc_gross']    = The amount being paid
     * $this->_dataIpn['mc_currency'] = Currency code of amount
     */
    protected function _checkAmount()
    {
        // check that payment_amount/payment_currency are correct

        $this->_logger->info('Not doing _checkAmount(' . $this->_dataIpn['mc_gross'] . ', ' . $this->_dataIpn['mc_currency'] . ')');

        return false;
    }

} 