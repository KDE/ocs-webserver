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
class Local_Verification_WebsiteProject
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
        'timeout'      => 30
    );

    /**
     * @param string $url
     * @param string $authCode
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Uri_Exception
     */
    public function testForAuthCodeExist($url, $authCode)
    {
        if (true == empty($url)) {
            return false;
        }

        $httpClient = $this->getHttpClient();

        $uri = $this->generateUri($url);

        $httpClient->setUri($uri);
        $response = $this->retrieveBody($httpClient);

        if (false === $response) {
            $httpClient->setUri($url);
            $response = $this->retrieveBody($httpClient);
            if (false === $response) {
                Zend_Registry::get('logger')->err(__METHOD__ . " - Error while validate AuthCode for Website: " . $url
                    . ".\n Server replay was: " . $httpClient->getLastResponse()->getStatus() . ". " . $httpClient->getLastResponse()
                                                                                                                  ->getMessage()
                    . PHP_EOL)
                ;

                return false;
            }
        }

        return (strpos($response, $authCode) !== false) ? true : false;
    }

    /**
     * @return Zend_Http_Client
     * @throws Zend_Http_Client_Exception
     */
    public function getHttpClient()
    {
        $httpClient = new Zend_Http_Client();
        $httpClient->setConfig($this->_config);

        return $httpClient;
    }

    /**
     * @param $url
     *
     * @return Zend_Uri
     * @throws Zend_Uri_Exception
     */
    protected function generateUri($url)
    {
        $uri = Zend_Uri::factory($url);

        return $uri;
    }

    /**
     * @param Zend_Http_Client $httpClient
     *
     * @return bool
     * @throws Zend_Http_Client_Exception
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

    /**
     * @param string $domain
     *
     * @return string
     */
    public function getAuthFileUri($domain)
    {
        return $domain . '/' . $this->getAuthFileName($domain);
    }

    /**
     * @param string $domain
     *
     * @return string
     */
    public function getAuthFileName($domain)
    {
        return self::FILE_PREFIX . $this->generateAuthCode($domain) . self::FILE_POSTFIX;
    }

    /**
     * @param string $domain
     *
     * @return null|string
     */
    public function generateAuthCode($domain)
    {
        if (empty($domain)) {
            return null;
        }

        return md5($domain . self::SALT_KEY);
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
     * @param $project_id
     * @param $verificationResult
     *
     * @throws Zend_Db_Table_Exception
     */
    public function updateData($project_id, $verificationResult)
    {
        $modelProject = new Default_Model_Project();
        /** @var Zend_Db_Table_Row $rowMember */
        $rowMember = $modelProject->find($project_id)->current();
        if (count($rowMember->toArray()) == 0) {
            return;
        }
        $rowMember->validated_at = new Zend_Db_Expr('NOW()');
        $rowMember->validated = (int)$verificationResult;
        $rowMember->save();
    }

    /**
     * @param $url
     *
     * @return mixed
     * @throws Exception
     */
    protected function _parseDomain($url)
    {
        $matches = array();
        $count = preg_match_all("/^(?:(?:http|https):\/\/)?([\da-zA-ZäüöÄÖÜ\.-]+\.[a-z\.]{2,6})[\/\w \.-]*\/?$/", $url, $matches);
        if ($count > 0) {
            return current($matches[1]);
        } else {
            throw new Exception(__FILE__ . '(' . __LINE__ . '): ' . 'Error while parsing url= ' . $url);
        }
    }

}