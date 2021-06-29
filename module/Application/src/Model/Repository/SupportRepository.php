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

namespace Application\Model\Repository;

use Application\Model\Entity\Support;
use Application\Model\Interfaces\SupportInterface;
use ArrayObject;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;
use Library\Payment\ResponseInterface;
use Library\Payment\ResponseSubscriptionSignupInterface;

class SupportRepository extends BaseRepository implements SupportInterface
{
    const STATUS_NEW = 0;
    const STATUS_PAYED = 1;
    const STATUS_DONATED = 2;
    const STATUS_TRANSFERRED = 3;
    const STATUS_FINISHED = 4;
    const STATUS_ERROR = 90;
    const STATUS_DELETED = 99;

    const SUPPORT_TYPE_SIGNUP = 1;
    const SUPPORT_TYPE_PAYMENT = 2;

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "support";
        $this->_key = "id";
        $this->_prototype = Support::class;
    }

    /**
     * Support Subscription Signup.
     *
     * @param ResponseSubscriptionSignupInterface $payment_response
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewSupportSubscriptionPaymentFromResponse($payment_response)
    {
        $new_row = new Support();

        $rows = $this->fetchAllRows(['payment_reference_key' => $payment_response->getCustom(), 'type_id' => 1]);
        $list = $rows->toArray();
        $signUp = array_pop($list);

        if (!empty($signUp)) {
            $new_row->member_id = $signUp['member_id'];
            $new_row->subscription_id = $signUp['subscription_id'];
            $new_row->tier = $signUp['tier'];
            $new_row->period = $signUp['period'];
            $new_row->period_frequency = $signUp['period_frequency'];
        }

        $new_row->amount = $payment_response->getTransactionAmount();
        $new_row->payment_transaction_id = $payment_response->getTransactionId();
        $new_row->donation_time = new Expression ('Now()');
        $new_row->active_time = new Expression ('Now()');
        $new_row->status_id = self::STATUS_DONATED;
        $new_row->type_id = self::SUPPORT_TYPE_PAYMENT;
        $new_row->payment_reference_key = $payment_response->getCustom();
        $new_row->payment_provider = $payment_response->getProviderName();
        $new_row->payment_status = $payment_response->getStatus();
        $new_row->payment_raw_message = serialize($payment_response->getRawMessage());

        return $this->insert($new_row->getArrayCopy());
    }

    /**
     * Support.
     *
     * @param ResponseInterface $payment_response
     * @param int               $member_id Id of the Sender
     * @param float             $amount    amount donations/dollars
     * @param string|null       $comment   Comment from the buyer
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewSupportFromResponse($payment_response, $member_id, $amount, $comment = null)
    {

        $new_row = new Support();
        $new_row->member_id = $member_id;
        $new_row->amount = $amount;
        $new_row->comment = $comment;
        $new_row->donation_time = new Expression ('Now()');
        $new_row->status_id = self::STATUS_NEW;

        $new_row->payment_reference_key = $payment_response->getPaymentId();
        $new_row->payment_provider = $payment_response->getProviderName();
        $new_row->payment_status = $payment_response->getStatus();
        $new_row->payment_raw_message = serialize($payment_response->getRawMessage());

        return $this->insert($new_row->getArrayCopy());
    }

    /**
     * Support.
     *
     * @param             $transaction_id
     * @param int         $member_id Id of the Sender
     * @param float       $amount    amount donations/dollars
     * @param string|null $comment   Comment from the buyer
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewSupport($transaction_id, $member_id, $amount, $comment = null)
    {

        $new_row = new Support();
        $new_row->member_id = $member_id;
        $new_row->amount = $amount;
        $new_row->comment = $comment;
        $new_row->donation_time = new Expression ('Now()');
        $new_row->status_id = self::STATUS_NEW;

        $new_row->payment_reference_key = $transaction_id;
        $new_row->payment_provider = 'paypal';
        $new_row->payment_status = $this::STATUS_NEW;
        $new_row->payment_raw_message = '';

        return $this->insert($new_row->getArrayCopy());

    }

    /**
     * Support.
     *
     * @param             $transaction_id
     * @param int         $member_id Id of the Sender
     * @param float       $amount    amount donations/dollars
     * @param             $tier
     * @param             $period
     * @param             $period_frequency
     * @param string|null $comment   Comment from the buyer
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
    ) {
        $new_row = new Support();

        $new_row->member_id = $member_id;
        $new_row->type_id = $this::SUPPORT_TYPE_SIGNUP;
        $new_row->amount = $amount;
        $new_row->tier = $tier;
        $new_row->period = $period;
        $new_row->period_frequency = $period_frequency;
        $new_row->comment = $comment;
        $new_row->donation_time = new Expression ('Now()');
        $new_row->status_id = self::STATUS_NEW;

        $new_row->payment_reference_key = $transaction_id;
        $new_row->payment_provider = 'paypal';
        $new_row->payment_status = $this::STATUS_NEW;
        $new_row->payment_raw_message = '';

        $new_row->create_time = new Expression ('Now()');

        return $this->insert($new_row->getArrayCopy());
    }

    /**
     * Mark donations as payed.
     * So they can be used to donation.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function activateSupportFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id'              => self::STATUS_DONATED,
            'payment_transaction_id' => $payment_response->getTransactionId(),
            'payment_raw_Message'    => serialize($payment_response->getRawMessage()),
            'payment_status'         => $payment_response->getTransactionStatus(),
            'active_time'            => new Expression ('Now()'),
        );

        $this->update($updateValues, ['payment_reference_key' => $payment_response->getCustom()]);
    }

    /**
     * Mark donations as payed.
     * So they can be used to donation.
     *
     * @param ResponseSubscriptionSignupInterface $payment_response
     *
     */
    public function activateSupportSubscriptionSignupFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id'           => self::STATUS_DONATED,
            'subscription_id'     => $payment_response->getSubscriptionId(),
            'payment_raw_Message' => serialize($payment_response->getRawMessage()),
            'active_time'         => new Expression ('Now()'),
        );
        $this->update($updateValues, ['payment_reference_key' => $payment_response->getCustom(), 'type_id' => 1]);
    }

    /**
     * @param ResponseInterface $payment_response
     */
    public function deactivateSupportSubscriptionSignupFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id'           => self::STATUS_DELETED,
            'payment_raw_Message' => serialize($payment_response->getRawMessage()),
            'delete_time'         => new Expression ('Now()'),
        );
        $this->update($updateValues, ['payment_reference_key' => $payment_response->getCustom(), 'type_id' => 1]);
    }

    /**
     * @param ResponseInterface $payment_response
     */
    public function deactivateSupportFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id'         => 0,
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
     * @return array|ArrayObject|null
     */
    public function fetchSupportFromResponse($payment_response)
    {
        if ($payment_response->getCustom() != null) {
            $where = array('payment_reference_key = ?' => $payment_response->getCustom());
        } elseif ($payment_response->getTransactionId() != null) {
            $where = array('payment_transaction_id = ?' => $payment_response->getTransactionId());
        } else {
            return null;
        }

        return $this->fetchAllRows($where)->current();
    }

    /**
     * @param ResponseInterface $payment_response
     */
    public function updateSupportTransactionStatusFromResponse($payment_response)
    {
        $updateValues = array(
            'payment_status'    => $payment_response->getTransactionStatus(),
            'payment_raw_error' => serialize($payment_response->getRawMessage()),
        );

        $this->update(
            $updateValues, "payment_transaction_id='" . $payment_response->getTransactionId() . "' and (status_id=0 or status_id=1 or status_id=2)"
        );

    }

    /**
     * Payout of the donations successful.
     *
     * @param string $donation_unique_id
     *            Unique-ID, to indentify the donations
     *
     * @deprecated
     */
    public function payout_success($donation_unique_id)
    {

    }

    /**
     * Payout was not successful, so the donations went back to staus 2 (donationed,
     * but not payouted).
     *
     * @param string $donation_unique_id
     *            Unique-ID, to indentify the donations
     *
     * @deprecated
     */
    public function payout_revert($donation_unique_id)
    {

    }

    /**
     * @return int
     * @deprecated
     */
    public function countActive()
    {

    }

    /**
     * @return int
     * @deprecated
     */
    public function countSupported()
    {

    }

    /**
     * @param $memberId
     *
     * @return mixed
     * @deprecated
     */
    public function getCountAvailableSupportsPerUser($memberId)
    {

    }

    /**
     * @param int $memberId
     *
     * @return mixed
     * @deprecated
     */
    public function getCountSupportsPerUser($memberId)
    {

    }

    /**
     * @param null $limit
     *
     * @deprecated
     */
    public function getComments($limit = null)
    {

    }

    /**
     * @param int|null $limit
     *
     * @return null|array
     */
    public function getSupports($limit = null)
    {
        $sqlComments = "select *
            from support as donations
            straight_join member on member.member_id = donations.member_id
            left join comments on comments.comment_donation_id = donations.id
            where donations.status_id = :status_id
            order by donations.create_time desc
        ";

        if (isset($limit)) {
            $sqlComments .= ' limit ' . $limit;
        }

        $rowSet = $this->fetchAll($sqlComments, array('status_id' => self::STATUS_DONATED));

        if (0 == count($rowSet)) {
            return array();
        }

        return $rowSet;
    }

    /**
     * @param null $limit
     * @param bool $randomizeOrder
     *
     * @return array|\Laminas\Db\ResultSet\ResultSet
     */
    public function getSupporters($limit = null, $randomizeOrder = false)
    {
        $sql = ' 
            SELECT * FROM `support` `s`
            JOIN `member` `m` ON `s`.`member_id` = `m`.`member_id`
            WHERE `status_id` >=:param_status_id
            GROUP BY `m`.`member_id`';
        if ($randomizeOrder) {
            $sql .= ' order by rand()';
        }
        if ($limit !== null) {
            $sql .= ' limit ' . $limit;
        }

        return $this->fetchAll($sql, ['param_status_id' => self::STATUS_DONATED], false);
    }

    /**
     * @return int
     */
    public function getCountSupporters()
    {
        $sql = 'SELECT count(1) AS `count`
        FROM(
        SELECT `s`.`member_id` FROM `support` `s`
        JOIN `member` `m` ON `s`.`member_id` = `m`.`member_id`
        WHERE `s`.`status_id`>=:param_status_id
        GROUP BY `s`.`member_id`
        ) `t`';
        $row = $this->fetchRow($sql, ['param_status_id' => self::STATUS_DONATED]);

        return $row['count'];
    }

    /**
     * @deprecated
     */
    public function getLatestSupport()
    {
    }

    /**
     * @param $member_id
     *
     * @return \Laminas\Db\ResultSet\ResultSet
     */
    public function getSupporterDonationList($member_id)
    {
        return $this->fetchAllRows('status_id >= ' . self::STATUS_DONATED . ' and member_id = ' . $member_id, 'active_time DESC');
    }

}