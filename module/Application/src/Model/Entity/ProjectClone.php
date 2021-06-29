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

class ProjectClone implements InputFilterAwareInterface
{
    // attributes
    public $project_clone_id;
    public $project_id;
    public $project_id_parent;
    public $external_link;
    public $member_id;
    public $text;
    public $is_deleted;
    public $is_valid;
    public $project_clone_type;
    public $created_at;
    public $changed_at;
    public $deleted_at;

    public function exchangeArray(array $data)
    {
        $this->project_clone_id = !empty($data['project_clone_id']) ? $data['project_clone_id'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->project_id_parent = !empty($data['project_id_parent']) ? $data['project_id_parent'] : null;
        $this->external_link = !empty($data['external_link']) ? $data['external_link'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->text = !empty($data['text']) ? $data['text'] : null;
        $this->is_deleted = !empty($data['is_deleted']) ? $data['is_deleted'] : null;
        $this->is_valid = !empty($data['is_valid']) ? $data['is_valid'] : null;
        $this->project_clone_type = !empty($data['project_clone_type']) ? $data['project_clone_type'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->changed_at = !empty($data['changed_at']) ? $data['changed_at'] : null;
        $this->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'project_clone_id'   => $this->project_clone_id,
            'project_id'         => $this->project_id,
            'project_id_parent'  => $this->project_id_parent,
            'external_link'      => $this->external_link,
            'member_id'          => $this->member_id,
            'text'               => $this->text,
            'is_deleted'         => $this->is_deleted,
            'is_valid'           => $this->is_valid,
            'project_clone_type' => $this->project_clone_type,
            'created_at'         => $this->created_at,
            'changed_at'         => $this->changed_at,
            'deleted_at'         => $this->deleted_at,

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