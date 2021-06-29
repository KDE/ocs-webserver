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

namespace Library\Payment\PayPal;

use Library\Payment\PayPal\AdaptivePayment\ResponseAdjustment;
use Library\Payment\PayPal\AdaptivePayment\ResponseChargeback;
use Library\Payment\PayPal\AdaptivePayment\ResponsePay;
use Library\Payment\PayPal\AdaptivePayment\ResponsePayRequest;
use Library\Payment\PayPal\Masspay\ResponseMasspay;
use Library\Payment\PayPal\Support\ResponseSupport;
use Library\Payment\ResponseInterface;

/**
 * Class Response
 *
 * @package Library\Payment\PayPal
 */
class Response
{

    /**
     * @param null $rawResponse
     *
     * @return ResponseInterface|PaymentInterface|SubscriptionSignupInterface|null
     */
    public static function buildResponse($rawResponse = null)
    {
        if (isset($rawResponse['txn_type']) and ($rawResponse['txn_type'] == 'masspay')) {
            return new ResponseMasspay($rawResponse);
        } else {
            if (isset($rawResponse['txn_type']) and ($rawResponse['txn_type'] == 'subscr_payment')) {
                return new SubscriptionPayment\Response($rawResponse);
            } else {
                if (isset($rawResponse['txn_type']) and ($rawResponse['txn_type'] == 'subscr_signup')) {
                    return new SubscriptionSignup\Response($rawResponse);
                } else {
                    if (isset($rawResponse['txn_type']) and (($rawResponse['txn_type'] == 'subscr_cancel') || ($rawResponse['txn_type'] == 'subscr_failed') || ($rawResponse['txn_type'] == 'recurring_payment_suspended_due_to_max_failed_paym'))) {
                        return new SubscriptionCancel\Response($rawResponse);
                    } else {
                        if (isset($rawResponse['responseEnvelope_ack'])) {
                            return new ResponsePayRequest($rawResponse);
                        } else {
                            if (isset($rawResponse['transaction_type']) and ($rawResponse['transaction_type'] == 'Adaptive Payment PAY')) {
                                return new ResponsePay($rawResponse);
                            } else {
                                if (isset($rawResponse['action_type']) and ($rawResponse['action_type'] == 'PAY')) {
                                    return new ResponsePay($rawResponse);
                                } else {
                                    if (isset($rawResponse['transaction_type']) and ($rawResponse['transaction_type'] == 'Adjustment')) {
                                        return new ResponseAdjustment($rawResponse);
                                    } else {
                                        if (isset($rawResponse['txn_type']) and ($rawResponse['txn_type'] == 'web_accept')) {
                                            return new ResponseSupport($rawResponse);
                                        } else {
                                            if ($rawResponse['transaction_subject'] == '' and $rawResponse['payment_status'] == 'Refunded') {
                                                return new ResponseChargeback($rawResponse);
                                            } else {
                                                //Unknown response from PayPal. Raw message
                                                //throw new \Exception('Unknown response from PayPal. Raw message:' . print_r($rawResponse, true) . "\n");
                                                return null;

                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}