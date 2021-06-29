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

use Application\Model\Entity\Plings;
use Application\Model\Interfaces\PlingsInterface;
use ArrayObject;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Library\Payment\ResponseInterface;

/**
 * Class PlingsRepository
 *
 * @package Application\Model\Repository
 */
class PlingsRepository extends BaseRepository implements PlingsInterface
{
    const STATUS_NEW = 0;
    const STATUS_PAYED = 1;
    const STATUS_PLINGED = 2;
    const STATUS_TRANSFERRED = 3;
    const STATUS_FINISHED = 4;
    const STATUS_ERROR = 90;
    const STATUS_DELETED = 99;

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "plings";
        $this->_key = "id";
        $this->_prototype = Plings::class;
    }

    /**
     * Pling a project.
     *
     * @param ResponseInterface $payment_response
     * @param int               $member_id  Id of the Sender
     * @param int               $project_id Id of the receiving project
     * @param float             $amount     amount plings/dollars
     * @param string|null       $comment    Comment from the buyer
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewPlingFromResponse($payment_response, $member_id, $project_id, $amount, $comment = null)
    {
        $new_row = array();
        $new_row['member_id'] = $member_id;
        $new_row['project_id'] = $project_id;
        $new_row['amount'] = $amount;
        $new_row['comment'] = $comment;
        $new_row['pling_time'] = new Expression ('Now()');
        $new_row['status_id'] = self::STATUS_NEW;

        $new_row['payment_reference_key'] = $payment_response->getPaymentId();
        $new_row['payment_provider'] = $payment_response->getProviderName();
        $new_row['payment_status'] = $payment_response->getStatus();
        $new_row['payment_raw_message'] = serialize($payment_response->getRawMessage());

        return $this->insertOrUpdate($new_row);
    }

    /**
     * Mark plings as payed.
     * So they can be used to pling.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function activatePlingsFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id'              => self::STATUS_PLINGED,
            'payment_transaction_id' => $payment_response->getTransactionId(),
            'payment_raw_Message'    => serialize($payment_response->getRawMessage()),
            'payment_status'         => $payment_response->getTransactionStatus(),
            'active_time'            => new Expression ('Now()'),
        );

        $this->update($updateValues, ["payment_reference_key" => $payment_response->getPaymentId()]);
    }

    /**
     * @param ResponseInterface $payment_response
     */
    public function deactivatePlingsFromResponse($payment_response)
    {
        $updateValues = array(
            'status_id'         => 0,
            'payment_status'    => $payment_response->getTransactionStatus(),
            'payment_raw_error' => serialize($payment_response->getRawMessage()),
        );

        $this->update(
            $updateValues, [
                             "payment_transaction_id" => $payment_response->getTransactionId(),
                             "status_id"              => 1,
                         ]
        );
        $this->update(
            $updateValues, [
                             "payment_transaction_id" => $payment_response->getTransactionId(),
                             "status_id"              => 2,
                         ]
        );

    }

    /**
     * @param ResponseInterface $payment_response
     *
     * @return array|ArrayObject
     */
    public function fetchPlingFromResponse($payment_response)
    {
        if ($payment_response->getPaymentId() != null) {
            $where = 'payment_reference_key = ' . $payment_response->getPaymentId();
        } elseif ($payment_response->getTransactionId() != null) {
            $where = 'payment_transaction_id = ' . $payment_response->getTransactionId();
        } else {
            return null;
        }

        return $this->fetchRow("select * from " . $this->getName() . " where " . $where);

    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->_name;
    }

    //TODO: implement ResponseInterface

    /**
     * @param ResponseInterface $payment_response
     */
    public function updatePlingTransactionStatusFromResponse($payment_response)
    {
        $updateValues = array(
            'payment_status'    => $payment_response->getTransactionStatus(),
            'payment_raw_error' => serialize($payment_response->getRawMessage()),
        );

        $this->update(
            $updateValues, [
                             "payment_transaction_id" => $payment_response->getTransactionId(),
                             "status_id"              => 0,
                         ]
        );
        $this->update(
            $updateValues, [
                             "payment_transaction_id" => $payment_response->getTransactionId(),
                             "status_id"              => 1,
                         ]
        );
        $this->update(
            $updateValues, [
                             "payment_transaction_id" => $payment_response->getTransactionId(),
                             "status_id"              => 2,
                         ]
        );

    }

    /**
     * pling a project.
     *
     * @param int $member_id
     *            Pling-Geber
     * @param int $project_id
     *            Pling-Empfänger
     * @param int $amount
     *
     * @return mixed
     * @noinspection SqlResolve
     */
    public function pling($member_id, $project_id, $amount = 0)
    {
        $sql = "select id from " . $this->_name . " where member_id = " . (int)$member_id . " and status_id=1 order by create_time desc limit 1";
        $row = $this->fetchRow($sql);

        $row['project_id'] = (int)$project_id;
        $row['status_id'] = 2;
        $row['pling_time'] = new Expression('Now()');
        $row['amount'] = (int)$amount;

        return $this->update($row);
    }

    /**
     * @param int $projectId
     *
     * @return int
     * @noinspection SqlResolve
     */
    public function getCountPlingsForProject($projectId)
    {
        $sql = "SELECT count(1) AS countPlinged
                FROM {$this->_name} AS p
            WHERE project_id = :project_id
            AND
            status_id >= :status_id;
        ";
        $result = $this->fetchRow($sql, array('project_id' => $projectId, 'status_id' => self::STATUS_PLINGED));

        return ( int )$result ['countPlinged'];
    }

    /**
     * @param int $projectId
     *
     * @return float
     * @noinspection SqlResolve
     */
    public function getAmountPlingsForProject($projectId)
    {
        $sql = "
            SELECT SUM(amount) AS countPlinged
            FROM {$this->_name} AS p
            WHERE project_id = :project_id
            AND
            status_id >= :status_id;
        ";
        $result = $this->fetchRow($sql, array('project_id' => $projectId, 'status_id' => self::STATUS_PLINGED));

        return ( float )$result ['countPlinged'];
    }

    /**
     * @param int $projectId
     *
     * @return array
     * @noinspection SqlResolve
     */
    public function getSupporterForProjectId($projectId)
    {
        $sql = 'SELECT MAX(plings.id) AS "id" ' . 'FROM plings ' . 'WHERE project_id = :project_id ' . 'AND status_id >= :status_id ' . 'GROUP BY member_id';

        $ids = $this->fetchAll($sql, array('project_id' => $projectId, 'status_id' => self::STATUS_PLINGED));

        $stringIds = "";
        foreach ($ids as $item) {
            $stringIds .= $item['id'] . ',';
        }
        $stringIds = substr($stringIds, 0, -1);

        $backerSel = 'SELECT * FROM ' . $this->_name . ' ' . 'JOIN member on member.member_id=plings.member_id ' . 'WHERE plings.id IN (' . $stringIds . ')';

        return $this->fetchAll($backerSel);
    }

    /**
     * @param int $projectId
     *
     * @return array
     */
    public function getSupporterWithPlingsForProjectId($projectId)
    {
        $sql = 'SELECT p.*, sum(p.amount) AS "sum_plings", count(p.member_id) AS "count_support" ' . 'FROM plings AS p ' . 'JOIN member AS m ON m.member_id=p.member_id ' . 'WHERE project_id = :project_id ' . 'AND status_id >= :status_id ' . 'GROUP BY p.member_id ' . 'ORDER BY sum(p.amount) DESC, create_time DESC';

        return $this->fetchAll($sql, array('project_id' => $projectId, 'status_id' => self::STATUS_PLINGED));
    }

    /**
     * @param int $project_id
     *
     * @return mixed
     */
    public function getPlingersCountForProject($project_id)
    {
        return $this->getCount($project_id);
    }

    /**
     * @param int $_projectId
     *
     * @return mixed
     * @noinspection SqlResolve
     */
    public function getCount($_projectId)
    {
        // selectArr = $this->_db->fetchRow('SELECT count(*) as anzahl FROM
        // '.$this->_name.' WHERE status_id>=2 AND project_id='.$_projectId);
        $selectArr = $this->fetchRow('SELECT count(*) AS count FROM ( SELECT member_id FROM ' . $this->_name . ' WHERE status_id >= 2 AND project_id = ' . $_projectId . ' GROUP BY member_id) a');

        return $selectArr ['count'];
    }

    /**
     * @param int  $projectId
     * @param null $limit
     *
     * @return array|ResultSet
     * @deprecated
     */
    public function getCommentsForProject($projectId, $limit = null)
    {
        $sqlComments = "
            SELECT *
            FROM `plings`
            STRAIGHT_JOIN `member` ON `member`.`member_id` = `plings`.`member_id`
            STRAIGHT_JOIN `comments` ON `comments`.`comment_pling_id` = `plings`.`id`
            WHERE `plings`.`project_id` = :project_id
            AND `plings`.`status_id` = :status_id
            AND `comments`.`comment_text` > ''
        ";

        $sqlComments .= ' order by RAND()';

        if (isset($limit)) {
            $sqlComments .= ' limit ' . $limit;
        }

        $rowSet = $this->fetchAll($sqlComments, array('project_id' => $projectId, 'status_id' => self::STATUS_PLINGED));

        if (0 == count($rowSet)) {
            return array();
        }

        return $rowSet;
    }

    /**
     * @param int      $projectId
     * @param int|null $limit
     *
     * @return null|array
     */
    public function getDonationsForProject($projectId, $limit = null)
    {
        $sqlComments = "SELECT *
            FROM `plings`
            STRAIGHT_JOIN `member` ON `member`.`member_id` = `plings`.`member_id`
            LEFT JOIN `comments` ON `comments`.`comment_pling_id` = `plings`.`id`
            WHERE `plings`.`project_id` = :project_id
            AND `plings`.`status_id` = :status_id
            ORDER BY `plings`.`create_time` DESC
        ";

        if (isset($limit)) {
            $sqlComments .= ' limit ' . $limit;
        }

        $rowSet = $this->fetchAll($sqlComments, array('project_id' => $projectId, 'status_id' => self::STATUS_PLINGED));

        if (0 == count($rowSet)) {
            return array();
        }

        return $rowSet;
    }

    /**
     * @param int  $projectId
     * @param null $limit
     * @param bool $randomizeOrder
     *
     * @return ResultInterface
     */
    public function getProjectSupporters($projectId, $limit = null, $randomizeOrder = false)
    {
        $sql = new Sql($this->db);
        $select = $sql->select()->from(array('p' => $this->_name));

        $select->join(array('m' => 'member'), 'm.member_id=p.member_id')->where(['p.project_id = ?' => $projectId])
               ->where(['p.status_id >= ?' => self::STATUS_PLINGED])->group(['p.member_id']);

        if ($randomizeOrder) {
            $select->order(array(new Expression("RAND()")));
        }
        if ($limit !== null) {
            $select->limit($limit);
        }

        //$selectString = $sql->buildSqlString($select);
        //$result = $this->db->query($selectString);

        $statement = $sql->prepareStatementForSqlObject($select);

        return $statement->execute();

    }

    /**
     * @param int $projectId
     *
     * @return int
     */
    public function getCountSupporters($projectId)
    {
        $sql = new Sql($this->db);
        $select = $sql->select()->from(array('p' => $this->_name));

        $select->columns(array('member_id'))->join(array('m' => 'member'), 'm.member_id=p.member_id')
               ->where(['p.project_id = ?' => $projectId])->where(['p.status_id >= ?' => self::STATUS_PLINGED])
               ->group(['p.member_id']);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return $result->count();

    }

    /**
     * @param $projectId
     *
     * @return array|ArrayObject|null
     * @deprecated
     * @noinspection SqlResolve
     */
    public function getLatestPling($projectId)
    {

        $sql = "select * from " . $this->_name . " where plings.project_id = " . $projectId . " and status_id = " . self::STATUS_PLINGED . " ORDER BY active_time DESC";

        return $this->fetchRow($sql);
    }

    public function fetchTotalAmountSupported()
    {
        $sql = "
                SELECT
                    sum(`amount`) AS `total_sum`
                FROM
                    `plings`
                WHERE
                    `status_id` = 2
                ";

        $result = $this->fetchRow($sql);

        return $result['total_sum'];
    }

    /**
     * @param $limit
     *
     * @return array|ResultSet
     */
    public function fetchRecentDonations($limit = null)
    {
        $sql = "
                SELECT
					`plings`.`amount`,
					`plings`.`create_time`,
					`project`.`project_id`,
                    `project`.`member_id` AS `project_owner_id`,
					`project`.`title`,
					`member`.`member_id`,
					`member`.`username`,
					`member`.`profile_image_url`
                FROM
                    `project`
					   JOIN
                    `plings` ON (`project`.`project_id` = `plings`.`project_id` AND `plings`.`status_id` = 2)
                       JOIN
                    `member` ON `plings`.`member_id` = `member`.`member_id`
				ORDER BY `plings`.`create_time` DESC
                ";

        if (null != $limit) {
            $sql .= " limit " . $limit;
        }

        return $this->fetchAll($sql);
    }

    /**
     * @param int $member_id
     *
     * @return array|ResultSet
     */
    public function fetchRecentDonationsForUser($member_id)
    {
        $sql = "
                SELECT
                    `project`.`member_id` AS `owner_id`,
                    sum(`plings`.`amount`) AS 'amount',
                    count(1) AS 'count',
                    year(`pling_time`) AS 'year',
                    month(`pling_time`) AS 'month'
                FROM
                    `plings`
                        JOIN
                    `project` ON `plings`.`project_id` = `project`.`project_id`
                WHERE
                    `project`.`member_id` = :member_id
                        AND `plings`.`status_id` = 2
                GROUP BY `project`.`member_id` , month(`pling_time`) , year(`pling_time`)
                ORDER BY `pling_time` DESC
               ";

        return $this->fetchAll($sql, ['member_id' => $member_id]);
    }

    public function setAllPlingsForUserDeleted($member_id)
    {
        $sql = '
                UPDATE `plings`
                SET `status_id` = 99
                WHERE `member_id` = :member_id
                ;';

        $this->update($sql, array('member_id' => $member_id));
    }

    public function setAllPlingsForUserActivated($member_id)
    {
        $sql = '
                UPDATE `plings`
                SET `status_id` = 99
                WHERE `member_id` = :member_id
                ;';

        $this->update($sql, array('member_id' => $member_id));
    }

}
