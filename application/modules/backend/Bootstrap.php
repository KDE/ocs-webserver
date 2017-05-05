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

class Backend_Bootstrap extends Zend_Application_Module_Bootstrap
{

    protected function _initAutoloader()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Backend',
            'basePath' => realpath(dirname(__FILE__)),
        ));
        $autoloader
            ->addResourceType('element', 'forms/elements', 'Form_Element')
            ->addResourceType('other', 'forms/other', 'Form_Other')
            ->addResourceType('commands', 'library/backend/commands', 'Commands')
        ;
        return $autoloader;
    }

    protected function _initPluginLoader()
    {
        $pluginLoader = new Zend_Loader_PluginLoader();
        $pluginLoader->addPrefixPath('Backend_Form_Element', APPLICATION_PATH . '/modules/backend/forms/elements/');
        return $pluginLoader;
    }

    /**
     * @return false|mixed|Zend_Config
     * @throws Zend_Exception
     * @todo: When the storm is over let's check if we really need this. In usual cases we don't need this.
     */
//    protected function _initConfig()
//    {
//    	if (Zend_Registry::isRegistered('cache')) {
//    		/** @var Zend_Cache_Core $cache */
//    		$cache = Zend_Registry::get('cache');
//
//    		if (false == ($config = $cache->load('application_config'))) {
//    			$config = new Zend_Config($this->getOptions(), true);
//    			$cache->save($config, 'application_config', array(), 14400);
//    		}
//    	} else {
//    		$config = new Zend_Config($this->getOptions(), true);
//    	}
//
//    	Zend_Registry::set('config', $config);
//    	return $config;
//    }

    protected function _initIncludePath () {
        set_include_path(implode(PATH_SEPARATOR, array(
            dirname(__FILE__) . '/library',
            get_include_path(),
        )));
    }
    
}

