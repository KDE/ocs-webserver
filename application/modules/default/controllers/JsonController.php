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

    const chat_avatarUrl = 'https://chat.opendesktop.org/_matrix/media/v1/thumbnail';
    const chat_roomPublicUrl = 'https://chat.opendesktop.org/_matrix/client/unstable/publicRooms';
    const chat_roomsUrl = 'https://chat.opendesktop.org/_matrix/client/unstable/rooms/';
    const chat_roomUrl = 'https://chat.opendesktop.org/#/room/';
    const chat_userProfileUrl = 'https://chat.opendesktop.org/_matrix/client/r0/profile/';
    const chat_userPresense = 'https://chat.opendesktop.org/_matrix/client/r0/presence/';
    protected $_format = 'json';
    public function init()
    {
        parent::init();
        $this->initView();
        $this->log = Zend_Registry::get('logger');
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

    private function curlRiot($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        curl_close($ch);
        $results = json_decode($data);  
        return $results;
    }

    public function riotAction()
    {
        $chatServer='chat.opendesktop.org';
        $this->_initResponseHeader();
        $config = Zend_Registry::get('config')->settings->client->default;
        $access_token = $config->riot_access_token;
        $p_username = $this->getParam('username');
        $member_data = $this->getUserData($p_username);
        $urlProfile = JsonController::chat_userProfileUrl.'@'.$member_data['username'].':'.$chatServer.'?access_token=' . $access_token;
        //https://chat.opendesktop.org/_matrix/client/r0/profile/@rvs75:chat.opendesktop.org?access_token=       
        $results = $this->curlRiot($urlProfile);   
        $urlPresense = JsonController::chat_userPresense.'@'.$member_data['username'].':'.$chatServer.'/status?access_token=' . $access_token;
        $status =  $this->curlRiot($urlPresense);   
        $resonse=array("user" => $results,"status" => $status);     
        $this->_sendResponse($resonse, $this->_format);
    }

    private function curlNextcloud($url)
    {
        $config = Zend_Registry::get('config')->settings->server->nextcloud;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);       
        curl_setopt($ch, CURLOPT_USERPWD, $config->user_sodo.':'.$config->user_sodo_pw);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true'));         
        $data = curl_exec($ch);
        curl_close($ch);
        $results = json_decode($data);   
        return $results;
    }
    public function plingAction()
    {
        $p_username = $this->getParam('username');
        $member_data = $this->getUserData($p_username);
        if(!$member_data)
        {
            $this->_sendResponse(null, $this->_format);
            return;
        }

       
        $helpPrintDate = new Default_View_Helper_PrintDate();
        $helperImage = new Default_View_Helper_Image();

        $tableProduct = new Default_Model_Project();
        $productsRowset = $tableProduct->fetchAllProjectsForMember($member_data['member_id'],5);
        
        $products = array();
        foreach ($productsRowset as $row) {
            $products[] = $row;
        }
    
        $parray=array();
        foreach ($products as $p) {            
            $tmp= array('project_id'=> $p->project_id    
                , 'image_small'=>$helperImage->Image($p->image_small, array('width' => 200, 'height' => 200))  
                , 'title' => $p->title
                ,'laplace_score' =>$p->laplace_score*10
                ,'cat_title' =>$p->catTitle
                ,'updated_at' => $helpPrintDate->printDate(($p->changed_at==null?$p->created_at:$p->changed_at))
            ) ; 
            $parray[] = $tmp;
        }

        
        $result = array('user'=>$member_data,'products'=>$parray);
        $this->_sendResponse($result, $this->_format);

    }

    public function nextcloudAction()
    {
        
        $config = Zend_Registry::get('config')->settings->server->nextcloud;
        
        $p_username = $this->getParam('username');
        $member_data = $this->getUserData($p_username);
        if(!$member_data)
        {
            $this->_sendResponse(null, $this->_format);
            return;
        }
        $url = $config->host."/ocs/v1.php/cloud/users?search=".$member_data['username']."&format=json";                     
        $results = $this->curlNextcloud($url);
        $status =$results->ocs->meta->status; 
        $usersArray=array();
        if($status== 'ok' && sizeof($results->ocs->data->users)>0)
        {   
            $users = $results->ocs->data->users;
            foreach ($users as $user) {
                $urlUser = $config->host."/ocs/v1.php/cloud/users/".$user."?format=json";  
                $u = $this->curlNextcloud($urlUser);
                if($u->ocs->meta->status=='ok')
                {
                    $usersArray[]= $u->ocs->data;   
                }
                
            }
        }
        $reternUsers = array("users" => $usersArray);

        $this->_sendResponse($reternUsers, $this->_format);
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

    protected function getUserData($p_username)
    {
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        $isAdmin = false;
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
                $isAdmin = true;
        }
        $member_data = null;
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $modelSubSystem = new Default_Model_Ocs_Forum();
            if($isAdmin)
            {               
                if($p_username && $p_username!='null')
                {                
                    $modelMember = new Default_Model_Member();
                    $memberId = $modelMember->fetchActiveUserByUsername($p_username);   
                    if($memberId)
                    {
                        $member = $modelMember->fetchMemberData($memberId);                                         
                        $member_data = array('username'=>$p_username,'member_id' =>$memberId, 'mail'=>$member->mail,'avatar'=>$member->profile_image_url);
                    }
                    
                }else{
                    $authMember = $auth->getStorage()->read();                              
                    $member_data = array('username'=>$authMember->username,'member_id' =>$authMember->member_id 
                    , 'mail'=>$authMember->mail,'avatar'=>$authMember->profile_image_url);    
                }
            }else
            {
                $authMember = $auth->getStorage()->read();                              
                $member_data = array('username'=>$authMember->username,'member_id' =>$authMember->member_id 
                , 'mail'=>$authMember->mail,'avatar'=>$authMember->profile_image_url);
            }
        }           
        return $member_data;  
    }

    public function forumpostsAction()
    {        
        $this->_initResponseHeader();
        $p_username = $this->getParam('username');
        $results=null;        
        $member_data = $this->getUserData($p_username);        
        if($member_data)
        {          
            $modelSubSystem = new Default_Model_Ocs_Forum();
            $user = $modelSubSystem->getUserByUsername($member_data['username']);    
            if($user)
            {
                $results['user'] = $user;
            }
            $posts = $modelSubSystem->getPostsFromUser($member_data);  
                
           
            if($posts)
            {
                $results['posts'] = $posts['posts'];
            }
             
        }            
        $this->_sendResponse($results, $this->_format);
    }

    public function gitlabAction()
    {
        $this->_initResponseHeader();
        $p_username = $this->getParam('username');
        $results=null;        
        $member_data = $this->getUserData($p_username);        
        if ($member_data) {
            $modelSubSystem = new Default_Model_Ocs_Gitlab();                              
            $user = $modelSubSystem->getUserWithName($member_data['username']);    
            //$this->log->info(">>>>>>>results>>>>".json_encode($user));     
            if($user)
            {
                $results['user'] = $user;
                $posts = $modelSubSystem->getUserProjects($user['id']);    
                if($posts)
                {
                    $results['projects'] = $posts;
                }
            }

            //$this->log->info(">>>>>>>results>>>>".json_encode($results));      
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

    public function socialtimelineAction()
    {
        $this->_initResponseHeader();
        /** @var Zend_Cache_Backend_Memcached $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__;
        if (false === ($timelines = $cache->load($cacheName))) {

            $model = new Default_Model_Ocs_Mastodon();
            $timelines = $model->getTimelines();
        
            $helpPrintDate = new Default_View_Helper_PrintDateSince();
            foreach ($timelines as &$m) {               
                if($m['created_at'])
                {                                  
                    $m['created_at'] = $helpPrintDate->printDateSince(str_replace('T', ' ', substr($m['created_at'], 0, 19)));      
                }                            
            }        
            $cache->save($timelines, $cacheName, array(), 60 * 60);
           
        }
        $this->_sendResponse($timelines, $this->_format);
    }

    public function socialuserstatusesAction()
    {
        $this->_initResponseHeader();        
        $p_username = $this->getParam('username');
        $member_data = $this->getUserData($p_username);        
        $result=array();
        $model = new Default_Model_Ocs_Mastodon();
        $user = $model->getUserByUsername($member_data['username']);        
        if(sizeof($user)>0)
        {
            $user=$user[0];
        }else{
            $user=null;
        }
        $result['user'] = $user;
        $statuses=null;        
        if($user && $user['id'])
        {
            $statuses = $model->getUserStatuses($user['id']);
        }
        $result['statuses'] = $statuses;
        $this->_sendResponse($result, $this->_format);      
    }

    public function newsAction()
    {
        $this->_initResponseHeader();

        /** @var Zend_Cache_Backend_Memcached $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__;
        if (false === ($news = $cache->load($cacheName))) {
            $url = 'https://blog.opendesktop.org/?json=1';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $data = curl_exec($ch);
            curl_close($ch);
            $news = json_decode($data);
            $news->posts = array_slice($news->posts, 0, 3);
            $cache->save($news, $cacheName, array(), 60 * 60);
        }

        $this->_sendResponse($news, $this->_format);
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
        if(is_array($projectSearchText)) {
            $projectSearchText = array_pop(array_values($projectSearchText));
        }
        
        $store = null;
        if($this->hasParam('s')){
            $store = $this->getParam('s');
        }
        if($store=='Opendesktop')
        {
            $store = null;
        }

        // $storemodel = new Default_Model_DbTable_ConfigStore(); 
        // $s = $storemodel->fetchDomainObjectsByName($store);
        
        // $currentStoreConfig = new Default_Model_ConfigStore($s['host']);
        // var_dump($currentStoreConfig);        
        // die;
        
        $param = array('q' => $projectSearchText,'store'=>$store,'page' => 1
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
                    ,'cat_title' =>$p->cat_title
                    ,'image_small' =>$img);
            }

            $model = new Default_Model_Member();
            $results = $model->findActiveMemberByName($projectSearchText);
            $helperImage = new Default_View_Helper_Image();
            $ps_user=array();
            foreach ($results as $value) {
                $avatar = $helperImage->image($value['profile_image_url'],
                    array('width' => 100, 'height' => 100, 'crop' => 2));
                
                $ps_user[] =array('type'=>'user'
                ,'username'=>$value['username']
                ,'member_id'=>$value['member_id']
                ,'image_small' =>$avatar
                );
            }

            $searchresult=array();
            $searchresult[] = array('title' =>'Products','values' =>$ps);
            $searchresult[] = array('title' =>'Users','values' =>$ps_user);

            $this->_sendResponse($searchresult, $this->_format);
            
        } catch (Exception $e) {
            $this->_sendResponse(null, $this->_format);
        }            
    }


    public function searchpAction()
    {  
        $this->_initResponseHeader();
        $projectSearchText = $this->getParam('p');
        $projectSearchCategory = $this->getParam('c');
        $store = null;
        if($this->hasParam('s')){
            $store = $this->getParam('s');
        }                
        //$filterCat = 'project_category_id:('.$projectSearchCategory.')';
        //$param = array('q' => $projectSearchText,'store'=>$store,'page' => 1
          //  , 'count' => 10,'fq' => array($filterCat));
        $param = array('q' => $projectSearchText,'store'=>$store,'page' => 1, 'count' => 10);
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
                    ,'cat_title' =>$p->cat_title
                    ,'image_small' =>$img);
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

    public function fetchrandomsupporterAction()
    {
        $this->_initResponseHeader();
        $section_id = $this->getParam('s');
        $info = new Default_Model_Info();
        $s = $info->getRandomSupporterForSection($section_id);        
        $response = array(
            'status'     => 'ok',
            'supporter'    => $s
        );
        $this->_sendResponse($response, $this->_format);
    }


}
