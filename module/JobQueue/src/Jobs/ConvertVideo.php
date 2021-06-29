<?php

/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace JobQueue\Jobs;


use Application\Model\Repository\VideoRepository;
use Application\Model\Service\PploadService;
use JobQueue\Jobs\Interfaces\JobInterface;

/**
 * Class ConvertVideo
 *
 * @package JobQueue\Jobs
 */
class ConvertVideo extends BaseJob implements JobInterface
{

    public static $VIDEO_FILE_TYPES = array(
        'video/3gpp',
        'video/3gpp2',
        'video/mpeg',
        'video/quicktime',
        'video/x-flv',
        'video/webm',
        'application/ogg',
        'video/x-ms-asf',
        'video/x-matroska',
        'video/mp4',
    );

    protected $collectionId;
    protected $fileId;
    protected $fileType;
    protected $videoTable;

    /**
     * @param $args
     *
     * @return bool
     * @see \Application\Controller\ProductController::addpploadfileAction
     * @see \Application\Controller\ProductController::indexAction
     */
    public function perform($args)
    {
        $this->collectionId = $args['collectionId'];
        $this->fileId = $args['fileId'];
        $this->fileType = $args['fileType'];
        //$this->videoTable = new VideoRepository(GlobalAdapterFeature::getStaticAdapter());
        $this->videoTable = $this->serviceManager->get(VideoRepository::class);

        return $this->callConvertVideo($this->collectionId, $this->fileId, $this->fileType);
    }

    protected function callConvertVideo($collectionId, $fileId, $fileType)
    {

        $log = $this->logger;
        $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . '**********' . "\n");

        try {
            /** @var VideoRepository $videoServer */
            $videoServer = $this->videoTable;
            $data = array(
                'id'               => $videoServer->getNewId(),
                'collection_id'    => $collectionId,
                'file_id'          => $fileId,
                'create_timestamp' => 'NOW()',
            );

            $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . ' Insert new VideoPreview: ' . print_r($data, true) . '**********' . "\n");

            $videoServer->insertNewVideo($data);

            $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . ' Insert Done **********' . "\n");

            //call video convert server
            $salt = PPLOAD_DOWNLOAD_SECRET;
            $timestamp = time() + 3600; // one hour valid
//            $hash = hash('sha512', $salt . $collectionId . $timestamp); // order isn't important at all... just do the same when verifying
//            $url = PPLOAD_API_URI . 'files/download/id/' . $fileId . '/s/' . $hash . '/t/' . $timestamp;
//            $url .= '/lt/filepreview/' . $fileId;
            $url = PploadService::createDownloadUrl(
                $collectionId, $fileId, array(
                'id' => $fileId,
                't'  => $timestamp,
                'lt' => 'filepreview',
            )
            );

            $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . ' DL-Url: ' . print_r($url, true) . '**********' . "\n");


            $result = $videoServer->storeExternalVideo($collectionId, $fileType, $url);

            $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . ' storeExternalVideo Result: ' . print_r($result, true) . '**********' . "\n");

            if (!empty($result) && $result != 'Error') {
                //Save Preview URL in DB
                $config = $GLOBALS['ocs_config'];
                $cdnurl = $config->settings->server->videos->media->cdnserver;
                $url_preview = $cdnurl . $collectionId . "/" . $result . ".mp4";
                $url_thumb = $cdnurl . $collectionId . "/" . $result . "_thumb.png";

                $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . ' VideoLink: ' . print_r($url_preview, true) . '**********' . "\n");
                $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . ' VideoThumb: ' . print_r($url_thumb, true) . '**********' . "\n");


                $videoServer->updateVideo($url_preview, $url_thumb, "collection_id = $collectionId AND file_id = $fileId");

            } else {
                $log->debug("Error on Converting Video! Result: " . $result);

                return false;
            }

            return true;

        } catch (Exception $exc) {
            $log->err('**********' . __CLASS__ . '::' . __FUNCTION__ . ' Error: ' . print_r($exc, true) . ' **********' . "\n");

            return false;
        }

    }

}