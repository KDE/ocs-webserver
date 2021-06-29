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


use Application\Model\Service\Interfaces\UrlEncryptInterface;
use Laminas\Crypt\Symmetric\Mcrypt;

class UrlEncrypt implements UrlEncryptInterface
{
    protected $config;

    /**
     * @param string $url
     *
     * @return string
     */
    public function encryptForUrl($url)
    {
        if (empty($url)) {
            return '';
        }
        $filter = $this->getCryptAdapter();

        return $this->base64url_encode($filter->encrypt(trim($url)));
    }

    /**
     * @return Mcrypt
     */
    public function getCryptAdapter()
    {
        return new Mcrypt($this->getConfig());
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        if (empty($this->config)) {
            /*  Set various default encryption options. */
            $this->config = array(
                // Initialization vector
                'vector'    => '236587hgtyujkirtfgty5678',
                // Encryption algorithm
                'algorithm' => 'rijndael-192',
                // Encryption key
                'key'       => 'KFJGKDK$$##^FFS345678FG2',
                //salt
                'salt'      => 'KDMC7dhcnHCd2mfKmfjkd038vHAsneJ',
            );
        }

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
     * @param string $data
     *
     * @return string
     */
    protected function base64url_encode($data)
    {
        return strtr(base64_encode($data), '+/=', '._-');
        //return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function decryptFromUrl($url)
    {
        if (empty($url)) {
            return '';
        }
        $filter = $this->getDecryptAdapter();

        return rtrim($filter->decrypt($this->base64url_decode($url)));
    }

    /**
     * @return Mcrypt
     */
    protected function getDecryptAdapter()
    {
        return new Mcrypt($this->getConfig());
    }

    /**
     * @param string $data
     *
     * @return bool|string
     */
    protected function base64url_decode($data)
    {
        return base64_decode(strtr($data, '._-', '+/='));
        //return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * @param string $param
     * @param null   $default
     *
     * @return string|null
     */
    public static function sanitizeUrlParam($param, $default = null)
    {
        if (empty($param)) {
            return $param;
        }

        $validator = new \Laminas\Validator\Regex(['pattern' => '/^[A-Za-z0-9\._-]/']);
        if ($validator->isValid($param)) {
            return $param;
        }

        return $default;
    }

}