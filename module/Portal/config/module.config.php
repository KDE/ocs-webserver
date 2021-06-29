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

namespace Portal;

return array(
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            Controller\PortalController::class,
        ),
    ),

    // This lines opens the configuration for the RouteManager
    'router'      => array(
        // Open configuration for all possible routes
        'routes' => array(
            // Define a new route called "portal"
            'portal' => array(
                // Define the routes type to be "Laminas\Mvc\Router\Http\Literal", which is basically just a string
                'type'    => 'segment',
                // Configure the route itself
                'options' => array(
                    // Listen to "/portal" as uri
                    'route'    => '/portal[/]',
                    // Define default controller and action to be called when this route is matched
                    'defaults' => array(
                        'controller' => Controller\PortalController::class,
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
);