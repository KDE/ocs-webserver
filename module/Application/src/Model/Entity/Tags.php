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

class Tags implements InputFilterAwareInterface
{
    public $tag_id;
    public $tag_name;
    public $tag_fullname;
    public $tag_description;
    public $is_active;

    public function exchangeArray(array $data)
    {
        $this->tag_id = !empty($data['tag_id']) ? $data['tag_id'] : null;
        $this->tag_name = !empty($data['tag_name']) ? $data['tag_name'] : null;
        $this->tag_fullname = !empty($data['tag_fullname']) ? $data['tag_fullname'] : null;
        $this->tag_description = !empty($data['tag_description']) ? $data['tag_description'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'tag_id'          => $this->tag_id,
            'tag_name'        => $this->tag_name,
            'tag_fullname'    => $this->tag_fullname,
            'tag_description' => $this->tag_description,
            'is_active'       => $this->is_active,

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