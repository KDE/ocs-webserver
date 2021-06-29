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

use Application\Model\Service\Interfaces\HtmlPurifyServiceInterface;
use zf2htmlpurifier\Filter\HTMLPurifierFilter;

class HtmlPurifyService extends BaseService implements HtmlPurifyServiceInterface
{

    const ALLOW_NOTHING = 1;
    const ALLOW_HTML = 2;
    const ALLOW_VIDEO = 3;
    const ALLOW_URL = 4;
    const ALLOW_EMBED = 5;

    private $cache;

    /**
     * @param string $dirty_html
     * @param int    $schema
     *
     * @return string
     *
     */
    public static function purify($dirty_html, $schema = self::ALLOW_NOTHING)
    {
        return self::getPurifier($schema)->filter($dirty_html);
    }

    /**
     * @param int $schema
     *
     * @return HTMLPurifierFilter
     */
    public static function getPurifier($schema = self::ALLOW_NOTHING)
    {

        $purifier = new HTMLPurifierFilter();
        $config = array();

        switch ($schema) {
            case self::ALLOW_HTML:
                $config['HTML.Allowed'] = 'em,strong,br,p,b,a[href],img[src|alt],i,li,ol,ul,small,abbr[title],acronym,blockquote,caption,cite,code,del,dl, dt, sub, sup,tt,var';
                break;

            case self::ALLOW_VIDEO:
                $config['HTML.SafeIframe'] = true;
                $config['URI.SafeIframeRegexp'] = '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%'; //allow YouTube and Vimeo
                break;

            case self::ALLOW_EMBED:
                $config['HTML.SafeIframe'] = true;
                $config['URI.SafeIframeRegexp'] = '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/|w\.soundcloud\.com/player/)%';
                break;

            case self::ALLOW_URL:
                $config['HTML.Allowed'] = ''; // Allow Nothing
                $config['URI.AllowedSchemes'] = array('http' => true, 'https' => true);
                $config['URI.MakeAbsolute'] = true;
                break;

            default:
                $config['HTML.Allowed'] = ''; // Allow Nothing
        }

        //$config['Cache.SerializerPath'] = $this->cache;
        //$config->set('AutoFormat.AutoParagraph', true);
        //$purifier = new HTMLPurifier($config);
        $purifier->setConfig($config);

        return $purifier;
    }
}