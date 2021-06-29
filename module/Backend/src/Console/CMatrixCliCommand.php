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

namespace Backend\Console;

use Application\Model\Service\Ocs\Matrix;
use Exception;
use Laminas\Config\Config;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Json\Encoder;
use Laminas\Log\Logger;
use Laminas\Validator\EmailAddress;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CMatrixCliCommand
 *
 * @package Backend\Console
 */
class CMatrixCliCommand extends Command
{

    // the name of the command (the part after "scripts/console")
    const filename = "member";
    const filename_errors = "member_error";
    protected static $defaultName = 'app:matrix-sync';
    /** @var Config */
    protected $config;

    /** @var Logger */
    protected $_logger;
    protected $db;

    public function __construct()
    {
        parent::__construct();

        $this->initVars();

        $this->db = GlobalAdapterFeature::getStaticAdapter();
    }

    public function initVars()
    {
        //init
        $this->_logger = $GLOBALS['ocs_log'];
        $this->config = $GLOBALS['ocs_config']->settings->server->chat;
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Command to refresh the Avatar Images')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Command to refresh the Avatar Images.')
            ->addArgument('method', InputArgument::REQUIRED, 'Sync-Method, Required')
            ->addArgument('force', InputArgument::OPTIONAL, 'Force Sync, Optional')
            ->addArgument('member_id', InputArgument::OPTIONAL, 'Just this Member, Optional')
            ->addArgument('op', InputArgument::OPTIONAL, 'Operator, Optional');
    }

    /**
     * Run php code as cronjob.
     * I.e.:
     * php scripts/application.php app:matrix-sync avatar
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @see CliInterface::runAction()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initVars();

        ini_set('memory_limit', '1024M');

        $force = $input->getArgument('force');
        $method = $input->getArgument('method');

        $this->_logger->info(__METHOD__ . ":" . __LINE__ . " - METHOD: {$method}");

        if ('create' == $method) {
            if ($input->hasArgument('member_id')) {
                $memberId = $input->getArgument('member_id');
                $operator = $input->getArgument('op');
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
     * @return ResultInterface
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
            $filter = " AND `m`.`member_id` {$operator} " . (int)$member_id;
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
              AND LOCATE('_double', `m`.`username`) = 0 
              AND LOCATE('_double', `me`.`email_address`) = 0
              {$filter}
            ORDER BY `m`.`member_id` ASC
        ";

        $result = $this->db->query($sql)->execute();

        $this->_logger->info("Load : " . $result->count() . " members...");

        return $result;
    }

    /**
     * @param      $members
     *
     * @param bool $force
     *
     * @return bool
     */
    private function exportMembers($members, $force = false)
    {
        // only usernames which are valid in github/gitlab
        $emailValidate = new EmailAddress();
        $modelOcs = new Matrix();

        while ($member = $members->fetch()) {
            $this->_logger->info("process " . Encoder::encode($member));

            if (false === $emailValidate->isValid($member["email_address"])) {
                $this->_logger->info("messages [\"email address validation error\"] ");
                continue;
            }
            try {
                //Export User, if he not exists
                $modelOcs->createUserFromArray($member, $force);
            } catch (Exception $e) {
                $this->_logger->info($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            $messages = $modelOcs->getMessages();
            $this->_logger->info("messages " . Encoder::encode($messages));
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

        $results = $this->db->query($sql)->execute();

        $resultArray = array();

        foreach ($results as $result) {
            $resultArray[] = $result;
        }

        $this->_logger->info("Load : " . count($resultArray) . " members...");

        return $resultArray;
    }

    /**
     * @param $members
     *
     * @return bool
     */
    private function updateAvatar($members)
    {
        $model = new Matrix();

        foreach ($members as $member) {
            $this->_logger->info("process " . Encoder::encode($member));
            try {
                $successful = $model->setAvatarFromArray($member);
                if ($successful) {
                    $this->setAvatarUpdated($member['user_id']);
                }
            } catch (Exception $e) {
                $this->_logger->info($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            $messages = $model->getMessages();
            $this->_logger->info("messages " . Encoder::encode($messages));
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

        $result = $this->db->query($sql)->execute();

        return $result;
    }

}
