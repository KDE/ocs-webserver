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
 *
 *    Created: 22.09.2016
 **/
class Default_Model_MemberDeactivationLog extends Default_Model_DbTable_MemberDeactivationLog
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


    /**
     * @param int $identifer
     *
     * @return void
     * @throws Zend_Exception
     */
    public function logMemberAsDeleted($identifer)
    {
        return $this->addLog($identifer, Default_Model_MemberDeactivationLog::OBJ_TYPE_OPENDESKTOP_MEMBER, $identifer);
    }

    /**
     * @param int $member_id
     * @param int $object_type
     * @param int $identifier object id
     *
     * @return void
     * @throws Zend_Exception
     */
    public function addLog($member_id, $object_type, $identifier)
    {
        $identity = Zend_Auth::getInstance()->getIdentity()->member_id;

        $sql =
            "INSERT INTO `member_deactivation_log` (`deactivation_id`,`object_type_id`,`object_id`,`member_id`) VALUES (:deactivation_id,:object_type_id,:object_id,:member_id)";

        try {
            Zend_Db_Table::getDefaultAdapter()->query($sql, array(
                'deactivation_id' => $member_id,
                'object_type_id'  => $object_type,
                'object_id'       => $identifier,
                'member_id'       => $identity
            ))
            ;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ERROR write member deactivation log - ' . print_r($e, true));
        }
    }

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return void
     * @throws Zend_Exception
     */
    public function logMemberEmailAsDeleted($member_id, $identifer)
    {
        return $this->addLog($member_id, Default_Model_MemberDeactivationLog::OBJ_TYPE_OPENDESKTOP_MEMBER_EMAIL, $identifer);
    }

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return void
     * @throws Zend_Exception
     */
    public function logProjectAsDeleted($member_id, $identifer)
    {
        return $this->addLog($member_id, Default_Model_MemberDeactivationLog::OBJ_TYPE_OPENDESKTOP_PROJECT, $identifer);
    }

    /**
     * @param     $member_id
     * @param int $identifer
     *
     * @return void
     * @throws Zend_Exception
     */
    public function logCommentAsDeleted($member_id, $identifer)
    {
        return $this->addLog($member_id, Default_Model_MemberDeactivationLog::OBJ_TYPE_OPENDESKTOP_COMMENT, $identifer);
    }

    /**
     * @param int $member_id
     * @param int $object_type
     * @param int $identifier
     * @param string $data
     *
     * @throws Zend_Exception
     */
    public function addLogData($member_id, $object_type, $identifier, $data)
    {
        $identity = Zend_Auth::getInstance()->getIdentity()->member_id;

        $sql =
            "INSERT INTO `member_deactivation_log` (`deactivation_id`,`object_type_id`,`object_id`,`member_id`, `object_data`) VALUES (:deactivation_id,:object_type_id,:object_id,:member_id,:object_data)";

        try {
            Zend_Db_Table::getDefaultAdapter()->query($sql, array(
                'deactivation_id' => $member_id,
                'object_type_id'  => $object_type,
                'object_id'       => $identifier,
                'member_id'       => $identity,
                'object_data'     => $data
            ))
            ;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ERROR write member deactivation log - ' . print_r($e, true));
        }
    }

    /**
     * @param int $identifer
     *
     * @return void
     * @throws Zend_Exception
     */
    public function removeLogMemberAsDeleted($identifer)
    {
        return $this->deleteLog($identifer, Default_Model_MemberDeactivationLog::OBJ_TYPE_OPENDESKTOP_MEMBER, $identifer);
    }

    /**
     * @param int $member_id
     * @param int $object_type
     * @param int $identifer object id
     *
     * @return void
     * @throws Zend_Exception
     */
    public function deleteLog($member_id, $object_type, $identifer)
    {
        $identity = Zend_Auth::getInstance()->getIdentity()->member_id;

        $sql =
            "UPDATE `member_deactivation_log` SET is_deleted = 1, deleted_at = NOW() WHERE  deactivation_id = :deactivation_id AND object_type_id = :object_type_id AND object_id = :object_id";

        try {
            Zend_Db_Table::getDefaultAdapter()->query($sql,
                array('deactivation_id' => $member_id, 'object_type_id' => $object_type, 'object_id' => $identifer))
            ;
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ERROR write member deactivation log - ' . print_r($e, true));
        }
    }

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return void
     * @throws Zend_Exception
     */
    public function removeLogMemberEmailAsDeleted($member_id, $identifer)
    {
        return $this->deleteLog($member_id, Default_Model_MemberDeactivationLog::OBJ_TYPE_OPENDESKTOP_MEMBER_EMAIL, $identifer);
    }

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return void
     * @throws Zend_Exception
     */
    public function removeLogProjectAsDeleted($member_id, $identifer)
    {
        return $this->deleteLog($member_id, Default_Model_MemberDeactivationLog::OBJ_TYPE_OPENDESKTOP_PROJECT, $identifer);
    }

    /**
     * @param int $member_id
     * @param int $identifer
     *
     * @return void
     * @throws Zend_Exception
     */
    public function removeLogCommentAsDeleted($member_id, $identifer)
    {
        return $this->deleteLog($member_id, Default_Model_MemberDeactivationLog::OBJ_TYPE_OPENDESKTOP_COMMENT, $identifer);
    }

    public function getLogEntries($member_id, $obj_type, $id)
    {
        $sql = "SELECT * FROM member_deactivation_log WHERE deactivation_id = :memberid AND object_type_id = :objecttype AND object_id = :objectid AND is_deleted = 0";
        $result = array();

        try {
            $result = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql, array('memberid' => $member_id, 'objecttype' => $obj_type, 'objectid' => $id));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ERROR READ member deactivation log - ' . print_r($e, true));
        }

        return $result;
    }

    /**
     * @param int $member_id
     *
     * @return array
     * @throws Zend_Exception
     */
    public function getLogForumPosts($member_id)
    {
        $sql = "SELECT * FROM member_deactivation_log WHERE deactivation_id = :memberid AND (object_type_id = ".self::OBJ_TYPE_DISCOURSE_TOPIC." OR object_type_id = ".self::OBJ_TYPE_DISCOURSE_POST.") AND is_deleted = 0";
        $result = array();

        try {
            $result = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('memberid' => $member_id));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ERROR READ member deactivation log - ' . print_r($e, true));
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