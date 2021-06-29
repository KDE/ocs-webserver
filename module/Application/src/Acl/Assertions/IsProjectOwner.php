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
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

/**
 * Class IsProjectOwner
 *
 * @package Application\Acl\Assertions
 */
class IsProjectOwner implements AssertionInterface
{
    const NO_OWNER = false;
    private $requested_project_id;

    /**
     * IsProjectOwner constructor.
     *
     * @param int $requested_project_id
     */
    public function __construct($requested_project_id)
    {
        $this->requested_project_id = $requested_project_id;
    }

    /**
     * @param Acl                    $acl
     * @param RoleInterface|null     $role
     * @param ResourceInterface|null $resource
     * @param null                   $privilege
     *
     * @return bool
     */
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        /** @var CurrentUser $current_user */
        $current_user = $GLOBALS['ocs_user'];

        if (false === $current_user->hasIdentity()) {
            return self::NO_OWNER;
        }

        $user_projects = $current_user->projects;

        foreach ($user_projects as $projectArray) {
            if ($projectArray['project_id'] == $this->requested_project_id) {
                return true;
            }
        }

        return false;
    }

}