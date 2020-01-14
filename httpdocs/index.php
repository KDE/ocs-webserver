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
|| define('APPLICATION_CACHE', realpath(dirname(__FILE__) . '/../data/cache'));

// Define path to application library
defined('APPLICATION_LIB')
|| define('APPLICATION_LIB', realpath(dirname(__FILE__) . '/../library'));

defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

require APPLICATION_LIB . '/Local/CrawlerDetect.php';
if (crawlerDetect($_SERVER['HTTP_USER_AGENT'])) {
    define('SEARCHBOT_DETECTED', true);
} else {
    define('SEARCHBOT_DETECTED', false);
}

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

// Init and start Zend_Application
require_once APPLICATION_LIB . '/Local/Application.php';
// Create application, bootstrap, and run
$application = new Local_Application(APPLICATION_ENV, $configuration);
$application->bootstrap()->run();