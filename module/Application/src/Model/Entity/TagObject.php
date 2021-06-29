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

class TagObject implements InputFilterAwareInterface
{
    // attributes
    public $tag_item_id;
    public $tag_id;
    public $tag_type_id;
    public $tag_group_id;
    public $tag_object_id;
    public $tag_parent_object_id;
    public $tag_created;
    public $tag_changed;
    public $is_deleted;

    public function exchangeArray(array $data)
    {
        $this->tag_item_id = !empty($data['tag_item_id']) ? $data['tag_item_id'] : null;
        $this->tag_id = !empty($data['tag_id']) ? $data['tag_id'] : null;
        $this->tag_type_id = !empty($data['tag_type_id']) ? $data['tag_type_id'] : null;
        $this->tag_group_id = !empty($data['tag_group_id']) ? $data['tag_group_id'] : null;
        $this->tag_object_id = !empty($data['tag_object_id']) ? $data['tag_object_id'] : null;
        $this->tag_parent_object_id = !empty($data['tag_parent_object_id']) ? $data['tag_parent_object_id'] : null;
        $this->tag_created = !empty($data['tag_created']) ? $data['tag_created'] : null;
        $this->tag_changed = !empty($data['tag_changed']) ? $data['tag_changed'] : null;
        $this->is_deleted = !empty($data['is_deleted']) ? $data['is_deleted'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'tag_item_id'          => $this->tag_item_id,
            'tag_id'               => $this->tag_id,
            'tag_type_id'          => $this->tag_type_id,
            'tag_group_id'         => $this->tag_group_id,
            'tag_object_id'        => $this->tag_object_id,
            'tag_parent_object_id' => $this->tag_parent_object_id,
            'tag_created'          => $this->tag_created,
            'tag_changed'          => $this->tag_changed,
            'is_deleted'           => $this->is_deleted,

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