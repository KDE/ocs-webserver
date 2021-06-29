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
use Laminas\Router\Http\Regex;
use Laminas\Router\Http\Segment;

return [    
        // 'application_home'            => [
        //     'type'    => Segment::class,
        //     'options' => [
        //         'route'    => '[/s/:store_id][/]',
        //         'defaults' => [
        //             'controller' => Controller\HomeController::class,
        //             'action'     => 'index',
        //         ],
        //     ],
        // ],
        
        'application_home_metaheader' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/home/metamenubundlejs',
                'defaults' => [
                    'controller' => Controller\HomeController::class,
                    'action'     => 'metamenubundlejs',
                ],
            ],
        ],
       
        'application_start'           => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/start',
                'defaults' => [
                    'controller' => Controller\HomeController::class,
                    'action'     => 'start',
                ],
            ],
        ],
        'application_register'        => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/register[/:action][/vid/:vid][/e/:e][/redirect/:redirect][/]',
                'defaults' => [
                    'controller' => Controller\AuthController::class,
                    'action'     => 'register',
                ],
            ],
        ],
        'application_login'           => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/login[/redirect/:redirect][/]',
                'defaults' => [
                    'controller' => Controller\AuthController::class,
                    'action'     => 'login',
                ],
            ],
        ],
        'application_logout'          => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/logout[/redirect/:redirect][/]',
                'defaults' => [
                    'controller' => Controller\AuthController::class,
                    'action'     => 'logout',
                ],
            ],
        ],
        'application_logout_set'      => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/logout/set',
                'defaults' => [
                    'controller' => Controller\LogoutController::class,
                    'action'     => 'set',
                ],
            ],
        ],
        'not-authorized'              => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/not-authorized[/]',
                'defaults' => [
                    'controller' => Controller\AuthController::class,
                    'action'     => 'notAuthorized',
                ],
            ],
        ],
        'application_password'        => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/password/:action[/]',
                'defaults' => [
                    'controller' => Controller\PasswordController::class,
                    'action'     => 'request',
                ],
            ],
        ],
        'application_showfeatureajax' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/showfeatureajax/page/:page[/]',
                'defaults' => [
                    'controller' => Controller\HomeController::class,
                    'action'     => 'showfeatureajax',
                ],
            ],
        ],
        'application_showfeatureajax2' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/showfeature[/]',
                'defaults' => [
                    'controller' => Controller\HomeController::class,
                    'action'     => 'showfeature',
                ],
            ],
        ],

        'application_explore'         => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/explore[/:action][/]',
                'defaults' => [
                    'controller' => Controller\ExploreController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_store_browse_param' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/browse[/cat/:cat][/page/:page][/order/:ord][/ord/:ord][/]',
                'defaults' => [
                    'controller' => Controller\ExploreController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        

        // 'application_store_browse2_param' => [
        //     'type'    => Segment::class,
        //     'options' => [
        //         'route'    => '[/s/:store_id]/browse2[/cat/:cat][/page/:page][/order/:ord][/ord/:ord][/]',
        //         'defaults' => [
        //             'controller' => Controller\ExploreController::class,
        //             'action'     => 'indexReact',
        //         ],
        //     ],
        // ],

       
        'application_browse'          => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/browse[/cat][/cat/:cat][/page/:page][/order/:ord][/ord/:ord][/fav/[:fav]][/]',
                'defaults' => [
                    'controller' => Controller\ExploreController::class,
                    'action'     => 'index',
                ],
            ],
        ],
       

        // 'application_browse_react'          => [
        //     'type'    => Segment::class,
        //     'options' => [
        //         'route'    => '[/s/:store_id]/browse2[/cat][/cat/:cat][/page/:page][/order/:ord][/ord/:ord][/fav/[:fav]][/]',
        //         'defaults' => [
        //             'controller' => Controller\ExploreController::class,
        //             'action'     => 'indexReact',
        //         ],
        //     ],
        // ],

        /**
         * @TODO need to dbl check
         * replaced with react router
        'application_browse_regex_w_store' => [
            'type'    => Regex::class,
            'options' => [
                'regex' => '^(?=\/s\/(?<store_id>.*?)\/)?.*\/browse(?=.*\/cat\/(?<cat>[0-9]+))?(?=.*\/order\/(?<order>.*?)\/)?(?=.*\/ord\/(?<ord>.*?)\/)?(?=.*\/fav\/(?<fav>.*?)\/)?(?=.*\/page\/(?<page>[0-9]+))?.*',
                'defaults' => [
                    'controller' => Controller\ExploreController::class,
                    'action'     => 'index',
                ],
                'spec' => '%store_id%/browse/%cat%.%ord%.%order%.%fav%.%page%',
            ],
        ],
        'application_browse_2'        => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/browse[/page/:page][/cat][/cat/:cat][/order/:ord][/ord/:ord][/fav/[:fav]][/]',
                'defaults' => [
                    'controller' => Controller\ExploreController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        end replaced with react router
            */
        
        'application_product_show_next_comments' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/p/:project_id/show/page/:page',
                'defaults' => [
                    'controller' => Controller\ProductController::class,
                    'action'     => 'index',
                ],
            ],
        ],

        // 'application_product'                    => [
        //     'type'    => Segment::class,
        //     'options' => [
        //         'route'    => '[/s/:store_id]/p/:project_id[/:action][/m/:m][/]',
        //         'defaults' => [
        //             'controller' => Controller\ProductController::class,
        //             'action'     => 'indexReact',
        //         ],
        //     ],
        // ],
       
       
        
        'application_product'                    => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/p/:project_id[/:action][/m/:m][/]',
                'defaults' => [
                    'controller' => Controller\ProductController::class,
                    'action'     => 'index',
                ],
            ],
        ],

        // 'application_product_react'                 => [
        //     'type'    => Segment::class,
        //     'options' => [
        //         'route'    => '[/s/:store_id]/p2/:project_id[/:action][/m/:m][/]',
        //         'defaults' => [
        //             'controller' => Controller\ProductController::class,
        //             'action'     => 'indexReact',
        //         ],
        //     ],
        // ],
        'application_product_add'                => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/product/add[/]',
                'defaults' => [
                    'controller' => Controller\ProductController::class,
                    'action'     => 'add',
                ],
            ],
        ],
        // edited to difference from ocs_routers_react application_product_react
        'application_product_save'               => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/product/save[/]',
                'defaults' => [
                    'controller' => Controller\ProductController::class,
                    'action'     => 'saveproduct',
                ],
            ],
        ],
        'application_comment'                    => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/productcomment/:action[/]',
                'defaults' => [
                    'controller' => Controller\ProductcommentController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_report'                     => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/report/:action[/]',
                'defaults' => [
                    'controller' => Controller\ReportController::class,
                    'action'     => 'index',
                ],
            ],
        ],

        'application_search_query'                     => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/find[/]',
                'defaults' => [
                    'controller' => Controller\SearchController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_search_regex'                     => [
            'type'    => Regex::class,
            'options' => [
                'regex' => '(?=\/s\/(?<store_id>.*?)\/)?.*\/search(?=.*\/projectSearchText\/(?<search>.*?)\/)?(?=.*\/t\/(?<t>.*?)\/)?(?=.*\/f\/(?<f>.*?)\/)?(?=.*\/pci\/(?<pci>.*?)\/)?(?=.*\/lic\/(?<lic>.*?)\/)?(?=.*\/pkg\/(?<pkg>.*?)\/)?(?=.*\/arch\/(?<arch>.*?)\/)?(?=.*\/page\/(?<page>.*?)\/)?.*',
                'defaults' => [
                    'controller' => Controller\SearchController::class,
                    'action'     => 'index',
                ],
                'spec' => '/search/%search%.%t%.%f%.%pci%.%lic%.%pkg%.page%',
            ],
        ],

        /**
         *  replaced with react router        
        'application_user'                       => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/u/:username[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\UserController::class,
                    'action'     => 'indexReact',
                ],
            ],
        ],
            end replaced with react router
         */

        'application_user'                       => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/u/:username[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\UserController::class,
                    'action'     => 'index',
                ],
            ],
        ],

        'application_user_avatar'                => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/member/avatar[/:emailhash][/:size][/]',
                'defaults' => [
                    'controller' => Controller\UserController::class,
                    'action'     => 'avatar',
                ],
            ],
        ],
        'application_avatar_username'            => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/avatar[/:user_name][/]',
                'defaults' => [
                    'controller' => Controller\UserController::class,
                    'action'     => 'avatar',
                ],
            ],
        ],
        'application_member'                     => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/member/:member_id[/:action][/page/:page][/]',
                'constraints' => [
                    'member_id'  => '\d+',
                ],
                'defaults' => [
                    'controller' => Controller\UserController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_settings'                   => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/settings[/:action][/v/:v][/]',
                'defaults' => [
                    'controller' => Controller\SettingsController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_static_faq'                 => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/faq-pling',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'faq',
                ],
            ],
        ],
        'application_static_gitfaq'              => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/faq-opencode',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'gitfaq',
                ],
            ],
        ],
        'application_static_ocsapi'              => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/ocs-api',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'ocsapi',
                ],
            ],
        ],
        'application_static_about'               => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/about',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'about',
                ],
            ],
        ],
        'application_static_terms'               => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/terms',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'terms',
                ],
            ],
        ],
        'application_static_terms_general'       => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/terms/general',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'terms-general',
                ],
            ],
        ],
        'application_static_terms_publishing'    => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/terms/publishing',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'terms-publishing',
                ],
            ],
        ],
        'application_static_terms_dmca'          => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/terms/dmca',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'terms-dmca',
                ],
            ],
        ],
        'application_static_terms_payout'        => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/terms/payout',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'terms-payout',
                ],
            ],
        ],
        'application_static_terms_cookies'       => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/terms/cookies',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'terms-cookies',
                ],
            ],
        ],
        'application_static_privacy'             => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/privacy',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'privacy',
                ],
            ],
        ],
        'application_static_imprint'             => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/imprint',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'imprint',
                ],
            ],
        ],
        'application_static_contact'             => [
            'type'    => Literal::class,
            'options' => [
                'route'    => '/contact',
                'defaults' => [
                    'controller' => Controller\ContentController::class,
                    'action'     => 'index',
                    'page'       => 'contact',
                ],
            ],
        ],


        'application_tag'                         => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/tag/:action[/]',
                'defaults' => [
                    'controller' => Controller\TagController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_productcategory'             => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/productcategory/:action[/]',
                'defaults' => [
                    'controller' => Controller\ProductcategoryController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_moderation'                  => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/moderation[/:action]',
                'defaults' => [
                    'controller' => Controller\ModerationController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_clones'                      => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/clones[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\ModerationController::class,
                    'action'     => 'indexcredits',
                ],
            ],
        ],
        'application_mods'                        => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/mods[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\ModerationController::class,
                    'action'     => 'indexmods',
                ],
            ],
        ],
        'application_watchlist_productmoderation' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/watchlist-productmoderation[/:action]',
                'defaults' => [
                    'controller' => Controller\ModerationController::class,
                    'action'     => 'productmoderation',
                ],
            ],
        ],

        'application_spam' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/spam[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\SpamController::class,
                    'action'     => 'index',
                ],
            ],
        ],

        'application_watchlist_newproducts'         => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/watchlist-newproducts[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\SpamController::class,
                    'action'     => 'mostnewproduct',
                ],
            ],
        ],
        'application_watchlist_products_10_files'   => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/watchlist-products-10-files[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\SpamController::class,
                    'action'     => 'product',
                ],
            ],
        ],
        'application_watchlist_samepaypal'          => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/watchlist-samepaypal[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\SpamController::class,
                    'action'     => 'paypal',
                ],
            ],
        ],
        'application_watchlist_md5sum'              => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/watchlist-md5sum-duplicated[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\SpamController::class,
                    'action'     => 'mdsum',
                ],
            ],
        ],
        'application_watchlist_unpublishedproduct'  => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/watchlist-unpublishedproduct[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\SpamController::class,
                    'action'     => 'unpublishedproduct',
                ],
            ],
        ],
        'application_watchlist_newproduct_2_month'  => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/watchlist-newproduct-2-month[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\SpamController::class,
                    'action'     => 'newproduct',
                ],
            ],
        ],
        'application_watchlist_products_deprecated' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/watchlist-products-deprecated[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\SpamController::class,
                    'action'     => 'deprecated',
                ],
            ],
        ],
        'application_duplicates'                    => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/duplicates[/:action]',
                'defaults' => [
                    'controller' => Controller\DuplicatesController::class,
                    'action'     => 'index',
                ],
            ],
        ],

        'application_misuse'                 => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/misuse[/:action][/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\MisuseController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_supporters'             => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/supporters[/:action]',
                'defaults' => [
                    'controller' => Controller\SupportersController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_section'                => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/section[/:action]',
                'defaults' => [
                    'controller' => Controller\SectionController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_dl'                     => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/dl[/:action]',
                'defaults' => [
                    'controller' => Controller\DlController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_json'                   => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/json[/:action][/p/:p][/s/:s][/c/:c][/id/:id][/]',
                'defaults' => [
                    'controller' => Controller\JsonController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_support_predefined'     => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/support-predefined',
                'defaults' => [
                    'controller' => Controller\SubscriptionController::class,
                    'action'     => 'supportpredefinded',
                ],
            ],
        ],
        'application_support_pay_predefined' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/support/paypredefined',
                'defaults' => [
                    'controller' => Controller\SubscriptionController::class,
                    'action'     => 'paypredefined',
                ],
            ],
        ],
        'application_support'                => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/support',
                'defaults' => [
                    'controller' => Controller\SubscriptionController::class,
                    'action'     => 'support',
                ],
            ],
        ],

        'application_support_pay' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/support/pay',
                'defaults' => [
                    'controller' => Controller\SubscriptionController::class,
                    'action'     => 'pay',
                ],
            ],
        ],

        'application_support_paymentok' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/support/paymentok',
                'defaults' => [
                    'controller' => Controller\SubscriptionController::class,
                    'action'     => 'paymentok',
                ],
            ],
        ],

        'application_support_paymentcancel' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/support/paymentcancel',
                'defaults' => [
                    'controller' => Controller\SubscriptionController::class,
                    'action'     => 'paymentcancel',
                ],
            ],
        ],
        'application_membersetting'         => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/membersetting[/:action][/itemid/:itemid][/itemvalue/:itemvalue]',
                'defaults' => [
                    'controller' => Controller\MembersettingController::class,
                    'action'     => 'index',
                ],
            ],
        ],

        'application_collection' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/c/:project_id[/:action][/page/:page][/m/:m][/]',
                'defaults' => [
                    'controller' => Controller\CollectionController::class,
                    'action'     => 'index',
                ],
            ],
        ],

        'application_collection_show_next_comments' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '[/s/:store_id]/c/:project_id/show/page/:page',
                'defaults' => [
                    'controller' => Controller\CollectionController::class,
                    'action'     => 'index',
                ],
            ],
        ],

        'application_collection_action'        => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/c/:project_id/:action[/]',
                'defaults' => [
                    'controller' => Controller\CollectionController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_collection_parm_m'        => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/c/:project_id/:action/m/[:m]/',
                'defaults' => [
                    'controller' => Controller\CollectionController::class,
                    'action'     => 'index',
                ],
            ],
        ],               
        
        'application_collection_add'                => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/collection/add',
                'defaults' => [
                    'controller' => Controller\CollectionController::class,
                    'action'     => 'add',
                ],
            ],
        ],
        'application_collection_save'               => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/c/save[/]',
                'defaults' => [
                    'controller' => Controller\CollectionController::class,
                    'action'     => 'saveproduct',
                ],
            ],
        ],
        'application_ocs_providers'                 => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/ocs/providers.xml',
                'defaults' => [
                    'controller' => Controller\Ocsv1Controller::class,
                    'action'     => 'providers',
                ],
            ],
        ],
        'application_ocs'                           => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/ocs/v1/[:action][/]',
                'defaults' => [
                    'controller' => Controller\Ocsv1Controller::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_ocs_contentcategories'         => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/ocs/v1/content/categories',
                'defaults' => [
                    'controller' => Controller\Ocsv1Controller::class,
                    'action'     => 'contentcategories',
                ],
            ],
        ],
        'application_ocs_content_contentdata'       => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/ocs/v1/content/data',
                'defaults' => [
                    'controller' => Controller\Ocsv1Controller::class,
                    'action'     => 'contentdata',
                ],
            ],
        ],
        'application_ocs_content_contentdata2'      => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/ocs/v1/content/data/[:contentid][/]',
                'defaults' => [
                    'controller' => Controller\Ocsv1Controller::class,
                    'action'     => 'contentdata',
                ],
            ],
        ],
        'application_ocs_content_contentdownload'   => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/ocs/v1/content/download/[:contentid]/[:itemid]',
                'defaults' => [
                    'controller' => Controller\Ocsv1Controller::class,
                    'action'     => 'contentdownload',
                ],
            ],
        ],
        'application_ocs_content_contentpreviewpic' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/ocs/v1/content/previewpic/[:contentid]',
                'defaults' => [
                    'controller' => Controller\Ocsv1Controller::class,
                    'action'     => 'contentpreviewpic',
                ],
            ],
        ],
        'application_ocs_comment'                   => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/ocs/v1/comments/data/[:comment_type]/[:contentid]/[:page][/]',
                'defaults' => [
                    'controller' => Controller\Ocsv1Controller::class,
                    'action'     => 'comments',
                ],
            ],
        ],
        'application_gateway'                       => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/gateway[/:action][/]',
                'defaults' => [
                    'controller' => Controller\GatewayController::class,
                    'action'     => 'index',
                ],
            ],
        ],

        'application_community'  => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/community[/:action][/]',
                'defaults' => [
                    'controller' => Controller\CommunityController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_file'       => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/file[/:action][/]',
                'defaults' => [
                    'controller' => Controller\FileController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        'application_funding'    => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/funding[/:action][/]',
                'defaults' => [
                    'controller' => Controller\FundingController::class,
                    'action'     => 'index',
                ],
            ],
        ],
        /**
        'application_favourites' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/my-favourites[/]',
                'defaults' => [
                    'controller' => Controller\ExploreController::class,
                    'action'     => 'index',
                    'fav'        => '1',
                ],
            ],
        ],
            */
            
        /*
         * $router->addRoute('browse_favourites', new Zend_Controller_Router_Route('/my-favourites/*', array(
            'module'     => 'default',
            'controller' => 'explore',
            'action'     => 'index',
            'fav'        => '1'
        )));
         */

        'application_store'              => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/s/[:store_id][/]',
                'defaults' => [
                    'controller' => Controller\HomeController::class,
                    'action'     => 'index',
                ],
            ],
        ],

        'application_plings'              => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/plings[/]',
                'defaults' => [
                    'controller' => Controller\HomeController::class,
                    'action'     => 'plings',
                ],
            ],
        ],

        'application_loginservices' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/l/[:action][/]',
                'defaults' => [
                    'controller' => Controller\LoginController::class,
                    'action'     => 'index',
                ],
            ],
        ],

        // mapping old hive url's
        // https://store.kde.org/content/show.php?content=26043
        // https://www.linux-apps.com/hive/show/content/11576/page/2
        // https://www.opendesktop.org/usermanager/search.php?username=houston4444
        // https://www.pling.com/s/App-Addonsp/999895/
        // https://www.pling.com/s/App-Addonssearch/projectSearchText/source-package/page/1/t/restore/lic/gplv3/pkg/source-package
        // https://www.gnome-look.org/content/download.php?content=82562&id=7&tan=56766389
        // https://www.gnome-look.org/CONTENT/content-files/68524-Pro%20Street%20Wood.tar.gz

        'application_hive_project' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/hive/show/content/:content[/page/:page][/]',
                'defaults' => [
                    'controller' => Controller\HiveController::class,
                    'action'     => 'show',
                ],
            ],
        ],

        'application_hive_member' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/hive/usersearch/username[/:username][/]',
                'defaults' => [
                    'controller' => Controller\HiveController::class,
                    'action'     => 'usersearch',
                ],
            ],
        ],

        'hive_download'      => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/content/download.php[/]',
                'defaults' => [
                    'controller' => Controller\HiveController::class,
                    'action'     => 'show',
                ],
            ],
        ],
        'hive_content_files' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/CONTENT/content-files/:content[-][:anything_else][/]',
                'defaults' => [
                    'controller' => Controller\HiveController::class,
                    'action'     => 'show',
                ],
            ],
        ],
        'hive_project'       => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/content/show.php[/:title][/]',
                'defaults' => [
                    'controller' => Controller\HiveController::class,
                    'action'     => 'show',
                ],
            ],
        ],
        'hive_member'        => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/hive/usersearch/username/[:username][/page/:page][/][:anything][/][:else][/]',
                'defaults' => [
                    'controller' => Controller\HiveController::class,
                    'action'     => 'usersearch',
                ],
            ],
        ],
        'hive_member_search' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/usermanager/search.php[/]',
                'defaults' => [
                    'controller' => Controller\HiveController::class,
                    'action'     => 'usersearch',
                ],
            ],
        ],
    
];