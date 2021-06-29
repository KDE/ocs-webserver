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

namespace Application\Model\Service\Interfaces;

use Application\Model\Entity\Member;
use Application\Model\Interfaces\MemberInterface;
use ArrayObject;
use Exception;
use Laminas\Paginator\Paginator;

interface MemberServiceInterface
{
    const CASE_INSENSITIVE = 1;

    /**
     * @return MemberInterface
     */
    public function getAdapter();

    /**
     * @param int    $count
     * @param string $orderBy
     * @param string $dir
     *
     * return Zend_Db_Table_Rowset
     *
     * @return
     */
    public function fetchNewActiveMembers($count = 100, $orderBy = 'created_at', $dir = 'DESC');

    /**
     * @return array
     * @deprecated
     */
    public function getMembersForSelectList();

    /**
     * @param int $member_id
     *
     * @param     $verification_value
     *
     * @return boolean returns true if successful
     */
    public function activateMemberFromVerification($member_id, $verification_value);

    /**
     * @param int $member_id
     *
     * @throws Exception
     */
    public function setDeleted($member_id);

    /**
     * @param $member_id
     *
     * @return bool
     */
    public function isAllowedForDeletion($member_id);

    /**
     * @param int $member_id
     *
     * @throws Exception
     */
    public function setActivated($member_id);

    /**
     * @param int  $member_id
     *
     * @param bool $onlyNotDeleted
     *
     * return Zend_Db_Table_Row
     *
     * @return |null
     */
    public function fetchMemberData($member_id, $onlyNotDeleted = true);

    /**
     * @param      $member_id
     * @param bool $onlyActive
     *
     * return null|Zend_Db_Table_Row_Abstract
     *
     * @return array|ArrayObject|null
     */
    public function fetchMember($member_id, $onlyActive = true);

    /**
     * @param string $user_name
     *
     * return Zend_Db_Table_Row
     *
     * @return
     */
    public function fetchMemberFromHiveUserName($user_name);

    /**
     * @param string $user_id
     *
     * return Zend_Db_Table_Row
     *
     * @return
     */
    public function fetchMemberFromHiveUserId($user_id);

    /**
     * @param int $member_id
     * @param int $limit
     *
     * return Zend_Db_Table_Rowset
     *
     * @return
     */
    public function fetchFollowedMembers($member_id, $limit = null);

    /**
     * @param int  $member_id
     * @param null $limit
     *
     * return Zend_Db_Table_Rowset_Abstract
     *
     * @return
     */
    public function fetchFollowedProjects($member_id, $limit = null);

    /**
     * @param      $member_id
     * @param null $limit
     *
     * @return mixed
     */
    public function fetchPlingedProjects($member_id, $limit = null);

    /**
     * @param      $member_id
     * @param null $limit
     *
     * @return mixed
     */
    public function fetchProjectsSupported($member_id, $limit = null);

    /**
     * @param $userData
     *
     * @return array
     * @throws Exception
     */
    public function createNewUser($userData);

    /**
     * @param array $userData
     *
     * @return array|ArrayObject|null
     */
    public function storeNewUser($userData);

    /**
     * @return mixed
     */
    public function fetchTotalMembersCount();

    /**
     * @return mixed
     */
    public function fetchTotalMembersInStoreCount();

    /**
     * @param string $email
     *
     * return null|Zend_Db_Table_Row_Abstract
     *
     * @return
     * @deprecated
     */
    public function fetchCheckedActiveLocalMemberByEmail($email);

    /**
     * @param      $member_id
     * @param null $limit
     *
     * @return mixed
     */
    public function fetchEarnings($member_id, $limit = null);

    /**
     * Finds an active user by given username or email ($identity)
     * Returns an empty rowset when no user found.
     *
     * @param string $identity could be the username or users mail address
     * @param bool   $withLoginLocal
     *
     * return Zend_Db_Table_Row_Abstract
     *
     * @return Member
     */
    public function findActiveMemberByIdentity($identity, $withLoginLocal = false);

    /**
     * @param string $username
     *
     * @return mixed
     */
    public function findActiveMemberByName($username);

    /**
     * @param string $hash
     * @param bool   $only_active
     *
     * @return array | false
     */
    public function findMemberForMailHash($hash, $only_active = true);

    /**
     * @param $memberData
     *
     * @return bool
     */
    public function isHiveUser($memberData);

    /**
     * @param $username
     *
     * @return mixed
     */
    public function fetchActiveHiveUserByUsername($username);

    /**
     * @param $username
     *
     * @return int|null
     */
    public function fetchActiveUserByUsername($username);

    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function fetchCommentsCount($member_id);

    /**
     * @param      $member_id
     * @param null $limit
     *
     * @return Paginator
     * @throws Exception
     */
    public function fetchComments($member_id, $limit = null);

    /**
     * @param      $member_id
     * @param null $limit
     *
     * @return array
     */
    public function fetchCommentsList($member_id, $limit = null);

    /**
     * @param $member_id
     *
     * @return int
     */
    public function fetchCntSupporters($member_id);

    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function fetchSupporterDonationInfo($member_id);

    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function fetchSupporterSubscriptionInfo($member_id);

    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function fetchSupporterSectionNr($member_id);

    /**
     * @param $member_id
     *
     * @return int
     */
    public function fetchSupportersActiveYears($member_id);
    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function fetchSupporterSectionInfo($member_id);

    /**
     * @param $member_id
     *
     * @return int|null
     */
    public function fetchLastActiveTime($member_id);

    /**
     * @param int $member_id
     *
     * @return array
     */
    public function fetchContributedProjectsByCat($member_id);

    /**
     * @param int  $member_id
     * @param null $limit
     *
     * return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchSupportedProjects($member_id, $limit = null);

    /**
     * @param string $value
     * @param int    $test_case_sensitive
     * @param array  $omitMember
     * @param bool   $onlyActive
     *
     * @return array return an array of rows
     */
    public function findUsername(
        $value,
        $test_case_sensitive = self::CASE_INSENSITIVE,
        $omitMember = array(),
        $onlyActive = false
    );

    /**
     * @param string $login
     *
     * @return int
     */
    public function generateUniqueUsername($login);

    /**
     * @param int    $member_id
     * @param string $email
     *
     * @return bool
     */
    public function setActive($member_id, $email);

    /**
     * @param string $identity
     *
     * @return Object
     */
    public function findActiveMemberByMail($identity);

    /**
     * @param string $orderby
     * @param null   $limit
     * @param null   $offset
     *
     * @return mixed
     */
    public function getMembersAvatarOldAutogenerated($orderby = 'member_id desc', $limit = null, $offset = null);

    /**
     * @return mixed
     */
    public function getMembersAvatarOldAutogeneratedTotalCount();

    /**
     * @param $member_id
     * @param $type_id
     */
    public function updateAvatarTypeId($member_id, $type_id);
}