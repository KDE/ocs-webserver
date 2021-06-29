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

namespace Application\Model\Service;

use Application\Model\Repository\CommentsRepository;
use Application\Model\Repository\PlingsRepository;
use Application\Model\Repository\PploadCollectionsRepository;
use Application\Model\Repository\PploadFilesRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Repository\ProjectGalleryPictureRepository;
use Application\Model\Repository\ProjectRatingRepository;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Service\Interfaces\ProjectServiceInterface;
use Exception;
use JobQueue\Jobs\DeleteProductExtended;
use JobQueue\Jobs\JobBuilder;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Exception\InvalidArgumentException;
use Laminas\Db\Sql\Expression;
use Laminas\Log\Logger;
use Library\Tools;

/**
 * Class ProjectService
 *
 * @package Application\Model\Service
 */
class ProjectService extends BaseService implements ProjectServiceInterface
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
    const PROJECT_FAULTY = 0;
    const PROJECT_INCOMPLETE = 10;
    const PROJECT_ILLEGAL = 20;
    const PROJECT_DELETED = 30;       // project data contains errors
    const PROJECT_INACTIVE = 40;  // process for adding the product was not successfully completed
    const PROJECT_ACTIVE = 100;     // project data is complete, but the project doesn't accord to our rules
    const PROJECT_CLAIMED = 1;     // owner or staff deleted the product
    const PROJECT_CLAIMABLE = 1;    // project is not visible to the world, but for the owner and staff
    const PROJECT_DEFAULT = null;     // project is active and visible to the world
    const MYSQL_DATE_FORMAT = "Y-m-d H:i:s";
    const PROJECT_SPAM_CHECKED = 1;
    const PROJECT_SPAM_UNCHECKED = 0;

    protected $db;
    protected $cache;
    protected $_allowedStatusTypes = array(
        self::PROJECT_FAULTY,
        self::PROJECT_INCOMPLETE,
        self::PROJECT_ILLEGAL,
        self::PROJECT_INACTIVE,
        self::PROJECT_ACTIVE,
        self::PROJECT_DELETED,
    );
    private $projectRepository;
    /** @var Logger */
    private $log;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
        $this->projectRepository = new ProjectRepository($this->db);
        $this->cache = $GLOBALS['ocs_cache'];
        $this->log = $GLOBALS['ocs_log'];
    }

    /**
     * @param array $productInfo
     *
     * @return array
     */
    public static function cleanProductBrowse(array $productInfo)
    {
        if (empty($productInfo)) {
            return $productInfo;
        }

        $wantedKeys = array(
            'project_id'           => 0,
            'member_id'            => 0,
            'project_category_id'  => 0,
            'title'                => 0,
            'description'          => 0,
            'version'              => 0,
            'deleted_at'           => 0,
            'image_big'            => 0,
            'image_small'          => 0,
            'created_at'           => 0,
            'changed_at'           => 0,
            'major_updated_at'     => 0,
            'creator_id'           => 0,
            'ppload_collection_id' => 0,
            'featured'             => 0,
            'ghns_excluded'        => 0,
            'count_likes'          => 0,
            'count_comments'       => 0,
            'laplace_score'        => 0,
            'laplace_score_old'    => 0,
            'laplace_score_test'   => 0,
            'username'             => 0,
            'profile_image_url'    => 0,
            'cat_title'            => 0,
            'cat_xdg_type'         => 0,
            'cat_show_description' => 0,
            'count_plings'         => 0,
            'amount_reports'       => 0,
            'package_types'        => 0,
            'package_names'        => 0,
            'tags'                 => 0,
            'tag_ids'              => 0,
            'count_follower'       => 0,
        );
        $productInfo = array_intersect_key($productInfo, $wantedKeys);

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
        if (false === in_array($status, $this->_allowedStatusTypes)) {
            throw new Exception('Wrong value for project status.');
        }

        $updateValues = array(
            'status'     => $status,
            'changed_at' => new Expression('Now()'),
        );

        if (self::PROJECT_DELETED == $status) {
            $updateValues['deleted_at'] = new Expression('NOW()');
        }

        $this->projectRepository->update($updateValues, ['project_id' => $id]);
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

        $this->projectRepository->update($updateValues, ['project_id' => $id]);
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

        $this->projectRepository->update($updateValues, ['project_id' => $id]);
    }

    /**
     * @param int $id
     */
    public function transferClaimToMember($id)
    {
        $project = $this->projectRepository->fetchProductInfo($id);

        //Update ppload
        $pploadFiles = new PploadFilesRepository($this->db);
        $updateValues = array(
            'owner_id' => $project['claimed_by_member'],
        );
        $pploadFiles->update($updateValues, "collection_id = " . $project['ppload_collection_id']);

        $pploadCollection = new PploadCollectionsRepository($this->db);
        $updateValues = array(
            'owner_id' => $project['claimed_by_member'],
        );
        $pploadCollection->update($updateValues, "id = " . $project['ppload_collection_id']);

        //And prohect
        $updateValues = array(
            'member_id'         => new Expression('claimed_by_member'),
            'claimable'         => new Expression('NULL'),
            'claimed_by_member' => new Expression('NULL'),
        );

        $this->projectRepository->update($updateValues, ['project_id' => $id, 'claimable' => 1]);

    }

    /**
     * @param int $project_id
     * @param     $member_id
     *
     * @throws Exception
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
     * @throws Exception
     */
    private function setDeletedForComments($member_id, $id)
    {
        $modelComments = new CommentsRepository($this->db);
        $modelComments->setAllCommentsForProjectDeleted($member_id, $id);
    }

    /**
     * @param int $id
     *
     * @return mixed
     * @throws Exception
     */
    public function fetchActiveBySourcePk($id)
    {
        return $this->projectRepository->fetchActiveBySourcePk($id);
    }

    /**
     * @param int  $member_id
     * @param bool $onlyActiveProjects
     *
     * @return mixed
     */
    public function countAllProjectsForMember($member_id, $onlyActiveProjects = false)
    {
        return $this->projectRepository->countAllProjectsForMember($member_id, $onlyActiveProjects);
    }

    /**
     * @param int  $member_id
     * @param bool $onlyActiveProjects
     * @param      $catids
     *
     * @return mixed
     * @throws Exception
     */
    public function countAllProjectsForMemberCatFilter($member_id, $onlyActiveProjects = false, $catids = null)
    {
        return $this->projectRepository->countAllProjectsForMemberCatFilter(
            $member_id, $onlyActiveProjects, $this->_getCatIds($catids)
        );
    }

    /**
     * @param $catids
     *
     * @return string
     * @throws Exception
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
        return $this->projectRepository->fetchAllProjectsForMember($member_id, $limit, $offset, $onlyActiveProjects);
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
     * @throws Exception
     */
    public function fetchAllProjectsForMemberCatFilter(
        $member_id,
        $limit = null,
        $offset = null,
        $onlyActiveProjects = false,
        $catids = null
    ) {
        return $this->projectRepository->fetchAllProjectsForMemberCatFilter(
            $member_id, $limit, $offset, $onlyActiveProjects, $this->_getCatIds($catids)
        );
    }

    /**
     * @param $collection_id
     *
     * @return null|array
     */
    public function fetchProductForCollectionId($collection_id)
    {
        return $this->projectRepository->fetchProductForCollectionId($collection_id);
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
     * @return array
     */
    public function fetchSimilarProjects($project, $count = 10)
    {
        return $this->projectRepository->fetchSimilarProjects($project, $count);
    }

    /**
     * @param     $project
     * @param int $count
     *
     * @return ResultInterface
     * @throws Exception
     */
    public function fetchMoreProjects($project, $count = 6)
    {
        return $this->projectRepository->fetchMoreProjects($project, $count);
    }

    /**
     * @param     $project
     * @param int $count
     *
     * @return ResultInterface
     * @throws Exception
     * @todo improve processing speed
     */
    public function fetchMoreProjectsOfOtherUsr($project, $count = 8)
    {
        return $this->projectRepository->fetchMoreProjectsOfOtherUsr($project, $count);
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
     * @param $sources
     */
    public function updateGalleryPictures($projectId, $sources)
    {
        $galleryPictureTable = new ProjectGalleryPictureRepository($this->db);
        $galleryPictureTable->clean($projectId);
        $galleryPictureTable->insertAll($projectId, $sources);
    }

    /**
     * @param $projectId
     *
     * @return array
     */
    public function getGalleryPictureSources($projectId)
    {
        return $this->projectRepository->getGalleryPictureSources($projectId);
    }

    /**
     * @param int $project_id
     *
     * @return array
     * @throws Exception
     */
    public function fetchProjectViews($project_id)
    {
        return $this->projectRepository->fetchProjectViews($project_id);
    }

    /**
     * @param int $member_id
     *
     * @return int
     * @throws Exception
     */
    public function fetchOverallPageViewsByMember($member_id)
    {
        return $this->projectRepository->fetchOverallPageViewsByMember($member_id);
    }

    /**
     * @return array
     * @throws Exception
     * @deprecated
     */
    public function getStatsForNewProjects()
    {
        return array();
    }

    /**
     * @param int      $idCategory
     * @param int|null $limit
     *
     * @return array
     * @throws Exception
     * @deprecated
     */
    public function fetchProductsByCategory($idCategory, $limit = null)
    {
        return array();
    }

    /**
     * @param int|array $idCategory id of a category or an array of id's
     * @param bool      $withSubCat if was set true it will also count products in sub categories
     * @param null      $store_id
     *
     * @return int count of products in given category
     * @throws Exception
     * @deprecated
     */
    public function countProductsInCategory($idCategory = null, $withSubCat = true, $store_id = null)
    {
        return 0;
    }

    /**
     * @param int|array $idCategory
     *
     * @return int
     * @throws Exception
     * @deprecated
     */
    public function countActiveMembersForCategory($idCategory)
    {
        return 0;
    }

    /**
     * @param int $project_id
     *
     * @return bool
     */
    public function isProjectFeatured($project_id)
    {
        return $this->projectRepository->isProjectFeatured($project_id);
    }

    /**
     * @param int $project_id
     *
     * @return bool
     */
    public function isProjectClone($project_id)
    {
        return $this->projectRepository->isProjectClone($project_id);
    }

    /**
     * @param bool $in_current_store
     *
     * @return int
     * @throws Exception
     */
    public function fetchTotalProjectsCount($in_current_store = false)
    {
        return $this->projectRepository->fetchTotalProjectsCount($in_current_store);
    }

    /**
     * @param $member_id
     *
     * @throws Exception
     */
    public function setAllProjectsForMemberDeleted($member_id)
    {
        $sql = "SELECT `project_id` FROM `project` WHERE `member_id` = :memberId AND `type_id` = :typeId AND `status` > :project_status";
        $projectForDelete = $this->projectRepository->fetchAll(
            $sql, array(
                    'memberId'       => $member_id,
                    'typeId'         => self::PROJECT_TYPE_STANDARD,
                    'project_status' => self::PROJECT_DELETED,
                )
        );
        foreach ($projectForDelete as $item) {
            $this->setDeleted($member_id, $item['project_id']);
        }

        // set personal page deleted
        $sql = "SELECT `project_id` FROM `project` WHERE `member_id` = :memberId AND `type_id` = :typeId";
        $projectForDelete = $this->projectRepository->fetchAll(
            $sql, array(
                    'memberId' => $member_id,
                    'typeId'   => self::PROJECT_TYPE_PERSONAL,
                )
        );
        foreach ($projectForDelete as $item) {
            $this->setDeleted($member_id, $item['project_id']);
        }
    }

    /**
     * @param int $member_id
     * @param int $id
     *
     * @throws Exception
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

        $product = $this->projectRepository->fetchById($id);

        // this will delete the product and request the ppload for deleting associated files
        //@formatter:off
        JobBuilder::getJobBuilder()
                  ->withJobClass(DeleteProductExtended::class)
                  ->withParam('product', $product)
                  ->build()
        ;
        //@formatter:on

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
     * @throws Exception
     */
    private function setDeletedInMaterializedView($id)
    {
        $this->projectRepository->setDeletedInMaterializedView($id);
    }

    /**
     * @param int $member_id
     *
     * @throws Exception
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
     * @throws Exception
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

        $this->setActiveForUpdates($member_id, $id);
        $this->setActiveForComments($member_id, $id);
    }

    /**
     * @param     $member_id
     * @param int $id
     */
    protected function setActiveForUpdates($member_id, $id)
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
     * @param array    $inputFilterParams
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     * @throws Exception
     */
    public function fetchProjectsByFilter($inputFilterParams, $limit = null, $offset = null)
    {
        return $this->projectRepository->fetchProjectsByFilter($inputFilterParams, $limit, $offset);
    }

    /**
     * @param int    $member_id
     * @param array  $values
     * @param string $username
     *
     * @return |ArrayObject|null
     * @throws Exception
     */
    public function createProject($member_id, $values, $username)
    {
        $values = (array)$values;

        if (empty($member_id)) {
            throw new Exception('member_id is not set');
        }
        if (empty($username)) {
            throw new Exception('username is not set');
        }
        //$now =
        // check important values for a new project
        $values['project_id'] = (empty($values['project_id'])) ? null : $values['project_id'];
        $values['uuid'] = (!array_key_exists('uuid', $values)) ? Tools\Uuid::generateUUID() : $values['uuid'];
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
        $newId = null;
        $newProject = null;
        try {

            $newId = $this->projectRepository->insert($values);

            if ($newId) {
                $newProject = $this->projectRepository->findById($newId);

            }
        } catch (Exception $exc) {
            $this->log->err($exc->getMessage() . PHP_EOL . $exc->getTraceAsString());
        }


        return $newProject;
    }

    /**
     * @param int   $project_id
     * @param array $values
     *
     * @return |ArrayObject|null
     * @throws Exception
     */
    public function updateProject($project_id, $values)
    {
        $values = (array)$values;
        $projectData = $this->projectRepository->findById($project_id);
        if (empty($projectData)) {
            throw new Exception('project_id not found');
        }

        $values['project_id'] = $project_id;
        $values['gitlab_project_id'] = empty($values['gitlab_project_id']) ? null : $values['gitlab_project_id'];
        $values['is_gitlab_project'] = empty($values['is_gitlab_project']) ? 0 : $values['is_gitlab_project'];
        $values['show_gitlab_project_issues'] = empty($values['show_gitlab_project_issues']) ? 0 : $values['show_gitlab_project_issues'];
        $values['use_gitlab_project_readme'] = empty($values['use_gitlab_project_readme']) ? 0 : $values['use_gitlab_project_readme'];

        //$updateArray = array_merge($projectData->getArrayCopy(), $values);

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
        return $this->projectRepository->fetchMainProject($member_id);
        /**
         * $sql = "SELECT * FROM {$this->_name} WHERE type_id = :type AND member_id = :member";
         *
         * //        $this->_db->getProfiler()->setEnabled(true);
         * $result = $this->projectRepository->fetchRow($sql, array('type' => self::PROJECT_TYPE_PERSONAL, 'member' => (int)$member_id));
         * //        $dummy = $this->_db->getProfiler()->getLastQueryProfile()->getQuery();
         * //        $this->_db->getProfiler()->setEnabled(true);
         *
         * if (count($result) > 0) {
         * return $result;
         * } else {
         * return array();
         * }
         */
    }

    /**
     * @param $project_id
     *
     * @return array
     * @throws Exception
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

    public function getUserCreatingCategories($member_id)
    {
        $sql = "
            SELECT
               `c`.`title` AS `category1`,
               COUNT(1) AS `cnt`
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
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function getUserActiveProjects($member_id, $limit = null, $offset = null)
    {
        $cache_name = __FUNCTION__ . '_' . $member_id . $limit . $offset;
        if ($result = $this->readCache($cache_name)) {
            return $result;
        }

        // for member me page
        $sql = "
                SELECT
                `p`.`project_id`,
                `p`.`title`,
                `p`.`created_at`  AS `project_created_at`,
                `p`.`changed_at` AS `project_changed_at`,
                `pr`.`likes` AS `count_likes`,
                `pr`.`dislikes`AS `count_dislikes`,
                IFNULL(`pr`.`score_with_pling`, 500) AS `laplace_score`,
                `p`.`member_id`,
                `cat`.`title` AS `catTitle`,
                `p`.`project_category_id`,
                `p`.`image_small`,
                (SELECT COUNT(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`,
                `c`.`cnt` `cntCategory`
                FROM `project` `p`
                JOIN `project_category` `cat` ON `p`.`project_category_id` = `cat`.`project_category_id`
                LEFT JOIN  `stat_rating_project` AS `pr`  ON `p`.`project_id` = `pr`.`project_id`
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
            $this->writeCache($cache_name, $result, 600);

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
     * @return array
     */
    public function getUserActiveProjectsDuplicatedSourceUrl($member_id, $limit = null, $offset = null)
    {
        // for member me page
        $sql = "
              SELECT * FROM
              (
              SELECT
                    `p`.`project_id`,
                    `p`.`title`,
                    `p`.`created_at`  AS `project_created_at`,
                    `p`.`changed_at` AS `project_changed_at`,
                    `pr`.`likes` AS `count_likes`,
                    `pr`.`dislikes`AS `count_dislikes`,
                     IFNULL(`pr`.`score_with_pling`, 500) AS `laplace_score`,
                    `p`.`member_id`,
                    `cat`.`title` AS `catTitle`,
                    `p`.`project_category_id`,
                    `p`.`image_small`,
                    (SELECT COUNT(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`,
                    `c`.`cnt` `cntCategory`,
                      (SELECT COUNT(1) FROM `stat_projects_source_url` `s` WHERE TRIM(TRAILING '/' FROM `p`.`source_url`)  = `s`.`source_url`) AS `cntDuplicates`
                    FROM `project` `p`
                    JOIN `project_category` `cat` ON `p`.`project_category_id` = `cat`.`project_category_id`
                    LEFT JOIN `stat_cnt_projects_catid_memberid` `c` ON `p`.`project_category_id` = `c`.`project_category_id` AND `p`.`member_id` = `c`.`member_id`
                    LEFT JOIN  `stat_rating_project` AS `pr`  ON `p`.`project_id` = `pr`.`project_id`
                    WHERE `p`.`status` =100
                    AND `p`.`type_id` = 1
                    AND `p`.`member_id` = :member_id
                    ORDER BY `cntCategory` DESC,`catTitle` ASC, `p`.`changed_at` DESC
                    ) `t` WHERE `t`.`cntDuplicates` >1
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
     * @param int $member_id
     *
     * @return int
     */
    public function getOriginalProjectsForMemberCnt($member_id)
    {
        $sql = "
                select count(1) as cnt from project p 
                inner JOIN project_category as cat ON p.project_category_id = cat.project_category_id
                inner join tag_object t on tag_id = 2451 and tag_group_id = 11 and tag_type_id = 1 and t.is_deleted = 0 and t.tag_object_id = p.project_id
                where p.status = 100 and p.is_deleted = 0 and p.member_id = :member_id         
        ";
        $result = $this->projectRepository->fetchRow($sql, array('member_id' => $member_id));
        if ($result) {
            return $result['cnt'];
        } else {
            return 0;
        }
    }

    /**
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array|ResultSet|null
     */
    public function getOriginalProjectsForMember($member_id, $limit = null, $offset = null)
    {
        $sql = "
                select p.project_id,
                p.title,
                p.created_at AS project_created_at,
                p.changed_at AS project_changed_at,
                p.project_category_id,
                cat.title as catTitle,
                p.image_small,
                IFNULL(pr.score_with_pling, 500) AS laplace_score,
                p.member_id,
                (SELECT COUNT(1) FROM project_plings as l WHERE p.project_id = l.project_id AND l.is_deleted = 0 AND l.is_active = 1 ) countplings,
                c.cnt as cntCategory
            from project p 
            inner JOIN project_category as cat ON p.project_category_id = cat.project_category_id
            inner join tag_object t on tag_id = 2451 and tag_group_id = 11 and tag_type_id = 1 and t.is_deleted = 0 and t.tag_object_id = p.project_id
            LEFT JOIN stat_cnt_projects_catid_memberid as c ON p.project_category_id = c.project_category_id AND p.member_id = c.member_id    
            LEFT JOIN  stat_rating_project AS pr  ON p.project_id = pr.project_id
            where p.status = 100 and p.is_deleted = 0 and p.member_id = :member_id     
            ORDER BY cntCategory DESC,catTitle ASC, p.changed_at DESC
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
        }

        return null;
    }

    /**
     * @param int $member_id
     *
     * @return int
     */
    public function getUnpublishedProjectsForMemberCnt($member_id)
    {
        // for member me page
        $sql = "
                SELECT
                COUNT(1) AS `cnt`
                FROM `project` `p`                        
                WHERE `p`.`status` = 40
                AND `p`.`type_id` = 1
                AND `p`.`member_id` = :member_id                        
        ";
        $result = $this->projectRepository->fetchRow($sql, array('member_id' => $member_id));
        if ($result) {
            return $result['cnt'];
        }

        return 0;
    }

    /**
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array|ResultSet|null
     */
    public function getUnpublishedProjectsForMember($member_id, $limit = null, $offset = null)
    {
        // for member me page
        $sql = "
                SELECT
                `p`.`project_id`,
                `p`.`title`,
                `p`.`created_at`  AS `project_created_at`,
                `p`.`changed_at` AS `project_changed_at`,
                `pr`.`likes` AS `count_likes`,
                `pr`.`dislikes`AS `count_dislikes`,
                IFNULL(`pr`.`score_with_pling`, 500) AS `laplace_score`,
                `p`.`member_id`,
                `cat`.`title` AS `catTitle`,
                `p`.`project_category_id`,
                `p`.`image_small`,
                (SELECT COUNT(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`
                FROM `project` `p`
                JOIN `project_category` `cat` ON `p`.`project_category_id` = `cat`.`project_category_id`
                LEFT JOIN  `stat_rating_project` AS `pr`  ON `p`.`project_id` = `pr`.`project_id`
                WHERE `p`.`status` = 40
                AND `p`.`type_id` = 1
                AND `p`.`member_id` = :member_id 
                ORDER BY `catTitle` ASC, `p`.`changed_at` DESC
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
        }

        return null;
    }

    /**
     * @param int $member_id
     *
     * @return int
     */
    public function getDeletedProjectsForMemberCnt($member_id)
    {
        // for member me page
        $sql = "
                SELECT
                COUNT(1) AS `cnt`
                FROM `project` `p`                        
                WHERE `p`.`status` = 30
                AND `p`.`type_id` = 1
                AND `p`.`member_id` = :member_id                        
        ";
        $result = $this->projectRepository->fetchRow($sql, array('member_id' => $member_id));
        if ($result) {
            return $result['cnt'];
        }

        return 0;
    }

    /**
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array|ResultSet|null
     */
    public function getDeletedProjectsForMember($member_id, $limit = null, $offset = null)
    {
        // for member me page
        $sql = "
                SELECT
                `p`.`project_id`,
                `p`.`title`,
                `p`.`created_at`  AS `project_created_at`,
                `p`.`changed_at` AS `project_changed_at`,
                `pr`.`likes` AS `count_likes`,
                `pr`.`dislikes`AS `count_dislikes`,
                IFNULL(`pr`.`score_with_pling`, 500) AS `laplace_score`,
                `p`.`member_id`,
                `cat`.`title` AS `catTitle`,
                `p`.`project_category_id`,
                `p`.`image_small`,
                (SELECT COUNT(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`
                FROM `project` `p`
                JOIN `project_category` `cat` ON `p`.`project_category_id` = `cat`.`project_category_id`
                LEFT JOIN  `stat_rating_project` AS `pr`  ON `p`.`project_id` = `pr`.`project_id`
                WHERE `p`.`status` = 30
                AND `p`.`type_id` = 1
                AND `p`.`member_id` = :member_id 
                ORDER BY `catTitle` ASC, `p`.`changed_at` DESC
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
        }

        return null;
    }

    /**
     * @param int $project_id
     *
     * @return array|ResultSet
     */
    public function fetchFilesForProject($project_id)
    {
        $sql = "
                SELECT 
                `f`.`id`
                ,`f`.`name`
                ,`f`.`type`
                ,`f`.`size`
                ,`f`.`title`
                ,`f`.`ocs_compatible`
                ,`f`.`collection_id`
                ,`f`.`tags`
                FROM `stat_projects` `p`, `ppload`.`ppload_files` `f`
                WHERE `p`.`ppload_collection_id` = `f`.`collection_id`
                AND `f`.`active` = 1 
                AND `p`.`project_id` = :project_id
        ";

        return $this->projectRepository->fetchAll($sql, array("project_id" => $project_id));
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
        $cache_name = __FUNCTION__ . '_' . $member_id . $limit . $offset;
        if ($result = $this->readCache($cache_name)) {
            return $result;
        }

        // for member me page
        $sql = "
              SELECT
              `p`.`project_id`,
              `p`.`project_category_id`,              
              `p`.`title`,
              `p`.`created_at`  AS `project_created_at`,
              `p`.`changed_at` AS `project_changed_at`,
              `p`.`count_likes`,
              `p`.`count_dislikes`,
              `p`.`laplace_score`,
              `p`.`member_id`,
              `p`.`cat_title` AS `catTitle`,
              `p`.`image_small`,
              (SELECT COUNT(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`
              FROM `stat_projects` `p`
              WHERE `p`.`status` =100
              AND `p`.`type_id` = 1
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
            $this->writeCache($cache_name, $result, 600);

            return $result;
        }

        return null;
    }

    /**
     * @param int      $member_id  
     *
     * @return int
     */
    public function fetchAllFeaturedProjectsForMemberCnt($member_id)
    {
        $cache_name = __FUNCTION__ . '_' . $member_id;
        if ($result = $this->readCache($cache_name)) {
            return $result;
        }

        // for member me page
        $sql = "
              SELECT
              count(1) as cnt
              FROM `stat_projects` `p`
              WHERE `p`.`status` =100
              AND `p`.`type_id` = 1
              AND `featured` = 1
              AND `p`.`member_id` = :member_id           
          ";

        $result = $this->projectRepository->fetchRow($sql, array('member_id' => $member_id));
        if ($result) {
            $this->writeCache($cache_name, $result['cnt'], 600);
            return $result['cnt'];
        }

        return null;
    }

    /**
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array|ResultSet|null
     */
    public function fetchAllCollectionsForMember($member_id, $limit = null, $offset = null)
    {
        $cache_name = __FUNCTION__ . '_' . $member_id . $limit . $offset;
        if ($result = $this->readCache($cache_name)) {
            return $result;
        }

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
              (SELECT COUNT(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`
              FROM `stat_projects` `p`
              WHERE `p`.`status` =100
              AND `p`.`type_id` = 3
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
            $this->writeCache($cache_name, $result, 600);

            return $result;
        }

        return null;
    }

/**
     * @param int      $member_id     
     * @return int
     */
    public function fetchAllCollectionsForMemberCnt($member_id)
    {
        $cache_name = __FUNCTION__ . '_' . $member_id ;
        if ($result = $this->readCache($cache_name)) {
            return $result;
        }

        // for member me page
        $sql = "
              SELECT
              count(1) as cnt
              FROM `stat_projects` `p`
              WHERE `p`.`status` =100
              AND `p`.`type_id` = 3
              AND `p`.`member_id` = :member_id            
          ";

        $result = $this->projectRepository->fetchRow($sql, array('member_id' => $member_id));
        if ($result) {
            $this->writeCache($cache_name, $result['cnt'], 600);
            return $result['cnt'];
        }
        return null;
    }

    /**
     * @param string   $order
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function fetchDuplicatedSourceProjects($order = 'source_url asc', $limit = null, $offset = null)
    {
        $sql = "
            SELECT
            `source_url`
            ,COUNT(1) AS `cnt`,
            GROUP_CONCAT(`p`.`project_id` ORDER BY `p`.`created_at`) `pids`
            FROM `stat_projects_source_url` `p`
            GROUP BY `source_url`
            HAVING COUNT(1)>1
       ";
        if (isset($order)) {
            $sql = $sql . '  order by ' . $order;
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
     * @return int
     */
    public function getTotalCountDuplicates()
    {
        $sql = "
          SELECT COUNT(1) AS `cnt` FROM
          (
                      SELECT
                       `source_url`
                       ,COUNT(1) AS `cnt`,
                       GROUP_CONCAT(`p`.`project_id` ORDER BY `p`.`created_at`) `pids`
                       FROM `stat_projects_source_url` `p`
                       GROUP BY `p`.`source_url`
                       HAVING COUNT(1)>1
          ) `a`
      ";
        $result = $this->projectRepository->fetchRow($sql);

        return (int)$result['cnt'];
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
            SELECT COUNT(1) AS `cnt` FROM
            `stat_projects_source_url` `p`
            WHERE `p`.`source_url`= :source_url
      ";
        $result = $this->projectRepository->fetchRow($sql, array('source_url' => $source_url));

        return $result['cnt'];
    }

    /**
     * @param $source_url
     *
     * @return array
     */
    public function getSourceUrlProjects($source_url)
    {
        $last = substr($source_url, -1);
        if ($last == '/') {
            $source_url = substr($source_url, 0, -1);
        }
        $sql = "
            SELECT
                `p`.`project_id`,
                `pj`.`title`,
                `pj`.`member_id`,
                `pj`.`created_at`,
                `pj`.`changed_at`,
                `m`.`username`
                FROM `stat_projects_source_url` `p`
                INNER JOIN `project` `pj` ON `p`.`project_id` = `pj`.`project_id` AND `pj`.`status`=100
                INNER JOIN `member` `m` ON `pj`.`member_id` = `m`.`member_id`
            WHERE `p`.`source_url`= :source_url
      ";

        return $this->projectRepository->fetchAll($sql, array('source_url' => $source_url));
    }

    /**
     * @param int $member_id
     *
     * @return mixed
     */
    public function getCountProjectsDuplicateSourceurl($member_id)
    {

        $sql = "
           SELECT COUNT(1) AS `cnt`
           FROM
           (
              SELECT  `p`.`source_url`
              ,(SELECT COUNT(1) FROM `stat_projects_source_url` `pp` WHERE `pp`.`source_url`=`p`.`source_url`) `cnt`
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
     * @return array
     * @throws Exception
     */
    public function fetchProjects($ids)
    {
        $sql = "SELECT * FROM stat_projects WHERE project_id in (" . $ids . ") order by project_id";

        return $this->projectRepository->fetchAll($sql);
    }

    /**
     * @param int $project_id
     *
     * @return bool
     * @throws Exception
     */
    public function isAllowedForDeletion($project_id)
    {
        // product should not deleted when
        // - older than 6 month
        // - or has more than 5 comments
        // - or has 1 pling or more
        $sql = '
            SELECT `count_comments`
            ,`created_at`
            ,(`created_at`+ INTERVAL 6 MONTH < NOW()) AS `is_old`
            ,(SELECT COUNT(1) FROM `project_plings` `f` WHERE `f`.`project_id` = `p`.`project_id` AND `f`.`is_deleted` = 0) AS `plings`
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
     * @param int      $projectId
     * @param int      $member_id
     * @param int      $userRating
     * @param int|null $msg comment
     */
    public function rateForProject($projectId, $member_id, $userRating, $msg)
    {
        $commentService = new ProjectCommentsService($this->db);
        $commentTable = new CommentsRepository($this->db);
        $ratingTable = new ProjectRatingRepository($this->db);

        $msg = trim($msg);
        if (strlen($msg) < 1) {
            return;
        }
        $userLikeIt = $userRating == 1 ? 1 : 0;
        $userDislikeIt = $userRating == 2 ? 1 : 0;
        $sql = 'SELECT `rating_id`,`comment_id` FROM `project_rating` WHERE `project_id`=:project_id  AND `rating_active`=1 AND `user_like`=:userlikeit AND `user_dislike`=:userdislikeit AND `member_id`=:member_id';
        $result = $ratingTable->fetchRow(
            $sql, array(
                    'project_id'    => $projectId,
                    'userlikeit'    => $userLikeIt,
                    'userdislikeit' => $userDislikeIt,
                    'member_id'     => $member_id,
                )
        );
        $is_upvote = $userRating == 1;
        $is_exist = ($result != null) && ($result['rating_id'] != null);

        if ($is_exist) {
            // this do cancel old rating .  remove rating & deactive
            $rating_id = $result['rating_id'];
            $comment_id = $result['comment_id'];
            $ratingTable->update(array('rating_active' => 0), 'rating_id=' . $rating_id);
            $commentService->deactivateComment($comment_id);

        } else {
            // this do first rating or change from - to + or + to -
            // first comment
            $data = array();
            $data['comment_target_id'] = $projectId;
            $data['comment_member_id'] = $member_id;
            $data['comment_parent_id'] = 0;
            $data['comment_text'] = $msg;
            $resultId = $commentTable->save($data);
            $comment_id = $resultId;

            // get old rating
            $sql = 'SELECT `rating_id`,`comment_id`,`user_like` FROM `project_rating` WHERE `project_id`=:project_id  AND `rating_active`=1 AND `member_id`=:member_id';
            $result = $ratingTable->fetchRow($sql, array('project_id' => $projectId, 'member_id' => $member_id));
            if ($result != null && $result['rating_id'] != null) {
                $ratingTable->update(array('rating_active' => 0), 'rating_id=' . $result['rating_id']);
                $commentService->deactivateComment($result['comment_id']);
            }

            if ($userLikeIt == 1) {
                $score = 8;
            } else {
                $score = 3;
            }
            $ratingTable->save(
                array(
                    'project_id'    => $projectId,
                    'member_id'     => $member_id,
                    'user_like'     => $userLikeIt,
                    'user_dislike'  => $userDislikeIt,
                    'score'         => $score,
                    'rating_active' => 1,
                    'comment_id'    => $comment_id,
                )
            );
        }
    }

    /**
     * @param int $projectId
     * @param int $member_id
     * @param int $score
     * @param int $msg comment
     * @param int $comment_id
     *
     * @return int rating id
     */
    public function scoreForProject($projectId, $member_id, $score, $msg, $comment_id)
    {
        if (false == isset($score)) {
            throw new InvalidArgumentException('score need to have a value');
        }
        if (empty($score)) {
            throw new InvalidArgumentException('comment_id need to have a value');
        }

        $commentService = new ProjectCommentsService($this->db);
        $ratingTable = new ProjectRatingRepository($this->db);

        $msg = trim($msg);
        $score = (int)$score;
        if (strlen($msg) < 1) {
            return null;
        }
        if ($score < 6) {
            $userLikeIt = 0;
            $userDislikeIt = 1;
        } else {
            $userLikeIt = 1;
            $userDislikeIt = 0;
        }

        // get old/current rating
        $hasCurrentRating = false;
        $sql = 'SELECT `rating_id`,`comment_id`,`score` FROM `project_rating` WHERE `project_id`=:project_id  AND `rating_active`=1 AND `member_id`=:member_id';
        $currentRating = $ratingTable->fetchRow($sql, array('project_id' => $projectId, 'member_id' => $member_id));                
        if ($currentRating != null && $currentRating['rating_id'] != null) {
            $hasCurrentRating = true;
        }

        if(!$hasCurrentRating)
        {
            // insert
            $currentRating = $ratingTable->insert(
                array(
                    'project_id'    => $projectId,
                    'member_id'     => $member_id,
                    'user_like'     => $userLikeIt,
                    'user_dislike'  => $userDislikeIt,
                    'score'         => $score,
                    'rating_active' => 1,
                    'comment_id'    => $comment_id,
                )
            );
            return $currentRating;
        }else{
            if($currentRating['score']<>$score)
            {
                 // deactivate old/current rating
                    $ratingTable->update(array('rating_active' => 0), 'rating_id=' . $currentRating['rating_id']);
                    $commentService->deactivateComment($currentRating['comment_id']);

                    // if score is lower than 0 we are finished at this point
                    if ($score <= 0) {
                        return null;
                    }

                    $currentRating = $ratingTable->insert(
                        array(
                            'project_id'    => $projectId,
                            'member_id'     => $member_id,
                            'user_like'     => $userLikeIt,
                            'user_dislike'  => $userDislikeIt,
                            'score'         => $score,
                            'rating_active' => 1,
                            'comment_id'    => $comment_id,
                        )
                    );

                    return $currentRating;
            }
        }
    
    }

    /**
     * @param int    $projectId
     * @param int    $member_id
     * @param string $msg
     * @param int    $parent_id
     * @param int    $comment_type
     *
     * @return int
     */
    public function saveComment($projectId, $member_id, $msg, $parent_id = 0, $comment_type = 0)
    {
        $commentTable = new CommentsRepository($this->db);

        $data = array();
        $data['comment_target_id'] = $projectId;
        $data['comment_member_id'] = $member_id;
        $data['comment_parent_id'] = $parent_id;
        $data['comment_type'] = $comment_type;
        $data['comment_text'] = $msg;

        return $commentTable->save($data);
    }

    /**
     * @return ProjectRepository
     */
    public function getProjectRepository()
    {
        return $this->projectRepository;
    }

    /**
     * @param ProjectRepository $projectRepository
     */
    public function setProjectRepository($projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

}