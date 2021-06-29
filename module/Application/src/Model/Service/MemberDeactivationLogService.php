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

namespace Application\Model\Service;

use Application\Model\Repository\MemberDeactivationLogRepository;
use Application\Model\Service\Interfaces\MemberDeactivationLogServiceInterface;
use Laminas\Db\Adapter\AdapterInterface;

class MemberDeactivationLogService extends BaseService implements MemberDeactivationLogServiceInterface
{
    protected $db;
    private $deactLogRepository;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
        $this->deactLogRepository = new MemberDeactivationLogRepository($this->db);
    }

    /**
     * @param int $identifer
     *
     * @return void
     */
    public function logMemberAsDeleted($identifer)
    {
        $this->addLog($identifer, MemberDeactivationLogRepository::OBJ_TYPE_OPENDESKTOP_MEMBER, $identifer);
    }

    /**
     * @param int $member_id
     * @param int $object_type
     * @param int $identifier object id
     *
     * @return bool
     */
    public function addLog($member_id, $object_type, $identifier)
    {
        //$identity = Zend_Auth::getInstance()->getIdentity()->member_id;
        $identity = $GLOBALS['ocs_user']->member_id;

        return $this->deactLogRepository->addLog($member_id, $object_type, $identifier, $identity);
    }

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return bool
     */
    public function logMemberEmailAsDeleted($member_id, $identifer)
    {
        return $this->addLog($member_id, MemberDeactivationLogRepository::OBJ_TYPE_OPENDESKTOP_MEMBER_EMAIL, $identifer);
    }

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return void
     */
    public function logProjectAsDeleted($member_id, $identifer)
    {
        $this->addLog($member_id, MemberDeactivationLogRepository::OBJ_TYPE_OPENDESKTOP_PROJECT, $identifer);
    }

    /**
     * @param     $member_id
     * @param int $identifer
     *
     * @return void
     */
    public function logCommentAsDeleted($member_id, $identifer)
    {
        $this->addLog($member_id, MemberDeactivationLogRepository::OBJ_TYPE_OPENDESKTOP_COMMENT, $identifer);
    }

    /**
     * @param int    $member_id
     * @param int    $object_type
     * @param int    $identifier
     * @param string $data
     *
     * @return bool
     */
    public function addLogData($member_id, $object_type, $identifier, $data)
    {
        //$identity = Zend_Auth::getInstance()->getIdentity()->member_id;
        $identity = $GLOBALS['ocs_user']->member_id;

        return $this->deactLogRepository->addLogData($member_id, $object_type, $identifier, $data, $identity);
    }

    /**
     * @param int $identifer
     *
     * @return void
     */
    public function removeLogMemberAsDeleted($identifer)
    {
        $this->deleteLog($identifer, MemberDeactivationLogRepository::OBJ_TYPE_OPENDESKTOP_MEMBER, $identifer);
    }

    /**
     * @param int $member_id
     * @param int $object_type
     * @param int $identifer object id
     *
     * @return bool
     */
    public function deleteLog($member_id, $object_type, $identifer)
    {
        return $this->deactLogRepository->deleteLog($member_id, $object_type, $identifer);
    }

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return bool
     */
    public function removeLogMemberEmailAsDeleted($member_id, $identifer)
    {
        return $this->deleteLog($member_id, MemberDeactivationLogRepository::OBJ_TYPE_OPENDESKTOP_MEMBER_EMAIL, $identifer);
    }

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return bool
     */
    public function removeLogProjectAsDeleted($member_id, $identifer)
    {
        return $this->deleteLog($member_id, MemberDeactivationLogRepository::OBJ_TYPE_OPENDESKTOP_PROJECT, $identifer);
    }

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return bool
     */
    public function removeLogCommentAsDeleted($member_id, $identifer)
    {
        return $this->deleteLog($member_id, MemberDeactivationLogRepository::OBJ_TYPE_OPENDESKTOP_COMMENT, $identifer);
    }

    public function getLogEntries($member_id, $obj_type, $id)
    {
        return $this->deactLogRepository->getLogEntries($member_id, $obj_type, $id);
    }

    /**
     * @param int $member_id
     *
     * @return array
     */
    public function getLogForumPosts($member_id)
    {
        return $this->deactLogRepository->getLogForumPosts($member_id);
    }
}