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
            'basePath' => realpath(dirname(__FILE__)),
        ));
        $autoloader->addResourceType('formelements', 'forms/elements', 'Form_Element');
        return $autoloader;
    }

    protected function _initSessionManagement()
    {
        // fallback procedure for default "session handler" on (maybe development) environments which have no memcached installed
        if (false === MEMCACHED_EXTENSION_LOADED) {
            return;
        }

        $config = $this->getOption('settings');
        if (APPLICATION_ENV != 'development') {
            $domain = $this->get_domain($_SERVER['SERVER_NAME']);
        } else {
            $domain = $_SERVER['SERVER_NAME'];
        }

        $_cache = new Zend_Cache_Backend_Libmemcached($config['session']['saveHandler']['options']);
        Zend_Loader::loadClass('Local_Session_Handler_Memcache');
        Zend_Session::setSaveHandler(new Local_Session_Handler_Memcache($_cache));
        Zend_Session::setOptions(array('cookie_domain' => $domain));
        Zend_Session::start();
    }

    /**
     * @param string $domain
     * @return bool|string
     */
    private function get_domain($domain)
    {
//        $pieces = parse_url($url);
//        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        return false;
    }

    protected function _initCache()
    {
        if (false === Zend_Registry::isRegistered('cache')) {
            return;
        }

        $cache = Zend_Registry::get('cache');
        Zend_Locale_Data::setCache($cache);
        Zend_Locale::setCache($cache);
        Zend_Currency::setCache($cache);
        Zend_Translate::setCache($cache);

        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

        return $cache;
    }

    protected function _initConfig()
    {
        if (Zend_Registry::isRegistered('cache')) {
            /** @var Zend_Cache_Core $cache */
            $cache = Zend_Registry::get('cache');

            if (false == ($config = $cache->load('application_config'))) {
                $config = new Zend_Config($this->getOptions(), true);
                $cache->save($config, 'application_config', array(), 14400);
            }
        } else {
            $config = new Zend_Config($this->getOptions(), true);
        }

        Zend_Registry::set('config', $config);
        return $config;
    }

    protected function _initViewConfig()
    {
        $view = $this->bootstrap('view')->getResource('view');

        $view->addHelperPath(APPLICATION_PATH . '/modules/default/views/helpers', 'Default_View_Helper_');
        $view->addHelperPath(APPLICATION_LIB . '/Zend/View/Helper', 'Zend_View_Helper_');

        $config = $this->getResource('config');

        $docType = $config->resources->view->doctype ? $config->resources->view->doctype : 'XHTML1_TRANSITIONAL';
        $view->doctype($docType);

        //$contentType = $config->resources->view->contentType ? $config->resources->view->contentType : 'text/html;charset=utf-8';
        //$view->headMeta()->appendHttpEquiv('Content-Type', $contentType);
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
        $cache = $this->getResource('cache');
        Zend_Translate::setCache($cache);
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

        $appConfig = $this->getResource('config');
        defined('APPLICATION_VERSION') ||
        define('APPLICATION_VERSION', $appConfig->version);
    }

    protected function _initAclRules()
    {
        $aclRules = new Default_Plugin_AclRules();
        Zend_Registry::set('acl', $aclRules);
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
        $front->registerPlugin(new Default_Plugin_Acl(Zend_Auth::getInstance(), $aclRules));

        $loader = new Zend_Loader_PluginLoader();
        $loader->addPrefixPath('Zend_View_Helper', APPLICATION_LIB . '/Zend/View/Helper/')
            ->addPrefixPath('Zend_Form_Element', APPLICATION_LIB . '/Zend/Form/Element')
            ->addPrefixPath('Default_View_Helper', APPLICATION_PATH . '/modules/default/views/helpers')
            ->addPrefixPath('Default_Form_Helper', APPLICATION_PATH . '/modules/default/forms/helpers')
            ->addPrefixPath('Default_Form_Element', APPLICATION_PATH . '/modules/default/forms/elements')
            ->addPrefixPath('Default_Form_Decorator', APPLICATION_PATH . '/modules/default/forms/decorators');
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

        $facebookConfig = $appConfig->third_party->facebook;
        defined('FACEBOOK_APP_ID') || define('FACEBOOK_APP_ID', $facebookConfig->app_id);
        defined('FACEBOOK_SECRET') || define('FACEBOOK_SECRET', $facebookConfig->secret);

        $twitterConfig = $appConfig->third_party->twitter->consumer;
        defined('TWITTER_CONSUMER_KEY') || define('TWITTER_CONSUMER_KEY', $twitterConfig->key);
        defined('TWITTER_CONSUMER_SECRET') || define('TWITTER_CONSUMER_SECRET', $twitterConfig->secret);

        $thingiverseConfig = $appConfig->third_party->thingiverse->consumer;
        defined('THINGIVERSE_CONSUMER_KEY') || define('THINGIVERSE_CONSUMER_KEY', $thingiverseConfig->key);
        defined('THINGIVERSE_CONSUMER_SECRET') || define('THINGIVERSE_CONSUMER_SECRET', $thingiverseConfig->secret);

        $imageConfig = $appConfig->images;
        defined('IMAGES_UPLOAD_PATH') || define('IMAGES_UPLOAD_PATH', $imageConfig->upload->path);
        defined('IMAGES_MEDIA_SERVER') || define('IMAGES_MEDIA_SERVER', $imageConfig->media->server);

        // ppload
        $pploadConfig = $appConfig->third_party->ppload;
        defined('PPLOAD_API_URI') || define('PPLOAD_API_URI', $pploadConfig->api_uri);
        defined('PPLOAD_CLIENT_ID') || define('PPLOAD_CLIENT_ID', $pploadConfig->client_id);
        defined('PPLOAD_SECRET') || define('PPLOAD_SECRET', $pploadConfig->secret);
    }

    protected function _initRouter()
    {
        $this->bootstrap('frontController');
        /** @var $front Zend_Controller_Front */
        $front = $this->getResource('frontController');

        /** @var $router Zend_Controller_Router_Rewrite */
        $router = $front->getRouter();


        /** RSS Feed */
        $router->addRoute(
            'rdf_store',
            new Zend_Controller_Router_Route(
                '/content.rdf',
                array(
                    'module' => 'default',
                    'controller' => 'rss',
                    'action' => 'rdf'
                )
            )
        );

        $router->addRoute(
            'rdf_events_hive',
            new Zend_Controller_Router_Route_Regex(
                '.*-events.rss',
                array(
                    'module' => 'default',
                    'controller' => 'rss',
                    'action' => 'rss'
                )
            )
        );

        $router->addRoute(
            'rdf_store_hive',
            new Zend_Controller_Router_Route_Regex(
                '.*-content.rdf',
                array(
                    'module' => 'default',
                    'controller' => 'rss',
                    'action' => 'rdf'
                )
            )
        );

        $router->addRoute(
            'rdf_store_hive_rss',
            new Zend_Controller_Router_Route_Regex(
                'rss/.*-content.rdf',
                array(
                    'module' => 'default',
                    'controller' => 'rss',
                    'action' => 'rdf'
                )
            )
        );

        /** new store dependent routing rules */
        $router->addRoute(
            'store_home',
            new Zend_Controller_Router_Route(
                '/s/:domain_store_id/',
                array(
                    'module' => 'default',
                    'controller' => 'home',
                    'action' => 'index'
                )
            )
        );

        $router->addRoute(
            'store_browse',
            new Zend_Controller_Router_Route(
                '/s/:domain_store_id/browse/*',
                array(
                    'module' => 'default',
                    'controller' => 'explore',
                    'action' => 'index'
                )
            )
        );

        $router->addRoute(
            'store_product',
            new Zend_Controller_Router_Route(
                '/s/:domain_store_id/p/:project_id/:action/*',
                array(
                    'module' => 'default',
                    'controller' => 'product',
                    'action' => 'show'
                )
            )
        );


        /** general routing rules */
        $router->addRoute(
            'home',
            new Zend_Controller_Router_Route(
                '/',
                array(
                    'module' => 'default',
                    'controller' => 'home',
                    'action' => 'index'
                )
            )
        );

        /** general routing rules */
        $router->addRoute(
            'home_ajax',
            new Zend_Controller_Router_Route(
                '/showfeatureajax/*',
                array(
                    'module' => 'default',
                    'controller' => 'home',
                    'action' => 'showfeatureajax'
                )
            )
        );

        $router->addRoute(
            'backend',
            new Zend_Controller_Router_Route(
                '/backend/:controller/:action/*',
                array(
                    'module' => 'backend',
                    'controller' => 'index',
                    'action' => 'index'
                )
            )
        );

        $router->addRoute(
            'browse',
            new Zend_Controller_Router_Route(
                '/browse/*',
                array(
                    'module' => 'default',
                    'controller' => 'explore',
                    'action' => 'index'
                )
            )
        );

        $router->addRoute(
            'button_render',
            new Zend_Controller_Router_Route(
                '/button/:project_id/:size/',
                array(
                    'module' => 'default',
                    'controller' => 'button',
                    'action' => 'render',
                    'size' => 'large'
                )
            )
        );

        $router->addRoute(
            'button_action',
            new Zend_Controller_Router_Route(
                '/button/a/:action/',
                array(
                    'module' => 'default',
                    'controller' => 'button',
                    'action' => 'index'
                )
            )
        );

        $router->addRoute(
            'supporter_box_show',
            new Zend_Controller_Router_Route(
                '/supporterbox/:project_uuid/',
                array(
                    'module' => 'default',
                    'controller' => 'supporterbox',
                    'action' => 'render'
                )
            )
        );

        $router->addRoute(
            'external_donation_list',
            new Zend_Controller_Router_Route(
                '/donationlist/:project_id/',
                array(
                    'module' => 'default',
                    'controller' => 'donationlist',
                    'action' => 'render'
                )
            )
        );

        $router->addRoute(
            'external_widget',
            new Zend_Controller_Router_Route(
                '/widget/:project_id/',
                array(
                    'module' => 'default',
                    'controller' => 'widget',
                    'action' => 'render'
                )
            )
        );

        $router->addRoute(
            'external_widget_save',
            new Zend_Controller_Router_Route(
                '/widget/save/*',
                array(
                    'module' => 'default',
                    'controller' => 'widget',
                    'action' => 'save'
                )
            )
        );

        $router->addRoute(
            'external_widget_save',
            new Zend_Controller_Router_Route(
                '/widget/config/:project_id/',
                array(
                    'module' => 'default',
                    'controller' => 'widget',
                    'action' => 'config'
                )
            )
        );

        $router->addRoute(
            'external_widget_save_default',
            new Zend_Controller_Router_Route(
                '/widget/savedefault/*',
                array(
                    'module' => 'default',
                    'controller' => 'widget',
                    'action' => 'savedefault'
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
                    'projecttitle' => '',
                    'module' => 'default',
                    'controller' => 'product',
                    'action' => 'show'
                )
            )
        );

        $router->addRoute(
            'product_referrer_url',
            new Zend_Controller_Router_Route(
                '/p/:project_id/er/:er/*',
                array(
                    'projecttitle' => '',
                    'module' => 'default',
                    'controller' => 'product',
                    'action' => 'show'
                )
            )
        );

        $router->addRoute(
            'product_add',
            new Zend_Controller_Router_Route(
                '/product/add',
                array(
                    'module' => 'default',
                    'controller' => 'product',
                    'action' => 'add'
                )
            )
        );

        $router->addRoute(
            'search',
            new Zend_Controller_Router_Route(
                '/search/*',
                array(
                    'module' => 'default',
                    'controller' => 'explore',
                    'action' => 'search'
                )
            )
        );

        $router->addRoute(
            'product_save',
            new Zend_Controller_Router_Route(
                '/p/save/*',
                array(
                    'module' => 'default',
                    'controller' => 'product',
                    'action' => 'saveproduct'
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
                    'module' => 'default',
                    'controller' => 'settings',
                    'action' => 'index'
                )
            )
        );

        $router->addRoute(
            'user_show',
            new Zend_Controller_Router_Route(
                '/member/:member_id/:action/*',
                array(
                    'module' => 'default',
                    'controller' => 'user',
                    'action' => 'index'
                )
            )
        );

        $router->addRoute(
            'register',
            new Zend_Controller_Router_Route_Static(
                '/register',
                array(
                    'module' => 'default',
                    'controller' => 'authorization',
                    'action' => 'register'
                )
            )
        );

        $router->addRoute(
            'register_validate',
            new Zend_Controller_Router_Route_Static(
                '/register/validate',
                array(
                    'module' => 'default',
                    'controller' => 'authorization',
                    'action' => 'validate'
                )
            )
        );

        $router->addRoute(
            'verification',
            new Zend_Controller_Router_Route(
                '/verification/:vid',
                array(
                    'module' => 'default',
                    'controller' => 'authorization',
                    'action' => 'verification'
                )
            )
        );

        $router->addRoute(
            'logout',
            new Zend_Controller_Router_Route_Static(
                '/logout',
                array(
                    'module' => 'default',
                    'controller' => 'authorization',
                    'action' => 'logout'
                )
            )
        );

        $router->addRoute(
            'login',
            new Zend_Controller_Router_Route(
                '/login',
                array(
                    'module' => 'default',
                    'controller' => 'authorization',
                    'action' => 'login'
                )
            )
        );

        $router->addRoute(
            'login',
            new Zend_Controller_Router_Route(
                '/login/:action/*',
                array(
                    'module' => 'default',
                    'controller' => 'authorization',
                    'action' => 'login'
                )
            )
        );

//        $router->addRoute(
//            'login_forgot',
//            new Zend_Controller_Router_Route(
//                '/login/forgot',
//                array(
//                    'module' => 'default',
//                    'controller' => 'authorization',
//                    'action' => 'forgot'
//                )
//            )
//        );
//
//        $router->addRoute(
//            'login_from_cookie',
//            new Zend_Controller_Router_Route(
//                '/login/lfc',
//                array(
//                    'module' => 'default',
//                    'controller' => 'authorization',
//                    'action' => 'lfc'
//                )
//            )
//        );

        $router->addRoute(
            'content',
            new Zend_Controller_Router_Route(
                '/content/:page',
                array(
                    'module' => 'default',
                    'controller' => 'content',
                    'action' => 'index'
                )
            )
        );

        $router->addRoute(
            'categories_about',
            new Zend_Controller_Router_Route(
                '/cat/:page/about',
                array(
                    'module' => 'default',
                    'controller' => 'categories',
                    'action' => 'about'
                )
            )
        );

        // **** static routes
        $router->addRoute(
            'static_faq',
            new Zend_Controller_Router_Route_Static(
                '/faq',
                array(
                    'module' => 'default',
                    'controller' => 'content',
                    'action' => 'index',
                    'page' => 'faq'
                )
            )
        );

        $router->addRoute(
            'static_terms',
            new Zend_Controller_Router_Route_Static(
                '/terms',
                array(
                    'module' => 'default',
                    'controller' => 'content',
                    'action' => 'index',
                    'page' => 'terms'
                )
            )
        );

        $router->addRoute(
            'static_terms_general',
            new Zend_Controller_Router_Route_Static(
                '/terms/general',
                array(
                    'module' => 'default',
                    'controller' => 'content',
                    'action' => 'index',
                    'page' => 'terms-general'
                )
            )
        );


        $router->addRoute(
            'static_terms_publish',
            new Zend_Controller_Router_Route_Static(
                '/terms/publishing',
                array(
                    'module' => 'default',
                    'controller' => 'content',
                    'action' => 'index',
                    'page' => 'terms-publishing'
                )
            )
        );

        $router->addRoute(
            'static_privacy',
            new Zend_Controller_Router_Route_Static(
                '/privacy',
                array(
                    'module' => 'default',
                    'controller' => 'content',
                    'action' => 'index',
                    'page' => 'privacy'
                )
            )
        );

        $router->addRoute(
            'static_contact',
            new Zend_Controller_Router_Route_Static(
                '/contact',
                array(
                    'module' => 'default',
                    'controller' => 'content',
                    'action' => 'index',
                    'page' => 'contact'
                )
            )
        );


        // **** ppload
        $router->addRoute(
            'pploadlogin',
            new Zend_Controller_Router_Route(
                '/pploadlogin/*',
                array(
                    'module' => 'default',
                    'controller' => 'authorization',
                    'action' => 'pploadlogin'
                )
            )
        );

        // OCS API
        $router->addRoute(
            'ocs_providers_xml',
            new Zend_Controller_Router_Route(
                '/ocs/providers.xml',
                array(
                    'module' => 'default',
                    'controller' => 'ocsv1',
                    'action' => 'providers'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_config',
            new Zend_Controller_Router_Route(
                '/ocs/v1/config',
                array(
                    'module' => 'default',
                    'controller' => 'ocsv1',
                    'action' => 'config'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_person_check',
            new Zend_Controller_Router_Route(
                '/ocs/v1/person/check',
                array(
                    'module' => 'default',
                    'controller' => 'ocsv1',
                    'action' => 'personcheck'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_person_data',
            new Zend_Controller_Router_Route(
                '/ocs/v1/person/data',
                array(
                    'module' => 'default',
                    'controller' => 'ocsv1',
                    'action' => 'persondata'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_person_data_personid',
            new Zend_Controller_Router_Route(
                '/ocs/v1/person/data/:personid',
                array(
                    'module' => 'default',
                    'controller' => 'ocsv1',
                    'action' => 'persondata'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_person_self',
            new Zend_Controller_Router_Route(
                '/ocs/v1/person/self',
                array(
                    'module' => 'default',
                    'controller' => 'ocsv1',
                    'action' => 'personself'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_content_categories',
            new Zend_Controller_Router_Route(
                '/ocs/v1/content/categories',
                array(
                    'module' => 'default',
                    'controller' => 'ocsv1',
                    'action' => 'contentcategories'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_content_data',
            new Zend_Controller_Router_Route(
                '/ocs/v1/content/data',
                array(
                    'module' => 'default',
                    'controller' => 'ocsv1',
                    'action' => 'contentdata'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_content_data_contentid',
            new Zend_Controller_Router_Route(
                '/ocs/v1/content/data/:contentid',
                array(
                    'module' => 'default',
                    'controller' => 'ocsv1',
                    'action' => 'contentdata'
                )
            )
        );
        $router->addRoute(
            'ocs_v1_content_download_contentid_itemid',
            new Zend_Controller_Router_Route(
                '/ocs/v1/content/download/:contentid/:itemid',
                array(
                    'module' => 'default',
                    'controller' => 'ocsv1',
                    'action' => 'contentdownload'
                )
            )
        );
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
