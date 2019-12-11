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

class Local_Session_Handler_Memcache implements Zend_Session_SaveHandler_Interface
{

    /** @var Zend_Cache_Backend_Interface $cache */
    public $cache = null;
    private $maxlifetime = 3600;

    public function __construct($cacheHandler)
    {
        if ($cacheHandler instanceof Zend_Cache_Backend_Interface) {
            $this->cache = $cacheHandler;

            return $this;
        }

        $cacheClass = 'Zend_Cache_Backend_' . $cacheHandler['cache']['type'];
        $_cache = new $cacheClass($cacheHandler);
        $this->cache = $_cache;

        if (isset($cacheHandler['cache']['maxlifetime'])) {
            $this->maxlifetime = (int)$cacheHandler['cache']['maxlifetime'];
        }

        return $this;
    }

    public function open($save_path, $name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        if (false === ($data = $this->cache->load($id))) {
            return '';
        } else {
            return $data;
        }
    }

    public function write($id, $sessionData)
    {
        $this->cache->save($sessionData, $id, array(), $this->maxlifetime);
        return true;
    }

    public function destroy($id)
    {
        $this->cache->remove($id);
        return true;
    }

    public function gc($notusedformemcache)
    {
        return true;
    }

}