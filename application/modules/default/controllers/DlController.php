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
class DlController extends Local_Controller_Action_DomainSwitch
{

    public function indexAction()
    {
        $this->_helper->layout->disableLayout();

        $file_id = $this->getParam('file_id');
        $file_type = $this->getParam('file_type');
        $file_name = $this->getParam('file_name');
        $file_size = $this->getParam('file_size');
        $projectId = $this->getParam('project_id');
        $linkType = "download";
        if ($this->hasParam('link_type')) {
            $linkType = $this->getParam('link_type');
        }
        $isExternal = $this->getParam('is_external');
        $externalLink = $this->getParam('external_link');

        $hasTorrent = $this->getParam('has_torrent');

        $modelProduct = new Default_Model_Project();
        $productInfo = $modelProduct->fetchProductInfo($projectId);

        $collectionID = $productInfo->ppload_collection_id;

        $sModel = new Default_Model_Section();
        $section = $sModel->fetchSectionForCategory($productInfo->project_category_id);
        $info = new Default_Model_Info();
        $supporter = $info->getRandomSupporterForSection($section['section_id']);

        $this->view->section_id = $section['section_id'];

        $this->view->link_type = $linkType;
        $this->view->file_name = $file_name;
        $this->view->file_size = $file_size;
        $this->view->file_size_human = $this->humanFileSize($file_size);
        $this->view->project_title = $productInfo->title;
        $this->view->project_owner = $productInfo->username;
        $this->view->project_id = $projectId;
        $this->view->is_external = $isExternal;
        $this->view->external_link = $externalLink;
        $this->view->supporter = $supporter;
        $this->view->has_torrent = ($hasTorrent == "1");
        $this->view->file_id = $file_id;

        $memberId = $this->_authMember->member_id;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $payload = array('id' => $file_id, 'u' => $memberId, 'lt' => $linkType);
            $url = Default_Model_PpLoad::createDownloadUrlJwt($collectionID, $file_name, $payload);

            if ($linkType == 'install') {
                $helperCatXdgType = new Default_View_Helper_CatXdgType();
                $xdgType = $helperCatXdgType->catXdgType($productInfo->project_category_id);

                $url = 'ocs://install'
                       . '?url=' . urlencode($url)
                       . '&type=' . urlencode($xdgType)
                       . '&filename=' . urldecode($file_name);
            }

            $this->view->url = $url;

            // save to member_download_history            
            if (isset($file_id) && isset($projectId)) {

                $server_info = '';

                foreach ($_SERVER as $key => $value) {
                    if ($value) {
                        $server_info = $server_info . $key . ': ' . $value . ' ';
                    }
                }

                // handle cookie
                $config = Zend_Registry::get('config');
                $cookieName = $config->settings->session->auth->anonymous;
                $storedInCookie = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : null;
                if (!$storedInCookie) {
                    $remember_me_seconds = $config->settings->session->remember_me->cookie_lifetime;
                    $cookieExpire = time() + $remember_me_seconds;
                    $hash = hash('sha512', PPLOAD_DOWNLOAD_SECRET . $collectionID . (time() + 3600));
                    $storedInCookie = $hash;
                    setcookie($cookieName, $hash, $cookieExpire, '/');
                }

                $data = array(
                    'project_id'           => $projectId,
                    'member_id'            => $memberId,
                    'anonymous_cookie'     => $storedInCookie,
                    'file_id'              => $file_id,
                    'file_type'            => $file_type,
                    'file_name'            => $file_name,
                    'file_size'            => $file_size,
                    'downloaded_ip'        => $this->getRealIpAddr(),
                    'HTTP_X_FORWARDED_FOR' => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null,
                    'HTTP_X_FORWARDED'     => isset($_SERVER['HTTP_X_FORWARDED']) ? $_SERVER['HTTP_X_FORWARDED'] : null,
                    'HTTP_CLIENT_IP'       => isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : null,
                    'HTTP_FORWARDED_FOR'   => isset($_SERVER['HTTP_FORWARDED_FOR']) ? $_SERVER['HTTP_FORWARDED_FOR'] : null,
                    'HTTP_FORWARDED'       => isset($_SERVER['HTTP_FORWARDED']) ? $_SERVER['HTTP_FORWARDED'] : null,
                    'REMOTE_ADDR'          => $_SERVER['REMOTE_ADDR'],
                    'server_info'          => $server_info
                );

                $memberDlHistory = new Default_Model_DbTable_MemberDownloadHistory();
                $memberDlHistory->createRow($data)->save();
            }
        }
    }

    /**
     * @param int $bytes
     * @return string|null
     */
    public function humanFileSize($bytes)
    {
        if (!empty($bytes)) {
            $size = round($bytes / 1048576, 2);
            if ($size == 0.0) {
                return '0.01 MB';
            } else {
                return $size . ' MB';
            }
        } else {
            return null;
        }
    }

    public function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * @return mixed|null
     */
    protected function getReferer()
    {
        $referer = null;
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        }

        return $referer;
    }

    /**
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

}