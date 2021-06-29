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

namespace Application\Model\Service\Interfaces;

interface MemberDeactivationLogServiceInterface
{
    /**
     * @param int $identifer
     *
     * @return void
     */
    public function logMemberAsDeleted($identifer);

    /**
     * @param int $member_id
     * @param int $object_type
     * @param int $identifier object id
     *
     * @return void
     */
    public function addLog($member_id, $object_type, $identifier);

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return void
     */
    public function logMemberEmailAsDeleted($member_id, $identifer);

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return void
     */
    public function logProjectAsDeleted($member_id, $identifer);

    /**
     * @param     $member_id
     * @param int $identifer
     *
     * @return void
     */
    public function logCommentAsDeleted($member_id, $identifer);

    /**
     * @param int    $member_id
     * @param int    $object_type
     * @param int    $identifier
     * @param string $data
     *
     */
    public function addLogData($member_id, $object_type, $identifier, $data);

    /**
     * @param int $identifer
     *
     * @return void
     */
    public function removeLogMemberAsDeleted($identifer);

    /**
     * @param int $member_id
     * @param int $object_type
     * @param int $identifer object id
     *
     * @return void
     */
    public function deleteLog($member_id, $object_type, $identifer);

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return void
     */
    public function removeLogMemberEmailAsDeleted($member_id, $identifer);

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return void
     */
    public function removeLogProjectAsDeleted($member_id, $identifer);

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return void
     */
    public function removeLogCommentAsDeleted($member_id, $identifer);

    /**
     * @param $member_id
     * @param $obj_type
     * @param $id
     *
     * @return mixed
     */
    public function getLogEntries($member_id, $obj_type, $id);

    /**
     * @param int $member_id
     *
     * @return array
     */
    public function getLogForumPosts($member_id);
}