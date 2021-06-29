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

class MemberEmail implements InputFilterAwareInterface
{
    public $email_id;
    public $email_member_id;
    public $email_address;
    public $email_primary;
    public $email_deleted;
    public $email_created;
    public $email_checked;
    public $email_verification_value;
    public $email_hash;

    public function exchangeArray(array $data)
    {
        $this->email_id = !empty($data['email_id']) ? $data['email_id'] : null;
        $this->email_member_id = !empty($data['email_member_id']) ? $data['email_member_id'] : null;
        $this->email_address = !empty($data['email_address']) ? $data['email_address'] : null;
        $this->email_primary = !empty($data['email_primary']) ? $data['email_primary'] : null;
        $this->email_deleted = !empty($data['email_deleted']) ? $data['email_deleted'] : null;
        $this->email_created = !empty($data['email_created']) ? $data['email_created'] : null;
        $this->email_checked = !empty($data['email_checked']) ? $data['email_checked'] : null;
        $this->email_verification_value = !empty($data['email_verification_value']) ? $data['email_verification_value'] : null;
        $this->email_hash = !empty($data['email_hash']) ? $data['email_hash'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'email_id'                 => $this->email_id,
            'email_member_id'          => $this->email_member_id,
            'email_address'            => $this->email_address,
            'email_primary'            => $this->email_primary,
            'email_deleted'            => $this->email_deleted,
            'email_created'            => $this->email_created,
            'email_checked'            => $this->email_checked,
            'email_verification_value' => $this->email_verification_value,
            'email_hash'               => $this->email_hash,
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