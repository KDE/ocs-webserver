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
    'invokables' => [
        Controller\ContentController::class,
        Controller\LogoutController::class,
    ],
    'factories'  => [
        Controller\AuthController::class            => Controller\Factory\AuthControllerFactory::class,
        Controller\CollectionController::class      => Controller\Factory\CollectionControllerFactory::class,
        Controller\CommunityController::class       => Controller\Factory\CommunityControllerFactory::class,
        Controller\DlController::class              => Controller\Factory\DlControllerFactory::class,
        Controller\DuplicatesController::class      => Controller\Factory\DuplicatesControllerFactory::class,
        Controller\ExploreController::class         => Controller\Factory\ExploreControllerFactory::class,
        Controller\FileController::class            => Controller\Factory\FileControllerFactory::class,
        Controller\FundingController::class         => Controller\Factory\FundingControllerFactory::class,
        Controller\GatewayController::class         => Controller\Factory\GatewayControllerFactory::class,
        Controller\HomeController::class            => Controller\Factory\HomeControllerFactory::class,
        Controller\ReactController::class            => Controller\Factory\ReactControllerFactory::class,
        Controller\JsonController::class            => Controller\Factory\JsonControllerFactory::class,
        Controller\LoginController::class           => Controller\Factory\LoginControllerFactory::class,
        Controller\MembersettingController::class   => Controller\Factory\MembersettingControllerFactory::class,
        Controller\MisuseController::class          => Controller\Factory\MisuseControllerFactory::class,
        Controller\ModerationController::class      => Controller\Factory\ModerationControllerFactory::class,
        Controller\Ocsv1Controller::class           => Controller\Factory\Ocsv1ControllerFactory::class,
        Controller\PasswordController::class        => Controller\Factory\PasswordControllerFactory::class,
        Controller\ProductController::class         => Controller\Factory\ProductControllerFactory::class,
        Controller\ProductcategoryController::class => Controller\Factory\ProductcategoryControllerFactory::class,
        Controller\ProductcommentController::class  => Controller\Factory\ProductcommentControllerFactory::class,
        Controller\SearchController::class          => Controller\Factory\SearchControllerFactory::class,
        Controller\SectionController::class         => Controller\Factory\SectionControllerFactory::class,
        Controller\SettingsController::class        => Controller\Factory\SettingsControllerFactory::class,
        Controller\SpamController::class            => Controller\Factory\SpamControllerFactory::class,
        Controller\SubscriptionController::class    => Controller\Factory\SubscriptionControllerFactory::class,
        Controller\SupportersController::class      => Controller\Factory\SupportersControllerFactory::class,
        Controller\ReportController::class          => Controller\Factory\ReportControllerFactory::class,
        Controller\TagController::class             => Controller\Factory\TagControllerFactory::class,
        Controller\UserController::class            => Controller\Factory\UserControllerFactory::class,
        Controller\HiveController::class            => Controller\Factory\HiveControllerFactory::class,
    ],
];