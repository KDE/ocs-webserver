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
 * Created: 01.08.2018
 */
class Default_Model_Ocs_Ident
{
    /** @var string */
    protected $baseDn;
    /** @var Zend_Config */
    protected $config;
    protected $errMessages;
    protected $errCode;
    /** @var Zend_Ldap */
    private $identServer;

    /**
     * @inheritDoc
     */
    public function __construct($config = null)
    {
        if (isset($config)) {
            $this->config = $config;
        } else {
            $this->config = Zend_Registry::get('config')->settings->server->ldap;
        }
        $this->baseDn = $this->config->baseDn;
        $this->errMessages = array();
        $this->errCode = 0;
    }

    /**
     * @param int $member_id
     *
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function updateMail($member_id)
    {
        $connection = $this->getServerConnection();
        $member_data = $this->getMemberData($member_id);
        $username = strtolower($member_data['username']);
        $entry = $this->getEntry($member_data, $connection);
        $oldUidAttribute = Zend_Ldap_Attribute::getAttribute($entry, 'email');
        Zend_Ldap_Attribute::removeFromAttribute($entry, 'uid', $oldUidAttribute[0]);
        Zend_Ldap_Attribute::setAttribute($entry, 'email', $member_data['email_address']);
        Zend_Ldap_Attribute::setAttribute($entry, 'uid', $member_data['email_address'], true);
        $connection->update("cn={$username},{$this->baseDn}", $entry);
    }

    /**
     * @return null|Zend_Ldap
     * @throws Zend_Ldap_Exception
     */
    private function getServerConnection()
    {
        if (false === empty($this->identServer)) {
            return $this->identServer;
        }
        $this->identServer = new Zend_Ldap($this->config);
        $this->identServer->bind();

        return $this->identServer;
    }

    /**
     * @param int $member_id
     *
     * @return array
     * @throws Zend_Exception
     */
    private function getMemberData($member_id, $onlyActive = true)
    {

        $onlyActiveFilter = '';
        if ($onlyActive) {
            $onlyActiveFilter = " AND `m`.`is_active` = 1 AND `m`.`is_deleted` = 0 AND `me`.`email_checked` IS NOT NULL AND `me`.`email_deleted` = 0";
        }
        $sql = "
            SELECT `mei`.`external_id`,`m`.`member_id`, `m`.`username`, `me`.`email_address`, `m`.`password`, `m`.`roleId`, `m`.`firstname`, `m`.`lastname`, `m`.`profile_image_url`, `m`.`created_at`, `m`.`changed_at`, `m`.`source_id`
            FROM `member` AS `m`
            LEFT JOIN `member_email` AS `me` ON `me`.`email_member_id` = `m`.`member_id` AND `me`.`email_primary` = 1
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE `m`.`member_id` = :memberId {$onlyActiveFilter}
            ORDER BY `m`.`member_id` DESC
        ";

        $result = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql, array('memberId' => $member_id));
        if (count($result) == 0) {
            throw new Default_Model_Ocs_Exception('member with id ' . $member_id . ' could not found.');
        }

        return $result;
    }

    /**
     * @param array     $member_data
     * @param Zend_Ldap $ldap
     *
     * @return mixed
     * @throws Zend_Ldap_Exception
     */
    private function getEntry($member_data, $ldap)
    {
        $username = strtolower($member_data['username']);
        $entry = $ldap->getEntry("cn={$username},{$this->baseDn}");

        return $entry;
    }

    /**
     * @param int $member_id
     *
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function updatePassword($member_id)
    {
        $connection = $this->getServerConnection();
        $member_data = $this->getMemberData($member_id);
        $entry = $this->getEntry($member_data, $connection);
        if (empty($entry)) {
            Zend_Registry::get('logger')->info(__METHOD__ . ' - ldap entry for member does not exists. Going to create it.');
            $this->createUser($member_id);

            return;
        }
        $password = '{MD5}' . base64_encode(pack("H*", $member_data['password']));
        Zend_Ldap_Attribute::setAttribute($entry, 'userPassword', $password);
        //Zend_Ldap_Attribute::setPassword($entry,
        //        'newPa$$w0rd',
        //        Zend_Ldap_Attribute::PASSWORD_HASH_MD5);
        $username = strtolower($member_data['username']);
        $connection->update("cn={$username},{$this->baseDn}", $entry);
        $connection->getLastError($this->errCode, $this->errMessages);
    }

    /**
     * @param int $member_id
     *
     * @throws Zend_Ldap_Exception
     * @throws Zend_Exception
     */
    public function createUser($member_id)
    {
        $connection = $this->getServerConnection();
        $member_data = $this->getMemberData($member_id);
        $entry = $this->createIdentEntry($member_data);
        $username = strtolower($member_data['username']);
        $connection->add("cn={$username},{$this->baseDn}", $entry);
        $connection->getLastError($this->errCode, $this->errMessages);
    }

    /**
     * @param array $member
     *
     * @return array
     */
    private function createIdentEntry($member)
    {
        $username = strtolower($member['username']);
        $password = '{MD5}' . base64_encode(pack("H*", $member['password']));

        $entry = array();
        Zend_Ldap_Attribute::setAttribute($entry, 'objectClass', 'top');
        Zend_Ldap_Attribute::setAttribute($entry, 'objectClass', 'account', true);
        Zend_Ldap_Attribute::setAttribute($entry, 'objectClass', 'extensibleObject', true);
        Zend_Ldap_Attribute::setAttribute($entry, 'uid', $username);
        Zend_Ldap_Attribute::setAttribute($entry, 'uid', $member['email_address'], true);
        Zend_Ldap_Attribute::setAttribute($entry, 'userPassword', $password);
        Zend_Ldap_Attribute::setAttribute($entry, 'cn', $username);
        Zend_Ldap_Attribute::setAttribute($entry, 'email', $member['email_address']);
        Zend_Ldap_Attribute::setAttribute($entry, 'uidNumber', $member['member_id']);
        Zend_Ldap_Attribute::setAttribute($entry, 'gidNumber', $member['roleId']);
        Zend_Ldap_Attribute::setAttribute($entry, 'memberUid', $member['external_id']);
        if (false === empty(trim($member['firstname']))) {
            Zend_Ldap_Attribute::setAttribute($entry, 'gn', $member['firstname']);
        }
        if (false === empty(trim($member['lastname']))) {
            Zend_Ldap_Attribute::setAttribute($entry, 'sn', $member['lastname']);
        }

        return $entry;
    }

    /**
     * @param int $member_id
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function updateUser($member_id)
    {
        $connection = $this->getServerConnection();
        $member_data = $this->getMemberData($member_id);
        $username = strtolower($member_data['username']);
        if (false === $connection->exists("cn={$username},{$this->baseDn}")) {
            return false;
        }
        $entry = $this->createIdentEntry($member_data);
        $connection->update("cn={$username},{$this->baseDn}", $entry);
        $connection->getLastError($this->errCode, $this->errMessages);
    }

    /**
     * @param int $member_id
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function deleteUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }
        $connection = $this->getServerConnection();
        $member_data = $this->getMemberData($member_id);
        $username = strtolower($member_data['username']);
        if (false === $connection->exists("cn={$username},{$this->baseDn}")) {
            return false;
        }
        $connection->delete("cn={$username},{$this->baseDn}");
        $connection->getLastError($this->errCode, $this->errMessages);

        return true;
    }

    /**
     * @param string $username
     *
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function deleteByUsername($username)
    {
        if (empty($username)) {
            throw new Default_Model_Ocs_Exception('given username is empty.');
        }
        $connection = $this->getServerConnection();
        $username = strtolower($username);
        if (false === $connection->exists("cn={$username},{$this->baseDn}")) {
            $this->errCode = 999;
            $this->errMessages[] = 'user not found.';
            return;
        }
        $connection->delete("cn={$username},{$this->baseDn}");
        $connection->getLastError($this->errCode, $this->errMessages);
    }

    /**
     * @param $member_data
     *
     * @return array
     * @throws Zend_Ldap_Exception
     */
    public function createUserInLdap($member_data)
    {
        $entry = $this->createIdentEntry($member_data);
        $connection = $this->getServerConnection();
        $username = strtolower($member_data['username']);
        $dn = "cn={$username},{$this->baseDn}";
        if ($connection->exists($dn)) {
            $this->errCode = 999;
            $this->errMessages[] = "user {$member_data['username']} already exists";

            return array();
        }
        $connection->add($dn, $entry);
        $connection->getLastError($this->errCode, $this->errMessages);

        return $entry;
    }

    /**
     * @param array $member_data
     *
     * @return array
     * @throws Zend_Ldap_Exception
     */
    public function updateUserInLdap($member_data)
    {
        $entry = $this->createIdentEntry($member_data);
        $connection = $this->getServerConnection();
        $username = strtolower($member_data['username']);
        $dn = "cn={$username},{$this->baseDn}";
        if (false == $connection->exists($dn)) {
            $this->errCode = 998;
            $this->errMessages[] = "user {$member_data['username']} does not exists";

            return array();
        }
        $connection->update($dn, $entry);
        $connection->getLastError($this->errCode, $this->errMessages);

        return $entry;
    }

    /**
     * @return array
     */
    public function getErrMessages()
    {
        return $this->errMessages;
    }

    /**
     * @param array $errMessages
     *
     * @return Default_Model_Ocs_Ident
     */
    public function setErrMessages($errMessages)
    {
        $this->errMessages = $errMessages;

        return $this;
    }

}