<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Model\Entity;

use DomainException;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class MemberScore implements InputFilterAwareInterface
{
    public $member_score_id;
    public $member_id;
    public $score;
    public $count_product;
    public $count_pling;
    public $count_like;
    public $count_comment;
    public $count_years_membership;
    public $count_report_product_spam;
    public $count_report_product_fraud;
    public $count_report_comment;
    public $count_report_member;
    public $created_at;

    public function exchangeArray(array $data)
    {
        $this->member_score_id = !empty($data['member_score_id']) ? $data['member_score_id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->score = !empty($data['score']) ? $data['score'] : null;
        $this->count_product = !empty($data['count_product']) ? $data['count_product'] : null;
        $this->count_pling = !empty($data['count_pling']) ? $data['count_pling'] : null;
        $this->count_like = !empty($data['count_like']) ? $data['count_like'] : null;
        $this->count_comment = !empty($data['count_comment']) ? $data['count_comment'] : null;
        $this->count_years_membership = !empty($data['count_years_membership']) ? $data['count_years_membership'] : null;
        $this->count_report_product_spam = !empty($data['count_report_product_spam']) ? $data['count_report_product_spam'] : null;
        $this->count_report_product_fraud = !empty($data['count_report_product_fraud']) ? $data['count_report_product_fraud'] : null;
        $this->count_report_comment = !empty($data['count_report_comment']) ? $data['count_report_comment'] : null;
        $this->count_report_member = !empty($data['count_report_member']) ? $data['count_report_member'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'member_score_id'            => $this->member_score_id,
            'member_id'                  => $this->member_id,
            'score'                      => $this->score,
            'count_product'              => $this->count_product,
            'count_pling'                => $this->count_pling,
            'count_like'                 => $this->count_like,
            'count_comment'              => $this->count_comment,
            'count_years_membership'     => $this->count_years_membership,
            'count_report_product_spam'  => $this->count_report_product_spam,
            'count_report_product_fraud' => $this->count_report_product_fraud,
            'count_report_comment'       => $this->count_report_comment,
            'count_report_member'        => $this->count_report_member,
            'created_at'                 => $this->created_at,
        ];
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(
            sprintf(
                '%s does not allow injection of an alternate input filter', __CLASS__
            )
        );
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();
        //-----------------> hier come inputfiler
        $this->inputFilter = $inputFilter;

        return $this->inputFilter;
    }
}