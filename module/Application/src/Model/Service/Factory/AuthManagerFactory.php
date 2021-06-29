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

namespace Application\Model\Service\Factory;


use Application\Model\Service\AuthManager;
use Application\Model\Service\Interfaces\AuthManagerInterface;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Session\SessionManager;

/**
 * This is the factory class for AuthManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class AuthManagerFactory implements FactoryInterface
{
    /**
     * This method creates the AuthManager service and returns its instance.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return AuthManagerInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // Instantiate dependencies.
        $authenticationService = $container->get(AuthenticationService::class);
        $sessionManager = $container->get(SessionManager::class);

        // Get contents of 'access_filter' config key (the AuthManager service
        // will use this data to determine whether to allow currently logged in user
        // to execute the controller action or not.
        $config = $container->get('Config');
        if (isset($config['access_filter'])) {
            $config = $config['access_filter'];
        } else {
            $config = [];
        }

        // Instantiate the AuthManager service and inject dependencies to its constructor.
        return new AuthManager($authenticationService, $sessionManager, $config);
    }
}
