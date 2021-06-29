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


use Application\Model\Repository\AuthenticationRepository;
use Application\Model\Service\AuthAdapter;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * This is the factory class for AuthAdapter service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class AuthAdapterFactory implements FactoryInterface
{
    /**
     * This method creates the AuthAdapter service and returns its instance.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return AuthAdapter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // Get entity member from Service Manager.
        $entityManager = $container->get(AuthenticationRepository::class);

        // Create the AuthAdapter and inject dependency to its constructor.
        return new AuthAdapter($entityManager);
    }
}