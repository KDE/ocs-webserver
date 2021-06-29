<?php /** @noinspection PhpUnused */

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

namespace Application\Model\Repository;

use Application\Model\Entity\MemberPayout;
use Application\Model\Interfaces\MemberPayoutInterface;
use ArrayObject;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Expression;
use Library\Payment\ResponseInterface;


class MemberPayoutRepository extends BaseRepository implements MemberPayoutInterface
{
    public static $PAYOUT_STATUS_NEW = 0;
    public static $PAYOUT_STATUS_REQUESTED = 1;
    public static $PAYOUT_STATUS_PROCESSED = 10;
    public static $PAYOUT_STATUS_PENDING = 50;
    public static $PAYOUT_STATUS_ERROR = 99;
    public static $PAYOUT_STATUS_COMPLETED = 100;
    public static $PAYOUT_STATUS_REFUND = 900;
    public static $PAYOUT_STATUS_RESERVED = 901;
    public static $PAYOUT_STATUS_DENIED = 940;
    public static $PAYOUT_STATUS_FAILED = 950;

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "member_payout";
        $this->_key = "id";
        $this->_prototype = MemberPayout::class;
    }

    /**
     * @param null $yearmonth
     * @param int  $status
     *
     * @return array|ResultSet
     */
    public function fetchAllPayouts($yearmonth = null, $status = 0)
    {

        $sql = "
                SELECT
                    *
                FROM
                    " . $this->_name . "
                WHERE
                    status = " . $status;
        if ($yearmonth) {
            $sql .= " AND yearmonth = " . $yearmonth;
        }

        return $this->fetchAll($sql);

    }

    //TODO: implement ResponseInterface

    /**
     * Mark payout as payed.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function setPayoutStatusCompletedFromResponse($payment_response)
    {
        $updateValues = array(
            'status'                     => self::$PAYOUT_STATUS_COMPLETED,
            'payment_transaction_id'     => $payment_response->getTransactionId(),
            'payment_raw_Message'        => serialize($payment_response->getRawMessage()),
            'payment_status'             => $payment_response->getTransactionStatus(),
            'timestamp_masspay_last_ipn' => new Expression('Now()'),
        );

        $this->update($updateValues, "payment_reference_key='" . $payment_response->getPaymentId() . "'");
    }

    /**
     * Mark payout as payed.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function setPayoutStatusPendingFromResponse($payment_response)
    {
        $updateValues = array(
            'status'                     => self::$PAYOUT_STATUS_PENDING,
            'payment_transaction_id'     => $payment_response->getTransactionId(),
            'payment_raw_Message'        => serialize($payment_response->getRawMessage()),
            'payment_status'             => $payment_response->getTransactionStatus(),
            'timestamp_masspay_last_ipn' => new Expression ('Now()'),
        );

        $this->update($updateValues, "payment_reference_key='" . $payment_response->getPaymentId() . "'");
    }

    /**
     * Mark payout as payed.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function setPayoutStatusRefundFromResponse($payment_response)
    {
        $updateValues = array(
            'status'                     => self::$PAYOUT_STATUS_REFUND,
            'payment_transaction_id'     => $payment_response->getTransactionId(),
            'payment_raw_Message'        => serialize($payment_response->getRawMessage()),
            'payment_status'             => $payment_response->getTransactionStatus(),
            'timestamp_masspay_last_ipn' => new Expression ('Now()'),
        );

        $this->update($updateValues, "payment_reference_key='" . $payment_response->getPaymentId() . "'");
    }

    /**
     * Mark payout as denied.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function setPayoutStatusDeniedFromResponse($payment_response)
    {
        $updateValues = array(
            'status'                     => self::$PAYOUT_STATUS_DENIED,
            'payment_transaction_id'     => $payment_response->getTransactionId(),
            'payment_raw_Message'        => serialize($payment_response->getRawMessage()),
            'payment_status'             => $payment_response->getTransactionStatus(),
            'timestamp_masspay_last_ipn' => new Expression ('Now()'),
        );

        $this->update($updateValues, "payment_reference_key='" . $payment_response->getPaymentId() . "'");
    }

    /**
     * Mark payout as failed.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function setPayoutStatusFailedFromResponse($payment_response)
    {
        $updateValues = array(
            'status'                     => self::$PAYOUT_STATUS_FAILED,
            'payment_transaction_id'     => $payment_response->getTransactionId(),
            'payment_raw_Message'        => serialize($payment_response->getRawMessage()),
            'payment_status'             => $payment_response->getTransactionStatus(),
            'timestamp_masspay_last_ipn' => new Expression ('Now()'),
        );

        $this->update($updateValues, "payment_reference_key='" . $payment_response->getPaymentId() . "'");
    }

    /**
     * Mark payout as reserved.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function setPayoutStatusReservedFromResponse($payment_response)
    {
        $updateValues = array(
            'status'                     => self::$PAYOUT_STATUS_RESERVED,
            'payment_transaction_id'     => $payment_response->getTransactionId(),
            'payment_raw_Message'        => serialize($payment_response->getRawMessage()),
            'payment_status'             => $payment_response->getTransactionStatus(),
            'timestamp_masspay_last_ipn' => new Expression ('Now()'),
        );

        $this->update($updateValues, "payment_reference_key='" . $payment_response->getPaymentId() . "'");
    }

    /**
     * Mark plings as payed.
     * So they can be used to pling.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function activatePayoutFromResponse($payment_response)
    {
        $updateValues = array(
            'status'                     => self::$PAYOUT_STATUS_COMPLETED,
            'payment_transaction_id'     => $payment_response->getTransactionId(),
            'payment_raw_Message'        => serialize($payment_response->getRawMessage()),
            'payment_status'             => $payment_response->getTransactionStatus(),
            'timestamp_masspay_last_ipn' => new Expression ('Now()'),
        );

        $this->update($updateValues, "payment_reference_key='" . $payment_response->getPaymentId() . "'");
    }

    /**
     * @param ResponseInterface $payment_response
     */
    public function deactivatePayoutFromResponse($payment_response)
    {
        $updateValues = array(
            'status'            => 0,
            'payment_status'    => $payment_response->getTransactionStatus(),
            'payment_raw_error' => serialize($payment_response->getRawMessage()),
        );

        $this->update(
            $updateValues, "payment_transaction_id='" . $payment_response->getTransactionId() . "' and (status_id=1 or status_id=2)"
        );

    }

    /**
     * @param ResponseInterface $payment_response
     *
     * @return array|ArrayObject
     */
    public function fetchPayoutFromResponse($payment_response)
    {
        if ($payment_response->getPaymentId() != null) {
            $where = "payment_reference_key = '" . $payment_response->getPaymentId() . "'";
        } elseif ($payment_response->getTransactionId() != null) {
            $where = "payment_transaction_id = '" . $payment_response->getTransactionId() . "'";
        } else {
            return null;
        }

        return $this->fetchRow('select * from member_payout where ' . $where);
    }

    /**
     * @param ResponseInterface $payment_response
     */
    public function updatePayoutTransactionStatusFromResponse($payment_response)
    {
        $updateValues = array(
            'payment_status'    => $payment_response->getTransactionStatus(),
            'payment_raw_error' => serialize($payment_response->getRawMessage()),
        );

        $this->update(
            $updateValues, "payment_transaction_id='" . $payment_response->getTransactionId() . "' and status>1"
        );

    }

}
