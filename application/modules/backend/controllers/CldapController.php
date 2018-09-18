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

    const filename = "members.ldif";
    const filename_errors = "members.error.ldif";

    protected $domain;
    protected $tld;
    protected $logfile;
    protected $errorlogfile;
    protected $config;

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
    }


    /**
     * @throws Zend_Exception
     * @throws Zend_Validate_Exception
     */
    public function runAction()
    {
        ini_set('memory_limit', '1024M');
        $fileDomainId = str_replace('.', '_', $this->config->accountDomainName);
        $this->logfile = realpath(APPLICATION_DATA . "/logs") . DIRECTORY_SEPARATOR . $fileDomainId . '_' . self::filename;
        $this->errorlogfile = realpath(APPLICATION_DATA . "/logs") . DIRECTORY_SEPARATOR . $fileDomainId . '_' . self::filename_errors;
        $this->initFiles($this->logfile, $this->errorlogfile);
        $method = $this->getParam('method', 'create');
        

        file_put_contents($this->logfile, "METHOD: {$method}\n--------------\n", FILE_APPEND);
        file_put_contents($this->errorlogfile, "METHOD: {$method}\n--------------\n", FILE_APPEND);

        
        if($this->hasParam('member_id')) {
            $memberId = $this->getParam('member_id');
            $filter = " AND `m`.`member_id` = ".$memberId;
            $members = $this->getMemberList($filter);
            
        } else {
            $members = $this->getMemberList();
        }

        if ('create' == $method) {
            $this->exportMembers($members);
            return;
        }
        if ('update' == $method) {
            $this->updateMembers($members);
            return;
        }
    }

    /**
     * @param $file
     * @param $errorfile
     */
    private function initFiles($file, $errorfile)
    {
        if (file_exists($file)) {
            file_put_contents($file, "1");
            unlink($file);
        }
        if (file_exists($errorfile)) {
            file_put_contents($errorfile, "1");
            unlink($errorfile);
        }
    }

    /**
     * @return Zend_Db_Statement_Interface
     */
    private function getMemberList($filter = "")
    {
        $sql = "
            SELECT `mei`.`external_id`,`m`.`member_id`, `m`.`username`, `me`.`email_address`, `m`.`password`, `m`.`roleId`, `m`.`firstname`, `m`.`lastname`, `m`.`profile_image_url`, `m`.`created_at`, `m`.`changed_at`, `m`.`source_id`
            FROM `member` AS `m`
            LEFT JOIN `member_email` AS `me` ON `me`.`email_member_id` = `m`.`member_id` AND `me`.`email_primary` = 1
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE `m`.`is_active` = 1 AND `m`.`is_deleted` = 0 AND `me`.`email_checked` IS NOT NULL AND `me`.`email_deleted` = 0 
            # AND (me.email_address like '%opayq%' OR m.username like '%rvs%')
            " . $filter . "
            ORDER BY `m`.`member_id` ASC
            # LIMIT 200
        ";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql);
        
        file_put_contents($this->logfile, "Load : " . count($result) . " members...\n", FILE_APPEND);

        return $result;
    }

    /**
     * @param Zend_Db_Statement_Interface $members
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Validate_Exception
     */
    private function exportMembers($members)
    {
        $usernameValidChars = new Zend_Validate_Regex('/^(?=.{3,40}$)(?![-])(?!.*[-]{2})[a-z0-9-]+(?<![-])$/');
        $modelOcsIdent = new Default_Model_Ocs_Ident();

        file_put_contents($this->logfile, "Start exportMembers with " . count($members) . " members...\n", FILE_APPEND);
        
        while ($member = $members->fetch()) {
            file_put_contents($this->logfile, "Member " . $member['username'] . "\n", FILE_APPEND);
            if (false === $usernameValidChars->isValid($member['username'])) {
                file_put_contents($this->errorlogfile, print_r($member, true), FILE_APPEND);
                continue;
            }
            file_put_contents($this->logfile, print_r($member, true), FILE_APPEND);
            try {
                $modelOcsIdent->createUserInLdap($member);
            } catch (Zend_Ldap_Exception $e) {
                Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            $errors = $modelOcsIdent->getMessages();
            file_put_contents($this->errorlogfile, print_r($errors, true), FILE_APPEND);
            Zend_Registry::get('logger')->info(print_r($errors, true));
        }

        return true;
    }

    /**
     * @param $members
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Validate_Exception
     */
    private function updateMembers($members)
    {
        $usernameValidChars = new Zend_Validate_Regex('/^(?=.{4,40}$)(?![-])(?!.*[-]{2})[a-z0-9-]+(?<![-])$/');
        $modelOcsIdent = new Default_Model_Ocs_Ident();

        while ($member = $members->fetch()) {
            if (false === $usernameValidChars->isValid($member['username'])) {
                file_put_contents($this->errorlogfile, print_r($member, true), FILE_APPEND);
                continue;
            }
            file_put_contents($this->logfile, print_r($member, true), FILE_APPEND);
            try {
                $modelOcsIdent->updateUserInLdap($member);
            } catch (Zend_Ldap_Exception $e) {
                Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            $errors = $modelOcsIdent->getErrMessages();
            Zend_Registry::get('logger')->info(print_r($errors, true));
        }

        return true;
    }


    /**
     * @param Zend_Db_Statement_Interface $members
     *
     * @param                             $file
     *
     * @param                             $errorfile
     *
     * @return string
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Validate_Exception
     */
    private function renderLdif($members, $file, $errorfile)
    {
        $usernameValidChars = new Zend_Validate_Regex('/^(?=.{4,40}$)(?![-])(?!.*[-]{2})[a-z0-9-]+(?<![-])$/');

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
email: {$member['email_address']}\n" . (empty(trim($member['firstname'])) ? "" : "gn: {$member['firstname']}\n")
            . (empty(trim($member['lastname'])) ? "" : "sn: {$member['lastname']}\n") . "uidNumber: {$member['member_id']}
gidNumber: {$member['roleId']}
memberUid: {$member['external_id']}
";
    }

}