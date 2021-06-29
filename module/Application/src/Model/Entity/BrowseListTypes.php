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

class BrowseListTypes implements InputFilterAwareInterface
{
    // attributes
    public $browse_list_type_id;
    public $name;
    public $desc;
    public $render_page_name;
    public $is_active;
    public $deleted_at;

    public function exchangeArray(array $data)
    {
        $this->browse_list_type_id = !empty($data['browse_list_type_id']) ? $data['browse_list_type_id'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->desc = !empty($data['desc']) ? $data['desc'] : null;
        $this->render_page_name = !empty($data['render_page_name']) ? $data['render_page_name'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;
        $this->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'browse_list_type_id' => $this->browse_list_type_id,
            'name'                => $this->name,
            'desc'                => $this->desc,
            'render_page_name'    => $this->render_page_name,
            'is_active'           => $this->is_active,
            'deleted_at'          => $this->deleted_at,

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
