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
 **/
class Backend_View_Helper_UserRole extends Zend_View_Helper_Abstract
{

    public function userRole()
    {
        $auth = Zend_Auth::getInstance();
        if (false == $auth->hasIdentity()) {
            return Default_Model_DbTable_MemberRole::ROLE_DEFAULT;
        }
        #Zend_Debug::dump($auth->getStorage());
        $roleId = $auth->getStorage()->read()->roleId;

        $modelUserRoles = new Default_Model_DbTable_MemberRole();
        $roleData = $modelUserRoles->fetchRow('member_role_id = ' . $roleId);
        if (!$roleData || empty($roleData)) {
            return Default_Model_DbTable_MemberRole::ROLE_DEFAULT;
        }
        return $roleData->shortname;
    }

}