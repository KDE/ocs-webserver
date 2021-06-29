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


return [
    'session_containers' => [
        'Ocs_Global',
    ],
    'controllers'        => require __DIR__ . '/ocs_controllers.php',
    // We register module-provided controller plugins under this key.
    'controller_plugins' => require __DIR__ . '/ocs_controller_plugins.php',
    'router'             => ['routes' => array_merge(require __DIR__ . '/ocs_routers_pling.php', require __DIR__ . '/ocs_routers_react.php')],
    'view_manager'       => [
        'doctype'                  => 'HTML5',
        'display_not_found_reason' => false,
//        'not_found_template'       => 'error/404',
        'not_found_template'       => 'error/404-astronaut',
        'display_exceptions'       => false,
        'exception_template'       => 'error/index',
        // The TemplateMapResolver allows you to directly map template names
        // to specific templates. The following map would provide locations
        // for a home page template ("application/index/index"), as well as for
        // the layout ("layout/layout"), error pages ("error/index"), and
        // 404 page ("error/404"), resolving them to view scripts.
        'template_map'             => [
            'layout/empty'                        => __DIR__ . '/../view/layout/empty.phtml',
            'layout/layout'                       => __DIR__ . '/../view/layout/layout.phtml',
            'layout/flat-ui'                      => __DIR__ . '/../view/layout/flat_ui_template.phtml',
            'layout/pling-ui'                     => __DIR__ . '/../view/layout/pling_ui_template.phtml',
            //'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'                           => __DIR__ . '/../view/error/404.phtml',
            'error/404-astronaut'                 => __DIR__ . '/../view/error/404-astronaut.phtml',
            'error/index'                         => __DIR__ . '/../view/error/index.phtml',
            'product/partials/productCommentsUX1' => __DIR__ . '/../view/application/product/partials/productCommentsUX1.phtml',
            'product/partials/productCommentsUX2' => __DIR__ . '/../view/application/product/partials/productCommentsUX2.phtml',
        ],
        'template_path_stack'      => [
            __DIR__ . '/../view',
        ],
        // Set the template name for the site's layout.
        //
        // By default, the MVC's default Rendering Strategy uses the
        // template name "layout/layout" for the site's layout.
        // Here, we tell it to use the "site/layout" template,
        // which we mapped via the TemplateMapResolver above.
        'layout'                   => 'layout/empty',
        'strategies'               => array(
            'ViewJsonStrategy',
        ),
    ],

    'service_manager' => [
        // associative array that maps a key to a service instance.
        'services'           => [],
        // an associative array that maps a key to a constructor-less service; i.e., for services that do not require arguments to the constructor. The key and service name usually are the same; if they are not, the key is treated as an alias.
        'invokables'         => [
            Model\Service\UrlEncrypt::class,
        ],
        // associative array that map a key to a factory name, or any callable.
        'factories'          => require __DIR__ . '/ocs_factories.php',
        // a list of abstract factories classes. An abstract factory is a factory that can potentially create any object, based on some criterias.
        'abstract_factories' => [],
        // an associative array that maps service keys to lists of delegator factory keys, see the delegators documentation for more details
        'delegators'         => [],
        // associative array that map a key to a service key (or another alias).
        'aliases'            => [
            Model\Interfaces\ConfigStoreInterface::class => Model\Repository\ConfigStoreRepository::class,
        ],
        // a list of callable or initializers that are run whenever a service has been created
        'initializers'       => [],
        // configuration for the lazy service proxy manager, and a class map of service:class pairs that will act as lazy services
        'lazy_services'      => [],
        // associative array that maps a service name to a boolean, in order to indicate to the service manager whether or not it should cache services it creates via get method
        'shared'             => [],
        //'shared_by_default'  => true,
    ],

    // We register module-provided view helpers under this key.
    'view_helpers'    => require __DIR__ . '/ocs_view_helpers.php',

    // We register module-provided form elements under this key.
    'form_elements'   => require __DIR__ . '/ocs_forms.php',


    'view_helper_config' => [
        // Flash messenger
        'flashmessenger' => [
            'message_open_format'      => '<div %s role="alert">',
            'message_close_string'     => '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>',
            'message_separator_string' => '<br>',
        ],
    ],

];
