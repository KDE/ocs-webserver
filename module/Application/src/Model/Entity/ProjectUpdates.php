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

class ProjectUpdates implements InputFilterAwareInterface
{
    // attributes
    public $project_update_id;
    public $project_id;
    public $member_id;
    public $public;
    public $title;
    public $text;
    public $created_at;
    public $changed_at;
    public $source_id;
    public $source_pk;

    public function exchangeArray(array $data)
    {
        $this->project_update_id = !empty($data['project_update_id']) ? $data['project_update_id'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->public = !empty($data['public']) ? $data['public'] : null;
        $this->title = !empty($data['title']) ? $data['title'] : null;
        $this->text = !empty($data['text']) ? $data['text'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->changed_at = !empty($data['changed_at']) ? $data['changed_at'] : null;
        $this->source_id = !empty($data['source_id']) ? $data['source_id'] : null;
        $this->source_pk = !empty($data['source_pk']) ? $data['source_pk'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'project_update_id' => $this->project_update_id,
            'project_id'        => $this->project_id,
            'member_id'         => $this->member_id,
            'public'            => $this->public,
            'title'             => $this->title,
            'text'              => $this->text,
            'created_at'        => $this->created_at,
            'changed_at'        => $this->changed_at,
            'source_id'         => $this->source_id,
            'source_pk'         => $this->source_pk,

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