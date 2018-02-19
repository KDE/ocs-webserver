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
class Default_View_Helper_ProjectFiles extends Zend_View_Helper_Abstract
{

    /**
     * @param int $ppload_collection_id
     *
     * @return array
     */
    public function projectFiles($ppload_collection_id)
    {
        $filesInfos = array();
        // require_once 'Ppload/Api.php';
        $pploadApi = new Ppload_Api(array(
            'apiUri'   => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret'   => PPLOAD_SECRET
        ));

        $fileCount = 0;
        if ($ppload_collection_id) {
            // FIXME: Remove the mark '!' from ppload_collection_id in DB. Because torrent download feature (finalize files) has already dropped.
            $filesRequest = array(
                'collection_id' => ltrim($ppload_collection_id, '!'),
                'perpage'       => 100
            );
            $filesResponse = $pploadApi->getFiles($filesRequest);
            if (isset($filesResponse->status)
                && $filesResponse->status == 'success') {
                $fileCount = $filesResponse->pagination->totalItems;
            }
        }
        $filesInfos['fileCount'] = $fileCount;

        return $filesInfos;
    }

}
