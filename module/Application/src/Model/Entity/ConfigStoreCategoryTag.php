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

class ConfigStoreCategoryTag implements InputFilterAwareInterface
{
    public $changed_at;
    public $config_store_category_id;
    public $config_store_category_tag_id;
    public $created_at;
    public $deleted_at;
    public $is_active;
    public $tag_id;


    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->changed_at= isset($data['changed_at']) ? $data['changed_at'] : null;
        $this->config_store_category_id= isset($data['config_store_category_id']) ? $data['config_store_category_id'] : null;
        $this->config_store_category_tag_id= isset($data['config_store_category_tag_id']) ? $data['config_store_category_tag_id'] : null;
        $this->created_at= isset($data['created_at']) ? $data['created_at'] : null;
        $this->deleted_at= isset($data['deleted_at']) ? $data['deleted_at'] : null;
        $this->is_active= isset($data['is_active']) ? $data['is_active'] : null;
        $this->tag_id= isset($data['tag_id']) ? $data['tag_id'] : null;     
    }

    public function getArrayCopy()
    {
        return [
            'changed_at'=> $this->changed_at,
            'config_store_category_id'=> $this->config_store_category_id,
            'config_store_category_tag_id'=> $this->config_store_category_tag_id,
            'created_at'=> $this->created_at,
            'deleted_at'=> $this->deleted_at,
            'is_active'=> $this->is_active,
            'tag_id'=> $this->tag_id,            
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
