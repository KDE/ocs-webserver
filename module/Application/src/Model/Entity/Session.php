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

class Session implements InputFilterAwareInterface
{
    public $session_id;
    public $member_id;
    public $remember_me_id;
    public $expiry;
    public $created;
    public $changed;

    public function exchangeArray(array $data)
    {
        $this->session_id = !empty($data['session_id']) ? $data['session_id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->remember_me_id = !empty($data['remember_me_id']) ? $data['remember_me_id'] : null;
        $this->expiry = !empty($data['expiry']) ? $data['expiry'] : null;
        $this->created = !empty($data['created']) ? $data['created'] : null;
        $this->changed = !empty($data['changed']) ? $data['changed'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'session_id'     => $this->session_id,
            'member_id'      => $this->member_id,
            'remember_me_id' => $this->remember_me_id,
            'expiry'         => $this->expiry,
            'created'        => $this->created,
            'changed'        => $this->changed,

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