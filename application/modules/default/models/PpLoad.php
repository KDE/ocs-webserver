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

    public static function createDownloadUrl($collection_id, $file_name, array $params)
    {
        $valid_until = time() + 3600; // one hour valid
        $hash = self::createDownloadHash($collection_id, $valid_until);
        $url = PPLOAD_API_URI . 'files/download';
        foreach ($params as $key => $param) {
            $url .= '/' . $key . '/' . $param;
        }

        return $url . '/s/' . $hash . '/t/' . $valid_until . '/' . $file_name;
    }

    /**
     * @param int $collection_id
     * @param int $valid_until
     * @return string
     */
    public static function createDownloadHash($collection_id, $valid_until)
    {
        return hash('sha512',
            PPLOAD_DOWNLOAD_SECRET . $collection_id . $valid_until); // order isn't important at all... just do the same when verifying
    }

    /**
     * @param int    $collection_id
     * @param string $file_name
     * @param array  $payload
     * @return string
     */
    public static function createDownloadUrlJwt($collection_id, $file_name, array $payload)
    {
        $valid_until = time() + 3600; // one hour valid
        $hash = self::createDownloadHash($collection_id, $valid_until);
        $payload['s'] = $hash;
        $payload['t'] = $valid_until;
        try {
            $session = new Zend_Session_Namespace();
            $payload['stfp'] = $session->stat_fp;
            $payload['stip'] = $session->stat_ipv6 ? $session->stat_ipv6 : $session->stat_ipv4;
        } catch (Zend_Session_Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . '   ' . $e->getMessage());
//            error_log(__METHOD__ . '   ' . $e->getMessage());
        }
        $jwt = Default_Model_Jwt::encodeFromArray($payload);

        return PPLOAD_API_URI . 'files/download/j/' . $jwt . '/' . $file_name;
    }

    /**
     * @param int    $projectId
     * @param string $url
     * @param string $filename
     * @param string $fileDescription
     * @return bool|mixed
     * @throws Zend_Auth_Storage_Exception
     * @throws Zend_Exception
     */
    public function uploadEmptyFileWithLink($projectId, $url, $filename, $fileDescription)
    {
        $projectId = (int)$projectId;

        $projectData = $this->getProjectData($projectId);

        if (empty($projectData)) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ppload upload error. no project data found. project_id:'
                                              . $projectId);

            return false;
        }

        $pploadApi = $this->getPpLoadApi();

        // create empty text file
        $fileDummy = '../../data/files/empty';

        $fileRequest = array(
            //            'file' => $fileDummy,
            'local_file_path' => $fileDummy,
            'local_file_name' => $filename,
            'owner_id'        => $projectData->member_id,
            'tags'            => 'link##' . urlencode($url)
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
            Zend_Registry::get('logger')->err(__METHOD__
                                              . ' - ppload upload error. requestData:'
                                              . print_r($fileRequest, true) . "\n" . 'response:'
                                              . print_r($fileResponse, true)
            );

            return false;
        }
        $log = Zend_Registry::get('logger');
        if ($projectData->ppload_collection_id <> $fileResponse->file->collection_id) {
            $projectData->ppload_collection_id = $fileResponse->file->collection_id;
            if ($this->isAuthmemberProjectCreator($projectData->member_id)) {
                $projectData->changed_at = new Zend_Db_Expr('NOW()');
            } else {
                $auth = Zend_Auth::getInstance();
                $authMember = $auth->getStorage()->read();
                $log->info('********** ' . __METHOD__ . ' Project ChangedAt is not set: Auth-Member (' . $authMember->member_id . ') != Project-Owner (' . $projectData->member_id . '): **********' . "\n");
            }
            $projectData->save();
        } else {
            if ($this->isAuthmemberProjectCreator($projectData->member_id)) {
                $projectData->changed_at = new Zend_Db_Expr('NOW()');
                $projectData->save();
            } else {
                $auth = Zend_Auth::getInstance();
                $authMember = $auth->getStorage()->read();
                $log->info('********** ' . __METHOD__ . ' Project ChangedAt is not set: Auth-Member (' . $authMember->member_id . ') != Project-Owner (' . $projectData->member_id . '): **********' . "\n");
            }
        }

        return $fileResponse;
    }

    /**
     * @param int $projectId
     *
     * @return Zend_Db_Table_Row_Abstract
     * @throws Zend_Db_Table_Exception
     */
    protected function getProjectData($projectId)
    {
        $projectTable = new Default_Model_DbTable_Project();

        return $projectTable->find($projectId)->current();
    }

    /**
     * @return Ppload_Api
     */
    protected function getPpLoadApi()
    {
        return new Ppload_Api(array(
            'apiUri'   => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret'   => PPLOAD_SECRET
        ));
    }

    /**
     * @param int $creator_id
     * @return bool
     * @throws Zend_Auth_Storage_Exception
     */
    public function isAuthmemberProjectCreator($creator_id)
    {
        $auth = Zend_Auth::getInstance();
        $authMember = $auth->getStorage()->read();
        if ($authMember->member_id == $creator_id) {
            return true;
        }

        return false;
    }

}