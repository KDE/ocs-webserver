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
class Default_Model_DbTable_SectionSupport extends Zend_Db_Table_Abstract
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
    protected $_name = "section_support";

    
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
    public function createNewSectionSupport($support_id, $section_id, $amount, $tier, $period, $period_frequency, $project_id = null, $referer = null)
    {
        $new_row = $this->createRow();
        $new_row->support_id = $support_id;
        $new_row->section_id = $section_id;
        $new_row->amount = $amount;
        $new_row->tier = $tier;
        $new_row->period = $period;
        $new_row->period_frequency = $period_frequency;
        $new_row->created_at = new Zend_Db_Expr ('Now()');
        
        $new_row->project_id = $project_id;
        $new_row->referer = $referer;

        return $new_row->save();
    }
    

    /**
     * @return int
     * @deprecated
     */
    public function countActive()
    {
        $q = $this->select()->where('is_active = ?', 1);

        return count($q->query()->fetchAll());
    }
    
    
    public function fetchLatestSectionSupportForMember($section_id, $member_id) {
        $sql = "
            SELECT section_support.section_support_id, section_support.support_id, section_support.section_id, section_support.amount, section_support.tier, section_support.period_frequency
            FROM section_support 
            JOIN section ON section.section_id = section_support.section_id
            JOIN support ON support.id = section_support.support_id AND support.status_id = 2
            WHERE section_support.is_active = 1
            AND section.section_id = :section_id
            AND support.member_id = :member_id
            ORDER BY section_support.created_at desc 
            LIMIT 1
        ";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('section_id' => $section_id, 'member_id' => $member_id));
        
        return $resultSet;
    }
    
    
    public function fetchAllSectionSupportsForMember($section_id, $member_id) {
        $sql = "
            SELECT section_support.section_support_id, section_support.support_id, section_support.section_id, section_support.project_id, m2.member_id AS affiliate_member_id, m2.username AS affiliate_username, section_support.referer ,case when support.subscription_id IS NULL then support.payment_transaction_id ELSE support.subscription_id END AS subscription_id, support.type_id, section_support.amount, section_support.tier, section_support.period, section_support.period_frequency, support.status_id, support.type_id, support.active_time, support.delete_time, support.payment_provider,member.member_id,member.username,
            case 
            when support.status_id = 2 AND support.type_id = 0 AND (date_format(support.active_time  + INTERVAL 11 MONTH, '%Y%m')) >= date_format(NOW(), '%Y%m') then 'active'
            when support.status_id = 2 AND support.type_id = 1 then 'active'
            ELSE 'inactive'
            END AS active_status
            ,(support.active_time  + INTERVAL 11 MONTH) AS active_time_one_year
            ,(support.active_time  + INTERVAL 1 MONTH) AS active_time_one_month
            ,(SELECT MAX(active_time) FROM support p2 WHERE p2.type_id = 2 and p2.subscription_id = support.subscription_id) AS last_payment_time
            ,case 
                when support.type_id = 1 AND section_support.period = 'Y' then (SELECT (MAX(active_time)  + INTERVAL 11 MONTH) FROM support p2 WHERE p2.type_id = 2 and p2.subscription_id = support.subscription_id)
        	when support.type_id = 1 AND section_support.period = 'M' then (SELECT (STR_TO_DATE(CONCAT(DATE_FORMAT(MAX(active_time) + INTERVAL 1 MONTH,'%Y%m'),'01'),'%Y%m%d') - INTERVAL 1 DAY)  FROM support p2 WHERE p2.type_id = 2 and p2.subscription_id = support.subscription_id)
            END AS last_payment_until_time
            FROM section_support 
            JOIN section ON section.section_id = section_support.section_id
            JOIN support ON support.id = section_support.support_id AND support.status_id >= 2
            JOIN member ON member.member_id = support.member_id
            left JOIN project ON project.project_id = section_support.project_id
            left JOIN member m2 ON m2.member_id = project.member_id
            WHERE section_support.is_active = 1
            AND section.section_id = :section_id
            AND support.member_id = :member_id
            ORDER BY support.active_time DESC  
        ";
        $resultSet = $this->getAdapter()->fetchAll($sql, array('section_id' => $section_id, 'member_id' => $member_id));
        
        return $resultSet;
    }
}



