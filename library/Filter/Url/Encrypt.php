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

namespace Library\Filter\Url;

use Laminas\Crypt\Symmetric\Mcrypt;

class Encrypt
{

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     *
     * @return string
     */
    public function filter($value)
    {
        return $this->encryptForUrl($value);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function encryptForUrl($url)
    {
        if (empty($url)) {
            return '';
        }

        /*  Set various encryption options. */
        $options = array(
            // Encryption type - Openssl or Mcrypt
            //'adapter'   => \Laminas\Crypt\Symmetric\Mcrypt,
            // Initialization vector
            'vector'    => '236587hgtyujkirtfgty5678',
            // Encryption algorithm
            'algorithm' => 'rijndael-192',
            // Encryption key
            'key'       => 'KFJGKDK$$##^FFS345678FG2',
            //salt
            'salt'      => 'KDMC7dhcnHCd2mfKmfjkd038vHAsneJ',
        );
        $mcrypt = new Mcrypt($options);

        return $this->base64url_encode($mcrypt->encrypt(trim($url)));
    }

    protected function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

}