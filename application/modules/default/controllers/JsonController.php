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

class JsonController extends Zend_Controller_Action
{

    const chat_access_token = 'MDAyMmxvY2F0aW9uIGNoYXQub3BlbmRlc2t0b3Aub3JnCjAwMTNpZGVudGlmaWVyIGtleQowMDEwY2lkIGdlbiA9IDEKMDAzM2NpZCB1c2VyX2lkID0gQG1hZ2dpZWRvbmc6Y2hhdC5vcGVuZGVza3RvcC5vcmcKMDAxNmNpZCB0eXBlID0gYWNjZXNzCjAwMjFjaWQgbm9uY2UgPSBnMSxnRUA2c3AuKyxtYSx4CjAwMmZzaWduYXR1cmUgc3LtmFDiz7wU0TVOdGS7EbEg0wnXVKwXxNqkqe5qpCAK';
    const chat_avatarUrl = 'https://chat.opendesktop.org/_matrix/media/v1/thumbnail';
    const chat_roomPublicUrl = 'https://chat.opendesktop.org/_matrix/client/unstable/publicRooms';
    const chat_roomsUrl = 'https://chat.opendesktop.org/_matrix/client/unstable/rooms/';
    const chat_roomUrl = 'https://chat.opendesktop.org/#/room/';

    protected $_format = 'json';
    public function init()
    {
        parent::init();
        $this->initView();
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
                'OPTIONS', 'HEAD', 'GET', 'POST', 'PUT',
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

    protected function _sendResponse($response, $format = 'json', $xmlRootTag = 'ocs')
    {

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($response);
    }


    public function chatAction()
    {

        $this->_initResponseHeader();
        $config = Zend_Registry::get('config')->settings->client->default;
        $access_token = $config->riot_access_token;
        $urlRooms = JsonController::chat_roomPublicUrl . '?access_token=' . $access_token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $urlRooms);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        curl_close($ch);
        $results = json_decode($data);
        // https://chat.opendesktop.org/_matrix/client/unstable/publicRooms?access_token=

        $rooms = array();
        foreach ($results->chunk as &$room) {
            if ($room->guest_can_join) continue;
            $urlMembers = JsonController::chat_roomsUrl . $room->room_id . '/joined_members?access_token=' . $access_token;
            //https://chat.opendesktop.org/_matrix/client/unstable/rooms/!LNQABMgCYqWKSysjJK%3Achat.opendesktop.org/joined_members?access_token=
            $k = curl_init();
            curl_setopt($k, CURLOPT_AUTOREFERER, true);
            curl_setopt($k, CURLOPT_HEADER, 0);
            curl_setopt($k, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($k, CURLOPT_URL, $urlMembers);
            curl_setopt($k, CURLOPT_FOLLOWLOCATION, true);
            $t = curl_exec($k);
            curl_close($k);
            $r = json_decode($t);
            $room->members = $r->joined;
            $rooms[] = $room;
        }
        $this->_sendResponse($rooms, $this->_format);
    }

    public function forumAction()
    {

        $this->_initResponseHeader();

        $url_forum = Zend_Registry::get('config')->settings->client->default->url_forum;
        $url = $url_forum . '/latest.json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        curl_close($ch);
        $results = json_decode($data);
        $timeago = new Default_View_Helper_PrintDateSince();
        foreach ($results->topic_list->topics as &$t) {

            $strTime = str_replace('T', ' ', substr($t->last_posted_at, 0, 19));

            //$t->timeago = $timeago->printDateSince($strTime);

            $fromFormat = 'Y-m-d H:i:s';
            $date = DateTime::createFromFormat($fromFormat, $strTime);
            // forum/latest.json last_posted_at is 5 hours later as server somehow.. quick workaround
            $date->sub(new DateInterval('PT4H10M'));
            $t->timeago = $timeago->printDateSince($date->format('Y-m-d h:s:m'));
            //$t->timeago =  $date->format('Y-m-d H:i:s');
            $r = 'Reply';
            $counts = $t->posts_count - 1;
            if ($counts == 0) {
                $r = 'Replies';
            } else if ($counts == 1) {
                $r = 'Reply';
            } else {
                $r = 'Replies';
            }
            $t->replyMsg = $counts . ' ' . $r;
        }
        $this->_sendResponse($results, $this->_format);
    }

    public function gitlabnewprojectsAction()
    {

        $this->_initResponseHeader();
        $url_git = Zend_Registry::get('config')->settings->server->opencode->host;
        $url = $url_git . '/api/v4/projects?order_by=created_at&sort=desc&visibility=public&page=1&per_page=5';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        curl_close($ch);
        $results = json_decode($data);
        $timeago = new Default_View_Helper_PrintDateSince();
        foreach ($results as &$t) {
            $tmp = str_replace('T', ' ', substr($t->created_at, 0, 19));
            $t->timeago = $timeago->printDateSince($tmp);
        }
        $this->_sendResponse($results, $this->_format);
    }

    public function gitlabfetchuserAction()
    {

        $this->_initResponseHeader();
        $url_git = Zend_Registry::get('config')->settings->server->opencode->host;
        $url = $url_git . '/api/v4/users?username=' . $this->getParam('username');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        curl_close($ch);
        $results = json_decode($data);
        $this->_sendResponse($results, $this->_format);
    }

    public function cattagsAction()
    {

        $this->_initResponseHeader();
        $catid = $this->getParam('id');
        $results = array();
        if ($catid) {
            $m = new Default_Model_Tags();
            $results = $m->getTagsPerCategory($catid);
        }
        $this->_sendResponse($results, $this->_format);
    }

    public function searchAction()
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
                $ps[] =array('type'=>'project'                    
                    ,'title' =>$p->title
                    ,'project_id' =>$p->project_id
                    ,'member_id'=>$p->member_id
                    ,'username' => $p->username
                    ,'laplace_score' =>$p->laplace_score
                    ,'score' =>$p->score
                    ,'image_small' =>$img);
            }

            $model = new Default_Model_Member();
            $results = $model->findActiveMemberByName($projectSearchText);
            $helperImage = new Default_View_Helper_Image();
            foreach ($results as $value) {
                $avatar = $helperImage->image($value['profile_image_url'],
                    array('width' => 100, 'height' => 100, 'crop' => 2));
                
                $ps[] =array('type'=>'user'
                ,'username'=>$value['username']
                ,'member_id'=>$value['member_id']
                ,'avatar' =>$avatar
                );
            }

            $this->_sendResponse($ps, $this->_format);
            
        } catch (Exception $e) {
            $this->_sendResponse(null, $this->_format);
        }    

        
    }

    public function anonymousdlAction()
    {
        $this->_initResponseHeader();
        $identity = Zend_Auth::getInstance()->getStorage()->read();

        $config = Zend_Registry::get('config');
        $cookieName = $config->settings->session->auth->anonymous;
        $storedInCookie = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : NULL;
        if ($storedInCookie) {
            $model = new Default_Model_DbTable_MemberDownloadHistory();
            if ($identity && $identity->member_id) {
                $dlsection = $model->getAnonymousDLSection($storedInCookie, $identity->member_id);
            } else {
                $dlsection = $model->getAnonymousDLSection($storedInCookie);
            }
            $dls = 0;
            foreach ($dlsection as $value) {
                $dls = $dls + $value['dls'];
            }
            //$dls = $model->countDownloadsAnonymous($storedInCookie);
            $response = array(
                'status'     => 'ok',
                'section' => $dlsection,
                'dls'    => $dls
            );
            $this->_sendResponse($response, $this->_format);
            return;
        }
        $response = array(
            'status'     => 'ok',
            'dls'    => 0
        );
        $this->_sendResponse($response, $this->_format);
    }


}
