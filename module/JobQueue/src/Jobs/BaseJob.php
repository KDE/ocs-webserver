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

namespace JobQueue\Jobs;

use JobQueue\Jobs\Interfaces\JobInterface;
use Laminas\Log\Logger;
use Laminas\Mvc\Application;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;

class BaseJob implements JobInterface
{

    /** @var Application */
    protected $application;
    /** @var ServiceManager */
    protected $serviceManager;
    /** @var Logger logger */
    protected $logger;

    public function setUp()
    {
        // Retrieve configuration
        $appConfig = require __DIR__ . '/../../../../config/application.config.php';
        if (file_exists(__DIR__ . '/../../../../config/development.config.php')) {
            $appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/../../../../config/development.config.php');
        }

        // Prepare application and service manager
        $this->application = Application::init($appConfig);
        $this->serviceManager = $this->application->getServiceManager();

        // Load modules
        $this->serviceManager->get('ModuleManager')->loadModules();

        $this->logger = $this->serviceManager->get('Ocs_Log');
    }

    /**
     * @param $args
     */
    public function perform($args)
    {

    }

    public function tearDown()
    {
        // Remove environment for this job
    }

}