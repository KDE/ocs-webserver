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

use Exception;
use Laminas\Paginator\Paginator;

interface InfoServiceInterface
{
    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getLast200ImgsProductsForAllStores($limit = 200);

    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getActiveCategoriesForAllStores($limit = null);

    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getActiveStoresForCrossDomainLogin($limit = null);

    /**
     *
     * @return int
     */
    public function countTotalActiveMembers();

    /**
     * if category id not set the latest comments for all categories on the current host wil be returned.
     *
     * @param int      $limit
     * @param int|null $project_category_id
     * @param array    $tags
     *
     * @return array
     */
    public function getLatestComments($limit = 5, $project_category_id = null, $tags = null);

    /**
     * @param int $omitCategoryId
     *
     * @return array
     * @TODO: check all occurrences of this function
     */
    public function getActiveCategoriesForCurrentHost($omitCategoryId = null);

    /**
     * @param int      $project_category_id
     * @param int|null $omitCategoryId
     *
     * @return array
     */
    public function getActiveCategoriesForCatId($project_category_id, $omitCategoryId = null);

    /**
     * if category id not set the most downloaded products for all categories on the current host wil be returned.
     *
     * @param int   $limit
     * @param null  $project_category_id
     * @param array $tags
     *
     * @return array|false|mixed
     * @throws Exception
     */
    public function getMostDownloaded($limit = 100, $project_category_id = null, $tags = null);

    /**
     * @param int         $limit
     * @param int|null    $project_category_id
     * @param array|null  $tags
     * @param string|null $tag_isoriginal
     *
     * @return array|false
     * @throws Exception
     */
    public function getLastProductsForHostStores(
        $limit = 10,
        $project_category_id = null,
        $tags = null,
        $tag_isoriginal = null
    );

    /**
     * @param int         $limit
     * @param string|null $project_category_id
     * @param array|null  $tags
     * @param string|null $tag_isoriginal
     * @param int         $offset
     *
     * @return string
     * @throws Exception
     */
    public function getJsonLastProductsForHostStores(
        $limit = 10,
        $project_category_id = null,
        $tags = null,
        $tag_isoriginal = null,
        $offset = 0
    );

    /**
     * @param int         $limit
     * @param string|null $project_category_id
     * @param array|null  $tags
     *
     * @return array|false
     * @throws Exception
     */
    public function getTopProductsForHostStores($limit = 10, $project_category_id = null, $tags = null);

    /**
     * gets a random project for this store
     *
     * @return array
     */
    public function getRandProduct();

    /**
     * get random project id as array
     *
     * @return array
     */
    public function getRandPlingedProduct();

    /**
     * get random project id as array
     *
     * @return array
     */
    public function getRandFeaturedProduct();

    /**
     * get a random project id in an array
     *
     * @return array
     */
    public function getRandomStoreProjectId();

    /**
     * get random project id as array
     *
     * @return array
     */
    public function getRandomPlingedProjectId();

    /**
     * get a random project id as array
     *
     * @return array
     */
    public function getRandomFeaturedProjectId();

    /**
     * @param int  $limit
     * @param null $project_category_id
     *
     * @return array|Paginator
     */
    public function getFeaturedProductsForHostStores($limit = 10, $project_category_id = null);

    /**
     * get last comments for actual store and member
     *
     * @param int $member_id
     * @param int $limit
     * @param int $comment_type
     *
     * @return array
     */
    public function getLastCommentsForUsersProjects($member_id, $limit = 10, $comment_type = 0);

    /**
     * get featured products for user
     *
     * @param int $member_id
     * @param int $limit
     *
     * @return array
     */
    public function getFeaturedProductsForUser($member_id, $limit = 10);

    /**
     * last votes for user
     *
     * @param int $member_id
     * @param int $limit
     *
     * @return array
     */
    public function getLastVotesForUsersProjects($member_id, $limit = 10);

    /**
     * Last spam projects for user
     *
     * @param int $member_id
     * @param int $limit
     *
     * @return array
     */
    public function getLastSpamProjects($member_id, $limit = 10);

    /**
     *
     * @param int $member_id
     * @param int $limit
     *
     * @return array
     */
    public function getLastDonationsForUsersProjects($member_id, $limit = 10);

    /**
     * @param int $limit
     *
     * @return array|false|mixed
     */
    public function getNewActiveMembers($limit = 20);

    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getSupporters($limit = 20);

    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getNewActiveSupporters($limit = 20);

    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getNewActiveSupportersForSectionAll($limit = 20);

    /**
     *
     * @param int $member_id
     *
     * @return boolean
     */
    public function isMemberActiveSupporter($member_id);

    /**
     *
     * @param int $member_id
     *
     * @return array
     */
    public function getSupporterActiveMonths($member_id);

    /**
     *
     * @param int $section_id
     *
     * @return array
     */
    public function getSectionSupportersActiveMonths($section_id);

    /**
     *
     * @param int $section_id
     * @param int $limit
     *
     * @return array
     */
    public function getNewActiveSupportersForSectionUnique($section_id, $limit = 1000);

    /**
     *
     * @param int $section_id
     *
     * @return array
     */
    public function getRandomSupporterForSection($section_id);

    /**
     *
     * @param int $section_id
     * @param int $limit
     *
     * @return array
     */
    public function getNewActiveSupportersForSection($section_id, $limit = 20);

    /**
     *
     * @param int $section_id
     * @param int $yearmonth
     * @param int $limit
     *
     * @return array
     */
    public function getNewActiveSupportersForSectionAndMonth($section_id, $yearmonth, $limit = 100);

    /**
     *
     * @param int $limit
     *
     * @return array
     */
    public function getNewActivePlingProduct($limit = 20);

    /**
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getJsonNewActivePlingProduct($limit = 20, $offset = null);

    /**
     * @param int  $limit
     * @param null $offset
     *
     * @return array|false|mixed
     */
    public function getTopScoreUsers($limit = 120, $offset = null);

    /**
     *
     * @return array
     */
    public function getMostPlingedProductsTotalCnt();

    /**
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getMostPlingedProducts($limit = 20, $offset = null);

    /**
     *
     * @param int $member_id
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getMostPlingedProductsForUser($member_id, $limit = 20, $offset = null);

    /**
     *
     * @return int
     */
    public function getMostPlingedCreatorsTotalCnt();

    /**
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getMostPlingedCreators($limit = 20, $offset = null);

    /**
     *
     * @return int
     */
    public function getCountActiveSupporters();

    /**
     *
     * @return int
     */
    public function getCountAllSupporters();

    /**
     *
     * @param int $tier
     *
     * @return int
     */
    public function getCountTierSupporters($tier);

    /**
     *
     * @param int $section_id
     *
     * @return int
     */
    public function getCountSectionSupporters($section_id);

    /**
     *
     * @param int $section_id
     * @param int $member_id
     *
     * @return int
     */
    public function getCountSupportedMonthsForSectionAndMember($section_id, $member_id);

    /**
     *
     * @return double
     */
    public function getSumSupporting();

    /**
     *
     * @param int $yearmonth
     *
     * @return double
     */
    public function getSumPayoutForMonth($yearmonth);

    /**
     *
     * @return array
     */
    public function getModeratorsList();

    /**
     *
     * @return int
     */
    public function getCountMembers();

    /**
     * gets all data from a user for the tooltip
     *
     * @param int $member_id
     *
     * @return array
     */
    public function getTooltipForMember($member_id);

    /**
     *
     * @param int $project_id
     *
     * @return double
     */
    public function getProbablyPayoutPlingsCurrentmonth($project_id);

    /**
     *
     * @return string
     */
    public function getOCSInstallInstruction();

    /**
     *
     * @param int $member_id
     *
     * @return array
     */
    public function getDiscussionOpendeskop($member_id);

    public function findProductPostion($project_id);
}