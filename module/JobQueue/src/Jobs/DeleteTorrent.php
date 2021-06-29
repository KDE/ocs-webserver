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


use JobQueue\Jobs\Interfaces\JobInterface;

class DeleteTorrent extends AbstractHttpJob implements JobInterface
{

    /**
     * Configuration for HTTP-Client
     *
     * @var array
     */
    protected $_config = array(
        'maxredirects' => 0,
        'timeout'      => 21600,
    );

    protected $file;

    /**
     * @param $args
     */
    public function perform($args)
    {
        var_export($args);
        $this->file = $args['file'];
        $this->doCommand();
    }

    public function doCommand()
    {

        return $this->callDeleteTorrent($this->file);
    }

    protected function callDeleteTorrent($file)
    {

        $link = null;
        $isExternLink = false;
        if ($file->tags) {
            $tags = explode(',', $file->tags);
            foreach ($tags as $t) {
                $tagStr = explode('##', $t);
                if (sizeof($tagStr) == 2 && $tagStr[0] == 'link') {
                    $link = $tagStr[1];
                }
            }
        }


        if ($link) {
            $isExternLink = true;
        }

        if (!$isExternLink) {

            $log = $this->logger;
            $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . '**********' . "\n");

            $result = $this->deleteExternalTorrent($file->id);

            if (!empty($result) && $result != 'Error') {
                //Done, set has_torrent in table ppload_files
            } else {
                $log->debug("Error on Deleting Torrent! Result: " . $result);

                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    public function deleteExternalTorrent($fileId)
    {
        $httpClient = $this->getHttpClient();

        $config = $GLOBALS['ocs_config'];
        $torrenturl = $config->torrent->media->deleteurl . "?id=" . $fileId;

        $uri = $this->generateUri($torrenturl);

        $httpClient->setUri($uri);
        $response = $this->retrieveBody($httpClient);
        if (false === $response) {
            $this->logger->err(
                __METHOD__ . " - Error while deleting torrent: " . $uri . ".\n Server replay was: " . $httpClient->getLastResponse()
                                                                                                                 ->getStatus() . ". " . $httpClient->getLastResponse()
                                                                                                                                                   ->getMessage() . PHP_EOL
            );

            return false;
        }

        $this->logger->debug(__METHOD__ . ' Result: ' . print_r($response, true));

        return $response;
    }

}