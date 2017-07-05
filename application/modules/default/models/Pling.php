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
class Default_Model_Pling extends Default_Model_DbTable_Plings
{

    public function fetchTotalAmountSupported()
    {
        $sql = "
                SELECT
                    sum(amount) AS total_sum
                FROM
                    plings
                WHERE
                    status_id = 2
                ";

        $result = $this->_db->fetchRow($sql);

        return $result['total_sum'];
    }

    /**
     * @param $limit
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchRecentDonations($limit = null)
    {
        $sql = "
                SELECT
					plings.amount,
					plings.create_time,
					project.project_id,
                    project.member_id AS project_owner_id,
					project.title,
					member.member_id,
					member.username,
					member.profile_image_url
                FROM
                    project
					   JOIN
                    plings ON (project.project_id = plings.project_id AND plings.status_id = 2)
                       JOIN
                    member ON plings.member_id = member.member_id
				ORDER BY plings.create_time DESC
                ";

        if (null != $limit) {
            $sql .= $this->_db->quoteInto(" limit ?", $limit, 'INTEGER');
        }

        $result = $this->_db->fetchAll($sql);

        return $this->generateRowSet($result);
    }

    /**
     * @param $data
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    protected function generateRowSet($data)
    {
        $classRowSet = $this->getRowsetClass();

        $returnRowSet = new $classRowSet(array(
            'table'    => $this,
            'rowClass' => $this->getRowClass(),
            'stored'   => true,
            'data'     => $data
        ));

        return $returnRowSet;
    }

    /**
     * @param int $member_id
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchRecentDonationsForUser($member_id)
    {
        $sql = "
                SELECT
                    project.member_id AS owner_id,
                    sum(plings.amount) AS 'amount',
                    count(1) AS 'count',
                    year(`pling_time`) AS 'year',
                    month(`pling_time`) AS 'month'
                FROM
                    plings
                        JOIN
                    project ON plings.project_id = project.project_id
                WHERE
                    project.member_id = ?
                        AND plings.status_id = 2
                GROUP BY project.member_id , month(`pling_time`) , year(`pling_time`)
                ORDER BY pling_time DESC
               ";

        $sql = $this->_db->quoteInto($sql, $member_id, 'INTEGER');

        $result = $this->_db->fetchAll($sql);

        return $this->generateRowSet($result);
    }

    public function setAllPlingsForUserDeleted($member_id)
    {
        $sql = '
                UPDATE plings
                SET status_id = 99
                WHERE member_id = :member_id
                ;';

        $this->_db->query($sql, array('member_id' => $member_id))->execute();
    }

    public function setAllPlingsForUserActivated($member_id)
    {
        $sql = '
                UPDATE plings
                SET status_id = 99
                WHERE member_id = :member_id
                ;';

        $this->_db->query($sql, array('member_id' => $member_id))->execute();
    }

} 