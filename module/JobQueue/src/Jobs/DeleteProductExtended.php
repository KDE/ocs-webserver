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

use Application\Model\Repository\ProjectGalleryPictureRepository;
use Application\Model\Repository\ProjectRepository;
use Exception;
use JobQueue\Jobs\Interfaces\JobInterface;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Http\Client;
use Library\Ppload\PploadApi;

class DeleteProductExtended extends BaseJob implements JobInterface
{

    /**
     * @param $args
     *
     * @see  \Application\Model\Service\RegisterManager::sendConfirmationMail
     */
    public function perform($args)
    {
        $this->logger = $GLOBALS['ocs_log'];
        //var_export($args);
        $product = $args['product'];
        // This has to be checked because the ArrayObject is a pure array after serialization
        if (is_array($product)) {
            $GLOBALS['ocs_log']->debug(__METHOD__ . ' - product is an array');
            $product = (object)$args['product'];
        }
        $this->deleteCollectionFromPPload($product);
        $this->deleteImagesFromCdn($product);
    }

    /**
     * @param $product
     */
    private function deleteCollectionFromPPload($product)
    {
        // ppload
        // Delete collection
        $GLOBALS['ocs_log']->debug(__METHOD__ . ' - $product->ppload_collection_id: ' . print_r($product->ppload_collection_id, true));
        if ($product->ppload_collection_id) {
            $pploadApi = new PploadApi(
                array(
                    'apiUri'   => PPLOAD_API_URI,
                    'clientId' => PPLOAD_CLIENT_ID,
                    'secret'   => PPLOAD_SECRET,
                )
            );
            $collectionResponse = $pploadApi->deleteCollection($product->ppload_collection_id);

            //$GLOBALS['ocs-log']->info(__METHOD__ . ' - product delete request for ppload: ' . $product->project_id
            //                          . ' response: ' . print_r($collectionResponse, true));
        }
    }

    /**
     * @param $product
     */
    private function deleteImagesFromCdn($product)
    {
        $db = GlobalAdapterFeature::getStaticAdapter();

        //Remove Logo
        $imgPath = $product->image_small;
        $GLOBALS['ocs_log']->debug(__METHOD__ . ' - $imgPath:' . $imgPath);

        $newPath = null;
        if ($imgPath != "std_avatar_80.png") {
            try {
                $newPath = $this->deleteImageFromCdn($imgPath);
            } catch (Exception $e) {
                $this->logger->err(__METHOD__ . ' - can not delete product picture: ' . $e->getMessage() . '   ' . json_encode($product));
            }

            if (false === empty($newPath)) {
                //save renamed images
                $product->image_small = $newPath;
                $projectTable = new ProjectRepository($db);
                //$projectTable->update(array('image_small' => $product->image_small), 'project_id = '.$product->project_id);
                $projectTable->update(array('image_small' => $product->image_small), "image_small = '" . $imgPath . "'");
            }
        }

        //Remove Gallery Pics
        $galleryPictureTable = new ProjectGalleryPictureRepository($db);
        $pictureRows = $galleryPictureTable->fetchAllRows('project_id = ' . $product->project_id);

        foreach ($pictureRows as $pictureRow) {
            $imgPath = $pictureRow['picture_src'];
            if ($imgPath != "std_avatar_80.png") {
                try {
                    $newPath = $this->deleteImageFromCdn($imgPath);//save renamed images
                    //$galleryPictureTable->update(array('picture_src' => $newPath), 'project_id = ' . $pictureRow['project_id'] . ' AND sequence = ' . $pictureRow['sequence']);
                    $galleryPictureTable->update(array('picture_src' => $newPath), "picture_src = '" . $imgPath . "'");
                } catch (Exception $e) {
                    $this->logger->err(__METHOD__ . ' - can not delete gallery picture: ' .$e->getMessage() . "\n" . print_r($pictureRow, true) . "\n" . $e->getTraceAsString());
                }
            }
        }
    }

    /**
     * @param string $imgPath
     *
     * @return string
     * @throws Exception
     */
    private function deleteImageFromCdn($imgPath)
    {
        if (empty($imgPath)) {
            throw new Exception('image path is empty. (' . $imgPath . ')');
        }

        $config = $GLOBALS['ocs_config'];
        $url = $config->settings->server->images->media->delete;
        $secret = $config->settings->server->images->media->privateKey;

        $postString = '--' . md5(rand()) . md5(rand());
        $url .= '?path=' . urlencode($imgPath) . '&post=' . $postString . '&key=' . $secret;

        $client = new Client($url);
        $client->setMethod('GET');
        $response = $client->send();

        if ($response->getStatusCode() > 200) {
            throw new Exception('URL: ' . $url . ' - server response: ' . $response->getBody());
        }

        //$this->logger->info(__METHOD__ . ' - Result fromCN-Server: ' . $response->getBody());

        //save renamed images
        return $imgPath . $postString;
    }

}
