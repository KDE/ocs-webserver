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

namespace Application\Model\Service;

use Application\Model\Repository\ProjectRepository;
use Application\Model\Service\Interfaces\PploadServiceInterface;
use ArrayObject;
use Exception;use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;use Library\Ppload\PploadApi;

class PploadService extends BaseService implements PploadServiceInterface
{
    protected $db;
    private $config;
    private $PPLOAD_API_URI;
    private $PPLOAD_CLIENT_ID;
    private $PPLOAD_SECRET;
    private $PPLOAD_HOST;
    private $PPLOAD_DOWNLOAD_SECRET;

    public function __construct(
        AdapterInterface $db,
        array $config
    ) {
        $this->config = $config;
        $this->db = $db;
        $this->PPLOAD_API_URI = $config['ocs_config']['settings']['server']['files']['api']['uri'];
        $this->PPLOAD_CLIENT_ID = $config['ocs_config']['settings']['server']['files']['api']['client_id'];
        $this->PPLOAD_SECRET = $config['ocs_config']['settings']['server']['files']['api']['client_secret'];
        $this->PPLOAD_HOST = $config['ocs_config']['settings']['server']['files']['host'];
        $this->PPLOAD_DOWNLOAD_SECRET = $config['ocs_config']['settings']['server']['files']['download_secret'];
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
     *
     * @return string
     */
    public static function createDownloadHash($collection_id, $valid_until)
    {
        // order isn't important at all... just do the same when verifying
        return hash('sha512', PPLOAD_DOWNLOAD_SECRET . $collection_id . $valid_until);
    }

    //TODO: add jwt stats from session

    /**
     * @param int    $collection_id
     * @param string $file_name
     * @param array  $payload
     *
     * @return string
     */
    public function createDownloadUrlJwt($collection_id, $file_name, array $payload)
    {
        $valid_until = time() + 3600; // one hour valid
        $hash = self::createDownloadHash($collection_id, $valid_until);
        $payload['s'] = $hash;
        $payload['t'] = $valid_until;
        try {
            //$session = new Zend_Session_Namespace();
            $session = $GLOBALS['ocs_session'];
            $payload['stfp'] = $session->stat_fp;
            $payload['stip'] = $session->stat_ipv6 ? $session->stat_ipv6 : $session->stat_ipv4;
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . '   ' . $e->getMessage());
            error_log(__METHOD__ . '   ' . $e->getMessage());
        }
        $jwt = JwtService::encodeFromArray($payload);

        return $this->PPLOAD_API_URI . 'files/download/j/' . $jwt . '/' . $file_name;
    }

    /**
     * @param int    $collection_id
     * @param string $file_name
     * @param array  $payload
     *
     * @return string
     */
    public function createDownloadUrlJwtFromMirror($collection_id, $file_name, array $payload)
    {
        $valid_until = time() + 3600; // one hour valid
        $hash = self::createDownloadHash($collection_id, $valid_until);
        $payload['s'] = $hash;
        $payload['t'] = $valid_until;
        try {
            //$session = new Zend_Session_Namespace();
            $session = $GLOBALS['ocs_session'];
            $payload['stfp'] = $session->stat_fp;
            $payload['stip'] = $session->stat_ipv6 ? $session->stat_ipv6 : $session->stat_ipv4;
        } catch (Exception $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . '   ' . $e->getMessage());
            error_log(__METHOD__ . '   ' . $e->getMessage());
        }
        $jwt = JwtService::encodeFromArray($payload);

        return $this->config['ocs_config']['settings']['server']['files']['api']['mirror'] . 'files/download/j/' . $jwt . '/' . $file_name;
    }

    /**
     * @param int    $projectId
     * @param string $url
     * @param string $filename
     * @param string $fileDescription
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function uploadEmptyFileWithLink($projectId, $url, $filename, $fileDescription)
    {
        $projectId = (int)$projectId;

        $projectData = $this->getProjectData($projectId);
        $log = $GLOBALS['ocs_log'];

        if (empty($projectData)) {
            $log->err(__METHOD__ . ' - ppload upload error. no project data found. project_id:' . $projectId);

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
            'tags'            => 'link##' . urlencode($url),
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

        $log->debug(__METHOD__ . ' - fileResponse: ' . print_r($fileResponse, true));

        if (empty($fileResponse) or empty($fileResponse->file) or $fileResponse->status <> 'success') {
            $log->err(
                __METHOD__ . ' - ppload upload error. requestData:' . print_r($fileRequest, true) . "\n" . 'response:' . print_r($fileResponse, true)
            );

            return false;
        }
        $projectTable = new ProjectRepository($this->db);
        if ($projectData->ppload_collection_id <> $fileResponse->file->collection_id) {
            $projectData->ppload_collection_id = $fileResponse->file->collection_id;
            $data = array('ppload_collection_id' => $fileResponse->file->collection_id);
            if ($this->isAuthmemberProjectCreator($projectData->member_id)) {
                $data['changed_at'] = new Expression('NOW()');
            } else {
                //$auth = Zend_Auth::getInstance();
                //$authMember = $auth->getStorage()->read();
                $authMember = $GLOBALS['ocs_user'];
                $log->info('********** ' . __METHOD__ . ' Project ChangedAt is not set: Auth-Member (' . $authMember->member_id . ') != Project-Owner (' . $projectData->member_id . '): **********' . "\n");
            }
            $projectTable->update($data, 'project_id = ' . $projectData->project_id);
        } else {
            if ($this->isAuthmemberProjectCreator($projectData->member_id)) {
                $data = array('changed_at' => new Expression('NOW()'));
                $projectTable = new ProjectRepository($this->db);
                $projectTable->update($data, 'project_id = ' . $projectData->project_id);
            } else {
                //$auth = Zend_Auth::getInstance();
                //$authMember = $auth->getStorage()->read();
                $authMember = $GLOBALS['ocs_user'];
                $log->info('********** ' . __METHOD__ . ' Project ChangedAt is not set: Auth-Member (' . $authMember->member_id . ') != Project-Owner (' . $projectData->member_id . '): **********' . "\n");
            }
        }

        return $fileResponse;
    }

    /**
     * @param int $projectId
     *
     * @return |ArrayObject|null
     */
    protected function getProjectData($projectId)
    {
        $projectTable = new ProjectRepository($this->db);

        return $projectTable->findById($projectId);
    }

    /**
     * @return PploadApi
     */
    protected function getPpLoadApi()
    {
        return new PploadApi(
            array(
                'apiUri'   => $this->PPLOAD_API_URI,
                'clientId' => $this->PPLOAD_CLIENT_ID,
                'secret'   => $this->PPLOAD_SECRET,
            )
        );
    }

    /**
     * @param int $creator_id
     *
     * @return bool
     */
    public function isAuthmemberProjectCreator($creator_id)
    {
        /** @var \Application\Model\Entity\CurrentUser $authMember */
        $authMember = $GLOBALS['ocs_user'];
        if ($authMember->member_id == $creator_id) {
            return true;
        }

        return false;
    }
}