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

use Application\Model\Interfaces\ProjectInterface;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Service\Interfaces;
use Exception;
use Laminas\Db\ResultSet\ResultSet;

interface ProjectServiceInterface
{
    /**
     * @param int $status
     * @param int $id
     *
     * @throws Exception
     */
    public function setStatus($status, $id);

    /**
     * @param int $member_id
     * @param int $id
     */
    public function setClaimedByMember($member_id, $id);

    /**
     * @param int $id
     */
    public function resetClaimedByMember($id);

    /**
     * @param int $id
     */
    public function transferClaimToMember($id);

    /**
     * @param int $project_id
     * @param     $member_id
     *
     * @throws Exception
     */
    public function setInActive($project_id, $member_id);

    /**
     * @param int $id
     *
     * @return mixed
     * @throws Exception
     */
    public function fetchActiveBySourcePk($id);

    /**
     * @param int  $member_id
     * @param bool $onlyActiveProjects
     *
     * @return mixed
     */
    public function countAllProjectsForMember($member_id, $onlyActiveProjects = false);

    /**
     * @param int  $member_id
     * @param bool $onlyActiveProjects
     * @param      $catids
     *
     * @return mixed
     * @throws Exception
     */
    public function countAllProjectsForMemberCatFilter($member_id, $onlyActiveProjects = false, $catids = null);

    /**
     * By default it will show all projects for a member included the unpublished elements.
     *
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     * @param bool     $onlyActiveProjects
     *
     * @return resultset
     */
    public function fetchAllProjectsForMember($member_id, $limit = null, $offset = null, $onlyActiveProjects = false);

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
    );

    /**
     * @param $collection_id
     *
     * @return null|array
     */
    public function fetchProductForCollectionId($collection_id);

    /**
     * @param $project_id
     *
     * @return array
     * @deprecated
     */
    public function fetchProjectUpdates($project_id);

    /**
     * @param $project_id
     *
     * @return array
     * @deprecated
     */
    public function fetchAllProjectUpdates($project_id);

    /**
     * @param     $project
     * @param int $count
     *
     * @return array
     */
    public function fetchSimilarProjects($project, $count = 10);

    /**
     * @param ProjectInterface $project
     * @param int              $count
     *
     * @return array
     * @throws Exception
     */
    public function fetchMoreProjects($project, $count = 6);

    /**
     * @param     $project
     * @param int $count
     *
     * @return array
     * @throws Exception
     * @todo improve processing speed
     */
    public function fetchMoreProjectsOfOtherUsr($project, $count = 8);

    /**
     * @param int $project_id
     *
     * @return array
     */
    public function fetchProjectSupporter($project_id);

    /**
     * @param int $project_id
     *
     * @return array
     */
    public function fetchProjectSupporterWithPlings($project_id);

    /**
     * @param $projectId
     * @param $sources
     */
    public function updateGalleryPictures($projectId, $sources);

    /**
     * @param $projectId
     *
     * @return array
     */
    public function getGalleryPictureSources($projectId);

    /**
     * @param int $project_id
     *
     * @return array
     * @throws Exception
     */
    public function fetchProjectViews($project_id);

    /**
     * @param int $member_id
     *
     * @return int
     * @throws Exception
     */
    public function fetchOverallPageViewsByMember($member_id);

    /**
     * @return array
     * @throws Exception
     * @deprecated
     */
    public function getStatsForNewProjects();

    /**
     * @param int      $idCategory
     * @param int|null $limit
     *
     * @return array
     * @throws Exception
     * @deprecated
     */
    public function fetchProductsByCategory($idCategory, $limit = null);

    /**
     * @param int|array $idCategory id of a category or an array of id's
     * @param bool      $withSubCat if was set true it will also count products in sub categories
     * @param null      $store_id
     *
     * @return int count of products in given category
     * @throws Exception
     * @deprecated
     */
    public function countProductsInCategory($idCategory = null, $withSubCat = true, $store_id = null);

    /**
     * @param int|array $idCategory
     *
     * @return int
     * @throws Exception
     * @deprecated
     */
    public function countActiveMembersForCategory($idCategory);

    /**
     * @param int $project_id
     *
     * @return bool
     */
    public function isProjectFeatured($project_id);

    /**
     * @param int $project_id
     *
     * @return bool
     */
    public function isProjectClone($project_id);

    /**
     * @param bool $in_current_store
     *
     * @return int
     * @throws Exception
     */
    public function fetchTotalProjectsCount($in_current_store = false);

    /**
     * @param $member_id
     *
     * @throws Exception
     */
    public function setAllProjectsForMemberDeleted($member_id);

    /**
     * @param int $member_id
     * @param int $id
     *
     * @throws Exception
     */
    public function setDeleted($member_id, $id);

    /**
     * @param int $member_id
     *
     * @throws Exception
     */
    public function setAllProjectsForMemberActivated($member_id);

    /**
     * @param int $member_id
     * @param int $id
     *
     * @throws Exception
     */
    public function setActive($member_id, $id);

    /**
     * @param array    $inputFilterParams
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     * @throws Exception
     */
    public function fetchProjectsByFilter($inputFilterParams, $limit = null, $offset = null);

    /**
     * @param int    $member_id
     * @param array  $values
     * @param string $username
     *
     * @throws Exception
     */
    public function createProject($member_id, $values, $username);

    /**
     * @param int   $project_id
     * @param array $values
     *
     * @throws Exception
     */
    public function updateProject($project_id, $values);

    /**
     * @param int $member_id
     *
     * @return array|mixed
     */
    public function fetchMainProject($member_id);

    /**
     * @param $project_id
     *
     * @return array
     * @throws Exception
     */
    public function fetchProductDataFromMV($project_id);

    /**
     * @return array
     */
    public function fetchGhnsExcludedProjects();

    public function getUserCreatingCategories($member_id);

    /**
     * @param int  $member_id
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function getUserActiveProjects($member_id, $limit = null, $offset = null);

    /**
     * @return array
     */
    public function getUserActiveProjectsDuplicatedSourceUrl($member_id, $limit = null, $offset = null);

    /**
     * @return int
     */
    public function getOriginalProjectsForMemberCnt($member_id);

    /**
     * @param      $member_id
     * @param null $limit
     * @param null $offset
     *
     * @return Interfaces\cnt|array|ResultSet|null
     */
    public function getOriginalProjectsForMember($member_id, $limit = null, $offset = null);

    /**
     * @return int
     */
    public function getUnpublishedProjectsForMemberCnt($member_id);

    /**
     * @param      $member_id
     * @param null $limit
     * @param null $offset
     *
     * @return array|ResultSet|null
     */
    public function getUnpublishedProjectsForMember($member_id, $limit = null, $offset = null);

    /**
     * @return int
     */
    public function getDeletedProjectsForMemberCnt($member_id);

    /**
     * @param      $member_id
     * @param null $limit
     * @param null $offset
     *
     * @return array|ResultSet|null
     */
    public function getDeletedProjectsForMember($member_id, $limit = null, $offset = null);

    /**
     * @param $project_id
     *
     * @return array|ResultSet
     */
    public function fetchFilesForProject($project_id);

    /**
     * @param int  $member_id
     * @param null $limit
     * @param null $offset
     *
     * @return array|ResultSet|null
     */
    public function fetchAllFeaturedProjectsForMember($member_id, $limit = null, $offset = null);
  /**
     * @param int      $member_id  
     *
     * @return int
     */
    public function fetchAllFeaturedProjectsForMemberCnt($member_id);
    /**
     * @param int  $member_id
     * @param null $limit
     * @param null $offset
     *
     * @return array|ResultSet|null
     */
    public function fetchAllCollectionsForMember($member_id, $limit = null, $offset = null);
/**
     * @param int      $member_id     
     * @return int
     */
    public function fetchAllCollectionsForMemberCnt($member_id);
    /**
     * @param string   $order
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function fetchDuplicatedSourceProjects($order = 'source_url asc', $limit = null, $offset = null);

    /**
     * @return mixed
     */
    public function getTotalCountDuplicates();

    /**
     * @param string $source_url
     *
     * @return mixed
     */
    public function getCountSourceUrl($source_url);

    /**
     * @param $source_url
     *
     * @return array
     */
    public function getSourceUrlProjects($source_url);

    /**
     * @param int $member_id
     *
     * @return mixed
     */
    public function getCountProjectsDuplicateSourceurl($member_id);

    /**
     * @param $ids
     *
     * @return array
     * @throws Exception
     */
    public function fetchProjects($ids);

    /**
     * @param int $project_id
     *
     * @return true/false
     * @throws Exception
     */
    public function isAllowedForDeletion($project_id);

    /**
     * @param int      $projectId
     * @param int      $member_id
     * @param int      $score
     * @param int|null $msg comment
     * @param int      $comment_id
     */
    public function scoreForProject($projectId, $member_id, $score, $msg, $comment_id);

    /**
     * @param int      $projectId
     * @param int      $member_id
     * @param int      $userRating
     * @param int|null $msg comment
     */
    public function rateForProject($projectId, $member_id, $userRating, $msg);

    /**
     * @param int    $projectId
     * @param int    $member_id
     * @param string $msg
     * @param int    $parent_id
     * @param int    $comment_type
     *
     * @return int
     */
    public function saveComment($projectId, $member_id, $msg, $parent_id = 0, $comment_type = 0);

    /**
     * @return ProjectRepository
     */
    public function getProjectRepository();

}