<?php
/** @noinspection PhpUnused */

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

namespace Library\Payment\PayPal\AdaptivePayment;

use Library\Payment\Exception;
use Library\Payment\GatewayInterface;
use Library\Payment\PayPal\Base;
use Library\Payment\UserDataInterface;

/**
 * Class Gateway
 *
 * @package Library\Payment\PayPal\AdaptivePayment
 */
abstract class Gateway extends Base implements GatewayInterface
{

    const ATTRIBUTE_FALSE = 'false';
    const ATTRIBUTE_TRUE = 'true';
    const ATTRIBUTE_FEE_PAYER_SENDER = 'SENDER';
    const ATTRIBUTE_FEE_PAYER_PRIMARY = 'PRIMARYRECEIVER';
    const ATTRIBUTE_FEE_PAYER_EACH = 'EACHRECEIVER';
    const ATTRIBUTE_FEE_PAYER_SECONDARY = 'SECONDARYONLY';
    const ATTRIBUTE_CURRENCY_USD = 'USD';
    const ATTRIBUTE_ACTION_PAY = 'PAY';

    const SUCCESS = 'Success';
    const VERIFIED = 'VERIFIED';
    const RESPONSE_ENVELOPE_ACK = 'responseEnvelope_ack';
    const ACCOUNT_STATUS = 'accountStatus';

    const API_ADAPTIVE_PAYMENTS = 'AdaptivePayments';

    const OPERATION_PAY = 'Pay';

    protected $_config;
    protected $_logger;
    protected $_ipnNotificationUrl;
    protected $_cancelUrl;
    protected $_returnUrl;
    /** @var UserDataInterface */
    protected $_paymentUserData;
    protected $_dataIpn;

    /**
     * @param float $amount
     * @param null  $requestMsg
     * @param null  $senderMail
     *
     * @return ResponsePayRequest
     * @throws Exception
     */
    public function requestPayment($amount, $requestMsg = null, $senderMail = null)
    {
        $log = $this->_logger;

        $log->info('********** Start PayPal Payment **********');
        $log->info(__FUNCTION__);
        //$log->debug(APPLICATION_ENV);

        if (empty($this->_returnUrl)) {
            throw new Exception('return url was not set.');
        }

        if (empty($this->_cancelUrl)) {
            throw new Exception('cancel return url was not set.');
        }

        if (empty($this->_ipnNotificationUrl)) {
            throw new Exception('ipn notification url was not set.');
        }

        $receiver_amount = $amount - (float)$this->_config->facilitator_fee;

        $bodyParameter = array(
            'requestEnvelope.errorLanguage'     => "en_US",
            'actionType'                        => self::ATTRIBUTE_ACTION_PAY,
            'currencyCode'                      => self::ATTRIBUTE_CURRENCY_USD,
            'feesPayer'                         => self::ATTRIBUTE_FEE_PAYER_EACH,
            'receiverList.receiver(0).email'    => $this->_paymentUserData->getPaymentUserId(),
            'receiverList.receiver(0).amount'   => $receiver_amount,
            'receiverList.receiver(0).primary'  => self::ATTRIBUTE_FALSE,
            'requestEnvelope.detailLevel'       => 'ReturnAll',
            'reverseAllParallelPaymentsOnError' => self::ATTRIBUTE_TRUE,
            'clientDetails.ipAddress'           => $_SERVER ['REMOTE_ADDR'],
            'clientDetails.deviceId'            => $_SERVER ['SERVER_ADDR'],
            'clientDetails.applicationId'       => $this->_config->client->application_id,
            'clientDetails.partnerName'         => $this->_config->client->partner_name,
            'cancelUrl'                         => $this->_cancelUrl,
            'returnUrl'                         => $this->_returnUrl,
            'ipnNotificationUrl'                => $this->_ipnNotificationUrl,
            'memo'                              => $requestMsg,
        );

        if ($senderMail) {
            $bodyParameter['senderEmail'] = $senderMail;
        }

        if ((float)$this->_config->facilitator_fee > 0.00 and isset($this->_config->facilitator_fee_receiver)) {
            $bodyParameter['receiverList.receiver(1).email'] = $this->_config->facilitator_fee_receiver;
            $bodyParameter['receiverList.receiver(1).amount'] = $this->_config->facilitator_fee;
            $bodyParameter['receiverList.receiver(1).primary'] = self::ATTRIBUTE_FALSE;
        }

        $response = $this->_makeRequest($bodyParameter, self::API_ADAPTIVE_PAYMENTS, self::OPERATION_PAY);

        $log->info('********** Finished PayPal Payment **********');

        $paypalResponse = new ResponsePayRequest($response);
        if (false === $paypalResponse->isSuccessful()) {
            throw new Exception('PayPal payment request failed. Request response:' . print_r($paypalResponse->getRawMessage(),
                    true));
        }

        return $paypalResponse;
    }

    /**
     * @param $senderMail
     * @param $receiverMail
     * @param $amount
     * @param $trackingId
     * @param $yearmonth
     *
     * @return ResponsePayRequest|null
     * @throws Exception
     */
    public function requestPaymentForPayout($senderMail, $receiverMail, $amount, $trackingId, $yearmonth)
    {
        $log = $this->_logger;

        $log->info('********** Start PayPal Payment for Payout **********');
        $log->info(__FUNCTION__);

        //$log->debug(APPLICATION_ENV);

        if (empty($this->_returnUrl)) {
            throw new Exception('return url was not set.');
        }

        if (empty($this->_cancelUrl)) {
            throw new Exception('cancel return url was not set.');
        }

        if (empty($this->_ipnNotificationUrl)) {
            throw new Exception('ipn notification url was not set.');
        }

        $receiver_amount = $amount - (float)$this->_config->facilitator_fee;

        $bodyParameter = array(
            'requestEnvelope.errorLanguage'     => "en_US",
            'senderEmail'                       => $senderMail,
            'actionType'                        => self::ATTRIBUTE_ACTION_PAY,
            'currencyCode'                      => self::ATTRIBUTE_CURRENCY_USD,
            'feesPayer'                         => self::ATTRIBUTE_FEE_PAYER_EACH,
            //'trackingId' => $trackingId,
            'receiverList.receiver(0).email'    => $receiverMail,
            'receiverList.receiver(0).amount'   => $receiver_amount,
            'receiverList.receiver(0).primary'  => self::ATTRIBUTE_FALSE,
            'requestEnvelope.detailLevel'       => 'ReturnAll',
            'reverseAllParallelPaymentsOnError' => self::ATTRIBUTE_TRUE,
            'clientDetails.applicationId'       => $this->_config->client->application_id,
            'clientDetails.partnerName'         => $this->_config->client->partner_name,
            'cancelUrl'                         => $this->_cancelUrl,
            'returnUrl'                         => $this->_returnUrl,
            'ipnNotificationUrl'                => $this->_ipnNotificationUrl,
            'memo'                              => 'Pling.com payout for month: ' . $yearmonth,
        );

        $response = $this->_makeRequest($bodyParameter, self::API_ADAPTIVE_PAYMENTS, self::OPERATION_PAY);

        $log->info('********** Finished PayPal Payment for Payout **********');

        $paypalResponse = new ResponsePayRequest($response);

        if (false === $paypalResponse->isSuccessful()) {
            //throw new \Library\Payment\Exception('PayPal payment request failed. Request response:' . print_r($paypalResponse->getRawMessage(), true));
            return null;
        }

        return $paypalResponse;
    }

    /**
     * @param $userData
     *
     * @throws Exception
     */
    public function setUserDataStore($userData)
    {
        if (false === ($userData instanceof UserDataInterface)) {
            throw new Exception('Wrong data type for user data');
        }
        $this->_paymentUserData = $userData;
    }

    /**
     * @return UserDataInterface
     */
    public function getUserDataStore()
    {
        return $this->_paymentUserData;
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->_cancelUrl;
    }

    /**
     * @param string $cancelUrl
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->_cancelUrl = $cancelUrl;
    }

    /**
     * @return string
     */
    public function getIpnNotificationUrl()
    {
        return $this->_ipnNotificationUrl;
    }

    /**
     * @param string $ipnNotificationUrl
     */
    public function setIpnNotificationUrl($ipnNotificationUrl)
    {
        $this->_ipnNotificationUrl = $ipnNotificationUrl;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->_returnUrl;
    }

    /**
     * @param string $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->_returnUrl = $returnUrl;
    }

    /**
     * @return string
     */
    public function getCheckoutEndpoint()
    {
        return trim($this->_config->form->endpoint . '/webapps/adaptivepayment/flow/pay');
    }

}