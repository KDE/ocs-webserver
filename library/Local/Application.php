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

class Local_Application extends Zend_Application
{

    /**
     *
     * @var Zend_Cache_Core|null
     */
    protected $_configCache;

    //Override

    /**
     * Constructor
     *
     * Initialize application. Potentially initializes include_paths, PHP
     * settings, and bootstrap class.
     *
     * @param  string $environment
     * @param  string|array|Zend_Config $options String path to configuration file, or array/Zend_Config of configuration options
     * @param Zend_Cache_Core $configCache
     * @throws Zend_Application_Exception
     * @return \Local_Application
     */
    public function __construct($environment, $options = null, Zend_Cache_Core $configCache = null)
    {
        $this->_configCache = $configCache;
        parent::__construct($environment, $options);
    }

    /**
     * Load configuration file of options
     *
     * @param  string $file
     * @throws Zend_Application_Exception When invalid configuration file is provided
     * @return array
     */
    protected function _loadConfig($file)
    {
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if ($this->_configCache === null OR $suffix == 'php' OR $suffix == 'inc') { //No need for caching those
            return parent::_loadConfig($file);
        }

        $cacheId = $this->_cacheId($file);

        if ($this->_testCache($file, $cacheId)) { //Valid cache?
            return $this->_configCache->load($cacheId, true);
        } else {
            $config = parent::_loadConfig($file);
            $this->_configCache->save($config, $cacheId, array(), 14400);

            return $config;
        }
    }

    protected function _cacheId($file)
    {
        return md5($file) . '_' . $this->getEnvironment();
    }

    /**
     * @param string $file
     * @return bool|string
     */
    protected function _testCache($file, $cacheId)
    {
        $configMTime = filemtime($file);

        $cacheLastMTime = $this->_configCache->test($cacheId);

        if ($cacheLastMTime !== false AND $configMTime < $cacheLastMTime) { //Valid cache?
            return true;
        }

        return false;
    }

} 