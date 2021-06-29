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

use Application\Model\Service\MemberService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class MemberServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return MemberService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        return new MemberService(
            $container->get('Application\Model\Repository\MemberRepository'), $container->get('Application\Model\Factory\CacheFactory'), $container->get('Application\Model\Repository\ProjectRatingRepository'), $container->get('Application\Model\Repository\ReportProductsRepository'), $container->get('Application\Model\Repository\ReportCommentsRepository'), $container->get('Application\Model\Repository\MemberEmailRepository'), $container->get('Application\Model\Repository\ImageRepository'), $container->get('Application\Model\Service\ProjectService'), $container->get('Application\Model\Repository\ProjectRepository'), $container->get('Application\Model\Service\MemberDeactivationLogService'), $container->get('Application\Model\Service\ProjectCommentsService'), $container->get('Application\Model\Repository\MemberExternalIdRepository'), $container->get('Application\Model\Service\MemberEmailService'), $container->get('Application\Model\Service\Ocs\ServerManager')

        );

    }
}