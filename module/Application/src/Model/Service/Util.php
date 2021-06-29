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

namespace Application\Model\Service;

use DateTime;
use DateTimeZone;
use Exception;
use Laminas\Validator\Uri;

class Util
{

    public static function parseIni()
    {
        try {
            $file = __DIR__ . "/application.local.ini";
            $ini_array = self::parse_ini_file_multi($file);
            file_put_contents(__DIR__ . '/data.php', '<?php return ' . var_export($ini_array, true) . ";\n");

        } catch (Exception $e) {
            error_log($e);
        }

        return $ini_array;
    }

    private static function parse_ini_file_multi($file, $process_sections = false, $scanner_mode = INI_SCANNER_NORMAL)
    {
        $explode_str = '.';
        $escape_char = "'";
        // load ini file the normal way
        $data = parse_ini_file($file, $process_sections, $scanner_mode);
        if (!$process_sections) {
            $data = array($data);
        }
        foreach ($data as $section_key => $section) {
            // loop inside the section
            foreach ($section as $key => $value) {
                if (strpos($key, $explode_str)) {
                    if (substr($key, 0, 1) !== $escape_char) {
                        // key has a dot. Explode on it, then parse each subkeys
                        // and set value at the right place thanks to references
                        $sub_keys = explode($explode_str, $key);
                        $subs =& $data[$section_key];
                        foreach ($sub_keys as $sub_key) {
                            if (!isset($subs[$sub_key])) {
                                $subs[$sub_key] = [];
                            }
                            $subs =& $subs[$sub_key];
                        }
                        // set the value at the right place
                        $subs = $value;
                        // unset the dotted key, we don't need it anymore
                        unset($data[$section_key][$key]);
                    } // we have escaped the key, so we keep dots as they are
                    else {
                        $new_key = trim($key, $escape_char);
                        $data[$section_key][$new_key] = $value;
                        unset($data[$section_key][$key]);
                    }
                }
            }
        }
        if (!$process_sections) {
            $data = $data[0];
        }

        return $data;
    }

    // parse array to object recursively

    public static function arrayToObject($array)
    {
        // First we convert the array to a json string
        $json = json_encode($array);

        // The we convert the json string to a stdClass()
        return json_decode($json);
    }

    public static function printDate($strTime, $fromFormat = 'Y-m-d H:i:s')
    {
        if (empty($strTime)) {
            return null;
        }
        if (strtotime($strTime) == strtotime('0000-00-00 00:00:00')) {
            return '';
        }

        $date = DateTime::createFromFormat($fromFormat, $strTime);
        $now = new DateTime();
        $interval = $date->diff($now);
        if ($interval->days > 2) {
            return $date->format('M d Y');
        }

        $tokens = array(
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($tokens as $unit => $text) {
            if ($interval->$unit == 0) {
                continue;
            }

            return $interval->$unit . ' ' . $text . (($interval->$unit > 1) ? 's' : '') . ' ago';
        }

        return null;
    }

    public static function printDateSince($strTime, $fromFormat = 'Y-m-d H:i:s')
    {

        if (empty($strTime)) {
            return null;
        }
        if (strtotime($strTime) == strtotime('0000-00-00 00:00:00')) {
            return '';
        }

        $date = DateTime::createFromFormat($fromFormat, $strTime);
        $now = new DateTime();
        $interval = $date->diff($now);

        $tokens = array(
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($tokens as $unit => $text) {
            if ($interval->$unit == 0) {
                continue;
            }

            return $interval->$unit . ' ' . $text . (($interval->$unit > 1) ? 's' : '') . ' ago';
        }

        return null;
    }

    public static function printDateSinceForum($last_posted_at)
    {
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('UTC'));

        $last_posted_at = new DateTime($last_posted_at, new DateTimeZone('UTC'));

        $interval = $last_posted_at->diff($now);

        $tokens = array(
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($tokens as $unit => $text) {
            if ($interval->$unit == 0) {
                continue;
            }

            return $interval->$unit . ' ' . $text . (($interval->$unit > 1) ? 's' : '') . ' ago';
        }

        return null;
    }

    public static function image($filename, $options = array())
    {
        if (false === self::validUri($filename)) {

            return self::createImageUri($filename, $options);
        }

        $uri = $filename;
        if (false === self::isLocalhost($filename)) {
            $httpScheme = 'https';
            $uri = self::replaceScheme($filename, $httpScheme);
        }

        if (empty($options)) {

            return $uri;
        }

        return self::updateImageUri($uri, $options);
    }

    /**
     * @param $filename
     *
     * @return bool
     */
    private static function validUri($filename)
    {

        $validator = new Uri();
        $validator->setAllowAbsolute(true);
        $validator->setAllowRelative(false);

        return $validator->isValid($filename);
    }

    /**
     * @param $filename
     * @param $options
     *
     * @return string
     */
    private static function createImageUri($filename, $options)
    {
        $operations = "";

        if (isset($options['width']) && isset($options['height'])) {
            $operations .= $options['width'] . 'x' . $options['height'];
        } else {
            //$operations .= '80x80';
            $operations .= '';
        }
        if (isset($options['crop'])) {
            $operations .= '-' . $options['crop'];
        } else {
            //$operations .= '-2';
            $operations .= '';
        }

        if ($filename == "") {
            $filename = 'default.png';
        }

        if (isset($options['temporal'])) {
            $filename = '/img/default/tmp/' . $filename;
            $url = $filename;
        } else {
            if (strpos($filename, '.gif') > 0 || $operations == '') {
                $url = IMAGES_MEDIA_SERVER . '/img/' . $filename;
            } else {
                $url = IMAGES_MEDIA_SERVER . '/cache/' . $operations . '/img/' . $filename;
            }
        }

        return $url;
    }

    /**
     * @param string $filename
     *
     * @return bool
     */
    private static function isLocalhost($filename)
    {
        $host = parse_url($filename, PHP_URL_HOST);

        $whitelist = array('127.0.0.1', '::1', 'localhost');

        if (in_array($host, $whitelist)) {
            return true;
        }

        return false;
    }

    /**
     * @param $filename
     * @param $getScheme
     *
     * @return string|string[]|null
     */
    private static function replaceScheme($filename, $getScheme)
    {
        return preg_replace("|^https?|", $getScheme, $filename);
    }

    /**
     * @param $filename
     * @param $options
     *
     * @return string|string[]|null
     */
    private static function updateImageUri($filename, $options)
    {
        $dimension = '';
        if (isset($options['width']) && isset($options['height'])) {
            $dimension = $options['width'] . 'x' . $options['height'];
        } else {
            if (isset($options['width']) && (false === isset($options['height']))) {
                $dimension = $options['width'] . 'x' . $options['width'];
            } else {
                if (isset($options['height']) && (false === isset($options['width']))) {
                    $dimension = $options['height'] . 'x' . $options['height'];
                }
            }
        }
        $uri = preg_replace("/\d\d\dx\d\d\d/", $dimension, $filename);

        return $uri;
    }

    public static function humanFilesize($bytes)
    {
        if ($bytes == 0) {
            return "0.00 B";
        }

        $s = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $e = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $e), 2) . $s[$e];
    }

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
    public static function truncate($text, $length = 150, $ending = '...', $exact = false, $considerHtml = false)
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
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length + $content_length > $length) {
                    $left = $length - $total_length;
                    $entities_length = 0;
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                break;
                            }
                        }
                    }
                    //$truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                    $truncate .= mb_substr($line_matchings[2], 0, $left + $entities_length,'UTF-8');                   
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
                // $truncate = substr($text, 0, $length - strlen($ending));
                $truncate = mb_substr($text, 0, $length - strlen($ending),'UTF-8');     
            }
        }

        if (!$exact) {
            $spacepos = strrpos($truncate, ' ');
            if ($spacepos !== false) {
                // $truncate = substr($truncate, 0, $spacepos);
                $truncate = mb_substr($truncate, 0, $spacepos,'UTF-8');    
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

    /**
     * $score beteween 1..10
     *
     * @param $score
     *
     * @return string
     */
    public static function getScoreColor($score)
    {
        $score2 = $score;
        $blue2 = $red2 = $green2 = $default2 = 200;
        if ($score2 >= 5) {
            $red2 = dechex($default2 - (($score2 * 10 - 50) * 4));
            $green2 = dechex($default2);
            $blue2 = dechex($default2 - (($score2 * 10 - 50) * 4));
        } elseif ($score2 < 5) {
            $red2 = dechex($default2);
            $green2 = dechex($default2 - ((50 - $score2 * 10) * 4));
            $blue2 = dechex($default2 - ((50 - $score2 * 10) * 4));
        }
        if (strlen($green2) == 1) {
            $green2 = '0' . $green2;
        }
        if (strlen($red2) == 1) {
            $red2 = '0' . $red2;
        }
        if (strlen($blue2) == 1) {
            $blue2 = '0' . $blue2;
        }

        return '#' . $red2 . $green2 . $blue2;
    }
}