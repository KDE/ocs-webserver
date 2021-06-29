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

namespace Backend\Controller\Factory;

use Application\Model\Service\Ocs\ServerManager;
use Backend\Controller\UserController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class UserControllerFactory implements FactoryInterface
{

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return UserController(
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        return new UserController(
            $container->get('Application\Model\Repository\MemberRepository'), $container->get('Application\Model\Service\MemberService'), $container->get('ViewRenderer'), $container->get('Application\Model\Repository\PaypalValidStatusRepository'), $container->get(ServerManager::class)
        );

    }
}