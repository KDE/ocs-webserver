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

namespace Application;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
return [    
            'application_home'            => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '[/s/:store_id][/]',
                    'defaults' => [
                        'controller' => Controller\ReactController::class,
                        'action'     => 'home',
                    ],
                ],
            ],

            'application_home_react'            => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/react/home2',
                    'defaults' => [
                        'controller' => Controller\ReactController::class,
                        'action'     => 'home',
                    ],
                ],
            ],
            'application_browse_react'          => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '[/s/:store_id]/browse[/]',
                    'defaults' => [
                        'controller' => Controller\ReactController::class,
                        'action'     => 'explore',
                    ],
                ],
            ],

            'application_explore_react'         => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '[/s/:store_id]/explore[/]',
                    'defaults' => [
                        'controller' => Controller\ReactController::class,
                        'action'     => 'explore',
                    ],
                ],
            ],

            'application_favourites_react' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/my-favourites[/]',
                    'defaults' => [
                        'controller' => Controller\ReactController::class,
                        'action'     => 'explore',
                        'fav'        => '1',
                    ],
                ],
            ],

            'application_product_react'                => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '[/s/:store_id]/p/:project_id[/]',
                    'defaults' => [
                        'controller' => Controller\ReactController::class,
                        'action'     => 'detail',
                    ],
                ],
            ],
            'application_listing_product_react'                => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '[/s/:store_id]/c/:project_id[/]',
                    'defaults' => [
                        'controller' => Controller\ReactController::class,
                        'action'     => 'detaillisting',
                    ],
                ],
            ],
        
            'application_user_react'                       => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '[/s/:store_id]/u/:username[/]',
                    'defaults' => [
                        'controller' => Controller\ReactController::class,
                        'action'     => 'user',
                    ],
                ],
            ],

            
        ]
    ;