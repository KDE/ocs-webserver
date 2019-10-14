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

use YoHang88\LetterAvatar\LetterAvatar;

class Default_Model_Member extends Default_Model_DbTable_Member
{
    const CASE_INSENSITIVE = 1;

    /**
     * @param int    $count
     * @param string $orderBy
     * @param string $dir
     *
     * @return Zend_Db_Table_Rowset
     * @throws Zend_Exception
     */
    public function fetchNewActiveMembers($count = 100, $orderBy = 'created_at', $dir = 'DESC')
    {
        if (empty($count)) {
            return $this->generateRowSet($this->createRow());
        }

        $allowedDirection = array('desc' => true, 'asc' => true);
        if (false == isset($allowedDirection[strtolower($dir)])) {
            $dir = null;
        }

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . md5($count . $orderBy . $dir);
        $members = $cache->load($cacheName);

        if ($members) {
            return $members;
        } else {

            $sql = '
              SELECT count(*) AS `total_count`
              FROM `member`
              WHERE `is_active` = :activeVal
                 AND `type` = :typeVal
               AND `profile_image_url` <> :defaultImgUrl
               AND `profile_image_url` LIKE :likeImgUrl
          ';

            $resultCnt = $this->_db->fetchRow($sql, array(
                'activeVal'     => Default_Model_Member::MEMBER_ACTIVE,
                'typeVal'       => Default_Model_Member::MEMBER_TYPE_PERSON,
                'defaultImgUrl' => 'hive/user-pics/nopic.png',
                'likeImgUrl'    => 'hive/user-bigpics/0/%'
            ));

            $totalcnt = $resultCnt['total_count'];

            if ($totalcnt > $count) {
                $offset = ' offset ' . rand(0, $totalcnt - $count);
            } else {
                $offset = '';
            }

            $sql = '
                SELECT *
                FROM `member`
                WHERE `is_active` = :activeVal
                   AND `type` = :typeVal
            	   AND `profile_image_url` <> :defaultImgUrl
                 AND `profile_image_url` LIKE :likeImgUrl
            ';
            //$sql .= ' ORDER BY ' . $this->_db->quoteIdentifier($orderBy) . ' ' . $dir;

            $sql .= ' LIMIT ' . $this->_db->quote($count, Zend_Db::INT_TYPE);
            $sql .= $offset;

            $resultMembers = $this->getAdapter()->query($sql, array(
                'activeVal'     => Default_Model_Member::MEMBER_ACTIVE,
                'typeVal'       => Default_Model_Member::MEMBER_TYPE_PERSON,
                'defaultImgUrl' => 'hive/user-pics/nopic.png',
                'likeImgUrl'    => 'hive/user-bigpics/0/%'
            ))->fetchAll();

            $resultSet = $this->generateRowSet($resultMembers);

            $cache->save($resultSet, $cacheName, array(), 14400);

            return $resultSet;
        }
    }

    /**
     * @param $data
     *
     * @return Zend_Db_Table_Rowset
     */
    protected function generateRowSet($data)
    {
        $classRowSet = $this->getRowsetClass();

        $returnRowSet = new $classRowSet(array(
            'table'    => $this,
            'rowClass' => $this->getRowClass(),
            'stored'   => true,
            'data'     => $data
        ));

        return $returnRowSet;
    }

    /**
     * @return array
     * @deprecated
     */
    public function getMembersForSelectList()
    {
        $selectArr =
            $this->_db->fetchAll("SELECT `member_id`,`username`,`firstname`, `lastname` FROM {$this->_name} WHERE is_active=1 AND is_deleted=0 ORDER BY username");

        $arrayModified = array();

        $arrayModified[0] = "Benutzer wählen";
        foreach ($selectArr as $item) {
            $tmpStr = ($item['firstname']) ? $item['firstname'] : "";
            $tmpStr .= ($item['lastname']) ? ", " . $item['lastname'] : "";
            $tmpStr = ($tmpStr != "") ? " (" . $tmpStr . ")" : "";

            $arrayModified[$item['member_id']] = stripslashes($item['username'] . $tmpStr);
        }

        return $arrayModified;
    }

    /**
     * @param int $member_id
     *
     * @param     $verification_value
     *
     * @return boolean returns true if successful
     * @throws Zend_Db_Statement_Exception
     */
    public function activateMemberFromVerification($member_id, $verification_value)
    {
        $sql = "
            UPDATE `member`
              STRAIGHT_JOIN `member_email` ON `member`.`member_id` = `member_email`.`email_member_id` AND `member_email`.`email_checked` IS NULL AND `member`.`is_deleted` = 0 AND `member_email`.`email_deleted` = 0
            SET `member`.`mail_checked` = 1, `member`.`is_active` = 1, `member`.`changed_at` = NOW(), `member_email`.`email_checked` = NOW()
            WHERE `member`.`member_id` = :memberId AND `member_email`.`email_verification_value` = :verificationValue;
        ";
        $stmnt = $this->_db->query($sql, array('memberId' => $member_id, 'verificationValue' => $verification_value));

        return $stmnt->rowCount() > 0 ? true : false;
    }

    /**
     * @param int $member_id
     *
     * @throws Zend_Exception
     */
    public function setDeleted($member_id)
    {
        $updateValues = array(
            'is_active'  => 0,
            'is_deleted' => 1,
            'deleted_at' => new Zend_Db_Expr('Now()'),
        );
        $this->update($updateValues, $this->_db->quoteInto('member_id=?', $member_id, 'INTEGER'));

        $memberLog = new Default_Model_MemberDeactivationLog();
        $memberLog->logMemberAsDeleted($member_id);


        $this->setMemberProjectsDeleted($member_id);
        $this->setMemberCommentsDeleted($member_id);
        $this->setMemberRatingsDeleted($member_id);
        $this->setMemberReportingsDeleted($member_id);
        $this->setMemberEmailsDeleted($member_id);
        //$this->setMemberPlingsDeleted($member_id);
        //$this->removeMemberProjectsFromSearch($member_id);
        $this->setDeletedInMaterializedView($member_id);
        $this->setDeletedInSubSystems($member_id);
    }

    //User ist mind. 1 jahr alt, user ist supporter, user hat minimum 20 kommentare

    private function setMemberProjectsDeleted($member_id)
    {
        $modelProject = new Default_Model_Project();
        $modelProject->setAllProjectsForMemberDeleted($member_id);
    }

    private function setMemberCommentsDeleted($member_id)
    {
        $modelComments = new Default_Model_ProjectComments();
        $modelComments->setAllCommentsForUserDeleted($member_id);
    }

    private function setMemberRatingsDeleted($member_id)
    {
        $modelRatings = new Default_Model_DbTable_ProjectRating();
        $affectedRows = $modelRatings->setDeletedByMemberId($member_id);
        if (false === empty($affectedRows)) {
            $modelProject = new Default_Model_DbTable_Project();
            $modelProject->deleteLikes($affectedRows);
        }
    }

    private function setMemberReportingsDeleted($member_id)
    {
        $modelReportsProject = new Default_Model_DbTable_ReportProducts();
        $modelReportsProject->setDeleteByMember($member_id);
        $modelReportsComments = new Default_Model_DbTable_ReportComments();
        $modelReportsComments->setDeleteByMember($member_id);
    }

    private function setMemberEmailsDeleted($member_id)
    {
        $modelEmail = new Default_Model_DbTable_MemberEmail();
        $modelEmail->setDeletedByMember($member_id);
    }

    private function setDeletedInMaterializedView($member_id)
    {
        $sql = "UPDATE `stat_projects` SET `status` = :new_status WHERE `member_id` = :member_id";

        $this->_db->query($sql,
            array('new_status' => Default_Model_DbTable_Project::PROJECT_DELETED, 'member_id' => $member_id))
                  ->execute();
    }

    private function setDeletedInSubSystems($member_id)
    {
        try {
            $id_server = new Default_Model_Ocs_OAuth();
            $id_server->deleteUser($member_id);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - oauth : ' . implode(PHP_EOL . " - ",
                    $id_server->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $ldap_server = new Default_Model_Ocs_Ldap();
            $ldap_server->deleteUser($member_id);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - ldap : ' . implode(PHP_EOL . " - ",
                    $ldap_server->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $openCode = new Default_Model_Ocs_Gitlab();
            $openCode->blockUser($member_id);
            $openCode->blockUserProjects($member_id);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - opencode : ' . implode(PHP_EOL . " - ",
                    $openCode->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $forum = new Default_Model_Ocs_Forum();
            $forum->blockUser($member_id);
            $forum->blockUserPosts($member_id);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - forum : ' . implode(PHP_EOL . " - ",
                    $forum->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    public function validDeleteMemberFromSpam($member_id)
    {
        $sql = 'SELECT 
              `m`.`created_at`
              , (`m`.`created_at`+ INTERVAL 12 MONTH < NOW()) `is_old`
              ,(SELECT count(1) FROM `comments` `c` WHERE `c`.`comment_member_id` = `m`.`member_id` AND `comment_active` = 1) `comments`
              ,(SELECT (DATE_ADD(max(`active_time`), INTERVAL 1 YEAR) > now()) FROM `support` `s`  WHERE `s`.`status_id` = 2  AND `s`.`member_id` =`m`.`member_id`) `is_supporter`
              FROM `member` `m` WHERE `member_id` = :member_id';
        $result = $this->_db->fetchRow($sql, array(
            'member_id' => $member_id,
        ));
        if ($result['is_supporter'] && $result['is_supporter'] == 1) {
            return false;
        }
        if ($result['is_old'] == 1 || $result['comments'] > 20) {
            return false;
        }

        return true;
    }

    /**
     * @param int $member_id
     *
     * @throws Zend_Db_Exception
     */
    public function setActivated($member_id)
    {
        $updateValues = array(
            'is_active'  => 1,
            'is_deleted' => 0,
            'changed_at' => new Zend_Db_Expr('Now()'),
            'deleted_at' => null
        );

        $this->update($updateValues, $this->_db->quoteInto('member_id=?', $member_id, 'INTEGER'));

        $memberLog = new Default_Model_MemberDeactivationLog();
        $memberLog->removeLogMemberAsDeleted($member_id);

        $this->setMemberProjectsActivated($member_id);
        $this->setMemberCommentsActivated($member_id);
        $this->setMemberEmailsActivated($member_id);

        $this->setActivatedInSubsystems($member_id);

        //$this->setMemberPlingsActivated($member_id);
    }

    private function setMemberProjectsActivated($member_id)
    {
        $modelProject = new Default_Model_Project();
        $modelProject->setAllProjectsForMemberActivated($member_id);
    }

    private function setMemberCommentsActivated($member_id)
    {
        $modelComment = new Default_Model_ProjectComments();
        $modelComment->setAllCommentsForUserActivated($member_id);
    }

    private function setMemberEmailsActivated($member_id)
    {
        $modelEmail = new Default_Model_DbTable_MemberEmail();
        $modelEmail->setActivatedByMember($member_id);
    }

    private function setActivatedInSubsystems($member_id)
    {
        try {
            $id_server = new Default_Model_Ocs_OAuth();
            $id_server->updateUser($member_id);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - oauth : ' . print_r($id_server->getMessages(), true));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $ldap_server = new Default_Model_Ocs_Ldap();
            $ldap_server->createUser($member_id);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - ldap : ' . implode(PHP_EOL . " - ",
                    $ldap_server->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $openCode = new Default_Model_Ocs_Gitlab();
            $openCode->unblockUser($member_id);
            $openCode->unblockUserProjects($member_id);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - opencode : ' . implode(PHP_EOL . " - ",
                    $openCode->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $forum = new Default_Model_Ocs_Forum();
            $forum->unblockUser($member_id);
            $forum->unblockUserPosts($member_id);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - forum : ' . implode(PHP_EOL." - ", $forum->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    /**
     * @param int  $member_id
     *
     * @param bool $onlyNotDeleted
     *
     * @return Zend_Db_Table_Row
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchMemberData($member_id, $onlyNotDeleted = true)
    {
        if (null === $member_id) {
            return null;
        }

        $sql = '
                SELECT `m`.*, `member_email`.`email_address` AS `mail`, IF(ISNULL(`member_email`.`email_checked`),0,1) AS `mail_checked`, `member_email`.`email_address`, `mei`.`external_id`, `mei`.`gitlab_user_id`
                FROM `member` AS `m`
                JOIN `member_email` ON `m`.`member_id` = `member_email`.`email_member_id` AND `member_email`.`email_primary` = 1
                LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
                WHERE
                    (`m`.`member_id` = :memberId)
        ';

        if ($onlyNotDeleted) {
            $sql .= " AND (m.is_deleted = " . self::MEMBER_NOT_DELETED . ")";
        }

        $result = $this->getAdapter()->query($sql, array('memberId' => $member_id))->fetch();

        $classRow = $this->getRowClass();

        return new $classRow(array('table' => $this, 'stored' => true, 'data' => $result));
    }

    /**
     * @param      $member_id
     * @param bool $onlyActive
     *
     * @return null|Zend_Db_Table_Row_Abstract
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchMember($member_id, $onlyActive = true)
    {
        if (empty($member_id)) {
            return null;
        }

        $sql = "
                SELECT `m`.*, `member_email`.`email_address` AS `mail`, IF(ISNULL(`member_email`.`email_checked`),0,1) AS `mail_checked`, `member_email`.`email_address`, `mei`.`external_id`
                FROM `member` AS `m`
                JOIN `member_email` ON `m`.`member_id` = `member_email`.`email_member_id` AND `member_email`.`email_primary` = 1
                LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
                WHERE `m`.`member_id` = :memberId";

        if ($onlyActive) {
            $sql .= " AND `m`.`is_deleted` = " . self::MEMBER_NOT_DELETED . " AND `m`.`is_active` = " . self::MEMBER_ACTIVE;
        }

        $stmnt = $this->_db->query($sql, array('memberId' => $member_id));

        if ($stmnt->rowCount() == 0) {
            return null;
        }

        return $this->generateRowClass($stmnt->fetch());
    }

    /**
     * @param string $user_name
     *
     * @return Zend_Db_Table_Row
     */
    public function fetchMemberFromHiveUserName($user_name)
    {
        $sql = "
                SELECT *
                FROM `member`
        		WHERE `source_id` = :sourceId
                  AND `username` = :userName
                ";

        return $this->_db->fetchRow($sql,
            array('sourceId' => Default_Model_Member::SOURCE_HIVE, 'userName' => $user_name));
    }

    /**
     * @param string $user_name
     *
     * @return Zend_Db_Table_Row
     */
    public function fetchMemberFromHiveUserId($user_id)
    {
        $sql = "
                SELECT *
                FROM `member`
        	WHERE `source_id` = :sourceId
                AND `source_pk` = :userId
                ";

        return $this->_db->fetchRow($sql, array('sourceId' => Default_Model_Member::SOURCE_HIVE, 'userId' => $user_id));
    }

    /**
     * @param int $member_id
     * @param int $limit
     *
     * @return Zend_Db_Table_Rowset
     */
    public function fetchFollowedMembers($member_id, $limit = null)
    {
        $sql = "
                SELECT member_follower.member_id,
                       member_follower.follower_id,
                       member.*
                FROM member_follower
                LEFT JOIN member ON member_follower.member_id = member.member_id
        		WHERE member_follower.follower_id = :followerId
                  AND member.is_active = :activeVal
                GROUP BY member_follower.member_id
                ORDER BY max(member_follower.member_follower_id) DESC
                ";

        if (null != $limit) {
            $sql .= $this->_db->quoteInto(" limit ?", $limit, 'INTEGER');
        }

        $result = $this->_db->fetchAll($sql, array('followerId' => $member_id, 'activeVal' => self::MEMBER_ACTIVE));

        return $this->generateRowSet($result);
    }

    /**
     * @param int  $member_id
     * @param null $limit
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchFollowedProjects($member_id, $limit = null)
    {
        $sql = "
                SELECT `project_follower`.`project_id`,
                        `project`.`title`,
                        `project`.`image_small`                                              
                FROM `project_follower`
                  JOIN `project` ON `project_follower`.`project_id` = `project`.`project_id`                 
                  WHERE `project_follower`.`member_id` = :member_id
                  AND `project`.`status` = :project_status
                  AND `project`.`type_id` = 1               
                ORDER BY `project_follower`.`project_follower_id` DESC
                ";

        if (null != $limit) {
            $sql .= $this->_db->quoteInto(" limit ?", $limit, 'INTEGER');
        }

        $result =
            $this->_db->fetchAll($sql,
                array('member_id' => $member_id, 'project_status' => Default_Model_Project::PROJECT_ACTIVE));

        return $this->generateRowSet($result);
    }

    public function fetchPlingedProjects($member_id, $limit = null)
    {
        $sql = "
                SELECT project_category.title AS catTitle,
                       project.*,
        			   member.*,
    				   plings.*
                FROM plings
                LEFT JOIN project ON plings.project_id = project.project_id
                LEFT JOIN project_category ON project.project_category_id = project_category.project_category_id
        		LEFT JOIN member ON project.member_id = member.member_id
        		WHERE plings.member_id = :member_id
    			  AND plings.status_id = 2
                  AND project.status = :project_status
                  AND project.type_id = 1
                ORDER BY plings.create_time DESC
                ";

        if (null != $limit) {
            $sql .= $this->_db->quoteInto(" limit ?", $limit, 'INTEGER');
        }

        $result =
            $this->_db->fetchAll($sql,
                array('member_id' => $member_id, 'project_status' => Default_Model_Project::PROJECT_ACTIVE));

        return $this->generateRowSet($result);
    }

    public function fetchProjectsSupported($member_id, $limit = null)
    {
        $sql = "
                SELECT `project_category`.`title` AS `catTitle`,
                       `project`.`project_id`,
                       `project`.`title`,
                       `project`.`image_small`,
                       `plings`.`member_id`,
                       `plings`.`amount`,
                       `plings`.`create_time`,
                       `member`.`profile_image_url`,
                       `member`.`username`

                FROM `plings`
                JOIN `project` ON `plings`.`project_id` = `project`.`project_id`
                JOIN `project_category` ON `project`.`project_category_id` = `project_category`.`project_category_id`
                JOIN `member` ON `plings`.`member_id` = `member`.`member_id`
                WHERE `project`.`member_id` = :member_id
                  AND `plings`.`status_id` = 2
                  AND `project`.`status` = :project_status
                  AND `project`.`type_id` = 1
                ORDER BY `plings`.`create_time` DESC
                ";

        if (null != $limit) {
            $sql .= $this->_db->quoteInto(" limit ?", $limit, 'INTEGER');
        }

        $result =
            $this->_db->fetchAll($sql,
                array('member_id' => $member_id, 'project_status' => Default_Model_Project::PROJECT_ACTIVE));

        return $this->generateRowSet($result);
    }

    /**
     * @param $userData
     *
     * @return array
     * @throws Exception
     */
    public function createNewUser($userData)
    {
        $uuidMember = Local_Tools_UUID::generateUUID();

        if (false == isset($userData['password'])) {
            throw new Exception(__METHOD__ . ' - user password is not set.');
        }
        $userData['password'] = Local_Auth_Adapter_Ocs::getEncryptedPassword($userData['password'],Default_Model_DbTable_Member::SOURCE_LOCAL);
        if (false == isset($userData['roleId'])) {
            $userData['roleId'] = self::ROLE_ID_DEFAULT;
        }
        if ((false == isset($userData['avatar'])) OR (false == isset($userData['profile_image_url']))) {
            $imageFilename = $this->generateIdentIcon($userData, $uuidMember);
            $userData['avatar'] = $imageFilename;
            $userData['profile_image_url'] = IMAGES_MEDIA_SERVER . '/cache/200x200-2/img/' . $imageFilename;
        }
        if (false == isset($userData['uuid'])) {
            $userData['uuid'] = $uuidMember;
        }
        if (false == isset($userData['mail_checked'])) {
            $userData['mail_checked'] = 0;
        }

        //email is allways lower case
        $userData['mail'] = strtolower(trim($userData['mail']));

        $newUser = $this->storeNewUser($userData)->toArray();

        $memberMail = $this->createPrimaryMailAddress($newUser);
        $externalId = $this->createExternalId($newUser['member_id']);

        $newUser['verificationVal'] = $memberMail->email_verification_value;
        $newUser['externalId'] = $externalId;

        return $newUser;
    }

    /**
     * @param $userData
     * @param $uuidMember
     *
     * @return string
     * @throws Exception
     */
    protected function generateIdentIcon($userData, $uuidMember)
    {
        require_once 'vendor/autoload.php';
        // $name = substr($userData['username'],0,1).' '.substr($userData['username'],1);
        $name = $userData['username'] . '  ';
        $avatar = new LetterAvatar($name, 'square', 400);
        $tmpImagePath = IMAGES_UPLOAD_PATH . 'tmp/' . $uuidMember . '.png';
        $avatar->saveAs($tmpImagePath, LetterAvatar::MIME_TYPE_PNG);
        $imageService = new Default_Model_DbTable_Image();
        $imageFilename = $imageService->saveImageOnMediaServer($tmpImagePath);

        return $imageFilename;
    }

    /**
     * @param array $userData
     *
     * @return Zend_Db_Table_Row_Abstract
     */
    public function storeNewUser($userData)
    {
        $newUserData = $this->createRow($userData);
        $newUserData->save();

        //create a user specified main project in project table
        $projectId = $this->createPersonalProject($newUserData->toArray());

        //and save the id in member table
        $newUserData->main_project_id = $projectId;
        $newUserData->save();

        return $newUserData;
    }

    /**
     * @param array $userData
     *
     * @return mixed $projectId
     */
    protected function createPersonalProject($userData)
    {
        $tableProject = new Default_Model_Project();
        /** @var Default_Model_DbRow_Project $newPersonalProject */
        $newPersonalProject = $tableProject->createRow($userData);
        $newPersonalProject->uuid = Local_Tools_UUID::generateUUID();
        $newPersonalProject->project_category_id = $newPersonalProject::CATEGORY_DEFAULT_PROJECT;
        $newPersonalProject->status = $newPersonalProject::STATUS_PROJECT_ACTIVE;
        $newPersonalProject->image_big = $newPersonalProject::DEFAULT_AVATAR_IMAGE;
        $newPersonalProject->image_small = $newPersonalProject::DEFAULT_AVATAR_IMAGE;
        $newPersonalProject->creator_id = $userData['member_id'];
        $newPersonalProject->title = $newPersonalProject::PERSONAL_PROJECT_TITLE;
        $projectId = $newPersonalProject->save();

        return $projectId;
    }

    /**
     * @param array $newUser
     * @return Zend_Db_Table_Row_Abstract
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Exception
     */
    private function createPrimaryMailAddress($newUser)
    {
        $modelEmail = new Default_Model_MemberEmail();
        $userMail = $modelEmail->saveEmailAsPrimary($newUser['member_id'], $newUser['mail'], $newUser['mail_checked']);

        return $userMail;
    }

    /**
     * @param int $member_id
     *
     * @return string
     */
    private function createExternalId($member_id)
    {
        $modelExternalId = new Default_Model_DbTable_MemberExternalId();
        $externalId = $modelExternalId->createExternalId($member_id);

        return $externalId;
    }

    public function fetchTotalMembersCount()
    {
        $sql = "
                SELECT
                    count(1) AS `total_member_count`
                FROM
                    `member`
               ";

        $result = $this->_db->fetchRow($sql);

        return $result['total_member_count'];
    }

    public function fetchTotalMembersInStoreCount()
    {
        $sql = "
                SELECT
                    count(1) AS `total_member_count`
                FROM
                    `member`
               ";

        $result = $this->_db->fetchRow($sql);

        return $result['total_member_count'];
    }

    /**
     * @param string $email
     *
     * @return null|Zend_Db_Table_Row_Abstract
     * @deprecated
     */
    public function fetchCheckedActiveLocalMemberByEmail($email)
    {
        $sel = $this->select()->where('mail=?', $email)->where('is_deleted = ?',
            Default_Model_DbTable_Member::MEMBER_NOT_DELETED)
                    ->where('is_active = ?', Default_Model_DbTable_Member::MEMBER_ACTIVE)
                    ->where('mail_checked = ?', Default_Model_DbTable_Member::MEMBER_MAIL_CHECKED)
                    ->where('login_method = ?', Default_Model_DbTable_Member::MEMBER_LOGIN_LOCAL);

        return $this->fetchRow($sel);
    }

    public function fetchEarnings($member_id, $limit = null)
    {
        $sql = "
                SELECT `project_category`.`title` AS `catTitle`,
                       `project`.*,
                       `member`.*,
                       `plings`.*
                FROM `plings`
                 JOIN `project` ON `plings`.`project_id` = `project`.`project_id`
                 JOIN `project_category` ON `project`.`project_category_id` = `project_category`.`project_category_id`
                 JOIN `member` ON `project`.`member_id` = `member`.`member_id`
                WHERE `plings`.`status_id` = 2
                  AND `project`.`status` = :status
                  AND `project`.`type_id` = 1
                  AND `project`.`member_id` = :memberId
                ORDER BY `plings`.`create_time` DESC
                ";

        if (null != $limit) {
            $sql .= $this->_db->quoteInto(" limit ?", $limit, 'INTEGER');
        }

        $result = $this->_db->fetchAll($sql,
            array('memberId' => $member_id, 'status' => Default_Model_Project::PROJECT_ACTIVE));

        return $this->generateRowSet($result);
    }

    /**
     * Finds an active user by given username or email ($identity)
     * Returns an empty rowset when no user found.
     *
     * @param string $identity could be the username or users mail address
     * @param bool   $withLoginLocal
     *
     * @return Zend_Db_Table_Row_Abstract
     */
    public function findActiveMemberByIdentity($identity, $withLoginLocal = false)
    {
        $sqlName = "SELECT * FROM `member` WHERE `is_active` = :active AND `is_deleted` = :deleted AND `username` = :identity";
        $sqlMail = "SELECT * FROM `member` WHERE `is_active` = :active AND `is_deleted` = :deleted AND `mail` = :identity";
        if ($withLoginLocal) {
            $sqlName .= " AND login_method = '" . self::MEMBER_LOGIN_LOCAL . "'";
            $sqlMail .= " AND login_method = '" . self::MEMBER_LOGIN_LOCAL . "'";
        }

        // test identity as username
        $resultName = $this->getAdapter()->fetchRow($sqlName,
            array('active' => self::MEMBER_ACTIVE, 'deleted' => self::MEMBER_NOT_DELETED, 'identity' => $identity));
        if ((false !== $resultName) AND (count($resultName) > 0)) {
            return $this->generateRowClass($resultName);
        }

        // test identity as mail
        $resultMail = $this->getAdapter()->fetchRow($sqlMail,
            array('active' => self::MEMBER_ACTIVE, 'deleted' => self::MEMBER_NOT_DELETED, 'identity' => $identity));
        if ((false !== $resultMail) AND (count($resultMail) > 0)) {
            return $this->generateRowClass($resultMail);
        }

        return $this->createRow();
    }

    /**
     * @param string $username
     * @return mixed
     */
    public function findActiveMemberByName($username)
    {
        $sql = '
          select m.member_id,m.username,profile_image_url 
          from member m 
          where m.is_active=1 and m.is_deleted = 0 and m.username like "' . $username . '%"
          limit 20
      ';
        $result = $this->getAdapter()->fetchAll($sql);

        return $result;
    }

    /**
     * @param string $hash
     * @param bool   $only_active
     *
     * @return array | false
     */
    public function findMemberForMailHash($hash, $only_active = true)
    {
        $sql = "
            SELECT `m`.* 
            FROM `member_email` AS `me`
            JOIN `member` AS `m` ON `m`.`member_id` = `me`.`email_member_id`
            WHERE `me`.`email_hash` = :email_hash
        ";

        if ($only_active) {
            $sql .= " `m`.`is_active` = 1 AND `m`.`is_deleted` = 0";
        }

        $member = $this->getAdapter()->fetchRow($sql, array('email_hash' => $hash));

        if (empty($member)) {
            return false;
        }

        return $member;
    }

    /**
     * @param Zend_Db_Table_Row_Abstract $memberData
     *
     * @return bool
     */
    public function isHiveUser($memberData)
    {
        if (empty($memberData)) {
            return false;
        }
        //20180801 ronald: If a hive user change his password, he gets the ocs password type and we do
        //have to check against the old hive password style
        //if ($memberData->source_id == self::SOURCE_HIVE) {
        //    return true;
        //}
        if ($memberData->password_type == self::PASSWORD_TYPE_HIVE) {
            return true;
        }

        return false;
    }

    public function fetchActiveHiveUserByUsername($username)
    {
        $sql = 'SELECT * FROM member WHERE username = :username AND is_active = 1 AND member.source_id = 1 AND member.is_deleted = 0';

        $result = $this->getAdapter()->query($sql, array('username' => $username))->fetch();

        return $result;
    }

    /**
     * @param $username
     * @return int|null
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchActiveUserByUsername($username)
    {
        $sql = 'SELECT DISTINCT `member`.`member_id`
                FROM `member`
                WHERE LOWER(`username`) = :username
                AND `is_active` = 1 
                AND `member`.`is_deleted` = 0';

        $result = $this->getAdapter()->query($sql, array('username' => strtolower($username)))->fetchAll();

        if ($result && count($result) > 0) {
            $member_id = (int)$result[0]['member_id'];

            return $member_id;
        }

        return null;
    }

    public function fetchCommentsCount($member_id)
    {
        $sql = "
                  SELECT
                      count(1) AS count
                  FROM
                      comments 
                  where comment_target_id <> 0 and comment_member_id = :member_id and comment_active = :comment_status
                 ";
        $result = $this->_db->fetchRow($sql, array(
            'member_id'      => $member_id,
            'comment_status' => Default_Model_DbTable_Comments::COMMENT_ACTIVE
        ));

        return $result['count'];
    }

    /**
     * @param      $member_id
     * @param null $limit
     *
     * @return Zend_Paginator
     * @throws Zend_Paginator_Exception
     */
    public function fetchComments($member_id, $limit = null)
    {
        $result = $this->fetchCommentsList($member_id, $limit);
        if (count($result) > 0) {
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
        } else {
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array(array()));
        }
    }

    /**
     * @param      $member_id
     * @param null $limit
     *
     * @return Zend_Paginator
     * @throws Zend_Paginator_Exception
     */
    public function fetchCommentsList($member_id, $limit = null)
    {
        $sql = '
            SELECT
                `comment_id`
                ,`comment_text`
                ,`member`.`member_id`
                ,`member`.`profile_image_url`
                ,`comment_created_at`
                ,`stat_projects`.`username`
                ,`stat_projects`.`project_member_id`
                ,`comment_target_id`
                ,`stat_projects`.`title`
                ,`stat_projects`.`project_id`      
                ,`stat_projects`.`laplace_score`
                ,`stat_projects`.`count_likes`
                ,`stat_projects`.`count_dislikes`
                ,`stat_projects`.`image_small` 
                ,`stat_projects`.`version`
                ,`stat_projects`.`cat_title`
                ,`stat_projects`.`count_comments`
                ,`stat_projects`.`changed_at`
                ,`stat_projects`.`created_at`        
            FROM `comments`
            INNER JOIN  `member` ON `comments`.`comment_member_id` = `member`.`member_id`
            INNER JOIN `stat_projects` ON `comments`.`comment_target_id` = `stat_projects`.`project_id` AND `comments`.`comment_type` = 0
            WHERE `comments`.`comment_active` = :comment_status
            AND `stat_projects`.`status` = :project_status
            AND `comments`.`comment_member_id` = :member_id
            ORDER BY `comments`.`comment_created_at` DESC
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = $this->_db->fetchAll($sql, array(
            'member_id'      => $member_id,
            'project_status' => Default_Model_DbTable_Project::PROJECT_ACTIVE,
            'comment_status' => Default_Model_DbTable_Comments::COMMENT_ACTIVE
        ));

        return $result;
    }

    public function fetchCntSupporters($member_id)
    {
        $sql = '
                SELECT DISTINCT `plings`.`member_id` FROM `plings`
                 JOIN `project` ON `plings`.`project_id` = `project`.`project_id`                
                 JOIN `member` ON `project`.`member_id` = `member`.`member_id`
                WHERE `plings`.`status_id` = 2
                  AND `project`.`status` = :project_status
                  AND `project`.`type_id` = 1
                  AND `project`.`member_id` = :member_id
            ';
        $result =
            $this->_db->fetchAll($sql,
                array('member_id' => $member_id, 'project_status' => Default_Model_Project::PROJECT_ACTIVE));

        return count($result);
    }

    public function fetchSupporterDonationInfo($member_id)
    {
        /*$sql = 'SELECT max(active_time) AS active_time_max
                            ,min(active_time)  AS active_time_min
                            ,(DATE_ADD(max(active_time), INTERVAL 1 YEAR) > now()) AS issupporter
                            ,count(1)  AS cnt from support  where status_id = 2 AND type_id = 0 AND member_id = :member_id ';*/
        $sql = "
                select 
                member_id,
                max(active_time_max) as active_time_max,
                min(active_time_min) as active_time_min,
                max(is_valid) as issupporter,
                count(1) AS cnt
                from v_support
                where member_id = :member_id
        ";
        $result = $this->getAdapter()->fetchRow($sql, array('member_id' => $member_id));

        return $result;
    }

    public function fetchSupporterSubscriptionInfo($member_id)
    {
        $sql = 'SELECT create_time,amount,period,period_frequency from support  where status_id = 2 AND type_id = 1 
                AND member_id = :member_id
                ORDER BY create_time desc
                LIMIT 1';
        $result = $this->getAdapter()->fetchRow($sql, array('member_id' => $member_id));

        return $result;
    }

    public function fetchSupporterSectionInfo($member_id)
    {
        $sql = "select GROUP_CONCAT(distinct c.name) sections from 
                section_support s, support t , section c
                where s.support_id = t.id and s.section_id = c.section_id
                and  t.member_id = :member_id and t.status_id=2
                and s.is_active = 1
                order by c.order";
        $result = $this->getAdapter()->fetchRow($sql, array('member_id' => $member_id));        
        return $result;
    }

    public function fetchLastActiveTime($member_id)
    {
        $sql_page_views =
            "SELECT `created_at` AS `lastactive` FROM `stat_page_views` WHERE `member_id` = :member_id ORDER BY `created_at` DESC LIMIT 1";
        $sql_activities = "SELECT `time` AS lastactive FROM activity_log WHERE member_id = :member_id ORDER BY `time` DESC LIMIT 1";

        $result_page_views = $this->getAdapter()->fetchRow($sql_page_views, array('member_id' => $member_id));
        $result_activities = $this->getAdapter()->fetchRow($sql_activities, array('member_id' => $member_id));

        if (count($result_page_views) > 0 AND count($result_activities) > 0) {
            return $result_page_views['lastactive'] > $result_activities['lastactive'] ? $result_page_views['lastactive']
                : $result_activities['lastactive'];
        }
        if (count($result_page_views) > count($result_activities)) {
            return $result_page_views['lastactive'];
        }
        if (count($result_activities) > count($result_page_views)) {
            return $result_activities['lastactive'];
        }

        return null;
    }

    /**
     * @param int $member_id
     *
     * @return array
     */
    public function fetchContributedProjectsByCat($member_id)
    {
        $projects = $this->fetchSupportedProjects($member_id);
        $catArray = array();
        if (count($projects) == 0) {
            return $catArray;
        }

        foreach ($projects as $pro) {
            $catArray[$pro->catTitle] = array();
        }

        $helperProductUrl = new Default_View_Helper_BuildProductUrl();
        foreach ($projects as $pro) {
            $projArr = array();
            $projArr['id'] = $pro->project_id;
            $projArr['name'] = $pro->title;
            $projArr['image'] = $pro->image_small;
            $projArr['url'] = $helperProductUrl->buildProductUrl($pro->project_id, '', null, true);
            $projArr['sumAmount'] = $pro->sumAmount;
            array_push($catArray[$pro->catTitle], $projArr);
        }

        return $catArray;
    }

    /**
     * @param int  $member_id
     * @param null $limit
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchSupportedProjects($member_id, $limit = null)
    {
        $sql = "
                SELECT plings.project_id,                       
                       project.title,
                       project.image_small,                       
                       project_category.title AS catTitle,                       
                       (SELECT SUM(amount) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2) AS sumAmount
                FROM plings
                 JOIN project ON plings.project_id = project.project_id
                 JOIN project_category ON project.project_category_id = project_category.project_category_id                 
                WHERE plings.status_id IN (2,3,4)
                  AND plings.member_id = :member_id
                  AND project.status = :project_status
                  AND project.type_id = 1
                GROUP BY plings.project_id
                ORDER BY sumAmount DESC
                ";

        if (null != $limit) {
            $sql .= $this->_db->quoteInto(" limit ?", $limit, 'INTEGER');
        }

        $result =
            $this->_db->fetchAll($sql,
                array('member_id' => $member_id, 'project_status' => Default_Model_Project::PROJECT_ACTIVE));

        return $this->generateRowSet($result);
    }

    /**
     * @param string $value
     * @param int    $test_case_sensitive
     * @param array  $omitMember
     * @param bool   $onlyActive
     *
     * @return array return an array of rows
     */
    public function findUsername($value, $test_case_sensitive = self::CASE_INSENSITIVE, $omitMember = array(), $onlyActive = false)
    {
        $sql = "
            SELECT *
            FROM `member`
        ";
        if ($test_case_sensitive == self::CASE_INSENSITIVE) {
            $sql .= "WHERE LCASE(member.username) = LCASE(:username)";
        } else {
            $sql .= "WHERE member.username = :username";
        }

        if (count($omitMember) > 0) {
            $sql .= " AND member.member_id NOT IN (" . implode(',', $omitMember) . ")";
        }

        if ($onlyActive) {
            $sql .= " AND member.is_active = 1 and member.is_deleted = 0";
        }

        return $this->_db->fetchAll($sql, array('username' => $value));
    }

    /**
     * @param string $login
     *
     * @return int
     */
    public function generateUniqueUsername($login)
    {
        $sql = "SELECT COUNT(*) AS `counter` FROM `member` WHERE `username` REGEXP CONCAT(:user_name,'[_0-9]*$')";
        $result = $this->_db->fetchRow($sql, array('user_name' => $login));

        return $login . '_' . $result['counter'];
    }

    /**
     * @param int    $member_id
     * @param string $email
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function setActive($member_id, $email)
    {
        $sql = "
            UPDATE `member`
              STRAIGHT_JOIN `member_email` ON `member`.`member_id` = `member_email`.`email_member_id` AND `member_email`.`email_checked` IS NULL AND `member`.`is_deleted` = 0 AND `member_email`.`email_deleted` = 0
            SET `member`.`mail_checked` = 1, `member`.`is_active` = 1, `member`.`changed_at` = NOW(), `member_email`.`email_checked` = NOW()
            WHERE `member`.`member_id` = :memberId AND `member_email`.`email_address` = :mailAddress;
        ";
        $stmnt = $this->_db->query($sql, array('memberId' => $member_id, 'mailAddress' => $email));

        return $stmnt->rowCount() > 0 ? true : false;
    }

    /**
     * @param string $identity
     *
     * @return Zend_Db_Table_Row_Abstract
     */
    public function findActiveMemberByMail($identity)
    {
        $sqlMail = "
                    SELECT `m`.*, `me`.`email_address` AS `mail`, IF(ISNULL(`me`.`email_checked`),0,1) AS `mail_checked`
                    FROM `member` AS `m`
                    JOIN `member_email` AS `me` ON `me`.`email_member_id` = `m`.`member_id` AND `me`.`email_primary` = 1
                    WHERE `is_active` = :active AND `is_deleted` = :deleted AND `me`.`email_address` = :identity
        ";

        // test identity as mail
        $resultMail = $this->getAdapter()->fetchRow($sqlMail,
            array('active' => self::MEMBER_ACTIVE, 'deleted' => self::MEMBER_NOT_DELETED, 'identity' => $identity));
        if ((false !== $resultMail) AND (count($resultMail) > 0)) {
            return $this->generateRowClass($resultMail);
        }

        return $this->createRow();
    }

    public function getMembersAvatarOldAutogenerated($orderby = 'member_id desc', $limit = null, $offset = null)
    {
        $sql = "
                     SELECT * FROM `tmp_member_avatar_unknow` 
             ";


        if (isset($orderby)) {
            $sql = $sql . '  order by ' . $orderby;
        }

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }

        $resultSet = $this->_db->fetchAll($sql);

        return $resultSet;
    }

    public function getMembersAvatarOldAutogeneratedTotalCount()
    {
        $sql = " 
                      select count(1) as cnt
                      from tmp_member_avatar_unknow 
        ";
        $result = $this->getAdapter()->query($sql, array())->fetchAll();

        return $result[0]['cnt'];
    }

    public function updateAvatarTypeId($member_id, $type_id)
    {
        $sql = "
                      update member set avatar_type_id = :type_id where member_id = :member_id
                   ";
        $this->getAdapter()->query($sql, array('type_id' => $type_id, 'member_id' => $member_id));
    }

    /**
     * @param $userData
     * @param $uuidMember
     *
     * @return string
     * @throws Exception
     */
    protected function generateIdentIcon_old($userData, $uuidMember)
    {
        $identIcon = new Local_Tools_Identicon();
        $tmpImagePath = IMAGES_UPLOAD_PATH . 'tmp/' . $uuidMember . '.png';
        imagepng($identIcon->renderIdentIcon(sha1($userData['mail']), 1100), $tmpImagePath);

        $imageService = new Default_Model_DbTable_Image();
        $imageFilename = $imageService->saveImageOnMediaServer($tmpImagePath);

        return $imageFilename;
    }

    /**
     * @param int $member_id
     *
     * @throws Exception
     * @deprecated since we're using solr server for searching
     */
    private function removeMemberProjectsFromSearch($member_id)
    {
        $modelProject = new Default_Model_Project();
        $memberProjects = $modelProject->fetchAllProjectsForMember($member_id);
        $modelSearch = new Default_Model_Search_Lucene();
        foreach ($memberProjects as $memberProject) {
            $product = array();
            $product['project_id'] = $memberProject->project_id;
            $product['project_category_id'] = $memberProject->project_category_id;
            $modelSearch->deleteDocument($product);
        }
    }

    public static function cleanAuthMemberForJson(array $authMember)
    {
        if (empty($authMember)) {
            return $authMember;
        }

        $unwantedKeys = array(
            'mail' => 0,
            'firstname' => 0,
            'lastname' => 0,
            'street' => 0,
            'zip' => 0,
            'phone' => 0,
            'paypal_mail' => 0,
            'gravatar_email' => 0,
            'source_pk' => 0,
            'source_id' => 0,
            'password_old' => 0,
            'password_type_old' => 0,
            'username_old' => 0,
            'mail_old' => 0
        );

        $authMember = array_diff_key($authMember, $unwantedKeys);

        return $authMember;
    }
}