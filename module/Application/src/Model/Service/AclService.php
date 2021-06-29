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

namespace Application\Model\Service;


use Application\Acl\Roles;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Exception\InvalidArgumentException;

class AclService extends BaseService
{
    const ACCESS_GRANTED = 10;
    const AUTH_REQUIRED = 20;
    const ACCESS_DENIED = 0;

    /**
     * @var Acl
     */
    public $acl;
    private $params;

    public function __construct(Acl $acl)
    {
        $this->acl = $acl;
    }

    public function setRequestedParams($params)
    {
        $this->params = $params;
    }

    public function isGranted(\Application\Model\Entity\CurrentUser $user, $controllerName, $action)
    {
        $role = Roles::ROLENAME_GUEST;
        if ($user->hasIdentity()) {
            $role = $user->roleName;
        }

        try {
            if ($this->acl->isAllowed($role, $controllerName, $action)) {
                return self::ACCESS_GRANTED;
            }
        } catch (InvalidArgumentException $exception) {
            $GLOBALS['ocs_log']->warn($exception->getMessage() . ' => ' . $exception->getFile());

        }

        // authorization needed
        if (false == $user->hasIdentity()) {
            return self::AUTH_REQUIRED;
        }

        return self::ACCESS_DENIED;
    }

}