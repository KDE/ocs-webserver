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
 * Created: 19.06.2018
 */
class Default_Model_OcsOpenCode
{
    protected $config;

    /**
     * @inheritDoc
     */
    public function __construct($config = null)
    {
        if (isset($config)) {
            $this->config = $config;
        } else {
            $this->config = Zend_Registry::get('config')->settings->opencode_server;
        }
    }

    /**
     * @param int $member_id
     *
     * @return bool
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Exception
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function createUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $openCode = $this->createDbConnection();
        $user = $this->getUserData($member_id);

        return $this->opencodeAddUser($user, $openCode);
    }

    /**
     * @return Zend_Db_Adapter_Pdo_Mysql
     * @throws Zend_Db_Exception
     */
    private function createDbConnection()
    {
        $db = Zend_Db::factory($this->config->db->adapter, $this->config->db->params);

        return $db;
    }

    /**
     * @param $member_id
     *
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    private function getUserData($member_id)
    {
        $modelMember = new Default_Model_Member();
        $member = $modelMember->fetchMemberData($member_id)->toArray();

        $modelExternalId = new Default_Model_DbTable_MemberExternalId();
        $externalId = $modelExternalId->fetchRow(array("member_id = ?" => $member['member_id']));
        if (count($externalId->toArray() > 0)) {
            $member['external_id'] = $externalId->external_id;
        }

        return $member;
    }

    /**
     * @param array                     $user
     * @param Zend_Db_Adapter_Pdo_Mysql $db
     *
     * @return bool
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    private function opencodeAddUser($user, $db)
    {
        $is_admin = $user['roleId'] == 100 ? 1 : 0;

        $stmt = $db->query("call add_ocs_user(?,?,?,?,?,?,?,?)", array(
            $user['member_id'],
            $user['username'],
            $user['mail'],
            $user['password'],
            $is_admin,
            $user['created_at'],
            $user['changed_at'],
            $user['external_id']
        ));

        $rowset = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (array_key_exists("error_msg", $rowset[0])) {
            throw new Zend_Exception($rowset[0]['error_msg']);
        }

        return true;
    }

    public function deactivateLoginForUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $openCode = $this->createDbConnection();
        $user = $this->getUserData($member_id);

        return $this->opencodeDeactivateUser($user, $openCode);
    }

    private function opencodeDeactivateUser($user, $db)
    {
        $stmt = $db->query("call deactivate_ocs_user(?)", array($user['member_id']));

        $rowset = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (array_key_exists("error_msg", $rowset[0])) {
            throw new Zend_Exception($rowset[0]['error_msg']);
        }

        return true;
    }

    public function activeLoginForUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $openCode = $this->createDbConnection();
        $user = $this->getUserData($member_id);

        return $this->opencodeActivateUser($user, $openCode);
    }

    private function opencodeActivateUser($user, $db)
    {
        $stmt = $db->query("call activate_ocs_user(?)", array($user['member_id']));

        $rowset = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (array_key_exists("error_msg", $rowset[0])) {
            throw new Zend_Exception($rowset[0]['error_msg']);
        }

        return true;
    }

    public function updateUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $openCode = $this->createDbConnection();
        $user = $this->getUserData($member_id);

        return $this->opencodeUpdateUser($user, $openCode);
    }

    private function opencodeUpdateUser($user, $db)
    {
        $is_admin = $user['roleId'] == 100 ? 1 : 0;

        $stmt = $db->query("call update_ocs_user(?,?,?,?,?,?)", array(
            $user['member_id'],
            $user['username'],
            $user['mail'],
            $user['password'],
            $is_admin,
            $user['external_id']
        ));

        $rowset = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (array_key_exists("error_msg", $rowset[0])) {
            throw new Zend_Exception($rowset[0]['error_msg']);
        }

        return true;
    }

    public function updateUserMail($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $openCode = $this->createDbConnection();
        $user = $this->getUserData($member_id);

        return $this->opencodeUpdateUserMail($user, $openCode);
    }

    private function opencodeUpdateUserMail($user, $db)
    {
        $stmt = $db->query("call update_mail_ocs_user(?,?)", array(
            $user['member_id'],
            $user['mail']
        ));

        $rowset = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (array_key_exists("error_msg", $rowset[0])) {
            throw new Zend_Exception($rowset[0]['error_msg']);
        }

        return true;
    }

}