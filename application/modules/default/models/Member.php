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
class Default_Model_Member extends Default_Model_DbTable_Member
{
    /**
     * @param int $count
     * @param string $orderBy
     * @param string $dir
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
              SELECT count(*) as total_count
              FROM member
              WHERE `is_active` = :activeVal
                 AND `type` = :typeVal
               AND `profile_image_url` <> :defaultImgUrl
               AND `profile_image_url` like :likeImgUrl
          ';


            $resultCnt = $this->_db->fetchRow($sql, array(
                'activeVal' => Default_Model_Member::MEMBER_ACTIVE,
                'typeVal' => Default_Model_Member::MEMBER_TYPE_PERSON,
                'defaultImgUrl' => 'hive/user-pics/nopic.png',
                'likeImgUrl' => 'hive/user-bigpics/0/%'
            ));


            $totalcnt = $resultCnt['total_count'];


            if ($totalcnt > $count) {
                $offset = ' offset ' . rand(0, $totalcnt - $count);
            } else {
                $offset = '';
            }

            $sql = '
                SELECT *
                FROM member
                WHERE `is_active` = :activeVal
                   AND `type` = :typeVal
            	   AND `profile_image_url` <> :defaultImgUrl
                 AND `profile_image_url` like :likeImgUrl
            ';
            //$sql .= ' ORDER BY ' . $this->_db->quoteIdentifier($orderBy) . ' ' . $dir;

            $sql .= ' LIMIT ' . $this->_db->quote($count, Zend_Db::INT_TYPE);
            $sql .= $offset;

            $resultMembers = $this->getAdapter()->query($sql, array(
                'activeVal' => Default_Model_Member::MEMBER_ACTIVE,
                'typeVal' => Default_Model_Member::MEMBER_TYPE_PERSON,
                'defaultImgUrl' => 'hive/user-pics/nopic.png',
                'likeImgUrl' => 'hive/user-bigpics/0/%'
            ))->fetchAll();


            $resultSet = $this->generateRowSet($resultMembers);

            $cache->save($resultSet, $cacheName, array(), 14400);

            return $resultSet;
        }

    }

    /**
     * @param $data
     * @return Zend_Db_Table_Rowset
     */
    protected function generateRowSet($data)
    {
        $classRowSet = $this->getRowsetClass();

        $returnRowSet = new $classRowSet(array(
            'table' => $this,
            'rowClass' => $this->getRowClass(),
            'stored' => true,
            'data' => $data
        ));
        return $returnRowSet;
    }

    /**
     * @return array
     * @deprecated
     */
    public function getMembersForSelectList()
    {
        $selectArr = $this->_db->fetchAll('SELECT member_id,username,firstname, lastname FROM ' . $this->_name . ' WHERE is_active=1 AND is_deleted=0 ORDER BY username');

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
     * @return int
     */
    public function activateMemberFromVerification($member_id)
    {
        $updateValues = array(
            'mail_checked' => 1,
            'is_active' => 1,
            'is_deleted' => 0,
            'changed_at' => new Zend_Db_Expr('Now()'),
        );

        return $this->update($updateValues, $this->_db->quoteInto('member_id=?', $member_id, 'INTEGER'));
    }

    /**
     * @param int $member_id
     */
    public function setDeleted($member_id)
    {
        $updateValues = array(
            'is_active' => 0,
            'is_deleted' => 1,
            'deleted_at' => new Zend_Db_Expr('Now()'),
        );

        $this->update($updateValues, $this->_db->quoteInto('member_id=?', $member_id, 'INTEGER'));

        $this->setMemberProjectsDeleted($member_id);
        $this->setMemberCommentsDeleted($member_id);
        // $this->setMemberPlingsDeleted($member_id);
        $this->removeMemberProjectsFromSearch($member_id);
    }

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

    /**
     * @param int $member_id
     */
    public function setActivated($member_id)
    {
        $updateValues = array(
            'is_active' => 1,
            'is_deleted' => 0,
            'changed_at' => new Zend_Db_Expr('Now()'),
        );

        $this->update($updateValues, $this->_db->quoteInto('member_id=?', $member_id, 'INTEGER'));

        $this->setMemberProjectsActivated($member_id);
        $this->setMemberCommentsActivated($member_id);
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

    /**
     * @param int $member_id
     * @return Zend_Db_Table_Row
     */
    public function fetchMemberData($member_id)
    {
        if (null === $member_id) {
            return null;
        }

        $sql = '
                SELECT 
                    `member`.*
                FROM
                    `member`
                WHERE
                    (member_id = :memberId) AND (is_deleted = :deletedVal)
        ';

        $result = $this->getAdapter()->query($sql,
            array('memberId' => $member_id, 'deletedVal' => self::MEMBER_NOT_DELETED))->fetch();

        $classRow = $this->getRowClass();

        return new $classRow(array('table' => $this, 'stored' => true, 'data' => $result));
    }

    /**
     * @param string $user_name
     * @return Zend_Db_Table_Row
     */
    public function fetchMemberFromHiveUserName($user_name)
    {
        $sql = "
                SELECT *
                FROM member
        		WHERE source_id = :sourceId
                  AND username = :userName
                ";

        return $this->_db->fetchRow($sql,
            array('sourceId' => Default_Model_Member::SOURCE_HIVE, 'userName' => $user_name));
    }

    /**
     * @param int $member_id
     * @param int $limit
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
     * @param int $member_id
     * @param null $limit
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchSupportedProjects($member_id, $limit = null)
    {
        /***
         * $sql = "
         * SELECT plings.project_id,
         * plings.member_id,
         * count(plings.member_id) AS collectPlingsFromMember,
         * project_category.title AS catTitle,
         * project.*,
         * member.*,
         * (SELECT COUNT(DISTINCT plings.member_id) FROM plings WHERE plings.status_id >= 2 AND plings.project_id = project.project_id) AS plingers,
         * (SELECT SUM(amount) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2) AS sumAmount,
         * (SELECT SUM(amount) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id IN (2,3,4)) AS collectPlingsAll
         * FROM plings
         * LEFT JOIN project ON plings.project_id = project.project_id
         * LEFT JOIN project_category ON project.project_category_id = project_category.project_category_id
         * LEFT JOIN member ON project.member_id = member.member_id
         * WHERE plings.status_id in (2,3,4)
         * AND plings.member_id = :member_id
         * AND project.status = :project_status
         * AND project.type_id = 1
         * GROUP BY plings.project_id
         * ORDER BY sumAmount DESC
         * ";
         **/
        $sql = "
                SELECT plings.project_id,                       
                       project.title,
                       project.image_small,                       
                       project_category.title AS catTitle,                       
                       (SELECT SUM(amount) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2) AS sumAmount
                FROM plings
                 JOIN project ON plings.project_id = project.project_id
                 JOIN project_category ON project.project_category_id = project_category.project_category_id                 
                WHERE plings.status_id in (2,3,4)
                  AND plings.member_id = :member_id
                  AND project.status = :project_status
                  AND project.type_id = 1
                GROUP BY plings.project_id
                ORDER BY sumAmount DESC
                ";

        if (null != $limit) {
            $sql .= $this->_db->quoteInto(" limit ?", $limit, 'INTEGER');
        }

        $result = $this->_db->fetchAll($sql,
            array('member_id' => $member_id, 'project_status' => Default_Model_Project::PROJECT_ACTIVE));

        return $this->generateRowSet($result);

    }

    /**
     * @param int $member_id
     * @param null $limit
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchFollowedProjects($member_id, $limit = null)
    {
        /**
         * $sql = "
         * SELECT project_follower.project_id,
         * project_follower.member_id,
         * project_category.title AS catTitle,
         * project.*,
         * member.*,
         * (SELECT COUNT(DISTINCT plings.member_id) FROM plings WHERE plings.status_id >= 2 AND plings.project_id = project.project_id) AS plingers,
         * (SELECT SUM(amount) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2) AS sumAmount,
         * (SELECT SUM(amount) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id IN (2,3,4)) AS collectPlingsAll
         * FROM project_follower
         * LEFT JOIN project ON project_follower.project_id = project.project_id
         * LEFT JOIN project_category ON project.project_category_id = project_category.project_category_id
         * LEFT JOIN member ON project.member_id = member.member_id
         * WHERE project_follower.member_id = :member_id
         * AND project.status = :project_status
         * AND project.type_id = 1
         * GROUP BY project_follower.project_id
         * ORDER BY max(project_follower.project_follower_id) DESC
         * ";
         **/

        $sql = "
                SELECT project_follower.project_id,
                        project.title,
                        project.image_small                                              
                FROM project_follower
                  JOIN project ON project_follower.project_id = project.project_id                 
                  WHERE project_follower.member_id = :member_id
                  AND project.status = :project_status
                  AND project.type_id = 1               
                ORDER BY project_follower.project_follower_id DESC
                ";

        if (null != $limit) {
            $sql .= $this->_db->quoteInto(" limit ?", $limit, 'INTEGER');
        }

        $result = $this->_db->fetchAll($sql,
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

        $result = $this->_db->fetchAll($sql,
            array('member_id' => $member_id, 'project_status' => Default_Model_Project::PROJECT_ACTIVE));

        return $this->generateRowSet($result);

    }

    public function fetchSupportedByProjects($member_id, $limit = null)
    {
        $sql = "
                SELECT project_category.title AS catTitle,
                       project.project_id,
                       project.title,
                       project.image_small,
                       plings.member_id,
                       plings.amount,
                       plings.create_time,
                       member.profile_image_url,
                       member.username

                FROM plings
                JOIN project ON plings.project_id = project.project_id
                JOIN project_category ON project.project_category_id = project_category.project_category_id
                JOIN member ON plings.member_id = member.member_id
                WHERE project.member_id = :member_id
                  AND plings.status_id = 2
                  AND project.status = :project_status
                  AND project.type_id = 1
                ORDER BY plings.create_time DESC
                ";

        if (null != $limit) {
            $sql .= $this->_db->quoteInto(" limit ?", $limit, 'INTEGER');
        }


        $result = $this->_db->fetchAll($sql,
            array('member_id' => $member_id, 'project_status' => Default_Model_Project::PROJECT_ACTIVE));

        return $this->generateRowSet($result);

    }

    public function createNewUser($userData)
    {
        $uuidMember = Local_Tools_UUID::generateUUID();

        if (false == isset($userData['password'])) {
            throw new Exception(__function__ . ': user password is not set.');
        }
        $userData['password'] = Local_Auth_Adapter_Ocs::getEncryptedPassword($userData['password'],
            Default_Model_DbTable_Member::SOURCE_LOCAL);
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
        if (false == isset($userData['verificationVal'])) {
            $verificationVal = MD5($userData['mail'] . $userData['username'] . time());
            $userData['verificationVal'] = $verificationVal;
        }

        return $this->storeNewUser($userData);
    }

    /**
     * @param $userData
     * @param $uuidMember
     * @return string
     */
    protected function generateIdentIcon($userData, $uuidMember)
    {
        $identIcon = new Local_Tools_Identicon();
        $tmpImagePath = IMAGES_UPLOAD_PATH . 'tmp/' . $uuidMember . '.png';
        imagepng($identIcon->renderIdentIcon(sha1($userData['mail']), 1100), $tmpImagePath);

        $imageService = new Default_Model_DbTable_Image();
        $imageFilename = $imageService->saveImageOnMediaServer($tmpImagePath);
        return $imageFilename;
    }

    /**
     * @param array $userData
     * @return Zend_Db_Table_Row_Abstract
     */
    public function storeNewUser($userData)
    {
        $newUserData = $this->createRow($userData);
        $newUserData->save();

        //Gleichzeitig auch ein Projekt anlegen
        $projectId = $this->storePersonalProject($newUserData->toArray());

        //Default-Prj in Member schreiben
        $newUserData->main_project_id = $projectId;
        $newUserData->save();

        return $newUserData;
    }

    /**
     * @param array $userData
     * @return mixed $projectId
     */
    protected function storePersonalProject($userData)
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

    public function fetchTotalMembersCount()
    {
        $sql = "
                SELECT
                    count(1) AS total_member_count
                FROM
                    member
               ";

        $result = $this->_db->fetchRow($sql);

        return $result['total_member_count'];
    }

    public function fetchTotalMembersInStoreCount()
    {
        $sql = "
                SELECT
                    count(1) AS total_member_count
                FROM
                    member
               ";

        $result = $this->_db->fetchRow($sql);

        return $result['total_member_count'];

    }

    /**
     * @param string $email
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchCheckedActiveLocalMemberByEmail($email)
    {
        $sel = $this->select()
            ->where('mail=?', $email)
            ->where('is_deleted = ?', Default_Model_DbTable_Member::MEMBER_NOT_DELETED)
            ->where('is_active = ?', Default_Model_DbTable_Member::MEMBER_ACTIVE)
            ->where('mail_checked = ?', Default_Model_DbTable_Member::MEMBER_MAIL_CHECKED)
            ->where('login_method = ?', Default_Model_DbTable_Member::MEMBER_LOGIN_LOCAL);

        return $this->fetchRow($sel);
    }

    public function fetchEarnings($member_id, $limit = null)
    {
        $sql = "
                SELECT project_category.title AS catTitle,
                       project.*,
                       member.*,
                       plings.*
                FROM plings
                 JOIN project ON plings.project_id = project.project_id
                 JOIN project_category ON project.project_category_id = project_category.project_category_id
                 JOIN member ON project.member_id = member.member_id
                WHERE plings.status_id = 2
                  AND project.status = " . Default_Model_Project::PROJECT_ACTIVE . "
                  AND project.type_id = 1
                  AND project.member_id = " . $member_id . "
                ORDER BY plings.create_time DESC
                ";

        if (null != $limit) {
            $sql .= $this->_db->quoteInto(" limit ?", $limit, 'INTEGER');
        }

        $result = $this->_db->fetchAll($sql);
        return $this->generateRowSet($result);
    }


    /*
        public function fetchEarnings($projectIds, $limit = null)
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
                    WHERE plings.status_id = 2
                      AND project.status = " . Default_Model_Project::PROJECT_ACTIVE . "
                      AND project.type_id = 1
                      AND plings.project_id IN (" . implode(",", $projectIds) . ")
                    ORDER BY plings.create_time DESC
                    ";

            if (null != $limit) {
                $sql .= $this->_db->quoteInto(" limit ?", $limit, 'INTEGER');
            }

            $result = $this->_db->fetchAll($sql);
            return $this->generateRowSet($result);
        }
    */

    /**
     * Finds an active user by given username or email ($identity)
     *
     * @param string $identity could be the username or users mail address
     * @param bool $withLoginLocal
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function findActiveMemberByIdentity($identity, $withLoginLocal = false)
    {
        $select = $this->select()
            ->where('username = ? or mail = ?', $identity)
            ->where('is_active = ?', self::MEMBER_ACTIVE)
            ->where('is_deleted = ?', self::MEMBER_NOT_DELETED);
        if ($withLoginLocal) {
            $select->where('login_method = ?', self::MEMBER_LOGIN_LOCAL);
        }

        return $this->fetchRow($select);
    }

    /**
     * @param Zend_Db_Table_Row_Abstract $memberData
     * @return bool
     */
    public function isHiveUser($memberData)
    {
        if ($memberData->source_id == self::SOURCE_HIVE) {
            return true;
        }
        return false;
    }

    public function fetchActiveHiveUserByUsername($username)
    {
        $sql = 'select * from member where username = :username and is_active = 1 and member.source_id = 1 and member.is_deleted = 0';

        $result = $this->getAdapter()->query($sql, array('username' => $username))->fetch();

        return $result;
    }

    /**
     * @param $member_id
     * @param null $limit
     * @return Zend_Paginator
     */
    public function fetchComments($member_id, $limit = null)
    {
        $sql = '
            SELECT
                comment_id
                ,comment_text
                ,member.member_id
                ,profile_image_url
                ,comment_created_at
                ,username
                ,comment_target_id
                ,title
                ,project_id               
            FROM comments
            STRAIGHT_JOIN member on comments.comment_member_id = member.member_id
            JOIN project ON comments.comment_target_id = project.project_id AND comments.comment_type = 0
            WHERE comments.comment_active = :comment_status
            AND project.status = :project_status
            And comments.comment_member_id = :member_id
            ORDER BY comments.comment_created_at DESC
        ';

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }
        $result = $this->_db->fetchAll($sql, array(
            'member_id' => $member_id,
            'project_status' => Default_Model_DbTable_Project::PROJECT_ACTIVE,
            'comment_status' => Default_Model_DbTable_Comments::COMMENT_ACTIVE
        ));

        if (count($result) > 0) {
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
        } else {
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array(array()));
        }
    }

    public function fetchCntSupporters($member_id)
    {
        $sql = '
                SELECT distinct plings.member_id FROM plings
                 JOIN project ON plings.project_id = project.project_id                
                 JOIN member ON project.member_id = member.member_id
                WHERE plings.status_id = 2
                  AND project.status = :project_status
                  AND project.type_id = 1
                  AND project.is_deleted = 0
                  AND project.member_id = :member_id
            ';
        $result = $this->_db->fetchAll($sql,
            array('member_id' => $member_id, 'project_status' => Default_Model_Project::PROJECT_ACTIVE));
        return count($result);
    }

    public function fetchLastActiveTime($member_id)
    {
        $sql = '
                  select max(lastactive) lastactive from
                  (
                      SELECT max(created_at) lastactive from stat_page_views where member_id = :member_id
                      union all
                      SELECT max(time) lastactive from activity_log where member_id = :member_id
                  ) lastactiv
                  ';

        $result = $this->_db->fetchRow($sql, array('member_id' => $member_id));
        $lastpageviewdate = null;
        if (count($result) > 0) {
            $lastpageviewdate = $result['lastactive'];
            return $lastpageviewdate;
        } else {
            return null;
        }
    }

    private function setMemberPlingsDeleted($member_id)
    {
        $modelPling = new Default_Model_Pling();
        $modelPling->setAllPlingsForUserDeleted($member_id);
    }

    private function setMemberPlingsActivated($member_id)
    {
        $modelPling = new Default_Model_Pling();
        $modelPling->setAllPlingsForUserActivated($member_id);
    }

}
