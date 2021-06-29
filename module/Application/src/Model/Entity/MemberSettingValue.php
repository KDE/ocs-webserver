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

class MemberSettingValue implements InputFilterAwareInterface
{
    public $member_setting_value_id;
    public $member_setting_item_id;
    public $value;
    public $member_id;
    public $created_at;
    public $changed_at;
    public $deleted_at;
    public $is_active;

    public function exchangeArray(array $data)
    {
        $this->member_setting_value_id = !empty($data['member_setting_value_id']) ? $data['member_setting_value_id'] : null;
        $this->member_setting_item_id = !empty($data['member_setting_item_id']) ? $data['member_setting_item_id'] : null;
        $this->value = !empty($data['value']) ? $data['value'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->changed_at = !empty($data['changed_at']) ? $data['changed_at'] : null;
        $this->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'member_setting_value_id' => $this->member_setting_value_id,
            'member_setting_item_id'  => $this->member_setting_item_id,
            'value'                   => $this->value,
            'member_id'               => $this->member_id,
            'created_at'              => $this->created_at,
            'changed_at'              => $this->changed_at,
            'deleted_at'              => $this->deleted_at,
            'is_active'               => $this->is_active,
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