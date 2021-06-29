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

class LoginHistory implements InputFilterAwareInterface
{
    public $id;
    public $member_id;
    public $login_at;
    public $ip;
    public $ip_inet;
    public $ipv4;
    public $ipv4_inet;
    public $ipv6;
    public $ipv6_inet;
    public $browser;
    public $os;
    public $architecture;
    public $fingerprint;
    public $user_agent;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->login_at = !empty($data['login_at']) ? $data['login_at'] : null;
        $this->ip = !empty($data['ip']) ? $data['ip'] : null;
        $this->ip_inet = !empty($data['ip_inet']) ? $data['ip_inet'] : null;
        $this->ipv4 = !empty($data['ipv4']) ? $data['ipv4'] : null;
        $this->ipv4_inet = !empty($data['ipv4_inet']) ? $data['ipv4_inet'] : null;
        $this->ipv6 = !empty($data['ipv6']) ? $data['ipv6'] : null;
        $this->ipv6_inet = !empty($data['ipv6_inet']) ? $data['ipv6_inet'] : null;
        $this->browser = !empty($data['browser']) ? $data['browser'] : null;
        $this->os = !empty($data['os']) ? $data['os'] : null;
        $this->architecture = !empty($data['architecture']) ? $data['architecture'] : null;
        $this->fingerprint = !empty($data['fingerprint']) ? $data['fingerprint'] : null;
        $this->user_agent = !empty($data['user_agent']) ? $data['user_agent'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'id'           => $this->id,
            'member_id'    => $this->member_id,
            'login_at'     => $this->login_at,
            'ip'           => $this->ip,
            'ip_inet'      => $this->ip_inet,
            'ipv4'         => $this->ipv4,
            'ipv4_inet'    => $this->ipv4_inet,
            'ipv6'         => $this->ipv6,
            'ipv6_inet'    => $this->ipv6_inet,
            'browser'      => $this->browser,
            'os'           => $this->os,
            'architecture' => $this->architecture,
            'fingerprint'  => $this->fingerprint,
            'user_agent'   => $this->user_agent,
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
