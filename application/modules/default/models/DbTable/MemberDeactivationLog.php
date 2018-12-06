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
class Default_Model_DbTable_MemberDeactivationLog extends Local_Model_Table
{

    const OBJ_TYPE_OPENDESKTOP_MEMBER = 1;
    const OBJ_TYPE_OPENDESKTOP_MEMBER_EMAIL = 2;
    const OBJ_TYPE_OPENDESKTOP_PROJECT = 3;
    const OBJ_TYPE_OPENDESKTOP_COMMENT = 4;

    const OBJ_TYPE_GITLAB_USER = 20;
    const OBJ_TYPE_GITLAB_PROJECT = 21;

    const OBJ_TYPE_DISCOURSE_USER = 30;
    const OBJ_TYPE_DISCOURSE_TOPIC = 31;

    protected $_keyColumnsForRow = array('log_id');
    protected $_key = 'log_id';
    protected $_name = "member_deactivation_log";
    
    /**
     * @param int $identifer
     *
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function logMemberAsDeleted($identifer)
    {
        return $this->addLog(Default_Model_DbTable_MemberDeactivationLog::OBJ_TYPE_OPENDESKTOP_MEMBER, $identifer);
    }
    
    /**
     * @param int $identifer
     *
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function logMemberEmailAsDeleted($identifer)
    {
        return $this->addLog(Default_Model_DbTable_MemberDeactivationLog::OBJ_TYPE_OPENDESKTOP_MEMBER_EMAIL, $identifer);
    }
    
    /**
     * @param int $identifer
     *
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function logProjectAsDeleted($identifer)
    {
        return $this->addLog(Default_Model_DbTable_MemberDeactivationLog::OBJ_TYPE_OPENDESKTOP_PROJECT);
    }
    
    /**
     * @param int $identifer
     *
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function logCommentAsDeleted($identifer)
    {
        return $this->addLog(Default_Model_DbTable_MemberDeactivationLog::OBJ_TYPE_OPENDESKTOP_COMMENT, $identifer);
    }

    /**
     * @param int $email_id
     *
     * @return int|void
     * @throws Zend_Db_Statement_Exception
     */
    public function addLog($object_type, $identifer)
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        
        $nextId = $this->fetchNextLogId();
        $sql = "INSERT INTO `{$this->_name}` (deactivation_id,object_type_id,object_id,member_id) VALUES (:deactivation_id,:object_type_id,:object_id,:member_id)";
        $stmnt = $this->_db->query($sql, array('deactivation_id' => $nextId, 'object_type_id' => $object_type,'object_id' => $identifer, 'member_id' => $identity));

        return $stmnt->rowCount();
    }

    public function fetchNextLogId()
    {
        $sql = "
                  SELECT
                      max(deactivation_id)+1 AS next_id
                  FROM
                      `{$this->_name}`
                 ";
        $result = $this->_db->fetchRow($sql);

        return $result['next_id'];
    }

}