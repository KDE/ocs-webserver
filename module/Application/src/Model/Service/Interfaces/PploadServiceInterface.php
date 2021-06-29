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

namespace Application\Model\Service\Interfaces;

use Exception;

interface PploadServiceInterface
{
    /**
     * @param int $collection_id
     * @param int $valid_until
     *
     * @return string
     */
    public static function createDownloadHash($collection_id, $valid_until);

    /**
     * @param int    $collection_id
     * @param string $file_name
     * @param array  $params
     *
     * @return mixed
     */
    public static function createDownloadUrl($collection_id, $file_name, array $params);

    /**
     * @param int    $collection_id
     * @param string $file_name
     * @param array  $payload
     *
     * @return string
     */
    public function createDownloadUrlJwt($collection_id, $file_name, array $payload);

    /**
     * @param int    $projectId
     * @param string $url
     * @param string $filename
     * @param string $fileDescription
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function uploadEmptyFileWithLink($projectId, $url, $filename, $fileDescription);

    /**
     * @param int $creator_id
     *
     * @return bool
     * @throws Exception
     */
    public function isAuthmemberProjectCreator($creator_id);
}