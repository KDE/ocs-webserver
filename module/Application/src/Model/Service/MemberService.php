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

namespace Application\Model\Service;

use Application\Model\Entity\Member;
use Application\Model\Interfaces\ImageInterface;
use Application\Model\Interfaces\MemberEmailInterface;
use Application\Model\Interfaces\MemberExternalIdInterface;
use Application\Model\Interfaces\MemberInterface;
use Application\Model\Interfaces\ProjectInterface;
use Application\Model\Interfaces\ProjectRatingInterface;
use Application\Model\Interfaces\ReportCommentsInterface;
use Application\Model\Interfaces\ReportProductsInterface;
use Application\Model\Repository\CommentsRepository;
use Application\Model\Repository\MemberRepository;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Service\Interfaces\MemberDeactivationLogServiceInterface;
use Application\Model\Service\Interfaces\MemberEmailServiceInterface;
use Application\Model\Service\Interfaces\MemberServiceInterface;
use Application\Model\Service\Interfaces\ProjectCommentsServiceInterface;
use Application\Model\Service\Interfaces\ProjectServiceInterface;
use Application\Model\Service\Ocs\ServerManager;
use ArrayObject;
use Exception;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Expression;
use Laminas\Hydrator\Reflection as ReflectionHydrator;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Library\Tools\Identicon;
use Library\Tools\PasswordEncrypt;
use Library\Tools\Uuid;
use stdClass;
use YoHang88\LetterAvatar\LetterAvatar;

class MemberService extends BaseService implements MemberServiceInterface
{
    const ROLE_ID_DEFAULT = 300;
    /**
     * @var MemberInterface
     */
    private $memberRepository;
    /**
     * @var ProjectRatingInterface
     */
    private $projectRatingRepository;
    /**
     * @var ReportProductsInterface
     */
    private $reportProductsRepository;
    /**
     * @var ReportCommentsInterface
     */
    private $reportCommentsRepository;
    /**
     * @var MemberEmailInterface
     */
    private $memberEmailRepository;
    /**
     * @var ImageInterface
     */
    private $imageRepository;
    /**
     * @var MemberExternalIdInterface
     */
    private $memberExternalIdRepository;
    /**
     * @var ProjectServiceInterface
     */
    private $projectService;
    /**
     * @var ProjectInterface
     */
    private $projectRepository;
    /**
     * @var MemberDeactivationLogServiceInterface
     */
    private $memberDeactivationLogService;
    /**
     * @var ProjectCommentsServiceInterface
     */
    private $projectCommentsService;
    /** @var MemberEmailService */
    private $memberEmailService;
    /** @var ServerManager */
    private $ocs_manager;
    private $cache;
    private $log;

    /**
     * MemberService constructor.
     *
     * @param MemberInterface                       $m
     * @param StorageInterface                      $storage
     * @param ProjectRatingInterface                $projectRatingRepository
     * @param ReportProductsInterface               $reportProductsRepository
     * @param ReportCommentsInterface               $reportCommentsRepository
     * @param MemberEmailInterface                  $memberEmailRepository
     * @param ImageInterface                        $imageRepository
     * @param ProjectServiceInterface               $projectService
     * @param ProjectInterface                      $projectRepository
     * @param MemberDeactivationLogServiceInterface $memberDeactivationLogService
     * @param ProjectCommentsServiceInterface       $projectCommentsService
     * @param MemberExternalIdInterface             $memberExternalIdRepository
     * @param MemberEmailServiceInterface           $memberEmailService
     * @param ServerManager                         $serverManager
     */
    public function __construct(
        MemberInterface $m,
        StorageInterface $storage,
        ProjectRatingInterface $projectRatingRepository,
        ReportProductsInterface $reportProductsRepository,
        ReportCommentsInterface $reportCommentsRepository,
        MemberEmailInterface $memberEmailRepository,
        ImageInterface $imageRepository,
        ProjectServiceInterface $projectService,
        ProjectInterface $projectRepository,
        MemberDeactivationLogServiceInterface $memberDeactivationLogService,
        ProjectCommentsServiceInterface $projectCommentsService,
        MemberExternalIdInterface $memberExternalIdRepository,
        MemberEmailServiceInterface $memberEmailService,
        ServerManager $serverManager
    ) {
        $this->memberRepository = $m;
        $this->cache = $storage;
        $this->projectRatingRepository = $projectRatingRepository;
        $this->reportProductsRepository = $reportProductsRepository;
        $this->reportCommentsRepository = $reportCommentsRepository;
        $this->memberEmailRepository = $memberEmailRepository;
        $this->imageRepository = $imageRepository;
        $this->projectService = $projectService;
        $this->projectRepository = $projectRepository;
        $this->log = $GLOBALS['ocs_log'];
        $this->memberDeactivationLogService = $memberDeactivationLogService;
        $this->projectCommentsService = $projectCommentsService;
        $this->memberExternalIdRepository = $memberExternalIdRepository;
        $this->memberEmailService = $memberEmailService;
        $this->ocs_manager = $serverManager;
    }

    /**
     * @param array $authMember
     *
     * @return array
     */
    public static function cleanAuthMemberForJson(array $authMember)
    {
        if (empty($authMember)) {
            return $authMember;
        }

        $unwantedKeys = array(
            'mail'              => 0,
            'firstname'         => 0,
            'lastname'          => 0,
            'street'            => 0,
            'zip'               => 0,
            'phone'             => 0,
            'paypal_mail'       => 0,
            'gravatar_email'    => 0,
            'source_pk'         => 0,
            'source_id'         => 0,
            'password_old'      => 0,
            'password_type_old' => 0,
            'username_old'      => 0,
            'mail_old'          => 0,
        );

        $authMember = array_diff_key($authMember, $unwantedKeys);

        return $authMember;
    }

    /**
     * @return MemberEmailService
     */
    public function getMemberEmailService()
    {
        return $this->memberEmailService;
    }

    /**
     * @param int    $count
     * @param string $orderBy
     * @param string $dir
     *
     * return Zend_Db_Table_Rowset
     *
     * @return mixed
     */
    public function fetchNewActiveMembers($count = 100, $orderBy = 'created_at', $dir = 'DESC')
    {
        $allowedDirection = array('desc' => true, 'asc' => true);
        if (false == isset($allowedDirection[strtolower($dir)])) {
            $dir = null;
        }

        $cache = $this->cache;
        $cacheName = __FUNCTION__ . md5($count . $orderBy . $dir);
        $members = $cache->getItem($cacheName);

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

            $resultCnt = $this->getAdapter()->fetchRow(
                $sql, array(
                        'activeVal'     => MemberRepository::MEMBER_ACTIVE,
                        'typeVal'       => MemberRepository::MEMBER_TYPE_PERSON,
                        'defaultImgUrl' => 'hive/user-pics/nopic.png',
                        'likeImgUrl'    => 'hive/user-bigpics/0/%',
                    )
            );

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
            //$sql .= ' ORDER BY ' . $this->getAdapter()->quoteIdentifier($orderBy) . ' ' . $dir;

            $sql .= ' LIMIT ' . $count;
            $sql .= $offset;

            $resultMembers = $this->getAdapter()->fetchAll(
                $sql, array(
                        'activeVal'     => MemberRepository::MEMBER_ACTIVE,
                        'typeVal'       => MemberRepository::MEMBER_TYPE_PERSON,
                        'defaultImgUrl' => 'hive/user-pics/nopic.png',
                        'likeImgUrl'    => 'hive/user-bigpics/0/%',
                    )
            );
            $resultSet = $this->getAdapter()->arrayToObject($resultMembers);
            $cache->setItem($cacheName, $resultSet);

            return $resultSet;
        }
    }

    /**
     * @return MemberInterface
     */
    public function getAdapter()
    {
        return $this->memberRepository;
    }

    /**
     * @return array
     * @deprecated
     */
    public function getMembersForSelectList()
    {
        return null;
    }

    /**
     * @param int $member_id
     *
     * @param     $verification_value
     *
     * @return boolean returns true if successful
     */
    public function activateMemberFromVerification($member_id, $verification_value)
    {
        $sql = "
            UPDATE `member`
              STRAIGHT_JOIN `member_email` ON `member`.`member_id` = `member_email`.`email_member_id` AND `member_email`.`email_checked` IS NULL AND `member`.`is_deleted` = 0 AND `member_email`.`email_deleted` = 0
            SET `member`.`mail_checked` = 1, `member`.`is_active` = 1, `member`.`changed_at` = NOW(), `member_email`.`email_checked` = NOW()
            WHERE `member`.`member_id` = :memberId AND `member_email`.`email_verification_value` = :verificationValue;
        ";
        $stmnt = $this->getAdapter()->query(
            $sql, array(
                    'memberId'          => $member_id,
                    'verificationValue' => $verification_value,
                )
        );

        return $stmnt->getAffectedRows() > 0;
    }

    //User ist mind. 1 jahr alt, user ist supporter, user hat minimum 20 kommentare    

    /**
     * @param int $member_id
     *
     * @throws Exception
     */
    public function setDeleted($member_id)
    {
        $updateValues = array(
            'is_active'  => 0,
            'is_deleted' => 1,
            'deleted_at' => new Expression('Now()'),
        );
        $this->getAdapter()->update($updateValues, ['member_id' => $member_id]);


        $this->memberDeactivationLogService->logMemberAsDeleted($member_id);

        $this->setMemberProjectsDeleted($member_id);
        $this->setMemberCommentsDeleted($member_id);
        $this->setMemberRatingsDeleted($member_id);
        $this->setMemberReportingsDeleted($member_id);
        $this->setMemberEmailsDeleted($member_id);
        $this->setDeletedInMaterializedView($member_id);
        $this->setDeletedInSubSystems($member_id);
    }

    /**
     * @param $member_id
     *
     * @throws Exception
     */
    private function setMemberProjectsDeleted($member_id)
    {
        $this->projectService->setAllProjectsForMemberDeleted($member_id);
    }

    /**
     * @param $member_id
     */
    private function setMemberCommentsDeleted($member_id)
    {
        $this->projectCommentsService->setAllCommentsForUserDeleted($member_id);
    }

    /**
     * @param $member_id
     */
    private function setMemberRatingsDeleted($member_id)
    {
        $this->projectRatingRepository->setDeletedByMemberId($member_id);
    }

    /**
     * @param $member_id
     */
    private function setMemberReportingsDeleted($member_id)
    {
        $this->reportProductsRepository->setDeleteByMember($member_id);
        $this->reportCommentsRepository->setDeleteByMember($member_id);
    }

    /**
     * @param $member_id
     */
    private function setMemberEmailsDeleted($member_id)
    {
        $this->memberEmailRepository->setDeletedByMember($member_id);
    }

    /**
     * @param $member_id
     */
    private function setDeletedInMaterializedView($member_id)
    {
        $sql = "UPDATE `stat_projects` SET `status` = :new_status WHERE `member_id` = :member_id";
        $this->getAdapter()->query(
            $sql, array(
                    'new_status' => ProjectRepository::PROJECT_DELETED,
                    'member_id'  => $member_id,
                )
        );
    }

    /**
     * @param $member_id
     *
     */
    private function setDeletedInSubSystems($member_id)
    {
        $this->ocs_manager->delete($member_id);
    }

    /**
     * @param $member_id
     *
     * @return bool
     */
    public function isAllowedForDeletion($member_id)
    {
        // a member should not deleted when
        // - the account is older than 12 month
        // - the member made more than 20 comments
        // - the member is a supporter through the last 12 month
        $sql = 'SELECT 
              `m`.`created_at`
              ,(`m`.`created_at`+ INTERVAL 12 MONTH < NOW()) AS `is_old`
              ,(SELECT count(1) FROM `comments` `c` WHERE `c`.`comment_member_id` = `m`.`member_id` AND `comment_active` = 1) AS `comments`
              ,(SELECT (DATE_ADD(max(`active_time`), INTERVAL 1 YEAR) > now()) FROM `support` `s`  WHERE `s`.`status_id` = 2  AND `s`.`member_id` =`m`.`member_id`) AS `is_supporter`
              FROM `member` `m` WHERE `member_id` = :member_id';
        $result = $this->getAdapter()->fetchRow(
            $sql, array(
                    'member_id' => $member_id,
                )
        );
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
     * @throws Exception
     */
    public function setActivated($member_id)
    {
        $updateValues = array(
            'is_active'  => 1,
            'is_deleted' => 0,
            'changed_at' => new Expression('Now()'),
            'deleted_at' => null,
        );

        $this->getAdapter()->update($updateValues, ['member_id' => $member_id]);

        $this->memberDeactivationLogService->removeLogMemberAsDeleted($member_id);

        $this->setMemberProjectsActivated($member_id);
        $this->setMemberCommentsActivated($member_id);
        $this->setMemberEmailsActivated($member_id);

        $this->setActivatedInSubsystems($member_id);

        //$this->setMemberPlingsActivated($member_id);
    }

    /**
     * @param $member_id
     *
     * @throws Exception
     */
    private function setMemberProjectsActivated($member_id)
    {
        $this->projectService->setAllProjectsForMemberActivated($member_id);
    }

    /**
     * @param $member_id
     */
    private function setMemberCommentsActivated($member_id)
    {
        $this->projectCommentsService->setAllCommentsForUserActivated($member_id);
    }

    /**
     * @param $member_id
     */
    private function setMemberEmailsActivated($member_id)
    {
        $this->memberEmailRepository->setActivatedByMember($member_id);
    }

    /**
     * @param $member_id
     *
     */
    private function setActivatedInSubsystems($member_id)
    {
        $this->ocs_manager->activate($member_id);
    }

    /**
     * @param int  $member_id
     *
     * @param bool $onlyNotDeleted
     *
     * return Zend_Db_Table_Row
     *
     * @return ArrayObject|null
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
            $sql .= " AND (m.is_deleted = " . MemberRepository::MEMBER_NOT_DELETED . ")";
        }

        /** @var ArrayObject $result */
        $result = $this->getAdapter()->fetchRow($sql, array('memberId' => $member_id), false);

        return $result;
    }

    /**
     * @param      $member_id
     * @param bool $onlyActive
     *
     * return null|Zend_Db_Table_Row_Abstract
     *
     * @return array|ArrayObject|null
     */
    public function fetchMember($member_id, $onlyActive = true)
    {
        if (empty($member_id)) {
            return null;
        }

        $sql = "
                SELECT `m`.*, `member_email`.`email_address` AS `mail`, IF(ISNULL(`member_email`.`email_checked`),0,1) AS `mail_checked`, `member_email`.`email_address`, `member_email`.`email_verification_value`, `mei`.`external_id`, `mei`.`gitlab_user_id`
                FROM `member` AS `m`
                JOIN `member_email` ON `m`.`member_id` = `member_email`.`email_member_id` AND `member_email`.`email_primary` = 1
                LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
                WHERE `m`.`member_id` = :memberId";

        if ($onlyActive) {
            $sql .= " AND `m`.`is_deleted` = " . MemberRepository::MEMBER_NOT_DELETED . " AND `m`.`is_active` = " . MemberRepository::MEMBER_ACTIVE;
        }

        return $this->getAdapter()->fetchRow($sql, array('memberId' => $member_id), false);
    }

    /**
     * @param string $user_name
     *
     * return Zend_Db_Table_Row
     *
     * @return array|ArrayObject|null
     */
    public function fetchMemberFromHiveUserName($user_name)
    {
        $sql = "
                SELECT *
                FROM `member`
        		WHERE `source_id` = :sourceId
                  AND `username` = :userName
                ";

        return $this->getAdapter()->fetchRow(
            $sql, array('sourceId' => MemberRepository::SOURCE_HIVE, 'userName' => $user_name), false
        );
    }

    /**
     * @param string $user_id
     *
     * return Zend_Db_Table_Row
     *
     * @return array|ArrayObject|null
     */
    public function fetchMemberFromHiveUserId($user_id)
    {
        $sql = "
                SELECT *
                FROM `member`
        	WHERE `source_id` = :sourceId
                AND `source_pk` = :userId
                ";

        return $this->getAdapter()->fetchRow(
            $sql, array(
            'sourceId' => MemberRepository::SOURCE_HIVE,
            'userId'   => $user_id,
        ), false
        );
    }

    /**
     * @param int $member_id
     * @param int $limit
     *
     * @return stdClass|null
     */
    public function fetchFollowedMembers($member_id, $limit = null)
    {
        $sql = "
                SELECT `member_follower`.`member_id`,
                       `member_follower`.`follower_id`,
                       `member`.*
                FROM `member_follower`
                LEFT JOIN `member` ON `member_follower`.`member_id` = `member`.`member_id`
        		WHERE `member_follower`.`follower_id` = :followerId
                  AND `member`.`is_active` = :activeVal
                GROUP BY `member_follower`.`member_id`
                ORDER BY max(`member_follower`.`member_follower_id`) DESC
                ";

        if (null != $limit) {
            $sql .= " limit " . $limit;
        }

        $result = $this->getAdapter()->fetchAll(
            $sql, array('followerId' => $member_id, 'activeVal' => MemberRepository::MEMBER_ACTIVE)
        );

        return $this->getAdapter()->arrayToObject($result);
    }

    /**
     * @param int  $member_id
     * @param null $limit
     *
     * @return stdClass|null
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
            $sql .= " limit " . $limit;
        }

        $result = $this->getAdapter()->fetchAll(
            $sql, array('member_id' => $member_id, 'project_status' => ProjectRepository::PROJECT_ACTIVE)
        );

        return $this->getAdapter()->arrayToObject($result);
    }

    /**
     * @param      $member_id
     * @param null $limit
     *
     * @return mixed
     */
    public function fetchPlingedProjects($member_id, $limit = null)
    {
        $sql = "
                SELECT `project_category`.`title` AS `catTitle`,
                       `project`.*,
        			   `member`.*,
    				   `plings`.*
                FROM `plings`
                LEFT JOIN `project` ON `plings`.`project_id` = `project`.`project_id`
                LEFT JOIN `project_category` ON `project`.`project_category_id` = `project_category`.`project_category_id`
        		LEFT JOIN `member` ON `project`.`member_id` = `member`.`member_id`
        		WHERE `plings`.`member_id` = :member_id
    			  AND `plings`.`status_id` = 2
                  AND `project`.`status` = :project_status
                  AND `project`.`type_id` = 1
                ORDER BY `plings`.`create_time` DESC
                ";
        if (null != $limit) {
            $sql .= " limit " . $limit;
        }

        $result = $this->getAdapter()->fetchAll(
            $sql, array('member_id' => $member_id, 'project_status' => ProjectRepository::PROJECT_ACTIVE)
        );

        return $this->getAdapter()->arrayToObject($result);

    }

    /**
     * @param      $member_id
     * @param null $limit
     *
     * @return mixed
     */
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
            $sql .= " limit " . $limit;
        }


        $result = $this->getAdapter()->fetchAll(
            $sql, array('member_id' => $member_id, 'project_status' => ProjectRepository::PROJECT_ACTIVE)
        );

        return $this->getAdapter()->arrayToObject($result);
    }

    /**
     * @param $userData
     *
     * @return array
     * @throws Exception
     */
    public function createNewUser($userData)
    {
        $uuidMember = Uuid::generateUUID();

        if (false == isset($userData['password'])) {
            throw new Exception(__METHOD__ . ' - user password is not set.');
        }
        $userData['password'] = PasswordEncrypt::get($userData['password'], PasswordEncrypt::PASSWORD_TYPE_OCS);
        if (false == isset($userData['roleId'])) {
            $userData['roleId'] = self::ROLE_ID_DEFAULT;
        }
        if ((false == isset($userData['avatar'])) or (false == isset($userData['profile_image_url']))) {
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

        //email is always lower case
        $userData['mail'] = strtolower(trim($userData['mail']));

        $newUser = $this->storeNewUser($userData);

        $memberMail = $this->createPrimaryMailAddress($newUser);
        $externalId = $this->createExternalId($newUser['member_id']);

        $newUser['verificationVal'] = $memberMail['email_verification_value'];
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
        //require_once 'vendor/autoload.php';
        // $name = substr($userData['username'],0,1).' '.substr($userData['username'],1);
        $name = $userData['username'] . '  ';
        $avatar = new LetterAvatar($name, 'square', 400);
        $tmpImagePath = IMAGES_UPLOAD_PATH . '/tmp/' . $uuidMember . '.png';
        $avatar->saveAs($tmpImagePath, LetterAvatar::MIME_TYPE_PNG);

        return $this->imageRepository->saveImageOnMediaServer($tmpImagePath);
    }

    /**
     * @param array $userData
     *
     * @return array
     * @throws Exception
     */
    public function storeNewUser($userData)
    {
        $member_id = $this->getAdapter()->insert($userData);
        $newUserData = $this->getAdapter()->fetchById($member_id);
        if (!sizeof($newUserData)) {
            throw new Exception('get empty result for ' . $member_id . ' when store new user. ' . json_encode($userData));
        }
        $result = $newUserData->getArrayCopy();
        //create a user specified main project in project table
        $projectId = $this->createPersonalProject($result);
        //and save the id in member table
        $result['main_project_id'] = $projectId;
        $this->getAdapter()->update($result);

        return $result;
    }

    /**
     * @param array $userData
     *
     * @return int
     */
    protected function createPersonalProject($userData)
    {
        $newPersonalProject = array();
        $newPersonalProject['uuid'] = UUID::generateUUID();
        $newPersonalProject['project_category_id'] = ProjectRepository::CATEGORY_DEFAULT_PROJECT;
        $newPersonalProject['status'] = ProjectRepository::STATUS_PROJECT_ACTIVE;
        $newPersonalProject['image_big'] = ProjectRepository::DEFAULT_AVATAR_IMAGE;
        $newPersonalProject['image_small'] = ProjectRepository::DEFAULT_AVATAR_IMAGE;
        $newPersonalProject['creator_id'] = $userData['member_id'];
        $newPersonalProject['title'] = ProjectRepository::PERSONAL_PROJECT_TITLE;

        return $this->projectRepository->insert($newPersonalProject);
    }

    /**
     * @param array $newUser
     *
     * @return array|bool
     */
    private function createPrimaryMailAddress($newUser)
    {
        return $this->memberEmailService->saveEmailAsPrimary(
            $newUser['member_id'], $newUser['mail'], $newUser['mail_checked']
        );
    }

    /**
     * @param int $member_id
     *
     * @return string
     */
    private function createExternalId($member_id)
    {
        return $this->memberExternalIdRepository->createExternalId($member_id);
    }

    /**
     * @return mixed
     */
    public function fetchTotalMembersCount()
    {
        $sql = "
                SELECT
                    count(1) AS `total_member_count`
                FROM
                    `member`
               ";

        $result = $this->getAdapter()->fetchRow($sql);

        return $result['total_member_count'];
    }

    /**
     * @return mixed
     */
    public function fetchTotalMembersInStoreCount()
    {
        $sql = "
                SELECT
                    count(1) AS `total_member_count`
                FROM
                    `member`
               ";

        $result = $this->getAdapter()->fetchRow($sql);

        return $result['total_member_count'];
    }

    /**
     * @param string $email
     *
     * return null|Zend_Db_Table_Row_Abstract
     *
     * @return ResultSet
     * @deprecated
     */
    public function fetchCheckedActiveLocalMemberByEmail($email)
    {
        return $this->getAdapter()->fetchAllRows(
            [
                'mail'         => $email,
                'is_deleted'   => MemberRepository::MEMBER_NOT_DELETED,
                'is_active'    => MemberRepository::MEMBER_ACTIVE,
                'mail_checked' => MemberRepository::MEMBER_MAIL_CHECKED,
                'login_method' => MemberRepository::MEMBER_LOGIN_LOCAL,
            ]
        );
    }

    /**
     * @param      $member_id
     * @param null $limit
     *
     * @return mixed
     */
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
            $sql .= " limit " . $limit;
        }

        $result = $this->getAdapter()->fetchAll(
            $sql, array(
                    'memberId' => $member_id,
                    'status'   => ProjectService::PROJECT_ACTIVE,
                )
        );

        return $this->getAdapter()->arrayToObject($result);
    }

    /**
     * Finds an active user by given username or email ($identity)
     * Returns an empty row set when no user found.
     *
     * @param string $identity could be the username or users mail address
     * @param bool   $withLoginLocal
     *
     * return Zend_Db_Table_Row_Abstract
     *
     * @return Member
     */
    public function findActiveMemberByIdentity($identity, $withLoginLocal = false)
    {
        $sqlName = "SELECT * FROM `member` WHERE `is_active` = :active AND `is_deleted` = :deleted AND `username` = :identity";
        $sqlMail = "SELECT * FROM `member` WHERE `is_active` = :active AND `is_deleted` = :deleted AND `mail` = :identity";
        if ($withLoginLocal) {
            $sqlName .= " AND login_method = '" . MemberRepository::MEMBER_LOGIN_LOCAL . "'";
            $sqlMail .= " AND login_method = '" . MemberRepository::MEMBER_LOGIN_LOCAL . "'";
        }

        // test identity as username
        $resultName = $this->getAdapter()->fetchRow(
            $sqlName, array(
                        'active'   => MemberRepository::MEMBER_ACTIVE,
                        'deleted'  => MemberRepository::MEMBER_NOT_DELETED,
                        'identity' => $identity,
                    )
        );
        if ((false !== $resultName) and (count($resultName) > 0)) {

            return new Member($resultName);
        }

        // test identity as mail
        $resultMail = $this->getAdapter()->fetchRow(
            $sqlMail, array(
                        'active'   => MemberRepository::MEMBER_ACTIVE,
                        'deleted'  => MemberRepository::MEMBER_NOT_DELETED,
                        'identity' => $identity,
                    )
        );
        if ((false !== $resultMail) and (count($resultMail) > 0)) {

            return new Member($resultMail);
        }

        return new Member();
    }

    /**
     * @param string $username
     *
     * @return mixed
     */
    public function findActiveMemberByName($username)
    {
        $param = array('username' => strtolower($username) . '%');
        $sql = '
          SELECT `m`.`member_id`,`m`.`username`,`profile_image_url` 
          FROM `member` `m` 
          WHERE `m`.`is_active`=1 AND `m`.`is_deleted` = 0 AND LOWER(`m`.`username`) LIKE :username
          LIMIT 10
      ';

        return $this->getAdapter()->fetchAll($sql, $param);
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
     * @param $memberData
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
        if ($memberData->password_type == MemberRepository::PASSWORD_TYPE_HIVE) {
            return true;
        }

        return false;
    }

    /**
     * @param $username
     *
     * @return mixed
     */
    public function fetchActiveHiveUserByUsername($username)
    {
        $sql = 'SELECT * FROM `member` WHERE `username` = :username AND `is_active` = 1 AND `member`.`source_id` = 1 AND `member`.`is_deleted` = 0';

        return $this->getAdapter()->query($sql, array('username' => $username));
    }

    /**
     * @param $username
     *
     * @return int|null
     */
    public function fetchActiveUserByUsername($username)
    {
        $sql = 'SELECT `member`.`member_id`
                FROM `member`
                WHERE LOWER(`username`) = :username
                AND `is_active` = 1 
                AND `member`.`is_deleted` = 0
                LIMIT 1
               ';

        $result = $this->getAdapter()->query($sql, array('username' => strtolower($username)))->current();
        if ($result) {
            return $result->member_id;
        }

        return null;
    }

    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function fetchCommentsCount($member_id)
    {
        $sql = "
                  SELECT
                      count(1) AS `count`
                  FROM
                      `comments` 
                  WHERE `comment_target_id` <> 0 AND `comment_member_id` = :member_id AND `comment_active` = :comment_status
                 ";
        $result = $this->getAdapter()->fetchRow(
            $sql, array(
                    'member_id'      => $member_id,
                    'comment_status' => CommentsRepository::COMMENT_ACTIVE,
                )
        );

        return $result['count'];
    }

    /**
     * @param      $member_id
     * @param null $limit
     *
     * @return Paginator
     * @throws Exception
     */
    public function fetchComments($member_id, $limit = null)
    {
        $result = $this->fetchCommentsList($member_id, $limit);

        return new Paginator(new ArrayAdapter($result));
    }

    /**
     * @param      $member_id
     * @param null $limit
     *
     * @return array
     */
    public function fetchCommentsList($member_id, $limit = null)
    {
        $cache_name = __FUNCTION__ . '_' . $member_id . $limit;
        if ($result = $this->readCache($cache_name)) {
            return $result;
        }

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
                ,`stat_projects`.`project_category_id`  
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

        $result = $this->getAdapter()->fetchAll(
            $sql, array(
                    'member_id'      => $member_id,
                    'project_status' => ProjectRepository::PROJECT_ACTIVE,
                    'comment_status' => CommentsRepository::COMMENT_ACTIVE,
                )
        );
        $this->writeCache($cache_name, $result, 600);

        return $result;
    }

    /**
     * @param $member_id
     *
     * @return int
     */
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
        $result = $this->getAdapter()->fetchAll(
            $sql, array('member_id' => $member_id, 'project_status' => ProjectRepository::PROJECT_ACTIVE)
        );

        return count($result);
    }

    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function fetchSupporterDonationInfo($member_id)
    {
        /*$sql = 'SELECT max(active_time) AS active_time_max
                            ,min(active_time)  AS active_time_min
                            ,(DATE_ADD(max(active_time), INTERVAL 1 YEAR) > now()) AS issupporter
                            ,count(1)  AS cnt from support  where status_id = 2 AND type_id = 0 AND member_id = :member_id ';*/
        $sql = "
                SELECT 
                `member_id`,
                max(`valid_till`) AS `active_time_max`,
                min(`active_time_min`) AS `active_time_min`,
                max(`is_valid`) AS `issupporter`,
                count(1) AS `cnt`
                FROM `v_support`
                WHERE `member_id` = :member_id
        ";

        return $this->getAdapter()->fetchRow($sql, array('member_id' => $member_id));
    }

    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function fetchSupporterSubscriptionInfo($member_id)
    {
        $sql = 'SELECT `create_time`,`amount`,`period`,`period_frequency` FROM `support`  WHERE `status_id` = 2 AND `type_id` = 1 
                AND `member_id` = :member_id
                ORDER BY `create_time` DESC
                LIMIT 1';

        return $this->getAdapter()->fetchRow($sql, array('member_id' => $member_id));
    }

    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function fetchSupporterSectionNr($member_id)
    {
        $sql = "SELECT count(DISTINCT `c`.`name`) `sections` FROM 
                `section_support` `s`, `support` `t` , `section` `c`
                WHERE `s`.`support_id` = `t`.`id` AND `s`.`section_id` = `c`.`section_id`
                AND  `t`.`member_id` = :member_id AND `t`.`status_id`>=2
                AND `s`.`is_active` = 1
            ";
        $result = $this->getAdapter()->fetchRow($sql, array('member_id' => $member_id));

        return $result['sections'];

    }

    /**
     *
     * @param int $member_id
     *
     * @return int
     */
    public function fetchSupportersActiveYears($member_id)
    {
        $activesupporters =[];
        $cache = $this->cache;
        $cacheName = __FUNCTION__;
        if ($cache->hasItem($cacheName)) {
            $activesupporters =  $cache->getItem($cacheName);
        }else{            
            $sql = "SELECT COUNT(1) AS active_months, member_id, floor((count(1)+11)/12) as active_years FROM
                    (
                    SELECT `s`.`member_id`, `p`.`yearmonth` , sum(`p`.`tier`) `tier` FROM `section_support_paypements` `p`
                    JOIN `support` `s` ON `s`.`id` = `p`.`support_id`
                    GROUP BY `s`.`member_id`, `p`.`yearmonth`
                    ) `A`
                    group by member_id
                    ";
            $activesupporters =  $this->getAdapter()->fetchAll($sql);
            $cache->setItem($cacheName, $activesupporters);
        }      
        foreach ($activesupporters as $value) {
            if($value['member_id']==$member_id)
            {                
                return $value['active_years'];                
            }
        }
        return 0;
    }
    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function fetchSupporterSectionInfo($member_id)
    {
        $sql = "SELECT GROUP_CONCAT(DISTINCT `c`.`name`) `sections` FROM 
                `section_support` `s`, `support` `t` , `section` `c`
                WHERE `s`.`support_id` = `t`.`id` AND `s`.`section_id` = `c`.`section_id`
                AND  `t`.`member_id` = :member_id AND `t`.`status_id`>=2
                AND `s`.`is_active` = 1
                ORDER BY `c`.`order`";

        return $this->getAdapter()->fetchRow($sql, array('member_id' => $member_id));
    }

    //lets put viewhelper in view BuildProductUrl

    /**
     * @param $member_id
     *
     * @return int|null
     */
    public function fetchLastActiveTime($member_id)
    {
        $sql_page_views = "SELECT `created_at` AS `lastactive` FROM `stat_page_views` WHERE `member_id` = :member_id ORDER BY `created_at` DESC LIMIT 1";
        $sql_activities = "SELECT `time` AS `lastactive` FROM `activity_log` WHERE `member_id` = :member_id ORDER BY `time` DESC LIMIT 1";

        $result_page_views = $this->getAdapter()->fetchRow($sql_page_views, array('member_id' => $member_id));
        $result_activities = $this->getAdapter()->fetchRow($sql_activities, array('member_id' => $member_id));

        if (count($result_page_views) > 0 and count($result_activities) > 0) {
            return $result_page_views['lastactive'] > $result_activities['lastactive'] ? $result_page_views['lastactive'] : $result_activities['lastactive'];
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

        foreach ($projects as $pro) {
            $projArr = array();
            $projArr['id'] = $pro->project_id;
            $projArr['name'] = $pro->title;
            $projArr['image'] = $pro->image_small;
            //$projArr['url'] = $this->buildProductUrl($pro->project_id, '', null, true);
            $projArr['sumAmount'] = $pro->sumAmount;
            array_push($catArray[$pro->catTitle], $projArr);
        }

        return $catArray;
    }

    /**
     * @param int  $member_id
     * @param null $limit
     *
     * @return array|ResultSet
     */
    public function fetchSupportedProjects($member_id, $limit = null)
    {
        $sql = "
                SELECT `plings`.`project_id`,                       
                       `project`.`title`,
                       `project`.`image_small`,                       
                       `project_category`.`title` AS `catTitle`,                       
                       (SELECT SUM(`amount`) FROM `plings` WHERE `plings`.`project_id`=`project`.`project_id` AND `plings`.`status_id`=2) AS `sumAmount`
                FROM `plings`
                 JOIN `project` ON `plings`.`project_id` = `project`.`project_id`
                 JOIN `project_category` ON `project`.`project_category_id` = `project_category`.`project_category_id`                 
                WHERE `plings`.`status_id` IN (2,3,4)
                  AND `plings`.`member_id` = :member_id
                  AND `project`.`status` = :project_status
                  AND `project`.`type_id` = 1
                GROUP BY `plings`.`project_id`
                ORDER BY `sumAmount` DESC
                ";


        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        return $this->getAdapter()->fetchAll(
            $sql, array('member_id' => $member_id, 'project_status' => ProjectRepository::PROJECT_ACTIVE), false
        );
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

        return $this->getAdapter()->fetchAll($sql, array('username' => $value));
    }

    /**
     * @param string $login
     *
     * @return int
     */
    public function generateUniqueUsername($login)
    {
        $sql = "SELECT COUNT(*) AS `counter` FROM `member` WHERE `username` REGEXP CONCAT(:user_name,'[_0-9]*$')";
        $result = $this->getAdapter()->fetchRow($sql, array('user_name' => $login));

        return $login . '_' . $result['counter'];
    }

    /**
     * @param int    $member_id
     * @param string $email
     *
     * @return bool
     */
    public function setActive($member_id, $email)
    {
        $sql = "
            UPDATE `member`
              STRAIGHT_JOIN `member_email` ON `member`.`member_id` = `member_email`.`email_member_id` AND `member_email`.`email_checked` IS NULL AND `member`.`is_deleted` = 0 AND `member_email`.`email_deleted` = 0
            SET `member`.`mail_checked` = 1, `member`.`is_active` = 1, `member`.`changed_at` = NOW(), `member_email`.`email_checked` = NOW()
            WHERE `member`.`member_id` = :memberId AND `member_email`.`email_address` = :mailAddress;
        ";
        $stmt = $this->getAdapter()->query($sql, array('memberId' => $member_id, 'mailAddress' => $email));

        return $stmt->getAffectedRows() > 0;
    }

    /**
     * @param string $identity
     *
     * @return Member
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
        $resultMail = $this->getAdapter()->fetchRow(
            $sqlMail, array(
                        'active'   => MemberRepository::MEMBER_ACTIVE,
                        'deleted'  => MemberRepository::MEMBER_NOT_DELETED,
                        'identity' => $identity,
                    )
        );
        if ((false !== $resultMail) and (count($resultMail) > 0)) {
            //return $this->getAdapter()->arrayToObject($resultMail);
            //return $this->generateRowClass($resultMail);
            $resultSet = new HydratingResultSet(new ReflectionHydrator(), new Member());
            // in this special case we have to wrap the result in an array because the hydrating class expected an array of rows
            $resultSet->initialize(array($resultMail));
            /** @var Member $member */
            $member = $resultSet->current() === false ? new Member() : $resultSet->current();

            return $member;
        }

        return new Member();
        //return $this->createRow();
    }

    /**
     * @param string $orderby
     * @param null   $limit
     * @param null   $offset
     *
     * @return mixed
     */
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

        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * @return mixed
     */
    public function getMembersAvatarOldAutogeneratedTotalCount()
    {
        $sql = " 
                      SELECT count(1) AS `cnt`
                      FROM `tmp_member_avatar_unknow` 
        ";
        $result = $this->getAdapter()->query($sql)->current();

        return $result->cnt;
    }

    /**
     * @param $member_id
     * @param $type_id
     */
    public function updateAvatarTypeId($member_id, $type_id)
    {
        $sql = "
                      UPDATE `member` SET `avatar_type_id` = :type_id WHERE `member_id` = :member_id
                   ";
        $this->getAdapter()->query($sql, array('type_id' => $type_id, 'member_id' => $member_id));
    }

    public function findRegisterToken($token)
    {
        return $this->memberRepository->findOneByToken($token);
    }

    /**
     * @return MemberInterface
     */
    public function getMemberRepository()
    {
        return $this->memberRepository;
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
        $identIcon = new Identicon();
        $tmpImagePath = IMAGES_UPLOAD_PATH . '/tmp/' . $uuidMember . '.png';
        imagepng($identIcon->renderIdentIcon(sha1($userData['mail']), 1100), $tmpImagePath);

        return $this->imageRepository->saveImageOnMediaServer($tmpImagePath);
    }

    /**
     * @param int $member_id
     *
     * @throws Exception
     * @deprecated since we're using solr server for searching
     */
    private function removeMemberProjectsFromSearch($member_id)
    {

    }

}