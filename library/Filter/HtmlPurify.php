<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Library\Filter;


use HTMLPurifier;

class HtmlPurify extends AbstractFilter
{
    const ALLOW_NOTHING = 1;
    const ALLOW_HTML = 2;
    const ALLOW_VIDEO = 3;
    const ALLOW_URL = 4;
    const ALLOW_EMBED = 5;
    const FORBIDDEN_SCRIPTS = 6;
    const ALLOW_MARKDOWN = 7;

    protected $schema = null;
    protected $config = null;
    protected $htmlPurifier = null;

    /**
     * Returns the result of filtering $value
     *
     * @param mixed $value
     *
     * @return string
     */
    public function filter($value)
    {
        return $this->getHtmlPurifier()->purify($value);
    }

    /**
     * @return HTMLPurifier
     */
    public function getHtmlPurifier()
    {
        if (!$this->htmlPurifier) {
            if (!isset($this->config)) {
                $this->config = array();
            }
            if (!isset($this->config['Cache.SerializerPath'])) {
                //$this->config->set('Cache.SerializerPath',sys_get_temp_dir());
                $this->config['Cache.SerializerPath'] = realpath(APPLICATION_DATA . '/cache');
            }
            switch ($this->schema) {
                case self::ALLOW_HTML:
                    //$this->config->set('HTML.Allowed','em,strong,br,p,b,a[href],img[src|alt],i,li,ol,ul,small,abbr[title],acronym,blockquote,caption,cite,code,del,dl,dt,sub,sup,tt,var');
                    $this->config['HTML.Allowed'] = 'em,strong,br,p,b,i,li,ol,ul,small,abbr[title],acronym,blockquote,caption,cite,code,del,dl,dt,sub,sup,tt,var,table,thead,tbody,tfoot,dd,kbd,q,samp,hr,tr,td,th,s,strike';
                    break;

                case self::ALLOW_VIDEO:
                    $this->config['HTML.SafeIframe'] = true;
                    $this->config['URI.SafeIframeRegexp'] = '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%'; //allow YouTube and Vimeo
                    break;

                case self::ALLOW_EMBED:
                    $this->config['HTML.SafeIframe'] = true;
                    $this->config['URI.SafeIframeRegexp'] = '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/|w\.soundcloud\.com/player/)%';
                    break;

                case self::ALLOW_URL:
                    $this->config['HTML.Allowed'] = ''; // Allow Nothing
                    $this->config['URI.AllowedSchemes'] = array('http' => true, 'https' => true);
                    $this->config['URI.MakeAbsolute'] = true;
                    break;

                case self::FORBIDDEN_SCRIPTS:
                    $this->config['HTML.ForbiddenElements'] = array('script', 'applet');
                    break;

                case self::ALLOW_MARKDOWN:
                    // These rules can be applied after a BBCode or Markdown Parser to remove dangerous HTML code.
                    // Allow Text without tag e.g P or DIV (plain text, obviously necessary for markdown)
                    $this->config['Core.LexerImpl'] = 'DirectLex';
                    // Define manually which elements can be rendered
                    // In this example, we allow (almost) all the basic elements that are converted with markdown
                    $this->config['HTML.Allowed'] = 'h1,h2,h3,h4,h5,h6,br,b,i,strong,em,a,pre,code,img,tt,div,ins,del,sup,sub,p,ol,ul,table,thead,tbody,tfoot,blockquote,dl,dt,dd,kbd,q,samp,var,hr,li,tr,td,th,s,strike';
                    break;

                default:
                    $this->config['HTML.Allowed'] = ''; // Allow Nothing
            }

            $this->htmlPurifier = new HTMLPurifier($this->config);
        }

        return $this->htmlPurifier;
    }

    /**
     * @param HTMLPurifier $purifier
     */
    public function setHtmlPurifier(HTMLPurifier $purifier)
    {
        $this->htmlPurifier = $purifier;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return null
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @param null $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

}