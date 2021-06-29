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

class MemberToken implements InputFilterAwareInterface
{
    public $token_id;
    public $token_member_id;
    public $token_provider_name;
    public $token_value;
    public $token_provider_username;
    public $token_fingerprint;
    public $token_created;
    public $token_changed;
    public $token_deleted;

    public function exchangeArray(array $data)
    {
        $this->token_id = !empty($data['token_id']) ? $data['token_id'] : null;
        $this->token_member_id = !empty($data['token_member_id']) ? $data['token_member_id'] : null;
        $this->token_provider_name = !empty($data['token_provider_name']) ? $data['token_provider_name'] : null;
        $this->token_value = !empty($data['token_value']) ? $data['token_value'] : null;
        $this->token_provider_username = !empty($data['token_provider_username']) ? $data['token_provider_username'] : null;
        $this->token_fingerprint = !empty($data['token_fingerprint']) ? $data['token_fingerprint'] : null;
        $this->token_created = !empty($data['token_created']) ? $data['token_created'] : null;
        $this->token_changed = !empty($data['token_changed']) ? $data['token_changed'] : null;
        $this->token_deleted = !empty($data['token_deleted']) ? $data['token_deleted'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'token_id'                => $this->token_id,
            'token_member_id'         => $this->token_member_id,
            'token_provider_name'     => $this->token_provider_name,
            'token_value'             => $this->token_value,
            'token_provider_username' => $this->token_provider_username,
            'token_fingerprint'       => $this->token_fingerprint,
            'token_created'           => $this->token_created,
            'token_changed'           => $this->token_changed,
            'token_deleted'           => $this->token_deleted,
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