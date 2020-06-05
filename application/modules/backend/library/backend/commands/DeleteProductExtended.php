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
class Backend_Commands_DeleteProductExtended implements Local_Queue_CommandInterface
{

    /** @var Zend_Db_Table_Row_Abstract */
    protected $product;

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
     * @param Zend_Db_Table_Row_Abstract $product
     *
     * @link http://php.net/manual/en/language.oop5.decon.php
     */
    public function __construct($product)
    {
        $this->product = $product;
    }

    public function doCommand()
    {
        $this->deleteProductFromIndex();
        $this->deleteCollectionFromPPload();
        $this->deleteImagesFromCdn();
    }

    protected function deleteProductFromIndex()
    {
        if (empty($this->product->project_id) OR empty($this->product->project_category_id)) {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - no productId or catId was set.');
            return;
        }

        $modelSearch = new Default_Model_Search_Lucene();
        $modelSearch->deleteDocument($this->product->toArray());
    }

    private function deleteCollectionFromPPload()
    {
        // ppload
        // Delete collection
        if ($this->product->ppload_collection_id) {
            $pploadApi = new Ppload_Api(array(
                'apiUri'   => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret'   => PPLOAD_SECRET
            ));

            $collectionResponse = $pploadApi->deleteCollection($this->product->ppload_collection_id);

            Zend_Registry::get('logger')->info(__METHOD__ . ' - product delete request for ppload: ' . $this->product->project_id
                . ' response: ' . print_r($collectionResponse,
                    true));
        }
    }
    
    private function deleteImagesFromCdn()
    {
        //Remove Logo
        $imgPath = $this->product->image_small;
        $newPath = $this->deleteImageFromCdn($imgPath);
        
        //save renamed images
        $this->product->image_small = $newPath;
        $this->product->save();
        
        
        //Remove Gallery Pics
        $galleryPictureTable = new Default_Model_DbTable_ProjectGalleryPicture();
        $stmt = $galleryPictureTable->select()->where('project_id = ?', $projectId)->order(array('sequence'));

        foreach ($galleryPictureTable->fetchAll($stmt) as $pictureRow) {
            $imgPath = $pictureRow['picture_src'];
            $newPath = $this->deleteImageFromCdn($imgPath);

            //save renamed images
            $galleryPictureTable->update(array('picture_src' => $newPath), 'project_id = '.$pictureRow['project_id'].' AND sequence = '.$pictureRow['sequence']);
        }
        
    }
    
    private function deleteImageFromCdn($imgPath) {
        $config = Zend_Registry::get('config');
        $url = $config->images->media->delete;
        $secret = $config->images->media->privateKey;
        
        $postString = '--'.md5(rand()).md5(rand());
        $url .= '?path='.urlencode($imgPath).'&post='.$postString.'&key='.$secret;
        
        $client = new Zend_Http_Client($url);
        $response = $client->request('POST');

        if ($response->getStatus() > 200) {
            throw new Default_Model_Exception_Image('ERROR: Could not remove images from CD-Server: ' . $url . ' - server response: ' . $response->getBody());
        }
        
        Zend_Registry::get('logger')->info(__METHOD__ . ' - Result fromCN-Server: ' . $response->getBody());
        
        //save renamed images
        return $imgPath.$postString;
    }

}
