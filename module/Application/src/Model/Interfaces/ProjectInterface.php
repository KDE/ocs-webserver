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

namespace Application\Model\Interfaces;

use Exception;
use Laminas\Db\Sql\Select;

interface ProjectInterface extends BaseInterface
{
    /**
     * Override the insert method.
     *
     * @param array $data
     *
     * @return int
     */
    public function insert($data);

    public function setSpamChecked($projectId, $spamChecked = 1);

    /**
     * @param int $project_id
     *
     * @return null|array
     */
    public function fetchProductInfo($project_id);

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
     * @param int    $member_id
     * @param bool   $onlyActiveProjects
     * @param string $catids
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
     * @return array
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
     * @param     $project
     * @param int $count
     *
     * @return array
     */
    public function fetchSimilarProjects($project, $count = 10);

    /**
     * @param array $project
     * @param int   $count
     *
     * @return array
     * @throws Exception
     */
    public function fetchMoreProjects($project, $count = 6);

    /**
     * @param Select $statement
     * @param array  $filterArrayValue
     *
     * @return Select
     */
    public function generateTagFilter(Select $statement, $filterArrayValue);

    /**
     * @param     $project
     * @param int $count
     *
     * @return array
     * @throws Exception
     * @throws Exception
     * @todo improve processing speed
     */
    public function fetchMoreProjectsOfOtherUsr($project, $count = 8);

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
     * @param $id
     *
     * @throws Exception
     */
    public function setDeletedInMaterializedView($id);

    /**
     * @param array    $inputFilterParams
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     * @throws Exception
     */
    public function fetchProjectsByFilter($inputFilterParams, $limit = null, $offset = null);

    public function fetchMainProject($member_id);

    public function fetchProjectInfoTabCnt($project_id);
}