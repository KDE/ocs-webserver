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


interface StatDownloadServiceInterface
{
    public function getUserDownloads($member_id);

    public function getUserDownloadsAndViewsForMonth($member_id, $yearmonth);

    public function getUserDownloadsAndViewsForMonthAndSection($member_id, $yearmonth, $section_id = null);

    public function getUserDownloadsAndViewsForProject($member_id, $yearmonth, $section_id, $project_id);

    public function getUserAffiliatesForMonth($member_id, $yearmonth);

    public function getUserAffiliatesForMonthAndSection($member_id, $yearmonth, $section_id = null);

    public function getUserAffiliatesSumForMonth($member_id, $yearmonth);

    public function getUserDownloadsForMonth($member_id, $yearmonth);

    public function getUserDownloadsForMonthAndSection($member_id, $yearmonth, $section_id = null);

    public function getUserSectionsForMonth($member_id, $yearmonth);

    public function getUserSectionsForDownloadAndViewsForMonth($member_id, $yearmonth);

    public function getUserAffiliateSectionsForMonth($member_id, $yearmonth);

    public function getUserDownloadMonths($member_id, $year);

    public function getUserDownloadsAndViewsMonths($member_id, $year);

    public function getUserAffiliatesMonths($member_id, $year);

    public function getUserDownloadYears($member_id);

    public function getUserDownloadsAndViewsYears($member_id);

    public function getUserAffiliatesYears($member_id);

    public function getMonthEarn($member_id, $yyyymm);

    public function getLastMonthEarn($member_id);

    public function getPayoutHistory($member_id);

    public function getPayoutHistory2($member_id);
}