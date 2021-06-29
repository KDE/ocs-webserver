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

class TagGroupItem implements InputFilterAwareInterface
{
    // attributes
    public $tag_group_item_id;
    public $tag_group_id;
    public $tag_id;

    public function exchangeArray(array $data)
    {
        $this->tag_group_item_id = !empty($data['tag_group_item_id']) ? $data['tag_group_item_id'] : null;
        $this->tag_group_id = !empty($data['tag_group_id']) ? $data['tag_group_id'] : null;
        $this->tag_id = !empty($data['tag_id']) ? $data['tag_id'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'tag_group_item_id' => $this->tag_group_item_id,
            'tag_group_id'      => $this->tag_group_id,
            'tag_id'            => $this->tag_id,
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