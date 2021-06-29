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

interface SectionServiceInterface
{

    public function fetchSponsorHierarchy();

    public function fetchAllSections();

    public function fetchAllSectionsAndCategories();

    public function fetchCategoriesWithPayout();

    public function fetchCategoriesWithPlinged();

    public function getNewActivePlingProduct($section_id = null);

    public function fetchTopProductsPerSection($section_id = null);

    public function fetchTopPlingedProductsPerSection($section_id = null);

    public function fetchTopPlingedProductsPerCategory($cat_id);

    public function fetchTopProductsPerCategory($cat_id);

    public function fetchProbablyPayoutLastMonth($section_id);

    public function fetchTopPlingedCreatorPerSection($section_id = null);

    public function fetchTopCreatorPerSection($section_id = null);

    public function fetchTopPlingedCreatorPerCategory($cat_id);

    public function fetchTopCreatorPerCategory($cat_id);

    public function fetchFirstSectionForStoreCategories($category_array);

    public function fetchSectionForCategory($category_id);

    public function isMemberSectionSupporter($section_id, $member_id);

    public function wasMemberSectionSupporter($section_id, $member_id);

    /**
     * @param int $yearmonth
     *
     * @return array
     */
    public function fetchAllSectionStats($yearmonth = null, $isForAdmin = false);

    /**
     * @param int  $yearmonth
     * @param int  $section_id
     * @param bool $isForAdmin
     *
     * @return array
     */
    public function fetchSectionStats($yearmonth, $section_id, $isForAdmin = false);

    /**
     * @param $section_id
     *
     * @return array
     */
    public function fetchSectionStatsLastMonth($section_id);

    /**
     * @param int  $yearmonth
     * @param      $section_id
     * @param bool $isForAdmin
     *
     * @return array
     */
    public function fetchSectionSupportStats($yearmonth, $section_id, $isForAdmin = false);

    public function fetchSection($section_id);

    public function getAllDownloadYears($isForAdmin = false);

    public function getAllDownloadMonths($year, $isForAdmin = false);
}
