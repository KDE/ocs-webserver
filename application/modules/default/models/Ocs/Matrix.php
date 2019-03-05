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
 *
 * Created: 19.06.2018
 */
class Default_Model_Ocs_Matrix
{
    protected $messages;
    protected $auth;
    private $httpServer;
    private $image_mime_types = array(
        // images
        'png'  => 'image/png',
        'jpe'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif'  => 'image/tiff',
        'svg'  => 'image/svg+xml',
        'svgz' => 'image/svg+xml'
    );


    /**
     * @inheritDoc
     */
    public function __construct($config = null)
    {
        if (isset($config)) {
            $this->config = $config;
        } else {
            $this->config = Zend_Registry::get('config')->settings->server->chat;
        }
        $uri = $this->config->host;
        $this->httpServer = new Zend_Http_Client($uri, array('keepalive' => true, 'strictredirects' => true));
    }

    public function setAvatarFromArray($member_data)
    {
        if (empty($member_data)) {
            return false;
        }

        $this->messages = array();

        try {
            $helperImage = new Default_View_Helper_Image();
            $memberAvatarUrl = $helperImage->Image($member_data['profile_image_url']);

            list($fileAvatar,$content_type) = $this->fetchAvatarFile($memberAvatarUrl);
            $mime_type = $this->checkValidMimeType($content_type) ? $content_type : $this->get_mime_content_type($member_data['profile_image_url']);
            $contentUri = $this->uploadAvatar($fileAvatar, $mime_type);
            $matrixUserId = $this->generateUserId(strtolower($member_data['username']));
            $result = $this->setAvatarUrl($matrixUserId, $contentUri);
            if (false === $result) {
                $this->messages[] = "Fail ";

                return false;
            }
        } catch (Exception $e) {
            $this->messages[] = "Fail : " . $e->getMessage();

            return false;
        }
        $this->messages[] = "set avatar file : Success";

        return true;
    }

    private function fetchAvatarFile($profile_image_url)
    {
        $http_client = new Zend_Http_Client($profile_image_url);
        $response = $http_client->request();
        if ($response->getStatus() < 200 OR $response->getStatus() >= 400) {
            $this->messages[] = 'Request failed.(' . $profile_image_url . ') OCS Matrix server send message: ' . $response->getBody();

            return false;
        }
        $filename = IMAGES_UPLOAD_PATH . 'tmp/' . md5($profile_image_url);
        file_put_contents($filename, $response->getBody());
        $content_type = $response->getHeader('content-type');

        return array($filename,$content_type);
    }

    private function get_mime_content_type($filename)
    {

        $mime_types = array(

            'txt'  => 'text/plain',
            'htm'  => 'text/html',
            'html' => 'text/html',
            'php'  => 'text/html',
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'xml'  => 'application/xml',
            'swf'  => 'application/x-shockwave-flash',
            'flv'  => 'video/x-flv',
            // images
            'png'  => 'image/png',
            'jpe'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'ico'  => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif'  => 'image/tiff',
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            // archives
            'zip'  => 'application/zip',
            'rar'  => 'application/x-rar-compressed',
            'exe'  => 'application/x-msdownload',
            'msi'  => 'application/x-msdownload',
            'cab'  => 'application/vnd.ms-cab-compressed',
            // audio/video
            'mp3'  => 'audio/mpeg',
            'qt'   => 'video/quicktime',
            'mov'  => 'video/quicktime',
            // adobe
            'pdf'  => 'application/pdf',
            'psd'  => 'image/vnd.adobe.photoshop',
            'ai'   => 'application/postscript',
            'eps'  => 'application/postscript',
            'ps'   => 'application/postscript',
            // ms office
            'doc'  => 'application/msword',
            'rtf'  => 'application/rtf',
            'xls'  => 'application/vnd.ms-excel',
            'ppt'  => 'application/vnd.ms-powerpoint',
            // open office
            'odt'  => 'application/vnd.oasis.opendocument.text',
            'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $filename_parts = explode('.', $filename);
        $ext = strtolower(array_pop($filename_parts));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } else if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);

            return $mimetype;
        } else {
            return 'image/png';
        }
    }

    private function checkValidMimeType($mime_type)
    {
        if (in_array(strtolower($mime_type), $this->image_mime_types)) {
            return true;
        }

        return false;
    }

    private function uploadAvatar($fileAvatar, $mime_type = 'image/png')
    {
        $auth = $this->authAdmin();

        $filename = basename($fileAvatar);
        $uri = $this->config->host . '/_matrix/media/v1/upload?filename=' . $filename;

        $file_data = file_get_contents(realpath($fileAvatar));
        $client = new Zend_Http_Client($uri);
        $client->setHeaders('Authorization', 'Bearer ' . $auth['access_token']);
        $client->setRawData($file_data, $mime_type);

        $response = $client->request('POST');

        if ($response->getStatus() < 200 OR $response->getStatus() >= 400) {
            $this->messages[] = 'Request failed.(' . $fileAvatar . ') OCS Matrix server send message: ' . $response->getBody();

            return false;
        }

        try {
            $body = Zend_Json_Decoder::decode($response->getBody());
        } catch (Zend_Json_Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }

        return $body['content_uri'];
    }

    private function authAdmin()
    {
        $cache_name = "matrix_auth";
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');

        if (($result = $cache->load($cache_name))) {
            return $result;
        }

        $method = Zend_Http_Client::POST;
        $uri = $this->config->host . "/_matrix/client/r0/login";
        $username = $this->config->sudo_user;
        $pw = $this->config->sudo_user_pw;
        $data = array(
            'identifier'                  => array('type' => 'm.id.user', 'user' => $username),
            'initial_device_display_name' => 'ocs webserver',
            'password'                    => $pw,
            'type'                        => 'm.login.password'
        );
        try {
            $result = $this->httpRequestWithoutToken($uri, $method, $data);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());
        }
        $cache->save($result, $cache_name, array());
        $this->auth = $result;

        return $result;
    }

    private function httpRequestWithoutToken($uri, $method, $post_param)
    {
        $this->httpServer->resetParameters(true);
        $this->httpServer->setUri($uri);
        $this->httpServer->setHeaders('Content-Type', 'application/json');
        $this->httpServer->setHeaders('Accept', 'application/json');
        $this->httpServer->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpServer->setMethod($method);
        if (isset($post_param)) {
            $jsonUserData = Zend_Json::encode($post_param);
            $this->httpServer->setRawData($jsonUserData, 'application/json');
        }

        $response = $this->httpServer->request();
        if ($response->getStatus() < 200 OR $response->getStatus() >= 500) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS Matrix server send message: ' . $response->getBody();

            return false;
        }

        try {
            return Zend_Json::decode($response->getBody());
        } catch (Zend_Json_Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());
        }

        return false;
    }

    private function generateUserId($username)
    {
        $auth = $this->authAdmin();

        return "@{$username}:{$auth['home_server']}";
    }

    private function setAvatarUrl($userId, $contentUri)
    {
        $uri = $this->config->host . "/_matrix/client/r0/profile/{$userId}/avatar_url";
        $method = Zend_Http_Client::PUT;
        $data = array('avatar_url' => $contentUri);

        $result = $this->httpRequest($uri, '', $method, $data);

        return $result;
    }

    /**
     * @param string     $uri
     * @param string     $uid
     * @param string     $method
     * @param array|null $post_param
     *
     * @return bool|array
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    protected function httpRequest($uri, $uid, $method = Zend_Http_Client::GET, $post_param = null)
    {
        $auth = $this->authAdmin();

        $this->httpServer->resetParameters();
        $this->httpServer->setUri($uri);
        $this->httpServer->setHeaders('Authorization', 'Bearer ' . $auth['access_token']);
        $this->httpServer->setHeaders('Content-Type', 'application/json');
        $this->httpServer->setHeaders('Accept', 'application/json');
        $this->httpServer->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpServer->setMethod($method);
        if (isset($post_param)) {
            $jsonUserData = Zend_Json::encode($post_param);
            $this->httpServer->setRawData($jsonUserData, 'application/json');
        }

        $response = $this->httpServer->request();
        if ($response->getStatus() < 200 OR $response->getStatus() >= 400) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS Matrix server send message: ' . $response->getBody();

            return false;
        }

        $body = Zend_Json::decode($response->getBody());

        return $body;
    }

    /**
     * @param array $member_data
     * @param bool  $force
     *
     * @return bool|array
     * @throws Zend_Cache_Exception
     * @throws Zend_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Json_Exception
     */
    public function createUserFromArray($member_data, $force = false)
    {
        if (empty($member_data)) {
            return false;
        }

        $this->messages = array();

        $usernameAvailable = $this->createUserAvailable($member_data['username']);

        if ($usernameAvailable) {
            try {
                $method = Zend_Http_Client::POST;
                $uri = $this->config->host . "/_matrix/client/r0/register?kind=user";
                $session = $this->fetchSessionInfo($uri, Zend_Http_Client::POST, array('auth' => array()));
                $data = array_merge($session, array('username' => $member_data['username'], 'password' => $member_data['password']));
                $userLogin = $this->httpRequestWithoutToken($uri, $method, $data);
                if (false === $userLogin) {
                    $this->messages[] = "Fail ";

                    return false;
                }
                $fileAvatar = $this->fetchAvatarFile($member_data['profile_image_url']);
                $contentUri = $this->uploadAvatar($fileAvatar);
                $result = $this->setAvatarUrl($userLogin['user_id'], $contentUri);
                if (false === $result) {
                    $this->messages[] = "Fail ";

                    return false;
                }
            } catch (Exception $e) {
                $this->messages[] = "Fail : " . $e->getMessage();

                return false;
            }
            $this->messages[] = "Create : Success";

            return $userLogin;
        }

        $this->messages[] = 'Fail : username already exists.';

        return false;
    }

    private function createUserAvailable($username)
    {
        try {
            $result = $this->httpRequest("{$this->config->host}/_matrix/client/r0/register/available?username={$username}", $username);
        } catch (Zend_Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $e->getMessage());
        }

        return (isset($result['available']) && $result['available'] == true) ? true : false;
    }

    private function fetchSessionInfo($uri, $method = Zend_Http_Client::POST, $post_param = null)
    {
        $response = $this->httpRequestWithoutToken($uri, $method, $post_param);

        if ($response) {
            return array('auth' => array('type' => $response['flows'][0]['stages'][0], 'session' => $response['session']));
        }

        return array();
    }

    /**
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    private function requestAccess($uri, $uid, $method, $post_param)
    {
        $this->httpServer->resetParameters(true);
        $this->httpServer->setUri($uri);
        $this->httpServer->setHeaders('Content-Type', 'application/json');
        $this->httpServer->setHeaders('Accept', 'application/json');
        $this->httpServer->setHeaders('User-Agent', $this->config->user_agent);
        $this->httpServer->setMethod($method);
        if (isset($post_param)) {
            $jsonUserData = Zend_Json::encode($post_param);
            $this->httpServer->setRawData($jsonUserData, 'application/json');
        }

        $response = $this->httpServer->request();
        if ($response->getStatus() < 200 OR $response->getStatus() >= 400) {
            $this->messages[] = 'Request failed.(' . $uri . ') OCS Matrix server send message: ' . $response->getBody();

            return false;
        }

        $body = Zend_Json::decode($response->getBody());

        return $body;
    }

}