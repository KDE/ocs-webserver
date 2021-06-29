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

use Application\Controller\DlController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class DlControllerFactory
 *
 * @package Application\Controller\Factory
 */
class DlControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return DlController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
        $config = $container->get('Config');
        $request = $container->get('Request');
        $infoService = $container->get('Application\Model\Service\InfoService');
        $projectCategoryRepository = $container->get('Application\Model\Repository\ProjectCategoryRepository');
        $projectRepository = $container->get('Application\Model\Repository\ProjectRepository');
        $sectionService = $container->get('Application\Model\Service\SectionService');
        $pploadService = $container->get('Application\Model\Service\PploadService');
        $memberDlHistory = $container->get('Application\Model\Repository\MemberDownloadHistoryRepository');

        return new DlController($dbAdapter, $config, $request, $infoService, $projectCategoryRepository,
            $projectRepository, $sectionService, $pploadService, $memberDlHistory);
    }
}
