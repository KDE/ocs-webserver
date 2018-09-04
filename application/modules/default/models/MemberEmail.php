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
class Default_Model_MemberEmail
{
    const CASE_INSENSITIVE = 1;
    /** @var string */
    protected $_dataTableName;
    /** @var  Default_Model_DbTable_MemberEmail */
    protected $_dataTable;

    /**
     * @inheritDoc
     */
    public function __construct($_dataTableName = 'Default_Model_DbTable_MemberEmail')
    {
        $this->_dataTableName = $_dataTableName;
        $this->_dataTable = new $this->_dataTableName;
    }

    /**
     * @param int  $member_id
     * @param bool $email_deleted
     *
     * @return array
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Db_Table_Exception
     */
    public function fetchAllMailAddresses($member_id, $email_deleted = false)
    {
        $deleted = $email_deleted === true ? Default_Model_DbTable_MemberEmail::EMAIL_DELETED
            : Default_Model_DbTable_MemberEmail::EMAIL_NOT_DELETED;
        $sql =
            "SELECT * FROM {$this->_dataTable->info('name')} WHERE `email_member_id` = :memberId AND `email_deleted` = :emailDeleted";
        $stmnt = $this->_dataTable->getAdapter()->query($sql, array('memberId' => $member_id, 'emailDeleted' => $deleted));

        return $stmnt->fetchAll();
    }

    /**
     * @param $emailId
     * @param $member_id
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Exception
     */
    public function setDefaultEmail($emailId, $member_id)
    {
        $result = $this->resetDefaultMailAddress($member_id);
        $this->_dataTable->setPrimary($emailId);
        $this->updateMemberPrimaryMail($member_id); /* if we change the mail in member table, we change the login. */
        Default_Model_ActivityLog::logActivity($member_id, null, $member_id, Default_Model_ActivityLog::MEMBER_EMAIL_CHANGED);

        return true;
    }

    /**
     * @param $member_id
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    private function resetDefaultMailAddress($member_id)
    {
        $sql = "UPDATE member_email SET email_primary = 0 WHERE email_member_id = :member_id AND email_primary = 1";

        return $this->_dataTable->getAdapter()->query($sql, array('member_id' => $member_id))->execute();
    }

    /**
     * @param $member_id
     *
     * @return mixed
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Db_Table_Exception
     */
    private function updateMemberPrimaryMail($member_id)
    {
        $dataEmail = $this->fetchMemberPrimaryMail($member_id);

        return $this->saveMemberPrimaryMail($member_id, $dataEmail);
    }

    /**
     * @param $member_id
     *
     * @return mixed
     * @throws Zend_Db_Table_Exception
     */
    public function fetchMemberPrimaryMail($member_id)
    {
        $sql = "SELECT * FROM {$this->_dataTable->info('name')} WHERE email_member_id = :member_id AND email_primary = 1";
        $dataEmail = $this->_dataTable->getAdapter()->fetchRow($sql, array('member_id' => $member_id));

        return $dataEmail;
    }

    /**
     * @param $member_id
     * @param $dataEmail
     *
     * @return mixed
     * @throws Zend_Db_Statement_Exception
     */
    protected function saveMemberPrimaryMail($member_id, $dataEmail)
    {
        $modelMember = new Default_Model_Member();
        $dataMember = $modelMember->fetchMemberData($member_id);
        $dataMember->mail = $dataEmail['email_address'];
        $dataMember->mail_checked = isset($dataEmail['email_checked']) ? 1 : 0;

        return $dataMember->save();
    }

    /**
     * @param string $verification
     *
     * @return int count of updated rows
     * @throws Zend_Db_Statement_Exception
     */
    public function verificationEmail($verification)
    {
        $sql =
            "UPDATE member_email SET `email_checked` = NOW() WHERE `email_verification_value` = :verification AND `email_deleted` = 0 AND `email_checked` IS NULL";
        $stmnt = $this->_dataTable->getAdapter()->query($sql, array('verification' => $verification));

        return $stmnt->rowCount();
    }

    /**
     * @param int         $user_id
     * @param string      $user_mail
     * @param null|string $user_verification
     *
     * @return Zend_Db_Table_Row_Abstract
     * @throws Exception
     */
    public function saveEmail($user_id, $user_mail, $user_verification = null)
    {
        $data = array();
        $data['email_member_id'] = $user_id;
        $data['email_address'] = $user_mail;
        $data['email_hash'] = md5($user_mail);
        $data['email_verification_value'] =
            empty($user_verification) ? Default_Model_MemberEmail::getVerificationValue($user_id, $user_mail) : $user_verification;

        Default_Model_ActivityLog::logActivity($user_id, null, $user_id, Default_Model_ActivityLog::MEMBER_EMAIL_CHANGED,
            array('description' => 'user saved new mail address: ' . $user_mail));

        return $this->_dataTable->save($data);
    }

    /**
     * @param int    $user_name
     * @param string $member_email
     *
     * @return string
     */
    public static function getVerificationValue($user_name, $member_email)
    {
        return md5($user_name . $member_email . time());
    }

    /**
     * @param int         $user_id
     * @param string      $user_mail
     * @param int         $user_mail_checked
     * @param null|string $user_verification
     *
     * @return Zend_Db_Table_Row_Abstract
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Exception
     * @throws Exception
     */
    public function saveEmailAsPrimary($user_id, $user_mail, $user_mail_checked = 0, $user_verification = null)
    {
        if (empty($user_id) OR empty($user_mail)) {
            return false;
        }

        $data = array();
        $data['email_member_id'] = $user_id;
        $data['email_address'] = $user_mail;
        $data['email_hash'] = MD5($user_mail);
        $data['email_checked'] = $user_mail_checked == 1 ? new Zend_Db_Expr('Now()') : new Zend_Db_Expr('NULL');
        $data['email_verification_value'] =
            empty($user_verification) ? Default_Model_MemberEmail::getVerificationValue($user_id, $user_mail) : $user_verification;
        $data['email_primary'] = Default_Model_DbTable_MemberEmail::EMAIL_PRIMARY;

        $result = $this->_dataTable->save($data);

        $this->resetOtherPrimaryEmail($user_id, $user_mail);

        $this->updateMemberPrimaryMail($user_id);

        Default_Model_ActivityLog::logActivity($user_id, null, $user_id, Default_Model_ActivityLog::MEMBER_EMAIL_CHANGED,
            array('description' => 'user saved new primary mail address: ' . $user_mail));

        return $result;
    }

    /**
     * @param $member_id
     *
     * @return null
     * @throws Zend_Db_Table_Exception
     */
    public function getValidationValue($member_id)
    {
        $memberData = $this->fetchMemberPrimaryMail($member_id);
        if (count($memberData) == 0) {
            return null;
        }
        return $memberData['email_verification_value'];
    }

    /**
     * @param string $value
     * @param int    $test_case_sensitive
     *
     * @param array  $omitMember
     *
     * @return mixed
     */
    public function findMailAddress($value, $test_case_sensitive = self::CASE_INSENSITIVE, $omitMember = array())
    {
        $sql = "
            SELECT *
            FROM `member_email`
            WHERE `member_email`.`email_deleted` = 0
        ";
        if ($test_case_sensitive == self::CASE_INSENSITIVE) {
            $sql .= "AND LCASE(member_email.email_address) = LCASE(:mail_address)";
        } else {
            $sql .= "AND member_email.email_address = :mail_address";
        }

        if (count($omitMember) > 0) {
            $sql .= " AND member_email.email_member_id NOT IN (" . implode(',', $omitMember) . ")";
        }

        return $this->_dataTable->getAdapter()->fetchAll($sql, array('mail_address' => $value));
    }

    public function sendConfirmationMail($val, $verificationVal)
    {
        $confirmMail = new Default_Plugin_SendMail('tpl_verify_user');
        $confirmMail->setTemplateVar('servername', $this->getServerName());
        $confirmMail->setTemplateVar('username', $val['username']);
        $confirmMail->setTemplateVar('verificationlinktext',
            '<a href="https://' . $this->getServerName() . '/verification/' . $verificationVal
            . '">Click here to verify your email address</a>');
        $confirmMail->setTemplateVar('verificationlink',
            '<a href="https://' . $this->getServerName() . '/verification/' . $verificationVal . '">https://' . $this->getServerName()
            . '/verification/' . $verificationVal . '</a>');
        $confirmMail->setTemplateVar('verificationurl', 'https://' . $this->getServerName() . '/verification/' . $verificationVal);
        $confirmMail->setReceiverMail($val['mail']);
        $confirmMail->setFromMail('registration@opendesktop.org');
        $confirmMail->send();
    }

    private function getServerName()
    {
        /** @var Zend_Controller_Request_Http $request */
        $request = Zend_Controller_Front::getInstance()->getRequest();

        return $request->getHttpHost();
    }

    private function resetOtherPrimaryEmail($user_id, $user_mail)
    {
        $sql = "
                UPDATE `member_email`
                SET `email_primary` = 0
                WHERE `email_member_id` = :user_id AND `email_address` <> :user_mail; 
                ";
        $result = $this->_dataTable->getAdapter()->query($sql, array('user_id' => $user_id, 'user_mail' => $user_mail));

    }

}