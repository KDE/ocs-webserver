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

interface SectionSupportInterface extends BaseInterface
{
    public function createNewSectionSupport(
        $support_id,
        $section_id,
        $amount,
        $tier,
        $period,
        $period_frequency,
        $project_id = null,
        $member_id = null,
        $project_category_id = null,
        $referer = null
    );

    public function fetchLatestSectionSupportForMember($section_id, $member_id);
    public function fetchLatestSectionSupportForProject($project_id,$member_id);
    public function fetchAllSectionSupportsForMember($section_id, $member_id);    
}