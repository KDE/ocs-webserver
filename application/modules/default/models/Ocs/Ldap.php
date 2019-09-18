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
class Default_Model_Ocs_Ldap
{
    const JPEG_PHOTO = 'jpegPhoto';
    const LDAP_SUCCESS = '(Success)';
    const USER_PASSWORD = 'userPassword';

    /** @var string */
    protected $baseDnUser;
    /** @var Zend_Config */
    protected $config;
    protected $messages;
    protected $errCode;
    protected $baseDnGroup;
    /** @var Zend_Ldap */
    protected $identGroupServer;
    /** @var Zend_Ldap */
    private $ldap;

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
        $this->baseDnUser = $this->config->baseDn;
        $this->baseDnGroup = $this->config->baseGroupDn;
        $this->messages = array();
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
        }

        return '';
    }

    /**
     * @param string $ouName
     * @return array
     */
    public function createEntryOrgUnit($ouName)
    {
        $entry = array();
        Zend_Ldap_Attribute::setAttribute($entry, 'objectClass', 'top');
        Zend_Ldap_Attribute::setAttribute($entry, 'objectClass', 'organizationalUnit', true);
        Zend_Ldap_Attribute::setAttribute($entry, 'ou', $ouName);

        return $entry;
    }

    /**
     * @param array  $entry
     * @param string $ouName
     * @return string return DN for the new org unit
     * @throws Zend_Exception
     */
    public function addOrgUnit(array $entry, $ouName)
    {
        $rootDn = Zend_Registry::get('config')->settings->server->ldap_ext->rootDn;
        $dn = "ou={$ouName},{$rootDn}";

        $this->addEntry($entry, $dn);

        return $dn;
    }

    public function addEntry(array $entry, $dn)
    {
        $this->getConnectionUser()->add($dn, $entry);
        $this->messages[] = __METHOD__ . ' = ' . $this->getConnectionUser()->getLastError();
    }

    /**
     * @return null|Zend_Ldap
     * @throws Zend_Ldap_Exception
     */
    private function getConnectionUser()
    {
        if (false === empty($this->ldap)) {
            return $this->ldap;
        }
        $this->ldap = new Zend_Ldap($this->config);
        $this->ldap->bind();

        return $this->ldap;
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
        $connection = $this->getConnectionUser();
        $member_data = $this->getMemberData($member_id);

        try {
            $entry = $this->getLdapUser($member_data);
        } catch (Exception $e) {
            $this->messages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (empty($entry)) {
            $this->messages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ldap entry for member does not exists. Going to create it.');

            return false;
        }

        $email = $this->lowerString($member_data['email_address']);
        $oldUidAttribute = Zend_Ldap_Attribute::getAttribute($entry, 'email');
        Zend_Ldap_Attribute::removeFromAttribute($entry, 'uid', $oldUidAttribute);
        Zend_Ldap_Attribute::removeFromAttribute($entry, 'email', $oldUidAttribute);
        Zend_Ldap_Attribute::setAttribute($entry, 'email', $member_data['email_address']);
        Zend_Ldap_Attribute::setAttribute($entry, 'uid', $email, true);
        $dn = $entry['dn'];
        $connection->update($dn, $entry);
        $connection->getLastError($this->errCode, $this->messages);

        return true;
    }

    /**
     * @param int  $member_id
     *
     * @param bool $onlyActive
     * @return array
     * @throws Default_Model_Ocs_Exception
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
     * @param array $member_data
     *
     * @return array
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     */
    public function getLdapUser(array $member_data)
    {
        if (empty($member_data)) {
            throw new Default_Model_Ocs_Exception('given member_data empty');
        }

        $entry = array();

        try {
            $entry = $this->getLdapUserByMemberId($member_data['member_id']);

            if ($entry) {
                return $entry;
            }

            $entry = $this->getLdapUserByUsername($member_data['username']);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage());
        }

        return $entry;
    }

    /**
     * @param int $member_id
     * @return array
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Ldap_Exception
     */
    public function getLdapUserByMemberId($member_id)
    {
        $ldap_connection = $this->getConnectionUser();

        $filter = "(&(uidNumber={$member_id})(objectClass=account))";
        $entries = $ldap_connection->searchEntries($filter, $this->baseDnUser);

        if (empty($entries)) {
            return array();
        }

        if (count($entries) > 1) {
            throw new Default_Model_Ocs_Exception('found member_id more than once. member_id: ' . $member_id);
        }

        return $entries[0];
    }

    /**
     * @param string $username
     *
     * @return array
     * @throws Zend_Ldap_Exception
     */
    public function getLdapUserByUsername($username)
    {
        $username = $this->lowerString($username);
        $ldap_connection = $this->getConnectionUser();
        $entry = $ldap_connection->getEntry("cn={$username},{$this->baseDnUser}");

        return $entry;
    }

    /**
     * @param $string
     * @return string
     */
    private function lowerString($string)
    {
        $enc = mb_detect_encoding($string) ? mb_detect_encoding($string) : 'UTF-8';
        $string = mb_strtolower($string, $enc);

        return $string;
    }

    /**
     * @param int         $member_id
     *
     * @param string|null $password
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function updatePassword($member_id, $password = null)
    {
        $connection = $this->getConnectionUser();
        $member_data = $this->getMemberData($member_id);
        try {
            $entry = $this->getLdapUser($member_data);
        } catch (Exception $e) {
            $this->messages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }

        if (empty($entry)) {
            $this->messages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ldap entry for member does not exists. member_id:' . $member_id);

            return false;
        }
        Zend_Ldap_Attribute::removeFromAttribute($entry, self::USER_PASSWORD,
            Zend_Ldap_Attribute::getAttribute($entry, self::USER_PASSWORD));
        if (isset($password)) {
            $hash = Local_Auth_Adapter_Ocs::getEncryptedLdapPass($password);
        } else {
            $hash = '{MD5}' . base64_encode(pack("H*", $member_data['password']));
        }
        Zend_Ldap_Attribute::setAttribute($entry, self::USER_PASSWORD, $hash);

        $connection->update($entry['dn'], $entry);
        $connection->getLastError($this->errCode, $this->messages);

        return true;
    }

    /**
     * @param int $member_id
     *
     * @return bool
     * @throws ImagickException
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function createUser($member_id)
    {
        $connection = $this->getConnectionUser();
        $member_data = $this->getMemberData($member_id);

        //Only create, if user do not exisits
        try {
            $entry = $this->getLdapUser($member_data);
        } catch (Exception $e) {
            $this->messages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (false === empty($entry)) {
            $this->messages[] = __METHOD__ . ' = ' . "user already exists.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ldap entry for member does not exists. Going to create it.');

            return false;
        }

        $entry = $this->createEntryForUser($member_data);
        $dn = $this->getDnForUser($member_data['username']);
        $connection->add($dn, $entry);
        $connection->getLastError($this->errCode, $this->messages);

        return true;
    }

    /**
     * @param array $member
     *
     * @return array
     * @throws Zend_Exception
     */
    public function createEntryForUser(array $member)
    {
        $username = $this->lowerString($member['username']);
        $password = $this->createPasswordFromHash($member['password']);
        $mail_address = $this->lowerString($member['email_address']);
        $jpegPhoto = $this->createJpegPhoto($member['member_id'], $member['profile_image_url']);

        $entry = array();
        Zend_Ldap_Attribute::setAttribute($entry, 'objectClass', 'top');
        Zend_Ldap_Attribute::setAttribute($entry, 'objectClass', 'account', true);
        Zend_Ldap_Attribute::setAttribute($entry, 'objectClass', 'extensibleObject', true);
        Zend_Ldap_Attribute::setAttribute($entry, 'uid', $username);
        Zend_Ldap_Attribute::setAttribute($entry, 'uid', $mail_address, true);
        Zend_Ldap_Attribute::removeDuplicatesFromAttribute($entry, 'uid');
        Zend_Ldap_Attribute::setAttribute($entry, self::USER_PASSWORD, $password);
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

        Zend_Ldap_Attribute::setAttribute($entry, self::JPEG_PHOTO, $jpegPhoto);

        return $entry;
    }

    /**
     * @param int    $member_id
     * @param string $profile_image_url
     *
     * @return bool|string
     * @throws Zend_Exception
     */
    public function createJpegPhoto($member_id, $profile_image_url)
    {
        $imgTempPath = APPLICATION_DATA . '/uploads/tmp/' . $member_id . "_avatar.jpg";
        $helperImagePath = new Default_View_Helper_Image();
        $urlImage = $helperImagePath->Image($profile_image_url);

        try {
            $im = new imagick($urlImage);
            $layer_method = imagick::LAYERMETHOD_FLATTEN;
            $im = $im->mergeImageLayers($layer_method);
        } catch (ImagickException $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - error during converting avatar image. ' . $e->getMessage() . " ({$member_id};{$profile_image_url})");

            return false;
        }

        // convert to jpeg
        $im->setImageFormat('jpeg');
        //write image on server
        $im->writeImage($imgTempPath);
        $blob = $im->getImageBlob();
        $im->clear();
        $im->destroy();

        $avatarBase64 = file_get_contents($imgTempPath);

        unlink($imgTempPath);

        return $avatarBase64;
    }

    /**
     * @param string      $user_name
     * @param string|null $baseDn
     * @param bool        $lowerCase

     * @return string
     */
    public function getDnForUser($user_name, $baseDn = null, $lowerCase = true)
    {
        if (empty($baseDn)) {
            $baseDn = $this->baseDnUser;
        }
        $username = $lowerCase ? $this->lowerString($user_name) : $user_name;
        $dn = "cn={$username},{$baseDn}";

        return $dn;
    }

    /**
     * @param int    $member_id
     * @param string $profile_image_url
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function updateAvatar($member_id, $profile_image_url)
    {
        $jpegPhoto = $this->createJpegPhoto($member_id, $profile_image_url);

        try {
            $entry = $this->getLdapUserByMemberId($member_id);
        } catch (Exception $e) {
            $this->messages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (empty($entry)) {
            $this->messages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ldap entry for member does not exists.');

            return false;
        }

        $entry = $this->updateLdapAttrib($entry, $jpegPhoto, self::JPEG_PHOTO);

        $this->updateLdapEntry($entry);

        return true;
    }

    /**
     * @param array  $entry
     * @param string $attribValue
     * @param string $attribName
     *
     * @return array
     */
    public function updateLdapAttrib(array $entry, $attribValue, $attribName)
    {
        Zend_Ldap_Attribute::removeFromAttribute($entry, $attribName,
            Zend_Ldap_Attribute::getAttribute($entry, $attribName));

        Zend_Ldap_Attribute::setAttribute($entry, $attribName, $attribValue);

        return $entry;
    }

    /**
     * @param array $entry
     * @throws Zend_Ldap_Exception
     */
    public function updateLdapEntry(array $entry)
    {
        $dn = $entry['dn'];
        $this->getConnectionUser()->update($dn, $entry);
        $this->messages[] = __METHOD__ . ' = ' . $this->getConnectionUser()->getLastError();
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
        $connection = $this->getConnectionUser();
        try {
            $entry = $this->getLdapUserByMemberId($member_id);
        } catch (Exception $e) {
            $this->messages[] = "Failed.";
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (empty($entry)) {
            $this->messages[] = "Failed. user entry not exists.";

            return false;
        }
        $connection->delete($entry['dn']);
        $connection->getLastError($this->errCode, $this->messages);

        return true;
    }

    /**
     * @param array $member_data
     * @param bool  $force_update
     *
     * @return array|bool
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function addUserFromArray(array $member_data, $force_update = false)
    {
        if (empty($member_data)) {
            return false;
        }

        $this->messages = array();

        $connection = $this->getConnectionUser();
        $entry = $this->createEntryForUser($member_data);
        $dn = $this->getDnForUser($member_data['username']);
        $ldapUser = null;

        try {
            $ldapUser = $this->getLdapUser($member_data);
        } catch (Exception $e) {
            $this->errCode = 998;
            $this->messages[] = $e->getMessage();
            $ldapUser = null;

            return false;
        }

        if (empty($ldapUser)) {
            $connection->add($dn, $entry);
            $this->messages[] = __METHOD__ . ' = ' . $connection->getLastError();

            return $entry;
        }

        if (true === $force_update) {
            return $this->updateUserFromArray($member_data);
        }

        $this->errCode = 999;
        $this->messages[] = __METHOD__ . ' = ' . "user already exists.";

        return false;
    }

    /**
     * @param array $member_data
     *
     * @return array|bool
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function updateUserFromArray(array $member_data)
    {
        if (empty($member_data)) {
            return false;
        }

        $this->messages = array();

        $connection = $this->getConnectionUser();
        $entry = $this->createEntryForUser($member_data);
        $dn = $this->getDnForUser($member_data['username']);
        $user = null;

        try {
            $user = $this->getLdapUser($member_data);
        } catch (Exception $e) {
            $this->errCode = 998;
            $this->messages[] = $e->getMessage();
            $user = null;

            return false;
        }

        if (empty($user)) {
            $this->errCode = 998;
            $this->messages[] = "user not exist";
            $user = null;

            return false;
        }

        if ($this->hasChangedUsername($member_data['username'], $user)) {
            $this->messages[] = 'username changed: '. $user['dn'] . $member_data['username'];
            $dnDelete = $user['dn'];
            $connection->delete($dnDelete);
            $connection->getLastError($this->errCode, $this->messages);
            $connection->add($dn, $entry);
            $connection->getLastError($this->errCode, $this->messages);
            $this->messages[] = "old user deleted : " . $user['dn'];
        } else {
            $connection->update($dn, $entry);
            $connection->getLastError($this->errCode, $this->messages);
        }
        $this->messages[] = "overwritten : " . json_encode($user);

        return $entry;
    }

    /**
     * @param string $user_name
     * @param array  $user
     * @return bool
     */
    private function hasChangedUsername($user_name, $user)
    {
        return !Zend_Ldap_Attribute::attributeHasValue($user, 'cn', $this->lowerString($user_name));
    }

    /**
     * @param $member_id
     * @param $username
     *
     * @return bool
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Ldap_Exception
     */
    public function hasUser($member_id, $username)
    {
        if (empty($member_id)) {
            throw new Default_Model_Ocs_Exception('given $member_id empty');
        }

        $ldap = $this->getConnectionUser();
        $filter = "(uidNumber={$member_id})";
        $entries = $ldap->searchEntries($filter, $this->baseDnUser);

        if (count($entries) > 1) {
            throw new Default_Model_Ocs_Exception("{$member_id} is ambiguous");
        }

        if (false === empty($entries)) {
            return $entries[0];
        }

        $username = $this->lowerString($username);
        $entry = $ldap->getEntry("cn={$username},{$this->baseDnUser}");

        return empty($entry) ? false : true;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    public function addGroupMember($user_username, $group_name, $group_access, $group_id = null, $group_path = null)
    {
        $connection = $this->getConnectionGroup();
        $dnGroup = $this->getDnGroup($group_name);

        //Only update, if member exists
        try {
            $entry = $this->getLdapUserByUsername($user_username);
        } catch (Exception $e) {
            $this->messages[] = "Failed.";
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (empty($entry)) {
            $this->messages[] = "user not exists. nothing to update.";
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - ldap entry for new group user does not exists.' . $user_username);

            return false;
        }

        //Only update, if group exists
        try {
            $entry = $connection->getEntry($dnGroup);
        } catch (Exception $e) {
            $this->messages[] = "Failed.";
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (empty($entry) AND (strtolower($group_access) != 'owner')) {
            $this->messages[] = "group not exists. nothing to update.";
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - ldap entry for group does not exists.');

            return false;
        }
        if (empty($entry) AND (strtolower($group_access) == 'owner')) {
            if (empty($group_id) OR empty($group_path)) {
                Zend_Registry::get('logger')->warn(__METHOD__
                                                   . ' - ldap entry for group does not exists and owner is given. But group_id or group_path is empty.');

                return false;
            }
            $group = $this->createGroup($group_name, $group_id, $group_path, $user_username, $group_access);

            return $group;
        }

        $group = $this->addMemberToGroupEntry($entry, $user_username, $group_access);

        $connection->update($dnGroup, $group);
        $connection->getLastError($this->errCode, $this->messages);

        return $group;
    }

    /**
     * @return null|Zend_Ldap
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    private function getConnectionGroup()
    {
        if (false === empty($this->identGroupServer)) {
            return $this->identGroupServer;
        }
        $config = $this->config;
        $config->baseDn = Zend_Registry::get('config')->settings->server->ldap_group->baseDn;
        $this->identGroupServer = new Zend_Ldap($config);
        $this->identGroupServer->bind();

        return $this->identGroupServer;
    }

    /**
     * @param $group_name
     *
     * @return string
     */
    private function getDnGroup($group_name)
    {
        $baseDn = '';
        try {
            $baseDn = Zend_Registry::get('config')->settings->server->ldap_group->baseDn;
        } catch (Zend_Exception $e) {
        }
        $dnGroup = "cn={$group_name},{$baseDn}";

        return $dnGroup;
    }

    /**
     * @param string $group_name
     * @param int    $group_id
     * @param        $group_path
     * @param        $user_name
     * @param        $group_access
     *
     * @return array|bool
     * @throws Zend_Exception
     * @throws Zend_Ldap_Exception
     */
    public function createGroup($group_name, $group_id, $group_path, $user_name, $group_access)
    {
        $newGroup = $this->createEntryGroup($group_name, $group_id, $group_path, $user_name, $group_access);
        $connection = $this->getConnectionGroup();

        $groupDn = $this->getDnGroup($group_name);

        if ($connection->exists($groupDn)) {
            $this->messages[] = "group already exists: {$groupDn}";

            return false;
        }

        $connection->add($groupDn, $newGroup);
        $connection->getLastError($this->errCode, $this->messages);

        return $newGroup;
    }

    /**
     * @param string $name
     * @param int    $group_id
     * @param string $group_path
     * @param string $user_name
     * @param string $group_access
     *
     * @return array
     */
    private function createEntryGroup($name, $group_id, $group_path, $user_name, $group_access)
    {
        $entry = array();
        Zend_Ldap_Attribute::setAttribute($entry, 'objectClass', 'top');
        Zend_Ldap_Attribute::setAttribute($entry, 'objectClass', 'groupOfNames', true);
        Zend_Ldap_Attribute::setAttribute($entry, 'objectClass', 'extensibleObject', true);
        Zend_Ldap_Attribute::setAttribute($entry, 'cn', $name);
        Zend_Ldap_Attribute::setAttribute($entry, 'member', null);
        Zend_Ldap_Attribute::setAttribute($entry, 'gidNumber', $group_id);
        Zend_Ldap_Attribute::setAttribute($entry, 'labeledURI', $group_path . ' group_path');
        $entry = $this->addMemberToGroupEntry($entry, $user_name, $group_access);

        return $entry;
    }

    /**
     * @param array  $group
     * @param string $user_username
     * @param string $group_access
     *
     * @return mixed
     */
    private function addMemberToGroupEntry($group, $user_username, $group_access)
    {
        $dn = $this->getDnForUser($user_username);
        Zend_Ldap_Attribute::setAttribute($group, 'member', $dn, true);
        if ('owner' == strtolower($group_access)) {
            Zend_Ldap_Attribute::setAttribute($group, 'owner', $dn, true);
        }
        Zend_Ldap_Attribute::removeDuplicatesFromAttribute($group, 'member');
        Zend_Ldap_Attribute::removeDuplicatesFromAttribute($group, 'owner');

        return $group;
    }

    public function resetMessages()
    {
        $this->messages = array();
    }

    public function saveEntry(array $entry, $dn)
    {
        $this->getConnectionUser()->save($dn, $entry);
        $this->messages[] = $this->getConnectionUser()->getLastError();
    }

    /**
     * @param string $password_hash
     * @return string
     */
    public function createPasswordFromHash($password_hash)
    {
        $password = '{MD5}' . base64_encode(pack("H*", $password_hash));

        return $password;
    }

}