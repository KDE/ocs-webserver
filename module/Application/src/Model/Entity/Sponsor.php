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

class Sponsor implements InputFilterAwareInterface
{
    public $sponsor_id;
    public $member_id;
    public $name;
    public $fullname;
    public $description;
    public $amount;
    public $created_at;
    public $begin_at;
    public $end_at;
    public $is_active;

    public function exchangeArray(array $data)
    {
        $this->sponsor_id = !empty($data['sponsor_id']) ? $data['sponsor_id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->fullname = !empty($data['fullname']) ? $data['fullname'] : null;
        $this->description = !empty($data['description']) ? $data['description'] : null;
        $this->amount = !empty($data['amount']) ? $data['amount'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->begin_at = !empty($data['begin_at']) ? $data['begin_at'] : null;
        $this->end_at = !empty($data['end_at']) ? $data['end_at'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'sponsor_id'  => $this->sponsor_id,
            'member_id'   => $this->member_id,
            'name'        => $this->name,
            'fullname'    => $this->fullname,
            'description' => $this->description,
            'amount'      => $this->amount,
            'created_at'  => $this->created_at,
            'begin_at'    => $this->begin_at,
            'end_at'      => $this->end_at,
            'is_active'   => $this->is_active,

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