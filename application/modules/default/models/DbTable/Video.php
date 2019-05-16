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
    
    public function storeExternalVideo($url, $fileExtension = null)
    {
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));
        $tmpFileName = $this->storeRemoteVideo($url, $fileExtension);

        if (file_exists(VIDEOS_UPLOAD_PATH . 'tmp/' . $tmpFileName)) {
            $filePath = $this->saveVideoOnMediaServer($tmpFileName);
            $filename = $filePath;
        }

        return $filename;
    }

    public function storeRemoteVideo($url, $fileExtention = null, &$file_info = null)
    {
        //$host = parse_url( $url, PHP_URL_HOST );
        //$path = parse_url($url, PHP_URL_PATH);
        //$query = parse_url($url, PHP_URL_QUERY);

        //$url = 'http://'.$host.$path.'?'.urlencode($query);
        //$limit = 4194304; #4Mb
        $filename = md5($url);
        if ($fileExtention) {
            $filename .= '.' . $fileExtention;
        }
        $file_info = array();
        if (file_exists(VIDEOS_UPLOAD_PATH . 'tmp/' . $filename)) {
            // Delete old file. Maybe an updated version is available.
            if (false == unlink(VIDEOS_UPLOAD_PATH . 'tmp/' . $filename)) {
                throw new Exception('Cannot delete file: ' . VIDEOS_UPLOAD_PATH . 'tmp/' . $filename);
            }
        }
        try {
            #$file = file_get_contents($url, NULL, NULL, -1, $limit);
            $file = $this->file_get_contents_curl($url);
        } catch (Exception $e) {
            $file = null;
        }
        if (file_put_contents(VIDEOS_UPLOAD_PATH . 'tmp/' . $filename, $file)) {
            $content_type = $this->_get_mime_content_type(VIDEOS_UPLOAD_PATH . 'tmp/' . $filename);
            if (!in_array($content_type, array_keys($this->_allowed))) {
                throw new Exception('Format not allowed ' . $content_type . ' for url ' . $url);
            }
            touch(VIDEOS_UPLOAD_PATH . 'tmp/' . $filename);
        } else {
            throw new Exception('Error storing remote image');
        }

        $file_info['size'] = filesize(VIDEOS_UPLOAD_PATH . 'tmp/' . $filename);

        return $filename;
    }

    public function file_get_contents_curl($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    public function saveVideoOnMediaServer($filePathName)
    {
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));
        
        if (empty($filePathName)) {
            return null;
        }

        $content_type = mime_content_type($filePathName);

        //if (!in_array($content_type, array_keys($this->_allowed))) {
        //    throw new Exception('Format not allowed: ' . $content_type . ' for img: ' . $filePathName);
        //}

        // Generate filename
        $srcPathOnMediaServer = $this->sendVideoToMediaServer($filePathName, $content_type);
        if (!$srcPathOnMediaServer) {
            throw new Exception("Error in upload to Video-Server. \n Server message:\n" . $this->_errorMsg);
        }
        
        if (false === unlink($filePathName)) {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - can not delete file: ' . $filePathName);
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - End upload video - ' . print_r(VIDEOS_UPLOAD_PATH
                . $srcPathOnMediaServer, true))
        ;

        return $srcPathOnMediaServer;
    }

    private function _generateFilename($filePathName)
    {
        return sha1_file($filePathName);
    }

    /**
     * @param $fullFilePath
     * @param $mimeType
     *
     * @return string
     */
    protected function sendVideoToMediaServer($fullFilePath, $mimeType)
    {
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));
        
        $config = Zend_Registry::get('config');
        $url = $config->videos->media->upload;

        $client = new Zend_Http_Client($url);
        $client->setFileUpload($fullFilePath, basename($fullFilePath), null, $mimeType);

        $response = $client->request('POST');

        if ($response->getStatus() > 200) {
            $this->_errorMsg = $response->getBody();
            Zend_Registry::get('logger')->error(__METHOD__ . ' - ' . print_r($response->getBody(), true));
            return null;
        }

        return $response->getBody();
    }

    
    /*
    public function save($image)
    {
        foreach ($image as $key => $value) {
            if (!in_array($key, array_keys($this->_fields))) {
                unset($image[$key]);
            }
        }

        if (isset($image['filename']) && !isset($image['code'])) {
            $image['code'] = $this->_trimExtension($image['filename']);
        }

        if (isset($image['id'])) {
            return $this->_update($image);
        } else {
            return $this->_add($image);
        }
    }

    private function _update($image)
    {
        if (!isset($image['id'])) {
            throw new Exception('Invalid update without an id');
        } else {
            $id = (int)$image['id'];
        }

        return $this->update($image, array('id = ?' => $id));
    }

    private function _add($image)
    {
        return $this->insert($image);
    }
     * 
     * 
     */

}