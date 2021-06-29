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

namespace Application\Model\Service;

use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;

class BaseService
{
    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function readCache($name)
    {
        $cache = new SimpleCacheDecorator($GLOBALS['ocs_cache']);
        $cache_name = hash('haval128,4', $name);

        try {
            return $cache->get($cache_name);
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - ' . $e->getMessage());

            return null;
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @param int    $ttl
     *
     * @return bool
     */
    public function writeCache($name, $value, $ttl = 1800)
    {
        $cache = new SimpleCacheDecorator($GLOBALS['ocs_cache']);
        $cache_name = hash('haval128,4', $name);

        try {
            return $cache->set($cache_name, $value, $ttl);
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
    }
}