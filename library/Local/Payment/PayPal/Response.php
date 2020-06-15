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

class Local_Payment_PayPal_Response
{

    /**
     * @param array|null $rawResponse
     * @throws Local_Payment_Exception
     * @return Local_Payment_ResponseInterface
     */
    public static function buildResponse($rawResponse = null)
    {
        if (isset($rawResponse['txn_type']) AND ($rawResponse['txn_type'] == 'masspay')) {
            return new Local_Payment_PayPal_Masspay_ResponseMasspay($rawResponse);
        } else    
        if (isset($rawResponse['txn_type']) AND ($rawResponse['txn_type'] == 'subscr_payment')) {
            return new Local_Payment_PayPal_SubscriptionPayment_Response($rawResponse);
        } else    
        if (isset($rawResponse['txn_type']) AND ($rawResponse['txn_type'] == 'subscr_signup')) {
            return new Local_Payment_PayPal_SubscriptionSignup_Response($rawResponse);
        } else    
        if (isset($rawResponse['txn_type']) AND (($rawResponse['txn_type'] == 'subscr_cancel') || ($rawResponse['txn_type'] == 'subscr_failed')  || ($rawResponse['txn_type'] == 'recurring_payment_suspended_due_to_max_failed_paym'))) {
            return new Local_Payment_PayPal_SubscriptionCancel_Response($rawResponse);
        } else    
        if (isset($rawResponse['responseEnvelope_ack'])) {
            return new Local_Payment_PayPal_AdaptivePayment_ResponsePayRequest($rawResponse);
        } else 
        if (isset($rawResponse['transaction_type']) AND ($rawResponse['transaction_type'] == 'Adaptive Payment PAY')) {
            return new Local_Payment_PayPal_AdaptivePayment_ResponsePay($rawResponse);
        } else         
        if (isset($rawResponse['action_type']) AND ($rawResponse['action_type'] == 'PAY')) {
            return new Local_Payment_PayPal_AdaptivePayment_ResponsePay($rawResponse);
        } else 
        if (isset($rawResponse['transaction_type']) AND ($rawResponse['transaction_type'] == 'Adjustment')) {
            return new Local_Payment_PayPal_AdaptivePayment_ResponseAdjustment($rawResponse);
        } else 
        if (isset($rawResponse['txn_type']) AND ($rawResponse['txn_type'] == 'web_accept')) {
            return new Local_Payment_PayPal_Support_ResponseSupport($rawResponse);
        } else 
        if ($rawResponse['transaction_subject'] == '' AND $rawResponse['payment_status'] == 'Refunded') {
            return new Local_Payment_PayPal_AdaptivePayment_ResponseChargeback($rawResponse);
        } else {
            //Unknown response from PayPal. Raw message
            //throw new Local_Payment_Exception('Unknown response from PayPal. Raw message:' . print_r($rawResponse, true) . "\n");
            return null;
        }
    }

}