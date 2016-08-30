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

class Local_Verification_WebsiteOwner
{

    const SALT_KEY = 'MakeItAndPlingIt';
    const FILE_PREFIX = 'pling';
    const FILE_POSTFIX = '.html';

    /**
     * Configuration for HTTP-Client
     *
     * @var array
     */
    protected $_config = array(
        'maxredirects' => 0,
        'timeout' => 30
    );

    /**
     * @param string $url
     * @param Zend_Db_Table_Row_Abstract $dataRow
     * @return bool
     */
    public function validateAuthCode($url, $dataRow)
    {
        if (true == empty($url)) {
            return false;
        }

        if (false == $this->validateUrlMemberData($url, $dataRow)) {
            return false;
        }

        $url = $this->addDefaultScheme($url);

        $httpClient = $this->getHttpClient();
        $httpClient->setUri($this->getAuthFileUri($url));

        $response = $this->retrieveBody($httpClient);

        if (false === $response) {
            $httpClient->setUri($url);
            $response = $this->retrieveBody($httpClient);
            if (false === $response) {
                Zend_Registry::get('logger')->err(__METHOD__ . " - Error while validate AuthCode for Website: " . $url . ".\n Server replay was: " . $httpClient->getLastResponse()->getStatus() . ". " . $httpClient->getLastResponse()->getMessage() . PHP_EOL);
                return false;
            }
        }
        return (strpos($response, $this->generateAuthCode($url)) !== false) ? true : false;
    }

    public function validateUrlMemberData($url, $dataRow)
    {
        $result = false;
        $memberTable = new Default_Model_Member();
        /** @var Zend_Db_Table_Row $rowMember */
        $rowMember = $memberTable->find($dataRow)->current();
        if ($rowMember->link_website == $url) {
            $result = true;
        }
        return $result;
    }

    /**
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        $httpClient = new Zend_Http_Client();
        $httpClient->setConfig($this->_config);
        return $httpClient;
    }

    /**
     * @param string $domain
     * @return string
     */
    public function getAuthFileUri($domain)
    {
        return $domain . '/' . $this->getAuthFileName($domain);
    }

    /**
     * @param string $url
     * @param string $scheme
     * @return string
     */
    public function addDefaultScheme($url, $scheme = 'http://')
    {
        if (false == preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = $scheme . $url;
        }
        return $url;
    }

    /**
     * @param string $domain
     * @return string
     */
    public function getAuthFileName($domain)
    {
        return self::FILE_PREFIX . $this->generateAuthCode($domain) . self::FILE_POSTFIX;
    }

    /**
     * @param string $domain
     * @return null|string
     */
    public function generateAuthCode($domain)
    {
        if (empty($domain)) {
            return null;
        }
        return md5($this->_parseDomain($domain) . self::SALT_KEY);
    }

    protected function _parseDomain($domain)
    {
        $count = preg_match_all("/^(?:(?:http|https):\/\/)?([\da-zA-ZäüöÄÖÜ\.-]+\.[a-z\.]{2,6})[\/\w \.-]*\/?$/", $domain, $matches);
        if ($count > 0) {
            return current($matches[1]);
        } else {
            throw new Exception(__FILE__ . '(' . __LINE__ . '): ' . 'Error while parsing the domain name = ' . $domain);
        }
    }

    /**
     * @param Zend_Http_Client $httpClient
     * @return bool
     */
    public function retrieveBody($httpClient)
    {
        $response = $httpClient->request();

        if ($response->isError()) {
            return false;
        } else {
            return $response->getBody();
        }
    }

    public function parseDomain($domain)
    {
        return $this->_parseDomain($domain);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param $config
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    /**
     * @param $memberId
     * @param $verificationResult
     */
    public function updateData($memberId, $verificationResult)
    {
        $memberTable = new Default_Model_Member();
        /** @var Zend_Db_Table_Row $rowMember */
        $rowMember = $memberTable->find($memberId)->current();
        $rowMember->validated_at = new Zend_Db_Expr('NOW()');
        $rowMember->validated = (int)$verificationResult;
        $rowMember->save();
    }

}