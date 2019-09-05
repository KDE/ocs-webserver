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
 * Created: 08.05.2017
 */
class Backend_CldapController extends Local_Controller_Action_CliAbstract
{

    const filename = "members";

    protected $domain;
    protected $tld;
    /** @var Zend_Config */
    protected $config;
    protected $log;

    /**
     * @inheritDoc
     */
    public function __construct(
        Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = array()
    ) {
        parent::__construct($request, $response, $invokeArgs);
        $this->config = Zend_Registry::get('config')->settings->server->ldap;
        $this->log = self::initLog();
        $this->_helper->viewRenderer->setNoRender(false);
    }

    /**
     * @return Zend_Log
     * @throws Zend_Log_Exception
     */
    private static function initLog()
    {
        $writer = new Zend_Log_Writer_Stream(APPLICATION_DATA . '/logs/ldap-' . date("Ymd-His") . '.log');
        $logger = new Zend_Log($writer);

        return $logger;
    }

    /**
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function runAction()
    {
        ini_set('memory_limit', '1024M');

        $force = (boolean)$this->getParam('force', false);
        $method = $this->getParam('method', 'create');

        $this->log->info("METHOD: {$method}\n--------------\n");
        $this->log->info(print_r($this->config->toArray(), true));

        if ($this->hasParam('member_id')) {
            $member_id = $this->getParam('member_id');
            $operator = $this->getParam('op', null);
            $members = $this->getMemberList($member_id, $operator);
        } else {
            $members = $this->getMemberList();
        }

        if ('create' == $method) {
            $this->exportMembers($members, $force);

            return;
        }
        if ('update' == $method) {
            $this->updateMembers($members);

            return;
        }
        if ('validate' == $method) {
            $this->validateMembers($members, $force);

            return;
        }
        if ('updateAvatar' == $method) {
            $this->updateAvatar($members);

            return;
        }
        if ('updatePassword' == $method) {
            $this->updatePassword($members);

            return;
        }
    }

    /**
     * @param int|null $memberId
     * @param string   $operator
     *
     * @return Zend_Db_Statement_Interface
     * @throws Zend_Db_Statement_Exception
     */
    private function getMemberList($memberId = null, $operator = "=")
    {
        $filter = "";
        if (empty($operator)) {
            $operator = "=";
        }
        if ($operator == "gt") {
            $operator = ">";
        }
        if ($operator == "lt") {
            $operator = "<";
        }
        if (isset($memberId)) {
            $filter = " AND `m`.`member_id` {$operator} " . $memberId;
        }

        $sql = "
            SELECT `mei`.`external_id`,`m`.`member_id`, `m`.`username`, `me`.`email_address`, `m`.`password`, `m`.`roleId`, `m`.`firstname`, `m`.`lastname`, `m`.`profile_image_url`, `m`.`created_at`, `m`.`changed_at`, `m`.`source_id`
            FROM `member` AS `m`
            LEFT JOIN `member_email` AS `me` ON `me`.`email_member_id` = `m`.`member_id` AND `me`.`email_primary` = 1
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE `m`.`is_active` = 1 
              AND `m`.`is_deleted` = 0 
              AND `me`.`email_checked` IS NOT NULL 
              AND `me`.`email_deleted` = 0
              AND LOCATE('_double', `m`.`username`) = 0 
              AND LOCATE('_double', `me`.`email_address`) = 0
            {$filter}
            ORDER BY `m`.`member_id`
            # LIMIT 100
        ";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql);

        $this->log->info("Load : " . $result->rowCount() . " members...");

        return $result;
    }

    /**
     * @param Zend_Db_Statement_Interface $members
     *
     * @param bool                        $force
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    private function exportMembers($members, $force = false)
    {
        $modelOcsLdap = new Default_Model_Ocs_Ldap();

        while ($member = $members->fetch()) {
            try {
                $ldapUserData = $modelOcsLdap->addUserFromArray($member, $force);
            } catch (Zend_Ldap_Exception $e) {
                $this->log->info("process " . Zend_Json::encode($member));
                $this->log->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());

                continue;
            }
            $messages = $modelOcsLdap->getMessages();
            if (isset($messages[0]) AND $messages[0] != Default_Model_Ocs_Ldap::LDAP_SUCCESS) {
                $this->log->info("process " . Zend_Json::encode($member));
                $this->log->info("messages " . Zend_Json::encode($messages));
            }
        }

        return true;
    }

    /**
     * @param int    $member_id
     * @param string $status_msg
     * @param string $response_msg
     * @param string $ldap_new
     * @param string $ldap_old
     *
     * CREATE TABLE `log_ldap` (
     * `id` BINARY(16) NOT NULL,
     * `member_id` int(11) NOT NULL,
     * `status` varchar(45) NOT NULL,
     * `response_msg` varchar(255) DEFAULT NULL,
     * `json_ldap_old` text DEFAULT NULL,
     * `json_ldap_new` text DEFAULT NULL,
     * `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     * PRIMARY KEY (`id`),
     * KEY `idx_member_id` (`member_id`)
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     *
     */
    private function dbLog($member_id, $status_msg, $response_msg, $ldap_new, $ldap_old)
    {
        $sql = "INSERT INTO `log_ldap` 
               (`id`,`member_id`, `status`, `response_msg`, `json_ldap_old`, `json_ldap_new`) 
               VALUES 
               (UNHEX(REPLACE(UUID(),'-','')),:memberId, :statusVal, :msgVal, :dbVal, :ldapVal)";
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query($sql,
            array(
                'memberId'  => $member_id,
                'statusVal' => $status_msg,
                'msgVal'    => $response_msg,
                'ldapVal'   => $ldap_new,
                'dbVal'     => $ldap_old
            ));
    }

    /**
     * @param Zend_Db_Statement_Interface $members
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    private function updateMembers($members)
    {
        $model_Ocs_Ldap = new Default_Model_Ocs_Ldap();
        // create an org unit for backup user data
        $ou = "member-bkp-" . date("Ymd-Hi");
        $entry_ou_dn = $this->createBackupTree($model_Ocs_Ldap, $ou);

        while ($member = $members->fetch()) {
            try {
                $model_Ocs_Ldap->resetMessages();
                $ldapUser = $model_Ocs_Ldap->getLdapUserByMemberId($member['member_id']);
                if (empty($ldapUser)) {
                    throw new Exception('user not found');
                }

                $model_Ocs_Ldap->updateUserFromArray($member);
                $ldapUser = $this->storeBackupEntry($model_Ocs_Ldap, $member, $entry_ou_dn, $ldapUser);
            } catch (Exception $e) {
                $this->log->info("process " . json_encode($member));
                $this->log->info($e->getMessage() . PHP_EOL . $e->getTraceAsString());

                continue;
            }
            $messages = $model_Ocs_Ldap->getMessages();
            if (isset($messages[0]) AND $messages[0] != "Success") {
                $this->log->info("process " . Zend_Json::encode($member));
                $this->log->info("messages " . Zend_Json::encode($messages));
            }
        }

        return true;
    }

    /**
     * @param Default_Model_Ocs_Ldap $modelOcsLdap
     * @param                        $ou
     * @return string
     */
    private function createBackupTree(Default_Model_Ocs_Ldap $modelOcsLdap, $ou)
    {
        $entry_ou = $modelOcsLdap->createEntryOrgUnit($ou);
        $entry_ou_dn = $modelOcsLdap->addOrgUnit($entry_ou, $ou);
        $this->log->info("name for backup in ldap tree: {$entry_ou_dn}");

        return $entry_ou_dn;
    }

    /**
     * @param Default_Model_Ocs_Ldap $modelOcsLdap
     * @param                        $member
     * @param                        $entry_ou_dn
     * @param array                  $ldapUser
     * @return array
     */
    private function storeBackupEntry(
        Default_Model_Ocs_Ldap $modelOcsLdap,
        array $member,
        $entry_ou_dn,
        array $ldapUser
    ) {
        // backup old entry
        $dnBackup = $modelOcsLdap->getDnForUser($member['username'], $entry_ou_dn);
        unset($ldapUser['dn']);
        $modelOcsLdap->addEntry($ldapUser, $dnBackup);

        return $ldapUser;
    }

    /**
     * @param      $members
     * @param bool $force
     * @return bool
     *
     * @throws Default_Model_Ocs_Exception
     * @throws Zend_Exception
     */
    private function validateMembers($members, $force = false)
    {
        $model_Ocs_Ldap = new Default_Model_Ocs_Ldap();
        if ($force) {
            // create an org unit for backup user data
            $ou = "member-bkp-" . date("Ymd-Hi");
            $entry_ou_dn = $this->createBackupTree($model_Ocs_Ldap, $ou);
        }

        while ($member = $members->fetch()) {
            $model_Ocs_Ldap->resetMessages();
            try {
                $ldapEntry = $model_Ocs_Ldap->getLdapUser($member);
                if (empty($ldapEntry)) {
                    $this->log->info('user not exist (' . $member['member_id'] . ', ' . $member['username'] . ')');

                    continue;
                }
                $result = $this->validateEntry($member, $ldapEntry);
                if (isset($result)) {
                    $this->log->info('member (' . $member['member_id'] . ', ' . $member['username'] . ') unequal: '
                                     . PHP_EOL
                                     . implode("<=>", $result)
                                     . ' '
                                     . $member[$result[0]] . '<=>' . Zend_Ldap_Attribute::getAttribute($ldapEntry,
                            $result[1], 0));
                    if ($force) {
                        $model_Ocs_Ldap->updateUserFromArray($member);
                        $ldapUser = $this->storeBackupEntry($model_Ocs_Ldap, $member, $entry_ou_dn, $ldapEntry);
                    }
                }
            } catch (Zend_Ldap_Exception $e) {
                $this->log->info("process " . Zend_Json::encode($member));
                $this->log->info($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            $messages = $model_Ocs_Ldap->getMessages();
            if (false == empty($messages)) {
                $this->log->info(json_encode($messages));
            }
        }

        return true;
    }

    /**
     * @param array $member
     * @param array $ldapEntry
     * @return array|null
     */
    private function validateEntry(array $member, array $ldapEntry)
    {
        $enc = mb_detect_encoding($member['username']) ? mb_detect_encoding($member['username']) : 'UTF-8';
        $lower_username = mb_strtolower($member['username'], $enc);

        $enc = mb_detect_encoding($member['email_address']) ? mb_detect_encoding($member['email_address']) : 'UTF-8';
        $lower_mail = mb_strtolower($member['email_address'], $enc);

        $attr = Zend_Ldap_Attribute::getAttribute($ldapEntry, 'uidNumber', 0);
        if ($member['member_id'] != $attr) {
            return array('member_id', 'uidNumber');
        }
        $attr = Zend_Ldap_Attribute::getAttribute($ldapEntry, 'memberUid', 0);
        if ($member['external_id'] != $attr) {
            return array('external_id', 'memberUid');
        }
        $attr = Zend_Ldap_Attribute::getAttribute($ldapEntry, 'cn', 0);
        $enc = mb_detect_encoding($attr) ? mb_detect_encoding($attr) : 'UTF-8';
        if ($lower_username != mb_strtolower($attr, $enc)) {
            return array('username', 'cn');
        }
        $attr = Zend_Ldap_Attribute::getAttribute($ldapEntry, 'email', 0);
        $enc = mb_detect_encoding($attr) ? mb_detect_encoding($attr) : 'UTF-8';
        if ($lower_mail != mb_strtolower($attr, $enc)) {
            return array('email_address', 'email');
        }
        $attr = Zend_Ldap_Attribute::getAttribute($ldapEntry, 'userPassword', 0);
        $password = '{MD5}' . base64_encode(pack("H*", $member['password']));
        if ($password != $attr) {
            return array('password', 'userPassword');
        }

        if(false == array_key_exists('jpegphoto', $ldapEntry)) {
            return array('profile_image_url','jpegPhoto');
        }

        return null;
    }

    private function updateAvatar($members)
    {
        $model_Ocs_Ldap = new Default_Model_Ocs_Ldap();
        // create an org unit for backup user data
        $ou = "member-bkp-" . date("Ymd-Hi");
        $backupTree = $this->createBackupTree($model_Ocs_Ldap, $ou);

        while ($member = $members->fetch()) {
            try {
                $model_Ocs_Ldap->resetMessages();
                $ldapUser = $model_Ocs_Ldap->getLdapUserByMemberId($member['member_id']);
                if (empty($ldapUser)) {
                    throw new Exception('user not found');
                }
                $avatar = $model_Ocs_Ldap->createJpegPhoto($member['member_id'], $member['profile_image_url']);
                if (false === $avatar) {
                    throw new Exception('update avatar failed');
                }
                $ldapUserNew = $model_Ocs_Ldap->updateLdapAttrib($ldapUser, $avatar,Default_Model_Ocs_Ldap::JPEG_PHOTO);
                $model_Ocs_Ldap->updateLdapEntry($ldapUserNew);
                $ldapUser = $this->storeBackupEntry($model_Ocs_Ldap, $member, $backupTree, $ldapUser);
            } catch (Exception $e) {
                $this->log->info("process " . Zend_Json::encode($member));
                $this->log->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());

                continue;
            }
            $messages = $model_Ocs_Ldap->getMessages();
            if (isset($messages[0]) AND $messages[0] != Default_Model_Ocs_Ldap::LDAP_SUCCESS) {
                $this->log->info("process " . Zend_Json::encode($member));
                $this->log->info("messages " . Zend_Json::encode($messages));
            }
        }

        return true;
    }

    private function updatePassword($members)
    {
        $model_Ocs_Ldap = new Default_Model_Ocs_Ldap();
        // create an org unit for backup user data
        $ou = "member-bkp-" . date("Ymd-Hi");
        $backupTree = $this->createBackupTree($model_Ocs_Ldap, $ou);

        while ($member = $members->fetch()) {
            try {
                $model_Ocs_Ldap->resetMessages();
                $ldapUser = $model_Ocs_Ldap->getLdapUserByMemberId($member['member_id']);
                if (empty($ldapUser)) {
                    throw new Exception('user not found');
                }
                $attribValue = Zend_Ldap_Attribute::getAttribute($ldapUser, Default_Model_Ocs_Ldap::USER_PASSWORD, 0);
                $passwordFromHash = $model_Ocs_Ldap->createPasswordFromHash($member['password']);
                if (false === $passwordFromHash) {
                    throw new Exception('update password failed');
                }
                if ($attribValue != $passwordFromHash) {
                    $ldapUserNew = $model_Ocs_Ldap->updateLdapAttrib($ldapUser, $passwordFromHash,Default_Model_Ocs_Ldap::USER_PASSWORD);
                    $model_Ocs_Ldap->updateLdapEntry($ldapUserNew);
                    $ldapUser = $this->storeBackupEntry($model_Ocs_Ldap, $member, $backupTree, $ldapUser);
                }
            } catch (Exception $e) {
                $this->log->info("process " . Zend_Json::encode($member));
                $this->log->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());

                continue;
            }
            $messages = $model_Ocs_Ldap->getMessages();
            if (isset($messages[0]) AND $messages[0] != Default_Model_Ocs_Ldap::LDAP_SUCCESS) {
                $this->log->info("process " . Zend_Json::encode($member));
                $this->log->info("messages " . Zend_Json::encode($messages));
            }
        }

        return true;
    }

    /**
     * @param Zend_Db_Statement_Interface $members
     * @param                             $file
     * @param                             $errorfile
     *
     * @return string
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    private function renderLdif($members, $file, $errorfile)
    {
        $usernameValidChars = new Local_Validate_UsernameValid();

        while ($member = $members->fetch()) {
            $ldif = $this->renderElement($member);
            if (false === $usernameValidChars->isValid($member['username'])) {
                file_put_contents($errorfile, $ldif, FILE_APPEND);
                continue;
            }
            file_put_contents($file, $ldif, FILE_APPEND);
        }

        return true;
    }

    /**
     * @param $member
     *
     * @return string
     */
    private function renderElement($member)
    {
        $username = strtolower($member['username']);
        $password = base64_encode(pack("H*", $member['password']));

        return "
dn: cn={$username},ou=member,dc={$this->domain},dc={$this->tld}
objectClass: top
objectClass: account
objectClass: extensibleObject
uid: {$username}
uid: {$member['email_address']}
userPassword: {MD5}{$password}
cn: {$member['username']}
email: {$member['email_address']}\n"
               . (empty(trim($member['firstname'])) ? "" : "gn: {$member['firstname']}\n")
               . (empty(trim($member['lastname'])) ? "" : "sn: {$member['lastname']}\n")
               . "uidNumber: {$member['member_id']}
gidNumber: {$member['roleId']}
memberUid: {$member['external_id']}
";
    }

}