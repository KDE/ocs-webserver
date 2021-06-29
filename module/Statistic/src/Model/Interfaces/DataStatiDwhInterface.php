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

namespace Statistic\Model\Interfaces;

interface DataStatiDwhInterface
{
    public function getNewmemberstats();

    public function getNewprojectstats();

    public function getNewprojectWeeklystatsWithoutWallpapers();

    public function getNewprojectWeeklystatsWallpapers();

    public function getPayout($yyyymm);

    public function getPayoutMemberPerCategory($yyyymm, $catid);

    public function getDownloadsDaily($numofmonthback);

    public function getDownloadsUndPayoutsDaily($yyyymm);

    public function getTopDownloadsPerDate($date);

    public function getTopDownloadsPerMonth($month, $catid);

    public function getProductMonthly($project_id);

    public function getProductDayly($project_id);

    public function getDownloadsDomainStati($begin, $end);

    public function getPayoutCategoryMonthly($yyyymm);

    public function getPayoutCategory($catid);

    public function getPayoutyear();

    public function getPayoutOfMember($member_id);

    public function getProject($project_id);

    public function getProjects($limit = 50);

    public function getMember($member_id);

    public function getMembers($limit = 50);

    public function getNewcomer($yyyymm);

    public function getNewloser($yyyymm);

    public function getMonthDiff($yyyymm);
}