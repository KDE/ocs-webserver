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

class MemberRole implements InputFilterAwareInterface
{
    public $member_role_id;
    public $title;
    public $shortname;
    public $is_active;
    public $is_deleted;
    public $created_at;
    public $changed_at;
    public $deleted_at;

    public function exchangeArray(array $data)
    {
        $this->member_role_id = !empty($data['member_role_id']) ? $data['member_role_id'] : null;
        $this->title = !empty($data['title']) ? $data['title'] : null;
        $this->shortname = !empty($data['shortname']) ? $data['shortname'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;
        $this->is_deleted = !empty($data['is_deleted']) ? $data['is_deleted'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->changed_at = !empty($data['changed_at']) ? $data['changed_at'] : null;
        $this->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'member_role_id' => $this->member_role_id,
            'title'          => $this->title,
            'shortname'      => $this->shortname,
            'is_active'      => $this->is_active,
            'is_deleted'     => $this->is_deleted,
            'created_at'     => $this->created_at,
            'changed_at'     => $this->changed_at,
            'deleted_at'     => $this->deleted_at,
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