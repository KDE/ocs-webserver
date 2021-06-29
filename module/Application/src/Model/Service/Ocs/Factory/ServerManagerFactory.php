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

namespace Application\Model\Service\Ocs\Factory;


use Application\Model\Service\Ocs\Forum;
use Application\Model\Service\Ocs\Gitlab;
use Application\Model\Service\Ocs\Ldap;
use Application\Model\Service\Ocs\Mastodon;
use Application\Model\Service\Ocs\Matrix;
use Application\Model\Service\Ocs\OAuth;
use Application\Model\Service\Ocs\ServerManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ServerManagerFactory implements FactoryInterface
{

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ServerManager(
            $container->get(Forum::class), $container->get(Gitlab::class), $container->get(Ldap::class), $container->get(Mastodon::class), $container->get(Matrix::class), $container->get(OAuth::class)
        );
    }
}