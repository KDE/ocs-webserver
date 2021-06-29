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


use Application\Model\Interfaces\AuthenticationRepositoryInterface;
use Application\Model\Service\Interfaces\CurrentUserInterface;
use Exception;
use Laminas\Authentication\AuthenticationService;

/** @deprecated  use AuthManager->getCurrentUser() */
class CurrentUser implements CurrentUserInterface
{
    /**
     * Previously fetched User entity.
     *
     * @var \Application\Model\Entity\CurrentUser
     */
    private static $user = null;
    /**
     * Entity manager.
     *
     * @var AuthenticationRepositoryInterface
     */
    private $repositoryAuthentication;
    /**
     * Authentication service.
     *
     * @var AuthenticationService
     */
    private $authService;

    /**
     * Constructor.
     *
     * @param $entityAuthentication
     * @param $authService
     */
    public function __construct($entityAuthentication, $authService)
    {
        $this->repositoryAuthentication = $entityAuthentication;
        $this->authService = $authService;
    }

    /**
     * Returns the current User or empty object if not logged in.
     *
     * @param bool $useCachedUser If true, the User entity is fetched only on the first call (and cached on subsequent
     *                            calls).
     *
     * @return \Application\Model\Entity\CurrentUser
     * @throws Exception
     */
    public function get($useCachedUser = true)
    {
        // Check if User is already fetched previously.
        if ($useCachedUser && self::$user !== null) {
            return self::$user;
        }

        // Check if user is logged in.
        if ($this->authService->hasIdentity()) {

            // Fetch User entity from database.
            self::$user = $this->repositoryAuthentication->findOneByEmail($this->authService->getIdentity());
            if (self::$user == null) {
                // Oops.. the identity presents in session, but there is no such user in database.
                // We throw an exception, because this is a possible security problem.
                throw new Exception('Not found user with such ID');
            }

            // Return the User entity we found.
            return self::$user;
        }

        // return an empty user object
        return new \Application\Model\Entity\CurrentUser();
    }

}