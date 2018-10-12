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
 * Created: 12.10.2018
 */

class Backend_Model_Group
{

    public function processEventData($data)
    {
        switch ($data['event_name']) {
            case 'group_create':
                $this->addGroup($data['name'], $data['group_id'], $data['full_path']);
                $this->addGroup2Ldap($data['name'], $data['group_id'], $data['full_path']);
                break;
            case 'user_add_to_group':
                $this->addUser($data['group_name'], $data['group_id'], $data['user_id'], $data['user_name'], $data['user_email'], $data['group_access']);
                break;
            default: Zend_Registry::get('logger')->info(__METHOD__ . ' - unhandled git event: ' . json_encode($data));
        }
    }

    public function addGroup($group_name, $group_id, $full_path)
    {
        $sql = "INSERT INTO git_group (`group_name`, `group_id`, `group_full_path`) VALUES (:groupName, :groupId, :fullPath)";

        $db = Zend_Db_Table::getDefaultAdapter();

        $statement = $db->query($sql, array('groupName'=>$group_name, 'groupId'=>$group_id, 'group_full_path'=>$full_path));

        return $statement->rowCount();
    }

    public function addUser($group_name, $group_id, $user_id, $user_name, $user_email, $group_access)
    {
        $sql = "INSERT INTO git_group_user (`group_name`, `group_id`, `user_id`, `user_name`, `user_email`, `group_access`) 
                       VALUES (:groupName, :groupId, :userId, :userName, :userEmail, :groupAccess)";

        $db = Zend_Db_Table::getDefaultAdapter();

        $statement = $db->query($sql, array('groupName'=>$group_name, 'groupId'=>$group_id, 'userId'=>$user_id, 'userName'=>$user_name, 'userEmail'=>$user_email, 'groupAccess'=>$group_access));

        return $statement->rowCount();
    }

    public function addGroup2Ldap($name, $group_id, $full_path)
    {
        $modelLdap = new Default_Model_Ocs_Ident();
        $modelLdap->createGroup($name, $group_id, $full_path);
    }

}