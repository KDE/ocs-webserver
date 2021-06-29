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


use Application\Model\Repository\MemberDeactivationLogRepository;
use Application\Model\Service\Ocs\Forum;
use Interop\Container\ContainerInterface;
use Laminas\Config\Config;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ForumFactory implements FactoryInterface
{

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $config_forum = new Config($config['ocs_config']['settings']['server']['forum']);
        $db_adapter = $container->get('Laminas\Db\Adapter\Adapter');
        $member_deactivation_log = $container->get(MemberDeactivationLogRepository::class);

        return new Forum($config_forum, $db_adapter, $member_deactivation_log);
    }
}