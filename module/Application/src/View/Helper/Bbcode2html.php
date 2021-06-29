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

namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class Bbcode2html extends AbstractHelper
{
    /**
     * transforms a string with bbcode markup into html
     *
     * @param string $txt
     * @param bool   $nl2br
     *
     * @return string
     */
    public function __invoke($txt, $nl2br = true, $forcecolor = '')
    {
        if (!empty($forcecolor)) {
            $fc = ' style="color:' . $forcecolor . ';"';
        } else {
            $fc = '';
        }
        $newtxt = htmlspecialchars($txt);
        if ($nl2br) {
            $newtxt = nl2br($newtxt);
        }

        $patterns = array(
            '`\[b\](.+?)\[/b\]`is',
            '`\[i\](.+?)\[/i\]`is',
            '`\[u\](.+?)\[/u\]`is',
            '`\[li\](.+?)\[/li\]`is',
            '`\[strike\](.+?)\[/strike\]`is',
            '`\[url\]([a-z0-9]+?://){1}([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)\[/url\]`si',
            '`\[quote\](.+?)\[/quote\]`is',
            '`\[indent](.+?)\[/indent\]`is',
        );

        $replaces = array(
            '<strong' . $fc . '>\\1</strong>',
            '<em' . $fc . '>\\1</em>',
            '<span style="border-bottom: 1px dotted">\\1</span>',
            '<li' . $fc . ' style="margin-left:20px;">\\1</li>',
            '<strike' . $fc . '>\\1</strike>',
            '<a href="\1\2" rel="nofollow" target="_blank">\1\2</a>',
            '<strong' . $fc . '>Quote:</strong><div style="margin:0px 10px;padding:5px;background-color:#F7F7F7;border:1px dotted #CCCCCC;width:80%;"><em>\1</em></div>',
            '<pre' . $fc . '>\\1</pre>',
        );

        $newtxt = preg_replace($patterns, $replaces, $newtxt);

        return $newtxt;
    }
}