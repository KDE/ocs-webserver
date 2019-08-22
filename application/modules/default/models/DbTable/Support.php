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
class Default_Model_DbTable_Support extends Zend_Db_Table_Abstract
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

    /**
     * @var string
     */
    protected $_name = "support";

    /**
     * @var array
     */
    protected $_dependentTables = array(
        'Default_Model_DbTable_Member'
    );
    
    
    /**
     * Support Subscription Signup.
     *
     * @param Local_Payment_ResponseSubscriptionSignupInterface $payment_response
     * @param int $member_id Id of the Sender
     * @param float $amount amount donations/dollars
     * @param string|null $comment Comment from the buyer
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewSupportSubscriptionPaymentFromResponse($payment_response)
    {
        $new_row = $this->createRow();
        
        $signUp = $this->fetchRow("payment_reference_key = '". $payment_response->getCustom() . "' AND type_id = 1");
        if(!empty($signUp)) {
            $new_row->member_id = $signUp['member_id'];
            $new_row->subscription_id = $signUp['subscription_id'];
            $new_row->tier = $signUp['tier'];
            $new_row->period = $signUp['period'];
            $new_row->period_frequency = $signUp['period_frequency'];
        }

        $new_row->amount = $payment_response->getTransactionAmount();
        $new_row->payment_transaction_id = $payment_response->getTransactionId();
        $new_row->donation_time = new Zend_Db_Expr ('Now()');
        $new_row->active_time = new Zend_Db_Expr ('Now()');
        $new_row->status_id = self::STATUS_DONATED;
        $new_row->type_id = self::SUPPORT_TYPE_PAYMENT;
        $new_row->payment_reference_key = $payment_response->getCustom();
        $new_row->payment_provider = $payment_response->getProviderName();
        $new_row->payment_status = $payment_response->getStatus();
        $new_row->payment_raw_message = serialize($payment_response->getRawMessage());

        return $new_row->save();
    }

    /**
     * Support.
     *
     * @param Local_Payment_ResponseInterface $payment_response
     * @param int $member_id Id of the Sender
     * @param float $amount amount donations/dollars
     * @param string|null $comment Comment from the buyer
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewSupportFromResponse($payment_response, $member_id, $amount, $comment = null)
    {
        $new_row = $this->createRow();
        $new_row->member_id = $member_id;
        $new_row->amount = $amount;
        $new_row->comment = $comment;
        $new_row->donation_time = new Zend_Db_Expr ('Now()');
        $new_row->status_id = self::STATUS_NEW;

        $new_row->payment_reference_key = $payment_response->getPaymentId();
        $new_row->payment_provider = $payment_response->getProviderName();
        $new_row->payment_status = $payment_response->getStatus();
        $new_row->payment_raw_message = serialize($payment_response->getRawMessage());

        return $new_row->save();
    }
    
    
    /**
     * Support.
     *
     * @param Local_Payment_ResponseInterface $payment_response
     * @param int $member_id Id of the Sender
     * @param float $amount amount donations/dollars
     * @param string|null $comment Comment from the buyer
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewSupport($transaction_id, $member_id, $amount, $comment = null)
    {
        $new_row = $this->createRow();
        $new_row->member_id = $member_id;
        $new_row->amount = $amount;
        $new_row->comment = $comment;
        $new_row->donation_time = new Zend_Db_Expr ('Now()');
        $new_row->status_id = self::STATUS_NEW;

        $new_row->payment_reference_key = $transaction_id;
        $new_row->payment_provider = 'paypal';
        $new_row->payment_status = $this::STATUS_NEW;
        $new_row->payment_raw_message = '';

        return $new_row->save();
    }
    
    
        /**
     * Support.
     *
     * @param Local_Payment_ResponseInterface $payment_response
     * @param int $member_id Id of the Sender
     * @param float $amount amount donations/dollars
     * @param string|null $comment Comment from the buyer
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewSupportSubscriptionSignup($transaction_id, $member_id, $amount,$tier, $period, $period_frequency, $comment = null)
    {
        $new_row = $this->createRow();
        $new_row->member_id = $member_id;
        $new_row->type_id = $this::SUPPORT_TYPE_SIGNUP;
        $new_row->amount = $amount;
        $new_row->tier = $tier;
        $new_row->period = $period;
        $new_row->period_frequency = $period_frequency;
        $new_row->comment = $comment;
        $new_row->donation_time = new Zend_Db_Expr ('Now()');
        $new_row->status_id = self::STATUS_NEW;

        $new_row->payment_reference_key = $transaction_id;
        $new_row->payment_provider = 'paypal';
        $new_row->payment_status = $this::STATUS_NEW;
        $new_row->payment_raw_message = '';

        return $new_row->save();
    }
    

    /**
     * Mark donations as payed.
     * So they can be used to donation.
     *
     * @param Local_Payment_ResponseInterface $payment_response
     *
     */
    public function activateSupportFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id' => self::STATUS_DONATED,
            'payment_transaction_id' => $payment_response->getTransactionId(),
            'payment_raw_Message' => serialize($payment_response->getRawMessage()),
            'payment_status' => $payment_response->getTransactionStatus(),
            'active_time' => new Zend_Db_Expr ('Now()')
        );

        $this->update($updateValues, "payment_reference_key='" . $payment_response->getCustom() . "'");
    }
    
    
    /**
     * Mark donations as payed.
     * So they can be used to donation.
     *
     * @param Local_Payment_ResponseSubscriptionSignupInterface $payment_response
     *
     */
    public function activateSupportSubscriptionSignupFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id' => self::STATUS_DONATED,
            'subscription_id' => $payment_response->getSubscriptionId(),
            'payment_raw_Message' => serialize($payment_response->getRawMessage()),
            'active_time' => new Zend_Db_Expr ('Now()')
        );

        $this->update($updateValues, "payment_reference_key='" . $payment_response->getCustom() . "' AND type_id = 1");
    }
    
    /**
     * @param Local_Payment_ResponseInterface $payment_response
     */
    public function deactivateSupportSubscriptionSignupFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id' => self::STATUS_DELETED,
            'payment_raw_Message' => serialize($payment_response->getRawMessage()),
            'delete_time' => new Zend_Db_Expr ('Now()')
        );

        $this->update($updateValues, "payment_reference_key='" . $payment_response->getCustom() . "' AND type_id = 1");

    }

    /**
     * @param Local_Payment_ResponseInterface $payment_response
     */
    public function deactivateSupportFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id' => 0,
            'payment_status' => $payment_response->getTransactionStatus(),
            'payment_raw_error' => serialize($payment_response->getRawMessage())
        );

        $this->update($updateValues,
            "payment_transaction_id='" . $payment_response->getTransactionId() . "' and (status_id=1 or status_id=2)");

    }

    /**
     * @param Local_Payment_ResponseInterface $payment_response
     * @return null|\Zend_Db_Table_Row_Abstract
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

        return $this->fetchRow($where);

    }

    /**
     * @param Local_Payment_ResponseInterface $payment_response
     */
    public function updateSupportTransactionStatusFromResponse($payment_response)
    {
        $updateValues = array(
            'payment_status' => $payment_response->getTransactionStatus(),
            'payment_raw_error' => serialize($payment_response->getRawMessage())
        );

        $this->update($updateValues,
            "payment_transaction_id='" . $payment_response->getTransactionId() . "' and (status_id=0 or status_id=1 or status_id=2)");

    }

    /**
     * Payout of the donations successful.
     *
     * @param string $donation_unique_id
     *            Unique-ID, to indentify the donations
     * @deprecated
     */
    public function payout_success($donation_unique_id)
    {
        $data = array(
            'status_id' => '4',
            'paypal_payout_success_time' => new Zend_Db_Expr ('Now()')
        );
        $countRows = 0;
        try {
            $countRows = $this->update($data, 'paypal_payout_unique_id=' . $donation_unique_id . ' and status_id=3');
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
        }
    }

    /**
     * Payout was not successful, so the donations went back to staus 2 (donationed,
     * but not payouted).
     *
     * @param string $donation_unique_id
     *            Unique-ID, to indentify the donations
     * @deprecated
     */
    public function payout_revert($donation_unique_id)
    {
        $data = array(
            'status_id' => '2',
            'paypal_payout_success_time' => null
        );
        $this->update($data, 'paypal_payout_unique_id=' . $donation_unique_id . ' and status_id=3');
    }

    /**
     * @return int
     * @deprecated
     */
    public function countActive()
    {
        $q = $this->select()->where('status_id = ?', 1);

        return count($q->query()->fetchAll());
    }

    /**
     * @return int
     * @deprecated
     */
    public function countSupported()
    {
        $q = $this->select()->where('status_id >= ?', 2);

        return count($q->query()->fetchAll());
    }

    /**
     * @param $memberId
     * @return mixed
     * @deprecated
     */
    public function getCountAvailableSupportsPerUser($memberId)
    {
        // SELECT COUNT(1) FROM support WHERE support.member_id=2861 AND
        // donations.status_id=1
        $selectArr = $this->_db->fetchRow('SELECT count(*) AS count FROM ' . $this->_name . ' WHERE member_id = ' . $memberId . ' AND status_id = 1');
        return $selectArr ['count'];
    }

    /**
     * @param int $memberId
     * @return mixed
     * @deprecated
     */
    public function getCountSupportsPerUser($memberId)
    {
        return $selectArr ['count'];
    }


    /**
     * @param null $limit
     * @param null|array $forbidden
     * @return null|Zend_Db_Table_Row_Abstract
     * @deprecated
     */
    public function getComments($limit = null)
    {
        $sqlComments = "select *
            from ' . $this->_name . '
            straight_join member on member.member_id = ' . $this->_name . '.member_id
            straight_join comments on comments.comment_donation_id = ' . $this->_name . '.id
            where ' . $this->_name . '.status_id = :status_id
            and comments.comment_text > ''
        ";

        $sqlComments .= ' order by RAND()';

        if (isset($limit)) {
            $sqlComments .= ' limit ' . $limit;
        }

        $rowSet = $this->getAdapter()->fetchAll($sqlComments, array('status_id' => self::STATUS_DONATED));

        if (0 == count($rowSet)) {
            return array();
        }

        return $rowSet;
    }

    /**
     * @param int|null $limit
     * @return null|array
     */
    public function getSupports($limit = null)
    {
        $sqlComments = "select *
            from ' . $this->_name . '
            straight_join member on member.member_id = donations.member_id
            left join comments on comments.comment_donation_id = donations.id
            where donations.status_id = :status_id
            order by donations.create_time desc
        ";

        if (isset($limit)) {
            $sqlComments .= ' limit ' . $limit;
        }

        $rowSet = $this->getAdapter()->fetchAll($sqlComments, array('status_id' => self::STATUS_DONATED));

        if (0 == count($rowSet)) {
            return array();
        }

        return $rowSet;
    }

    /**
     * @param null $limit
     * @param bool $randomizeOrder
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getSupporters($limit = null, $randomizeOrder = false)
    {
        $sel = $this->select()->setIntegrityCheck(false)->from($this->_name)
            ->join('member', 'member.member_id=donations.member_id')
            ->where('status_id >= ' . self::STATUS_DONATED)
            ->group(array('member.member_id'));
        if ($randomizeOrder) {
            $sel->order(array('RAND()'));
        }
        if ($limit !== null) {
            $sel->limit($limit);
        }

        return $this->fetchAll($sel);
    }

    /**
     * @return int
     */
    public function getCountSupporters()
    {
        $sel = $this->select()->setIntegrityCheck(false)->from($this->_name, 'member_id')
            ->join('member', 'member.member_id=donations.member_id')
            ->where('status_id >= ' . self::STATUS_DONATED)
            ->group(array('donations.member_id'));

        return $this->fetchAll($sel)->count();
    }

    /**
     * @return Zend_Db_Table_Row_Abstract
     * @deprecated
     */
    public function getLatestSupport()
    {
        $sel = $this->select()->from($this->_name)
            ->where('status_id >= ' . self::STATUS_DONATED)
            ->order('active_time DESC');

        return $this->fetchAll($sel)->current();
    }

    /**
     * @return Zend_Db_Table_Row_Abstract
     * @deprecated
     */
    public function getSupporterDonationList($member_id)
    {
        $sel = $this->select()->from($this->_name)
            ->where('status_id >= ' . self::STATUS_DONATED.' and member_id = ' . $member_id)
            ->order('active_time DESC');
        return $this->fetchAll($sel);
    }

}



