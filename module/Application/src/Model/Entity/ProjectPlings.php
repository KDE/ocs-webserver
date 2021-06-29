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

class ProjectPlings implements InputFilterAwareInterface
{
    // attributes
    public $project_plings_id;
    public $project_id;
    public $member_id;
    public $created_at;
    public $is_active;
    public $deactive_at;
    public $is_deleted;
    public $deleted_at;

    public function exchangeArray(array $data)
    {
        $this->project_plings_id = !empty($data['project_plings_id']) ? $data['project_plings_id'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;
        $this->deactive_at = !empty($data['deactive_at']) ? $data['deactive_at'] : null;
        $this->is_deleted = !empty($data['is_deleted']) ? $data['is_deleted'] : null;
        $this->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'project_plings_id' => $this->project_plings_id,
            'project_id'        => $this->project_id,
            'member_id'         => $this->member_id,
            'created_at'        => $this->created_at,
            'is_active'         => $this->is_active,
            'deactive_at'       => $this->deactive_at,
            'is_deleted'        => $this->is_deleted,
            'deleted_at'        => $this->deleted_at,
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