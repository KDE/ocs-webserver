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

class PayoutStatus implements InputFilterAwareInterface
{
    // attributes
    public $id;
    public $type;
    public $title;
    public $description;
    public $color;
    public $icon;
    public $is_active;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->type = !empty($data['type']) ? $data['type'] : null;
        $this->title = !empty($data['title']) ? $data['title'] : null;
        $this->description = !empty($data['description']) ? $data['description'] : null;
        $this->color = !empty($data['color']) ? $data['color'] : null;
        $this->icon = !empty($data['icon']) ? $data['icon'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'id'          => $this->id,
            'type'        => $this->type,
            'title'       => $this->title,
            'description' => $this->description,
            'color'       => $this->color,
            'icon'        => $this->icon,
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