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
    public function createNewSectionSupport($support_id, $section_id, $amount,$tier, $period, $period_frequency)
    {
        $new_row = $this->createRow();
        $new_row->support_id = $support_id;
        $new_row->section_id = $section_id;
        $new_row->amount = $amount;
        $new_row->tier = $tier;
        $new_row->period = $period;
        $new_row->period_frequency = $period_frequency;
        $new_row->created_at = new Zend_Db_Expr ('Now()');

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
    
}



