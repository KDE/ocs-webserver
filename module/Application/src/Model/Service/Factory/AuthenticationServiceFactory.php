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


use Application\Model\Service\AuthAdapter;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\Session as SessionStorage;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Session\SessionManager;

/**
 * The factory responsible for creating of authentication service.
 */
class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * This method creates the Laminas\Authentication\AuthenticationService service
     * and returns its instance.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $sessionManager = $container->get(SessionManager::class);
        $authStorage = new SessionStorage('Zend_Auth', 'session', $sessionManager);
        $authAdapter = $container->get(AuthAdapter::class);

        // Create the service and inject dependencies into its constructor.
        return new AuthenticationService($authStorage, $authAdapter);
    }
}