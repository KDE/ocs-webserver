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

namespace Application\Acl\Assertions;


use Application\Model\Entity\CurrentUser;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

class IsUserIdentical implements \Laminas\Permissions\Acl\Assertion\AssertionInterface
{

    private $requested_user;

    public function __construct($requested_user)
    {
        $this->requested_user = $requested_user;
    }

    /**
     * @inheritDoc
     */
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        /** @var CurrentUser $current_user */
        $current_user = $GLOBALS['ocs_user'];

        if (false === $current_user->hasIdentity()) {
            return false;
        }

        if ($current_user->isAdmin()) {
            return true;
        }

        if ($current_user->username == $this->requested_user) {
            return true;
        }

        return false;
    }
}