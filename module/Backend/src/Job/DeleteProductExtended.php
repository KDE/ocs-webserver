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

namespace Backend\Job;


use Application\Job\Interfaces\JobInterface;

/**
 * Class DeleteProductExtended
 * @package Backend\Job
 * @deprecated
 */
class DeleteProductExtended extends BaseJob
{

    /**
     * @see  \Application\Model\Service\RegisterManager::sendConfirmationMail
     * @param $args
     */
    public function perform($args)
    {
        var_export($args);
        $product = $args['product'];       
        $this->deleteCollectionFromPPload($product);
    }

    


    private function deleteCollectionFromPPload($product)
    {
        // ppload
        // Delete collection
        if ($product->ppload_collection_id) {
            $pploadApi = new \Library\Ppload\PploadApi(array(
                'apiUri'   => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret'   => PPLOAD_SECRET
            ));            
            $collectionResponse = $pploadApi->deleteCollection($product->ppload_collection_id);

            $GLOBALS['ocs_log']->info(__METHOD__ . ' - product delete request for ppload: ' . $product->project_id
                . ' response: ' . print_r($collectionResponse,
                    true));
        }
    }

}