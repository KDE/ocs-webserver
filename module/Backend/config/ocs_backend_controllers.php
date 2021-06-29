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

return [
    'invokables' => [

    ],
    'factories'  => [
        Controller\IndexController::class               => Controller\Factory\IndexControllerFactory::class,
        Controller\ProjectController::class             => Controller\Factory\ProjectControllerFactory::class,
        Controller\CategoriesController::class          => Controller\Factory\CategoriesControllerFactory::class,
        Controller\UserController::class                => Controller\Factory\UserControllerFactory::class,
        Controller\BrowselisttypeController::class      => Controller\Factory\BrowselisttypeControllerFactory::class,
        Controller\GhnsexcludedController::class        => Controller\Factory\GhnsexcludedControllerFactory::class,
        Controller\CategorytagController::class         => Controller\Factory\CategorytagControllerFactory::class,
        Controller\CategorytaggroupController::class    => Controller\Factory\CategorytaggroupControllerFactory::class,
        Controller\PaypalvalidstatusController::class   => Controller\Factory\PaypalvalidstatusControllerFactory::class,
        Controller\LetteravatarController::class        => Controller\Factory\LetteravatarControllerFactory::class,
        Controller\MemberpayoutController::class        => Controller\Factory\MemberpayoutControllerFactory::class,
        Controller\PayoutstatusController::class        => Controller\Factory\PayoutstatusControllerFactory::class,
        Controller\MemberpaypaladdressController::class => Controller\Factory\MemberpaypaladdressControllerFactory::class,
        Controller\CommentsController::class            => Controller\Factory\CommentsControllerFactory::class,
        Controller\MailController::class                => Controller\Factory\MailControllerFactory::class,
        Controller\ReportcommentsController::class      => Controller\Factory\ReportcommentsControllerFactory::class,
        Controller\ReportproductsController::class      => Controller\Factory\ReportproductsControllerFactory::class,
        Controller\TagsController::class                => Controller\Factory\TagsControllerFactory::class,
        Controller\SectionController::class             => Controller\Factory\SectionControllerFactory::class,
        Controller\SectioncategoriesController::class   => Controller\Factory\SectioncategoriesControllerFactory::class,
        Controller\StoreController::class               => Controller\Factory\StoreControllerFactory::class,
        Controller\StorecategoriesController::class     => Controller\Factory\StorecategoriesControllerFactory::class,
        Controller\SpamkeywordsController::class        => Controller\Factory\SpamkeywordsControllerFactory::class,
        Controller\ClaimController::class               => Controller\Factory\ClaimControllerFactory::class,

    ],
];