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

use Application\Model\Service\Util;
use Laminas\View\Helper\AbstractHelper;

class Truncate extends AbstractHelper
{

    /**
     * Truncates text.
     *
     * Cuts a string to the length of $length and replaces the last characters
     * with the ending if the text is longer than length.
     *
     * @param string  $text String to truncate.
     * @param integer $length Length of returned string, including ellipsis.
     * @param string  $ending Ending to be appended to the trimmed string.
     * @param boolean $exact If false, $text will not be cut mid-word
     * @param boolean $considerHtml If true, HTML tags would be handled correctly
     *
     * @return string Trimmed string.
     */
    public function __invoke($text, $length = 150, $ending = '...', $exact = false, $considerHtml = false)
    {
        return $this->truncate($text, $length, $ending, $exact, $considerHtml);
    }

    public function truncate($text, $length = 150, $ending = '...', $exact = false, $considerHtml = false)
    {
        return Util::truncate($text, $length, $ending, $exact, $considerHtml);
    }

}