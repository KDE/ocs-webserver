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

namespace Application\Model\Interfaces;

use Application\Model\Repository\MemberRepository;
use Laminas\Db\ResultSet\ResultSet;

interface MemberInterface extends BaseInterface
{
    /**
     * @param integer $member_id
     * @param bool    $onlyNotDeleted
     *
     * @return array
     */
    public function fetchMemberData($member_id, $onlyNotDeleted = true);

    /**
     * @param string $hash
     * @param bool   $only_active
     *
     * @return array | false
     */
    public function findMemberForMailHash($hash, $only_active = true);

    /**
     * @param string $value
     * @param int    $test_case_sensitive
     * @param array  $omitMember
     * @param bool   $onlyActive
     *
     * @return array return an array of rows
     */
    public function findUsername(
        $value,
        $test_case_sensitive = MemberRepository::CASE_INSENSITIVE,
        $omitMember = array(),
        $onlyActive = false
    );

    /**
     * @param string $token
     *
     * @return ResultSet
     */
    public function findOneByToken($token);

}