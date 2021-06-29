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

use Application\Controller\ReactController;
use Interop\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class ReactControllerFactory
 *
 * @package Application\Controller\Factory
 */
class ReactControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return ReactController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ReactController(          
            $container->get('Application\Model\Service\InfoService'),
            $container->get('Application\Model\Service\ProjectService'),
            $container->get('Application\Model\Service\MemberService'),
            $container->get('Application\Model\Repository\ProjectCategoryRepository'),
            $container->get('Application\Model\Repository\ProjectRepository'),
            $container->get('Application\Model\Service\SectionService'),
            $container->get('Application\Model\Repository\TagsRepository'),
            $container->get('Application\Model\Service\TagService'),
            $container->get('Application\Model\Repository\SectionSupportRepository'),
            $container->get('Application\Model\Repository\ProjectPlingsRepository'),
            $container->get('Application\Model\Repository\ProjectFollowerRepository'),
            $container->get('Application\Model\Service\ProjectUpdatesService'),
            $container->get('Application\Model\Repository\ProjectRatingRepository'),            
            $container->get('Application\Model\Service\Ocs\Gitlab'),        
            $container->get('Application\Model\Service\TagGroupService'),
            $container->get('Application\Model\Repository\PploadFilesRepository'),                          
            $container->get('Application\Model\Repository\CommentsRepository'),
            $container->get('Application\Model\Service\CollectionService'),
            $container->get('Application\Model\Repository\MediaViewsRepository'),
            $container->get('Application\Model\Service\ProjectCloneService'),
            $container->get('Application\Model\Repository\ReportProductsRepository'),
            $container->get('Application\Model\Service\ProjectPlingsService'),
            $container->get('Application\Model\Service\SectionSupportService'),
            $container->get('Application\Model\Service\PploadService'),
            $container->get('Application\Model\Repository\LoginHistoryRepository'),
            $container->get('Application\Model\Repository\MemberScoreRepository'),
            $container->get('Application\Model\Service\StatDownloadService'),
            $container->get('Application\Model\Repository\CollectionProjectsRepository'),
            $container->get('Application\Model\Service\ProjectCategoryService')            
            
                                
        );
    }
}
