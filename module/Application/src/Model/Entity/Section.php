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

class Section implements InputFilterAwareInterface
{
    public $section_id;
    public $name;
    public $description;
    public $goal_amount;
    public $order;
    public $hide;
    public $is_active;
    public $created_at;
    public $deleted_at;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->section_id = !empty($data['section_id']) ? $data['section_id'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->description = !empty($data['description']) ? $data['description'] : null;
        $this->goal_amount = !empty($data['goal_amount']) ? $data['goal_amount'] : null;
        $this->order = !empty($data['order']) ? $data['order'] : null;
        $this->hide = !empty($data['hide']) ? $data['hide'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'section_id'  => $this->section_id,
            'name'        => $this->name,
            'description' => $this->description,
            'goal_amount' => $this->goal_amount,
            'order'       => $this->order,
            'hide'        => $this->hide,
            'is_active'   => $this->is_active,
            'created_at'  => $this->created_at,
            'deleted_at'  => $this->deleted_at,
        ];
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

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(
            sprintf(
                '%s does not allow injection of an alternate input filter', __CLASS__
            )
        );
    }
}
