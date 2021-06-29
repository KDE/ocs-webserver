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

class SpamKeywords implements InputFilterAwareInterface
{
    public $spam_key_id;
    public $spam_key_word;
    public $spam_key_created_at;
    public $spam_key_is_deleted;
    public $spam_key_is_active;

    public function exchangeArray(array $data)
    {
        $this->spam_key_id = !empty($data['spam_key_id']) ? $data['spam_key_id'] : null;
        $this->spam_key_word = !empty($data['spam_key_word']) ? $data['spam_key_word'] : null;
        $this->spam_key_created_at = !empty($data['spam_key_created_at']) ? $data['spam_key_created_at'] : null;
        $this->spam_key_is_deleted = !empty($data['spam_key_is_deleted']) ? $data['spam_key_is_deleted'] : null;
        $this->spam_key_is_active = !empty($data['spam_key_is_active']) ? $data['spam_key_is_active'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'spam_key_id'         => $this->spam_key_id,
            'spam_key_word'       => $this->spam_key_word,
            'spam_key_created_at' => $this->spam_key_created_at,
            'spam_key_is_deleted' => $this->spam_key_is_deleted,
            'spam_key_is_active'  => $this->spam_key_is_active,

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