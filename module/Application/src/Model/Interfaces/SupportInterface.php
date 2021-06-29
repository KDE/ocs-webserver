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

namespace Application\Model\Interfaces;

use Library\Payment\ResponseSubscriptionSignupInterface;

interface SupportInterface extends BaseInterface
{
    /**
     * Support Subscription Signup.
     *
     * @param ResponseSubscriptionSignupInterface $payment_response
     * @param int                                                  $member_id Id of the Sender
     * @param float                                                $amount    amount donations/dollars
     * @param string|null                                          $comment   Comment from the buyer
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewSupportSubscriptionPaymentFromResponse($payment_response);

    /**
     * Support.
     *
     * @param ResponseSubscriptionSignupInterface $payment_response
     * @param int                                                  $member_id Id of the Sender
     * @param float                                                $amount    amount donations/dollars
     * @param string|null                                          $comment   Comment from the buyer
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewSupportFromResponse($payment_response, $member_id, $amount, $comment = null);

    /**
     * Support.
     *
     * @param ResponseSubscriptionSignupInterface $payment_response
     * @param int                                                  $member_id Id of the Sender
     * @param float                                                $amount    amount donations/dollars
     * @param string|null                                          $comment   Comment from the buyer
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewSupport($transaction_id, $member_id, $amount, $comment = null);

    /**
     * Support.
     *
     * @param ResponseSubscriptionSignupInterface $payment_response
     * @param int                                                  $member_id Id of the Sender
     * @param float                                                $amount    amount donations/dollars
     * @param string|null                                          $comment   Comment from the buyer
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewSupportSubscriptionSignup(
        $transaction_id,
        $member_id,
        $amount,
        $tier,
        $period,
        $period_frequency,
        $comment = null
    );

    /**
     * Mark donations as payed.
     * So they can be used to donation.
     *
     * @param ResponseSubscriptionSignupInterface $payment_response
     *
     */
    public function activateSupportFromResponse($payment_response);

    /**
     * Mark donations as payed.
     * So they can be used to donation.
     *
     * @param ResponseSubscriptionSignupInterface $payment_response
     *
     */
    public function activateSupportSubscriptionSignupFromResponse($payment_response);

    /**
     * @param ResponseSubscriptionSignupInterface $payment_response
     */
    public function deactivateSupportSubscriptionSignupFromResponse($payment_response);

    /**
     * @param ResponseSubscriptionSignupInterface $payment_response
     */
    public function deactivateSupportFromResponse($payment_response);

    /**
     * @param ResponseSubscriptionSignupInterface $payment_response
     */
    public function fetchSupportFromResponse($payment_response);

    /**
     * @param ResponseSubscriptionSignupInterface $payment_response
     */
    public function updateSupportTransactionStatusFromResponse($payment_response);

    /**
     * @param int|null $limit
     *
     * @return null|array
     */
    public function getSupports($limit = null);

    /**
     * @param null $limit
     * @param bool $randomizeOrder
     */
    public function getSupporters($limit = null, $randomizeOrder = false);

    /**
     * @return int
     */
    public function getCountSupporters();

    /**
     * @param $member_id
     */
    public function getSupporterDonationList($member_id);

}