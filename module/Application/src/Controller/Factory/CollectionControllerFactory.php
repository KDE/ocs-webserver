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

use Application\Controller\CollectionController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class CollectionControllerFactory
 *
 * @package Application\Controller\Factory
 */
class CollectionControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return CollectionController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
        $config = $container->get('Config');
        $request = $container->get('Request');
        $projectRepository = $container->get('Application\Model\Repository\ProjectRepository');
        $infoService = $container->get('Application\Model\Service\InfoService');
        $tagService = $container->get('Application\Model\Service\TagService');
        $memberService = $container->get('Application\Model\Service\MemberService');
        $projectService = $container->get('Application\Model\Service\ProjectService');
        $activityLog = $container->get('Application\Model\Repository\ActivityLogRepository');

        $collectionProjectsRepository = $container->get('Application\Model\Repository\CollectionProjectsRepository');
        $searchService = $container->get('Application\Model\Service\SolrService');
        $collectionService = $container->get('Application\Model\Service\CollectionService');
        $pploadRepository = $container->get('Application\Model\Repository\PploadFilesRepository');
        $statPageViewsRepository = $container->get('Application\Model\Repository\StatPageViewsRepository');
        $imageRepository = $container->get('Application\Model\Repository\ImageRepository');
        $projectCategoryRepository = $container->get('Application\Model\Repository\ProjectCategoryRepository');

        return new CollectionController($dbAdapter, $config, $request, $projectRepository, $infoService, $tagService,
            $memberService, $projectService, $activityLog, $collectionProjectsRepository, $searchService,
            $collectionService, $pploadRepository, $statPageViewsRepository, $imageRepository,
            $projectCategoryRepository,
            $container->get('Application\Model\Service\SectionService')       
        );
    }
}
