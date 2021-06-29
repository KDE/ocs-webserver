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

class ReportComments implements InputFilterAwareInterface
{
    public $report_id;
    public $project_id;
    public $comment_id;
    public $reported_by;
    public $is_deleted;
    public $is_active;
    public $created_at;
    public $user_ip;
    public $user_ip2;

    public function exchangeArray(array $data)
    {
        $this->report_id = !empty($data['report_id']) ? $data['report_id'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->comment_id = !empty($data['comment_id']) ? $data['comment_id'] : null;
        $this->reported_by = !empty($data['reported_by']) ? $data['reported_by'] : null;
        $this->is_deleted = !empty($data['is_deleted']) ? $data['is_deleted'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->user_ip = !empty($data['user_ip']) ? $data['user_ip'] : null;
        $this->user_ip2 = !empty($data['user_ip2']) ? $data['user_ip2'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'report_id'   => $this->report_id,
            'project_id'  => $this->project_id,
            'comment_id'  => $this->comment_id,
            'reported_by' => $this->reported_by,
            'is_deleted'  => $this->is_deleted,
            'is_active'   => $this->is_active,
            'created_at'  => $this->created_at,
            'user_ip'     => $this->user_ip,
            'user_ip2'    => $this->user_ip2,

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