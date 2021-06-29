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

use Application\Model\Entity\Project;
use Application\Model\Interfaces\ProjectInterface;
use Exception;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use stdClass;

/**
 * Class ProjectRepository
 *
 * @package Application\Model\Repository
 */
class ProjectRepository extends BaseRepository implements ProjectInterface
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

    //attribute type_id
    const PROJECT_TYPE_PERSONAL = 0;
    const PROJECT_TYPE_STANDARD = 1;
    const PROJECT_TYPE_UPDATE = 2;
    const PROJECT_TYPE_COLLECTION = 3;

    //attribute status
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
    public static $_allowedStatusTypes = array(
        self::PROJECT_FAULTY,
        self::PROJECT_INCOMPLETE,
        self::PROJECT_ILLEGAL,
        self::PROJECT_INACTIVE,
        self::PROJECT_ACTIVE,
        self::PROJECT_DELETED,
    );
    protected $_types = array(
        'person'     => self::PROJECT_TYPE_PERSONAL,
        'project'    => self::PROJECT_TYPE_STANDARD,
        'item'       => self::PROJECT_TYPE_UPDATE,
        'collection' => self::PROJECT_TYPE_COLLECTION,
    );

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "project";
        $this->_key = "project_id";
        $this->_prototype = Project::class;
    }

    /**
     * @return int[]
     */
    public static function getAllowedStatusTypes()
    {
        return self::$_allowedStatusTypes;
    }

    /**
     * Override the insert method.
     *
     * @param array $data
     *
     * @return int
     */
    public function insert($data)
    {
        //Insert
        if (!isset($data['description'])) {
            $data['description'] = null;
        }

        if (!isset($data['title'])) {
            $data['title'] = null;
        }

        if (!isset($data['image_small'])) {
            $data['image_small'] = null;
        }

        if (!isset($data['project_category_id'])) {
            if ($data['type_id'] == 2) {
                // Find parent... 
                $parent = null;
                try {
                    $parent = $this->fetchById($data['pid']);
                } catch (Exception $e) {
                    error_log(__METHOD__ . ' - ERROR write activity log - ' . print_r($e, true));
                }
                if ($parent) {
                    $data['project_category_id'] = $parent->project_category_id;
                }
            }
        }

        return $this->insertOrUpdate($data);
    }

    public function setSpamChecked($projectId, $spamChecked = 1)
    {
        $data = ['project_id' => $projectId, 'spam_checked' => $spamChecked];
        $this->update($data);
    }

    /**
     * @param int $project_id
     *
     * @return null|array
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
                  IFNULL(`pr`.`score_with_pling`, 500) AS `laplace_score`,
                 `view_reported_projects`.`amount_reports` AS `amount_reports`,
                (SELECT `tag`.`tag_fullname` FROM `tag_object`, `tag` WHERE `tag_object`.`tag_id`=`tag`.`tag_id` AND `tag_object_id` = `p`.`project_id` AND `tag_object`.`is_deleted`=0 AND `tag_group_id` = :tag_licence_gid AND `tag_type_id` = :tag_type_id  ORDER BY `tag_object`.`tag_created` DESC LIMIT 1)
                                AS `project_license_title`
                FROM `project` AS `p`
                  JOIN `member` AS `m` ON `p`.`member_id` = `m`.`member_id` AND `m`.`is_active` = 1 AND `m`.`is_deleted` = 0
                  JOIN `project_category` AS `pc` ON `p`.`project_category_id` = `pc`.`project_category_id`
                  LEFT JOIN  `stat_rating_project` AS `pr`  ON `p`.`project_id` = `pr`.`project_id`
                  LEFT JOIN `view_reported_projects` ON ((`view_reported_projects`.`project_id` = `p`.`project_id`))
                WHERE
                  `p`.`project_id` = :projectId
                  AND `p`.`status` >= :projectStatus AND (`p`.`type_id` = :typeIdStd OR `p`.`type_id` = :typeIdColl)
        ';
        $result = $this->fetchRow(
            $sql, array(
                    'projectId'       => $project_id,
                    'projectStatus'   => self::PROJECT_INACTIVE,
                    'typeIdStd'       => self::PROJECT_TYPE_STANDARD,
                    'typeIdColl'      => self::PROJECT_TYPE_COLLECTION,
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
     * @param int $id
     *
     * @return mixed
     * @throws Exception
     */
    public function fetchActiveBySourcePk($id)
    {
        $sql = "SELECT * FROM `project` WHERE `status` = :status AND `source_pk` = :source_pk AND `source_type` = 'project'";

        return $this->fetchAll($sql, array("status" => self::PROJECT_ACTIVE, "source_pk" => $id));
    }

    /**
     * @param int  $member_id
     * @param bool $onlyActiveProjects
     *
     * @return mixed
     */
    public function countAllProjectsForMember($member_id, $onlyActiveProjects = false)
    {
        return $this->countAllProjectsForMemberCatFilter($member_id, $onlyActiveProjects);
    }

    /**
     * @param int    $member_id
     * @param bool   $onlyActiveProjects
     * @param string $catids as String
     *
     * @return mixed
     */
    public function countAllProjectsForMemberCatFilter($member_id, $onlyActiveProjects = false, $catids = null)
    {
        $cache_name = __FUNCTION__ . '_' . $member_id . $onlyActiveProjects . $catids;
        if ($result = $this->readCache($cache_name)) {
            return $result;
        }

        $sql = "select count(1) as countAll from {$this->_name} where `status` >= :status and `member_id` = :member_id and `type_id` = :type_id";
        if ($catids) {
            $sql .= ' and project_category_id in (' . $catids . ')';
        }

        $result = $this->fetchRow(
            $sql, array(
                    "status"    => ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE),
                    "member_id" => $member_id,
                    "type_id"   => self::PROJECT_TYPE_STANDARD,
                )
        );
        $this->writeCache($cache_name, $result['countAll'], 600);

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
     * @return array
     */
    public function fetchAllProjectsForMember($member_id, $limit = null, $offset = null, $onlyActiveProjects = false)
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
            $sql .= " LIMIT " . $limit;
        }
        if (isset($offset)) {
            $sql .= " OFFSET " . $offset;
        }

        return $this->fetchAll(
            $sql, array(
            "member_id" => $member_id,
            "status"    => ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE),
            "type_id"   => self::PROJECT_TYPE_STANDARD,
        ), false
        );
    }

    /**
     * By default it will show all projects for a member included the unpublished elements.
     *
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     * @param bool     $onlyActiveProjects
     *
     * @param null     $catids
     *
     * @return array
     */
    public function fetchAllProjectsForMemberCatFilter(
        $member_id,
        $limit = null,
        $offset = null,
        $onlyActiveProjects = false,
        $catids = null
    ) {
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
                AND `project`.`type_id` = :type_id";
        if (isset($catids)) {
            $sql .= " AND project_category_id in (' . $catids . ')";
        }
        if (isset($limit)) {
            $sql .= " LIMIT " . $limit . " OFFSET " . $offset;
        }
        $sql .= " ORDER BY project_changed_at DESC";

        return $this->fetchAll(
            $sql, array(
                    "member_id" => $member_id,
                    "status"    => ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE),
                    "type_id"   => self::PROJECT_TYPE_STANDARD,
                )
        );

    }

    /**
     * @param $collection_id
     *
     * @return null|array
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
        $result = $this->fetchRow(
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
     * @param     $project
     * @param int $count
     *
     * @return array
     */
    public function fetchSimilarProjects($project, $count = 10)
    {
        $count = (int)$count;
        $sql = "
                SELECT *
                FROM `stat_projects` AS `p`
                WHERE `p`.`project_category_id` = :cat_id AND `project_id` <> :project_id
                ORDER BY `p`.`changed_at` DESC
                LIMIT {$count}
        ";

        return $this->fetchAll(
            $sql, array(
                    'cat_id'     => $project->project_category_id,
                    'project_id' => $project->project_id,
                )
        );
    }

    /**
     * @param stdClass $project
     * @param int      $count
     *
     * @return ResultInterface
     * @throws Exception
     */
    public function fetchMoreProjects($project, $count = 6)
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

       
       $result = $this->fetchAll($sql,['project_id' =>(int)$project->project_id
                                        ,'member_id' => (int)$project->member_id
                                        ,'project_category_id' => (int)$project->project_category_id
                                ],false);

                              
        return $result;
    }

    // /**
    //  * @param stdClass $project
    //  * @param int      $count
    //  *
    //  * @return ResultInterface
    //  * @throws Exception
    //  */
    // public function fetchMoreProjects_($project, $count = 6)
    // {
    //     $sql = new Sql($this->db);
    //     $q = $sql->select('stat_projects')->columns(
    //         array(
    //             'project_id',
    //             'image_small',
    //             'title',
    //             'project_category_id',
    //             'catTitle' => 'cat_title',
    //             'changed_at',
    //         )
    //     )->where('status = ' . self::PROJECT_ACTIVE)->where('member_id = ' . $project->member_id)
    //              ->where('project_id != ' . $project->project_id)->where('type_id = ' . self::PROJECT_TYPE_STANDARD)
    //              ->where('amount_reports is null')->where('project_category_id = ' . $project->project_category_id)
    //              ->limit($count)->order('project_created_at DESC');

    //     $tagFilter = isset($GLOBALS['ocs_config_store_tags']) ? $GLOBALS['ocs_config_store_tags'] : null;

    //     if ($tagFilter) {
    //         $q = $this->generateTagFilter($q, array(self::FILTER_NAME_TAG => $tagFilter));
    //     }

    //     $statement = $sql->prepareStatementForSqlObject($q);

    //     return $statement->execute();
    // }

    /**
     * @param Select $statement
     * @param array  $filterArrayValue
     *
     * @return Select
     */
    //protected function generateTagFilter(\Laminas\Db\Sql\Select $statement, $filterArrayValue)
    public function generateTagFilter(Select $statement, $filterArrayValue)
    {

        if (false == isset($filterArrayValue[self::FILTER_NAME_TAG])) {
            return $statement;
        }

        $filter = $filterArrayValue[self::FILTER_NAME_TAG];
        if (sizeof($filter) == 0) {
            return $statement;
        }

        if (is_array($filter) && sizeof($filter) > 0) {
            //$sql = new Sql($this->db);

            $tagList = $filter;
            //build where statement für projects
            //$selectAnd = $this->select()->from(array('project' => 'stat_projects'));
            //$selectAnd = $sql->select('stat_projects');

            $whereString = "(";

            if (count($tagList) > 1) {
                foreach ($tagList as $item) {
                    #and
                    $whereString .= ' find_in_set(' . $item . ', tag_ids) AND ';
                }
                $whereString .= ' 1=1 ';
            } else {
                $whereString .= ' find_in_set(' . $tagList[0] . ', tag_ids) ';
            }
            $whereString .= ')';
            $statement->where($whereString);

        } else {
            $statement->where('find_in_set(' . $filter . ', tag_ids)');
        }

        return $statement;
    }

    /**
     * @param     $project
     * @param int $count
     *
     * @return ResultInterface
     * @throws Exception
     * @throws Exception
     * @replace stat_projects with projects
     */
    public function fetchMoreProjectsOfOtherUsr($project, $count = 8)
    {
        // get random offset
        $tagFilter = isset($GLOBALS['ocs_config_store_tags']) ? $GLOBALS['ocs_config_store_tags'] : null;       
         $sql = 'select count(1) as count from 
                    (
                    select p.project_id                    
                    from project p 
                    inner join member m on p.member_id = m.member_id and m.is_active = 1 and m.is_deleted = 0
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
         
        $result = $this->fetchRow($sql,['member_id' => $project->member_id
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
                    inner join member m on p.member_id = m.member_id and m.is_active = 1 and m.is_deleted = 0
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
                  
        $result = $this->fetchAll($sql,['member_id' => $project->member_id
                                        ,'project_category_id' => $project->project_category_id],false);
                                                                              
        return $result;

    }


    // /**
    //  * @param     $project
    //  * @param int $count
    //  *
    //  * @return ResultInterface
    //  * @throws Exception
    //  * @throws Exception
    //  * @todo improve processing speed
    //  */
    // public function fetchMoreProjectsOfOtherUsr_($project, $count = 8)
    // {
    //     $sql = "
    //             SELECT COUNT(1) AS `count`
    //             FROM `stat_projects`
    //             WHERE `status` = :current_status
    //               AND `member_id` <> :current_member_id
    //               AND `project_category_id` = :category_id
    //               AND `type_id` = :project_type
    //     ";

    //     $result = $this->fetchRow(
    //         $sql, array(
    //                 'current_status'    => self::PROJECT_ACTIVE,
    //                 'current_member_id' => $project->member_id,
    //                 'category_id'       => $project->project_category_id,
    //                 'project_type'      => self::PROJECT_TYPE_STANDARD,
    //             )
    //     );

    //     if ($result['count'] > $count) {
    //         $offset = rand(0, $result['count'] - $count);
    //     } else {
    //         $offset = 0;
    //     }

    //     $sql = new Sql($this->db);
    //     $q = $sql->select('stat_projects')->columns(
    //         array(
    //             'project_id',
    //             'image_small',
    //             'title',
    //             'project_category_id',
    //             'catTitle' => 'cat_title',
    //             'changed_at',
    //         )
    //     )->where('status = ' . self::PROJECT_ACTIVE)->where('member_id != ' . $project->member_id)
    //              ->where('type_id = ' . 1)->where('amount_reports is null')
    //              ->where('project_category_id = ' . $project->project_category_id)->limit($count)->offset($offset)
    //              ->order('project_created_at DESC');

    //     $tagFilter = isset($GLOBALS['ocs_config_store_tags']) ? $GLOBALS['ocs_config_store_tags'] : null;
    //     if ($tagFilter) {
    //         $q = $this->generateTagFilter($q, array(self::FILTER_NAME_TAG => $tagFilter));
    //     }

    //     $statement = $sql->prepareStatementForSqlObject($q);

    //     $result = $statement->execute();

    //     return $result;
    // }

    /**
     * @param $projectId
     *
     * @return array
     */
    public function getGalleryPictureSources($projectId)
    {
        $galleryPictureTable = new ProjectGalleryPictureRepository($this->db);
        $sql = "select * from " . $galleryPictureTable->getName() . " where project_id = " . (int)$projectId . " order by sequence";

        $glleryPics = $galleryPictureTable->fetchAll($sql);

        $pics = array();
        foreach ($glleryPics as $pictureRow) {
            $pics[] = $pictureRow['picture_src'];
        }

        return $pics;
    }

    /**
     * @param int $project_id
     *
     * @return array
     * @throws Exception
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
     * @throws Exception
     */
    public function fetchOverallPageViewsByMember($member_id)
    {
        $sql = "
                SELECT SUM(`stat`.`amount`) AS `page_views`
                FROM `project`
                JOIN (SELECT `project_id`, COUNT(`project_id`) AS `amount` FROM `stat_page_views` GROUP BY `project_id`) AS `stat` ON `stat`.`project_id` = `project`.`project_id`
                WHERE `project`.`member_id` = :member_id AND `project`.`status` = :project_status
                GROUP BY `member_id`
              ";

        $result = $this->fetchRow($sql, array('member_id' => $member_id, 'project_status' => self::PROJECT_ACTIVE));
        if (count($result) > 0) {
            return $result['page_views'];
        } else {
            return 0;
        }
    }

    /**
     * @param int $project_id
     *
     * @return bool
     */
    public function isProjectFeatured($project_id)
    {
        $sql_object = "SELECT `project_id` FROM `project` WHERE `project_id`= :project_id AND  `status` = 100 AND `type_id` = 1 AND `featured` = 1";
        $r = $this->fetchRow($sql_object, array('project_id' => $project_id));
        if ($r) {
            return true;
        }

        return false;
    }

    /**
     * @param int $project_id
     *
     * @return bool
     */
    public function isProjectClone($project_id)
    {
        $sql_object = "SELECT `c`.`project_clone_id` FROM `project_clone` `c`
                WHERE `c`.`is_valid` = 1
                AND `c`.`is_deleted` = 0
                AND `c`.`project_id_parent` IS NOT NULL
                AND `c`.`project_id` = :project_id";
        $r = $this->fetchRow($sql_object, array('project_id' => $project_id));
        if ($r) {
            return true;
        }

        return false;
    }

    /**
     * @param $id
     *
     * @throws Exception
     */
    public function setDeletedInMaterializedView($id)
    {
        $sql = "UPDATE `stat_projects` SET `status` = :new_status WHERE `project_id` = :project_id";
        $this->db->query($sql, array('new_status' => self::PROJECT_DELETED, 'project_id' => $id));
    }

    /**
     * @param array    $inputFilterParams
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     * @throws Exception
     */
    public function fetchProjectsByFilter($inputFilterParams, $limit = null, $offset = null)
    {
        $cacheName = __FUNCTION__ . '_' . md5(serialize($inputFilterParams) . (string)$limit . (string)$offset);

        if (false == isset($inputFilterParams[self::FILTER_NAME_FAVORITE])) {
            // if filter favourite no cache. 
            if ($returnValue = $this->readCache($cacheName)) {
                return $returnValue;
            }    
        }
        
        $statement = $this->generateStatement($inputFilterParams, $limit, $offset);

        /*
        if (APPLICATION_ENV == 'development') {
            $GLOBALS['ocs_log']->debug(__METHOD__ . ' - ' . $statement->__toString());
        }*/

        $sql = new Sql($this->db);
        $sqlStatement = $sql->prepareStatementForSqlObject($statement);

        $fetchedElements = $sqlStatement->execute();
        $resultSet = new ResultSet();
        $resultSet->initialize($fetchedElements);
        $fetchedElements = $resultSet->toArray();

        //$fetchedElements = $this->fetchAll($statement);
        $statement->reset('limit')->reset('offset')->reset('order');
        $statement->reset('columns')->columns(array('count' => new Expression('count(*)')));

        $sqlStatement = $sql->prepareStatementForSqlObject($statement);

        $countElements = $this->fetchRow($sqlStatement->getSql());
        $returnValue = array('elements' => $fetchedElements, 'total_count' => $countElements['count']);

        $this->writeCache($cacheName, $returnValue, 300);

        return $returnValue;
    }

    /**
     * @param array    $inputFilterParams
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return Select
     */
    protected function generateStatement($inputFilterParams, $limit = null, $offset = null)
    {
        $statement = $this->generateBaseStatement();
        $statement = $this->generateCategoryFilter($statement, $inputFilterParams);
        $statement = $this->generateOrderFilter($statement, $inputFilterParams);
        $statement = $this->generateTagFilter($statement, $inputFilterParams);
        $statement = $this->generateFavoriteFilter($statement, $inputFilterParams);
        $statement = $this->generateReportedSpamFilter($statement);
        $statement->limit($limit)->offset($offset);

        return $statement;
    }

    /**
     * @return Select
     */
    protected function generateBaseStatement()
    {
        $sql = new Sql($this->db);

        $statement = $sql->select('stat_projects');

        /*$statement->from(array('project' => 'stat_projects'), array(
            '*'
        ));
         */
        $statement->where('status = ' . self::PROJECT_ACTIVE)
                  ->where('type_id IN (' . self::PROJECT_TYPE_STANDARD . ',' . self::PROJECT_TYPE_COLLECTION . ')');

        return $statement;
    }

    /**
     * @param Select $statement
     * @param array  $filterArrayValue
     *
     * @return Select
     */
    protected function generateCategoryFilter(Select $statement, $filterArrayValue)
    {
        if (false == isset($filterArrayValue[self::FILTER_NAME_CATEGORY])) {
            return $statement;
        }

        $filter = $filterArrayValue[self::FILTER_NAME_CATEGORY];

        if (false === is_array($filter)) {
            $filter = array($filter);
        }

        // fetch child elements for each category
        $modelProjectCategories = new ProjectCategoryRepository($this->db, $GLOBALS['ocs_cache']);
        $childElements = $modelProjectCategories->fetchChildIds($filter);
        $allCategories = array_unique(array_merge_recursive($filter, $childElements));
        $stringCategories = implode(',', $allCategories);

        $statement->where("(project_category_id IN (" . $stringCategories . "))");

        return $statement;
    }

    /**
     * @param Select $statement
     * @param array  $filterArrayValue
     *
     * @return Select
     */
    protected function generateOrderFilter(Select $statement, $filterArrayValue)
    {
        if (!isset($filterArrayValue[self::FILTER_NAME_ORDER])) {
            $filterValue = '';
        } else {
            $filterValue = $filterArrayValue[self::FILTER_NAME_ORDER];
        }
        switch ($filterValue) {
            case 'latest':
                $statement->order('major_updated_at DESC');
                //$statement->order('project.changed_at DESC');
                break;

            case 'rating':
                //$statement->order(array('amount_received DESC', 'count_plings DESC', 'latest_pling DESC', 'project.created_at DESC'));
                //$statement->order(array(new Zend_Db_Expr('(round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100) DESC'),'amount_received DESC', 'count_plings DESC', 'latest_pling DESC', 'project.created_at DESC'));
                /*$statement->order(array(
                    new Zend_Db_Expr('(round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100) DESC'),
                    'project.created_at DESC'
                ));*/ $statement->order('laplace_score DESC');
                break;
            case 'plinged':
                $statement->order('count_plings DESC');
                break;
            case 'test':
                $statement->order('laplace_score_test DESC');
                break;
            case 'top':
                $statement->order('laplace_score_old DESC');
                break;
            case 'download':
                $statement->order('count_downloads_hive DESC');
                break;
            case 'downloadQuarter':
                $statement->order('count_downloads_quarter DESC');
                break;


            case 'hot':

                $statement->order(
                    array(
                        'laplace_score DESC',
                        'count_plings DESC',
                        'created_at DESC',
                    )
                );
                $statement->where(' created_at >= (NOW()- INTERVAL 14 DAY)');
                break;

            case 'alpha':
            default:
                $statement->order('title');
        }

        return $statement;
    }

    /**
     * @param Select $statement
     * @param array  $filterArrayValue
     *
     * @return Select
     */
    protected function generateFavoriteFilter(Select $statement, $filterArrayValue)
    {
        if (false == isset($filterArrayValue[self::FILTER_NAME_FAVORITE])) {
            return $statement;
        }

        $filterMemberId = $filterArrayValue[self::FILTER_NAME_FAVORITE];

        if (null != $filterMemberId) {
            $statement->where('project_follower.member_id = ' . $filterMemberId);
            $statement->join('project_follower', 'stat_projects.project_id = project_follower.project_id', array('project_follower_id'));
        }

        return $statement;
    }

    /**
     * @param Select $statement
     *
     * @return Select
     */
    protected function generateReportedSpamFilter(Select $statement)
    {
        return $statement->where('(amount_reports is null)');
    }

    public function fetchMainProject($member_id)
    {
        return $this->fetchAllRows(['type_id' => self::PROJECT_TYPE_PERSONAL, 'member_id' => (int)$member_id])
                    ->current();
    }

    /**
     * @param bool $in_current_store
     *
     * @return int
     * @throws Exception
     */
    public function fetchTotalProjectsCount($in_current_store = false)
    {
        $sql = "SELECT COUNT(1) AS `total_project_count` FROM `stat_projects`";
        if ($in_current_store) {
            $store_tags = $GLOBALS['ocs_config_store_tags'];
            //$store_tags = null;

            $activeCategories = $this->getActiveCategoriesForCurrentHost();
            $sql .= ' WHERE project_category_id IN (' . implode(',', $activeCategories) . ')';
            //$sql .= ' WHERE 1=1';

            //Store Tag Filter
            if ($store_tags) {
                $tagList = $store_tags;
                //build where statement für projects
                $sql .= " AND (";

                if (!is_array($tagList)) {
                    $tagList = array($tagList);
                }

                foreach ($tagList as $item) {
                    #and
                    $sql .= ' find_in_set(' . $item . ', tag_ids) AND ';
                }
                $sql .= ' 1=1)';
            }

        }
        $result = $this->fetchRow($sql);

        return (int)$result['total_project_count'];
    }

    /**
     * @param int $omitCategoryId
     *
     * @return array
     * @TODO: check all occurrences of this function
     */
    public function getActiveCategoriesForCurrentHost($omitCategoryId = null)
    {
        $currentHostMainCategories = $GLOBALS['ocs_store_category_list'];


        $modelCategory = new ProjectCategoryRepository($this->db, $GLOBALS['ocs_cache']);
        $activeChildren = $modelCategory->fetchChildIds($currentHostMainCategories);
        $activeCategories = array_unique(array_merge($currentHostMainCategories, $activeChildren));

        if (empty($omitCategoryId)) {
            return $activeCategories;
        }

        $omitChildren = $modelCategory->fetchChildIds($omitCategoryId);

        return array_diff($activeCategories, $omitChildren);
    }

    public function fetchProjectInfoTabCnt($project_id)
    {
        $sql = "
                    SELECT COUNT(1) AS `cnt` FROM `project_rating` `p` WHERE `project_id` = :project_id AND `rating_active` = 1
                    UNION ALL
                    SELECT COUNT(1) AS `cnt` FROM `project_plings` `f`
                    INNER JOIN `member` `m` ON `f`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
                    WHERE  `f`.`project_id` = :project_id AND `f`.`is_deleted` = 0     
                    UNION ALL                    
                    SELECT  COUNT(1) AS `cnt` FROM `project_follower` `f`
                    INNER JOIN `member` `m` ON `f`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
                    WHERE  `f`.`project_id` = :project_id
                    UNION ALL
                    SELECT 
                    COUNT(1) AS `cnt` FROM `section_support_paypements` `p`
                    INNER JOIN `section_support` `f` ON `f`.`section_support_id` = `p`.`section_support_id`
                    INNER JOIN `support` `s` ON `s`.`id` = `f`.`support_id`
                    INNER JOIN `member` `m` ON `s`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
                    WHERE  `f`.`project_id` =  :project_id
                    AND `p`.`yearmonth` = DATE_FORMAT((NOW()),'%Y%m')
                    UNION ALL
                    SELECT COUNT(1) AS `cnt` FROM `project_updates`
                    WHERE `project_id` = :project_id AND `public` = 1
                    UNION ALL 
                    SELECT 
                    COUNT(1) AS `cnt`
                    FROM `comments`                               
                    WHERE `comments`.`comment_active` = 1                           
                    AND `comments`.`comment_target_id` = :project_id
                    AND `comments`.`comment_type`=50
                    UNION ALL 
                    SELECT 
                    COUNT(1) AS `cnt`
                    FROM `comments`                                    
                    WHERE `comments`.`comment_active` = 1       
                    AND `comments`.`comment_target_id` = :project_id
                    AND `comments`.`comment_type`=30
                    UNION ALL 
                    SELECT 
                    COUNT(1) AS `cnt`
                    FROM `comments`                                   
                    WHERE `comments`.`comment_active` = 1       
                    AND `comments`.`comment_target_id` = :project_id
                    AND `comments`.`comment_type`=0
                    UNION ALL
                    SELECT COUNT(1) AS `cnt` FROM `collection_projects` WHERE `active` = 1 AND `project_id` = :project_id
        ";
        $result = $this->fetchAll(
            $sql, array(
                    'project_id' => (int)$project_id,
                )
        );

        $return = [];
        $return['cntRatings'] = (int)$result[0]['cnt'];
        $return['cntPlings'] = (int)$result[1]['cnt'];
        $return['cntLikes'] = (int)$result[2]['cnt'];
        $return['cntAffiliates'] = (int)$result[3]['cnt'];
        $return['cntUpdates'] = (int)$result[4]['cnt'];
        $return['cntCommentsLic'] = (int)$result[5]['cnt'];
        $return['cntCommentsMod'] = (int)$result[6]['cnt'];
        $return['cntCommentsPro'] = (int)$result[7]['cnt'];
        $return['cntCollections'] = (int)$result[8]['cnt'];

        return $return;
    }
}