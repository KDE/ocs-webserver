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

use Application\Model\Service\InfoService;
use Application\Model\Service\MemberService;
use Application\Model\Service\MemberSettingValueService;
use Application\Model\Service\Ocs\Forum;
use Application\Model\Service\Util;
use Laminas\View\Model\JsonModel;
use stdClass;

/**
 * Class MembersettingController
 *
 * @package Application\Controller
 */
class MembersettingController extends BaseController
{

    const GROUP_METAHEADER = 1;
    protected $_format = 'json';
    private $memberSettingValueService;
    private $forumService;
    private $memberService;
    private $infoService;

    public function __construct(
        MemberSettingValueService $memberSettingValueService,
        MemberService $memberService,
        Forum $forumService,
        InfoService $infoService
    ) {
        parent::__construct();
        $this->memberSettingValueService = $memberSettingValueService;
        $this->forumService = $forumService;
        $this->memberService = $memberService;
        $this->infoService = $infoService;
    }

    public function indexAction()
    {
        $this->_sendErrorResponse(999, 'unknown request');
    }

    protected function _sendErrorResponse($statuscode, $message = '')
    {
        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'failed',
                'statuscode' => $statuscode,
                'message'    => $message,
            );
        }

        return $this->_sendResponse($response, $this->_format);
    }

    protected function _sendResponse($response, $format = 'json', $xmlRootTag = 'ocs')
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Pragma: public');
        header('Cache-Control: cache, must-revalidate');
        $duration = 60; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";
        header('Expires: ' . $expires);
        $callback = $this->params()->fromQuery('callback');
        if ($callback != "") {
            header('Content-Type: text/javascript; charset=UTF-8');
            // strip all non alphanumeric elements from callback
            $callback = preg_replace('/[^a-zA-Z0-9_]/', '', $callback);
            $response = $callback . '(' . json_encode($response) . ')';

            return new JsonModel($response);
        } else {
            header('Content-Type: application/json; charset=UTF-8');

            return new JsonModel((array)$response);
        }
    }

    public function getsettingsAction()
    {
        $this->_initResponseHeader();
        $identity = $this->ocsUser;
        if ($identity == null || $identity->member_id == null) {
            $response = array(
                'status' => 'error',
                'msg'    => 'no user found',
            );

            return $this->_sendResponse($response, $this->_format);
        }
        $model = $this->memberSettingValueService;
        $member_id = $identity->member_id;

        $results = $model->findMemberSettings($member_id, self::GROUP_METAHEADER);
        $response = array(
            'status'    => 'ok',
            'member_id' => $member_id,
            'results'   => $results,
        );

        return $this->_sendResponse($response, $this->_format);
    }

    /*public function getsettingsAction()
    {
        $this->_helper->layout->disableLayout();
    }*/

    protected function _initResponseHeader()
    {
        http_response_code(200);

        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'], true);
            header('Access-Control-Allow-Credentials: true', true);
            header('Access-Control-Max-Age: 1728000', true);
        }
        // if (!empty($_SERVER['HTTP_REFERER'])) {
        //     header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_REFERER'], true);
        //     header('Access-Control-Allow-Credentials: true', true);
        //     header('Access-Control-Max-Age: 1728000', true);
        // }


        if (!empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header(
                'Access-Control-Allow-Methods: ' . implode(
                    ', ', array_unique(
                            [
                                'OPTIONS',
                                'HEAD',
                                'GET',
                                'POST',
                                'PUT',
                                strtoupper($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']),
                            ]
                        )
                ), true
            );
        }

        if (!empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'], true);
            header('Access-Control-Expose-Headers: Authorization, Content-Type, Accept', true);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }

        header('Content-Type: application/json; charset=UTF-8', true);

    }

    public function setsettingsAction()
    {
        $this->_initResponseHeader();
        $identity = $this->ocsUser;
        if ($identity == null || $identity->member_id == null) {
            $response = array(
                'status' => 'error',
                'msg'    => 'no user found',
            );
        } else {
            $model = $this->memberSettingValueService;
            $member_id = $identity->member_id;
            $member_setting_item_id = $this->params()->fromPost('itemid');
            $value = $this->params()->fromPost('itemvalue');
            $model->updateOrInsertSetting($member_id, $member_setting_item_id, null, $value);
            $response = array(
                'status' => 'ok',
            );
        }

        return $this->_sendResponse($response, $this->_format);
    }

    public function notificationAction()
    {
        $this->_initResponseHeader();
        $identity = $this->ocsUser;
        if ($identity == null || $identity->member_id == null) {
            $response = array(
                'status' => 'error',
                'msg'    => 'no user found',
            );

            return $this->_sendResponse($response, $this->_format);

        }
        $modelForum = $this->forumService;
        $data = $modelForum->getUserNotifications($identity->member_id);

        $results = $this->arrayCastRecursive($data);

        return $this->_sendResponse($results, $this->_format);

    }

    function arrayCastRecursive($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = $this->arrayCastRecursive($value);
                }
                if ($value instanceof stdClass) {
                    $array[$key] = $this->arrayCastRecursive((array)$value);
                }
            }
        }
        if ($array instanceof stdClass) {
            return $this->arrayCastRecursive((array)$array);
        }

        return $array;
    }

    // public function anonymousdlAction()
    // {
    //
    //     $this->_initResponseHeader();
    //     $identity = Zend_Auth::getInstance()->getStorage()->read();
    //     if($identity==null)
    //     {
    //         $config = Zend_Registry::get('config');
    //         $cookieName = $config->settings->session->auth->anonymous;
    //         $storedInCookie = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : NULL;
    //         if($storedInCookie)
    //         {
    //             $memberDlAnonymous = new Default_Model_DbTable_MemberDownloadAnonymous();
    //             $dls = $memberDlAnonymous->countDownloads($storedInCookie);
    //
    //             $response = array(
    //             'status'     => 'ok',
    //             'dls'    => $dls
    //             );
    //             $this->_sendResponse($response, $this->_format);
    //             return;
    //         }
    //
    //     }
    //
    //     $response = array(
    //         'status'     => 'ok',
    //         'dls'    => 0
    //         );
    //     $this->_sendResponse($response, $this->_format);
    //
    // }

    public function memberjsonAction()
    {
        $this->_initResponseHeader();
        $identity = $this->ocsUser;
        $member_id = (int)$this->params()->fromQuery('member_id');
        $results = null;
        if ($member_id) {
            $info = $this->infoService;
            $commentsOpendeskop = $info->getDiscussionOpendeskop($member_id);
            $results = array('commentsOpendeskop' => $commentsOpendeskop);
        }

        return $this->_sendResponse($results, $this->_format);
    }

    public function searchmemberAction()
    {
        $this->_initResponseHeader();
        $username = $this->params()->fromQuery('username');
        $results = null;
        if (strlen(trim($username)) > 2) {
            $username = str_replace("_", "\_", $username);
            $model = $this->memberService;
            $results = $model->findActiveMemberByName($username);

            foreach ($results as &$value) {
                $avatar = Util::image(
                    $value['profile_image_url'], array('width' => 100, 'height' => 100, 'crop' => 2)
                );
                $value['profile_image_url'] = $avatar;
            }
        }

        return $this->_sendResponse($results, $this->_format);
    }

}
