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

namespace Application\Model\Repository;

use Application\Model\Entity\MemberDeactivationLog;
use Application\Model\Interfaces\MemberDeactivationLogInterface;
use Exception;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;

class MemberDeactivationLogRepository extends BaseRepository implements MemberDeactivationLogInterface
{
    const OBJ_TYPE_OPENDESKTOP_MEMBER = 1;
    const OBJ_TYPE_OPENDESKTOP_MEMBER_EMAIL = 2;
    const OBJ_TYPE_OPENDESKTOP_PROJECT = 3;
    const OBJ_TYPE_OPENDESKTOP_COMMENT = 4;

    const OBJ_TYPE_GITLAB_USER = 20;
    const OBJ_TYPE_GITLAB_PROJECT = 21;

    const OBJ_TYPE_DISCOURSE_USER = 30;
    const OBJ_TYPE_DISCOURSE_POST = 31;
    const OBJ_TYPE_DISCOURSE_TOPIC = 32;

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "member_deactivation_log";
        $this->_key = "log_id";
        $this->_prototype = MemberDeactivationLog::class;
    }

    /**
     * @param int $member_id
     * @param int $object_type
     * @param int $identifier     object id
     * @param int $auth_member_id member_id of auth user
     *
     * @return bool
     */
    public function addLog($member_id, $object_type, $identifier, $auth_member_id)
    {
        $sql = "INSERT INTO `member_deactivation_log` (`deactivation_id`,`object_type_id`,`object_id`,`member_id`) VALUES (:deactivation_id,:object_type_id,:object_id,:member_id)";

        try {
            $this->db->query(
                $sql, array(
                'deactivation_id' => $member_id,
                'object_type_id'  => $object_type,
                'object_id'       => $identifier,
                'member_id'       => $auth_member_id,
            )
            );

            return true;
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - ERROR write member deactivation log - ' . print_r($e, true));

            return false;
        }
    }

    /**
     * @param int    $member_id
     * @param int    $object_type
     * @param int    $identifier
     * @param string $data
     * @param        $auth_member_id
     *
     * @return bool
     */
    public function addLogData($member_id, $object_type, $identifier, $data, $auth_member_id)
    {
        $sql = "INSERT INTO `member_deactivation_log` (`deactivation_id`,`object_type_id`,`object_id`,`member_id`, `object_data`) VALUES (:deactivation_id,:object_type_id,:object_id,:member_id,:object_data)";

        try {
            $this->db->query(
                $sql, array(
                'deactivation_id' => $member_id,
                'object_type_id'  => $object_type,
                'object_id'       => $identifier,
                'member_id'       => $auth_member_id,
                'object_data'     => $data,
            )
            );

            return true;
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - ERROR write member deactivation log - ' . print_r($e, true));

            return false;
        }
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
        $sql = "UPDATE `member_deactivation_log` SET `is_deleted` = 1, `deleted_at` = NOW() WHERE  `deactivation_id` = :deactivation_id AND `object_type_id` = :object_type_id AND `object_id` = :object_id";

        try {
            $this->db->query(
                $sql, array(
                'deactivation_id' => $member_id,
                'object_type_id'  => $object_type,
                'object_id'       => $identifer,
            )
            );

            return true;
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - ERROR write member deactivation log - ' . print_r($e, true));

            return false;
        }
    }

    /**
     * @param $member_id
     * @param $obj_type
     * @param $id
     *
     * @return array|\ArrayObject
     */
    public function getLogEntries($member_id, $obj_type, $id)
    {
        $sql = "SELECT * FROM `member_deactivation_log` WHERE `deactivation_id` = :memberid AND `object_type_id` = :objecttype AND `object_id` = :objectid AND `is_deleted` = 0";
        $result = array();

        try {
            $result = $this->fetchRow(
                $sql, array(
                'memberid'   => $member_id,
                'objecttype' => $obj_type,
                'objectid'   => $id,
            )
            );
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - ERROR READ member deactivation log - ' . print_r($e, true));
        }

        return $result;
    }

    /**
     * @param int $member_id
     *
     * @return array
     */
    public function getLogForumPosts($member_id)
    {
        $sql = "SELECT * FROM `member_deactivation_log` WHERE `deactivation_id` = :memberid AND (`object_type_id` = " . self::OBJ_TYPE_DISCOURSE_TOPIC . " OR `object_type_id` = " . self::OBJ_TYPE_DISCOURSE_POST . ") AND `is_deleted` = 0";
        $result = array();

        try {
            $result = $this->fetchAll($sql, array('memberid' => $member_id));
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - ERROR READ member deactivation log - ' . print_r($e, true));
        }

        $posts = array();

        foreach ($result as $item) {
            if (self::OBJ_TYPE_DISCOURSE_TOPIC == $item['object_type_id']) {
                $posts['topics'][$item['object_id']] = $item;
            }
            if (self::OBJ_TYPE_DISCOURSE_POST == $item['object_type_id']) {
                $posts['posts'][$item['object_id']] = $item;
            }
        }

        return $posts;
    }
}
