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
class Default_Model_Discovery
{

    protected $_discoveryDomains = array(
        'YouTube' => array(
            'www.youtube.com',
            'youtube.com'
        )
    );

    public function guessGeneralData($url)
    {
        if (!preg_match('/(http|https)\:\/\//', $url)) {
            $url = 'http://' . $url;
        }
        $url_host = parse_url($url, PHP_URL_HOST);
        if (in_array($url_host, $this->_discoveryDomains['YouTube'])) {
            $data = $this->getYoutubeData($this->getYoutubeCode($url));
            $data['content_url'] = $url;
            return $data;
        } else {
            try {
                return $this->getGeneralData($url);
            } catch (Exception $e) {
                return null;
            }

        }
    }

    public function getYoutubeData($code)
    {
        $yt = new Zend_Gdata_YouTube();
        $video = $yt->getVideoEntry($code);
        $data = array(
            'description' => $video->getVideoDescription(),
            'title' => $video->getVideoTitle(),
            'content_type' => 'youtube',
            'thumbnails' => array()
        );

        $image_m = new Default_Model_DbTable_Image();
        $ImageHelper = new Default_View_Helper_Image();
        $thumbs = $video->getVideoThumbnails();
        foreach ($thumbs as $thumbnail) {
            $img_src = $thumbnail['url'];
            $filename = $image_m->storeRemoteImage($img_src, $file_info);
            $thumb = array(
                'full_url' => $ImageHelper->Image($filename, array('temporal' => true)),
                'filename' => $filename
            );
            $data['thumbnails'][] = $thumb;
        }

        return $data;
    }

    public function getYouTubeCode($url)
    {
        $url_host = parse_url($url, PHP_URL_HOST);
        if (in_array($url_host, $this->_discoveryDomains['YouTube'])) {
            parse_str(parse_url($url, PHP_URL_QUERY), $url_vars);
            if (!empty($url_vars['v'])) {
                return $url_vars['v'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getGeneralData($url)
    {
        $meta_info = array(
            'description',
            'og:image'
        );
        $dom = new DOMDocument;
        if (@$dom->loadHTMLFile($url)) {
            $result = array();
            $result['content_url'] = $url;
            $result['title'] = $dom->getElementsByTagName('title')->item(0)->textContent;
            $meta = $dom->getElementsByTagName('meta');
            for ($i = 0; $i < $meta->length; $i++) {
                $m = $meta->item($i);
                if (in_array($m->getAttribute('name'), $meta_info) || in_array($m->getAttribute('property'), $meta_info)) {
                    $name = $m->getAttribute('name');
                    if ($name == '') {
                        $name = $m->getAttribute('property');
                    }
                    $result[$name] = $m->getAttribute('content');
                }
            }
            $host = parse_url($url, PHP_URL_HOST);

            $dom_img = $dom->getElementsByTagName('img');
            $result['thumbnails'] = array();
            $image_m = new Default_Model_DbTable_Image();
            $ImageHelper = new Default_View_Helper_Image();

            $n_images = 0;
            $src_images = array();
            if (isset($result['og:image'])) {
                $src_images[] = $result['og:image'];
            }

            for ($i = 0; $i < $dom_img->length; $i++) {
                $src_images[] = $dom_img->item($i)->getAttribute('src');
            }

            foreach ($src_images as $img_src) {
                if (parse_url($img_src, PHP_URL_HOST) == null) {
                    $img_src = dirname($url) . '/' . $img_src;
                }
                $file_info = null;
                try {
                    $filename = $image_m->storeRemoteImage($img_src, $file_info);
                    if ($file_info['size'] > 4096) { #4Kb
                        $thumb = array(
                            'full_url' => $ImageHelper->Image($filename, array('temporal' => true)),
                            'filename' => $filename
                        );
                        $result['thumbnails'][] = $thumb;
                        $n_images++;
                    }

                } catch (Exception $e) {
                }
                if ($n_images >= 5) {
                    break;
                }
            }

            $result['content_type'] = 'text';
            return $result;
        } else {
            throw new Exception('Invalid url');
        }
    }
    
}
