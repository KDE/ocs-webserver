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
class Backend_CmatrixController extends Local_Controller_Action_CliAbstract
{

    const filename = "member";
    const filename_errors = "member_error";

    protected $logfile;
    protected $errorlogfile;
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
        $this->config = Zend_Registry::get('config')->settings->server->chat;
        $this->log = new Local_Log_File('matrix', self::filename);
        $this->_helper->viewRenderer->setNoRender(false);
    }


    /**
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function runAction()
    {
        ini_set('memory_limit', '1024M');

        $force = (boolean)$this->getParam('force', false);
        $method = $this->getParam('method', 'create');

        $this->log->info("--------------" . PHP_EOL . "METHOD: {$method}" . PHP_EOL);

        if ('create' == $method) {
            if ($this->hasParam('member_id')) {
                $memberId = $this->getParam('member_id');
                $operator = $this->getParam('op', null);
                $members = $this->getMemberList($memberId, $operator);
            } else {
                $members = $this->getMemberList();
            }
            $this->exportMembers($members, $force);

            return;
        }
        if ('avatar' == $method) {
            $members = $this->getNewChatUser();
            $this->updateAvatar($members);

            return;
        }
        if ('update' == $method) {
            //$this->updateMembers($members);
            echo "not implemented";

            return;
        }
        if ('validate' == $method) {
            //$this->validateMembers($members);
            echo "not implemented";

            return;
        }
    }

    /**
     * @param null   $member_id
     * @param string $operator
     *
     * @return Zend_Db_Statement_Interface
     * @throws Zend_Db_Statement_Exception
     */
    private function getMemberList($member_id = null, $operator = "=")
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
        if (isset($member_id)) {
            $filter = " AND `m`.`member_id` {$operator} " . $member_id;
        }

        $sql = "
            SELECT `mei`.`external_id`,`m`.`member_id`, `m`.`username`, `me`.`email_address`, `m`.`password`, `m`.`roleId`, `m`.`firstname`, `m`.`lastname`, `m`.`profile_image_url`, `m`.`created_at`, `m`.`changed_at`, `m`.`source_id`, `m`.`biography`, `me`.`email_address` AS `mail`, IF(ISNULL(`me`.`email_checked`),0,1) AS `mail_checked`, `m`.`password_type`, `m`.`is_active`, `m`.`is_deleted`
            FROM `member` AS `m`
            LEFT JOIN `member_email` AS `me` ON `me`.`email_member_id` = `m`.`member_id` AND `me`.`email_primary` = 1
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE `m`.`is_active` = 1 
              AND `m`.`is_deleted` = 0 
              AND `me`.`email_checked` IS NOT NULL 
              AND `me`.`email_deleted` = 0
              AND LOCATE('_deactivated', `m`.`username`) = 0 
              AND LOCATE('_deactivated', `me`.`email_address`) = 0
            " . $filter . "
            ORDER BY `m`.`member_id` ASC
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
        // only usernames which are valid in github/gitlab
        $usernameValidChars = new Local_Validate_UsernameValid();
        $emailValidate = new Zend_Validate_EmailAddress();
        $modelOcs = new Default_Model_Ocs_Matrix($this->config);

        while ($member = $members->fetch()) {
            $this->log->info("process " . Zend_Json::encode($member));

            //if (false === $usernameValidChars->isValid($member['username'])) {
            //    file_put_contents($this->errorlogfile, print_r($member, true) . "user name validation error" . "\n\n", FILE_APPEND);
            //    continue;
            //}
            if (false === $emailValidate->isValid($member["email_address"])) {
                $this->log->info("messages [\"email address validation error\"] ");
                continue;
            }
            try {
                //Export User, if he not exists
                $modelOcs->createUserFromArray($member, $force);
            } catch (Exception $e) {
                $this->log->info($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            $messages = $modelOcs->getMessages();
            $this->log->info("messages " . Zend_Json::encode($messages));
        }

        return true;
    }

    private function getNewChatUser()
    {
        $home_server = $this->config->home_server;

        $sql = "
            SELECT `user_id`, `username`, `profile_image_url`, `member_id`
            FROM `member_matrix_data`
            JOIN `member` ON `member_matrix_data`.`user_id` = concat('@',lower(`member`.`username`),':','{$home_server}')
            WHERE `member_matrix_data`.`is_imported` = 0 AND `member`.`is_active` = 1
        ";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql);

        $this->log->info("Load : " . $result->rowCount() . " members...");

        return $result;
    }

    /**
     * @param Zend_Db_Statement_Interface $members
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    private function updateAvatar($members)
    {
        $model = new Default_Model_Ocs_Matrix($this->config);

        while ($member = $members->fetch()) {
            $this->log->info("process " . Zend_Json::encode($member));
            try {
                $successful = $model->setAvatarFromArray($member);
                if ($successful) {
                    $this->setAvatarUpdated($member['user_id']);
                }
            } catch (Exception $e) {
                $this->log->info($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            $messages = $model->getMessages();
            $this->log->info("messages " . Zend_Json::encode($messages));
        }

        return true;
    }

    private function setAvatarUpdated($user_id)
    {
        $sql = "
            UPDATE `member_matrix_data` 
            SET `is_imported` = 1, `imported_at` = NOW()
            WHERE `user_id` = '{$user_id}'
        ";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql);

        return $result;
    }

}