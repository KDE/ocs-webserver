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

namespace Application\Model\Interfaces;


interface ImageInterface extends BaseInterface
{

    public function storeRemoteImage($url, $fileExtension = null, &$file_info = null);

    public function file_get_contents_curl($url);

    public function _get_mime_content_type($filename);

    public function saveImageOnMediaServer($filePathName);

    /**
     * @param  $formFileElement
     *
     * @return array
     */
    public function saveImages($formFileElement);

    public function getAllowedFileExtension();

    public function getAllowedMimeTypes();

    public function getAllowed();

    public function setAllowed($allowed);

    public function getMaxsize();

    public function setMaxsize($maxsize);
}