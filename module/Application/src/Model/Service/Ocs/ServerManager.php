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


use Exception;
use Laminas\Log\Logger;

class ServerManager
{
    /**
     * @var Forum
     */
    private $forum;
    /**
     * @var Gitlab
     */
    private $gitlab;
    /**
     * @var Ldap
     */
    private $ldap;
    /**
     * @var Mastodon
     */
    private $mastodon;
    /**
     * @var Matrix
     */
    private $matrix;
    /**
     * @var OAuth
     */
    private $oauth;

    /**
     * ServerManager constructor.
     *
     * @param Forum    $forum
     * @param Gitlab   $gitlab
     * @param Ldap     $ldap
     * @param Mastodon $mastodon
     * @param Matrix   $matrix
     * @param OAuth    $oauth
     */
    public function __construct(
        Forum $forum,
        Gitlab $gitlab,
        Ldap $ldap,
        Mastodon $mastodon,
        Matrix $matrix,
        OAuth $oauth
    ) {
        $this->forum = $forum;
        $this->gitlab = $gitlab;
        $this->ldap = $ldap;
        $this->mastodon = $mastodon;
        $this->matrix = $matrix;
        $this->oauth = $oauth;
    }

    public function delete($member_id)
    {
        /** @var Logger $logger */
        $logger = $GLOBALS['ocs_log'];
        try {
            $this->oauth->deleteUser($member_id);
            $logger->debug(__METHOD__ . ' - oauth : ' . var_export($this->oauth->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $this->ldap->deleteUser($member_id);
            $logger->debug(__METHOD__ . ' - ldap : ' . var_export($this->ldap->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $this->gitlab->blockUser($member_id);
            $this->gitlab->blockUserProjects($member_id);
            $logger->debug(__METHOD__ . ' - opencode : ' . var_export($this->gitlab->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $this->forum->blockUser($member_id);
            $this->forum->blockUserPosts($member_id);
            $logger->debug(__METHOD__ . ' - forum : ' . var_export($this->forum->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    public function activate($member_id)
    {
        /** @var Logger $logger */
        $logger = $GLOBALS['ocs_log'];
        try {
            $this->oauth->updateUser($member_id);
            $logger->debug(__METHOD__ . ' - oauth : ' . var_export($this->oauth->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $this->ldap->createUser($member_id);
            $logger->debug(__METHOD__ . ' - ldap : ' . var_export($this->ldap->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $this->gitlab->unblockUser($member_id);
            $this->gitlab->unblockUserProjects($member_id);
            $logger->debug(__METHOD__ . ' - opencode : ' . var_export($this->gitlab->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $this->forum->unblockUser($member_id);
            $this->forum->unblockUserPosts($member_id);
            $logger->debug(__METHOD__ . ' - forum : ' . var_export($this->forum->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    public function insert(array $record)
    {
        /** @var Logger $logger */
        $logger = $GLOBALS['ocs_log'];

        $modelOpenCode = $this->gitlab;
        $modelOpenCode->createUserFromArray($record, true);
        $logger->debug(__METHOD__ . ' - opencode : ' . var_export($modelOpenCode->getMessages(), true));

        $modelIdent = $this->ldap;
        $modelIdent->addUserFromArray($record, true);
        $logger->debug(__METHOD__ . ' - ldap : ' . var_export($modelIdent->getMessages(), true));

        $modelId = $this->oauth;
        $modelId->createUserFromArray($record, true);
        $logger->debug(__METHOD__ . ' - oauth : ' . var_export($modelId->getMessages(), true));

        $modelForum = $this->forum;
        $modelForum->createUserFromArray($record, true);
        $logger->debug(__METHOD__ . ' - forum : ' . var_export($modelForum->getMessages(), true));
    }
}