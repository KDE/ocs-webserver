<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use Laminas\Log\Formatter\Simple;
use Laminas\Log\Logger;
use Laminas\Log\LoggerAbstractServiceFactory;
use Laminas\Log\Processor\RequestId;

return [

    'module_layouts' => array(
        'Application' => 'layout/layout',
        'Statistic'   => 'layout/layout',
    ),

    'db' => array(
        'driver'         => 'Pdo',
        //'dsn' => 'mysql:dbname={db_name};host={hostname|ip_address}',
        //'username' => '{username}',
        //'password' => '{password}',
        'driver_options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''),

        'adapters' => array(
            'Statistic\Db\DwhAdapter' => array(
                'driver'         => 'pdo',
                'dsn'            => 'mysql:dbname={db_name};host={hostname|ip_address}',
                //'username'       => '{username}',
                //'password'       => '{password}',
                'driver_options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"),
            ),
        ),
    ),

    'cache' => array(
        'adapter' => array(
            'name'    => 'Memcached',
            'options' => array(
                'ttl' => 1800,
                //'servers' => ['1'=>['{hostname|ip_address}',11211]],
                //'namespace' => '{my_namespace}',
            ),
        ),
        'plugins' => array(
            'Serializer',
        ),
    ),

//@formatter:off

//    'cache' => array(
//        'adapter' => array(
//            'name'    => 'Memory',
//            'options' => array(
//                'ttl'       => 1800,
//                'namespace' => '{my_namespace}',
//            ),
//        ),
//        'plugins' => array(
//            'Serializer',
//        ),
//    ),

//    'cache'           => array(
//        'adapter' => array(
//            'name'    => 'filesystem',
//            'options' => array(
//                'cache_dir' => realpath(__DIR__ . '/../../data/cache'),
//                'dir_level' => 3,
//                'ttl'       => 1800,
//                'namespace' => '{my_namespace}',
//            ),
//        ),
//        'plugins' => array(
//            'Serializer'
//        ),
//    ),

//    'cache' => array(
//        'adapter' => array(
//            'name'    => 'Redis',
//            'options' => array(
//                'ttl' => 1800,
//                'server' => ['host'=>'{hostname|ip_address}', 'port'=>6379],
//                'namespace' => '{my_namespace}',
//            ),
//        ),
//        'plugins' => array(
//            'Serializer',
//        ),
//    ),

//@formatter:on
    'log'   => [ // <-- NOTE: key change!
        'Ocs_Log' => [
            'writers'    => [
                'stream' => [
                    'name'     => 'stream',
                    'priority' => 1,
                    'options'  => [
                        //'stream' => 'php://output',
                        //'stream' => 'php://stdout',
                        //'stream' => './data/log/application.log',
                        'stream'    => getenv('APPLICATION_LOGFILE') ? getenv('APPLICATION_LOGFILE') : realpath(__DIR__ . '/data/logs/application.log'),
                        'formatter' => [
                            'name'    => Simple::class,
                            'options' => [
                                'format'         => '%timestamp% %priorityName% (%priority%): %message% %extra%',
                                'dateTimeFormat' => 'c',
                            ],
                        ],
                        'filters'   => [
                            'priority' => [
                                'name'    => 'priority',
                                'options' => [
                                    'operator' => '<=',
                                    'priority' => Logger::INFO,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'processors' => [
                'requestid' => [
                    'name' => RequestId::class,
                ],
            ],
        ],
    ],

    'session_manager' => [
        'validators' => [
            // Laminas\Session\Validator\RemoteAddr::class, // removed until we solve the remote address issue with proxies from store.kde.org
            // Laminas\Session\Validator\HttpUserAgent::class, // removed because every browser update may break the validation
        ],
    ],

    // Session storage configuration.
    'session_storage' => [
        'type' => Laminas\Session\Storage\SessionArrayStorage::class,
    ],
    'session_config'  => [
        'options' => [
            'name'                => '{my_sessionname}',
            'cookie_lifetime'     => 2592000,
            'remember_me_seconds' => 31536000,
            'use_cookies'         => true,
            'cookie_httponly'     => true,
            'cookie_secure'       => true,
            'gc_maxlifetime'      => 2592000,
            // used for TTL ; For the memcached backend, there is a lifetime limit of 30 days (2592000 seconds)

            // for file storage
            'php_save_handler'    => 'files',
            'save_path'           => realpath(__DIR__ . '/../../data/sessions'),

            // for key-value stores like redis or memcached
            //'php_save_handler'    => '{name_for_php_save_handler}',
            //'save_path'           => '{hostname|id_address}:{port}',

            //'php_save_handler' => 'memcached',
            //'save_path' => '{hostname|id_address}:11211',

            //'php_save_handler'    => 'redis',
            //'save_path'           => 'tcp://{hostname|id_address}:6379',
        ],
    ],

    'service_manager' => array(
        'abstract_factories' => array(
            'Laminas\Db\Adapter\AdapterAbstractServiceFactory',
            // to allow other adapter to be called by $sm->get('dwh')
            LoggerAbstractServiceFactory::class,
            // we will be able to setup loggers via the configuration
        ),

        'factories' => array(
            'Laminas\Db\Adapter\Adapter' => Application\Model\Factory\DbAdapterFactory::class,
        ),
    ),

    // ocs application config
    'ocs_config'      => require __DIR__ . '/application.default.ini.php',

];
