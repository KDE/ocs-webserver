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

namespace Statistic;

use Laminas\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Factory\IndexControllerFactory::class,
        ],
    ],
    'router'      => [
        'routes' => [
            'statistics' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/statistics[/:action][/project_id/:project_id][/yyyymm/:yyyymm][/]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_map'        => [
            'statistic/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'service_manager' => [
        'aliases'   => [
            Model\Interfaces\DataStatiInterface::class    => Model\Repository\DataStatiRepository::class,
            Model\Interfaces\DataStatiDwhInterface::class => Model\Repository\DataStatiDwhRepository::class,
        ],
        'factories' => [
            Model\Repository\DataStatiRepository::class    => Factory\DataStatiRepositoryFactory::class,
            Model\Repository\DataStatiDwhRepository::class => Factory\DataStatiDwhRepositoryFactory::class,
        ],
    ],

];
