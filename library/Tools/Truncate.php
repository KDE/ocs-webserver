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
 */

namespace Library\Tools;

/**
 * Class Truncate
 *
 * @package Library\Tools
 */
class Truncate
{
    /**
     * Truncates text.
     *
     * Cuts a string to the length of $length and replaces the last characters
     * with the ending if the text is longer than length.
     *
     * @param string  $text         String to truncate.
     * @param integer $length       Length of returned string, including ellipsis.
     * @param string  $ending       Ending to be appended to the trimmed string.
     * @param boolean $exact        If false, $text will not be cut mid-word
     * @param boolean $considerHtml If true, HTML tags would be handled correctly
     *
     * @return string Trimmed string.
     */
    public static function get($text, $length = 150, $ending = '...', $exact = false, $considerHtml = false)
    {
        if (strlen($text) == 0) {
            return '';
        }

        $total_length = strlen($ending);
        $open_tags = array();
        $truncate = '';

        if ($considerHtml) {
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }

            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

            foreach ($lines as $line_matchings) {
                if (!empty($line_matchings[1])) {
                    if (preg_match('/^<(s*.+?\/s*|s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(s.+?)?)>$/is', $line_matchings[1])) {
                    } else {
                        if (preg_match('/^<s*\/([^s]+?)s*>$/s', $line_matchings[1], $tag_matchings)) {
                            $pos = array_search($tag_matchings[1], $open_tags);
                            if ($pos !== false) {
                                unset($open_tags[$pos]);
                            }
                        } else {
                            if (preg_match('/^<s*([^s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                                array_unshift($open_tags, strtolower($tag_matchings[1]));
                            }
                        }
                    }
                    $truncate .= $line_matchings[1];
                }
                $content_length = strlen(
                    preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2])
                );
                if ($total_length + $content_length > $length) {
                    $left = $length - $total_length;
                    $entities_length = 0;
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2],$entities, PREG_OFFSET_CAPTURE)) {
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                if ($total_length >= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }

        if (!$exact) {
            $spacepos = strrpos($truncate, ' ');
            if ($spacepos !== false) {
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
        $truncate .= $ending;

        if ($considerHtml) {
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }

        return $truncate;
    }
}