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
class Backend_CgitlabController extends Local_Controller_Action_CliAbstract
{

    const filename = "members";
    const filename_errors = "members";

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
        $this->config = Zend_Registry::get('config')->settings->server->opencode;
        $this->log = new Local_Log_File($this->config->user_logfilename, self::filename);
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

        $this->log->info("METHOD: {$method}\n--------------\n");

        if ($this->hasParam('member_id')) {
            $memberId = (int)$this->getParam('member_id');
            $operator = $this->getParam('op', null);
            $members = $this->getMemberList($memberId, $operator);
        } else {
            $members = $this->getMemberList();
        }

        if ('create' == $method) {
            $this->exportMembers($members, $force);

            return;
        }
        if ('update' == $method) {
            //$this->updateMembers($members);
            echo "not implemented";

            return;
        }
        if ('validate' == $method) {
            $this->validateMembers($members, $force);

            return;
        }
        if ('block' == $method) {
            $this->blockMember($members);

            return;
        }
        if ('unblock' == $method) {
            $this->unblockMember($members);

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
            SELECT `mei`.`external_id`,`m`.`member_id`, `m`.`username`, `me`.`email_address`, `m`.`password`, `m`.`roleId`, `m`.`firstname`, `m`.`lastname`, `m`.`profile_image_url`, `m`.`created_at`, `m`.`changed_at`, `m`.`source_id`, `m`.`biography`, `m`.`is_active`, `m`.`is_deleted`
            FROM `member` AS `m`
            LEFT JOIN `member_email` AS `me` ON `me`.`email_member_id` = `m`.`member_id` AND `me`.`email_primary` = 1
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE `m`.`is_active` = 1 
              AND `m`.`is_deleted` = 0 
              AND `me`.`email_checked` IS NOT NULL 
              AND `me`.`email_deleted` = 0
              AND LOCATE('_double', `m`.`username`) = 0 
              AND LOCATE('_double', `me`.`email_address`) = 0
            " . $filter . "
            ORDER BY `m`.`member_id` DESC
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
        $modelOpenCode = new Default_Model_Ocs_Gitlab($this->config);

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
                $modelOpenCode->createUserFromArray($member, $force);
            } catch (Exception $e) {
                $this->log->info($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            $messages = $modelOpenCode->getMessages();
            $this->log->info("messages " . Zend_Json::encode($messages));
        }

        return true;
    }

    /**
     * @param Zend_Db_Statement_Interface $members
     *
     * @param bool                        $force
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    private function validateMembers($members, $force)
    {
        $modelSubSystem = new Default_Model_Ocs_Gitlab($this->config);

        while ($member = $members->fetch()) {
            $modelSubSystem->resetMessages();
            $this->log->info("process " . Zend_Json::encode($member));
            try {
                $userSubsystem = $modelSubSystem->getUser($member['external_id'], $member['username']);
                if (false === $userSubsystem) {
                    $this->log->info('Fail : user not exist (' . $member['member_id'] . ', ' . $member['username'] . ') ' . Zend_Json::encode($modelSubSystem->getMessages()));
                    if ($force) {
                        $modelSubSystem->createUserFromArray($member, true);
                        $this->log->info("Message : " . Zend_Json::encode($modelSubSystem->getMessages()));
                    }

                    continue;
                }

                $result = $modelSubSystem->validateUserData($member, $userSubsystem);
                if (false === empty($result)) {
                    $this->log->info('Fail : ' . implode(" ", $result));
                    if ($force) {
                        $modelSubSystem->createUserFromArray($member, true);
                    }
                } else {
                    $this->log->info('Success');
                }
            } catch (Exception $e) {
                $this->log->info($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            $messages = $modelSubSystem->getMessages();
            if (false === empty($messages)) {
                $this->log->info("Message : " . Zend_Json::encode($messages));
            }
        }

        return true;
    }

    private function blockMember($members)
    {
        $modelSubSystem = new Default_Model_Ocs_Gitlab($this->config);

        while ($member = $members->fetch()) {
            $result = $modelSubSystem->blockUser($member);
            if (false == $result) {
                $this->log->info('Fail');
            }
            $result = $modelSubSystem->blockUserProjects($member);
            if (false == $result) {
                $this->log->info('Fail');
            }
            $messages = $modelSubSystem->getMessages();
            if (false === empty($messages)) {
                $this->log->info("Message : " . Zend_Json::encode($messages));
            }
        }
    }

    private function unblockMember($members)
    {
        $modelSubSystem = new Default_Model_Ocs_Gitlab($this->config);

        while ($member = $members->fetch()) {
            $result = $modelSubSystem->unblockUser($member);
            if (false == $result) {
                $this->log->info('Fail');
            }
            $result = $modelSubSystem->unblockUserProjects($member);
            if (false == $result) {
                $this->log->info('Fail');
            }
            $messages = $modelSubSystem->getMessages();
            if (false === empty($messages)) {
                $this->log->info("Message : " . Zend_Json::encode($messages));
            }
        }
    }

}