<?php
/** @noinspection PhpUnused */

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

namespace Library\Ppload;

use CURLFile;

/**
 * Class PploadApi
 *
 * ppload-API: https://github.com/KDE/ocs-fileserver/blob/master/docs/ocs-fileserver-API.md
 *
 * @package Library\Ppload
 */
class PploadApi
{

    /**
     * @var array|string[]
     */
    protected $_config = array(
        'apiUri'   => 'https://www.ppload.com/api/',
        'clientId' => '',
        'secret'   => '',
    );

    /**
     * PploadApi constructor.
     *
     * @param array|null $config
     */
    public function __construct(array $config = null)
    {
        if ($config) {
            $this->_config = $config + $this->_config;
        }
    }

    /**
     * @param array|null $params
     *
     * @return false|mixed
     */
    public function getProfiles(array $params = null)
    {
        return $this->_request('GET', 'profiles/index', $params);
    }

    /**
     * @param            $method
     * @param string     $uri
     * @param array|null $params
     *
     * @return false|mixed
     */
    protected function _request($method, $uri = '', array $params = null)
    {
        if (empty($this->_config['apiUri'])) {
            return false;
        }

        $timeout = 60;
        $postFields = array(
            'method' => $method,
            'format' => 'json',
        );
        if ($params) {
            $postFields = $postFields + $params;
        }
        if (isset($postFields['file'])) {
            $timeout = 1200;
            if ($postFields['file'][0] != '@') {
                $postFields['file'] = $this->_getCurlValue($postFields['file']);
            }
        } else {
            $postFields = http_build_query($postFields, '', '&');
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $this->_config['apiUri'] . ltrim($uri, '/'),
            //. '?XDEBUG_SESSION_START=localhost_files',
            CURLOPT_HEADER         => false,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
        ));
        $response = curl_exec($curl);
        $last_error = curl_error($curl);
        curl_close($curl);

        if ($last_error) {
            error_log(__METHOD__ . ' :: ' . $last_error);
        }
        if ($response) {
            return json_decode($response);
        }

        return false;
    }

    /**
     * @param string $filename
     *
     * @return CURLFile|string
     */
    private function _getCurlValue($filename)
    {
        // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
        // See: https://wiki.php.net/rfc/curl-file-upload
        if (function_exists('curl_file_create')) {
            return curl_file_create($filename);
        }

        return "@{$filename}";
    }

    /**
     * @param $id
     *
     * @return false|mixed
     */
    public function getProfile($id)
    {
        return $this->_request('GET', 'profiles/profile', array(
            'id' => $id,
        ));
    }

    /**
     * @param array $params
     *
     * @return false|mixed
     */
    public function postProfile(array $params)
    {
        return $this->_request('POST', 'profiles/profile', array(
                                                               'client_id' => $this->_config['clientId'],
                                                               'secret'    => $this->_config['secret'],
                                                           ) + $params);
    }

    /**
     * @param       $id
     * @param array $params
     *
     * @return false|mixed
     */
    public function putProfile($id, array $params)
    {
        return $this->_request('PUT', 'profiles/profile', array(
                                                              'id'        => $id,
                                                              'client_id' => $this->_config['clientId'],
                                                              'secret'    => $this->_config['secret'],
                                                          ) + $params);
    }

    /**
     * @param $id
     *
     * @return false|mixed
     */
    public function deleteProfile($id)
    {
        return $this->_request('DELETE', 'profiles/profile', array(
            'id'        => $id,
            'client_id' => $this->_config['clientId'],
            'secret'    => $this->_config['secret'],
        ));
    }

    /**
     * @param array|null $params
     *
     * @return false|mixed
     */
    public function getCollections(array $params = null)
    {
        return $this->_request('GET', 'collections/index', $params);
    }

    /**
     * @param $id
     *
     * @return false|mixed
     */
    public function getCollection($id)
    {
        return $this->_request('GET', 'collections/collection', array(
            'id' => $id,
        ));
    }

    /**
     * @param array $params
     *
     * @return false|mixed
     */
    public function postCollection(array $params)
    {
        return $this->_request('POST', 'collections/collection', array(
                                                                     'client_id' => $this->_config['clientId'],
                                                                     'secret'    => $this->_config['secret'],
                                                                 ) + $params);
    }

    /**
     * @param       $id
     * @param array $params
     *
     * @return false|mixed
     */
    public function putCollection($id, array $params)
    {
        return $this->_request('PUT', 'collections/collection', array(
                                                                    'id'        => $id,
                                                                    'client_id' => $this->_config['clientId'],
                                                                    'secret'    => $this->_config['secret'],
                                                                ) + $params);
    }

    /**
     * @param $id
     *
     * @return false|mixed
     */
    public function deleteCollection($id)
    {
        return $this->_request('DELETE', 'collections/collection', array(
            'id'        => $id,
            'client_id' => $this->_config['clientId'],
            'secret'    => $this->_config['secret'],
        ));
    }

    /**
     * @param array|null $params
     *
     * @return false|mixed
     */
    public function getFiles(array $params = null)
    {
        return $this->_request('GET', 'files/index', $params);
    }

    /**
     * @param $id
     *
     * @return false|mixed
     */
    public function getFile($id)
    {
        return $this->_request('GET', 'files/file', array(
            'id' => $id,
        ));
    }

    /**
     * @param array $params
     *
     * @return false|mixed
     */
    public function postFile(array $params)
    {
        return $this->_request('POST', 'files/file', array(
                                                         'client_id' => $this->_config['clientId'],
                                                         'secret'    => $this->_config['secret'],
                                                     ) + $params);
    }

    /**
     * @param       $id
     * @param array $params
     *
     * @return false|mixed
     */
    public function putFile($id, array $params)
    {
        return $this->_request('PUT', 'files/file', array(
                                                        'id'        => $id,
                                                        'client_id' => $this->_config['clientId'],
                                                        'secret'    => $this->_config['secret'],
                                                    ) + $params);
    }

    /**
     * @param $id
     *
     * @return false|mixed
     */
    public function deleteFile($id)
    {
        return $this->_request('DELETE', 'files/file', array(
            'id'        => $id,
            'client_id' => $this->_config['clientId'],
            'secret'    => $this->_config['secret'],
        ));
    }

    /**
     * @param array|null $params
     *
     * @return false|mixed
     */
    public function getFavorites(array $params = null)
    {
        return $this->_request('GET', 'favorites/index', $params);
    }

    /**
     * @param $id
     *
     * @return false|mixed
     */
    public function getFavorite($id)
    {
        return $this->_request('GET', 'favorites/favorite', array(
            'id' => $id,
        ));
    }

    /**
     * @param array $params
     *
     * @return false|mixed
     */
    public function postFavorite(array $params)
    {
        return $this->_request('POST', 'favorites/favorite', array(
                                                                 'client_id' => $this->_config['clientId'],
                                                                 'secret'    => $this->_config['secret'],
                                                             ) + $params);
    }

    /**
     * @param $id
     *
     * @return false|mixed
     */
    public function deleteFavorite($id)
    {
        return $this->_request('DELETE', 'favorites/favorite', array(
            'id'        => $id,
            'client_id' => $this->_config['clientId'],
            'secret'    => $this->_config['secret'],
        ));
    }

    /**
     * @param $id
     *
     * @return false|mixed
     */
    public function deleteOwner($id)
    {
        return $this->_request('DELETE', 'owners/owner', array(
            'id'        => $id,
            'client_id' => $this->_config['clientId'],
            'secret'    => $this->_config['secret'],
        ));
    }

    /**
     * @param array|null $params
     *
     * @return false|mixed
     */
    public function getMediaGenres(array $params = null)
    {
        return $this->_request('GET', 'media/genres', $params);
    }

    /**
     * @param array|null $params
     *
     * @return false|mixed
     */
    public function getMediaOwners(array $params = null)
    {
        return $this->_request('GET', 'media/owners', $params);
    }

    /**
     * @param array|null $params
     *
     * @return false|mixed
     */
    public function getMediaCollections(array $params = null)
    {
        return $this->_request('GET', 'media/collections', $params);
    }

    /**
     * @param array|null $params
     *
     * @return false|mixed
     */
    public function getMediaIndex(array $params = null)
    {
        return $this->_request('GET', 'media/index', $params);
    }

    /**
     * @param $id
     *
     * @return false|mixed
     */
    public function getMedia($id)
    {
        return $this->_request('GET', 'media/media', array(
            'id' => $id,
        ));
    }

    /**
     * @param       $id
     * @param array $params
     *
     * @return false|mixed
     */
    public function postMediaCollectionthumbnail($id, array $params)
    {
        return $this->_request('POST', 'media/collectionthumbnail', array(
                                                                        'id'        => $id,
                                                                        'client_id' => $this->_config['clientId'],
                                                                        'secret'    => $this->_config['secret'],
                                                                    ) + $params);
    }

    /**
     * @param       $id
     * @param array $params
     *
     * @return false|mixed
     */
    public function postMediaAlbumthumbnail($id, array $params)
    {
        return $this->_request('POST', 'media/albumthumbnail', array(
                                                                   'id'        => $id,
                                                                   'client_id' => $this->_config['clientId'],
                                                                   'secret'    => $this->_config['secret'],
                                                               ) + $params);
    }

}
