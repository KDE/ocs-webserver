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

use Laminas\Http\Client;
use Laminas\Uri\Uri;
use Laminas\Uri\UriFactory;
use Laminas\Uri\UriInterface;

abstract class AbstractHttpJob extends BaseJob
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

    /**
     * @return Client
     */
    public function getHttpClient()
    {
        $httpClient = new Client(null, $this->_config);

        return $httpClient;
    }

    /**
     * @param Client $httpClient
     *
     * @return string
     */
    public function retrieveBody($httpClient)
    {
        // $response = $httpClient->request();
        $response = $httpClient->send();
        if ($response->getStatusCode() < 200 or $response->getStatusCode() >= 400) {

            return false;
        } else {
            return $response->getBody();
        }
    }

    /**
     * @param $url
     *
     * @return Uri|UriInterface
     */
    protected function generateUri($url)
    {
        $uri = UriFactory::factory($url);

        return $uri;
    }

}