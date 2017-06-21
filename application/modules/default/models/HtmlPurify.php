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
 *
 * Created: 21.06.2017
 */
class Default_Model_HtmlPurify
{

    /**
     * @param string $dirty_html
     * @param null   $schema
     *
     * @return string
     */
    public static function purify($dirty_html, $schema = null)
    {
        return self::getPurifier($schema)->purify($dirty_html);
    }

    /**
     * @param null $schema
     *
     * @return false|HTMLPurifier
     */
    public static function getPurifier($schema = null)
    {
        include_once APPLICATION_LIB . '/HTMLPurifier.safe-includes.php';
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cache_name = isset($schema) ? 'html_purifier' . md5($schema) : 'html_purifier';
        if (false == ($purifier = $cache->load($cache_name))) {

            if (false == $schema) {
                $config = HTMLPurifier_Config::createDefault();
            } else {
                $config = new HTMLPurifier_Config($schema);
            }
            $config->set('Cache.SerializerPath', APPLICATION_CACHE);
            $purifier = new HTMLPurifier($config);
            $cache->save($purifier, $cache_name);
        }

        return $purifier;
    }

}