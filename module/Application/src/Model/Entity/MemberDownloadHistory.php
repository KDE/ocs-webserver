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

class MemberDownloadHistory implements InputFilterAwareInterface
{
    public $id;
    public $member_id;
    public $anonymous_cookie;
    public $project_id;
    public $file_id;
    public $file_name;
    public $file_type;
    public $file_size;
    public $downloaded_timestamp;
    public $downloaded_ip;
    public $HTTP_X_FORWARDED_FOR;
    public $HTTP_X_FORWARDED;
    public $HTTP_CLIENT_IP;
    public $HTTP_FORWARDED_FOR;
    public $HTTP_FORWARDED;
    public $REMOTE_ADDR;
    public $server_info;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->anonymous_cookie = !empty($data['anonymous_cookie']) ? $data['anonymous_cookie'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->file_id = !empty($data['file_id']) ? $data['file_id'] : null;
        $this->file_name = !empty($data['file_name']) ? $data['file_name'] : null;
        $this->file_type = !empty($data['file_type']) ? $data['file_type'] : null;
        $this->file_size = !empty($data['file_size']) ? $data['file_size'] : null;
        $this->downloaded_timestamp = !empty($data['downloaded_timestamp']) ? $data['downloaded_timestamp'] : null;
        $this->downloaded_ip = !empty($data['downloaded_ip']) ? $data['downloaded_ip'] : null;
        $this->HTTP_X_FORWARDED_FOR = !empty($data['HTTP_X_FORWARDED_FOR']) ? $data['HTTP_X_FORWARDED_FOR'] : null;
        $this->HTTP_X_FORWARDED = !empty($data['HTTP_X_FORWARDED']) ? $data['HTTP_X_FORWARDED'] : null;
        $this->HTTP_CLIENT_IP = !empty($data['HTTP_CLIENT_IP']) ? $data['HTTP_CLIENT_IP'] : null;
        $this->HTTP_FORWARDED_FOR = !empty($data['HTTP_FORWARDED_FOR']) ? $data['HTTP_FORWARDED_FOR'] : null;
        $this->HTTP_FORWARDED = !empty($data['HTTP_FORWARDED']) ? $data['HTTP_FORWARDED'] : null;
        $this->REMOTE_ADDR = !empty($data['REMOTE_ADDR']) ? $data['REMOTE_ADDR'] : null;
        $this->server_info = !empty($data['server_info']) ? $data['server_info'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'id'                   => $this->id,
            'member_id'            => $this->member_id,
            'anonymous_cookie'     => $this->anonymous_cookie,
            'project_id'           => $this->project_id,
            'file_id'              => $this->file_id,
            'file_name'            => $this->file_name,
            'file_type'            => $this->file_type,
            'file_size'            => $this->file_size,
            'downloaded_timestamp' => $this->downloaded_timestamp,
            'downloaded_ip'        => $this->downloaded_ip,
            'HTTP_X_FORWARDED_FOR' => $this->HTTP_X_FORWARDED_FOR,
            'HTTP_X_FORWARDED'     => $this->HTTP_X_FORWARDED,
            'HTTP_CLIENT_IP'       => $this->HTTP_CLIENT_IP,
            'HTTP_FORWARDED_FOR'   => $this->HTTP_FORWARDED_FOR,
            'HTTP_FORWARDED'       => $this->HTTP_FORWARDED,
            'REMOTE_ADDR'          => $this->REMOTE_ADDR,
            'server_info'          => $this->server_info,
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
