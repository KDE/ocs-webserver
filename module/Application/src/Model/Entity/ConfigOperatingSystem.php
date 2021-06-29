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

class ConfigOperatingSystem implements InputFilterAwareInterface
{
    public $os_id;
    public $name;
    public $displayname;
    public $order;
    public $created_at;
    public $changend_at;
    public $deleted_at;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->os_id = !empty($data['os_id']) ? $data['os_id'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->displayname = !empty($data['displayname']) ? $data['displayname'] : null;
        $this->order = !empty($data['order']) ? $data['order'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->changend_at = !empty($data['changend_at']) ? $data['changend_at'] : null;
        $this->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'os_id'       => $this->os_id,
            'name'        => $this->name,
            'displayname' => $this->displayname,
            'order'       => $this->order,
            'created_at'  => $this->created_at,
            'changend_at' => $this->changend_at,
            'deleted_at'  => $this->deleted_at,
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
