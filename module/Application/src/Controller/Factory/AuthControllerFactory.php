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

namespace Application\Controller\Factory;

use Application\Controller\AuthController;
use Application\Model\Repository\AuthenticationRepository;
use Application\Model\Service\AuthManager;
use Application\Model\Service\InfoService;
use Application\Model\Service\RegisterManager;
use Application\Model\Service\ReviewProfileDataService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class AuthControllerFactory
 * This is the factory for AuthController. Its purpose is to instantiate the controller
 * and inject dependencies into its constructor.
 *
 * @package Application\Controller\Factory
 */
class AuthControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authRepository = $container->get(AuthenticationRepository::class);
        $authManager = $container->get(AuthManager::class);
        $registerManager = $container->get(RegisterManager::class);
        $reviewProfileService = $container->get(ReviewProfileDataService::class);
        $infoService = $container->get(InfoService::class);

        return new AuthController($authRepository, $authManager, $registerManager, $reviewProfileService, $infoService);
    }
}