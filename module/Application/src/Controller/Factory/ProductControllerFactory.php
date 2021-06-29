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

use Application\Controller\ProductController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class ProductControllerFactory
 *
 * @package Application\Controller\Factory
 */
class ProductControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return ProductController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
        $config = $container->get('Config');
        $request = $container->get('Request');
        $infoService = $container->get('Application\Model\Service\InfoService');
        $tagService = $container->get('Application\Model\Service\TagService');
        $tagGroupService = $container->get('Application\Model\Service\TagGroupService');
        $memberService = $container->get('Application\Model\Service\MemberService');
        $projectService = $container->get('Application\Model\Service\ProjectService');
        $gitlab = $container->get('Application\Model\Service\Ocs\Gitlab');
        $activityLog = $container->get('Application\Model\Repository\ActivityLogRepository');
        $mailer = $container->get('Application\Model\Service\Mailer');
        $emailbuilder = $container->get(\Application\Model\Service\EmailBuilder::class);
        $websiteProject = $container->get('Application\Model\Service\Verification\WebsiteProject');
        $videoRepository = $container->get('Application\Model\Repository\VideoRepository');

        return new ProductController($dbAdapter, $config, $request, $infoService, $tagService, $tagGroupService,
            $memberService, $projectService, $gitlab, $activityLog, $mailer, $emailbuilder, $websiteProject,
            $videoRepository, $container->get('Application\Model\Repository\ProjectCategoryRepository'),
            $container->get('Application\Model\Repository\ProjectRepository'),
            $container->get('Application\Model\Service\ProjectCategoryService'),
            $container->get('Application\Model\Repository\ProjectPlingsRepository'),
            $container->get('Application\Model\Service\ProjectPlingsService'),
            $container->get('Application\Model\Repository\ProjectFollowerRepository'),
            $container->get('Application\Model\Service\SectionSupportService'),
            $container->get('Application\Model\Repository\PploadFilesRepository'),
            $container->get('Application\Model\Service\ProjectUpdatesService'),
            $container->get('Application\Model\Repository\ProjectRatingRepository'),
            $container->get('Application\Model\Repository\SectionSupportRepository'),
            $container->get('Application\Model\Service\CollectionService'),
            $container->get('Application\Model\Repository\CommentsRepository'),
            $container->get('Application\Model\Service\SectionService'),
            $container->get('Application\Model\Service\ProjectCloneService'),
            $container->get('Application\Model\Service\ProjectTagRatingsService'),
            $container->get('Application\Model\Repository\TagsRepository'),
            $container->get('Application\Model\Repository\MediaViewsRepository'),
            $container->get('Application\Model\Repository\ReportProductsRepository'),
            $container->get('Application\Model\Service\PploadService'),
            $container->get('Application\Model\Repository\CollectionProjectsRepository')
        );
    }
}
