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
class Default_Model_DbTable_Image extends Zend_Db_Table_Abstract
{
    protected $_name = "image";
    protected $_fields = array(
        'id'          => null,
        'filename'    => null,
        'code'        => null,
        'name'        => null,
        'member_id'   => null,
        'model'       => null,
        'foreign_key' => null,
        'foreign_id'  => null,
        'created'     => null
    );
    protected $_allowed = array(
        'image/jpeg'          => '.jpg',
        'image/jpg'           => '.jpg',
        'image/png'           => '.png',
        'image/gif'           => '.gif',
        'application/x-empty' => '.png'
    );
    protected $_allowedFileExtension = array(
        'jpg',
        'jpeg',
        'png',
        'gif'
    );
    protected $_maxsize = array(
        'width'  => 1024,
        'height' => 768
    );
    protected $_errorMsg = null;

    public function getMemberImages($member_id)
    {
        $images = $this->select()->where('member_id = ?', $member_id)->query()->fetchAll();

        return $images;
    }

    public function storeExternalImage($url, $fileExtension = null)
    {
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));
        $tmpFileName = $this->storeRemoteImage($url, $fileExtension);

        if (file_exists(IMAGES_UPLOAD_PATH . 'tmp/' . $tmpFileName)) {
            $content_type = mime_content_type(IMAGES_UPLOAD_PATH . 'tmp/' . $tmpFileName);
            $filePath = $this->saveImageOnMediaServer($tmpFileName);
            $filename = $filePath;
            $file_info['size'] = filesize(IMAGES_UPLOAD_PATH . '/' . $filename);
            $this->save(array('code' => $content_type, 'filename' => $filename));
        }

        return $filename;
    }

    public function storeRemoteImage($url, $fileExtention = null, &$file_info = null)
    {
        //$host = parse_url( $url, PHP_URL_HOST );
        //$path = parse_url($url, PHP_URL_PATH);
        //$query = parse_url($url, PHP_URL_QUERY);

        //$url = 'http://'.$host.$path.'?'.urlencode($query);
        $limit = 4194304; #4Mb
        $filename = md5($url);
        if ($fileExtention) {
            $filename .= '.' . $fileExtention;
        }
        $file_info = array();
        if (file_exists(IMAGES_UPLOAD_PATH . 'tmp/' . $filename)) {
            // Delete old file. Maybe an updated version is available.
            if (false == unlink(IMAGES_UPLOAD_PATH . 'tmp/' . $filename)) {
                throw new Exception('Cannot delete file: ' . IMAGES_UPLOAD_PATH . 'tmp/' . $filename);
            }
        }
        try {
            #$file = file_get_contents($url, NULL, NULL, -1, $limit);
            $file = $this->file_get_contents_curl($url);
        } catch (Exception $e) {
            $file = null;
        }
        if (file_put_contents(IMAGES_UPLOAD_PATH . 'tmp/' . $filename, $file)) {
            $content_type = $this->_get_mime_content_type(IMAGES_UPLOAD_PATH . 'tmp/' . $filename);
            if (!in_array($content_type, array_keys($this->_allowed))) {
                throw new Exception('Format not allowed ' . $content_type . ' for url ' . $url);
            }
            touch(IMAGES_UPLOAD_PATH . 'tmp/' . $filename);
        } else {
            throw new Exception('Error storing remote image');
        }

        $file_info['size'] = filesize(IMAGES_UPLOAD_PATH . 'tmp/' . $filename);

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

    public function _get_mime_content_type($filename)
    {

        if (!function_exists('mime_content_type')) {

            $mime_types = array(

                'txt'  => 'text/plain',
                'htm'  => 'text/html',
                'html' => 'text/html',
                'php'  => 'text/html',
                'css'  => 'text/css',
                'js'   => 'application/javascript',
                'json' => 'application/json',
                'xml'  => 'application/xml',
                'swf'  => 'application/x-shockwave-flash',
                'flv'  => 'video/x-flv',
                // images
                'png'  => 'image/png',
                'jpe'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpg'  => 'image/jpeg',
                'gif'  => 'image/gif',
                'bmp'  => 'image/bmp',
                'ico'  => 'image/vnd.microsoft.icon',
                'tiff' => 'image/tiff',
                'tif'  => 'image/tiff',
                'svg'  => 'image/svg+xml',
                'svgz' => 'image/svg+xml',
                // archives
                'zip'  => 'application/zip',
                'rar'  => 'application/x-rar-compressed',
                'exe'  => 'application/x-msdownload',
                'msi'  => 'application/x-msdownload',
                'cab'  => 'application/vnd.ms-cab-compressed',
                // audio/video
                'mp3'  => 'audio/mpeg',
                'qt'   => 'video/quicktime',
                'mov'  => 'video/quicktime',
                // adobe
                'pdf'  => 'application/pdf',
                'psd'  => 'image/vnd.adobe.photoshop',
                'ai'   => 'application/postscript',
                'eps'  => 'application/postscript',
                'ps'   => 'application/postscript',
                // ms office
                'doc'  => 'application/msword',
                'rtf'  => 'application/rtf',
                'xls'  => 'application/vnd.ms-excel',
                'ppt'  => 'application/vnd.ms-powerpoint',
                // open office
                'odt'  => 'application/vnd.oasis.opendocument.text',
                'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
            );

            $ext = strtolower(array_pop(explode('.', $filename)));
            if (array_key_exists($ext, $mime_types)) {
                return $mime_types[$ext];
            } else if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME);
                $mimetype = finfo_file($finfo, $filename);
                finfo_close($finfo);

                return $mimetype;
            } else {
                return 'application/octet-stream';
            }
        } else {
            return mime_content_type($filename);
        }
    }

    public function saveImageOnMediaServer($filePathName)
    {
        if (empty($filePathName)) {
            return null;
        }

        $content_type = mime_content_type($filePathName);

        if (!in_array($content_type, array_keys($this->_allowed))) {
            throw new Exception('Format not allowed: ' . $content_type . ' for img: ' . $filePathName);
        }

        // Generate filename
        $generatedFilename = $this->_generateFilename($filePathName);
        $destinationFile = IMAGES_UPLOAD_PATH . $generatedFilename . $this->_allowed[$content_type];

        if (copy($filePathName, $destinationFile)) {
            if (file_exists($filePathName)) {
                if (false === unlink($filePathName)) {
                    Zend_Registry::get('logger')->warn(__METHOD__ . ' - can not delete temp file: ' . $filePathName);
                }
            }
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - Start upload picture - ' . print_r($destinationFile,
                    true))
            ;
            $srcPathOnMediaServer = $this->sendImageToMediaServer($destinationFile, $content_type);
            if (file_exists($destinationFile)) {
                if (false === unlink($destinationFile)) {
                    Zend_Registry::get('logger')->warn(__METHOD__ . ' - can not delete file: ' . $destinationFile);
                }
            }
            if (!$srcPathOnMediaServer) {
                throw new Exception("Error in upload to CDN-Server. \n Server message:\n" . $this->_errorMsg);
            }

            Zend_Registry::get('logger')->debug(__METHOD__ . ' - End upload picture - ' . print_r(IMAGES_UPLOAD_PATH
                    . $srcPathOnMediaServer, true))
            ;

            return $srcPathOnMediaServer;
        }
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
    protected function sendImageToMediaServer($fullFilePath, $mimeType)
    {
        $config = Zend_Registry::get('config');
        $url = $config->images->media->upload;

        $client = new Zend_Http_Client($url);
        $client->setParameterPost('privateKey', $config->images->media->privateKey);
        $client->setFileUpload($fullFilePath, basename($fullFilePath), null, $mimeType);

        $response = $client->request('POST');

        if ($response->getStatus() > 200) {
            $this->_errorMsg = $response->getBody();

            return null;
        }

        return $response->getBody();
    }

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

    /**
     * @param Zend_Form_Element_File $formFileElement
     *
     * @return string
     * @throws Zend_Exception
     * @todo wrong place for this method
     */
    public function saveImage($formFileElement)
    {
        if (empty($formFileElement)) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - form file element empty');
            return null;
        }

        $filesInfo = $formFileElement->getFileInfo();

        if (1 < count($filesInfo)) {
            throw new Zend_Exception('Element contains more than one file elements');
        }

        foreach ($filesInfo as $file => $fileInfo) {
            if (null == $fileInfo['size']) {
                return null;
            }
            $contentType = mime_content_type($fileInfo['tmp_name']);
            if (false === in_array($contentType, array_keys($this->_allowed))) {
                throw new Zend_Exception('Format not allowed: ' . $contentType . ' for img: ' . $fileInfo['name']);
            }
            $generatedFilename = $this->_generateFilename($fileInfo['tmp_name']);
            $destinationFile = IMAGES_UPLOAD_PATH . $generatedFilename . $this->_allowed[$contentType];
            Zend_Registry::get('logger')->info(__METHOD__ . ' - destination file path: ' . $destinationFile);

            if (copy($fileInfo['tmp_name'], $destinationFile)) {
                if (file_exists($fileInfo['tmp_name'])) {
                    if (false === unlink($fileInfo['tmp_name'])) {
                        Zend_Registry::get('logger')->warn(__METHOD__ . ' - can not delete temp file: '
                            . $fileInfo['tmp_name'])
                        ;
                    }
                }
                Zend_Registry::get('logger')->debug(__METHOD__ . ' - Start upload picture - '
                    . print_r($destinationFile, true))
                ;
                $srcPathOnMediaServer = $this->sendImageToMediaServer($destinationFile, $contentType);
                if (file_exists($destinationFile)) {
                    if (false === unlink($destinationFile)) {
                        Zend_Registry::get('logger')->warn(__METHOD__ . ' - can not delete file: ' . $destinationFile);
                    }
                }
                if (!$srcPathOnMediaServer) {
                    throw new Zend_Exception("Error in upload to CDN-Server. \n Server message:\n" . $this->_errorMsg);
                }
                Zend_Registry::get('logger')->debug(__METHOD__ . ' - End upload a picture - '
                    . print_r(IMAGES_UPLOAD_PATH . $srcPathOnMediaServer, true))
                ;

                return $srcPathOnMediaServer;
            }
        }
    }

    /**
     * @param Zend_Form_Element_File $formFileElement
     *
     * @return array
     * @throws Zend_Exception
     */
    public function saveImages($formFileElement)
    {
        if (empty($formFileElement)) {
            return array();
        }

        $resultPath = array();
        $filesInfo = $formFileElement->getFileInfo();

        foreach ($filesInfo as $file => $fileInfo) {
            if (null == $fileInfo['size']) {
                continue;
            }
            $contentType = mime_content_type($fileInfo['tmp_name']);
            if (!in_array($contentType, array_keys($this->_allowed))) {
                throw new Zend_Exception('Format not allowed: ' . $contentType . ' for img: ' . $fileInfo['name']);
            }
            $generatedFilename = $this->_generateFilename($fileInfo['tmp_name']);
            $destinationFile = IMAGES_UPLOAD_PATH . $generatedFilename . $this->_allowed[$contentType];

            if (copy($fileInfo['tmp_name'], $destinationFile)) {
                Zend_Registry::get('logger')->debug(__METHOD__ . ' - Start upload picture - '
                    . print_r($destinationFile, true))
                ;
                $srcPathOnMediaServer = $this->sendImageToMediaServer($destinationFile, $contentType);
                if (!$srcPathOnMediaServer) {
                    throw new Zend_Exception("Error in upload to CDN-Server. \n Server message:\n" . $this->_errorMsg);
                }
                Zend_Registry::get('logger')->debug(__METHOD__ . ' - End upload a picture - '
                    . print_r(IMAGES_UPLOAD_PATH . $srcPathOnMediaServer, true))
                ;

                $resultPath[] = $srcPathOnMediaServer;
            }
        }

        return $resultPath;
    }

    public function getAllowedFileExtension()
    {
        return $this->_allowedFileExtension;
    }

    public function getAllowedMimeTypes()
    {
        return array_keys($this->getAllowed());
    }

    public function getAllowed()
    {
        return $this->_allowed;
    }

    public function setAllowed($allowed)
    {
        $this->_allowed = $allowed;
    }

    public function getMaxsize()
    {
        return $this->_maxsize;
    }

    public function setMaxsize($maxsize)
    {
        $this->_maxsize = $maxsize;
    }

}
