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

namespace Application\Model\Interfaces;

use Laminas\Db\ResultSet\ResultSet;

interface MemberDeactivationLogInterface
{
    /**
     * @param int $member_id
     * @param int $object_type
     * @param int $identifier     object id
     * @param int $auth_member_id member_id of auth user
     *
     * @return bool
     */
    public function addLog($member_id, $object_type, $identifier, $auth_member_id);

    /**
     * @param int    $member_id
     * @param int    $object_type
     * @param int    $identifier
     * @param string $data
     *
     * @return bool
     */
    public function addLogData($member_id, $object_type, $identifier, $data, $auth_member_id);

    /**
     * @param int $member_id
     * @param int $object_type
     * @param int $identifer object id
     *
     * @return bool
     */
    public function deleteLog($member_id, $object_type, $identifer);

    /**
     * @param $member_id
     * @param $obj_type
     * @param $id
     *
     * @return ResultSet
     */
    public function getLogEntries($member_id, $obj_type, $id);

    /**
     * @param int $member_id
     *
     * @return array
     */
    public function getLogForumPosts($member_id);
}