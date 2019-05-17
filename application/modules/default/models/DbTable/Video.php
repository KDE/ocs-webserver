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
class Default_Model_DbTable_Video extends Zend_Db_Table_Abstract
{
    protected $_name = "ppload.ppload_files";
    public static $VIDE_FILE_TYPES = array('video/3gpp','video/3gpp2','video/mpeg','video/quicktime','video/x-flv','video/webm','application/ogg','video/x-ms-asf','video/x-matroska');

    /**
     * Configuration for HTTP-Client
     *
     * @var array
     */
    protected $_config = array(
        'maxredirects' => 0,
        'timeout'      => 21600
    );
    
    protected $_allowed = array(
        'video/3gpp'        => '.3gp',
        'video/3gpp2'       => '.3g2',
        'video/mpeg'        => '.mpeg',
        'video/quicktime'   => '.mov',
        'video/x-flv'       => '.flv',
        'video/webm'        => '.webm',
        'application/ogg'   => '.ogv',
        'video/x-matroska'  => '.mkv',
        'video/mp4'         => '.mp4'
    );
    protected $_allowedFileExtension = array(
        '3gp',
        '3g2',
        'mpeg',
        'mov',
        'flv',
        'webm',
        'ogv',
        'mkv',
        'mp4'
    );
    protected $_errorMsg = null;
    
    /**
     * @param string $url
     * @param string $authCode
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Uri_Exception
     */
    public function storeExternalVideo($collectionId, $url)
    {
        if (true == empty($url)) {
            return false;
        }

        $httpClient = $this->getHttpClient();
        
        $config = Zend_Registry::get('config');
        $videourl = $config->videos->media->upload . "?url=".urlencode($url)."&collection_id=".$collectionId;
        
        $uri = $this->generateUri($videourl);

        $httpClient->setUri($uri);
        $response = $this->retrieveBody($httpClient);
        if (false === $response) {
            Zend_Registry::get('logger')->err(__METHOD__ . " - Error while converting Video: " . $uri
                . ".\n Server replay was: " . $httpClient->getLastResponse()->getStatus() . ". " . $httpClient->getLastResponse()
                                                                                                              ->getMessage()
                . PHP_EOL)
            ;

            return false;
        }
        
        Zend_Registry::get('logger')->debug(__METHOD__ . ' Result: ' . print_r($response, true));

        return $response;
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

}