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
    protected $fileType;
    
    public static $VIDEO_FILE_TYPES = array('video/3gpp','video/3gpp2','video/mpeg','video/quicktime','video/x-flv','video/webm','application/ogg','video/x-ms-asf','video/x-matroska', 'video/mp4');

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
    public function __construct($collectionId, $fileId, $fileType)
    {
        $this->collectionId = $collectionId;
        $this->fileId = $fileId;
        $this->fileType = $fileType;
        
    }

    public function doCommand()
    {

        return $this->callConvertVideo($this->collectionId, $this->fileId, $this->fileType);
    }

    protected function callConvertVideo($collectionId, $fileId, $fileType)
    {
        
        $log = Zend_Registry::get('logger');
        $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . '**********' . "\n");
        
        $videoServer = new Default_Model_DbTable_Video();
        $data = array('id' => $videoServer->getNewId(),'collection_id' => $collectionId,'file_id' => $fileId, 'create_timestamp' => new Zend_Db_Expr('NOW()'));
        $videoServer->insert($data);
        
        //call video convert server
        $salt = PPLOAD_DOWNLOAD_SECRET;
        $timestamp = time() + 3600; // one hour valid
        $hash = hash('sha512',$salt . $collectionId . $timestamp); // order isn't important at all... just do the same when verifying
        $url = PPLOAD_API_URI . 'files/download/id/' . $fileId . '/s/' . $hash . '/t/' . $timestamp;
        $url .= '/lt/filepreview/' . $fileId;
        $url = Default_Model_PpLoad::createDownloadUrl($collectionId,$fileId,array('id'=>$fileId, 't'=>$timestamp, 'lt'=>'filepreview'));

        $result = $videoServer->storeExternalVideo($collectionId, $fileType, $url);
        
        if(!empty($result) && $result != 'Error') {
            //Save Preview URL in DB
            $config = Zend_Registry::get('config');
            $cdnurl = $config->videos->media->cdnserver;
            $url_preview = $cdnurl.$collectionId."/".$result.".mp4";
            $url_thumb = $cdnurl.$collectionId."/".$result."_thumb.png";
            $data = array('url_preview' => $url_preview, 'url_thumb' => $url_thumb);
            $videoServer->update($data, "collection_id = $collectionId AND file_id = $fileId");
            
            
        } else {
            $log->debug("Error on Converting Video! Result: ".$result);
            return false;
        }
        
        
        return true;
    }

}