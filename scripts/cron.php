#!/usr/bin/php -f
<?php
/**
 *  ocs-webserver
 *
 *  Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-webserver.
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

date_default_timezone_set('EST');

$time = microtime(true);
$memory = memory_get_usage();
set_time_limit(0);

// Define if APC extension is loaded
define('APC_EXTENSION_LOADED',
    extension_loaded('apc') && ini_get('apc.enabled') && (PHP_SAPI !== 'cli' || ini_get('apc.enable_cli')));

// Define if APC extension is loaded
define('MEMCACHED_EXTENSION_LOADED', extension_loaded('memcached'));

// Define if APC extension is loaded
define('MEMCACHE_EXTENSION_LOADED', extension_loaded('memcache'));

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

defined('APPLICATION_DATA')
|| define('APPLICATION_DATA', realpath(dirname(__FILE__) . '/../data'));

// Define path to application cache
defined('APPLICATION_CACHE')
|| define('APPLICATION_CACHE', realpath(dirname(__FILE__) . '/../data/cache/cli'));

// Define path to application library
defined('APPLICATION_LIB')
|| define('APPLICATION_LIB', realpath(dirname(__FILE__) . '/../library'));

// Define application environment
define('APPLICATION_ENV', 'cronjob');
define('CRONJOB_RUNNING', true);

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_LIB,
    get_include_path(),
)));

// Initialising Autoloader
require APPLICATION_LIB . '/Zend/Loader/SplAutoloader.php';
require APPLICATION_LIB . '/Zend/Loader/StandardAutoloader.php';
require APPLICATION_LIB . '/Zend/Loader/AutoloaderFactory.php';
Zend_Loader_AutoloaderFactory::factory(array(
    'Zend_Loader_StandardAutoloader' => array(
        'autoregister_zf' => true,
        'namespaces'      => array(
            'Application' => APPLICATION_PATH,
        )
    )
));

// Including plugin cache file
if (file_exists(APPLICATION_CACHE . DIRECTORY_SEPARATOR . 'pluginLoaderCache.php')) {
    include_once APPLICATION_CACHE . DIRECTORY_SEPARATOR . 'pluginLoaderCache.php';
}
Zend_Loader_PluginLoader::setIncludeFileCache(APPLICATION_CACHE . DIRECTORY_SEPARATOR . 'pluginLoaderCache.php');


// Set configuration
$configuration = APPLICATION_PATH . '/configs/application.ini';
// Merge an existing local configuration file (application.local.ini) with global config
if (file_exists(APPLICATION_PATH . '/configs/application.local.ini')) {
    $configuration = array(
        'config' => array(
            APPLICATION_PATH . '/configs/application.ini',
            APPLICATION_PATH . '/configs/application.local.ini'
        )
    );
}

// Create application, bootstrap, and run
//require_once APPLICATION_LIB . 'Zend/Application.php';
//$application = new Zend_Application(
//    APPLICATION_ENV,
//    $configuration
//);
require_once APPLICATION_LIB . '/Local/Application.php';
// Create application, bootstrap, and run
$application = new Local_Application(
    APPLICATION_ENV,
    $configuration
);

$application->bootstrap(array(
    'Autoload',
    'Config',
    'Cache',
    'Locale',
    'DbAdapter',
    'Logger',
    'Globals',
    'ThirdParty',
    'FrontController',
    'Modules',
    'Db'
));

$consoleOptions = new Zend_Console_Getopt(array(
    'action|a=s' => 'action to perform in format of "module/controller/action"',
    'help|h'     => 'displays usage information',
    'list|l'     => 'List available jobs',
));

try {
    $consoleOptions->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    // Bad options passed: report usage
    echo $e->getUsageMessage();
    return false;
}

if ($consoleOptions->getOption('l')) {
    // add help messages..
}

if ($consoleOptions->getOption('h')) {
    echo $consoleOptions->getUsageMessage();
    return true;
}

if ($consoleOptions->getOption('a')) {
    $front = $application->getBootstrap()->getResource('frontcontroller');

    $parameter = array_reverse(array_filter(explode('/', $consoleOptions->getOption('a'))));
    $module = array_pop($parameter);
    $controller = array_pop($parameter);
    $action = array_pop($parameter);

    $passParam = array();

    if (count($parameter)) {
        for ($i = 0; $i < count($parameter); $i = $i + 2) {
            $passParam[$parameter[$i + 1]] = $parameter[$i];
        }
    }

    $front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array(
        'module'     => $module,
        'controller' => $controller,
        'action'     => 'error'
    )));

    $request = new Zend_Controller_Request_Simple($action, $controller, $module, $passParam);

    $front->setRequest($request)
        ->setResponse(new Zend_Controller_Response_Cli())
        ->setRouter(new Local_Controller_Router_Cli());


    $application->run();


    $endTime = microtime(true);
    $endMemory = memory_get_usage();
    $runAtDate = new DateTime();

    echo 'Run At: ' . $runAtDate->format(DateTime::ISO8601) . ' Time [' . ($endTime - $time) . 's] Memory ['
        . number_format(($endMemory - $memory) / 1024) . 'Kb]' . PHP_EOL;
}