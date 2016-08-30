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
class Default_Model_DbTable_Plings extends Zend_Db_Table_Abstract
{

    const STATUS_NEW = 0;
    const STATUS_PAYED = 1;
    const STATUS_PLINGED = 2;
    const STATUS_TRANSFERRED = 3;
    const STATUS_FINISHED = 4;
    const STATUS_ERROR = 90;
    const STATUS_DELETED = 99;

    /**
     * @var string
     */
    protected $_name = "plings";

    /**
     * @var array
     */
    protected $_dependentTables = array(
        'Default_Model_DbTable_Member',
        'Default_Model_DbTable_Project'
    );

    /**
     * Pling a project.
     *
     * @param Local_Payment_ResponseInterface $payment_response
     * @param int $member_id Id of the Sender
     * @param int $project_id Id of the receiving project
     * @param float $amount amount plings/dollars
     * @param string|null $comment Comment from the buyer
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewPlingFromResponse($payment_response, $member_id, $project_id, $amount, $comment = null)
    {
        $new_row = $this->createRow();
        $new_row->member_id = $member_id;
        $new_row->project_id = $project_id;
        $new_row->amount = $amount;
        $new_row->comment = $comment;
        $new_row->pling_time = new Zend_Db_Expr ('Now()');
        $new_row->status_id = self::STATUS_NEW;

        $new_row->payment_reference_key = $payment_response->getPaymentId();
        $new_row->payment_provider = $payment_response->getProviderName();
        $new_row->payment_status = $payment_response->getStatus();
        $new_row->payment_raw_message = serialize($payment_response->getRawMessage());

        return $new_row->save();
    }

    /**
     * Mark plings as payed.
     * So they can be used to pling.
     *
     * @param Local_Payment_ResponseInterface $payment_response
     *
     */
    public function activatePlingsFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id' => self::STATUS_PLINGED,
            'payment_transaction_id' => $payment_response->getTransactionId(),
            'payment_raw_Message' => serialize($payment_response->getRawMessage()),
            'payment_status' => $payment_response->getTransactionStatus(),
            'active_time' => new Zend_Db_Expr ('Now()')
        );

        $this->update($updateValues, "payment_reference_key='" . $payment_response->getPaymentId() . "'");
    }

    /**
     * @param Local_Payment_ResponseInterface $payment_response
     */
    public function deactivatePlingsFromResponse($payment_response)
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
    public function fetchPlingFromResponse($payment_response)
    {
        if ($payment_response->getPaymentId() != null) {
            $where = array('payment_reference_key = ?' => $payment_response->getPaymentId());
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
    public function updatePlingTransactionStatusFromResponse($payment_response)
    {
        $updateValues = array(
            'payment_status' => $payment_response->getTransactionStatus(),
            'payment_raw_error' => serialize($payment_response->getRawMessage())
        );

        $this->update($updateValues,
            "payment_transaction_id='" . $payment_response->getTransactionId() . "' and (status_id=0 or status_id=1 or status_id=2)");

    }

    /**
     * pling a project.
     *
     * @param int $member_id
     *            Pling-Geber
     * @param int $project_id
     *            Pling-Empfänger
     * @param int $amount
     * @return mixed
     */
    public function pling($member_id, $project_id, $amount = 0)
    {
        $rowset = $this->fetchAll($this->select()->where('member_id = ' . $member_id . ' and status_id=1')->order(' create_time desc'));
        $row = $rowset->current();

        $row->project_id = $project_id;
        $row->status_id = 2;
        $row->pling_time = new Zend_Db_Expr ('Now()');
        $row->amount = $amount;
        $newID = $row->save();

        return $newID;
    }

    /**
     * Move active plings from 1 project to another.
     *
     * @param int $project_id_from
     *            Sender der Plings
     * @param int $project_id_to
     *            Empfänger der Plings
     * @deprecated
     */
    public function movePlings($project_id_from, $project_id_to)
    {
        $updateValues = array(
            'project_id' => $project_id_to
        );
        $this->update($updateValues, "project_id='" . $project_id_from . "' and status_id=2");
    }

    /**
     * Mark these plings as ready to payout.
     *
     * @param string $project_id
     *            Projekt, welches ausbezahlt werden soll
     * @param string $email
     *            PayPal-Konto des Empfängers
     * @param string $pling_unique_id
     *            pling-id, wird für PayPal benutzt
     * @deprecated
     */
    public function payout_request($project_id, $email, $pling_unique_id)
    {
        $data = array(
            'status_id' => '3',
            'paypal_payout_request_time' => new Zend_Db_Expr ('Now()'),
            'paypal_payout_unique_id' => $pling_unique_id
        );
        $this->update($data, 'project_id=' . $project_id . ' and status_id=2');
    }

    /**
     * Payout of the plings successful.
     *
     * @param string $pling_unique_id
     *            Unique-ID, to indentify the plings
     * @deprecated
     */
    public function payout_success($pling_unique_id)
    {
        $data = array(
            'status_id' => '4',
            'paypal_payout_success_time' => new Zend_Db_Expr ('Now()')
        );
        $countRows = 0;
        try {
            $countRows = $this->update($data, 'paypal_payout_unique_id=' . $pling_unique_id . ' and status_id=3');
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
        }
    }

    /**
     * Payout was not successful, so the plings went back to staus 2 (plinged,
     * but not payouted).
     *
     * @param string $pling_unique_id
     *            Unique-ID, to indentify the plings
     * @deprecated
     */
    public function payout_revert($pling_unique_id)
    {
        $data = array(
            'status_id' => '2',
            'paypal_payout_success_time' => null
        );
        $this->update($data, 'paypal_payout_unique_id=' . $pling_unique_id . ' and status_id=3');
    }

    /**
     * @param $memberId
     * @return int
     * @deprecated
     */
    public function getCountPlingedProjectsForMember($memberId)
    {
        // selectArr = $this->_db->fetchRow('SELECT count(*) as anzahl FROM
        // '.$this->_name.' WHERE status_id>=2 AND project_id='.$_projectId);
        // selectArr = $this->_db->fetchRow('select project_id from
        // '.$this->_name.' WHERE member_id = '.$memberId . ' and project_id is
        // not null group by project_id');
        // eturn count($selectArr);
        $q = $this->select()->where('member_id = ?', $memberId)->group('project_id');

        return count($q->query()->fetchAll());
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
    public function countPlinged()
    {
        $q = $this->select()->where('status_id >= ?', 2)->where('project_id is not null');

        return count($q->query()->fetchAll());
    }

    /**
     * @param $memberId
     * @return mixed
     * @deprecated
     */
    public function getCountAvailablePlingsPerUser($memberId)
    {
        // SELECT COUNT(1) FROM plings WHERE plings.member_id=2861 AND
        // plings.status_id=1
        $selectArr = $this->_db->fetchRow('SELECT count(*) AS count FROM ' . $this->_name . ' WHERE member_id = ' . $memberId . ' AND status_id = 1');
        return $selectArr ['count'];
    }

    /**
     * @param int $memberId
     * @return mixed
     * @deprecated
     */
    public function getCountPlingsPerUser($memberId)
    {
        // SELECT count(1) FROM plings where project_id in (select project_id
        // from project where member_id = 2861)
        $selectArr = $this->_db->fetchRow('SELECT count(*) AS count FROM ' . $this->_name . ' WHERE  project_id IN (SELECT project_id FROM project WHERE member_id = ' . $memberId . ' )');
        return $selectArr ['count'];
    }

    /**
     * @param int $memberId
     * @return mixed
     * @deprecated
     */
    public function getCountPlingsToPayPerUser($memberId)
    {
        // SELECT count(1) FROM plings where status_id in (2,3) and project_id
        // in (select project_id from project where member_id = 2861)
        $selectArr = $this->_db->fetchRow('SELECT count(*) AS count FROM ' . $this->_name . ' WHERE status_id IN (2,3) AND project_id IN (SELECT project_id FROM project WHERE member_id = ' . $memberId . ' )');
        return $selectArr ['count'];
    }

    /**
     * @param int $memberId
     * @param $status_id
     * @return mixed
     * @deprecated
     */
    public function getCountPlingsPerUserStatus($memberId, $status_id)
    {
        // SELECT count(1) FROM plings where project_id in (select project_id
        // from project where member_id = 2861)
        $selectArr = $this->_db->fetchRow('SELECT count(*) AS count FROM ' . $this->_name . ' WHERE member_id = ' . $memberId . ' AND status_id = ' . $status_id);
        return $selectArr ['count'];
    }

    /**
     * @param int $projectId
     * @param $memberId
     * @return mixed
     * @deprecated
     */
    public function getCountPlingsPerProjectUser($projectId, $memberId)
    {
        // SELECT COUNT(1) FROM plings WHERE plings.member_id=2861 AND
        // plings.status_id=1
        $selectArr = $this->_db->fetchRow('SELECT count(*) AS count FROM ' . $this->_name . ' WHERE project_id = ' . $projectId . ' AND member_id = ' . $memberId . ' AND status_id >= 2');
        return $selectArr ['count'];
    }

    

    /**
     * @param int $projectId
     * @return int
     */
    public function getCountPlingsForProject($projectId)
    {
        $sql = "SELECT count(1) AS countPlinged
                FROM {$this->_name} AS p
                WHERE project_id = ?
                AND
                status_id >= ?;";
        $sql = $this->_db->quoteInto($sql, $projectId, 'INTEGER', 1);
        $sql = $this->_db->quoteInto($sql, self::STATUS_PLINGED, 'INTEGER', 1);
        $result = $this->_db->fetchRow($sql);

        return ( int )$result ['countPlinged'];
    }

    /**
     * @param int $projectId
     * @return float
     */
    public function getAmountPlingsForProject($projectId)
    {
        $sql = "
            SELECT SUM(amount) AS countPlinged
            FROM {$this->_name} AS p
            WHERE project_id = ?
            AND
            status_id >= ?;
        ";
        $sql = $this->_db->quoteInto($sql, $projectId, 'INTEGER', 1);
        $sql = $this->_db->quoteInto($sql, self::STATUS_PLINGED, 'INTEGER', 1);
        $result = $this->_db->fetchRow($sql);

        return ( float )$result ['countPlinged'];
    }

    /**
     * @param int $projectId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getSupporterForProjectId($projectId)
    {
        $subquerySel = $this->select()->setIntegrityCheck(false)->from($this->_name, array('MAX(id)'))
            ->where('project_id = ' . $projectId)
            ->where('status_id >= ' . self::STATUS_PLINGED)
            ->group('member_id');

        $backerSel = $this->select()->setIntegrityCheck(false)->from($this->_name)
            ->join('member', 'member.member_id=plings.member_id')
            ->where('plings.id IN (?)', $subquerySel);

        return $this->fetchAll($backerSel);
    }

    /**
     * @param int $projectId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getSupporterWithPlingsForProjectId($projectId)
    {
        $backerSel = $this->select()->setIntegrityCheck(false)->from($this->_name,
            array('*', 'sum(plings.amount) as sum_plings', 'count(plings.member_id) as count_support'))
            ->join('member', 'member.member_id=plings.member_id')
            ->where('project_id = ' . $projectId)
            ->where('status_id >= ' . self::STATUS_PLINGED)
            ->group('plings.member_id')
            ->order('sum(plings.amount) desc','create_time desc');

        return $this->fetchAll($backerSel);
    }

    /**
     * @param int $project_id
     * @return mixed
     */
    public function getPlingersCountForProject($project_id)
    {
        return $this->getCount($project_id);
    }

    /**
     * @param int $_projectId
     * @return mixed
     */
    public function getCount($_projectId)
    {
        // selectArr = $this->_db->fetchRow('SELECT count(*) as anzahl FROM
        // '.$this->_name.' WHERE status_id>=2 AND project_id='.$_projectId);
        $selectArr = $this->_db->fetchRow('SELECT count(*) AS count FROM ( SELECT member_id FROM ' . $this->_name . ' WHERE status_id >= 2 AND project_id = ' . $_projectId . ' GROUP BY member_id) a');

        return $selectArr ['count'];
    }

    /**
     * @param int $projectId
     * @param null $limit
     * @param null|array $forbidden
     * @return null|Zend_Db_Table_Row_Abstract
     * @deprecated
     */
    public function getCommentsForProject($projectId, $limit = null)
    {
        $sqlComments = "select *
            from plings
            straight_join member on member.member_id = plings.member_id
            straight_join comments on comments.comment_pling_id = plings.id
            where plings.project_id = :project_id
            and plings.status_id = :status_id
            and comments.comment_text > ''
        ";

        $sqlComments .= ' order by RAND()';

        if (isset($limit)) {
            $sqlComments .= ' limit ' . $limit;
        }

        $rowSet = $this->getAdapter()->fetchAll($sqlComments, array('project_id' => $projectId, 'status_id' => self::STATUS_PLINGED));

        if (0 == count($rowSet)) {
            return array();
        }

        return $rowSet;
    }

    /**
     * @param int $projectId
     * @param int|null $limit
     * @return null|array
     */
    public function getDonationsForProject($projectId, $limit = null)
    {
        $sqlComments = "select *
            from plings
            straight_join member on member.member_id = plings.member_id
            left join comments on comments.comment_pling_id = plings.id
            where plings.project_id = :project_id
            and plings.status_id = :status_id
            order by plings.create_time desc
        ";

        if (isset($limit)) {
            $sqlComments .= ' limit ' . $limit;
        }

        $rowSet = $this->getAdapter()->fetchAll($sqlComments, array('project_id' => $projectId, 'status_id' => self::STATUS_PLINGED));

        if (0 == count($rowSet)) {
            return array();
        }

        return $rowSet;
    }

    /**
     * @param int $projectId
     * @param null $limit
     * @param bool $randomizeOrder
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getProjectSupporters($projectId, $limit = null, $randomizeOrder = false)
    {
        $sel = $this->select()->setIntegrityCheck(false)->from($this->_name)
            ->join('member', 'member.member_id=plings.member_id')
            ->where('plings.project_id = ?', $projectId)
            ->where('status_id >= ' . self::STATUS_PLINGED)
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
     * @param int $projectId
     * @return int
     */
    public function getCountSupporters($projectId)
    {
        $sel = $this->select()->setIntegrityCheck(false)->from($this->_name, 'member_id')
            ->join('member', 'member.member_id=plings.member_id')
            ->where('plings.project_id = ?', $projectId)
            ->where('status_id >= ' . self::STATUS_PLINGED)
            ->group(array('plings.member_id'));

        return $this->fetchAll($sel)->count();
    }

    /**
     * @param $projectId
     * @return Zend_Db_Table_Row_Abstract
     * @deprecated
     */
    public function getLatestPling($projectId)
    {
        $sel = $this->select()->from($this->_name)
            ->where('plings.project_id = ?', $projectId)
            ->where('status_id >= ' . self::STATUS_PLINGED)
            ->order('active_time DESC');

        return $this->fetchAll($sel)->current();
    }

}
