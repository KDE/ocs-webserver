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
 * Created: 06.08.2018
 */

/**
 * Class Backend_CexportController
 * @deprecated
 */
class Backend_CexportController extends Local_Controller_Action_CliAbstract
{

    const filename = "member_export.log";
    const filename_errors = "member_export.error.log";

    protected $logfile;
    protected $errorlogfile;

    /**
     * @throws Zend_Exception
     */
    public function runAction()
    {
        $member_id = (int)$this->getParam("m");
        $member = $this->getMember($member_id);
        if (false === $this->isValidUsername($member)) {
            throw new Zend_Exception('username is not valid');
        }
        $this->logfile = realpath(APPLICATION_DATA . "/logs") . DIRECTORY_SEPARATOR . self::filename;
        $this->errorlogfile = realpath(APPLICATION_DATA . "/logs") . DIRECTORY_SEPARATOR . self::filename_errors;
        $this->exportMember($member);
    }

    /**
     * @param int $member_id
     *
     * @return array|null
     * @throws Zend_Exception
     */
    private function getMember($member_id)
    {
        $sql = "
            SELECT `mei`.`external_id`,
                   `m`.`member_id`, 
                   `m`.`username`, 
                   `me`.`email_address`,
                   `me`.`email_address` AS `email`, 
                   `me`.`email_address` AS `mail`, 
                   `m`.`password`, 
                   `m`.`roleId`, 
                   `m`.`firstname`, 
                   `m`.`lastname`, 
                   `m`.`profile_image_url`, 
                   `m`.`created_at`, 
                   `m`.`changed_at`, 
                   `m`.`source_id`, 
                   `m`.`biography`,
                   1 AS `mail_checked`,
                   `m`.`is_active`,
                   `m`.`is_deleted`,
                   `m`.`password_type`
            FROM `member` AS `m`
            LEFT JOIN `member_email` AS `me` ON `me`.`email_member_id` = `m`.`member_id` AND `me`.`email_primary` = 1
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE `m`.`member_id` = :memberId
              AND `m`.`is_active` = 1 
              AND `m`.`is_deleted` = 0 
              AND `me`.`email_checked` IS NOT NULL 
              AND `me`.`email_deleted` = 0
              AND LOCATE('_double', `m`.username) = 0 
              AND LOCATE('_double', `me`.`email_address`) = 0
            ORDER BY `m`.`member_id` ASC
            # LIMIT 200
        ";

        $result = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql, array('memberId' => $member_id));

        if (count($result) == 0) {
            throw new Zend_Exception('can not find user data for member_id');
        }

        return $result;
    }

    /**
     * @param array $member
     *
     * @return bool
     * @throws Zend_Exception
     */
    private function isValidUsername($member)
    {
        // only usernames which are valid in github/gitlab
        $usernameValidChars = new Local_Validate_UsernameValid();

        return $usernameValidChars->isValid($member['username']);
    }

    /**
     * @param $member
     *
     * @throws Zend_Exception
     */
    private function exportMember($member)
    {
        $this->export2OpenCode($member);
        $this->export2Ldap($member);
        $this->export2OpenId($member);
    }

    /**
     * @param $member
     *
     * @throws Zend_Exception
     */
    private function export2OpenCode($member)
    {
        try {
            $modelOpenCode = new Default_Model_Ocs_Gitlab(Zend_Registry::get('config')->settings->server->opencode);
            $modelOpenCode->createUserFromArray($member);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    /**
     * @param array $member
     *
     * @throws Zend_Exception
     */
    private function export2Ldap($member)
    {
        try {
            $modelOcsIdent = new Default_Model_Ocs_Ldap();
            $modelOcsIdent->createUserFromArray($member);
        } catch (Zend_Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        $errors = $modelOcsIdent->getMessages();
        Zend_Registry::get('logger')->info(print_r($errors, true));
    }

    private function export2OpenId($member)
    {
        $error = '';
        $id_server = new Default_Model_Ocs_OAuth();
        try {
            $id_server->createUserFromArray($member, true); // try create
            return;
        } catch (Exception $e) {
            $error = $e->getMessage();
            $this->logErrorMsg($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        if (strpos($error, 'duplicate') !== false) {
            try {
                $id_server->createUserFromArray($member);
            } catch (Zend_Exception $e) {
                $this->logErrorMsg($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        }
    }

    /**
     * @param string $errMsg
     */
    private function logErrorMsg($errMsg)
    {
        try {
            Zend_Registry::get('logger')->err($errMsg . PHP_EOL);
        } catch (Zend_Exception $e) {
            error_log(__METHOD__ . ' - ' . $e->getMessage());
        }
    }

}