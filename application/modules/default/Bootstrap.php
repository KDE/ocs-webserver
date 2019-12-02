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

    /**
     * @return Zend_Application_Module_Autoloader
     * @throws Zend_Loader_Exception
     */
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

    /**
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     */
    protected function _initSessionManagement()
    {
        $config = $this->getOption('settings')['session'];

        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;

        if ($config['saveHandler']['replace']['enabled']) {
            $cacheClass = 'Zend_Cache_Backend_' . $config['saveHandler']['cache']['type'];
            $_cache = new $cacheClass($config['saveHandler']['options']);
            Zend_Loader::loadClass($config['saveHandler']['class']);
            Zend_Session::setSaveHandler(new $config['saveHandler']['class']($_cache));
            Zend_Session::setOptions(array(
                'cookie_domain'   => $domain,
                'cookie_path'     => $config['auth']['cookie_path'],
                'cookie_lifetime' => $config['auth']['cookie_lifetime'],
                'cookie_httponly' => $config['auth']['cookie_httponly']
            ));
            Zend_Session::start();
        }

        $session_namespace = new Zend_Session_Namespace($config['auth']['name']);
        $session_namespace->setExpirationSeconds($config['auth']['cookie_lifetime']);

        Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session($session_namespace->getNamespace()));
    }

    protected function _initConfig()
    {
        /** $config Zend_Config */
        $config = $this->getApplication()->getApplicationConfig();
        Zend_Registry::set('config', $config);

        return $config;
    }

    /**
     * @return mixed|null|Zend_Cache_Core|Zend_Cache_Frontend
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     */
    protected function _initCache()
    {
        if (Zend_Registry::isRegistered('cache')) {
            return Zend_Registry::get('cache');
        }

        $cache = null;
        $options = $this->getOption('settings');

        if (true == $options['cache']['enabled']) {
            $cache = Zend_Cache::factory($options['cache']['frontend']['type'], $options['cache']['backend']['type'],
                $options['cache']['frontend']['options'], $options['cache']['backend']['options']);
        } else {
            // Fallback settings for some (maybe development) environments which have no cache management installed.

            if (false === is_writeable(APPLICATION_CACHE)) {
                error_log('directory for cache files does not exists or not writable: ' . APPLICATION_CACHE);
                throw new Zend_Application_Bootstrap_Exception('directory for cache files does not exists or not writable: ' . APPLICATION_CACHE);
            }

            $frontendOptions = array(
                'lifetime'                => 600,
                'automatic_serialization' => true,
                'cache_id_prefix'         => $options['cache']['frontend']['options']['cache_id_prefix'],
                'cache'                   => true
            );

            $backendOptions = array(
                'cache_dir'              => APPLICATION_CACHE,
                'file_locking'           => true,
                'read_control'           => true,
                'read_control_type'      => 'crc32',
                'hashed_directory_level' => 1,
                'hashed_directory_perm'  => 0700,
                'file_name_prefix'       => $options['cache']['frontend']['options']['cache_id_prefix'],
                'cache_file_perm'        => 0700
            );

            $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
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

    /**
     * @throws Zend_Application_Bootstrap_Exception
     */
    protected function _initViewConfig()
    {
        $view = $this->bootstrap('view')->getResource('view');

        $view->addHelperPath(APPLICATION_PATH . '/modules/default/views/helpers', 'Default_View_Helper_');
        $view->addHelperPath(APPLICATION_LIB . '/Zend/View/Helper', 'Zend_View_Helper_');

        $options = $this->getOptions();

        $docType = $options['resources']['view']['doctype'] ? $options['resources']['view']['doctype'] : 'XHTML1_TRANSITIONAL';
        $view->doctype($docType);
    }

    /**
     * @throws Zend_Locale_Exception
     */
    protected function _initLocale()
    {
        $configResources = $this->getOption('resources');
        Zend_Locale::setDefault($configResources['locale']['default']);
        Zend_Registry::set($configResources['locale']['registry_key'], $configResources['locale']['default']);
    }

    /**
     * @return Zend_Translate
     * @throws Zend_Application_Resource_Exception
     * @throws Zend_Form_Exception
     * @throws Zend_Session_Exception
     * @throws Zend_Translate_Exception
     * @throws Zend_Validate_Exception
     */
    protected function _initTranslate()
    {
        $options = $this->getOption('resources');
        $options = $options['translate'];
        if (!isset($options['data'])) {
            throw new Zend_Application_Resource_Exception('not found the file');
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

    /**
     * @throws Zend_Application_Bootstrap_Exception
     */
    protected function _initDbAdapter()
    {
        $db = $this->bootstrap('db')->getResource('db');

        Zend_Registry::set('db', $db);
        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }

    /**
     * @throws Zend_Application_Bootstrap_Exception
     */
    protected function _initLogger()
    {
        /** @var Zend_Log $logger */
        $logger = $this->getPluginResource('log')->getLog();
        $logger->registerErrorHandler();
        Zend_Registry::set('logger', $logger);
    }

    protected function _initGlobals()
    {
        Zend_Paginator::setDefaultScrollingStyle('Elastic');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('paginationControl.phtml');

        Zend_Filter::addDefaultNamespaces('Local_Filter');

        $version = $this->getOption('version');
        defined('APPLICATION_VERSION') || define('APPLICATION_VERSION', $version);
    }

    /**
     * @return Default_Plugin_AclRules|false|mixed
     * @throws Zend_Cache_Exception
     */
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

    /**
     * @throws Zend_Application_Bootstrap_Exception
     * @throws Zend_Loader_PluginLoader_Exception
     */
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

    protected function _initThirdParty()
    {
        $appConfig = $this->getResource('config');

        $imageConfig = $appConfig->images;
        defined('IMAGES_UPLOAD_PATH') || define('IMAGES_UPLOAD_PATH', $imageConfig->upload->path);
        defined('IMAGES_MEDIA_SERVER') || define('IMAGES_MEDIA_SERVER', $imageConfig->media->server);
        $videoConfig = $appConfig->videos;
        defined('VIDEOS_UPLOAD_PATH') || define('VIDEOS_UPLOAD_PATH', $videoConfig->upload->path);
        defined('VIDEOS_MEDIA_SERVER') || define('VIDEOS_MEDIA_SERVER', $videoConfig->media->server);

        // fileserver
        $configFileserver = $appConfig->settings->server->files;
        defined('PPLOAD_API_URI') || define('PPLOAD_API_URI', $configFileserver->api->uri);
        defined('PPLOAD_CLIENT_ID') || define('PPLOAD_CLIENT_ID', $configFileserver->api->client_id);
        defined('PPLOAD_SECRET') || define('PPLOAD_SECRET', $configFileserver->api->client_secret);
        defined('PPLOAD_HOST') || define('PPLOAD_HOST', $configFileserver->host);
        defined('PPLOAD_DOWNLOAD_SECRET') || define('PPLOAD_DOWNLOAD_SECRET', $configFileserver->download_secret);
    }

    /**
     * @return false|mixed|Zend_Controller_Router_Rewrite
     * @throws Zend_Application_Bootstrap_Exception
     * @throws Zend_Cache_Exception
     * @throws Zend_Controller_Exception
     * @throws Zend_Exception
     */
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
        $router->addRoute('rdf_store', new Zend_Controller_Router_Route('/content.rdf', array(
            'module'     => 'default',
            'controller' => 'rss',
            'action'     => 'rdf'
        )));

        $router->addRoute('rdf_events_hive', new Zend_Controller_Router_Route_Regex('.*-events.rss', array(
            'module'     => 'default',
            'controller' => 'rss',
            'action'     => 'rss'
        )));

        $router->addRoute('rdf_store_hive', new Zend_Controller_Router_Route_Regex('.*-content.rdf', array(
            'module'     => 'default',
            'controller' => 'rss',
            'action'     => 'rdf'
        )));

        $router->addRoute('rdf_store_hive_rss', new Zend_Controller_Router_Route_Regex('rss/.*-content.rdf', array(
            'module'     => 'default',
            'controller' => 'rss',
            'action'     => 'rdf'
        )));

        /** new store dependent routing rules */
        //$router->addRoute('store_general', new Zend_Controller_Router_Route('/s/:domain_store_id/:controller/:action/*', array(
        //    'module'     => 'default',
        //    'controller' => 'explore',
        //    'action'     => 'index'
        //)));

        $router->addRoute('store_home', new Zend_Controller_Router_Route('/s/:domain_store_id/', array(
            'module'     => 'default',
            'controller' => 'home',
            'action'     => 'index'
        )));

        $router->addRoute('store_browse', new Zend_Controller_Router_Route('/s/:domain_store_id/browse/*', array(
            'module'     => 'default',
            'controller' => 'explore',
            'action'     => 'index'
        )));

        $router->addRoute('store_product_add',
            new Zend_Controller_Router_Route('/s/:domain_store_id/product/add', array(
                'module'     => 'default',
                'controller' => 'product',
                'action'     => 'add'
            )));

        $router->addRoute('store_settings', new Zend_Controller_Router_Route('/s/:domain_store_id/settings', array(
            'module'     => 'default',
            'controller' => 'settings',
            'action'     => 'index'
        )));

        $router->addRoute('store_pling_box_show',
            new Zend_Controller_Router_Route('/s/:domain_store_id/supporterbox/:memberid', array(
                'module'     => 'default',
                'controller' => 'plingbox',
                'action'     => 'index'
            )));

        $router->addRoute('store_pling_box_show',
            new Zend_Controller_Router_Route('/s/:domain_store_id/productcomment/addreply/*', array(
                'module'     => 'default',
                'controller' => 'productcomment',
                'action'     => 'addreply'
            )));

        $router->addRoute('store_product',
            new Zend_Controller_Router_Route('/s/:domain_store_id/p/:project_id/:action/*', array(
                'module'     => 'default',
                'controller' => 'product',
                'action'     => 'show'
            )));

        $router->addRoute('store_collection',
            new Zend_Controller_Router_Route('/s/:domain_store_id/c/:project_id/:action/*', array(
                'module'     => 'default',
                'controller' => 'collection',
                'action'     => 'show'
            )));

        /*
        $router->addRoute('store_product', new Zend_Controller_Router_Route('/s/:domain_store_id/c/:project_id/:action/*', array(
                    'module'     => 'default',
                    'controller' => 'collection',
                    'action'     => 'show'
                )));
        */
        $router->addRoute('store_user',
            new Zend_Controller_Router_Route('/s/:domain_store_id/member/:member_id/:action/*', array(
                'module'     => 'default',
                'controller' => 'user',
                'action'     => 'index'
            )));

        $router->addRoute('store_user_name',
            new Zend_Controller_Router_Route('/s/:domain_store_id/u/:user_name/:action/*', array(
                'module'     => 'default',
                'controller' => 'user',
                'action'     => 'index'
            )));

        $router->addRoute('store_login', new Zend_Controller_Router_Route('/s/:domain_store_id/login/*', array(
            'module'     => 'default',
            'controller' => 'authorization',
            'action'     => 'login'
        )));

        $router->addRoute('store_register', new Zend_Controller_Router_Route('/s/:domain_store_id/register', array(
            'module'     => 'default',
            'controller' => 'authorization',
            'action'     => 'register'
        )));


        /** general routing rules */
        $router->addRoute('home', new Zend_Controller_Router_Route('/', array(
            'module'     => 'default',
            'controller' => 'home',
            'action'     => 'index'
        )));


        $router->addRoute('home_home', new Zend_Controller_Router_Route('/home', array(
            'module'     => 'default',
            'controller' => 'home',
            'action'     => 'index'
        )));

        $router->addRoute('home_start', new Zend_Controller_Router_Route('/start', array(
            'module'     => 'default',
            'controller' => 'home',
            'action'     => 'start'
        )));

        $router->addRoute('home_ajax', new Zend_Controller_Router_Route('/showfeatureajax/*', array(
            'module'     => 'default',
            'controller' => 'home',
            'action'     => 'showfeatureajax'
        )));

        $router->addRoute('backend', new Zend_Controller_Router_Route('/backend/:controller/:action/*', array(
            'module'     => 'backend',
            'controller' => 'index',
            'action'     => 'index'
        )));

        $router->addRoute('backend_statistics', new Zend_Controller_Router_Route('/statistics/:action/*', array(
            'module'     => 'backend',
            'controller' => 'statistics',
            'action'     => 'index'
        )));

        $router->addRoute('browse', new Zend_Controller_Router_Route('/browse/*', array(
            'module'     => 'default',
            'controller' => 'explore',
            'action'     => 'index'
        )));

        $router->addRoute('browse_favourites', new Zend_Controller_Router_Route('/my-favourites/*', array(
            'module'     => 'default',
            'controller' => 'explore',
            'action'     => 'index',
            'fav'        => '1'
        )));

        $router->addRoute('button_render', new Zend_Controller_Router_Route('/button/:project_id/:size/', array(
            'module'     => 'default',
            'controller' => 'button',
            'action'     => 'render',
            'size'       => 'large'
        )));

        $router->addRoute('button_action', new Zend_Controller_Router_Route('/button/a/:action/', array(
            'module'     => 'default',
            'controller' => 'button',
            'action'     => 'index'
        )));

        $router->addRoute('pling_box_show', new Zend_Controller_Router_Route('/supporterbox/:memberid/', array(
            'module'     => 'default',
            'controller' => 'plingbox',
            'action'     => 'index'
        )));

        $router->addRoute('external_donation_list',
            new Zend_Controller_Router_Route('/donationlist/:project_id/', array(
                'module'     => 'default',
                'controller' => 'donationlist',
                'action'     => 'render'
            )));

        $router->addRoute('external_widget', new Zend_Controller_Router_Route('/widget/:project_id/', array(
            'module'     => 'default',
            'controller' => 'widget',
            'action'     => 'render'
        )));

        $router->addRoute('external_widget_save', new Zend_Controller_Router_Route('/widget/save/*', array(
            'module'     => 'default',
            'controller' => 'widget',
            'action'     => 'save'
        )));

        $router->addRoute('external_widget_save', new Zend_Controller_Router_Route('/widget/config/:project_id/', array(
            'module'     => 'default',
            'controller' => 'widget',
            'action'     => 'config'
        )));

        $router->addRoute('external_widget_save_default',
            new Zend_Controller_Router_Route('/widget/savedefault/*', array(
                'module'     => 'default',
                'controller' => 'widget',
                'action'     => 'savedefault'
            )));

        $router->addRoute('support_old', new Zend_Controller_Router_Route('/support-old', array(
            'module'     => 'default',
            'controller' => 'support',
            'action'     => 'index'
        )));

        $router->addRoute('support_old_pay', new Zend_Controller_Router_Route('/support-old/pay', array(
            'module'     => 'default',
            'controller' => 'support',
            'action'     => 'pay'
        )));

        $router->addRoute('support_old_paymentok', new Zend_Controller_Router_Route('/support-old/paymentok', array(
            'module'     => 'default',
            'controller' => 'support',
            'action'     => 'paymentok'
        )));


        $router->addRoute('support_old_paymentcancel',
            new Zend_Controller_Router_Route('/support-old/paymentcancel', array(
                'module'     => 'default',
                'controller' => 'support',
                'action'     => 'paymentcancel'
            )));

        $router->addRoute('samepaypal', new Zend_Controller_Router_Route('/samepaypal', array(
            'module'     => 'default',
            'controller' => 'spam',
            'action'     => 'paypal'
        )));

        $router->addRoute('support_new', new Zend_Controller_Router_Route('/supportold2', array(
            'module'     => 'default',
            'controller' => 'subscription',
            'action'     => 'index'
        )));
        
        $router->addRoute('support_predefined', new Zend_Controller_Router_Route('/support-predefined', array(
            'module'     => 'default',
            'controller' => 'subscription',
            'action'     => 'supportpredefinded'
        )));
        
        $router->addRoute('support_pay_predefined', new Zend_Controller_Router_Route('/support/paypredefined', array(
            'module'     => 'default',
            'controller' => 'subscription',
            'action'     => 'paypredefined'
        )));
        
        $router->addRoute('support_new2', new Zend_Controller_Router_Route('/support', array(
            'module'     => 'default',
            'controller' => 'subscription',
            'action'     => 'support2'
        )));
        
        $router->addRoute('support_new_pay2', new Zend_Controller_Router_Route('/support/pay', array(
            'module'     => 'default',
            'controller' => 'subscription',
            'action'     => 'pay2'
        )));


        $router->addRoute('support_new_pay', new Zend_Controller_Router_Route('/support/payold2', array(
            'module'     => 'default',
            'controller' => 'subscription',
            'action'     => 'pay'
        )));

        $router->addRoute('support_new_paymentok', new Zend_Controller_Router_Route('/support/paymentok', array(
            'module'     => 'default',
            'controller' => 'subscription',
            'action'     => 'paymentok'
        )));


        $router->addRoute('support_new_paymentcancel', new Zend_Controller_Router_Route('/support/paymentcancel', array(
            'module'     => 'default',
            'controller' => 'subscription',
            'action'     => 'paymentcancel'
        )));

        /**
         * Project/Product
         */
        $router->addRoute('product_short_url', new Zend_Controller_Router_Route('/p/:project_id/:action/*', array(
            'module'     => 'default',
            'controller' => 'product',
            'action'     => 'show'
        )));

        $router->addRoute('product_referrer_url', new Zend_Controller_Router_Route('/p/:project_id/er/:er/*', array(
            'module'     => 'default',
            'controller' => 'product',
            'action'     => 'show'
        )));

        $router->addRoute('product_collectionid_url', new Zend_Controller_Router_Route('/co/:collection_id', array(
            'module'     => 'default',
            'controller' => 'product',
            'action'     => 'show'
        )));

        $router->addRoute('product_add', new Zend_Controller_Router_Route('/product/add', array(
            'module'     => 'default',
            'controller' => 'product',
            'action'     => 'add'
        )));

        $router->addRoute('product_add_extend', new Zend_Controller_Router_Route('/product/add/:catId', array(
            'module'     => 'default',
            'controller' => 'product',
            'action'     => 'add'
        )));

        $router->addRoute('search', new Zend_Controller_Router_Route('/search/*', array(
            'module'     => 'default',
            'controller' => 'product',
            'action'     => 'search'
        )));

        $router->addRoute('search_domain', new Zend_Controller_Router_Route('/s/:domain_store_id/search/*',
            array(
                'module'     => 'default',
                'controller' => 'product',
                'action'     => 'search'
            )));

        $router->addRoute('product_save', new Zend_Controller_Router_Route('/p/save/*', array(
            'module'     => 'default',
            'controller' => 'product',
            'action'     => 'saveproduct'
        )));


        /**
         * Collection
         */
        $router->addRoute('collection_short_url', new Zend_Controller_Router_Route('/c/:project_id/', array(
            'module'     => 'default',
            'controller' => 'collection',
            'action'     => 'index'
        )));

        $router->addRoute('collection_short_url', new Zend_Controller_Router_Route('/c/:project_id/:action/*', array(
            'module'     => 'default',
            'controller' => 'collection',
            'action'     => 'index'
        )));

        $router->addRoute('collection_referrer_url', new Zend_Controller_Router_Route('/c/:project_id/er/:er/*', array(
            'module'     => 'default',
            'controller' => 'collection',
            'action'     => 'index'
        )));

        $router->addRoute('collection_add', new Zend_Controller_Router_Route('/collection/add', array(
            'module'     => 'default',
            'controller' => 'collection',
            'action'     => 'add'
        )));

        /**
         * $router->addRoute('search', new Zend_Controller_Router_Route('/search/*', array(
         * 'module'     => 'default',
         * 'controller' => 'collection',
         * 'action'     => 'search'
         * )));
         *
         * $router->addRoute('search_domain',new Zend_Controller_Router_Route('/s/:domain_store_id/search/*',
         * array(
         * 'module'     => 'default',
         * 'controller' => 'product',
         * 'action'     => 'search'
         * )));
         */
        $router->addRoute('collection_save', new Zend_Controller_Router_Route('/c/save/*', array(
            'module'     => 'default',
            'controller' => 'collection',
            'action'     => 'saveproduct'
        )));


        /**
         * Member
         */
        $router->addRoute('member_settings_old', new Zend_Controller_Router_Route('/settings/:action/*', array(
            'module'     => 'default',
            'controller' => 'settings',
            'action'     => 'index'
        )));

        $router->addRoute('user_show', new Zend_Controller_Router_Route('/member/:member_id/:action/*', array(
            'module'     => 'default',
            'controller' => 'user',
            'action'     => 'index'
        )));
        
        
        
        $router->addRoute('user_avatar', new Zend_Controller_Router_Route('/member/avatar/:emailhash/:size', array(
            'module'     => 'default',
            'controller' => 'user',
            'action'     => 'avatar'
        )));

        $router->addRoute('user_show_with_name', new Zend_Controller_Router_Route('/u/:user_name/:action/*', array(
            'module'     => 'default',
            'controller' => 'user',
            'action'     => 'index'
        )));
        
        $router->addRoute('user_recification', new Zend_Controller_Router_Route('/r/:action/*', array(
            'module'     => 'default',
            'controller' => 'rectification',
            'action'     => 'index'
        )));

        $router->addRoute('user_show_short', new Zend_Controller_Router_Route('/me/:member_id/:action/*', array(
            'module'     => 'default',
            'controller' => 'user',
            'action'     => 'index'
        )));

        $router->addRoute('register', new Zend_Controller_Router_Route_Static('/register', array(
            'module'     => 'default',
            'controller' => 'authorization',
            'action'     => 'register'
        )));

        $router->addRoute('register_validate', new Zend_Controller_Router_Route_Static('/register/validate', array(
            'module'     => 'default',
            'controller' => 'authorization',
            'action'     => 'validate'
        )));

        $router->addRoute('verification', new Zend_Controller_Router_Route('/verification/:vid', array(
            'module'     => 'default',
            'controller' => 'authorization',
            'action'     => 'verification'
        )));

        $router->addRoute('logout', new Zend_Controller_Router_Route_Static('/logout', array(
            'module'     => 'default',
            'controller' => 'logout',
            'action'     => 'logout'
        )));

        $router->addRoute('propagatelogout', new Zend_Controller_Router_Route_Static('/logout/propagate', array(
            'module'     => 'default',
            'controller' => 'authorization',
            'action'     => 'propagatelogout'
        )));

        $router->addRoute('checkuser', new Zend_Controller_Router_Route_Static('/checkuser', array(
            'module'     => 'default',
            'controller' => 'authorization',
            'action'     => 'checkuser'
        )));

        $router->addRoute('login', new Zend_Controller_Router_Route('/login', array(
            'module'     => 'default',
            'controller' => 'authorization',
            'action'     => 'login'
        )));

        $router->addRoute('login', new Zend_Controller_Router_Route('/login/:action/*', array(
            'module'     => 'default',
            'controller' => 'authorization',
            'action'     => 'login'
        )));

        $router->addRoute('LoginController', new Zend_Controller_Router_Route('/l/:action/*', array(
            'module'     => 'default',
            'controller' => 'login',
            'action'     => 'login'
        )));

        $router->addRoute('content', new Zend_Controller_Router_Route('/content/:page', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index'
        )));

        $router->addRoute('categories_about', new Zend_Controller_Router_Route('/cat/:page/about', array(
            'module'     => 'default',
            'controller' => 'categories',
            'action'     => 'about'
        )));

        // **** static routes
        $router->addRoute('static_faq_old', new Zend_Controller_Router_Route_Static('/faq-old', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'faqold'
        )));

        $router->addRoute('static_faq', new Zend_Controller_Router_Route_Static('/faq-pling', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'faq'
        )));

        $router->addRoute('static_gitfaq', new Zend_Controller_Router_Route_Static('/faq-opencode', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'gitfaq'
        )));
        $router->addRoute('static_ocsapi', new Zend_Controller_Router_Route_Static('/ocs-api', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'ocsapi'
        )));
        $router->addRoute('static_plings', new Zend_Controller_Router_Route_Static('/about', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'about'
        )));

        $router->addRoute('static_terms', new Zend_Controller_Router_Route_Static('/terms', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'terms'
        )));

        $router->addRoute('static_terms_general', new Zend_Controller_Router_Route_Static('/terms/general', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'terms-general'
        )));

        $router->addRoute('static_terms_publish', new Zend_Controller_Router_Route_Static('/terms/publishing', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'terms-publishing'
        )));

        $router->addRoute('static_terms_dmca', new Zend_Controller_Router_Route_Static('/terms/dmca', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'terms-dmca'
        )));

        $router->addRoute('static_terms_payout', new Zend_Controller_Router_Route_Static('/terms/payout', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'terms-payout'
        )));

        $router->addRoute('static_terms_cookies', new Zend_Controller_Router_Route_Static('/terms/cookies', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'terms-cookies'
        )));

        $router->addRoute('static_privacy', new Zend_Controller_Router_Route_Static('/privacy', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'privacy'
        )));

        $router->addRoute('static_imprint', new Zend_Controller_Router_Route_Static('/imprint', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'imprint'
        )));

        $router->addRoute('static_contact', new Zend_Controller_Router_Route_Static('/contact', array(
            'module'     => 'default',
            'controller' => 'content',
            'action'     => 'index',
            'page'       => 'contact'
        )));

        // **** ppload
        $router->addRoute('pploadlogin', new Zend_Controller_Router_Route('/pploadlogin/*', array(
            'module'     => 'default',
            'controller' => 'authorization',
            'action'     => 'pploadlogin'
        )));

        // OCS API
        //20191120 OCS-API is disabled for webservers, only api.pling.com or api.kde-look.org allowed, see ticket #1494
        //20191125 erst mal wieder drin
        
        $router->addRoute('ocs_providers_xml', new Zend_Controller_Router_Route('/ocs/providers.xml', array(
            'module'     => 'default',
            'controller' => 'ocsv1',
            'action'     => 'providers'
        )));
        $router->addRoute('ocs_v1_config', new Zend_Controller_Router_Route('/ocs/v1/config', array(
            'module'     => 'default',
            'controller' => 'ocsv1',
            'action'     => 'config'
        )));
        $router->addRoute('ocs_v1_person_check', new Zend_Controller_Router_Route('/ocs/v1/person/check', array(
            'module'     => 'default',
            'controller' => 'ocsv1',
            'action'     => 'personcheck'
        )));
        $router->addRoute('ocs_v1_person_data', new Zend_Controller_Router_Route('/ocs/v1/person/data', array(
            'module'     => 'default',
            'controller' => 'ocsv1',
            'action'     => 'persondata'
        )));
        $router->addRoute('ocs_v1_person_data_personid',
            new Zend_Controller_Router_Route('/ocs/v1/person/data/:personid', array(
                'module'     => 'default',
                'controller' => 'ocsv1',
                'action'     => 'persondata'
            )));
        $router->addRoute('ocs_v1_person_self', new Zend_Controller_Router_Route('/ocs/v1/person/self', array(
            'module'     => 'default',
            'controller' => 'ocsv1',
            'action'     => 'personself'
        )));
        $router->addRoute('ocs_v1_content_categories',
            new Zend_Controller_Router_Route('/ocs/v1/content/categories', array(
                'module'     => 'default',
                'controller' => 'ocsv1',
                'action'     => 'contentcategories'
            )));
        $router->addRoute('ocs_v1_content_data_contentid',
            new Zend_Controller_Router_Route('/ocs/v1/content/data/:contentid', array(
                'module'     => 'default',
                'controller' => 'ocsv1',
                'action'     => 'contentdata',
                'contentid'  => null
            )));
        $router->addRoute('ocs_v1_content_download_contentid_itemid',
            new Zend_Controller_Router_Route('/ocs/v1/content/download/:contentid/:itemid', array(
                'module'     => 'default',
                'controller' => 'ocsv1',
                'action'     => 'contentdownload'
            )));
        $router->addRoute('ocs_v1_content_previewpic_contentid',
            new Zend_Controller_Router_Route('/ocs/v1/content/previewpic/:contentid', array(
                'module'     => 'default',
                'controller' => 'ocsv1',
                'action'     => 'contentpreviewpic'
            )));
        $router->addRoute('ocs_v1_comments',
            new Zend_Controller_Router_Route('/ocs/v1/comments/data/:comment_type/:content_id/:second_id', array(
                'module'       => 'default',
                'controller'   => 'ocsv1',
                'action'       => 'comments',
                'comment_type' => -1,
                'content_id'   => null,
                'second_id'    => null
            )));
        $router->addRoute('ocs_v1_voting',
            new Zend_Controller_Router_Route('/ocs/v1/content/vote/:contentid', array(
                'module'     => 'default',
                'controller' => 'ocsv1',
                'action'     => 'vote'
            )));
        

        
        // embed
        $router->addRoute('embed_v1_member_projects',
            new Zend_Controller_Router_Route('/embed/v1/member/:memberid', array(
                'module'     => 'default',
                'controller' => 'embedv1',
                'action'     => 'memberprojects'
            )));

        $router->addRoute('embed_v1_member_projects_files',
            new Zend_Controller_Router_Route('/embed/v1/ppload/:ppload_collection_id', array(
                'module'     => 'default',
                'controller' => 'embedv1',
                'action'     => 'ppload'
            )));

        $router->addRoute('embed_v1_member_projectscomments',
            new Zend_Controller_Router_Route('/embed/v1/comments/:id', array(
                'module'     => 'default',
                'controller' => 'embedv1',
                'action'     => 'comments'
            )));

        $router->addRoute('embed_v1_member_projectdetail',
            new Zend_Controller_Router_Route('/embed/v1/project/:projectid', array(
                'module'     => 'default',
                'controller' => 'embedv1',
                'action'     => 'projectdetail'
            )));

        $router->addRoute('clones', new Zend_Controller_Router_Route('/clones/*', array(
            'module'     => 'default',
            'controller' => 'credits',
            'action'     => 'index'
        )));
        $router->addRoute('mods', new Zend_Controller_Router_Route('/mods/*', array(
            'module'     => 'default',
            'controller' => 'credits',
            'action'     => 'mods'
        )));



        $cache->save($router, 'ProjectRouter', array('router'), 14400);

        return $router;
    }

    /**
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws exception
     */
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

    /**
     * @throws Zend_Application_Bootstrap_Exception
     */
    protected function _initStoreDependentVars()
    {
        /** @var $front Zend_Controller_Front */
        $front = $this->bootstrap('frontController')->getResource('frontController');
        $front->registerPlugin(new Default_Plugin_InitGlobalStoreVars());
    }

}
