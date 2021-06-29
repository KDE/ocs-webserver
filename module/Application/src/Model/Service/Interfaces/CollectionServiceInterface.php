<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Model\Service\Interfaces;

use Application\Model\Entity\Project;
use Exception;

interface CollectionServiceInterface
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
     */
    public function setInActive($project_id, $member_id);

    /**
     * @param int  $member_id
     * @param bool $onlyActiveProjects
     *
     * @return mixed
     */
    public function countAllCollectionsForMember($member_id, $onlyActiveProjects = false);

    /**
     * @param int  $project_id
     * @param bool $onlyActiveProjects
     *
     * @return mixed
     */
    public function countAllCollectionsForProject($project_id, $onlyActiveProjects = true);

    /**
     * @param int  $member_id
     * @param bool $onlyActiveProjects
     * @param      $catids
     *
     * @return mixed
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
     */
    public function fetchAllCollectionsForMember(
        $member_id,
        $limit = null,
        $offset = null,
        $onlyActiveProjects = false
    );

    /**
     * By default it will show all projects for a project included the unpublished elements.
     *
     * @param int      $project_id
     * @param int|null $limit
     * @param int|null $offset
     * @param bool     $onlyActiveProjects
     *
     */
    public function fetchAllCollectionsForProject(
        $project_id,
        $limit = null,
        $offset = null,
        $onlyActiveProjects = true
    );

    /**
     * @param $collection_id
     *
     */
    public function fetchProductForCollectionId($collection_id);

    /**
     * @param int $project_id
     *
     */
    public function fetchProductInfo($project_id);

    /**
     * @param $project_id
     *
     */
    public function fetchProjectUpdates($project_id);

    /**
     * @param $project_id
     *
     */
    public function fetchAllProjectUpdates($project_id);

    /**
     * @param     $project
     * @param int $count
     *
     */
    public function fetchSimilarProjects($project, $count = 10);

    public function fetchMoreCollections($project, $count = 6);

    public function fetchMoreCollectionsOfOtherUsr($project, $count = 8);

    /**
     * @param int $project_id
     *
     */
    public function fetchProjectSupporter($project_id);

    /**
     * @param int $project_id
     *
     */
    public function fetchProjectSupporterWithPlings($project_id);

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
     */
    public function fetchProjectViews($project_id);

    /**
     * @param int $member_id
     *
     * @return int
     */
    public function fetchOverallPageViewsByMember($member_id);

    /**
     * @param int $project_id
     *
     * @return bool
     */
    public function isProjectFeatured($project_id);

    /**
     * @param int $member_id
     * @param int $id
     *
     */
    public function setDeleted($member_id, $id);

    /**
     * @param int $member_id
     *
     */
    public function setAllProjectsForMemberActivated($member_id);

    /**
     * @param int $member_id
     * @param int $id
     *
     */
    public function setActive($member_id, $id);

    /**
     * @param int    $member_id
     * @param array  $values
     * @param string $username
     *
     * @return Project
     * @throws Exception
     */
    public function createCollection($member_id, $values, $username);

    /**
     * @param int   $project_id
     * @param array $values
     *
     * @return Project
     * @throws Exception
     */
    public function updateCollection($project_id, $values);

    /**
     * @param int $member_id
     *
     * @return array|mixed
     */
    public function fetchMainProject($member_id);

    /**
     * @param $project_id
     *
     */
    public function fetchProductDataFromMV($project_id);

    /**
     * @return array
     */
    public function fetchGhnsExcludedProjects();

    public function getUserCreatingCategorys($member_id);

    /**
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function getUserActiveProjects($member_id, $limit = null, $offset = null);

    /**
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     *
     */
    public function fetchAllFeaturedProjectsForMember($member_id, $limit = null, $offset = null);

    /**
     * @param string   $orderby
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function fetchDuplicatedSourceProjects($orderby = 'source_url asc', $limit = null, $offset = null);

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
     * @param int $member_id
     *
     * @return mixed
     */
    public function getCountProjectsDuplicateSourceurl($member_id);

    /**
     * @param $ids
     *
     */
    public function fetchProjects($ids);

    /**
     * @param $project_id
     *
     * @return true/false
     */
    public function validateDeleteProjectFromSpam($project_id);
}