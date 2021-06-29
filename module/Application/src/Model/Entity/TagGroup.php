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

class TagGroup implements InputFilterAwareInterface
{
    public $group_id;
    public $group_name;
    public $group_display_name;
    public $group_legacy_name;
    public $is_multi_select;

    public function exchangeArray(array $data)
    {
        $this->group_id = !empty($data['group_id']) ? $data['group_id'] : null;
        $this->group_name = !empty($data['group_name']) ? $data['group_name'] : null;
        $this->group_display_name = !empty($data['group_display_name']) ? $data['group_display_name'] : null;
        $this->group_legacy_name = !empty($data['group_legacy_name']) ? $data['group_legacy_name'] : null;
        $this->is_multi_select = !empty($data['is_multi_select']) ? $data['is_multi_select'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'group_id'           => $this->group_id,
            'group_name'         => $this->group_name,
            'group_display_name' => $this->group_display_name,
            'group_legacy_name'  => $this->group_legacy_name,
            'is_multi_select'    => $this->is_multi_select,

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