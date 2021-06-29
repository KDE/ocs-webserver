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

class ConfigStoreCategory implements InputFilterAwareInterface
{
    public $store_category_id;
    public $store_id;
    public $project_category_id;
    public $order;
    public $created_at;
    public $changed_at;
    public $deleted_at;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->store_category_id = !empty($data['store_category_id']) ? $data['store_category_id'] : null;
        $this->store_id = !empty($data['store_id']) ? $data['store_id'] : null;
        $this->project_category_id = !empty($data['project_category_id']) ? $data['project_category_id'] : null;
        $this->order = !empty($data['order']) ? $data['order'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->changed_at = !empty($data['changed_at']) ? $data['changed_at'] : null;
        $this->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'store_category_id'   => $this->store_category_id,
            'store_id'            => $this->store_id,
            'project_category_id' => $this->project_category_id,
            'order'               => $this->order,
            'created_at'          => $this->created_at,
            'changed_at'          => $this->changed_at,
            'deleted_at'          => $this->deleted_at,
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