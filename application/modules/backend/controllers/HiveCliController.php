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
class Backend_HiveCliController extends Local_Controller_Action_CliAbstract
{
    var $_HIVE_BASE_URL;
    var $_OCS_CN_FILE_SYNC_URL;
    var $_OCS_FILE_SYNC_URL;

    //Daily updated hive categories
    //var $hive_import_categories = array(56,57,210,211,212,213,214,220,221,222,223,224,241,242,243,260,261,282,285,284,270,271,272,273,281,280,230,231,232,233,234,235,236,310,311,312,313,314,320,321,322,323,324,341,342,343,360,361,382,385,384,370,371,372,373,381,380,330,331,332,333,334,335,336,4210,4211,4212,4213,4214,4220,4221,4222,4223,4224,4241,4242,4243,4260,4261,4282,4285,4284,4270,4271,4272,4273,4281,4280,4230,4231,4232,4233,4234,4235,4236,4298,4299,4283,4290,4320,4324,10,11,12,13,17,18,22,7525,7825,8025,8125,8121,8321,34,24,26,28,30,31,32,470,180,7304,186,90,190,91,92,638,690,691,83,687,646,694,686,643,35,413,4250,4251,4252,4253,4254,250,251,252,253,254,350,351,352,353,354,120,166,62,38,55,63,40,43,86,87,683,630,631,632,9821,9822,9823,9827,9840,644,642,7526,8726,8326,7826,8126,8026,7726,131,9700,60,14,20,6110,6120,7030,7060,7050,41,287,39,76,93,70,36,79,66,45,74,77,23,27,81,102,103,21,9,16,6700,165,101,188,189,133,130,160,150,121,104,191,167,100,430,420,7400,7402,7403,7311,7314,7313,7000,685,37,2,3,4,5,7,29,71,72,73,80,170,171,172,173,174,175,176,177,178,179);
    //var $hive_import_categories = array(15,8,64,8300,7700,8000,8100,7800,8400,8700,7600,7900,7500);
    //var $hive_import_categories = arra(25);
    //var $hive_import_categories = array(648, 692, 649);
    //var $hive_import_categories = array(322,323,324,325,326);
    //var $hive_import_categories = array(637);
    var $hive_import_categories = array(635);
    protected $_allowed = array(
        'image/jpeg' => '.jpg',
        'image/jpg' => '.jpg',
        'image/png' => '.png',
        'image/gif' => '.gif',
        'application/x-empty' => '.png'
    );

    /**
     * Run php code as cronjob.
     * I.e.:
     * /usr/bin/php /var/www/ocs-www/httpdocs/cron.php -a /backend/hive-cli/run/action/sync/context/users/ >> /var/www/ocs-www/logs/hive_sync.log $
     *
     * @see Local_Controller_Action_CliInterface::runAction()
     */
    public function runAction()
    {
        $this->initVars();

        $action = (int)$this->getParam('action');
        $context = $this->getParam('context');

        /**
         * Disable Sync
         * 20160627 Ronald
         */

        if (isset($action) && $action == 'sync' && isset($context) && $context == 'all') {
            /**$this->syncUser();
             * $this->syncContent();
             * $this->syncVotes();
             * $this->syncComments();
             * $this->syncDownloads();*/
            //} else if(isset($action) && $action == 'sync' && isset($context) && $context == 'users') {
            //	$this->syncUser();
            //} else if(isset($action) && $action == 'sync' && isset($context) && $context == 'content') {
            //	$this->syncContent();
        } else {
            if (isset($action) && $action == 'sync' && isset($context) && $context == 'votes') {
                $this->syncVotes();
            } else {
                if (isset($action) && $action == 'sync' && isset($context) && $context == 'comments') {
                    $this->syncComments();
                    //} else if(isset($action) && $action == 'sync' && isset($context) && $context == 'downloads') {
                    //	$this->syncDownloads();
                }
    	
            }
        }

    }

    public function initVars()
    {
        echo '################## SERVER: ' . php_uname("n") . ' ####################';

        if (strtolower(php_uname("n")) == 'do-pling-com') {
            $this->_HIVE_BASE_URL = 'http://cp1.hive01.com';
            $this->_OCS_CN_FILE_SYNC_URL = 'https://cn.com';
            $this->_OCS_FILE_SYNC_URL = 'https://www.ppload.com';
        } else {
            $this->_HIVE_BASE_URL = 'http://cp1.hive01.com';
            $this->_OCS_CN_FILE_SYNC_URL = 'https://cn.ws';
            $this->_OCS_FILE_SYNC_URL = 'https://ws.ppload.com';
        }
    }

    private function syncVotes()
    {
        $db = Zend_Db_Table::getDefaultAdapter();

        $sql = "SELECT ";
        $sql .= "   c.action,v.id,v.type,";
        $sql .= "	m.member_id as `member_id`,";
        $sql .= "	p.project_id as `project_id`,";
        $sql .= "	case when v.vote > 0 then 1 else 0 end as `user_like`,";
        $sql .= "	case when v.vote = 0 then 1 else 0 end `user_dislike`,";
        $sql .= "	1 as source_id,";
        $sql .= "	v.id as source_pk from H01.changes c";
        $sql .= " JOIN H01.votes v ON c.id = v.id";
        $sql .= " join member m on m.source_id = 1 and m.username = v.user and v.userdb = 0";
        $sql .= " join project p on p.source_id = 1 and p.source_type = 'project' and p.source_pk = v.content";
        $sql .= " WHERE c.table = 'votes'";
        $sql .= " ORDER BY action,v.timestamp";

        $result = '';

        $stmt = $db->query($sql);
        $votes = $stmt->fetchAll();
        echo '###################### Start VotesSync: ' . count($votes) . ' votes ';

        //Insert/Update users in table project_rating
        foreach ($votes as $vote) {
            if ($vote['type'] == 2) {
                if ($vote['action'] <> 'D') {
                    $vote_id = $this->insertUpdateVote($vote);
                } else {
                    if ($vote['action'] == 'D') {
                        $this->deleteVote($vote);
                    }
                }
                $sql = "DELETE FROM H01.changes WHERE `table` = 'votes' AND `action` = '" . $vote['action'] . "' AND `id` = " . $vote['id'];
                $stmt = $db->query($sql);
                $stmt->execute();
            }
        }
    }

    private function insertUpdateVote($vote)
    {
        $votingTable = new Default_Model_DbTable_ProjectRating();
        $votearray = $vote;
        unset($votearray['action']);
        unset($votearray['id']);
        unset($votearray['type']);

        $votingTable->delete('member_id = ' . $votearray['member_id'] . ' AND project_id = ' . $votearray['project_id']);

        $newVote = $votingTable->save($votearray);

        $projectTable = new Default_Model_Project();
        $project = $projectTable->find($votearray['project_id'])->current();

        if ($project) {
            $project = $project->toArray();
            $ratingSum = $votingTable->fetchRating($project['project_id']);
            $project['count_likes'] = $ratingSum['count_likes'];
            $project['count_dislikes'] = $ratingSum['count_dislikes'];

            $projectTable->save($project);
        }

        if ($newVote) {
            return $newVote;
        } else {
            return false;
        }

    }

    private function syncComments()
    {
        $db = Zend_Db_Table::getDefaultAdapter();


        //Parent-Comments
        $sql = "select * from (";
        $sql .= " SELECT ch.`action`,c.parent,NULL AS comment_id,0 AS comment_type,0 AS comment_parent_id,p.project_id AS comment_target_id,m.member_id AS comment_member_id,convert(cast(convert(c.text using latin1) as binary) using utf8) AS comment_text, FROM_UNIXTIME(c.date) AS comment_created_at,1 AS source_id,c.id AS source_pk";
        $sql .= " FROM H01.changes ch";
        $sql .= " JOIN H01.comments c ON c.id = ch.id";
        $sql .= " JOIN member m ON m.source_id = 1 AND m.username = CONVERT(c.user USING utf8) AND c.userdb = 0";
        $sql .= " JOIN project p ON p.source_id = 1 AND p.source_type = 'project' AND p.source_pk = c.content";
        $sql .= " LEFT JOIN comments c3 ON c3.source_id = 1 AND c3.source_pk = c.id";
        $sql .= " WHERE ch.table = 'comments'";
        $sql .= " ) A";
        $sql .= " where parent = 0";
        $sql .= " ORDER BY action,comment_created_at";

        $result = '';

        $stmt = $db->query($sql);
        $comments = $stmt->fetchAll();
        echo '###################### Start ComentSync: ' . count($comments) . ' comments ';

        //Insert/Update users in table comments
        foreach ($comments as $comment) {
            if ($comment['action'] <> 'D') {
                $comment_id = $this->insertUpdateComment($comment);
            }
            $sql = "DELETE FROM H01.changes WHERE `table` = 'comments' AND `action` = '" . $comment['action'] . "' AND `id` = " . $comment['source_pk'];
            $stmt = $db->query($sql);
            $stmt->execute();
        }


        //Child-Comments
        $sql = "select * from (";
        $sql .= " SELECT ch.action, c.parent, c3.comment_id,0 AS type,c2.comment_id AS comment_parent_id,p.project_id AS comment_target_id,m.member_id AS comment_member_id,convert(cast(convert(c.text using latin1) as binary) using utf8) AS comment_text, FROM_UNIXTIME(c.date) AS comment_created_at,1 AS source_id,c.id AS source_pk";
        $sql .= " FROM H01.changes ch";
        $sql .= " JOIN H01.comments c ON c.id = ch.id";
        $sql .= " JOIN member m ON m.source_id = 1 AND m.username = CONVERT(c.user USING utf8) AND c.userdb = 0";
        $sql .= " JOIN project p ON p.source_id = 1 AND p.source_type = 'project' AND p.source_pk = c.content";
        $sql .= " JOIN comments c2 ON c2.source_id = 1 AND c2.source_pk = c.parent";
        $sql .= " LEFT JOIN comments c3 ON c3.source_id = 1 AND c3.source_pk = c.id";
        $sql .= " WHERE ch.table = 'comments'";
        $sql .= " ) A";
        $sql .= " WHERE parent > 0 ";
        $sql .= " ORDER BY action,comment_created_at";

        $result = '';

        $stmt = $db->query($sql);
        $comments = $stmt->fetchAll();
        $countChildComments = count($comments);
        echo 'Start ChildCommentsSync: ' . count($comments) . ' comments ';

        //Insert/Update users in table comments
        foreach ($comments as $comment) {
            if ($comment['action'] <> 'D') {
                $comment_id = $this->insertUpdateComment($comment);
            }
            $sql = "DELETE FROM H01.changes WHERE `table` = 'comments' AND `action` = '" . $comment['action'] . "' AND `id` = " . $comment['source_pk'];
            $stmt = $db->query($sql);
            $stmt->execute();
        }


        //Deleted-Comments
        $sql = "select c.* from H01.changes c where c.`table` = 'comments' and c.`action` = 'D'";
        $stmt = $db->query($sql);
        $comments = $stmt->fetchAll();
        echo '###################### Start ComentSync Deleted Comments: ' . count($comments) . ' comments ';
        //Insert/Update users in table comments
        foreach ($comments as $comment) {
            $comment_id = $this->deleteComment($comment);
            $sql = "DELETE FROM H01.changes WHERE `table` = 'comments' AND `action` = '" . $comment['action'] . "' AND `id` = " . $comment['id'];
            $stmt = $db->query($sql);
            $stmt->execute();
        }

        if ($countChildComments > 1) {
            $this->syncComments();
        }


    }

    private function insertUpdateComment($comment)
    {
        $commentTable = new Default_Model_ProjectComments();
        $commentarray = $comment;
        unset($commentarray['action']);
        unset($commentarray['parent']);

        $orgComment = $commentTable->getCommentFromSource(0, 1, $comment['source_pk']);
        if ($orgComment) {
            $commentarray['comment_id'] = $orgComment['comment_id'];
            $newComment = $commentTable->save($commentarray);
        } else {
            $newComment = $commentTable->save($commentarray);
        }

        if ($newComment) {
            return $newComment;
        } else {
            return false;
        }

    }

    private function deleteComment($comment)
    {

        $ocsCommentTable = new Default_Model_DbTable_Comments();
        $ocsComment = $ocsCommentTable->fetchRow('source_id = 1 and source_pk = ' . $comment['id']);

        if ($ocsComment) {
            $ocsComment = $ocsComment->toArray();
            $ocsComment['comment_active'] = 0;
            $result = $ocsCommentTable->save($ocsComment);
            return $result;
        }

        return false;
    }

    public function initAction()
    {
        $action = (int)$this->getParam('action');
        $context = $this->getParam('context');

        $config = Zend_Registry::get('config');
        $this->_HIVE_BASE_URL = $config->admin->email;

        $this->createCronJob($action, $context);

    }

    /**
     * @param string $action
     * @param string $context
     * @throws Zend_Exception
     */
    protected function createCronJob($action, $context)
    {
        try {
            $manager = new Crontab_Manager_CrontabManager();
            //           $manager->user = 'www-data';
            $newJob = $manager->newJob('*/60 * * * * php /var/www/ocs-www/httpdocs/cron.php -a /backend/hive-cli/run/action/' . $action . '/context/' . $context . '/ >> /var/www/ocs-www/logs/hive_sync_' . $context . '.log 2>&1',
                'www-data');
            if (false == $manager->jobExists($newJob)) {
                $manager->add($newJob);
                $manager->save();
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            exit();
        }
    }

    private function syncUser()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT c.action,u.* from H01.changes c";
        $sql .= " JOIN H01.users u ON c.id = u.id";
        $sql .= " WHERE c.table = 'users'";
        $sql .= " ORDER BY action,createtime";

        $result = '';

        $stmt = $db->query($sql);
        $users = $stmt->fetchAll();
        echo '###################### Start UserSync: ' . count($users) . ' users ';

        //Refresh files on cn
        $result = file_get_contents($this->_OCS_CN_FILE_SYNC_URL . '/sync.php');
        echo $result;

        //Insert/Update users in table hive_users
        foreach ($users as $user) {
            if ($user['action'] <> 'D') {
                $newMember = $this->insertUpdateUserIntoImportTable($user);
            } else {
                if ($user['action'] == 'D') {
                    $newMember = $this->deleteUserFromImportTable($user);
                }
            }
            if ($newMember) {
                $sql = "DELETE FROM H01.changes WHERE `table` = 'users' AND `action` = '" . $user['action'] . "' AND `id` = " . $user['id'];
                $stmt = $db->query($sql);
                $stmt->execute();
            }
        }

        //Import users into member
        $this->importOcsMembers();

    }

    private function insertUpdateUserIntoImportTable($user)
    {
        $userTable = new Default_Model_DbTable_HiveUser();
        $updatearray = $user;
        $updatearray['is_imported'] = 0;
        $updatearray['import_error'] = '';
        unset($updatearray['action']);

        $member = $userTable->fetchRow('id = ' . $user['id']);
        if ($member) {
            $result = $userTable->update($updatearray, 'id = ' . $user['id']);
        } else {
            //save new user
            $result = $userTable->insert($updatearray);

        }
        return $result;
    }

    private function deleteUserFromImportTable($user)
    {
        return false;
    }

    private function importOcsMembers()
    {
        $hiveUserTable = new Default_Model_DbTable_HiveUser();
        $memberTable = new Default_Model_Member();

        $count = $hiveUserTable->fetchCountUsers();
        $import_counter = 0;

        $users = $hiveUserTable->fetchAllUsers(0, 100);

        $info = '';

        foreach ($users as $user) {
            $info .= " ## User: id = " . $user['id'] . ", name = " . $user['login'] . "  ";
            $start = microtime(true);
            $import_counter++;
            $member = $memberTable->fetchRow('source_id = 1 AND source_pk = ' . $user['id']);

            if ($member) {
                $this->updateMember($user);
            } else {
                $this->insertMember($user);
            }
            $time_elapsed_secs = microtime(true) - $start;
            $info .= $time_elapsed_secs . " secs";
            $info .= " - Done... ";

            //Mark user as imported
            $hiveUserTable->update(array("is_imported" => 1), "id = " . $user['id']);

        }
        echo $info;
    }

    private function updateMember($user)
    {
        $memberTable = new Default_Model_Member();
        $updatearray = $this->makeMemberFromHiveUser($user);
        unset($updatearray['roleId']);

        $member = $memberTable->fetchMemberFromHiveUserName($user['login']);
        $member_id = null;
        if ($member) {
            $member_id = $member['member_id'];
            $updatearray['member_id'] = $member_id;
            $memberTable->update($updatearray, 'member_id = ' . $member_id);
        } else {
            $member_id = $this->insertMember($user);
        }
        return $member_id;
    }

    private function makeMemberFromHiveUser($user)
    {
        $member = array();
        $member['source_id'] = 1;
        $member['source_pk'] = $user['id'];
        $member['username'] = $user['login'];
        $member['mail'] = $user['email'];
        $member['password'] = $user['passwd'];
        $member['roleId'] = 300;
        $member['type'] = 0;
        $member['is_active'] = 1;
        $member['is_deleted'] = 0;
        $member['mail_checked'] = 1;
        $member['agb'] = 1;
        $member['newsletter'] = $user['newsletter'];
        $member['login_method'] = 'local';
        $member['firstname'] = $user['firstname'];
        $member['lastname'] = $user['name'];
        $member['street'] = $user['street'];
        $member['zip'] = $user['zip'];
        $member['city'] = $user['city'];
        $member['country'] = $user['country_text'];
        $member['phone'] = $user['phonenumber'];
        $member['last_online'] = $user['last_online'];
        $member['biography'] = $user['description'];
        $member['paypal_mail'] = $user['paypalaccount'];
        //user pic
        $pic = $this->getHiveUserPicture($user['login'], $user['userdb']);
        if (empty($pic)) {
            $pic = 'hive/user-pics/nopic.png';
        }
        $member['profile_image_url'] = $pic;
        $member['profile_img_src'] = 'local';

        $member['validated'] = 0;
        $member['created_at'] = $user['created_at'];
        $member['changed_at'] = null;

        for ($i = 1; $i <= 10; $i++) {
            if (isset($user['homepage' . $i])) {
                $link = $user['homepage' . $i];
                $link_type = $user['homepagetype' . $i];
                /**
                 * 0 = null
                 * 10 = Blog
                 * 20 = delicious
                 * 30 = Digg
                 * 40 = Facebook
                 * 50 = Homepage
                 * 60 = LinkedIn
                 * 70 = MySpace
                 * 80 = other
                 * 90 = Reddit
                 * 100 = YouTube
                 * 110 = Twitter
                 * 120 = Wikipedia
                 * 130 = Xing
                 * 140 = identi.ca
                 * 150 = libre.fm
                 * 160 = StackOverflow **/
                if ($i == 1) {
                    $member['link_website'] = $user['homepage1'];
                }
                if ($link_type == 50) {
                    $member['link_website'] = $link;
                } else {
                    if ($link_type == 40) {
                        $member['link_facebook'] = $link;
                    } else {
                        if ($link_type == 110) {
                            $member['link_twitter'] = $link;
                        } else {
                            if (!strpos($link, 'google.com/') === false) {
                                $member['link_google'] = $link;
                            } else {
                                if (!strpos($link, 'github.com/') === false) {
                                    $member['link_github'] = $link;
                                }
                            }
                        }
                    }
                }
            }
        }


        return $member;
    }

    private function getHiveUserPicture($username, $userdb)
    {
        $imageModel = new Default_Model_DbTable_Image();
        $path = 'https://cn.com/img/hive/user-bigpics/' . $userdb . '/' . $username . '.';
        $fileFolder = 'user-bigpics';
        $fileUrl = null;
        $fileExtention = null;
        if ($this->check_img($path . 'png')) {
            $fileUrl = ($path . 'png');
            $fileExtention = 'png';
        } else {
            if ($this->check_img($path . 'gif')) {
                $fileUrl = ($path . 'gif');
                $fileExtention = 'gif';
            } else {
                if ($this->check_img($path . 'jpg')) {
                    $fileUrl = ($path . 'jpg');
                    $fileExtention = 'jpg';
                } else {
                    if ($this->check_img($path . 'jpeg')) {
                        $fileUrl = ($path . 'jpeg');
                        $fileExtention = 'jpeg';
                    }
                }
            }
        }

        if ($fileUrl == null) {
            $path = 'https://cn.com/img/hive/user-pics/' . $userdb . '/' . $username . '.';
            $fileFolder = 'user-pics';
            if ($this->check_img($path . 'png')) {
                $fileUrl = ($path . 'png');
                $fileExtention = 'png';
            } else {
                if ($this->check_img($path . 'gif')) {
                    $fileUrl = ($path . 'gif');
                    $fileExtention = 'gif';
                } else {
                    if ($this->check_img($path . 'jpg')) {
                        $fileUrl = ($path . 'jpg');
                        $fileExtention = 'jpg';
                    } else {
                        if ($this->check_img($path . 'jpeg')) {
                            $fileUrl = ($path . 'jpeg');
                            $fileExtention = 'jpeg';
                        }
                    }
                }
            }
        }

        $cnFileUrl = null;
        if ($fileUrl != null) {
            $cnFileUrl = 'hive/' . $fileFolder . '/' . $userdb . '/' . $username . '.' . $fileExtention;
        }

        //var_dump($info);
        return $cnFileUrl;
    }

    private function check_img($file)
    {
        $response = false;
        $x = getimagesize($file);

        switch ($x['mime']) {
            case "image/gif":
                $response = true;
                break;
            case "image/jpeg":
                $response = true;
                break;
            case "image/png":
                $response = true;
                break;
            default:
                $response = false;
                break;
        }

        return $response;
    }

    private function insertMember($user)
    {
        $memberTable = new Default_Model_Member();
        $projectTable = new Default_Model_Project();
        $updatearray = $this->makeMemberFromHiveUser($user);
        //save member
        $member_id = $memberTable->insert($updatearray);
        $member = $memberTable->fetchMemberData($member_id);
        //save .me project
        $project = $this->makeProjectFromOcsUser($member);
        $project_id = $projectTable->insert($project);

        $updatearray = array();
        $updatearray['main_project_id'] = $project_id;
        $memberTable->update($updatearray, 'member_id = ' . $member_id);

        return $member_id;
    }

    private function makeProjectFromOcsUser($user)
    {
        $project = array();
        $project['source_id'] = 1;
        $project['source_pk'] = $user['member_id'];
        $project['source_type'] = 'user';
        $project['member_id'] = $user['member_id'];
        $project['content_type'] = 'text';
        $project['project_category_id'] = 0;
        $project['is_active'] = 1;
        $project['is_deleted'] = 0;
        $project['status'] = 100;
        $project['type_id'] = 0;
        $project['description'] = $user['biography'];
        $project['created_at'] = $user['created_at'];
        $project['changed_at'] = new Zend_Db_Expr('Now()');

        return $project;
    }

    private function syncDownloads()
    {
        $db = Zend_Db_Table::getDefaultAdapter();

        $sql = "SELECT ";
        $sql .= "   v.id,p.project_id as `project_id`,";
        $sql .= "	v.downloads as count_downloads_hive";
        $sql .= " FROM H01.changes c";
        $sql .= " JOIN H01.content v ON c.id = v.id";
        $sql .= " join project p on p.source_id = 1 and p.source_type = 'project' and p.source_pk = c.id";
        $sql .= " WHERE c.table = 'downloads'";
        $sql .= " ORDER BY action,c.timestamp";

        $result = '';

        $stmt = $db->query($sql);
        $downloads = $stmt->fetchAll();
        echo '###################### Start DownloadsSync: ' . count($downloads) . ' downloads ';

        //Insert/Update users in table project_rating
        foreach ($downloads as $download) {
            $vote_id = $this->updateDownload($download);
            $sql = "DELETE FROM H01.changes WHERE `table` = 'downloads' AND `action` = 'U' AND `id` = " . $download['id'];
            $stmt = $db->query($sql);
            $stmt->execute();
        }
    }

    private function updateDownload($download)
    {
        $projectTable = new Default_Model_Project();
        $project = $projectTable->find($download['project_id'])->current();

        if ($project) {
            $project = $project->toArray();
            $project['count_downloads_hive'] = $download['count_downloads_hive'];
            $project = $projectTable->save($project);
        }

        if ($project) {
            return $project;
        } else {
            return false;
        }

    }

    private function deleteMember($user)
    {
        $memberTable = new Default_Model_Member();
        $member = $memberTable->fetchMemberFromHiveUserName($user['login']);
        $member_id = null;
        if ($member) {
            $member_id = $member['member_id'];
            $memberTable->setDeleted($member_id);
        }
        return true;
    }

    private function syncContent()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT c.action,u.* from H01.changes c";
        $sql .= " JOIN H01.content u ON c.id = u.id";
        $sql .= " WHERE c.table = 'content'";
        $sql .= " ORDER BY action,created";

        $stmt = $db->query($sql);
        $contents = $stmt->fetchAll();

        echo '###################### Start ContentSync: ' . count($contents) . ' contents ';

        //Refresh files on ppload.com and cn.com
        $result = file_get_contents($this->_OCS_CN_FILE_SYNC_URL . '/sync.php');
        echo $result;
        $result = file_get_contents($this->_OCS_FILE_SYNC_URL . '/sync.php');
        echo $result;

        foreach ($contents as $content) {
            //Insert/Update users in table hive_users
            if ($content['action'] <> 'D') {
                $newProject = $this->insertUpdateContentIntoImportTable($content);
            } else {
                if ($content['action'] == 'D') {
                    $newProject = $this->deleteContentFromImportTable($content);
                }
            }
            if ($newProject) {
                $sql = "DELETE FROM H01.changes WHERE `table` = 'content' AND `action` = '" . $content['action'] . "' AND `id` = " . $content['id'];
                $stmt = $db->query($sql);
                $stmt->execute();
            }
        }

        //Import content into project
        foreach ($this->hive_import_categories as $cat_id) {
            echo 'Start import cat: ' . $cat_id;
            $this->importOcsProjects($cat_id);
        }
    }

    private function insertUpdateContentIntoImportTable($content)
    {
        $contentTable = new Default_Model_DbTable_HiveContent();
        $updatearray = $content;
        $updatearray['is_imported'] = 0;
        $updatearray['import_error'] = '';
        unset($updatearray['action']);

        $project = $contentTable->fetchRow('id = ' . $content['id']);
        if ($project) {
            $newProject = $contentTable->update($updatearray, 'id = ' . $content['id']);
        } else {
            $newProject = $contentTable->insert($updatearray);
        }
        return $newProject;
    }

    private function importOcsProjects($cat_id, $importGalleryPics = true, $importFiles = true)
    {

        $_import_counter = 0;
        $_import_file_counter = 0;
        $_is_import_done = false;
        $info = '';

        $hiveCatTable = new Default_Model_DbTable_HiveContentCategory();
        $ocs_cat_id = $hiveCatTable->fetchOcsCategoryForHiveCategory($cat_id);

        if (!isset($ocs_cat_id)) {
            echo " - No ocs-Category found!";
            exit;
        }

        $startIndex = null;
        $limit = 100;
        $startIndex = 0;

        $result = array();
        $contentTable = new Default_Model_DbTable_HiveContent();
        $memberTable = new Default_Model_Member();
        $projectTable = new Default_Model_Project();
        try {
            $projects = $contentTable->fetchAllProjectsForCategory($cat_id, $startIndex, $limit, true);

            foreach ($projects as $project) {
                $_import_counter++;

                $info .= " ## Poject: id = " . $project['id'] . ", name = " . $project['name'] . "  ";
                $start = microtime(true);

                //1. Download/Upload Project-Picture
                $cnFilePath = $this->getProjectPicture($project['id']);

                //2. Create ocs Project
                $projectId = null;
                try {
                    $projectId = $this->createUpdateOcsProjects($project, $ocs_cat_id, $cnFilePath);
                } catch (Exception $e) {
                    //Error: log error and go on
                    $error = array();
                    $error['import_error'] = $e;
                    $error['is_imported'] = 1;
                    $contentTable->update($error, 'id = ' . $project['id']);
                }

                if ($projectId) {


                    //3. Upload files
                    if ($importFiles) {
                        $pploadError = null;
                        $info .= " - Files ";
                        $start = microtime(true);
                        try {
                            $_import_file_counter = $this->uploadFilesAndLinks($project, $projectId);
                        } catch (Exception $e) {
                            $pploadError .= $e;
                            $info .= $pploadError;
                        }
                        $info .= " - Files Uploaded: " . $_import_file_counter . " -  ";
                        $time_elapsed_secs = microtime(true) - $start;
                        $info .= $time_elapsed_secs . " secs";

                    } else {
                        $_import_file_counter = 1;
                    }

                    if (true) {

                        //4. Gallery Pics
                        if ($importGalleryPics) {

                            $info .= " - Gallery Pics ";
                            $previewPicsArray = array();
                            if (!empty($project['preview1'])) {
                                $cnFilePathPre = $this->getPreviewPicture($project['id'], 1, $project['preview1']);
                                //add preview pic to ocs-project
                                if (!empty($cnFilePathPre)) {
                                    $previewPicsArray[] = $cnFilePathPre;
                                    $info .= " - PreviewPic1 ";
                                }
                            }
                            if (!empty($project['preview2'])) {
                                $cnFilePathPre = $this->getPreviewPicture($project['id'], 2, $project['preview2']);
                                if (!empty($cnFilePathPre)) {
                                    $previewPicsArray[] = $cnFilePathPre;
                                    $info .= " - PreviewPic2 ";
                                }
                            }
                            if (!empty($project['preview3'])) {
                                $cnFilePathPre = $this->getPreviewPicture($project['id'], 3, $project['preview3']);
                                if (!empty($cnFilePathPre)) {
                                    $previewPicsArray[] = $cnFilePathPre;
                                    $info .= " - PreviewPic3 ";
                                }
                            }
                            if (!empty($previewPicsArray)) {
                                $projectTable->updateGalleryPictures($projectId, $previewPicsArray);
                            }
                        }

                        //5. Mark project as imported
                        $contentTable->update(array("is_imported" => 1), "id = " . $project['id']);
                    } else {
                        $info .= " - NO Files Uploaded";
                        $contentTable->update(array(
                            "is_imported" => 1,
                            "import_error" => "Error on fileupload to cc.ppload.com Exception: " . $pploadError
                        ), "id = " . $project['id']);
                    }
                } else {
                    $info .= " - Project NOT created! ";
                }

                $time_elapsed_secs = microtime(true) - $start;
                $info .= $time_elapsed_secs . " secs";
                $info .= " - Done... ";
            }
        } catch (Exception $e) {
            throw $e;
        }
        echo $info;
    }

    private function getProjectPicture($hiveProjectId)
    {
        $imageModel = new Default_Model_DbTable_Image();
        $path = 'https://cn.com/img/hive/content-pre1/' . $hiveProjectId . '-1.';
        $fileUrl = null;
        $fileExtention = null;
        $info = '';

        if ($this->check_img($path . 'gif')) {
            $fileUrl = ($path . 'gif');
            $fileExtention = 'gif';
        }
        if ($this->check_img($path . 'png')) {
            $fileUrl = ($path . 'png');
            $fileExtention = 'png';
        }
        if ($this->check_img($path . 'jpg')) {
            $fileUrl = ($path . 'jpg');
            $fileExtention = 'jpg';
        }
        if ($this->check_img($path . 'jpeg')) {
            $fileUrl = ($path . 'jpeg');
            $fileExtention = 'jpeg';
        }
        if ($this->check_img($path . 'mockup')) {
            $fileUrl = ($path . 'mockup');
            $fileExtention = 'mockup';
        }
        if ($this->check_img($path . 'GIF')) {
            $fileUrl = ($path . 'GIF');
            $fileExtention = 'GIF';
        }
        if ($this->check_img($path . 'PNG')) {
            $fileUrl = ($path . 'PNG');
            $fileExtention = 'PNG';
        }
        if ($this->check_img($path . 'JPG')) {
            $fileUrl = ($path . 'JPG');
            $fileExtention = 'JPG';
        }
        if ($this->check_img($path . 'JPEG')) {
            $fileUrl = ($path . 'JPEG');
            $fileExtention = 'JPEG';
        }
        if ($this->check_img($path . 'MOCKUP')) {
            $fileUrl = ($path . 'MOCKUP');
            $fileExtention = 'MOCKUP';
        }
        $cnFileUrl = null;
        if ($fileUrl != null) {
            $config = Zend_Registry::get('config');
            $cnFileUrl = '/hive/content-pre1/' . $hiveProjectId . '-1.' . $fileExtention;

            /**
             * //Workaround for gifs: don't use cache on cdn
             * $pos = strrpos($cnFileUrl, ".gif");
             * if ($pos>0) { // Beachten sie die drei Gleichheitszeichen
             * //gefunden ...
             * $cnFileUrl = str_replace('/cache/120x96-2', '', $cnFileUrl);
             * }
             **/
            $info .= "ImageUpload successfull: " . $cnFileUrl;
        } else {
            $path = 'https://cn.com/img/hive/content-pre2/' . $hiveProjectId . '-2.';
            $fileUrl = null;
            $fileExtention = null;
            $info = '';

            if ($this->check_img($path . 'gif')) {
                $fileUrl = ($path . 'gif');
                $fileExtention = 'gif';
            }
            if ($this->check_img($path . 'png')) {
                $fileUrl = ($path . 'png');
                $fileExtention = 'png';
            }
            if ($this->check_img($path . 'jpg')) {
                $fileUrl = ($path . 'jpg');
                $fileExtention = 'jpg';
            }
            if ($this->check_img($path . 'jpeg')) {
                $fileUrl = ($path . 'jpeg');
                $fileExtention = 'jpeg';
            }
            if ($this->check_img($path . 'mockup')) {
                $fileUrl = ($path . 'mockup');
                $fileExtention = 'mockup';
            }
            if ($this->check_img($path . 'GIF')) {
                $fileUrl = ($path . 'GIF');
                $fileExtention = 'GIF';
            }
            if ($this->check_img($path . 'PNG')) {
                $fileUrl = ($path . 'PNG');
                $fileExtention = 'PNG';
            }
            if ($this->check_img($path . 'JPG')) {
                $fileUrl = ($path . 'JPG');
                $fileExtention = 'JPG';
            }
            if ($this->check_img($path . 'JPEG')) {
                $fileUrl = ($path . 'JPEG');
                $fileExtention = 'JPEG';
            }
            if ($this->check_img($path . 'MOCKUP')) {
                $fileUrl = ($path . 'MOCKUP');
                $fileExtention = 'MOCKUP';
            }
            $cnFileUrl = null;
            if ($fileUrl != null) {
                $config = Zend_Registry::get('config');
                $cnFileUrl = '/hive/content-pre2/' . $hiveProjectId . '-2.' . $fileExtention;

                /**
                 * //Workaround for gifs: don't use cache on cdn
                 * $pos = strrpos($cnFileUrl, ".gif");
                 * if ($pos>0) { // Beachten sie die drei Gleichheitszeichen
                 * //gefunden ...
                 * $cnFileUrl = str_replace('/cache/120x96-2', '', $cnFileUrl);
                 * }
                 **/
                $info .= "ImageUpload successfull: " . $cnFileUrl;
            } else {
                $path = 'https://cn.com/img/hive/content-pre3/' . $hiveProjectId . '-3.';
                $fileUrl = null;
                $fileExtention = null;
                $info = '';

                if ($this->check_img($path . 'gif')) {
                    $fileUrl = ($path . 'gif');
                    $fileExtention = 'gif';
                }
                if ($this->check_img($path . 'png')) {
                    $fileUrl = ($path . 'png');
                    $fileExtention = 'png';
                }
                if ($this->check_img($path . 'jpg')) {
                    $fileUrl = ($path . 'jpg');
                    $fileExtention = 'jpg';
                }
                if ($this->check_img($path . 'jpeg')) {
                    $fileUrl = ($path . 'jpeg');
                    $fileExtention = 'jpeg';
                }
                if ($this->check_img($path . 'mockup')) {
                    $fileUrl = ($path . 'mockup');
                    $fileExtention = 'mockup';
                }
                if ($this->check_img($path . 'GIF')) {
                    $fileUrl = ($path . 'GIF');
                    $fileExtention = 'GIF';
                }
                if ($this->check_img($path . 'PNG')) {
                    $fileUrl = ($path . 'PNG');
                    $fileExtention = 'PNG';
                }
                if ($this->check_img($path . 'JPG')) {
                    $fileUrl = ($path . 'JPG');
                    $fileExtention = 'JPG';
                }
                if ($this->check_img($path . 'JPEG')) {
                    $fileUrl = ($path . 'JPEG');
                    $fileExtention = 'JPEG';
                }
                if ($this->check_img($path . 'MOCKUP')) {
                    $fileUrl = ($path . 'MOCKUP');
                    $fileExtention = 'MOCKUP';
                }
                $cnFileUrl = null;
                if ($fileUrl != null) {
                    $config = Zend_Registry::get('config');
                    $cnFileUrl = '/hive/content-pre3/' . $hiveProjectId . '-3.' . $fileExtention;

                    /**
                     * //Workaround for gifs: don't use cache on cdn
                     * $pos = strrpos($cnFileUrl, ".gif");
                     * if ($pos>0) { // Beachten sie die drei Gleichheitszeichen
                     * //gefunden ...
                     * $cnFileUrl = str_replace('/cache/120x96-2', '', $cnFileUrl);
                     * }
                     **/
                    $info .= "ImageUpload successfull: " . $cnFileUrl;
                } else {
                    $info .= "No preview pic";
                }
            }
        }

        var_dump($info);
        return $cnFileUrl;
    }

    private function createUpdateOcsProjects($project, $ocs_cat_id, $cnFilePath)
    {
        $projectTable = new Default_Model_Project();
        $memberTable = new Default_Model_Member();
        $info = '';
        $projectId = null;
        $uuid = null;
        $count_likes = null;
        $count_dislikes = null;
        try {
            $projectsResult = $projectTable->fetchAll("source_type = 'project' AND source_id = 1 AND source_pk = " . $project['id']);
            if (count($projectsResult) > 0) {
                $info .= "Project load successfull: " . $projectsResult[0]['project_id'];
                $projectId = $projectsResult[0]['project_id'];
                $uuid = $projectsResult[0]['uuid'];

                //delete old hive votings
                $votingTable = new Default_Model_DbTable_ProjectRating();
                $votingTable->delete('member_id = 0 AND project_id = ' . $projectId);
                //insert the old hive votings
                $votearray = array();
                $votearray['member_id'] = 0;
                $votearray['project_id'] = $projectId;
                $votearray['user_like'] = $project['scoregood'];
                $votearray['user_dislike'] = $project['scorebad'];
                $newVote = $votingTable->save($votearray);

                $ratingSum = $votingTable->fetchRating($projectId);
                $count_likes = $ratingSum['count_likes'];
                $count_dislikes = $ratingSum['count_dislikes'];

            } else {
                $votingTable = new Default_Model_DbTable_ProjectRating();
                //New project
                $ratingSum = $votingTable->fetchRating($projectId);
                $count_likes = $ratingSum['count_likes'];
                $count_dislikes = $ratingSum['count_dislikes'];
            }

        } catch (Exception $e) {
            $info .= (__FUNCTION__ . '::ERROR load Project: ' . $e);
        }

        $memberId = null;
        try {
            $member = $memberTable->fetchMemberFromHiveUserName($project['user']);
            if ($member) {
                $info .= "Member load successfull: " . $member['member_id'];
                $memberId = $member['member_id'];
            } else {
                throw new Exception(__FUNCTION__ . '::ERROR load member: Member not found: Username = ' . $project['user']);
            }

        } catch (Exception $e) {
            throw new Exception(__FUNCTION__ . '::ERROR load member: ' . $e);
        }


        $projectObj = array();
        $projectObj['member_id'] = $memberId;
        $projectObj['content_type'] = 'text';
        $projectObj['project_category_id'] = $ocs_cat_id;
        $projectObj['hive_category_id'] = $project['type'];

        //Project not deleted?
        if ($project['deletedat'] == 0 && $project['status'] == 1) {
            $projectObj['is_deleted'] = 0;
            $projectObj['deleted_at'] = $project['deleted_at'];
            $projectObj['is_active'] = 1;
            $projectObj['status'] = 100;
        } else {
            $projectObj['is_deleted'] = 1;
            $projectObj['deleted_at'] = $project['deleted_at'];
            $projectObj['is_active'] = 0;
            $projectObj['status'] = 30;
        }


        $projectObj['pid'] = null;
        $projectObj['type_id'] = 1;
        $projectObj['title'] = $project['name'];
        //$projectObj['description'] = $project['description'];
        $projectObj['description'] = $project['description_utf8'];
        $projectObj['version'] = $project['version'];
        $projectObj['image_big'] = $cnFilePath;
        $projectObj['image_small'] = $cnFilePath;
        $projectObj['start_date'] = null;
        $projectObj['content_url'] = null;
        $projectObj['created_at'] = $project['created_at'];
        $projectObj['changed_at'] = $project['changed_at'];
        $projectObj['creator_id'] = $memberId;
        $projectObj['count_likes'] = $count_likes;
        $projectObj['count_dislikes'] = $count_dislikes;
        $projectObj['count_downloads_hive'] = $project['downloads'];

        //     	$projectObj['facebook_code'] = null;
        //     	$projectObj['twitter_code'] = null;
        //     	$projectObj['google_code'] = null;
        //     	$projectObj['link_1'] = null;
        //     	$projectObj['embed_code'] = null;
        //     	$projectObj['ppload_collection_id'] = null;
        //     	$projectObj['validated'] = null;
        //     	$projectObj['validated_at'] = null;
        //     	$projectObj['featured'] = null;
        //     	$projectObj['amount'] = null;
        //     	$projectObj['amount_period'] = null;
        //     	$projectObj['claimable'] = null;
        //     	$projectObj['claimed_by_member'] = null;
        $projectObj['source_id'] = 1;
        $projectObj['source_pk'] = $project['id'];
        $projectObj['source_type'] = 'project';

        if (!isset($uuid)) {
            $uuid = Local_Tools_UUID::generateUUID();
            $projectObj['uuid'] = $uuid;
        }


        if ($projectId) {
            try {
                //update project
                $updateCount = $projectTable->update($projectObj, "project_id = " . $projectId);
                $info .= "Update Project successfull: Updated rows: " . $updateCount;


                //update changelog?
                if (isset($project['changelog']) && $project['changelog'] != '') {
                    $projectUpdatesTable = new Default_Model_ProjectUpdates();
                    $projectUpdate = $projectUpdatesTable->fetchRow('project_id = ' . $projectId . ' AND source_id = 1 AND source_pk = ' . $project['id']);
                    if ($projectUpdate) {
                        $projectUpdate = $projectUpdate->toArray();
                        if ($projectUpdate['text'] != $project['changelog']) {
                            $projectUpdate['text'] = $project['changelog'];
                            $projectUpdate['changed_at'] = $projectObj['changed_at'];
                            $projectUpdatesTable->save($projectUpdate);
                        }
                    } else {
                        $data = array();
                        $data['project_id'] = $projectId;
                        $data['member_id'] = $projectObj['member_id'];
                        $data['public'] = 1;
                        $data['text'] = $project['changelog'];
                        $data['created_at'] = $projectObj['created_at'];
                        $data['changed_at'] = $projectObj['changed_at'];
                        $data['source_id'] = 1;
                        $data['source_pk'] = $project['id'];

                        $projectUpdatesTable->save($data);
                    }
                }

            } catch (Exception $e) {
                throw new Exception(__FUNCTION__ . '::ERROR update project: ' . $e);
            }

        } else {
            try {
                //Create new project
                $newProjectObj = $projectTable->save($projectObj);
                $info .= "Create Project successfull: " . $newProjectObj['project_id'];
                $projectId = $newProjectObj['project_id'];

                //Add changelog
                if (isset($project['changelog']) && $project['changelog'] != '') {
                    $projectUpdatesTable = new Default_Model_ProjectUpdates();
                    $data = array();
                    $data['project_id'] = $projectId;
                    $data['member_id'] = $projectObj['member_id'];
                    $data['public'] = 1;
                    $data['text'] = $project['changelog'];
                    $data['created_at'] = $projectObj['created_at'];
                    $data['changed_at'] = $projectObj['changed_at'];
                    $data['source_id'] = 1;
                    $data['source_pk'] = $project['id'];

                    $projectUpdatesTable->save($data);
                }

                //insert the old hive votings
                $votearray = array();
                $votearray['member_id'] = 0;
                $votearray['project_id'] = $projectId;
                $votearray['user_like'] = $count_likes;
                $votearray['user_dislike'] = $count_dislikes;
                $newVote = $votingTable->save($votearray);


                if (null == $newProjectObj || null == $newProjectObj['project_id']) {
                    throw new Exception(__FUNCTION__ . '::ERROR save project: ' . implode(",", $newProjectObj));
                }

            } catch (Exception $e) {
                throw new Exception(__FUNCTION__ . '::ERROR save project: ' . $e);
            }

        }
        return $projectId;
    }

    private function uploadFilesAndLinks($project, $projectId)
    {
        $_import_file_counter = 0;
        //First real files
        $file1 = null;
        $info = '';

        //Clean up old collection data
        // require_once 'Ppload/Api.php';
        $pploadApi = new Ppload_Api(array(
            'apiUri' => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret' => PPLOAD_SECRET
        ));

        $projectTable = new Default_Model_DbTable_Project();
        $projectData = $projectTable->find($projectId)->current();
        $oldFiles = array();

        if ($projectData->ppload_collection_id) {
            $param = array();
            $param['collection_id'] = $projectData->ppload_collection_id;
            $oldFiles = $pploadApi->getFiles($param);

            $pploadApi->deleteCollection($projectData->ppload_collection_id);
            $projectData->ppload_collection_id = null;
            $projectTable->save($projectData->toArray());
        }

        if ($project['downloadtyp1'] == 0) {
            //a real file
            $file1 = $project['download1'];
            //$file1 = str_replace(' ', '%20', $file1);
            $pploadError = null;
            if (!empty($file1)) {
                try {
                    $downloadCounter = 0;
                    if (isset($oldFiles->files)) {
                        foreach ($oldFiles->files as $oldFile) {
                            $filename = $this->getFilenameFromUrl($this->_HIVE_BASE_URL . '/CONTENT/content-files/' . $file1);
                            var_dump('check file: ' . $oldFile->name . ' AND ' . $filename);
                            if ($oldFile->name == $filename) {
                                $downloadCounter = $oldFile->downloaded_count;
                            }
                        }
                    }

                    //$uploadFileResult = $this->uploadFileToPpload($projectId, 'http://cp1.hive01.com/CONTENT/content-files/'.$file1);
                    $uploadFileResult = $this->saveFileInPpload($projectId, $project['downloadname1'],
                        $project['licensetype'], base64_encode($project['license']), $downloadCounter,
                        $this->_HIVE_BASE_URL . '/CONTENT/content-files/' . $file1);
                    $info .= "Upload file successfull: " . $uploadFileResult;
                    if ($uploadFileResult == true) {
                        $_import_file_counter++;
                    } else {
                        throw new Exception(__FUNCTION__ . '::ERROR Upload file: ' . $uploadFileResult);
                    }
                } catch (Exception $e) {
                    throw new Exception(__FUNCTION__ . '::ERROR Upload file: ' . $e);
                }
            }
        } else {
            //a link
            try {
                $link1 = $project['downloadlink1'];
                if ($link1 != 'http://' && !empty($link1)) {
                    $link1 = urlencode($link1);
                    $linkName1 = $project['downloadname1'];
                    if (empty($linkName1)) {
                        $linkName1 = "link";
                    }
                    $downloadCounter = 0;
                    //$uploadFileResult = $this->uploadFileToPpload($projectId, 'http://hive01.cc/CONTENT/content-files/link',$link1,$linkName1);
                    $uploadFileResult = $this->saveFileInPpload($projectId, $project['downloadname1'],
                        $project['licensetype'], base64_encode($project['license']), 0,
                        $this->_HIVE_BASE_URL . '/CONTENT/content-files/link', $link1, $linkName1);
                    $info .= "Upload file successfull: " . $uploadFileResult;
                    if ($uploadFileResult == true) {
                        $_import_file_counter++;
                    }
                }
            } catch (Exception $e) {
                throw new Exception(__FUNCTION__ . '::ERROR Upload file: ' . $e);
            }
        }

        //Then links...
        for ($i = 2; $i <= 12; $i++) {
            try {
                $link1 = $project['downloadlink' . $i];
                if ($link1 != 'http://' && !empty($link1)) {
                    $link1 = urlencode($link1);
                    $linkName1 = $project['downloadname' . $i];
                    if (empty($linkName1)) {
                        $linkName1 = "link";
                    }
                    $downloadCounter = 0;
                    //$uploadFileResult = $this->uploadFileToPpload($projectId, 'http://hive01.cc/CONTENT/content-files/link',$link1,$linkName1);
                    $uploadFileResult = $this->saveFileInPpload($projectId, $project['downloadname' . $i],
                        $project['licensetype'], base64_encode($project['license']), 0,
                        $this->_HIVE_BASE_URL . '/CONTENT/content-files/link', $link1, $linkName1);
                    $info .= "Upload file successfull: " . $link1;
                    if ($uploadFileResult == true) {
                        $_import_file_counter++;
                    }
                }
            } catch (Exception $e) {
                //throw new Exception(__FUNCTION__ . '::ERROR Upload file: ' . $e);
            }
        }

        if ($_import_file_counter == 0) {
            return $info;
        }
        return $_import_file_counter;
    }

    private function getFilenameFromUrl($url)
    {
        $x = pathinfo($url);
        $fileName = $x['basename'];

        return $fileName;
    }

    private function saveFileInPpload(
        $projectId,
        $fileDescription,
        $licensetype,
        $license,
        $downloads,
        $fileUrl,
        $link = null,
        $linkName = null
    ) {
        $pploadInfo = "Start upload file " . $fileUrl . " for project " . $projectId;

        $projectTable = new Default_Model_DbTable_Project();
        $projectData = $projectTable->find($projectId)->current();

        if ($projectData) {
            $pploadInfo .= "Project found! ProjectId: " . $projectData->project_id . ", MemberId: " . $projectData->member_id;
        } else {
            $pploadInfo .= "ERROR::Project not found: " . $projectId;
            throw new Exception($pploadInfo);
            return false;
        }

        $filename = null;
        if (!empty($link)) {
            //take emtpy dummy file
            $filename = $this->getFilenameFromUrl($fileUrl);
            $tmpFilepath = "/hive/H01/CONTENT/content-files/link";
            $tmpFilename = $linkName;
        } else {
            //get file name
            $filename = $this->getFilenameFromUrl($fileUrl);
            $tmpFilepath = "/hive/H01/CONTENT/content-files/" . $filename;
            $tmpFilename = $filename;
            if (!empty($filename)) {
                $pploadInfo .= "FileName found: " . $filename;
            } else {
                $pploadInfo .= "ERROR::FileName not found: " . $filename;
                throw new Exception($pploadInfo);
                return false;
            }
        }

        // require_once 'Ppload/Api.php';
        $pploadApi = new Ppload_Api(array(
            'apiUri' => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret' => PPLOAD_SECRET
        ));


        $fileRequest = array(
            'local_file_path' => $tmpFilepath,
            'local_file_name' => $tmpFilename,
            'owner_id' => $projectData->member_id
        );
        if ($projectData->ppload_collection_id) {
            // Append to existing collection
            $fileRequest['collection_id'] = $projectData->ppload_collection_id;
        }
        if (!empty($fileDescription)) {
            $fileRequest['description'] = mb_substr($fileDescription, 0, 140);
        }
        if (!empty($downloads)) {
            $fileRequest['downloaded_count'] = $downloads;
        }
        $tags = '';
        if (!empty($link)) {
            $tags .= "link##" . $link . ',';
        }
        if (!empty($licensetype)) {
            $tags .= "licensetype-" . $licensetype . ',';
        }
        if (!empty($license)) {
            $tags .= "license##" . $license . ',';
        }
        if (!empty($tags)) {
            $fileRequest['tags'] = $tags;
        }


        //upload to ppload
        $fileResponse = $pploadApi->postFile($fileRequest);

        if (!empty($fileResponse)) {
            $pploadInfo .= "File uploaded to ppload! ";
        } else {
            $pploadInfo .= "ERROR::File NOT uploaded to ppload! Response: " . $fileResponse;
            throw new Exception($pploadInfo);
            return $pploadInfo;
        }

        //delete tmpFile
        //unlink($tmpFilename);

        //unlink($tmpFilename);

        if (!empty($fileResponse->file->collection_id)) {
            $pploadInfo .= "CollectionId: " . $fileResponse->file->collection_id;
            if (!$projectData->ppload_collection_id) {
                // Save collection ID
                $projectData->ppload_collection_id = $fileResponse->file->collection_id;
                //$projectData->changed_at = new Zend_Db_Expr('NOW()');
                $projectData->save();

                // Update profile information
                $memberTable = new Default_Model_DbTable_Member();
                $memberSettings = $memberTable->find($projectData->member_id)->current();
                $mainproject = $projectTable->find($memberSettings->main_project_id)->current();
                $profileName = '';
                if ($memberSettings->firstname
                    || $memberSettings->lastname
                ) {
                    $profileName = trim(
                        $memberSettings->firstname
                        . ' '
                        . $memberSettings->lastname
                    );
                } else {
                    if ($memberSettings->username) {
                        $profileName = $memberSettings->username;
                    }
                }
                $profileRequest = array(
                    'owner_id' => $projectData->member_id,
                    'name' => $profileName,
                    'email' => $memberSettings->mail,
                    'homepage' => $memberSettings->link_website,
                    'description' => $mainproject->description
                );
                $profileResponse = $pploadApi->postProfile($profileRequest);
                // Update collection information
                $collectionCategory = $projectData->project_category_id;
                if (Default_Model_Project::PROJECT_ACTIVE == $projectData->status) {
                    $collectionCategory .= '-published';
                }
                $collectionRequest = array(
                    'title' => $projectData->title,
                    'description' => $projectData->description,
                    'category' => $collectionCategory
                );
                $collectionResponse = $pploadApi->putCollection(
                    $projectData->ppload_collection_id,
                    $collectionRequest
                );
                // Store product image as collection thumbnail
                $this->_updatePploadMediaCollectionthumbnail($projectData);
            }
            //return $fileResponse->file;
            return true;
        } else {
            //return false;
            $pploadInfo .= "ERROR::No CollectionId in ppload-file! Response Status: " . json_encode($fileResponse);
            throw new Exception($pploadInfo);
            return $pploadInfo;
        }
        return $pploadInfo;
    }

    private function _updatePploadMediaCollectionthumbnail($projectData)
    {
        if (empty($projectData->ppload_collection_id)
            || empty($projectData->image_small)
        ) {
            return false;
        }

        // require_once 'Ppload/Api.php';
        $pploadApi = new Ppload_Api(array(
            'apiUri' => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret' => PPLOAD_SECRET
        ));

        $filename = sys_get_temp_dir() . '/' . $projectData->image_small;
        if (false === file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        /**
         * $viewHelperImage = new Default_View_Helper_Image();
         * $uri = $viewHelperImage->Image(
         * $projectData->image_small,
         * array(
         * 'width' => 600,
         * 'height' => 600
         * )
         * );**/
        $uri = $this->_OCS_CN_FILE_SYNC_URL . '/cache/600x600/img' . $projectData->image_small;

        file_put_contents($filename, file_get_contents($uri));

        $mediaCollectionthumbnailResponse = $pploadApi->postMediaCollectionthumbnail(
            $projectData->ppload_collection_id,
            array('file' => $filename)
        );

        unlink($filename);

        if (isset($mediaCollectionthumbnailResponse->status)
            && $mediaCollectionthumbnailResponse->status == 'success'
        ) {
            return true;
        }
        return false;
    }

    private function getPreviewPicture($hiveProjectId, $previewNum, $hivePreviewFileExtension)
    {
        $imageModel = new Default_Model_DbTable_Image();

        $config = Zend_Registry::get('config');

        $fileName = $hiveProjectId . '-' . $previewNum . '.' . $hivePreviewFileExtension;
        $cnFileUrl = '/hive/content-pre' . $previewNum . '/' . $fileName;

        return $cnFileUrl;
    }

    private function file_get_contents_curl($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

}