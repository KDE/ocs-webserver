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

use Backend\Console\CMatrixCliCommand;
use Backend\Console\DownloadStatCliCommand;
use Backend\Console\MemberPayoutCliCommand;
use Backend\Console\RssCliCommand;
use Symfony\Component\Console\Application;
use Laminas\Stdlib\ArrayUtils;

chdir(dirname(__DIR__));

require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoloader

// Prepare application and service manager
$appConfig = require __DIR__ . '/../config/application.config.php';
if (file_exists(__DIR__ . '/../config/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/../config/development.config.php');
}

$application = Laminas\Mvc\Application::init($appConfig);
$serviceManager = $application->getServiceManager();

// Load modules
$serviceManager->get('ModuleManager')->loadModules();
$config = $serviceManager->get('config');
$routes = $config['console']['commands']; // This depends on your structure, this is what I created (see. 1.)

$application = new Application(
#    $config['app'],
#    $config['version'],
#    $routes,
#    Console::getInstance(),
#    new Dispatcher($serviceManager)  // Use service manager as a dependency injection container
);

$application->add(new MemberPayoutCliCommand());
$application->add(new RssCliCommand());
$application->add(new CMatrixCliCommand());

$exit = $application->run();
exit($exit);