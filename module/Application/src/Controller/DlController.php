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

namespace Application\Controller;

use Application\Model\Repository\MemberDownloadHistoryRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Service\InfoService;
use Application\Model\Service\PploadService;
use Application\Model\Service\SectionService;
use Application\Model\Service\Util;
use Application\View\Helper\CatXdgType;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Request;

/**
 * Class DlController
 *
 * @package Application\Controller
 */
class DlController extends DomainSwitch
{

    protected $configArray;
    /**
     * Zend_Controller_Request_Abstract object wrapping the request environment
     */
    protected $_request = null;
    /** @var  int */
    protected $_projectId;
    /** @var  int */
    protected $_collectionId;
    /** @var  string */
    protected $_browserTitlePrepend;

    protected $_authMember;

    protected $isAdmin = false;

    protected $projectRepository;
    protected $projectCategoryRepository;
    protected $infoService;
    protected $sectionService;
    protected $pploadService;
    protected $memberDlHistory;

    public function __construct(
        AdapterInterface $db,
        array $config,
        Request $request,
        InfoService $infoService,
        ProjectCategoryRepository $projectCategoryRepository,
        ProjectRepository $projectRepository,
        SectionService $sectionService,
        PploadService $pploadService,
        MemberDownloadHistoryRepository $memberDlHistory
    ) {
        parent::__construct($db, $config, $request);
        parent::init();
        $this->projectCategoryRepository = $projectCategoryRepository;
        $this->projectRepository = $projectRepository;
        $this->infoService = $infoService;
        $this->sectionService = $sectionService;
        $this->pploadService = $pploadService;
        $this->memberDlHistory = $memberDlHistory;

        $this->_authMember = $this->view->getVariable('ocs_user');
        $this->configArray = $config;

    }

    public function indexAction()
    {
        $this->view->setTerminal(true);

        $file_id = $this->getParam('file_id');
        $file_type = $this->getParam('file_type');
        $file_name = $this->getParam('file_name');
        $file_size = $this->getParam('file_size');
        $projectId = $this->getParam('project_id');
        $linkType = "download";
        if ($this->getParam('link_type')) {
            $linkType = $this->getParam('link_type');
        }
        $isExternal = $this->getParam('is_external');
        $externalLink = $this->getParam('external_link');

        $hasTorrent = $this->getParam('has_torrent');

        $modelProduct = $this->projectRepository;
        $productInfo = $modelProduct->fetchProductInfo($projectId);
        if (null == $productInfo) {
            $GLOBALS['ocs_log']->info(
                __CLASS__ . '.' . __FUNCTION__ . ': Project not found for ProjectId: ' . $projectId
            );

            return $this->getResponse()->setStatusCode(404);
        }
        $productInfo = Util::arrayToObject($productInfo);

        $collectionID = $productInfo->ppload_collection_id;

        $sModel = $this->sectionService;
        $section = $sModel->fetchSectionForCategory($productInfo->project_category_id);

        $info = $this->infoService;
        $supporter = $info->getRandomSupporterForSection($section['section_id']);

        $this->view->setVariable('section_id', $section['section_id']);

        $this->view->setVariable('link_type', $linkType);
        $this->view->setVariable('file_name', $file_name);
        $this->view->setVariable('file_size', $file_size);
        $this->view->setVariable('file_size_human', $this->humanFileSize($file_size));
        $this->view->setVariable('project_title', $productInfo->title);
        $this->view->setVariable('project_owner', $productInfo->username);
        $this->view->setVariable('project_id', $projectId);
        $this->view->setVariable('is_external', $isExternal);
        $this->view->setVariable('external_link', $externalLink);
        $this->view->setVariable('supporter', $supporter);
        $this->view->setVariable('has_torrent', ($hasTorrent == "1"));
        $this->view->setVariable('file_id', $file_id);
        $this->view->setVariable('isAdmin', ($this->_authMember->roleName == "admin"));

        $memberId = $this->_authMember->member_id;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $payload = array('id' => $file_id, 'u' => $memberId, 'lt' => $linkType);
            // when a searchbot recognized we doesn't render the download url
            $url = '';
            if (false === SEARCHBOT_DETECTED) {
                if ($this->getParam('download_source') == 'Continue Download From Mirror') {
                    $url = $this->pploadService->createDownloadUrlJwtFromMirror($collectionID, $file_name, $payload);
                } else {
                    $url = $this->pploadService->createDownloadUrlJwt($collectionID, $file_name, $payload);
                }
            }

            if ($linkType == 'install') {
                $helperCatXdgType = new CatXdgType($this->projectCategoryRepository);
                $xdgType = $helperCatXdgType->catXdgType($productInfo->project_category_id);

                $url = 'ocs://install'
                       . '?url=' . urlencode($url)
                       . '&type=' . urlencode($xdgType)
                       . '&filename=' . urldecode($file_name);
            }

            $this->view->setVariable('url', $url);

            // save to member_download_history            
            if (isset($file_id) && isset($projectId)) {

                $server_info = '';

                foreach ($_SERVER as $key => $value) {
                    if ($value) {
                        if (is_array($value) or is_object($value)) {
                            $server_info = $server_info . $key . ': ' . json_encode($value) . ' ';
                        } else {
                            $server_info = $server_info . $key . ': ' . $value . ' ';
                        }
                    }
                }

                // handle cookie
                $config = $this->config;
                $cookieName = $config->settings->session->anonymous_cookie_name;
                $storedInCookie = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : null;
                if (!$storedInCookie) {
                    $remember_me_seconds = $config->settings->session->cookie_lifetime;
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
                    'file_type'            => empty($file_type) ? 'application/octet-stream' : $file_type,
                    'file_name'            => $file_name,
                    'file_size'            => $file_size,
                    'downloaded_ip'        => $this->getRealIpAddr(),
                    'HTTP_X_FORWARDED_FOR' => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null,
                    'HTTP_X_FORWARDED'     => isset($_SERVER['HTTP_X_FORWARDED']) ? $_SERVER['HTTP_X_FORWARDED'] : null,
                    'HTTP_CLIENT_IP'       => isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : null,
                    'HTTP_FORWARDED_FOR'   => isset($_SERVER['HTTP_FORWARDED_FOR']) ? $_SERVER['HTTP_FORWARDED_FOR'] : null,
                    'HTTP_FORWARDED'       => isset($_SERVER['HTTP_FORWARDED']) ? $_SERVER['HTTP_FORWARDED'] : null,
                    'REMOTE_ADDR'          => $_SERVER['REMOTE_ADDR'],
                    // for later use
                    //'server_info'          => $server_info,
                );

                if (false === SEARCHBOT_DETECTED) {
                    $memberDlHistory = $this->memberDlHistory;
                    $memberDlHistory->insert($data);
                }
            }
        }

        return $this->view;
    }

    /**
     * @param int $bytes
     *
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
     *
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