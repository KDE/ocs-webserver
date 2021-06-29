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


use Application\Model\Service\Ocs\HttpTransport\OAuthServer;
use Application\Model\Service\Ocs\OAuth;
use Interop\Container\ContainerInterface;
use Laminas\Config\Config;
use Laminas\ServiceManager\Factory\FactoryInterface;

class OAuthFactory implements FactoryInterface
{

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $config_OAuth = new Config($config['ocs_config']['settings']['server']['oauth']);
        $httpTransport = $container->get(OAuthServer::class);

        return new OAuth($config_OAuth, $httpTransport, $container->get('Application\Model\Repository\MemberRepository'));
    }
}