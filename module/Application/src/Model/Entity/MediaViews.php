<?php
/**
 *  ocs-webserver
 *
 *  Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-webserver.
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

namespace Application\Model\Entity;

use DomainException;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class MediaViews implements InputFilterAwareInterface
{
    public $media_view_id;
    public $media_view_type_id;
    public $project_id;
    public $collection_id;
    public $file_id;
    public $member_id;
    public $referer;
    public $start_timestamp;
    public $stop_timestamp;
    public $ip;
    public $source;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->media_view_id = !empty($data['media_view_id']) ? $data['media_view_id'] : null;
        $this->media_view_type_id = !empty($data['media_view_type_id']) ? $data['media_view_type_id'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->collection_id = !empty($data['collection_id']) ? $data['collection_id'] : null;
        $this->file_id = !empty($data['file_id']) ? $data['file_id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->referer = !empty($data['referer']) ? $data['referer'] : null;
        $this->start_timestamp = !empty($data['start_timestamp']) ? $data['start_timestamp'] : null;
        $this->stop_timestamp = !empty($data['stop_timestamp']) ? $data['stop_timestamp'] : null;
        $this->ip = !empty($data['ip']) ? $data['ip'] : null;
        $this->source = !empty($data['source']) ? $data['source'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'media_view_id'      => $this->media_view_id,
            'media_view_type_id' => $this->media_view_type_id,
            'project_id'         => $this->project_id,
            'collection_id'      => $this->collection_id,
            'file_id'            => $this->file_id,
            'member_id'          => $this->member_id,
            'referer'            => $this->referer,
            'start_timestamp'    => $this->start_timestamp,
            'stop_timestamp'     => $this->stop_timestamp,
            'ip'                 => $this->ip,
            'source'             => $this->source,
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
