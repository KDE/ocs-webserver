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

use Application\Controller\MembersettingController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class MembersettingControllerFactory
 *
 * @package Application\Controller\Factory
 */
class MembersettingControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return MembersettingController
     */

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new MembersettingController(
            $container->get('Application\Model\Service\MemberSettingValueService'),
            $container->get('Application\Model\Service\MemberService'),
            $container->get('Application\Model\Service\Ocs\Forum'),
            $container->get('Application\Model\Service\InfoService')
        );
    }
}
