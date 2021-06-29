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

class SectionSupport implements InputFilterAwareInterface
{
    public $section_support_id;
    public $support_id;
    public $section_id;
    public $amount;
    public $tier;
    public $period;
    public $period_frequency;
    public $is_active;
    public $created_at;
    public $changed_at;
    public $deleted_at;
    public $project_id;
    public $creator_id;
    public $project_category_id;
    public $referer;

    public function exchangeArray(array $data)
    {
        $this->section_support_id = !empty($data['section_support_id']) ? $data['section_support_id'] : null;
        $this->support_id = !empty($data['support_id']) ? $data['support_id'] : null;
        $this->section_id = !empty($data['section_id']) ? $data['section_id'] : null;
        $this->amount = !empty($data['amount']) ? $data['amount'] : null;
        $this->tier = !empty($data['tier']) ? $data['tier'] : null;
        $this->period = !empty($data['period']) ? $data['period'] : null;
        $this->period_frequency = !empty($data['period_frequency']) ? $data['period_frequency'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->changed_at = !empty($data['changed_at']) ? $data['changed_at'] : null;
        $this->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->creator_id = !empty($data['creator_id']) ? $data['creator_id'] : null;
        $this->project_category_id = !empty($data['project_category_id']) ? $data['project_category_id'] : null;
        $this->referer = !empty($data['referer']) ? $data['referer'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'tier'                => $this->tier,
            'support_id'          => $this->support_id,
            'section_support_id'  => $this->section_support_id,
            'section_id'          => $this->section_id,
            'referer'             => $this->referer,
            'project_id'          => $this->project_id,
            'project_category_id' => $this->project_category_id,
            'period_frequency'    => $this->period_frequency,
            'period'              => $this->period,
            'is_active'           => $this->is_active,
            'deleted_at'          => $this->deleted_at,
            'creator_id'          => $this->creator_id,
            'created_at'          => $this->created_at,
            'changed_at'          => $this->changed_at,
            'amount'              => $this->amount,

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