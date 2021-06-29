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


interface PploadFilesInterface extends BaseInterface
{
    /**
     * @param $collection_id
     *
     * @return array
     */
    public function fetchFilesForProject($collection_id);

    public function fetchFilesCntForProject($collection_id);

    public function fetchCountDownloadsTodayForProject($collection_id);

    public function fetchCountDownloadsTodayForProjectNew($collection_id);

    public function fetchCountDownloadsForFileAllTime($collectionId, $file_id);

    public function fetchCountDownloadsForFileToday($collectionId, $file_id);

    public function fetchCountDownloadsForFileTodayNew($collectionId, $file_id);

    public function fetchAllFilesForProject($collection_id, $isForAdmin = false);

    public function fetchAllFilesForCollection($collection_ids);

    public function fetchAllActiveFilesForCollection($collection_ids);

    public function fetchAllActiveFilesForProject($collection_id, $isForAdmin = false);

    public function fetchAllInactiveFilesForProject($collection_id, $isForAdmin = false);
}