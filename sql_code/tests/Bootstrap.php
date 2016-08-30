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
define('APC_EXTENSION_LOADED', extension_loaded('apc') && ini_get('apc.enabled') && (PHP_SAPI !== 'cli' || ini_get('apc.enable_cli')));

// Define if APC extension is loaded
define('MEMCACHED_EXTENSION_LOADED', extension_loaded('memcached') );

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define path to application library
defined('APPLICATION_LIB')
|| define('APPLICATION_LIB', realpath(dirname(__FILE__) . '/../library'));

// Define application environment
define('APPLICATION_ENV', 'testing');

defined('TEST_PATH')
|| define('TEST_PATH', realpath(dirname(__FILE__) . '/../tests'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    '.',
    APPLICATION_LIB,
    get_include_path(),
)));


require_once 'Zend/Loader/Autoloader.php';
require_once 'Zend/Loader/Autoloader/Interface.php';
require_once 'Zend/Loader/Autoloader/Resource.php';
require_once 'Zend/Application/Module/Autoloader.php';
$backendAutoloader = new Zend_Application_Module_Autoloader(array(
    'namespace' => 'Backend',
    'basePath' => APPLICATION_PATH . '/modules/backend',
));
$backendAutoloader
    ->addResourceType('element', 'forms/elements', 'Form_Element')
    ->addResourceType('other', 'forms/other', 'Form_Other')
    ->addResourceType('commands', 'library/backend/commands', 'Commands')
;

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setAutoloaders(array(
    new Zend_Application_Module_Autoloader(array(
        'namespace' => 'Default',
        'basePath' => APPLICATION_PATH . '/modules/default',
    )),
    new Zend_Application_Module_Autoloader(array(
        'namespace' => 'Backend',
        'basePath' => APPLICATION_PATH . '/modules/backend',
    ))
));

$frontendOptions = array(
    'automatic_serialization' => true
);
// Settings for some development environments. If there is no APC installed.
if (APC_EXTENSION_LOADED) {

    $backendOptions = array();

    $cache = Zend_Cache::factory(
        'Core',
        'Apc',
        $frontendOptions,
        $backendOptions
    );

} else {
    $cacheDir = realpath(APPLICATION_PATH . '/../data/cache/tests');

//    if (!is_dir($cacheDir)) mkdir($cacheDir, 0755);
//    if (file_exists($cacheDir . '/pluginLoaderCache.php')) unlink($cacheDir . '/pluginLoaderCache.php');

    $backendOptions = array(
        'cache_dir' => $cacheDir
    );

    $cache = Zend_Cache::factory(
        'Core',
        'File',
        $frontendOptions,
        $backendOptions
    );

}

Zend_Registry::set('cache', $cache);


if (file_exists(APPLICATION_PATH . '/configs/application.local.ini')) {
    $configuration = array('config' => array(APPLICATION_PATH . '/configs/application.ini',
        APPLICATION_PATH . '/configs/application.local.ini'));
} else {
    $configuration = APPLICATION_PATH . '/configs/application.ini';
}

Zend_Registry::set('configuration', $configuration);