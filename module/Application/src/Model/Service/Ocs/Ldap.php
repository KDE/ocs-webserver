<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Model\Service\Ocs;


use Application\View\Helper\Image;
use Exception;
use Imagick;
use ImagickException;
use Laminas\Config\Config;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Ldap\Attribute;
use Laminas\Ldap\Exception\LdapException;
use Laminas\Log\Logger;
use Library\Tools\PasswordEncrypt;

class Ldap
{
    const JPEG_PHOTO = 'jpegPhoto';
    const LDAP_SUCCESS = '(Success)';
    const USER_PASSWORD = 'userPassword';

    /** @var string */
    protected $baseDnUser;
    /** @var Config */
    protected $config;
    protected $messages;
    protected $errCode;
    protected $baseDnGroup;
    /** @var \Laminas\Ldap\Ldap */
    protected $identGroupServer;
    /** @var \Laminas\Ldap\Ldap */
    private $ldap;
    /** @var Image */
    private $image_helper;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @inheritDoc
     */
    public function __construct(Config $config, Image $image_helper)
    {
        if (isset($config)) {
            $this->config = $config;
        } else {
            throw new ServerException('config missing');
        }
        $this->baseDnUser = $this->config->userBaseDn;
        $this->baseDnGroup = $this->config->groupBaseDn;
        $this->messages = array();
        $this->errCode = 0;
        $this->image_helper = $image_helper;
        $this->logger = $GLOBALS['ocs_log'];
    }

    /**
     * @return string
     */
    public static function getUserBaseDn()
    {
        try {
            return $GLOBALS['ocs_config']->settings->server->ldap->userBaseDn;
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        return '';
    }

    /**
     * @param string $ouName
     *
     * @return array
     */
    public function createEntryOrgUnit($ouName)
    {
        $entry = array();
        Attribute::setAttribute($entry, 'objectClass', 'top');
        Attribute::setAttribute($entry, 'objectClass', 'organizationalUnit', true);
        Attribute::setAttribute($entry, 'ou', $ouName);

        return $entry;
    }

    /**
     * @param array  $entry
     * @param string $ouName
     *
     * @return string return DN for the new org unit
     * @throws LdapException
     */
    public function addOrgUnit(array $entry, $ouName)
    {
        $rootDn = $this->config->rootDn;
        $dn = "ou={$ouName},{$rootDn}";

        $this->addEntry($entry, $dn);

        return $dn;
    }

    /**
     * @param array $entry
     * @param       $dn
     *
     * @throws LdapException
     */
    public function addEntry(array $entry, $dn)
    {
        $this->getConnectionUser()->add($dn, $entry);
        $this->messages[] = __METHOD__ . ' = ' . $this->getConnectionUser()->getLastError();
    }

    /**
     * @return \Laminas\Ldap\Ldap
     * @throws LdapException
     */
    private function getConnectionUser()
    {
        if (false === empty($this->ldap)) {
            return $this->ldap;
        }
        $config_connection = [
            'host'              => $this->config->host,
            'username'          => $this->config->username,
            'password'          => $this->config->password,
            'accountDomainName' => $this->config->accountDomainName,
            'baseDn'            => $this->config->userBaseDn,
        ];
        $this->ldap = new \Laminas\Ldap\Ldap($config_connection);
        $this->ldap->bind();

        return $this->ldap;
    }

    /**
     * @param int $member_id
     *
     * @return bool
     * @throws LdapException
     * @throws ServerException
     */
    public function updateMail($member_id)
    {
        $connection = $this->getConnectionUser();
        $member_data = $this->getMemberData($member_id);

        try {
            $entry = $this->getLdapUser($member_data);
        } catch (Exception $e) {
            $this->messages[] = "Failed.";
            $this->logger->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (empty($entry)) {
            $this->messages[] = "Failed.";
            $this->logger->err(__METHOD__ . ' - ldap entry for member does not exists. Going to create it.');

            return false;
        }

        $email = $this->lowerString($member_data['email_address']);
        $oldUidAttribute = Attribute::getAttribute($entry, 'email');
        Attribute::removeFromAttribute($entry, 'uid', $oldUidAttribute);
        Attribute::removeFromAttribute($entry, 'email', $oldUidAttribute);
        Attribute::setAttribute($entry, 'email', $member_data['email_address']);
        Attribute::setAttribute($entry, 'uid', $email, true);
        $dn = $entry['dn'];
        $connection->update($dn, $entry);
        $connection->getLastError($this->errCode, $this->messages);

        return true;
    }

    /**
     * @param int  $member_id
     *
     * @param bool $onlyActive
     *
     * @return array
     * @throws ServerException
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

        $adapter = GlobalAdapterFeature::getStaticAdapter();
        $result = $adapter->query($sql, array('memberId' => $member_id));
        if (count($result) == 0) {
            throw new ServerException('member with id ' . $member_id . ' could not found.');
        }

        return $result->current()->getArrayCopy();
    }

    /**
     * @param array $member_data
     *
     * @return array
     * @throws ServerException
     */
    public function getLdapUser(array $member_data)
    {
        if (empty($member_data)) {
            throw new ServerException('given member_data empty');
        }

        $entry = array();

        try {
            $entry = $this->getLdapUserByMemberId($member_data['member_id']);

            if ($entry) {
                return $entry;
            }

            $entry = $this->getLdapUserByUsername($member_data['username']);
        } catch (Exception $e) {
            $this->logger->err($e->getMessage());
        }

        return $entry;
    }

    /**
     * @param int $member_id
     *
     * @return array
     * @throws LdapException
     * @throws ServerException
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
            throw new ServerException('found member_id more than once. member_id: ' . $member_id);
        }

        return $entries[0];
    }

    /**
     * @param string $username
     *
     * @return array
     * @throws LdapException
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
     *
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
     *
     * @return bool
     * @throws LdapException
     * @throws ServerException
     */
    public function updatePassword($member_id, $password = null)
    {
        $connection = $this->getConnectionUser();
        $member_data = $this->getMemberData($member_id);
        try {
            $entry = $this->getLdapUser($member_data);
        } catch (Exception $e) {
            $this->messages[] = "Failed.";
            $this->logger->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }

        if (empty($entry)) {
            $this->messages[] = "Failed.";
            $this->logger->err(__METHOD__ . ' - ldap entry for member does not exists. member_id:' . $member_id);

            return false;
        }
        Attribute::removeFromAttribute(
            $entry, self::USER_PASSWORD, Attribute::getAttribute($entry, self::USER_PASSWORD)
        );
        if (isset($password)) {
            $hash = PasswordEncrypt::getLdap($password);
        } else {
            $hash = '{MD5}' . base64_encode(pack("H*", $member_data['password']));
        }
        Attribute::setAttribute($entry, self::USER_PASSWORD, $hash);

        $connection->update($entry['dn'], $entry);
        $connection->getLastError($this->errCode, $this->messages);

        return true;
    }

    /**
     * @param int $member_id
     *
     * @return bool
     * @throws LdapException
     * @throws ServerException
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
            $this->logger->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (false === empty($entry)) {
            $this->messages[] = __METHOD__ . ' = ' . "user already exists.";
            $this->logger->err(__METHOD__ . ' - ldap entry for member does not exists. Going to create it.');

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
     */
    public function createEntryForUser(array $member)
    {
        $username = $this->lowerString($member['username']);
        $password = $this->createPasswordFromHash($member['password']);
        $mail_address = $this->lowerString($member['email_address']);
        $jpegPhoto = $this->createJpegPhoto($member['member_id'], $member['profile_image_url']);

        $entry = array();
        Attribute::setAttribute($entry, 'objectClass', 'top');
        Attribute::setAttribute($entry, 'objectClass', 'account', true);
        Attribute::setAttribute($entry, 'objectClass', 'extensibleObject', true);
        Attribute::setAttribute($entry, 'uid', $username);
        Attribute::setAttribute($entry, 'uid', $mail_address, true);
        Attribute::removeDuplicatesFromAttribute($entry, 'uid');
        Attribute::setAttribute($entry, self::USER_PASSWORD, $password);
        Attribute::setAttribute($entry, 'cn', $username);
        Attribute::setAttribute($entry, 'email', $member['email_address']);
        Attribute::setAttribute($entry, 'uidNumber', $member['member_id']);
        Attribute::setAttribute($entry, 'gidNumber', $member['roleId']);
        Attribute::setAttribute($entry, 'memberUid', $member['external_id']);
        if (false === empty(trim($member['firstname']))) {
            Attribute::setAttribute($entry, 'gn', $member['firstname']);
        }
        if (false === empty(trim($member['lastname']))) {
            Attribute::setAttribute($entry, 'sn', $member['lastname']);
        }

        Attribute::setAttribute($entry, self::JPEG_PHOTO, $jpegPhoto);

        return $entry;
    }

    /**
     * @param string $password_hash
     *
     * @return string
     */
    public function createPasswordFromHash($password_hash)
    {
        $password = '{MD5}' . base64_encode(pack("H*", $password_hash));

        return $password;
    }

    /**
     * @param int    $member_id
     * @param string $profile_image_url
     *
     * @return bool|string
     */
    public function createJpegPhoto($member_id, $profile_image_url)
    {
        $imgTempPath = APPLICATION_DATA . '/uploads/tmp/' . $member_id . "_avatar.jpg";
        $helperImagePath = $this->image_helper;
        $urlImage = $helperImagePath->Image($profile_image_url);

        try {
            $im = new imagick($urlImage);
            $layer_method = imagick::LAYERMETHOD_FLATTEN;
            $im = $im->mergeImageLayers($layer_method);
        } catch (ImagickException $e) {
            $this->logger->err(__METHOD__ . ' - error during converting avatar image. ' . $e->getMessage() . " ({$member_id};{$profile_image_url})");

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
     *
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
     * @throws LdapException
     */
    public function updateAvatar($member_id, $profile_image_url)
    {
        $jpegPhoto = $this->createJpegPhoto($member_id, $profile_image_url);

        try {
            $entry = $this->getLdapUserByMemberId($member_id);
        } catch (Exception $e) {
            $this->messages[] = "Failed.";
            $this->logger->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (empty($entry)) {
            $this->messages[] = "Failed.";
            $this->logger->err(__METHOD__ . ' - ldap entry for member does not exists.');

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
        Attribute::removeFromAttribute($entry, $attribName, Attribute::getAttribute($entry, $attribName));

        Attribute::setAttribute($entry, $attribName, $attribValue);

        return $entry;
    }

    /**
     * @param array $entry
     *
     * @throws LdapException
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
     * @throws LdapException
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
            $this->logger->err(__METHOD__ . ' - ' . $e->getMessage());

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
     * @throws LdapException
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
     * @throws LdapException
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
            $this->messages[] = 'username changed: ' . $user['dn'] . $member_data['username'];
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
     *
     * @return bool
     */
    private function hasChangedUsername($user_name, $user)
    {
        return !Attribute::attributeHasValue($user, 'cn', $this->lowerString($user_name));
    }

    /**
     * @param $member_id
     * @param $username
     *
     * @return bool
     * @throws ServerException
     * @throws LdapException
     */
    public function hasUser($member_id, $username)
    {
        if (empty($member_id)) {
            throw new ServerException('given $member_id empty');
        }

        $ldap = $this->getConnectionUser();
        $filter = "(uidNumber={$member_id})";
        $entries = $ldap->searchEntries($filter, $this->baseDnUser);

        if (count($entries) > 1) {
            throw new ServerException("{$member_id} is ambiguous");
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

    /**
     * @param string $user_username
     * @param string $group_name
     * @param string $group_access
     * @param null   $group_id
     * @param null   $group_path
     *
     * @return array|bool|mixed
     * @throws LdapException
     */
    public function addGroupMember($user_username, $group_name, $group_access, $group_id = null, $group_path = null)
    {
        $connection = $this->getConnectionGroup();
        $dnGroup = $this->getDnGroup($group_name);

        //Only update, if member exists
        try {
            $entry = $this->getLdapUserByUsername($user_username);
        } catch (Exception $e) {
            $this->messages[] = "Failed.";
            $this->logger->warn(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (empty($entry)) {
            $this->messages[] = "user not exists. nothing to update.";
            $this->logger->warn(__METHOD__ . ' - ldap entry for new group user does not exists.' . $user_username);

            return false;
        }

        //Only update, if group exists
        try {
            $entry = $connection->getEntry($dnGroup);
        } catch (Exception $e) {
            $this->messages[] = "Failed.";
            $this->logger->warn(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
        if (empty($entry) and (strtolower($group_access) != 'owner')) {
            $this->messages[] = "group not exists. nothing to update.";
            $this->logger->warn(__METHOD__ . ' - ldap entry for group does not exists.');

            return false;
        }
        if (empty($entry) and (strtolower($group_access) == 'owner')) {
            if (empty($group_id) or empty($group_path)) {
                $this->logger->warn(__METHOD__ . ' - ldap entry for group does not exists and owner is given. But group_id or group_path is empty.');

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
     * @return \Laminas\Ldap\Ldap
     * @throws LdapException
     */
    private function getConnectionGroup()
    {
        if (false === empty($this->identGroupServer)) {
            return $this->identGroupServer;
        }
        $config_connection = [
            'host'              => $this->config->host,
            'username'          => $this->config->username,
            'password'          => $this->config->password,
            'accountDomainName' => $this->config->accountDomainName,
            'baseDn'            => $this->config->groupBaseDn,
        ];

        $this->identGroupServer = new \Laminas\Ldap\Ldap($config_connection);
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
            $baseDn = $this->config->groupBaseDn;
        } catch (Exception $e) {
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
     * @throws LdapException
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
        Attribute::setAttribute($entry, 'objectClass', 'top');
        Attribute::setAttribute($entry, 'objectClass', 'groupOfNames', true);
        Attribute::setAttribute($entry, 'objectClass', 'extensibleObject', true);
        Attribute::setAttribute($entry, 'cn', $name);
        Attribute::setAttribute($entry, 'member', null);
        Attribute::setAttribute($entry, 'gidNumber', $group_id);
        Attribute::setAttribute($entry, 'labeledURI', $group_path . ' group_path');
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
        Attribute::setAttribute($group, 'member', $dn, true);
        if ('owner' == strtolower($group_access)) {
            Attribute::setAttribute($group, 'owner', $dn, true);
        }
        Attribute::removeDuplicatesFromAttribute($group, 'member');
        Attribute::removeDuplicatesFromAttribute($group, 'owner');

        return $group;
    }

    /**
     *
     */
    public function resetMessages()
    {
        $this->messages = array();
    }

    /**
     * @param array $entry
     * @param       $dn
     *
     * @throws LdapException
     */
    public function saveEntry(array $entry, $dn)
    {
        $this->getConnectionUser()->save($dn, $entry);
        $this->messages[] = $this->getConnectionUser()->getLastError();
    }

}