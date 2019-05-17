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
class Backend_Commands_ConvertVideo implements Local_Queue_CommandInterface
{

    protected $collectionId;
    protected $fileId;
    public static $VIDE_FILE_TYPES = array('video/3gpp','video/3gpp2','video/mpeg','video/quicktime','video/x-flv','video/webm','application/ogg','video/x-ms-asf','video/x-matroska');

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
    public function __construct($collectionId, $fileId)
    {
        $this->collectionId = $collectionId;
        $this->fileId = $fileId;
        
    }

    public function doCommand()
    {

        return $this->callConvertVideo($this->collectionId, $this->fileId);
    }

    protected function callConvertVideo($collectionId, $fileId)
    {
        
        $log = Zend_Registry::get('logger');
        $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . '**********' . "\n");
        //call video convert server
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $timestamp = time() + 3600; // one hour valid
        $hash = hash('sha512',$salt . $collectionId . $timestamp); // order isn't important at all... just do the same when verifying
        $url = PPLOAD_API_URI . 'files/download/id/' . $fileId . '/s/' . $hash . '/t/' . $timestamp;
        $url .= '/lt/filepreview/' . $fileId;
        
        $videoServer = new Default_Model_DbTable_Video();
        $result = $videoServer->storeExternalVideo($this->collectionId, $url);
        
        if(!empty($result) && $result != 'Error') {
            //Save Preview URL in DB
            $config = Zend_Registry::get('config');
            $cdnurl = $config->videos->media->cdnserver;
            $url_preview = $cdnurl.$collectionId."/".$result.".mp4";
            $url_thumb = $cdnurl.$collectionId."/".$result."_thumb.png";
            $data = array('id' => $videoServer->getNewId(),'collection_id' => $this->collectionId,'file_id' => $this->fileId, 'url_preview' => $url_preview, 'url_thumb' => $url_thumb);
            $videoServer->insert($data);
            
            
        } else {
            $log->error("Error on Converting Video! Result: ".$result);
            return false;
        }
        
        
        return true;
    }

}