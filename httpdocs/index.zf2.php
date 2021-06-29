<?php

defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

defined('APPLICATION_LIB')
|| define('APPLICATION_LIB', realpath(dirname(__FILE__) . '/../library'));

use Laminas\Mvc\Application;
use Laminas\Stdlib\ArrayUtils;
use Library\CrawlerDetect;

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// Composer autoloading
include __DIR__ . '/../vendor/autoload.php';

if (!class_exists(Application::class)) {
    throw new RuntimeException(
        "Unable to load application.\n"
        . "- Type `composer install` if you are developing locally.\n"
        . "- Type `vagrant ssh -c 'composer install'` if you are using Vagrant.\n"
        . "- Type `docker-compose run zf composer install` if you are using Docker.\n"
    );
}

// Retrieve configuration
$appConfig = require __DIR__ . '/../config/application.config.php';
if (file_exists(__DIR__ . '/../config/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/../config/development.config.php');
}

if (false === isset($_SERVER['HTTP_USER_AGENT'])) {
    // if HTTP_USER_AGENT is not set, then we suspect it is a bot.
    define('SEARCHBOT_DETECTED', true);
} else if (CrawlerDetect::isCrawler($_SERVER['HTTP_USER_AGENT'])) {
    define('SEARCHBOT_DETECTED', true);
} else {
    define('SEARCHBOT_DETECTED', false);
}

if (file_exists(__DIR__ . "/announcement.php")) {
    $GLOBALS['announcement'] = include __DIR__ . "/announcement.php";
}

// Run the application!
Application::init($appConfig)->run();
