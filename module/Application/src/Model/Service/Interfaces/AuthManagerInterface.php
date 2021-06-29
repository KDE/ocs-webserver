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

namespace Application\Model\Service\Interfaces;


use Exception;
use Laminas\Authentication\Result;

/**
 * The AuthManager service is responsible for user's login/logout and simple access
 * filtering. The access filtering feature checks whether the current visitor
 * is allowed to see the given page or not.
 */
interface AuthManagerInterface
{
    /**
     * Performs a login attempt. If $rememberMe argument is true, it forces the session
     * to last for one month (otherwise the session expires on one hour).
     *
     * @param $identity
     * @param $password
     * @param $rememberMe
     *
     * @return Result
     * @throws Exception
     */
    public function login($identity, $password, $rememberMe);

    /**
     * Performs user logout.
     *
     * @throws Exception
     */
    public function logout();

    /**
     * This is a simple access control filter. It is able to restrict unauthorized
     * users to visit certain pages.
     *
     * This method uses the 'access_filter' key in the config file and determines
     * whether the current visitor is allowed to access the given controller action
     * or not. It returns true if allowed; otherwise false.
     *
     * @param $controllerName
     * @param $actionName
     *
     * @return bool
     * @throws Exception
     */
    public function filterAccess($controllerName, $actionName);
}