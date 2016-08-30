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
class Default_Plugin_Acl_IsOwnerAssertion implements Zend_Acl_Assert_Interface
{

    const NO_OWNER = false;

    /**
     * Returns true if and only if the assertion conditions are met.
     *
     * This method get passed the ACL, Role, Resource, and privilege to which the authorization query applies.
     * If the $role, $resource, or $privilege parameters are null, it means that the query applies to all Roles,
     * Resources or privileges.
     *
     * @param  Zend_Acl $acl
     * @param  Zend_Acl_Role_Interface $role
     * @param  Zend_Acl_Resource_Interface $resource
     * @param  string $privilege
     * @return boolean
     */
    public function assert(
        Zend_Acl $acl,
        Zend_Acl_Role_Interface $role = null,
        Zend_Acl_Resource_Interface $resource = null,
        $privilege = null
    ) {
        $auth = Zend_Auth::getInstance();

        if (!$auth->hasIdentity()) {
            return self::NO_OWNER;
        }

        $identity = $auth->getIdentity();

        $member_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('member_id');

        $result = ($member_id == $identity->member_id);
        return $result;
    }

}
