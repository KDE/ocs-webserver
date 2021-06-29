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

class ActivityLog implements InputFilterAwareInterface
{
    public $activity_log_id;
    public $member_id;
    public $project_id;
    public $object_id;
    public $object_ref;
    public $object_title;
    public $object_text;
    public $object_img;
    public $activity_type_id;
    public $time;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->activity_log_id = !empty($data['activity_log_id']) ? $data['activity_log_id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->object_id = !empty($data['object_id']) ? $data['object_id'] : null;
        $this->object_ref = !empty($data['object_ref']) ? $data['object_ref'] : null;
        $this->object_title = !empty($data['object_title']) ? $data['object_title'] : null;
        $this->object_text = !empty($data['object_text']) ? $data['object_text'] : null;
        $this->object_img = !empty($data['object_img']) ? $data['object_img'] : null;
        $this->activity_type_id = !empty($data['activity_type_id']) ? $data['activity_type_id'] : null;
        $this->time = !empty($data['time']) ? $data['time'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'activity_log_id'  => $this->activity_log_id,
            'member_id'        => $this->member_id,
            'project_id'       => $this->project_id,
            'object_id'        => $this->object_id,
            'object_ref'       => $this->object_ref,
            'object_title'     => $this->object_title,
            'object_text'      => $this->object_text,
            'object_img'       => $this->object_img,
            'activity_type_id' => $this->activity_type_id,
            'time'             => $this->time,
        ];
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

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(
            sprintf(
                '%s does not allow injection of an alternate input filter', __CLASS__
            )
        );
    }
}
