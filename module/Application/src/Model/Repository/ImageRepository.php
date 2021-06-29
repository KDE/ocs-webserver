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

namespace Application\Model\Repository;

use Application\Model\Entity\Image;
use Application\Model\Interfaces\ImageInterface;
use Exception;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Form\Element\File;
use Laminas\Http\Client;

class ImageRepository extends BaseRepository implements ImageInterface
{
    protected $_fields = array(
        'id'          => null,
        'filename'    => null,
        'code'        => null,
        'name'        => null,
        'member_id'   => null,
        'model'       => null,
        'foreign_key' => null,
        'foreign_id'  => null,
        'created'     => null,
    );
    protected $_allowed = array(
        'image/jpeg'          => '.jpg',
        'image/jpg'           => '.jpg',
        'image/png'           => '.png',
        'image/gif'           => '.gif',
        'application/x-empty' => '.png',
    );
    protected $_allowedFileExtension = array(
        'jpg',
        'jpeg',
        'png',
        'gif',
    );
    protected $_maxsize = array(
        'width'  => 2000,
        'height' => 2000,
    );
    protected $_errorMsg = null;
    // protected $_maxsize = array(
    //     'width'  => 1024,
    //     'height' => 768
    // );
    /**
     * @var array
     */
    private $config;
    private $IMAGES_UPLOAD_PATH = "";

    public function __construct(
        AdapterInterface $db,
        array $config
    ) {
        parent::__construct($db);
        $this->_name = "image";
        $this->_key = "id";
        $this->_prototype = Image::class;

        $this->config = $config;

        $this->IMAGES_UPLOAD_PATH = $config['ocs_config']['settings']['server']['images']['upload']['path'] . '/';

    }

    /**
     * @param string $url
     * @param null   $fileExtension
     * @param null   $file_info
     *
     * @return string|null
     * @throws Exception
     */
    public function storeRemoteImage($url, $fileExtension = null, &$file_info = null)
    {
        //$host = parse_url( $url, PHP_URL_HOST );
        //$path = parse_url($url, PHP_URL_PATH);
        //$query = parse_url($url, PHP_URL_QUERY);

        //$url = 'http://'.$host.$path.'?'.urlencode($query);
        $limit = 4194304; #4Mb
        $filename = md5($url . '-' . rand(0, 1000));
        if ($fileExtension) {
            $filename .= '.' . $fileExtension;
        }
        $file_info = array();
        if (file_exists($this->IMAGES_UPLOAD_PATH . 'tmp/' . $filename)) {
            // Delete old file. Maybe an updated version is available.
            if (false == unlink($this->IMAGES_UPLOAD_PATH . 'tmp/' . $filename)) {
                throw new Exception('Cannot delete file: ' . $this->IMAGES_UPLOAD_PATH . 'tmp/' . $filename);
            }
        }
        try {
            #$file = file_get_contents($url, NULL, NULL, -1, $limit);
            $file = $this->file_get_contents_curl($url);
        } catch (Exception $e) {
            $file = null;
        }

        if (file_put_contents($this->IMAGES_UPLOAD_PATH . 'tmp/' . $filename, $file)) {
            $content_type = $this->_get_mime_content_type($this->IMAGES_UPLOAD_PATH . 'tmp/' . $filename);
            if (!in_array($content_type, array_keys($this->_allowed))) {
                return null;
                //throw new RuntimeException('Format not allowed ' . $content_type . ' for url ' . $url);
            }
            touch($this->IMAGES_UPLOAD_PATH . 'tmp/' . $filename);
        } else {
            return null;
            //throw new RuntimeException('Error storing remote image');
        }

        $file_info['size'] = filesize($this->IMAGES_UPLOAD_PATH . 'tmp/' . $filename);

        return $filename;
    }

    /**
     * @param $url
     *
     * @return bool|string
     */
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

    /**
     * @param $filename
     *
     * @return mixed|string
     */
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

            $filename_parts = explode('.', $filename);
            $ext = strtolower(array_pop($filename_parts));
            if (array_key_exists($ext, $mime_types)) {
                return $mime_types[$ext];
            } else {
                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME);
                    $mimetype = finfo_file($finfo, $filename);
                    finfo_close($finfo);

                    return $mimetype;
                } else {
                    return 'application/octet-stream';
                }
            }
        } else {
            return mime_content_type($filename);
        }
    }

    /**
     * @param $filePathName
     *
     * @return string|null
     * @throws Exception
     */
    public function saveImageOnMediaServer($filePathName)
    {
        if (empty($filePathName)) {
            return null;
        }

        $log = $GLOBALS['ocs_log'];

        $content_type = mime_content_type($filePathName);

        if (!in_array($content_type, array_keys($this->_allowed))) {
            throw new Exception('Format not allowed: ' . $content_type . ' for img: ' . $filePathName);
        }

        // Generate filename
        $generatedFilename = $this->_generateFilename($filePathName);
        $destinationFile = $this->IMAGES_UPLOAD_PATH . $generatedFilename . $this->_allowed[$content_type];

        if (copy($filePathName, $destinationFile)) {
            if (file_exists($filePathName)) {
                if (false === unlink($filePathName)) {
                    $log->warn(__METHOD__ . ' - can not delete temp file: ' . $filePathName);
                }
            }
            $log->debug(__METHOD__ . ' - Start upload picture - ' . print_r($destinationFile, true));
            $srcPathOnMediaServer = $this->sendImageToMediaServer($destinationFile, $content_type);
            if (file_exists($destinationFile)) {
                if (false === unlink($destinationFile)) {
                    $log->warn(__METHOD__ . ' - can not delete file: ' . $destinationFile);
                }
            }
            if (!$srcPathOnMediaServer) {
                throw new Exception("Error in upload to CDN-Server. \n Server message:\n" . $this->_errorMsg);
            }

            $log->debug(__METHOD__ . ' - End upload picture - ' . print_r($this->IMAGES_UPLOAD_PATH . $srcPathOnMediaServer, true));

            return $srcPathOnMediaServer;
        }
    }

    /**
     * @param $filePathName
     *
     * @return false|string
     */
    private function _generateFilename($filePathName)
    {
        return sha1_file($filePathName);
    }

    /**
     * @param $fullFilePath
     * @param $mimeType
     *
     * @return string
     * @throws Exception
     */
    protected function sendImageToMediaServer($fullFilePath, $mimeType)
    {
        //$config = Zend_Registry::get('config');
        //$url = $config->images->media->upload;
        $url = $this->config['ocs_config']['settings']['server']['images']['media']['upload'];
        //$client = new Zend_Http_Client($url);
        //$client->setFileUpload($fullFilePath, basename($fullFilePath), null, $mimeType);

        $client = new Client(
            $url, array(
                    'adapter' => 'Laminas\Http\Client\Adapter\Curl',
                )
        );
        //$client->setUri($url);
        $client->setFileUpload($fullFilePath, basename($fullFilePath), null, $mimeType);
        $client->setMethod('POST');

        $response = $client->send();

        if ($response->getStatusCode() > 200) {
            throw new Exception('Could not upload file to ' . $url . ' - server response: ' . $response->getBody());
        }

        return $response->getBody();
    }

    /**
     * @param File $formFileElement
     *
     * @return array
     * @throws Exception
     */
    public function saveImages($formFileElement)
    {
        if (empty($formFileElement)) {
            return array();
        }

        $resultPath = array();
        $filesInfo = $formFileElement;

        foreach ($filesInfo as $file => $fileInfo) {
            if (null == $fileInfo['size']) {
                continue;
            }
            $contentType = mime_content_type($fileInfo['tmp_name']);
            if (!in_array($contentType, array_keys($this->_allowed))) {
                throw new Exception('Format not allowed: ' . $contentType . ' for img: ' . $fileInfo['name']);
            }
            $generatedFilename = $this->_generateFilename($fileInfo['tmp_name']);
            $destinationFile = $this->IMAGES_UPLOAD_PATH . $generatedFilename . $this->_allowed[$contentType];

            if (copy($fileInfo['tmp_name'], $destinationFile)) {
                $GLOBALS['ocs_log']->debug(__METHOD__ . ' - Start upload picture - ' . print_r($destinationFile, true));
                $srcPathOnMediaServer = $this->sendImageToMediaServer($destinationFile, $contentType);
                if (!$srcPathOnMediaServer) {
                    throw new Exception("Error in upload to CDN-Server. \n Server message:\n" . $this->_errorMsg);
                }
                $GLOBALS['ocs_log']->debug(__METHOD__ . ' - End upload a picture - ' . print_r($this->IMAGES_UPLOAD_PATH . $srcPathOnMediaServer, true));

                $resultPath[] = $srcPathOnMediaServer;
            }
        }

        return $resultPath;
    }

    /**
     * @param $formFileElementArray
     *
     * @return string
     * @throws Exception
     * @todo wrong place for this method
     */
    public function saveImage($formFileElementArray)
    {
        if (empty($formFileElementArray)) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - form file element empty');

            return null;
        }

        $fileInfo = $formFileElementArray;

        if (null == $fileInfo['size']) {
            return null;
        }
        $contentType = mime_content_type($fileInfo['tmp_name']);
        if (false === in_array($contentType, array_keys($this->_allowed))) {
            throw new Exception('Format not allowed: ' . $contentType . ' for img: ' . $fileInfo['name']);
        }
        $generatedFilename = $this->_generateFilename($fileInfo['tmp_name']);
        $destinationFile = $this->IMAGES_UPLOAD_PATH . $generatedFilename . $this->_allowed[$contentType];
        $GLOBALS['ocs_log']->info(__METHOD__ . ' - destination file path: ' . $destinationFile);

        if (copy($fileInfo['tmp_name'], $destinationFile)) {
            if (file_exists($fileInfo['tmp_name'])) {
                if (false === unlink($fileInfo['tmp_name'])) {
                    $GLOBALS['ocs_log']->warn(__METHOD__ . ' - can not delete temp file: ' . $fileInfo['tmp_name']);
                }
            }
            $GLOBALS['ocs_log']->debug(__METHOD__ . ' - Start upload picture - ' . print_r($destinationFile, true));
            $srcPathOnMediaServer = $this->sendImageToMediaServer($destinationFile, $contentType);
            if (file_exists($destinationFile)) {
                if (false === unlink($destinationFile)) {
                    $GLOBALS['ocs_log']->warn(__METHOD__ . ' - can not delete file: ' . $destinationFile);
                }
            }
            if (!$srcPathOnMediaServer) {
                throw new Exception("Error in upload to CDN-Server. \n Server message:\n" . $this->_errorMsg);
            }
            $GLOBALS['ocs_log']->debug(__METHOD__ . ' - End upload a picture - ' . print_r($this->IMAGES_UPLOAD_PATH . $srcPathOnMediaServer, true));

            return $srcPathOnMediaServer;
        }
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
