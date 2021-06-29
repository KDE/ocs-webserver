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
 * */

namespace Application\Model\Service;

use Application\Model\Entity\Project;
use Application\Model\Repository\PlingsRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Repository\ProjectGalleryPictureRepository;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Service\Interfaces\CollectionServiceInterface;
use ArrayObject;
use Exception;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Library\Tools\Uuid;

class CollectionService extends BaseService implements CollectionServiceInterface
{

    const FILTER_NAME_PROJECT_ID_NOT_IN = 'project_id_not_in';
    const FILTER_NAME_RANKING = 'ranking';
    const FILTER_NAME_CATEGORY = 'category';
    const FILTER_NAME_TAG = 'tag';
    const FILTER_NAME_ORIGINAL = 'original';
    const FILTER_NAME_FAVORITE = 'favorite';
    const FILTER_NAME_MEMBER = 'member';
    const FILTER_NAME_ORDER = 'order';
    const FILTER_NAME_LOCATION = 'location';

    const ITEM_TYPE_DUMMY = 0;
    const ITEM_TYPE_PRODUCT = 1;
    const ITEM_TYPE_UPDATE = 2;

    const TAG_LICENCE_GID = 7;
    const TAG_TYPE_ID = 1;

    const TAG_ISORIGINAL = 'original-product';

    const PROJECT_TYPE_PERSONAL = 0;
    const PROJECT_TYPE_STANDARD = 1;
    const PROJECT_TYPE_UPDATE = 2;
    const PROJECT_TYPE_COLLECTION = 3;
    const PROJECT_FAULTY = 0;       // project data contains errors
    const PROJECT_INCOMPLETE = 10;  // process for adding the product was not successfully completed
    const PROJECT_ILLEGAL = 20;     // project data is complete, but the project doesn't accord to our rules
    const PROJECT_DELETED = 30;     // owner or staff deleted the product
    const PROJECT_INACTIVE = 40;    // project is not visible to the world, but for the owner and staff
    const PROJECT_ACTIVE = 100;     // project is active and visible to the world
    const PROJECT_CLAIMED = 1;
    const PROJECT_CLAIMABLE = 1;
    const PROJECT_DEFAULT = null;
    const MYSQL_DATE_FORMAT = "Y-m-d H:i:s";
    const PROJECT_SPAM_CHECKED = 1;
    const PROJECT_SPAM_UNCHECKED = 0;

    //from project row class
    const CATEGORY_DEFAULT_PROJECT = 0;
    const STATUS_PROJECT_ACTIVE = 10;
    const DEFAULT_AVATAR_IMAGE = 'std_avatar_80.png';
    const PERSONAL_PROJECT_TITLE = 'Personal Page';

    protected $db;
    protected $cache;
    private $projectRepository;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
        $this->cache = $GLOBALS['ocs_cache'];
        $this->projectRepository = new ProjectRepository($db);
    }

    /**
     * @param array $productInfo
     *
     * @return array
     */
    public static function cleanProductInfoForJson(array $productInfo)
    {
        if (empty($productInfo)) {
            return $productInfo;
        }

        $unwantedKeys = array(
            'roleId'           => 0,
            'mail'             => 0,
            'dwolla_id'        => 0,
            'paypal_mail'      => 0,
            'content_type'     => 0,
            'hive_category_id' => 0,
            'is_active'        => 0,
            'is_deleted'       => 0,
            'start_date'       => 0,
            'source_id'        => 0,
            'source_pk'        => 0,
            'source_type'      => 0,
        );

        $productInfo = array_diff_key($productInfo, $unwantedKeys);

        return $productInfo;
    }

    /**
     * @param int $status
     * @param int $id
     *
     * @throws Exception
     */
    public function setStatus($status, $id)
    {
        if (false === in_array($status, ProjectRepository::getAllowedStatusTypes())) {
            throw new Exception('Wrong value for project status.');
        }
        $updateValues = array(
            'status'     => $status,
            'changed_at' => new Expression('Now()'),
        );

        if (self::PROJECT_DELETED == $status) {
            $updateValues['deleted_at'] = new Expression('NOW()');
        }

        $this->projectRepository->update($updateValues, 'project_id=' . $id);
    }

    /**
     * @param int $member_id
     * @param int $id
     */
    public function setClaimedByMember($member_id, $id)
    {
        $updateValues = array(
            'claimed_by_member' => $member_id,
            'changed_at'        => new Expression('Now()'),
        );

        $this->projectRepository->update($updateValues, 'project_id=' . $id);
    }

    /**
     * @param int $id
     */
    public function resetClaimedByMember($id)
    {
        $updateValues = array(
            'claimed_by_member' => new Expression('NULL'),
            'changed_at'        => new Expression('Now()'),
        );

        $this->projectRepository->update($updateValues, 'project_id=' . $id);
    }

    /**
     * @param int $id
     */
    public function transferClaimToMember($id)
    {
        $updateValues = array(
            'member_id'         => new Expression('claimed_by_member'),
            'claimable'         => new Expression('NULL'),
            'claimed_by_member' => new Expression('NULL'),
        );

        $this->projectRepository->update($updateValues, 'project_id=' . $id . ' and claimable = 1');
    }

    /**
     * @param int $project_id
     * @param     $member_id
     *
     */
    public function setInActive($project_id, $member_id)
    {
        $project_id = (int)$project_id;
        $updateValues = array(
            'status'     => self::PROJECT_INACTIVE,
            'deleted_at' => new Expression('Now()'),
        );

        $this->projectRepository->update($updateValues, 'status > 40 AND project_id=' . $project_id);

        $this->setInActiveForUpdates($project_id);
        $this->setDeletedForComments($member_id, $project_id);
    }

    /**
     * @param int $id
     */
    protected function setInActiveForUpdates($id)
    {
        $id = (int)$id;
        $updateValues = array(
            'status'     => self::PROJECT_INACTIVE,
            'changed_at' => new Expression('Now()'),
        );

        $this->projectRepository->update($updateValues, 'status > 40 AND pid=' . $id);
    }

    /**
     * @param int $member_id
     * @param int $id
     *
     */
    private function setDeletedForComments($member_id, $id)
    {
        $modelComments = new ProjectCommentsService($this->db);
        $modelComments->setAllCommentsForProjectDeleted($member_id, $id);
    }

    /**
     * @param int  $member_id
     * @param bool $onlyActiveProjects
     *
     * @return mixed
     */
    public function countAllCollectionsForMember($member_id, $onlyActiveProjects = false)
    {
        $sql = "SELECT count(1) AS `countAll` FROM `project` WHERE `status` >= :status AND `member_id` = :member_id AND `type_id` = :type_id";
        $result = $this->projectRepository->fetchRow(
            $sql, array(
                    "status"    => ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE),
                    "member_id" => $member_id,
                    "type_id"   => self::PROJECT_TYPE_COLLECTION,
                )
        );

        return $result['countAll'];
    }

    /**
     * @param int  $project_id
     * @param bool $onlyActiveProjects
     *
     * @return mixed
     */
    public function countAllCollectionsForProject($project_id, $onlyActiveProjects = true)
    {
        $sql = "SELECT count(1) AS `countAll` FROM `collection_projects` WHERE `active` = 1 AND `project_id` = :project_id";

        $result = $this->projectRepository->fetchRow($sql, array("project_id" => $project_id));

        return $result['countAll'];

    }

    /**
     * @param int  $member_id
     * @param bool $onlyActiveProjects
     * @param      $catids
     *
     * @return mixed
     */
    public function countAllProjectsForMemberCatFilter($member_id, $onlyActiveProjects = false, $catids = null)
    {
        $sql = "SELECT count(1) AS `countAll` FROM `project` WHERE `status` >= :status AND `member_id` = :member_id AND `type_id` = :type_id";
        if (isset($catids)) {
            $sql .= ' and project_category_id in (' . $catids . ')';
        }

        $result = $this->projectRepository->fetchRow(
            $sql, array(
                    "status"    => ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE),
                    "member_id" => $member_id,
                    "type_id"   => self::PROJECT_TYPE_COLLECTION,
                )
        );

        return $result['countAll'];

    }

    /**
     * By default it will show all projects for a member included the unpublished elements.
     *
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     * @param bool     $onlyActiveProjects
     *
     * @return array|ResultSet
     */
    public function fetchAllCollectionsForMember($member_id, $limit = null, $offset = null, $onlyActiveProjects = false)
    {
        $sql = "SELECT `project`.*,
                `project`.`validated` AS 'project_validated',
                `project`.`uuid` AS 'project_uuid',
                `project`.`status` AS 'project_status',
                `project`.`created_at` AS 'project_created_at',
                `project`.`changed_at` AS 'project_changed_at',
                `member`.`type` AS 'member_type',
                `project`.`member_id` AS 'project_member_id',
                `laplace_score`(`count_likes`,`count_dislikes`) AS 'laplace_score',
                (SELECT `title` FROM `project_category` WHERE `project_category_id` = `project`.`project_category_id`) AS 'catTitle',
                `member`.`username`
                FROM `project`            
                JOIN `member` ON `project`.`member_id` = `member`.`member_id`
                WHERE `project`.`status` >= :status
                AND `project`.`member_id` = :member_id
                AND `project`.`type_id` = :type_id
                ORDER BY `project_changed_at` DESC";

        if (isset($limit)) {
            $sql .= " LIMIT " . $limit . " OFFSET " . $offset;
        }

        return $this->projectRepository->fetchAll(
            $sql, array(
                    "member_id" => $member_id,
                    "status"    => ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE),
                    "type_id"   => self::PROJECT_TYPE_COLLECTION,
                )
        );
    }

    /**
     * By default it will show all projects for a project included the unpublished elements.
     *
     * @param int      $project_id
     * @param int|null $limit
     * @param int|null $offset
     * @param bool     $onlyActiveProjects
     *
     * @return array|ResultSet
     */
    public function fetchAllCollectionsForProject($project_id, $limit = null, $offset = 0, $onlyActiveProjects = true)
    {
        $sql = "SELECT `collection_projects`.`collection_id`,
		`project`.*,
                (SELECT `title` FROM `project_category` WHERE `project_category_id` = `project`.`project_category_id`) AS 'catTitle',
                `project`.`project_category_id`,
                `member`.`username`
                FROM `collection_projects`
                JOIN `project` ON `collection_projects`.`collection_id` = `project`.`project_id`
                JOIN `member` ON `project`.`member_id` = `member`.`member_id`
                WHERE 
                `collection_projects`.`active` = 1
                AND `collection_projects`.`project_id` = :project_id
                AND `project`.`status` >= :status
                AND `project`.`type_id` = :type_id
                ORDER BY `project`.`changed_at` DESC";
        if (isset($limit)) {
            $sql = $sql . " LIMIT " . $limit . " OFFSET " . $offset;
        }

        return $this->projectRepository->fetchAll(
            $sql, array(
                    "project_id" => $project_id,
                    "status"     => ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE),
                    "type_id"    => self::PROJECT_TYPE_COLLECTION,
                )
        );
    }

    /**
     * @param $collection_id
     *
     * @return array|ArrayObject|null
     */
    public function fetchProductForCollectionId($collection_id)
    {
        $sql = '
                SELECT
                  `p`.*
                FROM `project` AS `p`
                WHERE 
                  `p`.`ppload_collection_id` = :collectionId
                  AND `p`.`status` >= :projectStatus AND `p`.`type_id` = :typeId
        ';
        $result = $this->projectRepository->fetchRow(
            $sql, array(
                    'collectionId'  => $collection_id,
                    'projectStatus' => self::PROJECT_INACTIVE,
                    'typeId'        => self::PROJECT_TYPE_STANDARD,
                )
        );

        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @param int $project_id
     *
     * @return array|ArrayObject|null
     */
    public function fetchProductInfo($project_id)
    {
        $sql = '
                SELECT
                  `p`.*,
                  `p`.`validated` AS `project_validated`,
                  `p`.`uuid` AS `project_uuid`,
                  `p`.`status` AS `project_status`,
                  `p`.`created_at` AS `project_created_at`,
                  `p`.`major_updated_at` AS `project_major_updated_at`,
                  `p`.`changed_at` AS `project_changed_at`,
                  `p`.`member_id` AS `project_member_id`,
                  `p`.`source_pk` AS `project_source_pk`,
                  `p`.`version` AS `project_version`,
                  `pc`.`title` AS `cat_title`,
                  `m`.`username`,
                  `m`.`avatar`,
                  `m`.`profile_image_url`,
                  `m`.`roleId`,
                  `m`.`mail`,
                  `m`.`paypal_mail`,
                  `m`.`dwolla_id`,
                  `laplace_score`(`p`.`count_likes`,`p`.`count_dislikes`) AS `laplace_score`,
                 `view_reported_projects`.`amount_reports` AS `amount_reports`,
                (SELECT `tag`.`tag_fullname` FROM `tag_object`, `tag` WHERE `tag_object`.`tag_id`=`tag`.`tag_id` AND `tag_object_id` = `p`.`project_id` AND `tag_object`.`is_deleted`=0 AND `tag_group_id` = :tag_licence_gid AND `tag_type_id` = :tag_type_id  ORDER BY `tag_object`.`tag_created` DESC LIMIT 1)
                                AS `project_license_title`
                FROM `project` AS `p`
                  JOIN `member` AS `m` ON `p`.`member_id` = `m`.`member_id` AND `m`.`is_active` = 1 AND `m`.`is_deleted` = 0
                  JOIN `project_category` AS `pc` ON `p`.`project_category_id` = `pc`.`project_category_id`
                  LEFT JOIN `view_reported_projects` ON ((`view_reported_projects`.`project_id` = `p`.`project_id`))                  
                WHERE 
                  `p`.`project_id` = :projectId
                  AND `p`.`status` >= :projectStatus AND `p`.`type_id` = :typeId
        ';
        $result = $this->projectRepository->fetchRow(
            $sql, array(
                    'projectId'       => $project_id,
                    'projectStatus'   => self::PROJECT_INACTIVE,
                    'typeId'          => self::PROJECT_TYPE_COLLECTION,
                    'tag_licence_gid' => self::TAG_LICENCE_GID,
                    'tag_type_id'     => self::TAG_TYPE_ID,
                )
        );

        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @param $project_id
     *
     * @return array
     * @deprecated
     */
    public function fetchProjectUpdates($project_id)
    {
        return array();
    }

    /**
     * @param $project_id
     *
     * @return array
     * @deprecated
     */
    public function fetchAllProjectUpdates($project_id)
    {
        return array();
    }

    /**
     * @param     $project
     * @param int $count
     *
     * @return array|ResultSet
     */
    public function fetchSimilarProjects($project, $count = 10)
    {
        $count = (int)$count;
        $sql = "
                SELECT *
                FROM `project` AS `p`
                WHERE `p`.`project_category_id` = :cat_id AND `project_id` <> :project_id
                ORDER BY `p`.`changed_at` DESC
                LIMIT {$count}
        ";

        return $this->projectRepository->fetchAll(
            $sql, array(
                    'cat_id'     => $project->project_category_id,
                    'project_id' => $project->project_id,
                )
        );
    }

    /**
     * @param     $project
     * @param int $count
     *
     * @return array|ResultInterface
     */
    public function fetchMoreCollections($project, $count = 6)
    {
        $tagFilter = isset($GLOBALS['ocs_config_store_tags']) ? $GLOBALS['ocs_config_store_tags'] : null;  
        $sql = '
                
                select p.project_id
                ,p.image_small
                ,p.title
                ,p.project_category_id
                ,p.changed_at
                , c.title as catTitle                
                from project p 
                inner join project_category c on p.project_category_id = c.project_category_id
                left join reports_project r on r.is_deleted=0 and r.report_type=0 and r.project_id = p.project_id
                ';
        if ($tagFilter) {
            $sql .= '
                    inner JOIN (SELECT DISTINCT project_id FROM stat_project_tagids 
                                    WHERE tag_id in ('.implode(",", $tagFilter).')
                                ) AS store_tags 
                                ON p.project_id = store_tags.project_id
                    ';
        }
        $sql .= '
                where 
                p.status = 100 
                and p.project_id != :project_id
                and p.member_id = :member_id 
                and p.project_category_id = :project_category_id
                and p.type_id = 1
                and r.report_id is null                
                order by changed_at DESC
                limit 6 
        ';        

       
       $result = $this->projectRepository->fetchAll($sql,['project_id' =>(int)$project->project_id
                                        ,'member_id' => (int)$project->member_id
                                        ,'project_category_id' => (int)$project->project_category_id
                                ],false);

                              
        return $result;
    }

    /**
     * @param Select $statement
     * @param        $filterArrayValue
     *
     * @return Select
     */
    public function generateTagFilter(Select $statement, $filterArrayValue)
    {
        if (false == isset($filterArrayValue[self::FILTER_NAME_TAG])) {
            return $statement;
        }

        $filter = $filterArrayValue[self::FILTER_NAME_TAG];

        if (is_array($filter)) {
            $sql = new Sql($this->db);

            $tagList = $filter;
            //build where statement für projects
            //$selectAnd = $this->select()->from(array('project' => 'stat_projects'));
            $selectAnd = $sql->select('stat_projects');

            foreach ($tagList as $item) {
                #and
                $selectAnd->where('find_in_set(' . $item . ', tag_ids)', $selectAnd::COMBINE);
            }
            $statement->where($selectAnd->getRawState('where'));

        } else {
            $statement->where('find_in_set(' . $filter . ', tag_ids)');
        }

        return $statement;
    }

    /**
     * @param     $project
     * @param int $count
     *
     * @return array|ResultInterface
     */
    public function fetchMoreCollectionsOfOtherUsr($project, $count = 8)
    {
       // get random offset
       $tagFilter = isset($GLOBALS['ocs_config_store_tags']) ? $GLOBALS['ocs_config_store_tags'] : null;       
       $sql = 'select count(1) as count from 
                  (
                  select p.project_id                    
                  from project p 
                  left join reports_project r on r.is_deleted=0 and r.report_type=0 and r.project_id = p.project_id
                  ';
      if ($tagFilter) {
          $sql .= '
                  inner JOIN (SELECT DISTINCT project_id FROM stat_project_tagids 
                                  WHERE tag_id in ('.implode(",", $tagFilter).')
                              ) AS store_tags 
                              ON p.project_id = store_tags.project_id
                  ';
      }
                  
      $sql .= 'where 
                  p.status = 100 
                  and p.member_id <> :member_id 
                  and p.project_category_id = :project_category_id
                  and p.type_id = 1
                  and r.report_id is null
                  ) t 
      ';
       
      $result = $this->projectRepository->fetchRow($sql,['member_id' => $project->member_id
                                    ,'project_category_id' => $project->project_category_id]);  
                                                                 
      $offset = 0;
      if ((int)$result['count'] > $count) {
          $offset = rand(0, $result['count'] - $count);
      } else {
          $offset = 0;
      }       
      
      $sql = '
                  select p.project_id
                  ,p.image_small
                  ,p.title
                  ,p.project_category_id
                  ,p.changed_at
                  , c.title as catTitle                    
                  from project p 
                  inner join project_category c on p.project_category_id = c.project_category_id
                  left join reports_project r on r.is_deleted=0 and r.report_type=0 and r.project_id = p.project_id
                  ';
      if ($tagFilter) {
          $sql .= '
                  inner JOIN (SELECT DISTINCT project_id FROM stat_project_tagids 
                                  WHERE tag_id in ('.implode(",", $tagFilter).')
                              ) AS store_tags 
                              ON p.project_id = store_tags.project_id
                  ';
      }
      $sql .= '        
                  where 
                  p.status = 100 
                  and p.member_id <> :member_id 
                  and p.project_category_id = :project_category_id
                  and p.type_id = 1
                  and r.report_id is null
                 
                  limit '.$count.' offset '.$offset;
                
      $result = $this->projectRepository->fetchAll($sql,['member_id' => $project->member_id
                                      ,'project_category_id' => $project->project_category_id],false);
                                                                            
      return $result;
    }

    /**
     * @param int $project_id
     *
     * @return array
     */
    public function fetchProjectSupporter($project_id)
    {
        $plingTable = new PlingsRepository($this->db);

        return $plingTable->getSupporterForProjectId($project_id);
    }

    /**
     * @param int $project_id
     *
     * @return array
     */
    public function fetchProjectSupporterWithPlings($project_id)
    {
        $plingTable = new PlingsRepository($this->db);

        return $plingTable->getSupporterWithPlingsForProjectId($project_id);
    }

    /**
     * @param $projectId
     *
     * @return array
     */
    public function getGalleryPictureSources($projectId)
    {
        $galleryPictureTable = new ProjectGalleryPictureRepository($this->db);
        $sql = sprintf("select * from %s where project_id = %d order by sequence", $galleryPictureTable->getName(), (int)$projectId);

        $galleryPics = $galleryPictureTable->fetchAll($sql);

        $pics = array();
        foreach ($galleryPics as $pictureRow) {
            $pics[] = $pictureRow['picture_src'];
        }

        return $pics;
    }

    /**
     * @param int $project_id
     *
     * @return array
     */
    public function fetchProjectViews($project_id)
    {
        $sql = "
                SELECT
                    `project_id`,
                    `count_views`,
                    `count_visitor`,
                    `last_view`
                FROM
                    `stat_page_views_mv`
                WHERE `project_id` = :id
                ";
        $resultSet = $this->fetchRow($sql, array("id" => $project_id));

        if (count($resultSet) > 0) {
            $result = $resultSet['count_views'];
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * @param int $member_id
     *
     * @return int
     */
    public function fetchOverallPageViewsByMember($member_id)
    {
        $sql = "
                SELECT sum(`stat`.`amount`) AS `page_views`
                FROM `project`
                JOIN (SELECT `project_id`, count(`project_id`) AS `amount` FROM `stat_page_views` GROUP BY `project_id`) AS `stat` ON `stat`.`project_id` = `project`.`project_id`
                WHERE `project`.`member_id` = :member_id AND `project`.`status` = :project_status
                GROUP BY `member_id`
              ";

        $result = $this->projectRepository->fetchRow($sql, array('member_id' => $member_id, 'project_status' => self::PROJECT_ACTIVE));
        if (count($result) > 0) {
            return $result['page_views'];
        }

        return 0;
    }

    /**
     * @param int $project_id
     *
     * @return bool
     */
    public function isProjectFeatured($project_id)
    {
        $sql_object = "SELECT `project_id` FROM `project` WHERE `project_id`= :project_id AND  `status` = 100 AND `type_id` = 1 AND `featured` = 1";
        $r = $this->projectRepository->fetchRow($sql_object, array('project_id' => $project_id));
        if ($r) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $member_id
     * @param int $id
     *


     */
    public function setDeleted($member_id, $id)
    {
        $id = (int)$id;
        $updateValues = array(
            'status'     => self::PROJECT_DELETED,
            'deleted_at' => new Expression('Now()'),
        );

        $this->projectRepository->update($updateValues, 'status > 30 AND project_id=' . $id);

        $memberLog = new MemberDeactivationLogService($this->db);
        $memberLog->logProjectAsDeleted($member_id, $id);

        $this->setDeletedForUpdates($member_id, $id);
        $this->setDeletedForComments($member_id, $id);
        $this->setDeletedInMaterializedView($id);
    }

    /**
     * @param     $member_id
     * @param int $id
     */
    protected function setDeletedForUpdates($member_id, $id)
    {
        $id = (int)$id;
        $updateValues = array(
            'status'     => self::PROJECT_DELETED,
            'deleted_at' => new Expression('Now()'),
        );

        $this->projectRepository->update($updateValues, 'status > 30 AND pid=' . $id);
    }

    /**
     * @param $id
     *

     */
    private function setDeletedInMaterializedView($id)
    {
        $sql = "UPDATE `stat_projects` SET `status` = :new_status WHERE `project_id` = :project_id";

        $result = $this->projectRepository->query(
            $sql, array(
                    'new_status' => self::PROJECT_DELETED,
                    'project_id' => $id,
                )
        );
    }

    /**
     * @param int $member_id
     *

     */
    public function setAllProjectsForMemberActivated($member_id)
    {
        $sql = "SELECT `p`.`project_id` FROM `project` `p`
                JOIN `member_deactivation_log` `l` ON `l`.`object_type_id` = 3 AND `l`.`object_id` = `p`.`project_id` AND `l`.`deactivation_id` = `p`.`member_id`
                WHERE `p`.`member_id` = :memberId";
        $projectForDelete = $this->projectRepository->fetchAll(
            $sql, array(
                    'memberId' => $member_id,
                )
        );
        foreach ($projectForDelete as $item) {
            $this->setActive($member_id, $item['project_id']);
        }
    }

    /**
     * @param int $member_id
     * @param int $id
     *

     */
    public function setActive($member_id, $id)
    {
        $updateValues = array(
            'status'     => self::PROJECT_ACTIVE,
            'deleted_at' => null,
        );

        $this->projectRepository->update($updateValues, 'project_id=' . $id);

        $memberLog = new MemberDeactivationLogService($this->db);
        $memberLog->removeLogProjectAsDeleted($member_id, $id);

        $this->setActiveForUpdates($id);
        $this->setActiveForComments($member_id, $id);
    }

    /**
     * @param int $id
     */
    protected function setActiveForUpdates($id)
    {
        $updateValues = array(
            'status'     => self::PROJECT_ACTIVE,
            'deleted_at' => null,
        );

        $this->projectRepository->update($updateValues, 'pid=' . $id);
    }

    /**
     * @param int $member_id
     * @param int $project_id
     */
    private function setActiveForComments($member_id, $project_id)
    {
        $modelComments = new ProjectCommentsService($this->db);
        $modelComments->setAllCommentsForProjectActivated($member_id, $project_id);
    }

    /**
     * @param int    $member_id
     * @param array  $values
     * @param string $username
     *
     * @return Project
     * @throws Exception
     */
    public function createCollection($member_id, $values, $username)
    {
        $values = (array)$values;
        if (empty($member_id)) {
            throw new Exception('member_id is not set');
        }
        if (empty($username)) {
            throw new Exception('username is not set');
        }
        // check important values for a new project
        $values['uuid'] = (!array_key_exists('uuid', $values)) ? Uuid::generateUUID() : $values['uuid'];
        $values['member_id'] = (!array_key_exists('member_id', $values)) ? $member_id : $values['member_id'];
        $values['status'] = (!array_key_exists('status', $values)) ? self::PROJECT_INACTIVE : $values['status'];
        $values['type_id'] = (!array_key_exists('type_id', $values)) ? self::ITEM_TYPE_PRODUCT : $values['type_id'];
        $values['created_at'] = (!array_key_exists('created_at', $values)) ? new Expression("NOW()") : $values['created_at'];
        $values['start_date'] = (!array_key_exists('start_date', $values)) ? null : $values['start_date'];
        $values['creator_id'] = (!array_key_exists('creator_id', $values)) ? $member_id : $values['creator_id'];
        $values['gitlab_project_id'] = (empty($values['gitlab_project_id'])) ? null : $values['gitlab_project_id'];
        $values['is_gitlab_project'] = (empty($values['is_gitlab_project'])) ? 0 : $values['is_gitlab_project'];
        $values['show_gitlab_project_issues'] = (empty($values['show_gitlab_project_issues'])) ? 0 : $values['show_gitlab_project_issues'];
        $values['use_gitlab_project_readme'] = (empty($values['use_gitlab_project_readme'])) ? 0 : $values['use_gitlab_project_readme'];
        $values['spam_checked'] = (empty($values['spam_checked'])) ? 0 : $values['spam_checked'];
        $values['pling_excluded'] = (empty($values['pling_excluded'])) ? 0 : $values['pling_excluded'];
        $values['major_updated_at'] = (empty($values['major_updated_at'])) ? new Expression('NOW()') : $values['major_updated_at'];
        $values['content_type'] = (empty($values['content_type'])) ? 'text' : $values['content_type'];
        $values['hive_category_id'] = (empty($values['hive_category_id'])) ? 0 : $values['hive_category_id'];
        $values['is_active'] = (empty($values['is_active'])) ? 1 : $values['is_active'];
        $values['is_deleted'] = (empty($values['is_deleted'])) ? 0 : $values['is_deleted'];

        if ($username == 'pling editor') {
            $values['claimable'] = (!array_key_exists('claimable', $values)) ? self::PROJECT_CLAIMABLE : $values['claimable'];
        }

        $newId = $this->projectRepository->insert($values);
        if ($newId) {
            $savedRow = $this->projectRepository->findById($newId);

        }

        return $savedRow;
    }

    /**
     * @param int   $project_id
     * @param array $values
     *
     * @return Project
     * @throws Exception
     */
    public function updateCollection($project_id, $values)
    {
        $values = (array)$values;
        $values['project_id'] = $project_id;
        $projectData = $this->projectRepository->findById($project_id);
        if (empty($projectData)) {
            throw new Exception('project_id not found');
        }

        $result = $this->projectRepository->update($values);
        $project = null;

        if ($result) {
            $project = $this->projectRepository->findById($project_id);
        }

        return $project;
    }

    /**
     * @param int $member_id
     *
     * @return array|mixed
     */
    public function fetchMainProject($member_id)
    {
        $sql = "SELECT * FROM {$this->_name} WHERE type_id = :type AND member_id = :member";

        $result = $this->projectRepository->fetchRow(
            $sql, array(
                    'type'   => self::PROJECT_TYPE_PERSONAL,
                    'member' => (int)$member_id,
                )
        );

        if (count($result) > 0) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * @param $project_id
     *
     * @return array|ResultSet
     */
    public function fetchProductDataFromMV($project_id)
    {
        $sql = "SELECT * FROM `stat_projects` WHERE `project_id` = :project_id";
        $resultSet = $this->projectRepository->fetchAll($sql, array('project_id' => $project_id));
        if (false === $resultSet) {
            return array();
        }

        return $resultSet;
    }

    /**
     * @return array
     */
    public function fetchGhnsExcludedProjects()
    {
        $sql = "
        	SELECT `p`.`project_id`, `p`.`title`, `l`.`member_id` AS `exclude_member_id`, `l`.`time` AS `exclude_time`, `m`.`username` AS `exclude_member_name` FROM `project` `p`
                JOIN `activity_log` `l` ON `l`.`project_id` = `p`.`project_id` AND `l`.`activity_type_id` = 314
                INNER JOIN `member` `m` ON `m`.`member_id` = `l`.`member_id`
                WHERE `p`.`ghns_excluded` = 1

        ";

        return $this->projectRepository->fetchAll($sql);
    }

    public function getUserCreatingCategorys($member_id)
    {
        $sql = " 
                    SELECT              
                       `c`.`title` AS `category1`,
                       count(1) AS `cnt`
                      FROM `project` `p`
                      JOIN `project_category` `c` ON `p`.`project_category_id` = `c`.`project_category_id`
                      WHERE `p`.`status` = 100
                      AND `p`.`member_id` =:member_id
                      AND `p`.`type_id` = 1
                      GROUP BY `c`.`title`
                      ORDER BY `cnt` DESC, `c`.`title` ASC
                  ";

        return $this->projectRepository->fetchAll($sql, array('member_id' => $member_id));
    }

    /**
     * @return array
     */
    public function getUserActiveProjects($member_id, $limit = null, $offset = null)
    {
        // for member me page
        $sql = "
                        SELECT
                        `p`.`project_id`,
                        `p`.`title`,
                        `p`.`created_at`  AS `project_created_at`,
                        `p`.`changed_at` AS `project_changed_at`,
                        `p`.`count_likes`,
                        `p`.`count_dislikes`,
                        `laplace_score`(`p`.`count_likes`, `p`.`count_dislikes`) AS `laplace_score`,
                        `p`.`member_id`,
                        `cat`.`title` AS `catTitle`,
                        `p`.`project_category_id`,    
                        `p`.`image_small`,
                        (SELECT count(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`,
                        `c`.`cnt` `cntCategory`
                        FROM `project` `p`
                        JOIN `project_category` `cat` ON `p`.`project_category_id` = `cat`.`project_category_id`
                        LEFT JOIN `stat_cnt_projects_catid_memberid` `c` ON `p`.`project_category_id` = `c`.`project_category_id` AND `p`.`member_id` = `c`.`member_id`
                        WHERE `p`.`status` =100
                        AND `p`.`type_id` = 1
                        AND `p`.`member_id` = :member_id       
                        ORDER BY `cntCategory` DESC,`catTitle` ASC, `p`.`changed_at` DESC
                      
        ";

        if (isset($limit)) {
            $sql = $sql . ' limit ' . $limit;
        }

        if (isset($offset)) {
            $sql = $sql . ' offset ' . $offset;
        }

        $result = $this->projectRepository->fetchAll($sql, array('member_id' => $member_id));
        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array|ResultSet|null
     */
    public function fetchAllFeaturedProjectsForMember($member_id, $limit = null, $offset = null)
    {
        // for member me page
        $sql = "
                          SELECT
                          `p`.`project_id`,
                          `p`.`title`,
                          `p`.`created_at`  AS `project_created_at`,
                          `p`.`changed_at` AS `project_changed_at`,
                          `p`.`count_likes`,
                          `p`.`count_dislikes`,
                          `p`.`laplace_score`,
                          `p`.`member_id`,
                          `p`.`cat_title` AS `catTitle`,
                          `p`.`image_small`,
                          (SELECT count(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`
                          FROM `stat_projects` `p`
                          WHERE `p`.`status` =100
                          AND `featured` = 1
                          AND `p`.`member_id` = :member_id        
                          ORDER BY `p`.`changed_at` DESC
          ";

        if (isset($limit)) {
            $sql = $sql . ' limit ' . $limit;
        }

        if (isset($offset)) {
            $sql = $sql . ' offset ' . $offset;
        }

        $result = $this->projectRepository->fetchAll($sql, array('member_id' => $member_id));
        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @param string   $orderby
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function fetchDuplicatedSourceProjects($orderby = 'source_url asc', $limit = null, $offset = null)
    {
        $sql = "
            SELECT 
            `source_url`
            ,count(1) AS `cnt`,
            GROUP_CONCAT(`p`.`project_id` ORDER BY `p`.`created_at`) `pids`
            FROM `stat_projects_source_url` `p` 
            GROUP BY `source_url`
            HAVING count(1)>1         
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

        return $this->projectRepository->fetchAll($sql);
    }

    /**
     * @return mixed
     */
    public function getTotalCountDuplicates()
    {

        $sql = "
          SELECT count(1) AS `cnt` FROM
          (
                      SELECT 
                       `source_url`
                       ,count(1) AS `cnt`,
                       GROUP_CONCAT(`p`.`project_id` ORDER BY `p`.`created_at`) `pids`
                       FROM `stat_projects_source_url` `p` 
                       GROUP BY `p`.`source_url`
                       HAVING count(1)>1 
          ) `a`  
      ";
        $result = $this->projectRepository->fetchAll($sql);

        return $result[0]['cnt'];
    }

    /**
     * @param string $source_url
     *
     * @return mixed
     */
    public function getCountSourceUrl($source_url)
    {
        $last = substr($source_url, -1);
        if ($last == '/') {
            $source_url = substr($source_url, 0, -1);
        }
        $sql = "
            SELECT count(1) AS `cnt` FROM 
            `stat_projects_source_url` `p`  
            WHERE `p`.`source_url`= :source_url        
      ";
        $result = $this->projectRepository->fetchRow($sql, array('source_url' => $source_url));

        return $result['cnt'];
    }

    /**
     * @param int $member_id
     *
     * @return mixed
     */
    public function getCountProjectsDuplicateSourceurl($member_id)
    {

        $sql = "
           SELECT count(1) AS `cnt`
           FROM
           (
              SELECT DISTINCT `p`.`source_url`
              ,(SELECT count(1) FROM `stat_projects_source_url` `pp` WHERE `pp`.`source_url`=`p`.`source_url`) `cnt` 
              FROM `stat_projects_source_url` `p` 
              WHERE `p`.`member_id` = :member_id 
           ) `t` WHERE `t`.`cnt`>1
      ";
        $result = $this->projectRepository->fetchRow($sql, array('member_id' => $member_id));

        return $result['cnt'];
    }

    /**
     * @param $ids
     *
     * @return array|ResultSet
     */
    public function fetchProjects($ids)
    {
        $sql = "SELECT * FROM stat_projects WHERE project_id in (" . $ids . ")";

        return $this->projectRepository->fetchAll($sql);
    }

    /**
     * @param $project_id
     *
     * @return true/false
     */
    public function validateDeleteProjectFromSpam($project_id)
    {
        //produkt ist ueber 6 monate alt oder produkt hat ueber 5 kommentare oder produkt hat minimum 1 pling
        // darf nicht gelöscht werden
        $sql = 'SELECT `count_comments`
            ,`created_at`
            , (`created_at`+ INTERVAL 6 MONTH < NOW()) `is_old`
            ,(SELECT count(1) FROM `project_plings` `f` WHERE `f`.`project_id` = `p`.`project_id` AND `f`.`is_deleted` = 0) `plings`
            FROM `project` `p` WHERE `project_id` =:project_id';
        $result = $this->projectRepository->fetchRow(
            $sql, array(
                    'project_id' => $project_id,
                )
        );

        if ($result['count_comments'] > 5 || $result['is_old'] == 1 || $result['plings'] > 0) {
            return false;
        }

        return true;
    }

    /**
     * @param $catids
     *
     * @return string
     */
    protected function _getCatIds($catids)
    {
        $sqlwhereCat = "";
        $sqlwhereSubCat = "";

        $idCategory = explode(',', $catids);
        if (false === is_array($idCategory)) {
            $idCategory = array($idCategory);
        }

        $sqlwhereCat .= implode(',', $idCategory);

        $modelCategory = new ProjectCategoryRepository($this->db, $this->cache);
        $subCategories = $modelCategory->fetchChildElements($idCategory);

        if (count($subCategories) > 0) {
            foreach ($subCategories as $element) {
                $sqlwhereSubCat .= "{$element['project_category_id']},";
            }
        }

        return $sqlwhereSubCat . $sqlwhereCat;
    }

}
