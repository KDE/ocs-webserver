<?php
/** @noinspection PhpUndefinedFieldInspection */

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

namespace Application\Acl;

use Laminas\Config\Config;
use Laminas\Config\Factory;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource;

/**
 * Class Resources
 *
 * @package Application\Acl
 */
class Resources
{
    /**
     * @var Acl
     */
    private $acl;

    /**
     * Resources constructor.
     *
     * @param Acl $acl
     */
    public function __construct(Acl $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @return Acl
     */
    public function getResources()
    {
        $this->acl->addResource(new GenericResource(\Application\Controller\AuthController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\CollectionController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\CommunityController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\ContentController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\DlController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\DuplicatesController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\ExploreController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\FileController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\FundingController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\GatewayController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\HomeController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\ReactController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\JsonController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\LoginController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\LogoutController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\MembersettingController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\MisuseController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\ModerationController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\Ocsv1Controller::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\PasswordController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\ProductcategoryController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\ProductcommentController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\ProductController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\ReportController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\SearchController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\SectionController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\SettingsController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\SpamController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\SubscriptionController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\SupportersController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\TagController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\UserController::class));
        $this->acl->addResource(new GenericResource(\Application\Controller\HiveController::class));

        // TODO: Move definition to modul
        $this->acl->addResource(new GenericResource(\Portal\Controller\PortalController::class));

        $this->acl->addResource(new GenericResource(\Statistic\Controller\IndexController::class));

        // add Backend Resources
        $config = new Config(Factory::fromFiles(glob(__DIR__ . '/../../../Backend/config/ocs_backend_controllers.php')),true);
        foreach ($config->factories as $key => $value) {
            $this->acl->addResource(new GenericResource($key));
        }

        // for developing purposes
        $this->acl->addResource(new GenericResource("SanSessionToolbar\Controller\SessionToolbar"));

        // add service resources
        $this->acl->addResource(new GenericResource(\OcsService\Middleware\Piwik::class));
        $this->acl->addResource(new GenericResource(\OcsService\SessionApi\Announcement::class));

        return $this->acl;
    }
}