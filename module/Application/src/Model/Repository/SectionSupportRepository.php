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

use Application\Model\Entity\SectionSupport;
use Application\Model\Interfaces\SectionSupportInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;

class SectionSupportRepository extends BaseRepository implements SectionSupportInterface
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
        $this->_name = "section_support";
        $this->_key = "section_support_id";
        $this->_prototype = SectionSupport::class;
    }

    /**
     * Support.
     *
     * @param       $support_id
     * @param       $section_id
     * @param float $amount    amount donations/dollars
     * @param       $tier
     * @param       $period
     * @param       $period_frequency
     * @param null  $project_id
     * @param int   $member_id Id of the Sender
     * @param null  $project_category_id
     * @param null  $referer
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewSectionSupport(
        $support_id,
        $section_id,
        $amount,
        $tier,
        $period,
        $period_frequency,
        $project_id = null,
        $member_id = null,
        $project_category_id = null,
        $referer = null
    ) {
        $new_row = new SectionSupport();
        $new_row->support_id = $support_id;
        $new_row->section_id = $section_id;
        $new_row->amount = $amount;
        $new_row->tier = $tier;
        $new_row->period = $period;
        $new_row->period_frequency = $period_frequency;
        $new_row->is_active = 1;
        $new_row->created_at = new Expression('Now()');
        $new_row->project_id = $project_id;
        $new_row->creator_id = $member_id;
        $new_row->project_category_id = $project_category_id;
        $new_row->referer = $referer;
        $data = $new_row->getArrayCopy();
        $id = $this->insert($data);

        return $this->fetchById($id);
    }

    /**
     * @return int
     * @deprecated
     */
    public function countActive()
    {
    }

    public function fetchLatestSectionSupportForProject($project_id, $member_id)
    {
        $sql = "
                select 
                ss.section_support_id
                ,ss.support_id
                ,ss.section_id
                ,ss.amount
                ,ss.tier
                ,ss.period_frequency
                ,s.name
                ,s.description
                from project as p 
                join section_category as c on p.project_category_id = c.project_category_id
                join section as s on s.section_id = c.section_id
                join section_support as ss on ss.section_id = s.section_id and ss.is_active = 1
                join support as st on st.id = ss.support_id and st.status_id = 2
                where p.project_id = :project_id and st.member_id = :member_id
                ORDER BY ss.created_at DESC 
                LIMIT 1
        ";
        return $this->fetchRow($sql, array('project_id' => $project_id, 'member_id' => $member_id));
    }

    public function fetchLatestSectionSupportForMember($section_id, $member_id)
    {
        $sql = "
            SELECT `section_support`.`section_support_id`, `section_support`.`support_id`, `section_support`.`section_id`, `section_support`.`amount`, `section_support`.`tier`, `section_support`.`period_frequency`
            FROM `section_support` 
            JOIN `section` ON `section`.`section_id` = `section_support`.`section_id`
            JOIN `support` ON `support`.`id` = `section_support`.`support_id` AND `support`.`status_id` = 2
            WHERE `section_support`.`is_active` = 1
            AND `section`.`section_id` = :section_id
            AND `support`.`member_id` = :member_id
            ORDER BY `section_support`.`created_at` DESC 
            LIMIT 1
        ";

        return $this->fetchRow($sql, array('section_id' => $section_id, 'member_id' => $member_id));
    }

    public function fetchAllSectionSupportsForMember($section_id, $member_id)
    {
        $sql = "
            SELECT `section_support`.`section_support_id`, `section_support`.`support_id`, `section_support`.`section_id`, `section_support`.`project_id`, `m2`.`member_id` AS `affiliate_member_id`, `m2`.`username` AS `affiliate_username`, `section_support`.`referer` ,CASE WHEN `support`.`subscription_id` IS NULL THEN `support`.`payment_transaction_id` ELSE `support`.`subscription_id` END AS `subscription_id`, `support`.`type_id`, `section_support`.`amount`, `section_support`.`tier`, `section_support`.`period`, `section_support`.`period_frequency`, `support`.`status_id`, `support`.`type_id`, `support`.`active_time`, `support`.`delete_time`, `support`.`payment_provider`,`member`.`member_id`,`member`.`username`,
            CASE 
            WHEN `support`.`status_id` = 2 AND `support`.`type_id` = 0 AND (date_format(`support`.`active_time`  + INTERVAL 11 MONTH, '%Y%m')) >= date_format(NOW(), '%Y%m') THEN 'active'
            WHEN `support`.`status_id` = 2 AND `support`.`type_id` = 1 THEN 'active'
            ELSE 'inactive'
            END AS `active_status`
            ,(`support`.`active_time`  + INTERVAL 11 MONTH) AS `active_time_one_year`
            ,(`support`.`active_time`  + INTERVAL 1 MONTH) AS `active_time_one_month`
            ,(SELECT MAX(`active_time`) FROM `support` `p2` WHERE `p2`.`type_id` = 2 AND `p2`.`subscription_id` = `support`.`subscription_id`) AS `last_payment_time`
            ,CASE 
                WHEN `support`.`type_id` = 1 AND `section_support`.`period` = 'Y' THEN (SELECT (MAX(`active_time`)  + INTERVAL 11 MONTH) FROM `support` `p2` WHERE `p2`.`type_id` = 2 AND `p2`.`subscription_id` = `support`.`subscription_id`)
        	WHEN `support`.`type_id` = 1 AND `section_support`.`period` = 'M' THEN (SELECT (STR_TO_DATE(CONCAT(DATE_FORMAT(MAX(`active_time`) + INTERVAL 1 MONTH,'%Y%m'),'01'),'%Y%m%d') - INTERVAL 1 DAY)  FROM `support` `p2` WHERE `p2`.`type_id` = 2 AND `p2`.`subscription_id` = `support`.`subscription_id`)
            END AS `last_payment_until_time`
            FROM `section_support` 
            JOIN `section` ON `section`.`section_id` = `section_support`.`section_id`
            JOIN `support` ON `support`.`id` = `section_support`.`support_id` AND `support`.`status_id` >= 2
            JOIN `member` ON `member`.`member_id` = `support`.`member_id`
            LEFT JOIN `project` ON `project`.`project_id` = `section_support`.`project_id`
            LEFT JOIN `member` `m2` ON `m2`.`member_id` = `project`.`member_id`
            WHERE `section_support`.`is_active` = 1
            AND `section`.`section_id` = :section_id
            AND `support`.`member_id` = :member_id
            ORDER BY `support`.`active_time` DESC  
        ";

        return $this->fetchAll($sql, array('section_id' => $section_id, 'member_id' => $member_id));
    }

    
}