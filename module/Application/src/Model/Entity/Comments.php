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

class Comments implements InputFilterAwareInterface
{
    public $comment_id;
    public $comment_target_id;
    public $comment_member_id;
    public $comment_parent_id;
    public $comment_type;
    public $comment_pling_id;
    public $comment_text;
    public $comment_active;
    public $comment_created_at;
    public $comment_deleted_at;
    public $source_id;
    public $source_pk;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->comment_id = !empty($data['comment_id']) ? $data['comment_id'] : null;
        $this->comment_target_id = !empty($data['comment_target_id']) ? $data['comment_target_id'] : null;
        $this->comment_member_id = !empty($data['comment_member_id']) ? $data['comment_member_id'] : null;
        $this->comment_parent_id = !empty($data['comment_parent_id']) ? $data['comment_parent_id'] : null;
        $this->comment_type = !empty($data['comment_type']) ? $data['comment_type'] : null;
        $this->comment_pling_id = !empty($data['comment_pling_id']) ? $data['comment_pling_id'] : null;
        $this->comment_text = !empty($data['comment_text']) ? $data['comment_text'] : null;
        $this->comment_active = !empty($data['comment_active']) ? $data['comment_active'] : null;
        $this->comment_created_at = !empty($data['comment_created_at']) ? $data['comment_created_at'] : null;
        $this->comment_deleted_at = !empty($data['comment_deleted_at']) ? $data['comment_deleted_at'] : null;
        $this->source_id = !empty($data['source_id']) ? $data['source_id'] : null;
        $this->source_pk = !empty($data['source_pk']) ? $data['source_pk'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'comment_id'         => $this->comment_id,
            'comment_target_id'  => $this->comment_target_id,
            'comment_member_id'  => $this->comment_member_id,
            'comment_parent_id'  => $this->comment_parent_id,
            'comment_type'       => $this->comment_type,
            'comment_pling_id'   => $this->comment_pling_id,
            'comment_text'       => $this->comment_text,
            'comment_active'     => $this->comment_active,
            'comment_created_at' => $this->comment_created_at,
            'comment_deleted_at' => $this->comment_deleted_at,
            'source_id'          => $this->source_id,
            'source_pk'          => $this->source_pk,
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
