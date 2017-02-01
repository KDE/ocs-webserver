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
 *
 * Created: 26.01.2017
 */
class Default_Model_PpLoad
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
    }

    public function uploadEmptyFileWithLink($projectId, $url, $filename, $fileDescription)
    {
        $projectId = (int) $projectId;

        $projectData = $this->getProjectData($projectId);

        if (empty($projectData)) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ppload upload error. no project data found. project_id:' . $projectId);
            return false;
        }

        $pploadApi = $this->getPpLoadApi();

        // create empty text file
//        $fileDummy = '/dev/null';
        $fileDummy = '../../data/files/empty';

        $fileRequest = array(
//            'file' => $fileDummy,
            'local_file_path' => $fileDummy,
            'local_file_name' => $filename,
            'owner_id' => $projectData->member_id,
            'tags' => 'link##' . urlencode($url)
        );

        if ($projectData->ppload_collection_id) {
            // Append to existing collection
            $fileRequest['collection_id'] = $projectData->ppload_collection_id;
        }
        if (false == empty($fileDescription)) {
            $fileRequest['description'] = mb_substr($fileDescription, 0, 140);
        }

        //upload to ppload
        $fileResponse = $pploadApi->postFile($fileRequest);

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - fileResponse: ' . print_r($fileResponse, true));

        if (empty($fileResponse) OR empty($fileResponse->file) OR $fileResponse->status <> 'success') {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ppload upload error. requestData:' . print_r($fileRequest, true) . "\n" . 'response:' . print_r($fileResponse, true));
            return false;
        }

        if ($projectData->ppload_collection_id <> $fileResponse->file->collection_id) {
            $projectData->ppload_collection_id = $fileResponse->file->collection_id;
            $projectData->save();
        }

        return $fileResponse;
    }

    /**
     * @param $projectId
     * @return Zend_Db_Table_Row_Abstract
     */
    protected function getProjectData($projectId)
    {
        $projectTable = new Default_Model_DbTable_Project();
        $projectData = $projectTable->find($projectId)->current();
        return $projectData;
    }

    /**
     * @return Ppload_Api
     */
    protected function getPpLoadApi()
    {
        $pploadApi = new Ppload_Api(array(
            'apiUri' => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret' => PPLOAD_SECRET
        ));
        return $pploadApi;
    }

}