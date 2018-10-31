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

class Local_Gitlab_Api
{

    protected $_config = array(
        'apiUri' => 'https://git.opendesktop.cc/api/v4/',
        'token' => ''
    );

    public function __construct(array $config = null)
    {
        if ($config) {
            $this->_config = $config + $this->_config;
        }
    }

    public function getUsers()
    {
        return $this->_request('POST', 'users');
    }

    public function getUserWithName($username)
    {
        return $this->_request('POST', 'users?username='.$username);
    }

    public function getUserWithId($id)
    {
        return $this->_request('POST', 'users/'.$id);
    }

    public function getProjects()
    {
        return $this->_request('POST', 'projects');
    }

    public function getProject($id)
    {
        return $this->_request('POST', 'projects?id='.$id);
    }
    
    public function getProjectIssues($id, $state='opened')
    {
        $result = $this->_request('POST', '/projects/'.$id.'/issues?state='.$state);
        
        return $result;
    }

    public function getUserProjects($user_id)
    {
        return $this->_request('POST', '/users/'.$user_id.'/projects');
    }

    protected function _request($method, $uri = '')
    {
        $timeout = 60; 
        $token = $this->_config['token'];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->_config['apiUri'] . ltrim($uri, '/'));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("PRIVATE-TOKEN: ".$token));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($curl, CURLOPT_POST, true);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        if ($response) {
            $obj = json_decode($response);
            return $obj;
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
