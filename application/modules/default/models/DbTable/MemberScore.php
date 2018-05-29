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
class Default_Model_DbTable_MemberScore extends Local_Model_Table
{

    protected $_name = "member_score";

    protected $_keyColumnsForRow = array('member_score_id');

    protected $_key = 'member_score_id';


    /**
     * @param int $member_id
     *
     * @return array
     */
    public function fetchScore($member_id)
    {
        $sql = "
                SELECT
                    p.*,
                 (select value from member_score_factors f where f.factor_id = 1) as factor_prod,
                 (select value from member_score_factors f where f.factor_id = 2) as factor_pling,
                 (select value from member_score_factors f where f.factor_id = 3) as factor_like,
                 (select value from member_score_factors f where f.factor_id = 4) as factor_comment,
                 (select value from member_score_factors f where f.factor_id = 5) as factor_year,
                 (select value from member_score_factors f where f.factor_id = 6) as factor_report_prod_spam,
                 (select value from member_score_factors f where f.factor_id = 7) as factor_report_prod_fraud

                 FROM
                     member_score p            
                 WHERE
                     member_id = :member_id               
                ;                  
               ";
        $result = $this->_db->query($sql, array('member_id' => $member_id))->fetchAll();

        if (count($result) > 0) {
            return $result[0];
        } else {
            return null;
        }
    }

    public function fetchTopUsers($limit = 100)
    {
            $sql = "
                    select  
                    s.*
                    ,m.profile_image_url
                    ,m.username
                    from member_score s
                    inner join member m on s.member_id = m.member_id
                    order by s.score desc             
            ";
            if (isset($limit)) {
                $sql .= ' limit ' . (int)$limit;
            }
            $result = $this->_db->query($sql)->fetchAll();

            return $this->generateRowSet($result);
    }


    /**
     * @param array $data
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    protected function generateRowSet($data)
    {
        $classRowSet = $this->getRowsetClass();

        return new $classRowSet(array(
            'table'    => $this,
            'rowClass' => $this->getRowClass(),
            'stored'   => true,
            'data'     => $data
        ));
    }

}