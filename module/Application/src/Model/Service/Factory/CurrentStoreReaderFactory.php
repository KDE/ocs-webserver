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

namespace Application\Model\Service\Factory;


use Application\Model\Repository\ConfigStoreRepository;
use Application\Model\Service\CurrentStoreReader;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CurrentStoreReaderFactory implements FactoryInterface
{

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $request = $container->get('Request');
        $routeParams = $container->get('Application')->getMvcEvent()->getRouteMatch();
        $cache = $container->get('Application\Model\Factory\CacheFactory');
        $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
        $storeConfigRepository = new ConfigStoreRepository($dbAdapter, $cache);
        $logger = $container->get('Ocs_Log');
        $templateReader = $container->get('Application\Model\Service\StoreTemplateReader');
        $storeService = $container->get('Application\Model\Service\StoreService');

        return new CurrentStoreReader($request, $routeParams, $logger, $templateReader, $storeService, $storeConfigRepository);
    }

}