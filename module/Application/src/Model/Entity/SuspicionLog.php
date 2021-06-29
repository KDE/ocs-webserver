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

class SuspicionLog implements InputFilterAwareInterface
{
    public $suspicion_id;
    public $project_id;
    public $member_id;
    public $http_referer;
    public $http_origin;
    public $client_ip;
    public $user_agent;
    public $suspicious;

    public function exchangeArray(array $data)
    {
        $this->suspicion_id = !empty($data['suspicion_id']) ? $data['suspicion_id'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->http_referer = !empty($data['http_referer']) ? $data['http_referer'] : null;
        $this->http_origin = !empty($data['http_origin']) ? $data['http_origin'] : null;
        $this->client_ip = !empty($data['client_ip']) ? $data['client_ip'] : null;
        $this->user_agent = !empty($data['user_agent']) ? $data['user_agent'] : null;
        $this->suspicious = !empty($data['suspicious']) ? $data['suspicious'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'suspicion_id' => $this->suspicion_id,
            'project_id'   => $this->project_id,
            'member_id'    => $this->member_id,
            'http_referer' => $this->http_referer,
            'http_origin'  => $this->http_origin,
            'client_ip'    => $this->client_ip,
            'user_agent'   => $this->user_agent,
            'suspicious'   => $this->suspicious,

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