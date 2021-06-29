<?php /** @noinspection PhpUnused */

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

use Application\Model\Interfaces\MemberDownloadHistoryInterface;
use Application\Model\Service\InfoService;
use Application\Model\Service\Interfaces\ProjectServiceInterface;
use Application\Model\Service\MemberService;
use Application\Model\Service\Ocs\Forum;
use Application\Model\Service\Ocs\Gitlab;
use Application\Model\Service\Ocs\Mastodon;
use Application\Model\Service\SolrService;
use Application\Model\Service\TagService;
use Application\Model\Service\Util;
use Exception;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Json\Decoder;
use Laminas\View\Model\JsonModel;

/**
 * Class JsonController
 *
 * @package Application\Controller
 */
class JsonController extends BaseController
{

    const chat_avatarUrl = 'https://chat.opendesktop.org/_matrix/media/v1/thumbnail';
    const chat_roomPublicUrl = 'https://chat.opendesktop.org/_matrix/client/unstable/publicRooms';
    const chat_roomsUrl = 'https://chat.opendesktop.org/_matrix/client/unstable/rooms/';
    const chat_roomUrl = 'https://chat.opendesktop.org/#/room/';
    const chat_userProfileUrl = 'https://chat.opendesktop.org/_matrix/client/r0/profile/';
    const chat_userPresense = 'https://chat.opendesktop.org/_matrix/client/r0/presence/';
    protected $_format = 'json';

    private $projectService;
    private $forumService;
    private $memberService;
    private $gitlabService;
    /** @var AbstractAdapter $cache */
    private $ocsCache;
    private $tagService;
    private $solrService;
    private $memberDownloadHistoryRepository;
    private $infoService;

    public function __construct(
        ProjectServiceInterface $projectService,
        MemberService $memberService,
        Forum $forumService,
        Gitlab $gitlabService,
        TagService $tagService,
        SolrService $solrService,
        MemberDownloadHistoryInterface $memberDownloadHistoryRepository,
        InfoService $infoService
    ) {
        parent::__construct();
        $this->projectService = $projectService;
        $this->forumService = $forumService;
        $this->memberService = $memberService;
        $this->gitlabService = $gitlabService;
        $this->ocsCache = $GLOBALS['ocs_cache'];
        $this->tagService = $tagService;
        $this->solrService = $solrService;
        $this->memberDownloadHistoryRepository = $memberDownloadHistoryRepository;
        $this->infoService = $infoService;
    }

    public function indexAction()
    {
        return $this->_sendErrorResponse(999, 'unknown request');
    }

    protected function _sendErrorResponse($statuscode, $message = '')
    {
        $response = '';

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
        //header('Content-Type: application/json; charset=UTF-8');
        //return new JsonModel($response);
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

    public function chatAction()
    {
        $this->_initResponseHeader();
        $config = $this->ocsConfig->settings->client->default;
        $access_token = $config->riot_access_token;
        $urlRooms = self::chat_roomPublicUrl . '?access_token=' . $access_token;

        $results = $this->curlRequest($urlRooms);
        // https://chat.opendesktop.org/_matrix/client/unstable/publicRooms?access_token=

        $rooms = array();
        foreach ($results->chunk as &$room) {
            if ($room->guest_can_join) {
                continue;
            }
            $urlMembers = self::chat_roomsUrl . $room->room_id . '/joined_members?access_token=' . $access_token;
            //https://chat.opendesktop.org/_matrix/client/unstable/rooms/!LNQABMgCYqWKSysjJK%3Achat.opendesktop.org/joined_members?access_token=
            $r = $this->curlRequest($urlMembers);
            $room->members = $r->joined;
            $rooms[] = $room;
        }

        return $this->_sendResponse($rooms, $this->_format);
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

    /**
     * @param $url
     *
     * @return mixed
     */
    private function curlRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        curl_close($ch);

        return Decoder::decode($data);
    }

    public function riotAction()
    {
        $chatServer = 'chat.opendesktop.org';
        $this->_initResponseHeader();
        $config = $this->ocsConfig->settings->client->default;
        $access_token = $config->riot_access_token;
        $p_username = $this->params()->fromQuery('username');
        $member_data = $this->getUserData($p_username);
        $urlProfile = self::chat_userProfileUrl . '@' . $member_data['username'] . ':' . $chatServer . '?access_token=' . $access_token;
        //https://chat.opendesktop.org/_matrix/client/r0/profile/@rvs75:chat.opendesktop.org?access_token=
        $results = $this->curlRiot($urlProfile);
        $urlPresense = self::chat_userPresense . '@' . $member_data['username'] . ':' . $chatServer . '/status?access_token=' . $access_token;
        $status = $this->curlRiot($urlPresense);
        $resonse = array("user" => $results, "status" => $status);

        return $this->_sendResponse($resonse, $this->_format);
    }

    protected function getUserData($p_username)
    {

        $isAdmin = $this->isAdmin();

        $member_data = null;
        $auth = $this->ocsUser;
        if ($auth->hasIdentity()) {
            $modelSubSystem = $this->forumService;
            if ($isAdmin) {
                if ($p_username && $p_username != 'null') {
                    $modelMember = $this->memberService;
                    $memberId = $modelMember->fetchActiveUserByUsername($p_username);
                    if ($memberId) {
                        $member = $modelMember->fetchMemberData($memberId);
                        $member_data = array(
                            'username'  => $p_username,
                            'member_id' => $memberId,
                            'mail'      => $member->mail,
                            'avatar'    => $member->profile_image_url,
                        );
                    }

                } else {
                    $authMember = $auth;
                    $member_data = array(
                        'username'  => $authMember->username,
                        'member_id' => $authMember->member_id,
                        'mail'      => $authMember->mail,
                        'avatar'    => $authMember->profile_image_url,
                    );
                }
            } else {
                $authMember = $auth;
                $member_data = array(
                    'username'  => $authMember->username,
                    'member_id' => $authMember->member_id,
                    'mail'      => $authMember->mail,
                    'avatar'    => $authMember->profile_image_url,
                );
            }
        }

        return $member_data;
    }

    private function curlRiot($url)
    {
        return $this->curlRequest($url);
    }

    public function plingAction()
    {
        $p_username = $this->params()->fromQuery('username');
        $member_data = $this->getUserData($p_username);
        if (!$member_data) {
            return $this->_sendResponse(null, $this->_format);

        }


        $tableProduct = $this->projectService;
        $productsRowset = $tableProduct->fetchAllProjectsForMember($member_data['member_id'], 5);

        $products = array();
        foreach ($productsRowset as $row) {
            $products[] = $row;
        }

        $parray = array();
        foreach ($products as $p) {
            $tmp = array(
                'project_id'    => $p->project_id,
                'image_small'   => Util::image($p->image_small, array('width' => 200, 'height' => 200)),
                'title'         => $p->title,
                'laplace_score' => $p->laplace_score * 10,
                'cat_title'     => $p->catTitle,
                'updated_at'    => Util::printDate(($p->changed_at == null ? $p->created_at : $p->changed_at)),
            );
            $parray[] = $tmp;
        }


        $result = array('user' => $member_data, 'products' => $parray);

        return $this->_sendResponse($result, $this->_format);

    }

    public function nextcloudAction()
    {

        $config = $this->ocsConfig->settings->server->nextcloud;

        $p_username = $this->params()->fromQuery('username');
        $member_data = $this->getUserData($p_username);
        if (!$member_data) {
            return $this->_sendResponse(null, $this->_format);

        }
        $url = $config->host . "/ocs/v1.php/cloud/users?search=" . $member_data['username'] . "&format=json";
        $results = $this->curlNextcloud($url);
        $status = $results->ocs->meta->status;
        $usersArray = array();
        if ($status == 'ok' && sizeof($results->ocs->data->users) > 0) {
            $users = $results->ocs->data->users;
            foreach ($users as $user) {
                $urlUser = $config->host . "/ocs/v1.php/cloud/users/" . $user . "?format=json";
                $u = $this->curlNextcloud($urlUser);
                if ($u->ocs->meta->status == 'ok') {
                    $usersArray[] = $u->ocs->data;
                }

            }
        }
        $reternUsers = array("users" => $usersArray);

        return $this->_sendResponse($reternUsers, $this->_format);
    }

    private function curlNextcloud($url)
    {
        $config = $this->ocsConfig->settings->server->nextcloud;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $config->user_sodo . ':' . $config->user_sodo_pw);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true'));
        $data = curl_exec($ch);
        curl_close($ch);

        return Decoder::decode($data);
    }

    public function forumAction()
    {

        $this->_initResponseHeader();

        $url_forum = $this->ocsConfig->settings->client->default->url_forum;
        $url = $url_forum . '/latest.json';
        $results = $this->curlRequest($url);
        if (!$results || !$results->topic_list) {
            return $this->_sendResponse([], $this->_format);
        }
        foreach ($results->topic_list->topics as &$t) {
            $t->timeago = Util::printDateSinceForum($t->last_posted_at);
            // $r = 'Reply';
            // $counts = $t->posts_count - 1;
            // if ($counts == 0) {
            //     $r = 'Replies';
            // } else if ($counts == 1) {
            //     $r = 'Reply';
            // } else {
            //     $r = 'Replies';
            // }
            // $t->replyMsg = $counts . ' ' . $r;
        }

        return $this->_sendResponse($results, $this->_format);
    }

    public function forumpostsAction()
    {
        $this->_initResponseHeader();
        $p_username = $this->params()->fromQuery('username');
        $results = null;
        $member_data = $this->getUserData($p_username);
        if ($member_data) {
            $modelSubSystem = $this->forumService;
            $user = $modelSubSystem->getUserByUsername($member_data['username']);
            if ($user) {
                $results['user'] = $user;
            }
            $posts = $modelSubSystem->getPostsFromUser($member_data);


            if ($posts) {
                $results['posts'] = $posts['posts'];
            }

        }

        return $this->_sendResponse($results, $this->_format);
    }

    public function gitlabAction()
    {
        $this->_initResponseHeader();
        $p_username = $this->params()->fromQuery('username');
        $results = null;
        $member_data = $this->getUserData($p_username);
        if ($member_data) {
            $results = array();
            $modelSubSystem = $this->gitlabService;
            $user = (array)$modelSubSystem->getUserWithName($member_data['username']);
            //$this->log->info(">>>>>>>results>>>>".Encoder::encode($user));
            if ($user != null) {
                if (is_array($user)) {
                    $results['user'] = (array)$user;
                    $posts = $modelSubSystem->getUserProjects($user['id']);
                    if ($posts) {
                        $results['projects'] = $posts;
                    }
                }
            }

            //$this->log->info(">>>>>>>results>>>>".Encoder::encode($results));
        }

        return $this->_sendResponse($results, $this->_format);
    }

    public function gitlabnewprojectsAction()
    {

        $this->_initResponseHeader();
        $url_git = $this->ocsConfig->settings->server->opencode->host;
        $url = $url_git . '/api/v4/projects?order_by=created_at&sort=desc&visibility=public&page=1&per_page=5';
        $results = $this->curlRequest($url);
        foreach ($results as &$t) {
            $tmp = str_replace('T', ' ', substr($t->created_at, 0, 19));
            $t->timeago = Util::printDateSince($tmp);
        }

        return $this->_sendResponse($results, $this->_format);
    }

    public function gitlabfetchuserAction()
    {
        $this->_initResponseHeader();
        $url_git = $this->ocsConfig->settings->server->opencode->host;
        $url = $url_git . '/api/v4/users?username=' . $this->params()->fromQuery('username');
        $results = $this->curlRequest($url);

        return $this->_sendResponse($results, $this->_format);
    }

    public function socialtimelineAction()
    {
        $this->_initResponseHeader();
        $cache = $this->ocsCache;
        $cacheName = __FUNCTION__;
        $timelines = $cache->getItem($cacheName);
        if (!$timelines) {
            $model = new Mastodon();
            $timelines = (array)$model->getTimelines();
            if ($timelines) {
                foreach ($timelines as &$m) {
                    if (array_key_exists('created_at', $m) && $m['created_at']) {
                        $m['created_at'] = Util::printDateSince(str_replace('T', ' ', substr($m['created_at'], 0, 19)));
                    }
                }
            } else {
                $timelines = array();
            }
            $cache->setItem($cacheName, $timelines);
        }

        return $this->_sendResponse((array)$timelines, $this->_format);
    }

    public function socialuserstatusesAction()
    {
        $this->_initResponseHeader();
        $p_username = $this->params()->fromQuery('username');
        //$p_username = $this->getParam('username');
        $member_data = $this->getUserData($p_username);
        $result = array();
        $model = new Mastodon();
        $user = (array)$model->getUserByUsername($member_data['username']);
        if (sizeof($user) > 0) {
            $user = $user[0];
        } else {
            $user = null;
        }
        $result['user'] = $user;
        $statuses = null;
        if ($user && $user['id']) {
            $statuses = $model->getUserStatuses($user['id']);
        }
        $result['statuses'] = $statuses;

        return $this->_sendResponse((array)$result, $this->_format);
    }

    public function newsAction()
    {
        $this->_initResponseHeader();

        $cacheName = __FUNCTION__;
        if ($this->ocsCache->hasItem($cacheName)) {
            $news = $this->ocsCache->getItem($cacheName);

        } else {
            $url = 'https://blog.opendesktop.org/?json=1';
            $news = $this->curlRequest($url);
            $news->posts = array_slice($news->posts, 0, 3);
            $this->ocsCache->setItem($cacheName, $news);
        }

        return $this->_sendResponse($news, $this->_format);
    }

    public function cattagsAction()
    {

        $this->_initResponseHeader();
        $catid = $this->params()->fromRoute("id");
        //$catid = $this->getParam('id');
        $results = array();
        $results = $this->tagService->getTopTagsPerCategory($catid);        
        return $this->_sendResponse($results, $this->_format);
    }

    public function searchAction()
    {
        $this->_initResponseHeader();
        $projectSearchText = $this->params()->fromRoute("p");
        //$projectSearchText = $this->getParam('p');
        if (is_array($projectSearchText)) {
            $values = array_values($projectSearchText);
            $projectSearchText = array_pop($values);
        }

        $store = null;
        $store = $this->params()->fromRoute("s");
        if ($store == 'Opendesktop') {
            $store = null;
        }

        // $storemodel = new Default_Model_DbTable_ConfigStore();
        // $s = $storemodel->fetchDomainObjectsByName($store);

        // $currentStoreConfig = new Default_Model_ConfigStore($s['host']);
        // var_dump($currentStoreConfig);
        // die;

        $param = array(
            'q'     => $projectSearchText,
            'store' => $store,
            'page'  => 1,
            'count' => 10,
        );

        try {
            $ps = $this->doSearch($this->solrService, $param);

            $model = $this->memberService;
            $results = $model->findActiveMemberByName($projectSearchText);

            $ps_user = array();
            foreach ($results as $value) {
                $avatar = Util::image($value['profile_image_url'], array('width' => 100, 'height' => 100, 'crop' => 2));

                $ps_user[] = array(
                    'type'        => 'user',
                    'username'    => $value['username'],
                    'member_id'   => $value['member_id'],
                    'image_small' => $avatar,
                );
            }

            $search_result = array();
            $search_result[] = array('title' => 'Products', 'values' => $ps);
            $search_result[] = array('title' => 'Users', 'values' => $ps_user);

            return $this->_sendResponse($search_result, $this->_format);

        } catch (Exception $e) {
            return $this->_sendResponse(null, $this->_format);
        }
    }

    /**
     * @param SolrService $modelSearch
     * @param array       $param
     *
     * @return array
     */
    private function doSearch(SolrService $modelSearch, array $param)
    {
        $result = $modelSearch->search($param);
        $products = $result['hits'];
        $ps = array();
        foreach ($products as $p) {
            $img = Util::image(
                $p->image_small, array(
                                   'width'  => 50,
                                   'height' => 50,
                               )
            );
            $ps[] = array(
                'type'          => 'project',
                'title'         => $p->title,
                'project_id'    => $p->project_id,
                'member_id'     => $p->member_id,
                'username'      => $p->username,
                'laplace_score' => $p->laplace_score,
                'score'         => $p->score,
                'cat_title'     => $p->cat_title,
                'image_small'   => $img,
            );
        }

        return $ps;
    }

    public function searchpAction()
    {
        $this->_initResponseHeader();
        $projectSearchText = $this->params()->fromRoute("p");

        //$projectSearchCategory = $this->params()->fromRoute("c");
        $store = null;
        /*
        if($this->hasParam('s')){
            $store = $this->getParam('s');
        }
        */ //$store = $this->params()->fromRoute("s");
        //$filterCat = 'project_category_id:('.$projectSearchCategory.')';
        //$param = array('q' => $projectSearchText,'store'=>$store,'page' => 1
        //  , 'count' => 10,'fq' => array($filterCat));
        $param = array('q' => $projectSearchText, 'page' => 1, 'count' => 10);

        // if($store)
        // {
        //     $param['store'] = $store;
        // }

        try {
            $ps = $this->doSearch($this->solrService, $param);

            return $this->_sendResponse($ps, $this->_format);
        } catch (Exception $e) {

            return $this->_sendResponse(null, $this->_format);
        }
    }

    public function anonymousdlAction()
    {
        $this->_initResponseHeader();
        $identity = $this->ocsUser;

        $config = $this->ocsConfig;
        $cookieName = $config->settings->session->auth->anonymous;
        $storedInCookie = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : null;
        if ($storedInCookie) {
            $model = $this->memberDownloadHistoryRepository;
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
                'status'  => 'ok',
                'section' => $dlsection,
                'dls'     => $dls,
            );

            return $this->_sendResponse($response, $this->_format);

        }
        $response = array(
            'status' => 'ok',
            'dls'    => 0,
        );

        return $this->_sendResponse($response, $this->_format);
    }

    public function fetchrandomsupporterAction()
    {
        $this->_initResponseHeader();

        $section_id = $this->params()->fromQuery('s');
        $info = $this->infoService;
        $s = $info->getRandomSupporterForSection($section_id);
        $response = array(
            'status'    => 'ok',
            'supporter' => $s,
        );

        return $this->_sendResponse($response, $this->_format);
    }

}
