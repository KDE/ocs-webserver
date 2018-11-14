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
class Default_View_Helper_ImageUri extends Zend_View_Helper_Abstract
{

    protected $_operations = array(
        'crop'        => '%d',
        'width'       => '%d',
        'height'      => '%d',
        'quality'     => '%d',
        'bgColor'     => '%s',
        'progressive' => '%d'
    );

    protected $_options = array(
        'temporal' => false
    );

    protected $_separator = '-';

    public function ImageUri($filename, $options = array())
    {
        if (empty($options) and $this->validUri($filename)) {
            /** @var Zend_Controller_Request_Http $request */
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $uri = $this->replaceScheme($filename, $request->getScheme());

            return $uri;
        }

        if ($this->validUri($filename)) {
            /** @var Zend_Controller_Request_Http $request */
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $uri = $this->replaceScheme($filename, $request->getScheme());

            return $this->updateImageUri($uri, $options);
        }

        return $this->createImageUri($filename, $options);
    }

    private function validUri($filename)
    {
        return Zend_Uri::check($filename);
    }

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

        if (isset($options['temporal'])) {
            $filename = '/img/default/tmp/' . $filename;
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

    private function updateImageUri($filename, $options)
    {
        $dimension = '';
        if (isset($options['width']) && isset($options['height'])) {
            $dimension = $options['width'] . 'x' . $options['height'];
        }
        elseif (isset($options['width']) && (false === isset($options['height']))) {
            $dimension = $options['width'] . 'x' . $options['width'];
        }
        elseif (isset($options['height']) && (false === isset($options['width']))) {
            $dimension = $options['height'] . 'x' . $options['height'];
        }
        $uri = preg_replace("/\d\d\dx\d\d\d/", $dimension, $filename);

        return $uri;
    }

    private function replaceScheme($filename, $getScheme)
    {
        $result = preg_replace("|^https?|", $getScheme, $filename);

        return $result;
    }

}