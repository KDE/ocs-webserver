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


use Application\Acl\Resources;
use Application\Acl\Roles;
use Application\Acl\Rules;
use Application\Model\Service\AclService;
use Interop\Container\ContainerInterface;
use Laminas\Permissions\Acl\Acl;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AclServiceFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $route_params = array_merge(
            $container->get('Application')->getMvcEvent()->getRouteMatch()->getParams(), $_GET, $_POST
        );
        $acl = new Acl();

        $resources = new Resources($acl);
        $roles = new Roles($resources->getResources());
        $rules = new Rules($roles->getRoles(), $route_params);

        return new AclService($rules->getRules());
    }

}