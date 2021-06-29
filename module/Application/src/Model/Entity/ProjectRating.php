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

class ProjectRating implements InputFilterAwareInterface
{
    // attributes
    public $rating_id;
    public $member_id;
    public $project_id;
    public $user_like;
    public $user_dislike;
    public $score;
    public $score_test;
    public $comment_id;
    public $rating_active;
    public $source_id;
    public $source_pk;
    public $created_at;

    public function exchangeArray(array $data)
    {
        $this->rating_id = !empty($data['rating_id']) ? $data['rating_id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->user_like = !empty($data['user_like']) ? $data['user_like'] : null;
        $this->user_dislike = !empty($data['user_dislike']) ? $data['user_dislike'] : null;
        $this->score = !empty($data['score']) ? $data['score'] : null;
        $this->score_test = !empty($data['score_test']) ? $data['score_test'] : null;
        $this->comment_id = !empty($data['comment_id']) ? $data['comment_id'] : null;
        $this->rating_active = !empty($data['rating_active']) ? $data['rating_active'] : null;
        $this->source_id = !empty($data['source_id']) ? $data['source_id'] : null;
        $this->source_pk = !empty($data['source_pk']) ? $data['source_pk'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'rating_id'     => $this->rating_id,
            'member_id'     => $this->member_id,
            'project_id'    => $this->project_id,
            'user_like'     => $this->user_like,
            'user_dislike'  => $this->user_dislike,
            'score'         => $this->score,
            'score_test'    => $this->score_test,
            'comment_id'    => $this->comment_id,
            'rating_active' => $this->rating_active,
            'source_id'     => $this->source_id,
            'source_pk'     => $this->source_pk,
            'created_at'    => $this->created_at,

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