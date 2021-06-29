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

/**
 * Class ExtractComic
 *
 * @package JobQueue\Jobs
 */
class ExtractComic extends AbstractHttpJob implements JobInterface
{

    protected $file;

    /**
     * @param $args
     *
     * @see \Application\Controller\ProductController::addpploadfileAction
     */
    public function perform($args)
    {
        $this->file = $args['file'];
        $this->doCommand();
    }

    public function doCommand()
    {
        return $this->callExtractComic($this->file);
    }

    protected function callExtractComic($file)
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

            $log = isset($this->logger) ? $this->logger : $GLOBALS['ocs_log'];
            $log->debug('**********' . __CLASS__ . '::' . __FUNCTION__ . '**********' . "\n");

            $result = $this->doExternalExtractComic($file->id);

            if (!empty($result) && $result != 'Error') {
                //Done, set has_torrent in table ppload_files
            } else {
                $log->debug("Error on Extracting Comic! Result: " . $result);

                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    public function doExternalExtractComic($fileId)
    {
        $httpClient = $this->getHttpClient();

        $config = $GLOBALS['ocs_config'];
        $torrent = $config->settings->server->comics->media->extracturl . "?id=" . $fileId;

        $uri = $this->generateUri($torrent);

        $httpClient->setUri($uri);
        $response = $this->retrieveBody($httpClient);
        if (false === $response) {
            $this->logger->err(__METHOD__ . " - Error while extracting comic: " . $uri . ".\n Server replay was: " . $httpClient->getLastRawResponse() . PHP_EOL);

            return false;
        }

        $this->logger->debug(__METHOD__ . ' Result: ' . print_r($response, true));

        return $response;
    }

}