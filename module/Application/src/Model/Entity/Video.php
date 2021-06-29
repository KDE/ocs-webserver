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

class Video implements InputFilterAwareInterface
{
    public $url_thumb;
    public $url_preview;
    public $id;
    public $file_id;
    public $create_timestamp;
    public $collection_id;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->collection_id = !empty($data['collection_id']) ? $data['collection_id'] : null;
        $this->file_id = !empty($data['file_id']) ? $data['file_id'] : null;
        $this->url_preview = !empty($data['url_preview']) ? $data['url_preview'] : null;
        $this->url_thumb = !empty($data['url_thumb']) ? $data['url_thumb'] : null;
        $this->create_timestamp = !empty($data['create_timestamp']) ? $data['create_timestamp'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'id'               => $this->id,
            'collection_id'    => $this->collection_id,
            'file_id'          => $this->file_id,
            'url_preview'      => $this->url_preview,
            'url_thumb'        => $this->url_thumb,
            'create_timestamp' => $this->create_timestamp,
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