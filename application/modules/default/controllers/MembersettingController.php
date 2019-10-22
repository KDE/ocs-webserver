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
class MembersettingController extends Zend_Controller_Action
{

    const GROUP_METAHEADER = 1;
    protected $_format = 'json';

    public function init()
    {
        parent::init();
        $this->initView();
        // $this->_initResponseHeader();
    }

    public function initView()
    {
        // Disable render view
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function indexAction()
    {
        $this->_sendErrorResponse(999, 'unknown request');
    }

    public function getsettingsAction()
    {
        $this->_initResponseHeader();
        $identity = Zend_Auth::getInstance()->getStorage()->read();
        if ($identity == null || $identity->member_id == null) {
            $response = array(
                'status' => 'error',
                'msg'    => 'no user found'
            );
            $this->_sendResponse($response, $this->_format);

            return;
        }
        $model = new Default_Model_MemberSettingValue();
        $member_id = $identity->member_id;

        $results = $model->findMemberSettings($member_id, $this::GROUP_METAHEADER);
        $response = array(
            'status'    => 'ok',
            'member_id' => $member_id,
            'results'   => $results
        );
        $this->_sendResponse($response, $this->_format);
    }

    protected function _initResponseHeader()
    {
        http_response_code(200);

        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'], true);
            header('Access-Control-Allow-Credentials: true', true);
            header('Access-Control-Max-Age: 1728000', true);
        }


        if (!empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header('Access-Control-Allow-Methods: ' . implode(', ', array_unique([
                    'OPTIONS',
                    'HEAD',
                    'GET',
                    'POST',
                    'PUT',
                    strtoupper($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])
                ])), true);
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

    /*public function getsettingsAction()
    {
        $this->_helper->layout->disableLayout();
    }*/

    protected function _sendResponse($response, $format = 'json', $xmlRootTag = 'ocs')
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($response);
    }

    public function setsettingsAction()
    {
        $this->_initResponseHeader();
        $identity = Zend_Auth::getInstance()->getStorage()->read();
        if ($identity == null || $identity->member_id == null) {
            $response = array(
                'status' => 'error',
                'msg'    => 'no user found'
            );
        } else {
            $model = new Default_Model_MemberSettingValue();
            $member_id = $identity->member_id;
            $member_setting_item_id = $this->getParam('itemid');
            $value = $this->getParam('itemvalue');
            $model->updateOrInsertSetting($member_id, $member_setting_item_id, null, $value);
            $response = array(
                'status' => 'ok'
            );
        }
        $this->_sendResponse($response, $this->_format);
    }

    public function notificationAction()
    {
        $this->_initResponseHeader();
        $identity = Zend_Auth::getInstance()->getStorage()->read();
        if ($identity == null || $identity->member_id == null) {
            $response = array(
                'status' => 'error',
                'msg'    => 'no user found'
            );
            $this->_sendResponse($response, $this->_format);

            return;
        }
        $modelForum = new Default_Model_Ocs_Forum();
        $data = $modelForum->getUserNotifications($identity->member_id);
        $results = $data;

        $this->_sendResponse($results, $this->_format);

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
        $identity = Zend_Auth::getInstance()->getStorage()->read();
        $member_id = $this->getParam('member_id');
        $results = null;
        if ($member_id) {
            $info = new Default_Model_Info();
            $commentsOpendeskop = $info->getDiscussionOpendeskop($member_id);
            $results = array('commentsOpendeskop' => $commentsOpendeskop);
        }
        $this->_sendResponse($results, $this->_format);
    }

    public function searchmemberAction()
    {
        $this->_initResponseHeader();
        $username = $this->getParam('username');
        $results = null;
        if (strlen(trim($username)) > 2) {
            $username = str_replace("_", "\_", $username);
            $model = new Default_Model_Member();
            $results = $model->findActiveMemberByName($username);
            $helperImage = new Default_View_Helper_Image();
            foreach ($results as &$value) {
                $avatar = $helperImage->image($value['profile_image_url'],
                    array('width' => 100, 'height' => 100, 'crop' => 2));
                $value['profile_image_url'] = $avatar;
            }
        }
        $this->_sendResponse($results, $this->_format);
    }

    public function searchproductsAction()
    {
        $this->_initResponseHeader();
        $projectSearchText = $this->getParam('p');
        $param = array('q' => $projectSearchText,'store'=>null,'page' => 1
            , 'count' => 10);
        $viewHelperImage = new Default_View_Helper_Image();
        $modelSearch = new Default_Model_Solr();   
        try {
            $result = $modelSearch->search($param);
            $products = $result['hits'];                      
            $ps=array();
            foreach ($products as $p) {
                $img = $viewHelperImage->Image($p->image_small, array(
                    'width'  => 50,
                    'height' => 50
                ));
                $ps[] =array('description'=>$p->description
                    ,'title' =>$p->title
                    ,'project_id' =>$p->project_id
                    ,'member_id'=>$p->member_id
                    ,'username' => $p->username
                    ,'laplace_score' =>$p->laplace_score
                    ,'score' =>$p->score
                    ,'image_small' =>$img);
            }
            $this->_sendResponse($ps, $this->_format);
            
        } catch (Exception $e) {
            $this->_sendResponse(null, $this->_format);
        }    
    }

    public function userinfoAction()
    {
        $this->_initResponseHeader();
        $member_id = $this->getParam('member_id');
        $info = new Default_Model_Info();
        $data = $info->getTooptipForMember($member_id);
        $this->_sendResponse($data, $this->_format);
    }

}
