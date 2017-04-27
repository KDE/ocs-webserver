<?php

// Define if APC extension is loaded
define('APC_EXTENSION_LOADED', extension_loaded('apc') && ini_get('apc.enabled') && (PHP_SAPI !== 'cli' || ini_get('apc.enable_cli')));

// Define if APC extension is loaded
define('MEMCACHED_EXTENSION_LOADED', extension_loaded('memcached'));

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define path to application library
defined('APPLICATION_LIB')
|| define('APPLICATION_LIB', realpath(dirname(__FILE__) . '/../library'));

// Define path to application cache
defined('APPLICATION_CACHE')
|| define('APPLICATION_CACHE', realpath(dirname(__FILE__) . '/../data/cache'));

defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

defined('APPLICATION_DATA')
|| define('APPLICATION_DATA', realpath(dirname(__FILE__) . '/../data'));

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
        'namespaces' => array(
            'Application' => APPLICATION_PATH,
        )
    )
));

// Including plugin cache file
if (file_exists(APPLICATION_CACHE . DIRECTORY_SEPARATOR . 'pluginLoaderCache.php')) {
    include_once APPLICATION_CACHE . DIRECTORY_SEPARATOR . 'pluginLoaderCache.php';
}
Zend_Loader_PluginLoader::setIncludeFileCache(APPLICATION_CACHE . DIRECTORY_SEPARATOR . 'pluginLoaderCache.php');


// Set cache options
$frontendOptions = array(
    'automatic_serialization' => true
);


if (MEMCACHED_EXTENSION_LOADED) {
    $frontendOpts = array(
        'caching' => true,
        'lifetime' => 1800,
        'automatic_serialization' => true
    );
  
    $backendOpts = array(
        'servers' =>array(
            array(
            'host' => 'localhost',
            'port' => 11211
            )
        ),
        'compression' => false
    );
    
    $cache = Zend_Cache::factory('Core', 'Memcached', $frontendOpts, $backendOpts);
    
} else if (APC_EXTENSION_LOADED) {
    $backendOptions = array();

    $cache = Zend_Cache::factory(
        'Core',
        'Apc',
        $frontendOptions,
        $backendOptions
    );
} else {
// Fallback settings for some (maybe development) environments with no installed APC.

    if (false === is_writeable(APPLICATION_CACHE)) {
        error_log('directory for cache files does not exists or not writable: ' . APPLICATION_CACHE);
        exit('directory for cache files does not exists or not writable: ' . APPLICATION_CACHE);
    }

    $backendOptions = array(
        'cache_dir' => APPLICATION_CACHE
    );

    $cache = Zend_Cache::factory(
        'Core',
        'File',
        $frontendOptions,
        $backendOptions
    );
}
Zend_Registry::set('cache', $cache);


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


//crawler detection
function crawlerDetect($USER_AGENT)
{
    $crawlers = array(
    array('Google', 'Google'),
    array('MSN', 'MSN'),
    array('msnbot-media', 'MSN'),
    array('bingbot', 'MSN'),
    array('MegaIndex.ru' , 'MegaIndex.ru'),
    array('Baiduspider', 'Baiduspider'),
    array('YandexBot', 'YandexBot'),
    array('AhrefsBot', 'AhrefsBot'),
    array('ltx71', 'ltx71'),
    array('msnbot', 'MSN'),
    array('Rambler', 'Rambler'),
    array('Yahoo', 'Yahoo'),
    array('AbachoBOT', 'AbachoBOT'),
    array('accoona', 'Accoona'),
    array('AcoiRobot', 'AcoiRobot'),
    array('ASPSeek', 'ASPSeek'),
    array('CrocCrawler', 'CrocCrawler'),
    array('Dumbot', 'Dumbot'),
    array('FAST-WebCrawler', 'FAST-WebCrawler'),
    array('GeonaBot', 'GeonaBot'),
    array('Gigabot', 'Gigabot'),
    array('Lycos', 'Lycos spider'),
    array('MSRBOT', 'MSRBOT'),
    array('Scooter', 'Altavista robot'),
    array('AltaVista', 'Altavista robot'),
    array('IDBot', 'ID-Search Bot'),
    array('eStyle', 'eStyle Bot'),
    array('Scrubby', 'Scrubby robot')
    );

    foreach ($crawlers as $c)
    {
        if (stristr($USER_AGENT, $c[0]))
        {
            return($c[1]);
        }
    }

    return false;
}






// Init and start Zend_Application
require_once APPLICATION_LIB . '/Local/Application.php';
// Create application, bootstrap, and run
$application = new Local_Application(
    APPLICATION_ENV,
    $configuration,
    $cache
);
$application->bootstrap()->run();