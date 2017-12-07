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
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Default',
            'basePath'  => realpath(dirname(__FILE__)),
        ));
        $autoloader->addResourceType('formelements', 'forms/elements', 'Form_Element');
        $autoloader->addResourceType('formvalidators', 'forms/validators', 'Form_Validator');
        return $autoloader;
    }

    protected function _initSessionManagement()
    {
        $config = $this->getOption('settings');
        if ($config['session']['saveHandler']['replace']['enabled'] == false) {
            return;
        }

        if (APPLICATION_ENV != 'development') {
            $domain = $this->get_domain($_SERVER['SERVER_NAME']);
        } else {
            $domain = $_SERVER['SERVER_NAME'];
        }

        $cacheClass = 'Zend_Cache_Backend_'.$config['session']['saveHandler']['cache']['type'];
        $_cache = new $cacheClass($config['session']['saveHandler']['options']);
        Zend_Loader::loadClass($config['session']['saveHandler']['class']);
        Zend_Session::setSaveHandler(new $config['session']['saveHandler']['class']($_cache));
        Zend_Session::setOptions(array('cookie_domain' => $domain));
        Zend_Session::start();
    }

    /**
     * @param string $domain
     *
     * @return bool|string
     */
    private function get_domain($domain)
    {
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        return false;
    }

    protected function _initConfig()
    {
        /** $config Zend_Config */
        $config = $this->getApplication()->getApplicationConfig();
        Zend_Registry::set('config', $config);
        return $config;
    }

    protected function _initCache()
    {
        if (Zend_Registry::isRegistered('cache')) {
            return Zend_Registry::get('cache');
        }

        $cache = null;
        $options = $this->getOption('settings');

        if (true == $options['cache']['enabled']) {
            $cache = Zend_Cache::factory(
                $options['cache']['frontend']['type'],
                $options['cache']['backend']['type'],
                $options['cache']['frontend']['options'],
                $options['cache']['backend']['options']
            );
        } else {
            // Fallback settings for some (maybe development) environments which have no cache management installed.

            if (false === is_writeable(APPLICATION_CACHE)) {
                error_log('directory for cache files does not exists or not writable: ' . APPLICATION_CACHE);
                exit('directory for cache files does not exists or not writable: ' . APPLICATION_CACHE);
            }

            $frontendOptions = array(
                'lifetime'                => 600,
                'automatic_serialization' => true,
                'cache_id_prefix'         => 'front_cache',
                'cache'                   => true
            );

            $backendOptions = array(
                'cache_dir'              => APPLICATION_CACHE,
                'file_locking'           => true,
                'read_control'           => true,
                'read_control_type'      => 'crc32',
                'hashed_directory_level' => 1,
                'hashed_directory_perm'  => 0700,
                'file_name_prefix'       => 'ocs',
                'cache_file_perm'        => 0700
            );

            $cache = Zend_Cache::factory(
                'Core',
                'File',
                $frontendOptions,
                $backendOptions
            );
        }

        Zend_Registry::set('cache', $cache);

        Zend_Locale::setCache($cache);
        Zend_Locale_Data::setCache($cache);
        Zend_Currency::setCache($cache);
        Zend_Translate::setCache($cache);
        Zend_Translate_Adapter::setCache($cache);
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
        Zend_Paginator::setCache($cache);

        return $cache;
    }

    protected function _initViewConfig()
    {
        $view = $this->bootstrap('view')->getResource('view');

        $view->addHelperPath(APPLICATION_PATH . '/modules/default/views/helpers', 'Default_View_Helper_');
        $view->addHelperPath(APPLICATION_LIB . '/Zend/View/Helper', 'Zend_View_Helper_');

        $options = $this->getOptions();

        $docType = $options['resources']['view']['doctype'] ? $options['resources']['view']['doctype'] : 'XHTML1_TRANSITIONAL';
        $view->doctype($docType);
    }

    protected function _initLocale()
    {
        $configResources = $this->getOption('resources');
        Zend_Locale::setDefault($configResources['locale']['default']);
        Zend_Registry::set($configResources['locale']['registry_key'], $configResources['locale']['default']);
    }

    protected function _initTranslate()
    {
        $options = $this->getOption('resources');
        $options = $options['translate'];
        if (!isset($options['data'])) {
            throw new Zend_Application_Resource_Exception(
                'not found the file');
        }
        $adapter = isset($options['adapter']) ? $options['adapter'] : Zend_Translate::AN_ARRAY;
        $session = new Zend_Session_Namespace('aa');
        if ($session->locale) {
            $locale = $session->locale;
        } else {
            $locale = isset($options['locale']) ? $options['locale'] : null;
        }
        $data = '';
        if (isset($options['data'][$locale])) {
            $data = $options['data'][$locale];
        }
        $translateOptions = isset($options['options']) ? $options['options'] : array();
        $translate = new Zend_Translate($adapter, $data, $locale, $translateOptions);
        Zend_Form::setDefaultTranslator($translate);
        Zend_Validate_Abstract::setDefaultTranslator($translate);
        Zend_Registry::set('Zend_Translate', $translate);
        return $translate;
    }

    protected function _initDbAdapter()
    {
        $db = $this->bootstrap('db')->getResource('db');

//(        if ((APPLICATION_ENV == 'development') OR (APPLICATION_ENV == 'testing')) {
//            $profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
//            $profiler->setEnabled(true);
//
//            // Attach the profiler to your db adapter
//            $db->setProfiler($profiler);
//        }

        Zend_Registry::set('db', $db);
        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }

    protected function _initLogger()
    {
        $settings = $this->getOption('settings');
        $log = new Zend_Log();

        $writer = new Zend_Log_Writer_Stream($settings['log']['path'] . 'all_' . date("Y-m-d"));
        $writer->addFilter(new Local_Log_Filter_MinMax(Zend_Log::WARN, Zend_Log::INFO));

        $log->addWriter($writer);

        $errorWriter = new Zend_Log_Writer_Stream($settings['log']['path'] . 'err_' . date('Y-m-d'));
        $errorWriter->addFilter(new Zend_Log_Filter_Priority(Zend_Log::ERR));

        $log->addWriter($errorWriter);

        Zend_Registry::set('logger', $log);

        if ((APPLICATION_ENV == 'development') OR (APPLICATION_ENV == 'testing')) {
            $firebugWriter = new Zend_Log_Writer_Firebug();
            $firebugLog = new Zend_Log($firebugWriter);
            Zend_Registry::set('firebug_log', $firebugLog);
        }
    }

    protected function _initGlobals()
    {
        Zend_Paginator::setDefaultScrollingStyle('Elastic');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('paginationControl.phtml');

        Zend_Filter::addDefaultNamespaces('Local_Filter');

        $version = $this->getOption('version');
        defined('APPLICATION_VERSION') || define('APPLICATION_VERSION', $version);
    }

    protected function _initAclRules()
    {
        /** @var Zend_Cache_Core $appCache */
        $appCache = $this->getResource('cache');

        if (false == ($aclRules = $appCache->load('AclRules'))) {
            $aclRules = new Default_Plugin_AclRules();
            Zend_Registry::set('acl', $aclRules);
            $appCache->save($aclRules, 'AclRules', array('AclRules'), 14400);
        }
        return $aclRules;
    }

    protected function _initPlugins()
    {
        /** @var $front Zend_Controller_Front */
        $front = $this->bootstrap('frontController')->getResource('frontController');
        $aclRules = $this->bootstrap('aclRules')->getResource('aclRules');

        $front->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $front->registerPlugin(new Default_Plugin_ErrorHandler());
        $front->registerPlugin(new Default_Plugin_RememberMe(Zend_Auth::getInstance()));
        $front->registerPlugin(new Default_Plugin_SignOn(Zend_Auth::getInstance()));
        $front->registerPlugin(new Default_Plugin_Acl(Zend_Auth::getInstance(), $aclRules));

        $loader = new Zend_Loader_PluginLoader();
        $loader->addPrefixPath('Zend_View_Helper', APPLICATION_LIB . '/Zend/View/Helper/')
            ->addPrefixPath('Zend_Form_Element', APPLICATION_LIB . '/Zend/Form/Element')
            ->addPrefixPath('Default_View_Helper', APPLICATION_PATH . '/modules/default/views/helpers')
            ->addPrefixPath('Default_Form_Helper', APPLICATION_PATH . '/modules/default/forms/helpers')
            ->addPrefixPath('Default_Form_Element', APPLICATION_PATH . '/modules/default/forms/elements')
            ->addPrefixPath('Default_Form_Decorator', APPLICATION_PATH . '/modules/default/forms/decorators')
            ->addPrefixPath('Default_Form_Validator', APPLICATION_PATH . '/modules/default/forms/validators');
    }

    protected function _initAuthSessionNamespace()
    {
        $config = $this->getResource('config');
        $auth_config = $config->settings->auth_session;

        $objSessionNamespace = new Zend_Session_Namespace($auth_config->name);
        $objSessionNamespace->setExpirationSeconds($auth_config->timeout);

        Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session($auth_config->name));
    }

    protected function _initThirdParty()
    {
        $appConfig = $this->getResource('config');

        $imageConfig = $appConfig->images;
        defined('IMAGES_UPLOAD_PATH') || define('IMAGES_UPLOAD_PATH', $imageConfig->upload->path);
        defined('IMAGES_MEDIA_SERVER') || define('IMAGES_MEDIA_SERVER', $imageConfig->media->server);

        // ppload
        $pploadConfig = $appConfig->third_party->ppload;
        defined('PPLOAD_API_URI') || define('PPLOAD_API_URI', $pploadConfig->api_uri);
        defined('PPLOAD_CLIENT_ID') || define('PPLOAD_CLIENT_ID', $pploadConfig->client_id);
        defined('PPLOAD_SECRET') || define('PPLOAD_SECRET', $pploadConfig->secret);
        defined('PPLOAD_DOWNLOAD_SECRET') || define('PPLOAD_DOWNLOAD_SECRET', $pploadConfig->download_secret);
    }

    protected function _initRouter()
    {
        $this->bootstrap('frontController');
        /** @var $front Zend_Controller_Front */
        $front = $this->getResource('frontController');


        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');

        if (($router = $cache->load('ProjectRouter'))) {
            $front->setRouter($router);
            return $router;
        }

        /** @var $router Zend_Controller_Router_Rewrite */
        $router = $front->getRouter();

        /** RSS Feed */
        $router->addRoute(
            'rdf_store',
            new Zend_Controller_Router_Route(
                '/content.rdf',
                array(
                    'module'     => 'default',
                    'controller' => 'rss',
                    'action'     => 'rdf'
                )
            )
        );

        $router->addRoute(
            'rdf_events_hive',
            new Zend_Controller_Router_Route_Regex(
                '.*-events.rss',
                array(
                    'module'     => 'default',
                    'controller' => 'rss',
                    'action'     => 'rss'
                )
            )
        );

        $router->addRoute(
            'rdf_store_hive',
            new Zend_Controller_Router_Route_Regex(
                '.*-content.rdf',
                array(
                    'module'     => 'default',
                    'controller' => 'rss',
                    'action'     => 'rdf'
                )
            )
        );

        $router->addRoute(
            'rdf_store_hive_rss',
            new Zend_Controller_Router_Route_Regex(
                'rss/.*-content.rdf',
                array(
                    'module'     => 'default',
                    'controller' => 'rss',
                    'action'     => 'rdf'
                )
            )
        );

        /** new store dependent routing rules */
        $router->addRoute(
            'store_home',
            new Zend_Controller_Router_Route(
                '/s/:domain_store_id/',
                array(
                    'module'     => 'default',
                    'controller' => 'home',
                    'action'     => 'index'
                )
            )
        );

        $router->addRoute(
            'store_browse',
            new Zend_Controller_Router_Route(
                '/s/:domain_store_id/browse/*',
                array(
                    'module'     => 'default',
                    'controller' => 'explore',
                    'action'     => 'index'
                )
            )
        );

        $router->addRoute(
            'store_product',
            new Zend_Controller_Router_Route(
                '/s/:domain_store_id/p/:project_id/:action/*',
                array(
                    'module'     => 'default',
                    'controller' => 'product',
                    'action'     => 'show'
                )
            )
        );

        $router->addRoute(
            'store_user',
            new Zend_Controller_Router_Route(
                '/s/:domain_store_id/member/:member_id/:action/*',
                array(
                    'module'     => 'default',
                    'controller' => 'user',
                    'action'     => 'index'
                )
            )
        );


        /** general routing rules */
        $router->addRoute(
            'home',
            new Zend_Controller_Router_Route(
                '/',
                array(
                    'module'     => 'default',
                    'controller' => 'home',
                    'action'     => 'index'
                )
            )
        );

        $router->addRoute(
            'home_ajax',
            new Zend_Controller_Router_Route(
                '/showfeatureajax/*',
                array(
                    'module'     => 'default',
                    'controller' => 'home',
                    'action'     => 'showfeatureajax'
                )
            )
        );

        $router->addRoute(
            'backend',
            new Zend_Controller_Router_Route(
                '/backend/:controller/:action/*',
                array(
                    'module'     => 'backend',
                    'controller' => 'index',
                    'action'     => 'index'
                )
            )
        );

        $router->addRoute(
            'browse',
            new Zend_Controller_Router_Route(
                '/browse/*',
                array(
                    'module'     => 'default',
                    'controller' => 'explore',
                    'action'     => 'index'
                )
            )
        );

        $router->addRoute(
            'button_render',
            new Zend_Controller_Router_Route(
                '/button/:project_id/:size/',
                array(
                    'module'     => 'default',
                    'controller' => 'button',
                    'action'     => 'render',
                    'size'       => 'large'
                )
            )
        );

        $router->addRoute(
            'button_action',
            new Zend_Controller_Router_Route(
                '/button/a/:action/',
                array(
                    'module'     => 'default',
                    'controller' => 'button',
                    'action'     => 'index'
                )
            )
        );

        $router->addRoute(
            'supporter_box_show',
            new Zend_Controller_Router_Route(
                '/supporterbox/:project_uuid/',
                array(
                    'module'     => 'default',
                    'controller' => 'supporterbox',
                    'action'     => 'render'
                )
            )
        );

        $router->addRoute(
            'external_donation_list',
            new Zend_Controller_Router_Route(
                '/donationlist/:project_id/',
                array(
                    'module'     => 'default',
                    'controller' => 'donationlist',
                    'action'     => 'render'
                )
            )
        );

        $router->addRoute(
            'external_widget',
            new Zend_Controller_Router_Route(
                '/widget/:project_id/',
                array(
                    'module'     => 'default',
                    'controller' => 'widget',
                    'action'     => 'render'
                )
            )
        );

        $router->addRoute(
            'external_widget_save',
            new Zend_Controller_Router_Route(
                '/widget/save/*',
                array(
                    'module'     => 'default',
                    'controller' => 'widget',
                    'action'     => 'save'
                )
            )
        );

        $router->addRoute(
            'external_widget_save',
            new Zend_Controller_Router_Route(
                '/widget/config/:project_id/',
                array(
                    'module'     => 'default',
                    'controller' => 'widget',
                    'action'     => 'config'
                )
            )
        );

        $router->addRoute(
            'external_widget_save_default',
            new Zend_Controller_Router_Route(
                '/widget/savedefault/*',
                array(
                    'module'     => 'default',
                    'controller' => 'widget',
                    'action'     => 'savedefault'
                )
            )
        );

        /**
         * Project/Product
         */
        $router->addRoute(
            'product_short_url',
            new Zend_Controller_Router_Route(
                '/p/:project_id/:action/*',
                array(
                    'module'       => 'default',
                    'controller'   => 'product',
                    'action'       => 'show'
                )
            )
        );

        $router->addRoute(
            'product_referrer_url',
            new Zend_Controller_Router_Route(
                '/p/:project_id/er/:er/*',
                array(
                    'module'       => 'default',
                    'controller'   => 'product',
                    'action'       => 'show'
                )
            )
        );
        
        $router->addRoute(
            'product_collectionid_url',
            new Zend_Controller_Router_Route(
                '/c/:collection_id',
                array(
                    'module'       => 'default',
                    'controller'   => 'product',
                    'action'       => 'show'
                )
            )
        );

        $router->addRoute(
            'product_add',
            new Zend_Controller_Router_Route(
                '/product/add',
                array(
                    'module'     => 'default',
                    'controller' => 'product',
                    'action'     => 'add'
                )
            )
        );

        $router->addRoute(
            'search',
            new Zend_Controller_Router_Route(
                '/search/*',
                array(
                    'module'     => 'default',
                    'controller' => 'product',
                    'action'     => 'search'
                )
            )
        );

        $router->addRoute(
            'product_save',
            new Zend_Controller_Router_Route(
                '/p/save/*',
                array(
                    'module'     => 'default',
                    'controller' => 'product',
                    'action'     => 'saveproduct'
                )
            )
        );


        /**
         * Member
         */
        $router->addRoute(
            'member_settings_old',
            new Zend_Controller_Router_Route(
                '/settings/:action/*',
                array(
                    'module'     => 'default',
                    'controller' => 'settings',
                    'action'     => 'index'
                )
            )
        );

        $router->addRoute(
            'user_show',
            new Zend_Controller_Router_Route(
                '/member/:member_id/:action/*',
                array(
                    'module'     => 'default',
                    'controller' => 'user',
                    'action'     => 'index'
                )
            )
        );

        $router->addRoute(
            'user_show_short',
            new Zend_Controller_Router_Route(
                '/me/:member_id/:action/*',
                array(
                    'module'     => 'default',
                    'controller' => 'user',
                    'action'     => 'index'
                )
            )
        );

        $router->addRoute(
            'register',
            new Zend_Controller_Router_Route_Static(
                '/register',
                array(
                    'module'     => 'default',
                    'controller' => 'authorization',
                    'action'     => 'register'
                )
            )
        );

        $router->addRoute(
            'register_validate',
            new Zend_Controller_Router_Route_Static(
                '/register/validate',
                array(
                    'module'     => 'default',
                    'controller' => 'authorization',
                    'action'     => 'validate'
                )
            )
        );

        $router->addRoute(
            'verification',
            new Zend_Controller_Router_Route(
                '/verification/:vid',
                array(
                    'module'     => 'default',
                    'controller' => 'authorization',
                    'action'     => 'verification'
                )
            )
        );

        $router->addRoute(
            'logout',
            new Zend_Controller_Router_Route_Static(
                '/logout',
                array(
                    'module'     => 'default',
                    'controller' => 'authorization',
                    'action'     => 'logout'
                )
            )
        );

        $router->addRoute(
            'propagatelogout',
            new Zend_Controller_Router_Route_Static(
                '/logout/propagate',
                array(
                    'module'     => 'default',
                    'controller' => 'authorization',
                    'action'     => 'propagatelogout'
                )
            )
        );

        $router->addRoute(
            'login',
            new Zend_Controller_Router_Route(
                '/login',
                array(
                    'module'     => 'default',
                    'controller' => 'authorization',
                    'action'     => 'login'
                )
            )
        );

        $router->addRoute(
            'login',
            new Zend_Controller_Router_Route(
                '/login/:action/*',
                array(
                    'module'     => 'default',
                    'controller' => 'authorization',
                    'action'     => 'login'
                )
            )
        );

        $router->addRoute(
            'content',
            new Zend_Controller_Router_Route(
                '/content/:page',
                array(
                    'module'     => 'default',
                    'controller' => 'content',
                    'action'     => 'index'
                )
            )
        );

        $router->addRoute(
            'categories_about',
            new Zend_Controller_Router_Route(
                '/cat/:page/about',
                array(
                    'module'     => 'default',
                    'controller' => 'categories',
                    'action'     => 'about'
                )
            )
        );

        // **** static routes
        $router->addRoute(
            'static_faq',
            new Zend_Controller_Router_Route_Static(
                '/faq',
                array(
                    'module'     => 'default',
                    'controller' => 'content',
                    'action'     => 'index',
                    'page'       => 'faq'
                )
            )
        );

        $router->addRoute(
            'static_terms',
            new Zend_Controller_Router_Route_Static(
                '/terms',
                array(
                    'module'     => 'default',
                    'controller' => 'content',
                    'action'     => 'index',
                    'page'       => 'terms'
                )
            )
        );

        $router->addRoute(
            'static_terms_general',
            new Zend_Controller_Router_Route_Static(
                '/terms/general',
                array(
                    'module'     => 'default',
                    'controller' => 'content',
                    'action'     => 'index',
                    'page'       => 'terms-general'
                )
            )
        );


        $router->addRoute(
            'static_terms_publish',
            new Zend_Controller_Router_Route_Static(
                '/terms/publishing',
                array(
                    'module'     => 'default',
                    'controller' => 'content',
                    'action'     => 'index',
                    'page'       => 'terms-publishing'
                )
            )
        );
        
        $router->addRoute(
            'static_terms_dmca',
            new Zend_Controller_Router_Route_Static(
                '/terms/dmca',
                array(
                    'module'     => 'default',
                    'controller' => 'content',
                    'action'     => 'index',
                    'page'       => 'terms-dmca'
                )
            )
        );

        $router->addRoute(
            'static_privacy',
            new Zend_Controller_Router_Route_Static(
                '/privacy',
                array(
                    'module'     => 'default',
                    'controller' => 'content',
                    'action'     => 'index',
                    'page'       => 'privacy'
                )
            )
        );

        $router->addRoute(
            'static_contact',
            new Zend_Controller_Router_Route_Static(
                '/contact',
                array(
                    'module'     => 'default',
                    'controller' => 'content',
                    'action'     => 'index',
                    'page'       => 'contact'
                )
            )
        );


        // **** ppload
        $router->addRoute(
            'pploadlogin',
            new Zend_Controller_Router_Route(
                '/pploadlogin/*',
                array(
                    'module'     => 'default',
                    'controller' => 'authorization',
                    'action'     => 'pploadlogin'
                )
            )
        );

        // OCS API
        $router->addRoute(
            'ocs_providers_xml',
            new Zend_Controller_Router_Route(
                '/ocs/providers.xml',
                array(
                    'module'     => 'default',
                    'controller' => 'ocsv1',
                    'action'     => 'providers'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_config',
            new Zend_Controller_Router_Route(
                '/ocs/v1/config',
                array(
                    'module'     => 'default',
                    'controller' => 'ocsv1',
                    'action'     => 'config'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_person_check',
            new Zend_Controller_Router_Route(
                '/ocs/v1/person/check',
                array(
                    'module'     => 'default',
                    'controller' => 'ocsv1',
                    'action'     => 'personcheck'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_person_data',
            new Zend_Controller_Router_Route(
                '/ocs/v1/person/data',
                array(
                    'module'     => 'default',
                    'controller' => 'ocsv1',
                    'action'     => 'persondata'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_person_data_personid',
            new Zend_Controller_Router_Route(
                '/ocs/v1/person/data/:personid',
                array(
                    'module'     => 'default',
                    'controller' => 'ocsv1',
                    'action'     => 'persondata'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_person_self',
            new Zend_Controller_Router_Route(
                '/ocs/v1/person/self',
                array(
                    'module'     => 'default',
                    'controller' => 'ocsv1',
                    'action'     => 'personself'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_content_categories',
            new Zend_Controller_Router_Route(
                '/ocs/v1/content/categories',
                array(
                    'module'     => 'default',
                    'controller' => 'ocsv1',
                    'action'     => 'contentcategories'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_content_data_contentid',
            new Zend_Controller_Router_Route(
                '/ocs/v1/content/data/:contentid',
                array(
                    'module'     => 'default',
                    'controller' => 'ocsv1',
                    'action'     => 'contentdata',
                    'contentid'  => null
                )
            )
        );
        $router->addRoute(
            'ocs_v1_content_download_contentid_itemid',
            new Zend_Controller_Router_Route(
                '/ocs/v1/content/download/:contentid/:itemid',
                array(
                    'module'     => 'default',
                    'controller' => 'ocsv1',
                    'action'     => 'contentdownload'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_content_previewpic_contentid',
            new Zend_Controller_Router_Route(
                '/ocs/v1/content/previewpic/:contentid',
                array(
                    'module'     => 'default',
                    'controller' => 'ocsv1',
                    'action'     => 'contentpreviewpic'
                )
            )
        );
        $router->addRoute('ocs_v1_comments',
            new Zend_Controller_Router_Route(
                '/ocs/v1/comments/data/:comment_type/:content_id/:second_id',
                array(
                    'module'       => 'default',
                    'controller'   => 'ocsv1',
                    'action'       => 'comments',
                    'comment_type' => -1,
                    'content_id'   => null,
                    'second_id'    => null
                ))
            );

      
        // embed 
        $router->addRoute(
            'embed_v1_member_projects',
            new Zend_Controller_Router_Route(
                '/embed/v1/member/:memberid',
                array(
                    'module'     => 'default',
                    'controller' => 'embedv1',
                    'action'     => 'memberprojects'
                )
            )
        );

        $router->addRoute(
            'embed_v1_member_projects_files',
            new Zend_Controller_Router_Route(
                '/embed/v1/ppload/:ppload_collection_id',
                array(
                    'module'     => 'default',
                    'controller' => 'embedv1',
                    'action'     => 'ppload'
                )
            )
        );

        $router->addRoute(
            'embed_v1_member_projectscomments',
            new Zend_Controller_Router_Route(
                '/embed/v1/comments/:id',
                array(
                    'module'     => 'default',
                    'controller' => 'embedv1',
                    'action'     => 'comments'
                )
            )
        );

        $router->addRoute(
            'embed_v1_member_projectdetail',
            new Zend_Controller_Router_Route(
                '/embed/v1/project/:projectid',
                array(
                    'module'     => 'default',
                    'controller' => 'embedv1',
                    'action'     => 'projectdetail'
                )
            )
        );

        $cache->save($router, 'ProjectRouter', array('router'), 14400);
    }

    protected function _initCss()
    {
        if (APPLICATION_ENV != "development" && APPLICATION_ENV != "staging") {
            return;
        }

        $appConfig = $this->getResource('config');
        if ((boolean)$appConfig->settings->noLESScompile === true) {
            return;
        }

        $sLess = realpath(APPLICATION_PATH . '/../httpdocs/theme/flatui/less/stylesheet.less');
        $sCss = realpath(APPLICATION_PATH . '/../httpdocs/theme/flatui/css/stylesheet.css');

        /**
         * @var Zend_Cache_Core $cache
         */
        $cache = Zend_Registry::get('cache');
        if (md5_file($sLess) !== $cache->load('md5Less')) {
            require_once APPLICATION_PATH . "/../library/lessphp/lessc.inc.php";
            $oLessc = new lessc($sLess);
            $oLessc->setFormatter('compressed');
            file_put_contents($sCss, $oLessc->parse());
            $cache->save(md5_file($sLess), 'md5Less');
        }
    }

    protected function _initGlobalApplicationVars()
    {
        $modelDomainConfig = new Default_Model_DbTable_ConfigStore();
        Zend_Registry::set('application_store_category_list', $modelDomainConfig->fetchAllStoresAndCategories());
        Zend_Registry::set('application_store_config_list', $modelDomainConfig->fetchAllStoresConfigArray());
        Zend_Registry::set('application_store_config_id_list', $modelDomainConfig->fetchAllStoresConfigByIdArray());
    }

    protected function _initStoreDependentVars()
    {
        /** @var $front Zend_Controller_Front */
        $front = $this->bootstrap('frontController')->getResource('frontController');
        $front->registerPlugin(new Default_Plugin_InitGlobalStoreVars());
    }

//    protected function _initOsDependentVars()
//    {
//        $modelOsConfig = new Default_Model_DbTable_ConfigOperatingSystem();
//        Zend_Registry::set('application_os', $modelOsConfig->fetchOperatingSystems());
//    }

}
