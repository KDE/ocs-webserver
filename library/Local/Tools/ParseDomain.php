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
 *    Created: 18.11.2016
 **/
class Local_Tools_ParseDomain
{

    /**
     * @param $domain
     * @param bool $debug
     * @return string
     */
    public static function get_domain($domain, $debug = false)
    {
        if (false == isset($domain)) {
            return null;
        }

        $original = $domain = strtolower($domain);
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return $domain;
        }
        $debug ? print('<strong style="color:green">&raquo;</strong> Parsing: ' . $original) : false;
        $arr = array_slice(array_filter(explode('.', $domain, 4), function ($value) {
            return $value !== 'www';
        }), 0); //rebuild array indexes
        if (count($arr) > 2) {
            $count = count($arr);
            $_sub = explode('.', $count === 4 ? $arr[3] : $arr[2]);
            $debug ? print(" (parts count: {$count})") : false;
            if (count($_sub) === 2) // two level TLD
            {
                $removed = array_shift($arr);
                if ($count === 4) // got a subdomain acting as a domain
                {
                    $removed = array_shift($arr);
                }
                $debug ? print("<br>\n" . '[*] Two level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
            } elseif (count($_sub) === 1) // one level TLD
            {
                $removed = array_shift($arr); //remove the subdomain
                if (strlen($_sub[0]) === 2 && $count === 3) // TLD domain must be 2 letters
                {
                    array_unshift($arr, $removed);
                } else {
                    // non country TLD according to IANA
                    $tlds = array(
                        'aero',
                        'arpa',
                        'asia',
                        'biz',
                        'cat',
                        'com',
                        'coop',
                        'edu',
                        'gov',
                        'info',
                        'jobs',
                        'mil',
                        'mobi',
                        'museum',
                        'name',
                        'net',
                        'org',
                        'post',
                        'pro',
                        'tel',
                        'travel',
                        'xxx',
                    );
                    if (count($arr) > 2 && in_array($_sub[0], $tlds) !== false) //special TLD don't have a country
                    {
                        array_shift($arr);
                    }
                }
                $debug ? print("<br>\n" . '[*] One level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
            } else // more than 3 levels, something is wrong
            {
                for ($i = count($_sub); $i > 1; $i--) {
                    $removed = array_shift($arr);
                }
                $debug ? print("<br>\n" . '[*] Three level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
            }
        } elseif (count($arr) === 2) {
            $arr0 = array_shift($arr);
            if (strpos(join('.', $arr), '.') === false
                && in_array($arr[0], array('localhost', 'test', 'invalid')) === false
            ) // not a reserved domain
            {
                $debug ? print("<br>\n" . 'Seems invalid domain: <strong>' . join('.',
                        $arr) . '</strong> re-adding: <strong>' . $arr0 . '</strong> ') : false;
                // seems invalid domain, restore it
                array_unshift($arr, $arr0);
            }
        }
        $debug ? print("<br>\n" . '<strong style="color:gray">&laquo;</strong> Done parsing: <span style="color:red">' . $original . '</span> as <span style="color:blue">' . join('.',
                $arr) . "</span><br>\n") : false;
        return join('.', $arr);
    }

}