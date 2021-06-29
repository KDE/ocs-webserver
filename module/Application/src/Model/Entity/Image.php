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

class Image implements InputFilterAwareInterface
{
    // attributes
    public $id;
    public $code;
    public $filename;
    public $name;
    public $member_id;
    public $model;
    public $foreign_key;
    public $foreign_id;
    public $created;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->code = !empty($data['code']) ? $data['code'] : null;
        $this->filename = !empty($data['filename']) ? $data['filename'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->model = !empty($data['model']) ? $data['model'] : null;
        $this->foreign_key = !empty($data['foreign_key']) ? $data['foreign_key'] : null;
        $this->foreign_id = !empty($data['foreign_id']) ? $data['foreign_id'] : null;
        $this->created = !empty($data['created']) ? $data['created'] : null;

    }

    /**
     * SELECT CONCAT('\'',COLUMN_NAME,'\'','=> $this->',COLUMN_NAME,',')
     * FROM INFORMATION_SCHEMA.COLUMNS
     * WHERE  TABLE_NAME = 'member' AND TABLE_SCHEMA = 'pling';
     */
    public function getArrayCopy()
    {
        return [
            'id'          => $this->id,
            'code'        => $this->code,
            'filename'    => $this->filename,
            'name'        => $this->name,
            'member_id'   => $this->member_id,
            'model'       => $this->model,
            'foreign_key' => $this->foreign_key,
            'foreign_id'  => $this->foreign_id,
            'created'     => $this->created,

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