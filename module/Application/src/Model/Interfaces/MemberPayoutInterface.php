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


use Library\Payment\ResponseInterface;

interface MemberPayoutInterface extends BaseInterface
{
    /**
     * @param int $status
     *
     * return all payouts with status = $status
     **/
    public function fetchAllPayouts($yearmonth = null, $status = 0);

    /**
     * Mark payout as payed.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function setPayoutStatusCompletedFromResponse($payment_response);

    /**
     * Mark payout as payed.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function setPayoutStatusPendingFromResponse($payment_response);

    /**
     * Mark payout as payed.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function setPayoutStatusRefundFromResponse($payment_response);

    /**
     * Mark payout as denied.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function setPayoutStatusDeniedFromResponse($payment_response);

    /**
     * Mark payout as failed.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function setPayoutStatusFailedFromResponse($payment_response);

    /**
     * Mark payout as reserved.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function setPayoutStatusReservedFromResponse($payment_response);

    /**
     * Mark plings as payed.
     * So they can be used to pling.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function activatePayoutFromResponse($payment_response);

    /**
     * @param ResponseInterface $payment_response
     */
    public function deactivatePayoutFromResponse($payment_response);

    /**
     * @param ResponseInterface $payment_response
     */
    public function fetchPayoutFromResponse($payment_response);

    /**
     * @param ResponseInterface $payment_response
     */
    public function updatePayoutTransactionStatusFromResponse($payment_response);
}