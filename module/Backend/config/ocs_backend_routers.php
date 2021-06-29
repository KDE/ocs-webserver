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

namespace Backend;

use Laminas\Router\Http\Segment;

return [
    'routes' => [
        'backend'                     => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend[/:action][/]',
                'defaults' => [
                    'controller' => Controller\IndexController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_project'             => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/project[/:action][/]',
                'defaults' => [
                    'controller' => Controller\ProjectController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_categories'          => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/categories[/:action][/]',
                'defaults' => [
                    'controller' => Controller\CategoriesController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_user'                => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/user[/:action][/]',
                'defaults' => [
                    'controller' => Controller\UserController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_browselisttype'      => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/browselisttype[/:action][/]',
                'defaults' => [
                    'controller' => Controller\BrowselisttypeController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_ghnsexcluded'        => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/ghnsexcluded[/:action][/]',
                'defaults' => [
                    'controller' => Controller\GhnsexcludedController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_categorytag'         => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/categorytag[/:action][/]',
                'defaults' => [
                    'controller' => Controller\CategorytagController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_categorytaggroup'    => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/categorytaggroup[/:action][/]',
                'defaults' => [
                    'controller' => Controller\CategorytaggroupController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_paypalvalidstatus'   => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/paypalvalidstatus[/:action][/]',
                'defaults' => [
                    'controller' => Controller\PaypalvalidstatusController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_letteravatar'        => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/letteravatar[/:action][/]',
                'defaults' => [
                    'controller' => Controller\LetteravatarController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_memberpayout'        => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/memberpayout[/:action][/]',
                'defaults' => [
                    'controller' => Controller\MemberpayoutController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_payoutstatus'        => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/payoutstatus[/:action][/]',
                'defaults' => [
                    'controller' => Controller\PayoutstatusController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_memberpaypaladdress' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/memberpaypaladdress[/:action][/]',
                'defaults' => [
                    'controller' => Controller\MemberpaypaladdressController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_comments'            => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/comments[/:action][/]',
                'defaults' => [
                    'controller' => Controller\CommentsController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_mail'                => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/mail[/:action][/id/:id][/]',
                'defaults' => [
                    'controller' => Controller\MailController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_reportcomments'      => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/reportcomments[/:action][/]',
                'defaults' => [
                    'controller' => Controller\ReportcommentsController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_reportproducts'      => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/reportproducts[/:action][/]',
                'defaults' => [
                    'controller' => Controller\ReportproductsController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_tags'                => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/tags[/:action][/]',
                'defaults' => [
                    'controller' => Controller\TagsController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_section'             => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/section[/:action][/]',
                'defaults' => [
                    'controller' => Controller\SectionController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_sectioncategories'   => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/sectioncategories[/:action][/]',
                'defaults' => [
                    'controller' => Controller\SectioncategoriesController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_store'               => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/store[/:action][/]',
                'defaults' => [
                    'controller' => Controller\StoreController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_storecategories'     => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/storecategories[/:action][/]',
                'defaults' => [
                    'controller' => Controller\StorecategoriesController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_spamkeywords'        => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/spamkeywords[/:action][/]',
                'defaults' => [
                    'controller' => Controller\SpamkeywordsController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'backend_claim'               => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/backend/claim[/:action][/]',
                'defaults' => [
                    'controller' => Controller\ClaimController::class,
                    'action'     => 'index',
                ],
            ],
        ],
    ],
];