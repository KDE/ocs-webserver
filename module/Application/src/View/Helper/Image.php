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

namespace Application\View\Helper;

use Laminas\Validator\Uri;
use Laminas\View\Helper\AbstractHelper;

class Image extends AbstractHelper
{
    protected $_operations = array(
        'crop'        => '%d',
        'width'       => '%d',
        'height'      => '%d',
        'quality'     => '%d',
        'bgColor'     => '%s',
        'progressive' => '%d',
    );

    protected $_options = array(
        'tempFile' => false,
    );

    protected $_separator = '-';

    // Constructor.
    public function __construct()
    {

    }

    public function __invoke($filename, $options = array())
    {
        return $this->Image($filename, $options);
    }

    /**
     * @param       $filename
     * @param array $options
     *
     * @return string|null
     */
    public function Image($filename, $options = array())
    {

        if (false === $this->validUri($filename)) {

            return $this->createImageUri($filename, $options);
        }

        $uri = $filename;
        if (false === $this->isLocalhost($filename)) {
            $httpScheme = 'https';
            $uri = $this->replaceScheme($filename, $httpScheme);
        }

        if (empty($options)) {

            return $uri;
        }

        return $this->updateImageUri($uri, $options);
    }

    /**
     * @param $filename
     *
     * @return bool
     */
    private function validUri($filename)
    {

        $validator = new Uri();
        $validator->setAllowAbsolute(true);
        $validator->setAllowRelative(false);

        return $validator->isValid($filename);
    }

    /**
     * @param $filename
     * @param $options
     *
     * @return string
     */
    private function createImageUri($filename, $options)
    {
        $operations = "";

        if (isset($options['width']) && isset($options['height'])) {
            $operations .= $options['width'] . 'x' . $options['height'];
        } else {
            //$operations .= '80x80';
            $operations .= '';
        }
        if (isset($options['crop'])) {
            $operations .= '-' . $options['crop'];
        } else {
            //$operations .= '-2';
            $operations .= '';
        }

        if ($filename == "") {
            $filename = 'default.png';
        }

        if (isset($options['tempFile'])) {
            $filename = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filename);
            $url = $filename;
        } else {
            if (strpos($filename, '.gif') > 0 || $operations == '') {
                $url = IMAGES_MEDIA_SERVER . '/img/' . $filename;
            } else {
                $url = IMAGES_MEDIA_SERVER . '/cache/' . $operations . '/img/' . $filename;
            }
        }

        return $url;
    }

    /**
     * @param string $filename
     *
     * @return bool
     */
    private function isLocalhost($filename)
    {
        $host = parse_url($filename, PHP_URL_HOST);

        $whitelist = array('127.0.0.1', '::1', 'localhost');

        if (in_array($host, $whitelist)) {
            return true;
        }

        return false;
    }

    /**
     * @param $filename
     * @param $getScheme
     *
     * @return string|string[]|null
     */
    private function replaceScheme($filename, $getScheme)
    {
        $result = preg_replace("|^https?|", $getScheme, $filename);

        return $result;
    }

    /**
     * @param $filename
     * @param $options
     *
     * @return string|string[]|null
     */
    private function updateImageUri($filename, $options)
    {
        $dimension = '';
        if (isset($options['width']) && isset($options['height'])) {
            $dimension = $options['width'] . 'x' . $options['height'];
        } else {
            if (isset($options['width']) && (false === isset($options['height']))) {
                $dimension = $options['width'] . 'x' . $options['width'];
            } else {
                if (isset($options['height']) && (false === isset($options['width']))) {
                    $dimension = $options['height'] . 'x' . $options['height'];
                }
            }
        }
        $uri = preg_replace("/\d\d\dx\d\d\d/", $dimension, $filename);

        return $uri;
    }

    public function getDataURI($image, $mime = '')
    {
        return 'data: ' . (function_exists('mime_content_type') ? mime_content_type($image) : $mime) . ';base64,' . base64_encode(file_get_contents($image));
    }

    public function getImageDataFromUrl($serverUrl, $filename, $options)
    {
        $url = $this->Image($serverUrl, $filename, $options);
        $urlParts = pathinfo($url);
        $extension = $urlParts['extension'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $base64 = 'data:image/' . $extension . ';base64,' . base64_encode($response);

        return $base64;
    }
}