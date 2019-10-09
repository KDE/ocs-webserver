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

// ppload-API: https://github.com/KDE/ocs-fileserver/blob/master/docs/ocs-fileserver-API.md

class Ppload_Api
{

    protected $_config = array(
        'apiUri' => 'https://www.ppload.com/api/',
        'clientId' => '',
        'secret' => ''
    );

    public function __construct(array $config = null)
    {
        if ($config) {
            $this->_config = $config + $this->_config;
        }
    }

    public function getProfiles(array $params = null)
    {
        return $this->_request('GET', 'profiles/index', $params);
    }

    public function getProfile($id)
    {
        return $this->_request('GET', 'profiles/profile', array(
            'id' => $id
        ));
    }

    public function postProfile(array $params)
    {
        return $this->_request('POST', 'profiles/profile', array(
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ) + $params);
    }

    public function putProfile($id, array $params)
    {
        return $this->_request('PUT', 'profiles/profile', array(
            'id' => $id,
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ) + $params);
    }

    public function deleteProfile($id)
    {
        return $this->_request('DELETE', 'profiles/profile', array(
            'id' => $id,
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ));
    }

    public function getCollections(array $params = null)
    {
        return $this->_request('GET', 'collections/index', $params);
    }

    public function getCollection($id)
    {
        return $this->_request('GET', 'collections/collection', array(
            'id' => $id
        ));
    }

    public function postCollection(array $params)
    {
        return $this->_request('POST', 'collections/collection', array(
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ) + $params);
    }

    public function putCollection($id, array $params)
    {
        return $this->_request('PUT', 'collections/collection', array(
            'id' => $id,
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ) + $params);
    }

    public function deleteCollection($id)
    {
        return $this->_request('DELETE', 'collections/collection', array(
            'id' => $id,
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ));
    }

    public function getFiles(array $params = null)
    {
        return $this->_request('GET', 'files/index', $params);
    }

    public function getFile($id)
    {
        return $this->_request('GET', 'files/file', array(
            'id' => $id
        ));
    }

    public function postFile(array $params)
    {
        return $this->_request('POST', 'files/file', array(
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ) + $params);
    }

    public function putFile($id, array $params)
    {
        return $this->_request('PUT', 'files/file', array(
            'id' => $id,
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ) + $params);
    }

    public function deleteFile($id)
    {
        return $this->_request('DELETE', 'files/file', array(
            'id' => $id,
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ));
    }

    public function getFavorites(array $params = null)
    {
        return $this->_request('GET', 'favorites/index', $params);
    }

    public function getFavorite($id)
    {
        return $this->_request('GET', 'favorites/favorite', array(
            'id' => $id
        ));
    }

    public function postFavorite(array $params)
    {
        return $this->_request('POST', 'favorites/favorite', array(
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ) + $params);
    }

    public function deleteFavorite($id)
    {
        return $this->_request('DELETE', 'favorites/favorite', array(
            'id' => $id,
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ));
    }

    public function deleteOwner($id)
    {
        return $this->_request('DELETE', 'owners/owner', array(
            'id' => $id,
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ));
    }

    public function getMediaGenres(array $params = null)
    {
        return $this->_request('GET', 'media/genres', $params);
    }

    public function getMediaOwners(array $params = null)
    {
        return $this->_request('GET', 'media/owners', $params);
    }

    public function getMediaCollections(array $params = null)
    {
        return $this->_request('GET', 'media/collections', $params);
    }

    public function getMediaIndex(array $params = null)
    {
        return $this->_request('GET', 'media/index', $params);
    }

    public function getMedia($id)
    {
        return $this->_request('GET', 'media/media', array(
            'id' => $id
        ));
    }

    public function postMediaCollectionthumbnail($id, array $params)
    {
        return $this->_request('POST', 'media/collectionthumbnail', array(
            'id' => $id,
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ) + $params);
    }

    public function postMediaAlbumthumbnail($id, array $params)
    {
        return $this->_request('POST', 'media/albumthumbnail', array(
            'id' => $id,
            'client_id' => $this->_config['clientId'],
            'secret' => $this->_config['secret']
        ) + $params);
    }

    protected function _request($method, $uri = '', array $params = null)
    {
        if (empty($this->_config['apiUri'])) {
            return false;
        }

        $timeout = 60;
        $postFields = array(
            'method' => $method,
            'format' => 'json'
        );
        if ($params) {
            $postFields = $postFields + $params;
        }
        if (isset($postFields['file'])) {
            $timeout = 600;
            if ($postFields['file'][0] != '@') {
                $postFields['file'] = $this->_getCurlValue($postFields['file']);
            }
        }
        else {
            $postFields = http_build_query($postFields, '', '&');
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->_config['apiUri'] . ltrim($uri, '/'),
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            return json_decode($response);
        }
        return false;
    }

    /**
     * @param string $filename
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

}
