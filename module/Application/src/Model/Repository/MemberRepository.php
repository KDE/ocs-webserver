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

namespace Application\Model\Repository;

use Application\Model\Entity\Member;
use Application\Model\Interfaces\MemberInterface;
use Exception;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;

class MemberRepository extends BaseRepository implements MemberInterface
{
    const CASE_INSENSITIVE = 1;

    const MEMBER_ACTIVE = 1;
    const MEMBER_INACTIVE = 0;
    const MEMBER_DELETED = 1;
    const MEMBER_NOT_DELETED = 0;
    const MEMBER_LOGIN_LOCAL = 'local';
    const MEMBER_LOGIN_FACEBOOK = 'facebook';
    const MEMBER_LOGIN_TWITTER = 'twitter';
    const MEMBER_MAIL_CHECKED = 1;
    const MEMBER_NOT_MAIL_CHECKED = 0;
    const MEMBER_DEFAULT_AVATAR = 'default-profile.png';
    const MEMBER_DEFAULT_PROFILE_IMAGE = '/images/system/default-profile.png';
    const MEMBER_TYPE_GROUP = 1;
    const MEMBER_TYPE_PERSON = 0;
    const ROLE_ID_MODERATOR = 400;
    const ROLE_ID_DEFAULT = 300;
    const ROLE_ID_STAFF = 200;
    const ROLE_ID_ADMIN = 100;
    const PROFILE_IMG_SRC_LOCAL = 'local';
    const SOURCE_LOCAL = 0;
    const SOURCE_HIVE = 1;
    const PASSWORD_TYPE_OCS = 0;
    const PASSWORD_TYPE_HIVE = 1;
    const MEMBER_AVATAR_TYPE_USERUPDATED = 2;

    const USER_DEACTIVATED = '_double';
    const EMAIL_DEACTIVATED = '_double';

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "member";
        $this->_key = "member_id";
        $this->_prototype = Member::class;
    }

    public function deleteReal($id)
    {
        throw new Exception('Deleting of users is not allowed.');
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

        $member = $this->fetchRow($sql, array('email_hash' => $hash));

        if (empty($member)) {
            return false;
        }

        return $member;
    }

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
    ) {
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

        return $this->fetchAll($sql, array('username' => $value));
    }

    /**
     * @param integer $member_id
     * @param bool    $onlyNotDeleted
     *
     * @return array
     */
    public function fetchMemberData($member_id, $onlyNotDeleted = true)
    {
        if (null === $member_id) {
            return array();
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
            $sql .= " AND (m.is_deleted = " . MemberRepository::MEMBER_NOT_DELETED . ")";
        }

        return $this->fetchRow($sql, array('memberId' => $member_id), true);
    }

    public function findOneByToken($token)
    {
        $resultSet = new ResultSet();
        $sql = "
            SELECT `m`.*, `member_email`.`email_verification_value`, `member_email`.`email_checked`, `member_email`.`email_address`,`mei`.`external_id` 
            FROM `member_email`
            JOIN `member` AS `m` ON `m`.`member_id` = `member_email`.`email_member_id`
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE `member_email`.`email_deleted` = 0 AND `member_email`.`email_verification_value` = :verification AND `m`.`is_deleted` = 0
        ";
        $params = array('verification' => $token);

        $statement = $this->db->driver->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute($params);

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet->initialize($result);
        }

        return $resultSet;
    }

}