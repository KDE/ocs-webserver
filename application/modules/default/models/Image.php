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

class Default_Model_Image extends Default_Model_DbTable_Image
{

    /**
     * @return array
     * @deprecated
     */
    public function uploadProfileImageToTempData()
    {
        $imageTable = new Default_Model_DbTable_Image();

        $upload = new Zend_File_Transfer();

        $upload->addValidator('Count', false, 1)
            ->addValidator('Size', false, 2097152)
            ->addValidator('FilesSize', false, 2000000)
            ->addValidator('Extension', false, $imageTable->getAllowedFileExtension())
            ->addValidator('ImageSize', false,
                array(
                    'minwidth' => 50,
                    'maxwidth' => 2000,
                    'minheight' => 50,
                    'maxheight' => 1200
                ))
            ->addValidator('MimeType', false, $imageTable->getAllowedMimeTypes());

        $files = $upload->getFileInfo();

        $tmpFilePathName = '';
        foreach ($files as $file => $fileInfo) {
            if ($upload->isValid()) {
                $tmpFilePathName = IMAGES_UPLOAD_PATH . 'tmp/' . Local_Tools_UUID::generateUUID() . $fileInfo['name'];
                $upload->addFilter('Rename', array('target' => $tmpFilePathName, 'overwrite' => true));
                $upload->receive();
            }
        }
        return array('status' => $upload->isReceived(), 'messages' => $upload->getMessages(), 'filename' => basename($tmpFilePathName));
    }

}