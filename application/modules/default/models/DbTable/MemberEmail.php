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
class Default_Model_DbTable_MemberEmail extends Local_Model_Table
{

    const EMAIL_DELETED = 1;
    const EMAIL_NOT_DELETED = 0;

    const EMAIL_PRIMARY = 1;
    const EMAIL_NOT_PRIMARY = 0;

    protected $_name = "member_email";

    protected $_keyColumnsForRow = array('email_id');

    protected $_key = 'email_id';

    protected $_defaultValues = array(
        'email_member_id' => 0,
        'email_address'   => null,
        'email_primary'   => 0,
        'email_deleted'   => 0,
        'email_created'   => null,
        'email_checked'   => null
    );

    /**
     * @param int $identifer
     *
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function setDeleted($member_id, $identifer)
    {
        $memberLog = new Default_Model_MemberDeactivationLog();
        $memberLog->logMemberEmailAsDeleted($member_id, $identifer);

        return $this->delete($identifer);
        
    }
    
    /**
     * @param int $identifer
     *
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function setActive($member_id, $identifer)
    {
        $memberLog = new Default_Model_MemberDeactivationLog();
        $memberLog->removeLogMemberEmailAsDeleted($member_id, $identifer);

        return $this->activate($identifer);
        
    }

    /**
     * @param int $email_id
     *
     * @return int|void
     * @throws Zend_Db_Statement_Exception
     */
    public function delete($email_id)
    {
        $sql = "UPDATE `{$this->_name}` SET `email_deleted` = 1 WHERE `{$this->_key}` = :emailId";
        $stmnt = $this->_db->query($sql, array('emailId' => $email_id));

        return $stmnt->rowCount();
    }
    
    /**
     * @param int $email_id
     *
     * @return int|void
     * @throws Zend_Db_Statement_Exception
     */
    public function activate($email_id)
    {
        $sql = "UPDATE `{$this->_name}` SET `email_deleted` = 0 WHERE `{$this->_key}` = :emailId";
        $stmnt = $this->_db->query($sql, array('emailId' => $email_id));

        return $stmnt->rowCount();
    }

    /**
     * @param int $email_id
     *
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function setChecked($email_id)
    {
        $sql = "UPDATE `{$this->_name}` SET `email_checked` = NOW() WHERE `{$this->_key}` = :emailId";
        $stmnt = $this->_db->query($sql, array('emailId' => $email_id));

        return $stmnt->rowCount();
    }

    /**
     * @param $email_id
     *
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function setPrimary($email_id)
    {
        $sql = "UPDATE `{$this->_name}` SET `email_primary` = 1 WHERE `{$this->_key}` = :emailId";
        $stmnt = $this->_db->query($sql, array('emailId' => $email_id));

        return $stmnt->rowCount();
    }

    /**
     * @param $member_id
     *
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function setDeletedByMember($member_id)
    {
        
        $sql = "SELECT email_id FROM member_email WHERE email_member_id = :member_id AND email_deleted = 0";
        $emailsForDelete = $this->_db->fetchAll($sql, array(
            'member_id'       => $member_id
        ));
        foreach ($emailsForDelete as $item) {
            $this->setDeleted($member_id, $item['email_id']);
        }
        
        $sql = "UPDATE `{$this->_name}` SET `email_deleted` = 1 WHERE `email_member_id` = :memberId";
        $stmnt = $this->_db->query($sql, array('memberId' => $member_id));

        return $stmnt->rowCount();
    }
    
    /**
     * @param $member_id
     *
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function setActivatedByMember($member_id)
    {
        
        $sql = "SELECT e.email_id FROM member_email e
                JOIN member_deactivation_log l ON l.object_type_id = 2 AND l.object_id = e.email_id AND l.deactivation_id = e.email_member_id  AND l.is_deleted = 0
                WHERE e.email_member_id = :member_id AND email_deleted = 1";
        $emails = $this->_db->fetchAll($sql, array(
            'member_id'       => $member_id
        ));
        foreach ($emails as $item) {
            $this->setActive($member_id, $item['email_id']);
        }
        
        /*
        $sql = "UPDATE `{$this->_name}` SET `email_deleted` = 1 WHERE `email_member_id` = :memberId";
        $stmnt = $this->_db->query($sql, array('memberId' => $member_id));

        return $stmnt->rowCount();
         * 
         */
    }
    

}