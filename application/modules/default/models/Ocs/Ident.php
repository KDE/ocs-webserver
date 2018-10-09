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
     * @return string
     * @throws Zend_Exception
     */
    public static function getBaseDn()
    {
        try {
            return Zend_Registry::get('config')->settings->server->ldap->baseDn;
        } catch (Zend_Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage());

            return '';
        }
    }

    /**
     * @param int $member_id
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function updateMail($member_id)
    {
        $connection = $this->getServerConnection();
        $member_data = $this->getMemberData($member_id);

        try {
            $entry = $this->getEntry($member_data, $connection);
        } catch (Exception $e) {
            $this->errMessages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (empty($entry)) {
            $this->errMessages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ldap entry for member does not exists. Going to create it.');

            return false;
        }

        $oldUidAttribute = Zend_Ldap_Attribute::getAttribute($entry, 'email');
        Zend_Ldap_Attribute::removeFromAttribute($entry, 'uid', $oldUidAttribute);
        Zend_Ldap_Attribute::removeFromAttribute($entry, 'email', $oldUidAttribute);
        Zend_Ldap_Attribute::setAttribute($entry, 'email', $member_data['email_address']);
        Zend_Ldap_Attribute::setAttribute($entry, 'uid', $member_data['email_address'], true);
        $dn = $entry['dn'];
        $connection->update($dn, $entry);
        $connection->getLastError($this->errCode, $this->errMessages);

        return true;
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
            $onlyActiveFilter =
                " AND `m`.`is_active` = 1 AND `m`.`is_deleted` = 0 AND `me`.`email_checked` IS NOT NULL AND `me`.`email_deleted` = 0";
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
     *
     * @param Zend_Ldap $ldap
     *
     * @return array
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Ldap_Exception
     */
    public function getEntry($member_data, $ldap)
    {
        if (empty($member_data)) {
            throw new Default_Model_Ocs_Exception('given member_data empty');
        }

        $filter = "(uidNumber={$member_data['member_id']})";
        $entries = $ldap->searchEntries($filter, $this->baseDn);

        if (count($entries) > 1) {
            throw new Default_Model_Ocs_Exception('found member_id more than once. member_id: ' . $member_data['member_id']);
        }

        if (count($entries) == 1) {
            return $entries[0];
        }

        return array();
    }

    /**
     * @param array     $member_data
     * @param Zend_Ldap $ldap
     *
     * @return mixed
     * @throws Zend_Ldap_Exception
     */
    public function getEntryByDN($member_data, $ldap)
    {
        $username = strtolower($member_data['username']);
        $entry = $ldap->getEntry("cn={$username},{$this->baseDn}");

        return $entry;
    }

    /**
     * @param int $member_id
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function updatePassword($member_id)
    {
        $connection = $this->getServerConnection();
        $member_data = $this->getMemberData($member_id);
        try {
            $entry = $this->getEntry($member_data, $connection);
        } catch (Exception $e) {
            $this->errMessages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }

        if (empty($entry)) {
            $this->errMessages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ldap entry for member does not exists. member_id:' . $member_id);

            return false;
        }
        Zend_Ldap_Attribute::removeFromAttribute($entry, 'userPassword', Zend_Ldap_Attribute::getAttribute($entry, 'userPassword'));
        $password = '{MD5}' . base64_encode(pack("H*", $member_data['password']));
        Zend_Ldap_Attribute::setAttribute($entry, 'userPassword', $password);

        $connection->update($entry['dn'], $entry);
        $connection->getLastError($this->errCode, $this->errMessages);

        return true;
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
        try {
            $oldEntry = $this->getEntry($member_data, $connection);
        } catch (Exception $e) {
            $this->errMessages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (empty($oldEntry)) {
            $this->errMessages[] = "user missing. Going to create one.";
            Zend_Registry::get('logger')->info(__METHOD__ . ' - ldap entry for member does not exists. Going to create it.');

            return $this->createUser($member_id);
        }
        if (strtolower($member_data['username']) != Zend_Ldap_Attribute::getAttribute($oldEntry, 'cn')) {
            $this->errMessages[] = "Fail. username changed. user should be deleted first and than user create.";

            return false;
        }

        $entry = $this->updateIdentEntry($member_data, $oldEntry);
        $connection->update($oldEntry['dn'], $entry);
        $connection->getLastError($this->errCode, $this->errMessages);

        return true;
    }

    /**
     * @param int $member_id
     *
     * @return bool
     * @throws Zend_Ldap_Exception
     * @throws Zend_Exception
     */
    public function createUser($member_id)
    {
        $connection = $this->getServerConnection();
        $member_data = $this->getMemberData($member_id);

        //Only create, if user do not exisits
        try {
            $entry = $this->getEntry($member_data, $connection);
        } catch (Exception $e) {
            $this->errMessages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (false === empty($entry)) {
            $this->errMessages[] = "user already exists.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ldap entry for member does not exists. Going to create it.');

            return false;
        }

        $entry = $this->createIdentEntry($member_data);
        $username = strtolower($member_data['username']);
        $connection->add("cn={$username},{$this->baseDn}", $entry);
        //set avatar
        $this->updateAvatar($member_id);
        $connection->getLastError($this->errCode, $this->errMessages);

        return true;
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
    public function updateAvatar($member_id)
    {

        $member_data = $this->getMemberData($member_id);
        $imgTempPath = 'img/data/' . $member_id . "_avatar.jpg";
        $im = new imagick($member_data['profile_image_url']);
        $im = $im->flattenImages();

        // convert to jpeg
        $im->setImageFormat('jpeg');
        //write image on server
        $im->writeImage($imgTempPath);
        $im->clear();
        $im->destroy();

        $avatarJpeg = $imgTempPath;
        $avatarBase64 = file_get_contents($avatarJpeg);

        $connection = $this->getServerConnection();

        try {
            $entry = $this->getEntry($member_data, $connection);
        } catch (Exception $e) {
            $this->errMessages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (empty($entry)) {
            $this->errMessages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ldap entry for member does not exists. Going to create it.');

            return false;
        }

        Zend_Ldap_Attribute::removeFromAttribute($entry, 'jpegPhoto', Zend_Ldap_Attribute::getAttribute($entry, 'jpegPhoto'));

        Zend_Ldap_Attribute::setAttribute($entry, 'jpegPhoto', $avatarBase64);

        $dn = $entry['dn'];
        $connection->update($dn, $entry);
        $connection->getLastError($this->errCode, $this->errMessages);

        unlink($imgTempPath);

        return true;
    }

    private function updateIdentEntry($member_data, $oldEntry)
    {
        $entry = $oldEntry;
        Zend_Ldap_Attribute::removeFromAttribute($entry, 'uidNumber', Zend_Ldap_Attribute::getAttribute($oldEntry, 'uidNumber'));
        Zend_Ldap_Attribute::setAttribute($entry, 'uidNumber', $member_data['member_id']);

        Zend_Ldap_Attribute::removeFromAttribute($entry, 'memberUid', Zend_Ldap_Attribute::getAttribute($oldEntry, 'memberUid'));
        Zend_Ldap_Attribute::setAttribute($entry, 'memberUid', $member_data['external_id']);

        Zend_Ldap_Attribute::removeFromAttribute($entry, 'gidNumber', Zend_Ldap_Attribute::getAttribute($oldEntry, 'gidNumber'));
        Zend_Ldap_Attribute::setAttribute($entry, 'gidNumber', $member_data['role_id']);

        Zend_Ldap_Attribute::removeFromAttribute($entry, 'email', Zend_Ldap_Attribute::getAttribute($oldEntry, 'email'));
        Zend_Ldap_Attribute::setAttribute($entry, 'email', $member_data['email_address']);

        Zend_Ldap_Attribute::removeFromAttribute($entry, 'cn', Zend_Ldap_Attribute::getAttribute($oldEntry, 'cn'));
        Zend_Ldap_Attribute::setAttribute($entry, 'cn', $member_data['username']);

        Zend_Ldap_Attribute::removeFromAttribute($entry, 'uid', Zend_Ldap_Attribute::getAttribute($oldEntry, 'uid'));
        Zend_Ldap_Attribute::setAttribute($entry, 'uid', $member_data['username']);

        Zend_Ldap_Attribute::removeFromAttribute($entry, 'uid', Zend_Ldap_Attribute::getAttribute($oldEntry, 'uid'));
        Zend_Ldap_Attribute::setAttribute($entry, 'uid', $member_data['email']);

        Zend_Ldap_Attribute::removeFromAttribute($entry, 'userPassword', Zend_Ldap_Attribute::getAttribute($oldEntry, 'userPassword'));
        $password = '{MD5}' . base64_encode(pack("H*", $member_data['password']));
        Zend_Ldap_Attribute::setAttribute($entry, 'userPassword', $password);

        if (false === empty(trim($member_data['firstname']))) {
            Zend_Ldap_Attribute::removeFromAttribute($entry, 'gn', Zend_Ldap_Attribute::getAttribute($oldEntry, 'gn'));
            Zend_Ldap_Attribute::setAttribute($entry, 'gn', $member_data['firstname']);
        }
        if (false === empty(trim($member_data['lastname']))) {
            Zend_Ldap_Attribute::removeFromAttribute($entry, 'sn', Zend_Ldap_Attribute::getAttribute($oldEntry, 'sn'));
            Zend_Ldap_Attribute::setAttribute($entry, 'sn', $member_data['lastname']);
        }

        //Avatar
        $imgTempPath = 'img/data/' . $member_data['member_id'] . "_avatar.jpg";
        $im = new imagick($member_data['profile_image_url']);
        $im = $im->flattenImages();

        // convert to jpeg
        $im->setImageFormat('jpeg');
        //write image on server
        $im->writeImage($imgTempPath);
        $im->clear();
        $im->destroy();
        $avatarJpeg = $imgTempPath;
        $avatarFileData = file_get_contents($avatarJpeg);
        Zend_Ldap_Attribute::removeFromAttribute($entry, 'jpegPhoto', Zend_Ldap_Attribute::getAttribute($entry, 'jpegPhoto'));
        Zend_Ldap_Attribute::setAttribute($entry, 'jpegPhoto', $avatarFileData);

        return $entry;
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
        $member_data = $this->getMemberData($member_id, false);
        try {
            $entry = $this->getEntry($member_data, $connection);
        } catch (Exception $e) {
            $this->errMessages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        $connection->delete($entry['dn']);
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
            $connection->getLastError($this->errCode, $this->errMessages);

            return;
        }
        $connection->delete("cn={$username},{$this->baseDn}");
        $connection->getLastError($this->errCode, $this->errMessages);
    }

    /**
     * @param      $member_data
     *
     * @param bool $force
     *
     * @return array
     * @throws Zend_Ldap_Exception
     */
    public function createUserInLdap($member_data, $force = false)
    {
        $this->errMessages = array();

        $entry = $this->createIdentEntry($member_data);
        $connection = $this->getServerConnection();
        $username = strtolower($member_data['username']);
        $dn = "cn={$username},{$this->baseDn}";

        try {
            $user = $this->getUser($member_data['member_id'], $member_data['username']);
        } catch (Exception $e) {
            $this->errCode = 998;
            $this->errMessages[] = $e->getMessage();
            $user = null;

            return array();
        }

        if (empty($user)) {
            $connection->add($dn, $entry);
            $connection->getLastError($this->errCode, $this->errMessages);

            return $entry;
        }

        if (true === $force) {
            $connection->update($dn, $entry);
            $connection->getLastError($this->errCode, $this->errMessages);
            $this->errMessages[] = "overwritten : " . json_encode($user);

            return $entry;
        }

        $this->errCode = 999;
        $this->errMessages[] = "user already exists.";

        return $user;
    }

    /**
     * @param $member_id
     * @param $username
     *
     * @return array|null
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Ldap_Exception
     */
    public function getUser($member_id, $username)
    {
        if (empty($member_id)) {
            throw new Default_Model_Ocs_Exception('given $member_id empty');
        }

        $ldap = $this->getServerConnection();
        $filter = "(uidNumber={$member_id})";
        $entries = $ldap->searchEntries($filter, $this->baseDn);

        if (count($entries) > 1) {
            throw new Default_Model_Ocs_Exception("{$member_id} is ambiguous");
        }

        $username = strtolower($username);
        $entry = $ldap->getEntry("cn={$username},{$this->baseDn}");

        if (empty($entry) AND empty($entries)) {
            return null;
        }
        if (empty($entry) AND !empty($entries)) {
            return $entries[0];
        }
        if (!empty($entry) AND empty($entries)) {
            return $entry;
        }

        return $entry;;
    }

    /**
     * @param int $member_id
     *
     * @return mixed
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Ldap_Exception
     */
    public function userExists($member_id, $username = null)
    {
        if (empty($member_id)) {
            throw new Default_Model_Ocs_Exception('given $member_id empty');
        }

        $ldap = $this->getServerConnection();
        $filter = "(uidNumber={$member_id})";
        $entries = $ldap->searchEntries($filter, $this->baseDn);

        if (count($entries) > 1) {
            throw new Default_Model_Ocs_Exception('found member_id more than once');
        }

        if (count($entries) == 1) {
            return true;
        }

        return false;
    }

    /**
     * @param array $member_data
     *
     * @return array
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function updateUserInLdap($member_data)
    {
        $newEntry = $this->createIdentEntry($member_data);
        $connection = $this->getServerConnection();
        try {
            $entry = $this->getEntry($member_data, $connection);
        } catch (Exception $e) {
            $this->errMessages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        $dn = $entry['dn'];
        $connection->update($dn, $newEntry);
        $connection->getLastError($this->errCode, $this->errMessages);

        return $entry;
    }

    /**
     * @return array
     */
    public function getMessages()
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