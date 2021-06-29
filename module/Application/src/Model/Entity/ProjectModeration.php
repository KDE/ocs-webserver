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

class ProjectModeration implements InputFilterAwareInterface
{
    // attributes
    public $project_moderation_id;
    public $project_moderation_type_id;
    public $project_id;
    public $value;
    public $created_by;
    public $note;
    public $is_deleted;
    public $is_valid;
    public $created_at;

    public function exchangeArray(array $data)
    {
        $this->project_moderation_id = !empty($data['project_moderation_id']) ? $data['project_moderation_id'] : null;
        $this->project_moderation_type_id = !empty($data['project_moderation_type_id']) ? $data['project_moderation_type_id'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->value = !empty($data['value']) ? $data['value'] : null;
        $this->created_by = !empty($data['created_by']) ? $data['created_by'] : null;
        $this->note = !empty($data['note']) ? $data['note'] : null;
        $this->is_deleted = !empty($data['is_deleted']) ? $data['is_deleted'] : null;
        $this->is_valid = !empty($data['is_valid']) ? $data['is_valid'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'project_moderation_id'      => $this->project_moderation_id,
            'project_moderation_type_id' => $this->project_moderation_type_id,
            'project_id'                 => $this->project_id,
            'value'                      => $this->value,
            'created_by'                 => $this->created_by,
            'note'                       => $this->note,
            'is_deleted'                 => $this->is_deleted,
            'is_valid'                   => $this->is_valid,
            'created_at'                 => $this->created_at,

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