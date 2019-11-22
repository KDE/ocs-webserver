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
class Backend_Commands_CreateTorrent implements Local_Queue_CommandInterface
{

    protected $file;
    
    /**
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * param [ mixed $args [, $... ]]
     *
     * @param int    $collectionId
     * @param int $fileId
     *
     * @link http://php.net/manual/en/language.oop5.decon.php
     */
    public function __construct($file)
    {
        $this->file = $file;
        
    }

    public function doCommand()
    {

        return $this->callCreateTorrent($this->file);
    }

    protected function callCreateTorrent($file)
    {
        
        
        $link = null;
        $isExternLink = false;
        if($file->tags)
        {
            $tags = explode(',', $file->tags);        
            foreach ($tags as $t) {
               $tagStr =  explode('##',$t);
               if(sizeof($tagStr)==2 && $tagStr[0]=='link')
               {
                $link = $tagStr[1];
               }
            }
        }

        
        if($link)
        {
            $isExternLink = true;
        }
        
        if(!$isExternLink) {
        
            $log = Zend_Registry::get('logger');
            $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . '**********' . "\n");

            $result = $this->createExternalTorrent($file->id);

            if(!empty($result) && $result != 'Error') {
                //Done, set has_torrent in table ppload_files
                $files = new Default_Model_DbTable_PploadFiles();
                $data = array();
                $data['has_torrent'] = 1;
                $files->update($data, 'id = '.$file->id);
            } else {
                $log->debug("Error on Creating Torrent! Result: ".$result);
                $files = new Default_Model_DbTable_PploadFiles();
                $data = array();
                $data['has_torrent'] = 0;
                $files->update($data, 'id = '.$file->id);
                return false;
            }
        } else {
            return false;   
        }
        
        return true;
    }
    
    public function createExternalTorrent($fileId)
    {
        $httpClient = $this->getHttpClient();
        
        $config = Zend_Registry::get('config');
        $torrenturl = $config->torrent->media->createurl . "?file_id=".$fileId;
        
        $uri = $this->generateUri($torrenturl);

        $httpClient->setUri($uri);
        $response = $this->retrieveBody($httpClient);
        if (false === $response) {
            Zend_Registry::get('logger')->err(__METHOD__ . " - Error while creating torrent: " . $uri
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