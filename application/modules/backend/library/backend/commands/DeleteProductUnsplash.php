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
class Backend_Commands_DeleteProductUnsplash implements Local_Queue_CommandInterface
{

    /** @var Zend_Db_Table_Row_Abstract */
    protected $product;
    /**
     * @var Zend_Config
     */
    private $config;

    public function __construct()
    {
        $this->config = Zend_Registry::get('config');
    }

    public function doCommand()
    {
        $projectTable = new Default_Model_DbTable_Project();
        $sql = 'SELECT `project`.`project_id`, `source_url`, `image_small`, `image_big`
                FROM `project` 
                WHERE `project`.`source_url` LIKE "%unsplash.com%" AND `project`.`status` = 20 AND `project`.`image_small` NOT LIKE "%--%"
                ORDER BY `project`.`project_id`
                LIMIT 1
                ';
        $result = $projectTable->getAdapter()->fetchAll($sql);
        $count_projects = count($result);
        $count_errors = 0;

        if (count($result) == 0) {
            return;
        }

        foreach ($result as $table_row) {
            try {
                $currentPath = $table_row['image_small'];
                $newPath = $this->renameImageOnCdn($currentPath);
                $projectTable->update(array('image_small' => $newPath),
                    "image_small = '" . $table_row['image_small'] . "'");
                Zend_Registry::get('logger')
                             ->info(__METHOD__ . ' --> ' . $table_row['project_id'] . ' (' . $currentPath . ' => ' . $newPath . ')');
                $this->renameGalleryImagesOnCdn($table_row['project_id']);
            } catch (Exception $e) {
                $count_errors++;
                Zend_Registry::get('logger')->err($e);
            }
        }

        return array('success' => true, 'total' => $count_projects, 'errors' => $count_errors);
    }

    private function renameImageOnCdn($imgPath)
    {
        $url = $this->config->images->media->delete;
        $secret = $this->config->images->media->privateKey;

        $postString = '--' . md5(rand()) . md5(rand());
        $url .= '?path=' . urlencode($imgPath) . '&post=' . $postString . '&key=' . $secret;

        $client = new Zend_Http_Client($url);
        $response = $client->request('POST');

        if ($response->getStatus() > 200) {
            throw new Default_Model_Exception_Image('ERROR: Could not remove images from CD-Server: ' . $url . ' - server response: ' . $response->getBody());
        }

        Zend_Registry::get('logger')->info(__METHOD__ . ' - Result fromCN-Server: ' . $response->getBody());

        return $imgPath . $postString;
    }

    private function renameGalleryImagesOnCdn($project_id)
    {
        $galleryTable = new Default_Model_DbTable_ProjectGalleryPicture();
        $sql = 'SELECT `picture_src`
                FROM `project_gallery_picture`
                WHERE `project_id` = :project_id
                ';
        $result = $galleryTable->getAdapter()->fetchAll($sql, array('project_id' => $project_id));

        if (count($result) == 0) {
            return;
        }

        foreach ($result as $table_row) {
            try {
                $currentPath = $table_row['picture_src'];
                $newImagePath = $this->renameImageOnCdn($currentPath);
                //save renamed images
                $galleryTable->update(array('picture_src' => $newImagePath), "picture_src = '" . $currentPath . "'");
                Zend_Registry::get('logger')
                             ->info(__METHOD__ . ' --> ' . $project_id . '(' . $currentPath . '=>' . $newImagePath);
            } catch (Exception $e) {
                Zend_Registry::get('logger')->err($e);
            }
        }
    }

}
