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

namespace Application\Model\Repository;

use Application\Model\Entity\CurrentUser;
use Application\Model\Interfaces\AuthenticationRepositoryInterface;
use Application\Model\Interfaces\MemberInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Hydrator\Reflection as ReflectionHydrator;
use Library\Tools\PasswordEncrypt;

class AuthenticationRepository extends MemberRepository implements MemberInterface, AuthenticationRepositoryInterface
{

    public function __construct(AdapterInterface $db)
    {
        parent::__construct($db);
        $this->_name = "member";
        $this->_key = "member_id";
        $this->_prototype = CurrentUser::class;
    }

    /**
     * @param $email
     *
     * @return HydratingResultSet
     */
    public function findAllByEmail($email)
    {
        $resultSet = false;
        $sql = "
            SELECT `m`.*, `member_email`.`email_verification_value`, `member_email`.`email_checked`, `mei`.`external_id` 
            FROM `member` AS `m`
            JOIN `member_email` ON `m`.`member_id` = `member_email`.`email_member_id` AND `member_email`.`email_primary` = 1 AND `member_email`.`email_deleted` = 0
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE
            `m`.`is_deleted` = 0 AND 
            `m`.`login_method` = 'local' 
              AND (LOWER(`m`.`mail`) = LOWER(:mail) OR LOWER(`m`.`mail`) = CONCAT(LOWER(:mail),'_double'))
        ";
        $params = array('mail' => $email);

        $statement = $this->db->driver->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute($params);

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new HydratingResultSet(new ReflectionHydrator(), new CurrentUser);
            $resultSet->initialize($result);
        }

        return $resultSet;
    }

    public function findAllByUsername($username)
    {
        $resultSet = false;
        $sql = "
            SELECT `m`.*, `member_email`.`email_verification_value`, `member_email`.`email_checked`, `mei`.`external_id` 
            FROM `member` AS `m`
            JOIN `member_email` ON `m`.`member_id` = `member_email`.`email_member_id` AND `member_email`.`email_primary` = 1 AND `member_email`.`email_deleted` = 0
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE
            `m`.`is_deleted` = 0 AND 
            `m`.`login_method` = 'local' 
              AND (LOWER(`m`.`username`) = LOWER(:username) OR LOWER(`m`.`username`) = CONCAT(LOWER(:username),'_double'))
        ";
        $params = array('username' => $username);

        $statement = $this->db->driver->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute($params);

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new HydratingResultSet(new ReflectionHydrator(), new CurrentUser);
            $resultSet->initialize($result);
        }

        return $resultSet;
    }

    public function findOneById($member_id)
    {
        $sql = "
            SELECT `m`.*, `member_email`.`email_verification_value`, `member_email`.`email_checked`, `mei`.`external_id`, `mr`.`shortname` AS `roleName`
            FROM `member` AS `m`
            JOIN `member_email` ON `m`.`member_id` = `member_email`.`email_member_id` AND `member_email`.`email_primary` = 1 AND `member_email`.`email_deleted` = 0
            JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            JOIN `member_role` AS `mr` ON `mr`.`member_role_id` = `m`.`roleId`
            WHERE  
            `m`.`login_method` = 'local' 
              AND (`m`.`member_id` = :member_id)
        ";
        $params = array('member_id' => $member_id);

        $statement = $this->db->driver->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute($params);

        $user = new CurrentUser();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new HydratingResultSet(new ReflectionHydrator(), new CurrentUser);
            $user = $resultSet->initialize($result)->current();
        }

        $projects = $this->findAllProjects($user->member_id);

        if ($projects->count() > 0) {
            $user->projects = $this->generateArrayWithKeyProjectId($projects->toArray());
        }

        $user->isSupporter = $this->isSupporter($user->member_id);

        return $user;
    }

    /**
     * @param int member_id
     *
     * @return ResultSet
     */
    private function findAllProjects($member_id)
    {
        $resultSet = new ResultSet();
        $sql = "
                SELECT `p`.`project_id`
                FROM `project` AS `p`
                WHERE `p`.`member_id` = :memberId;
        ";
        $params = array('memberId' => $member_id);

        $statement = $this->db->driver->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute($params);

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet->initialize($result);
        }

        return $resultSet;
    }

    /**
     * @param array $inputArray
     *
     * @return array
     */
    private function generateArrayWithKeyProjectId($inputArray)
    {
        $arrayWithKeyProjectId = array();
        foreach ($inputArray as $element) {
            $arrayWithKeyProjectId[$element['project_id']] = $element;
        }

        return $arrayWithKeyProjectId;
    }

    private function isSupporter($member_id)
    {
        $resultSet = new ResultSet();
        $sql = "SELECT count(DISTINCT `c`.`name`) `sections` FROM 
                `section_support` `s`, `support` `t` , `section` `c`
                WHERE `s`.`support_id` = `t`.`id` AND `s`.`section_id` = `c`.`section_id`
                AND  `t`.`member_id` = :member_id AND `t`.`status_id`>=2
                AND `s`.`is_active` = 1
            ";
        $params = array('member_id' => $member_id);

        $statement = $this->db->driver->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute($params);

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet->initialize($result);
        }

        return $resultSet->count() > 0;
    }

    public function findOneByEmail($email)
    {
        $sql = "
            SELECT `m`.*, `mei`.`external_id`, `mr`.`shortname` AS `roleName`
            FROM `member` AS `m`
            JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            JOIN `member_role` AS `mr` ON `mr`.`member_role_id` = `m`.`roleId`
            WHERE  
            `m`.`login_method` = 'local' 
              AND (LOWER(`m`.`mail`) = LOWER(:mail))
        ";
        $params = array('mail' => $email);

        $statement = $this->db->driver->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute($params);

        $user = new CurrentUser();
        $user->projects = array();
        $user->isSupporter = false;

        if ($result instanceof ResultInterface && $result->isQueryResult() && $result->count() > 0) {
            $resultSet = new HydratingResultSet(new ReflectionHydrator(), new CurrentUser);
            $user = $resultSet->initialize($result)->current();
            $projects = $this->findAllProjects($user->member_id);
            if ($projects->count() > 0) {
                $user->projects = $this->generateArrayWithKeyProjectId($projects->toArray());
            }

            $user->isSupporter = $this->isSupporter($user->member_id);
        }

        return $user;
    }

    public function changePasswordType(CurrentUser $user, $password)
    {
        $user = array(
            // user id
            'member_id'         => $user->member_id,

            //Save old data
            'password_old'      => $user->password,
            'password_type_old' => $user->password_type,

            //Change type and password
            'password_type'     => self::PASSWORD_TYPE_OCS,
            'password'          => PasswordEncrypt::get($password, self::PASSWORD_TYPE_OCS),
        );

        $result = $this->insertOrUpdate($user);

        return $result == 1;
    }

}