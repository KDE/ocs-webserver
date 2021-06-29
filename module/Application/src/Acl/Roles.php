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

namespace Application\Acl;

use Laminas\Permissions\Acl\Acl;

/**
 * Class Roles
 *
 * @package Application\Acl
 */
class Roles
{
    const ROLENAME_GUEST = 'guest';
    const ROLENAME_COOKIEUSER = 'cookieuser';
    const ROLENAME_FEUSER = 'feuser';
    const ROLENAME_MODERATOR = 'moderator';
    const ROLENAME_STAFF = 'staff';
    const ROLENAME_ADMIN = 'admin';
    const ROLENAME_SYSUSER = 'sysuser';
    /**
     * @var Acl
     */
    private $acl;

    /**
     * Roles constructor.
     *
     * @param Acl $acl
     */
    public function __construct(Acl $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @return Acl
     */
    public function getRoles()
    {
        $this->acl->addRole(self::ROLENAME_GUEST);
        $this->acl->addRole(self::ROLENAME_COOKIEUSER, self::ROLENAME_GUEST);
        $this->acl->addRole(self::ROLENAME_FEUSER, self::ROLENAME_COOKIEUSER);
        $this->acl->addRole(self::ROLENAME_MODERATOR, self::ROLENAME_FEUSER);
        $this->acl->addRole(self::ROLENAME_STAFF, self::ROLENAME_FEUSER);
        $this->acl->addRole(self::ROLENAME_ADMIN);

        return $this->acl;
    }
}