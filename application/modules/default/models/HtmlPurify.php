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

    const ALLOW_NOTHING = 1;
    const ALLOW_HTML = 2;
    const ALLOW_VIDEO = 3;
    const ALLOW_URL = 4;

    /**
     * @param string $dirty_html
     * @param int    $schema
     *
     * @return string
     *
     */
    public static function purify($dirty_html, $schema = self::ALLOW_NOTHING)
    {
        return self::getPurifier($schema)->purify($dirty_html);
    }

    /**
     * @param int $schema
     *
     * @return false|HTMLPurifier
     *
     */
    public static function getPurifier($schema = self::ALLOW_NOTHING)
    {
        include_once APPLICATION_LIB . '/HTMLPurifier.safe-includes.php';
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cache_name = isset($schema) ? 'html_purifier_' . $schema : 'html_purifier';
        //        if (false == ($purifier = $cache->load($cache_name))) {
        $config = HTMLPurifier_Config::createDefault();

        switch ($schema) {
            case self::ALLOW_HTML:
                $config->set('HTML.Allowed',
                    'em,strong,br,p,b,a[href],img[src|alt],i,li,ol,ul,small,abbr[title],acronym,blockquote,caption,cite,code,del,dl, dt, sub, sup,tt,var');
                break;

            case self::ALLOW_VIDEO:
                $config->set('HTML.SafeIframe', true);
                $config->set('URI.SafeIframeRegexp',
                    '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%'); //allow YouTube and Vimeo
                break;

            case self::ALLOW_URL:
                $config->set('HTML.Allowed', ''); // Allow Nothing
                $config->set('URI.AllowedSchemes', array('http' => true, 'https' => true));
                $config->set('URI.MakeAbsolute', true);
                break;

            default:
                $config->set('HTML.Allowed', ''); // Allow Nothing
        }

        $config->set('Cache.SerializerPath', APPLICATION_CACHE);
        //$config->set('AutoFormat.AutoParagraph', true);
        $purifier = new HTMLPurifier($config);
        $cache->save($purifier, $cache_name);

        //        }

        return $purifier;
    }

}