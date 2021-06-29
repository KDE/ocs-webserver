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

interface MemberSettingValueServiceInterface
{
    public function insert($itemid, $value, $memberid);

    public function update($itemid, $value, $memberid);

    public function updateSingle($valueid, $value);

    public function fetchMemberSettingItem($member_id, $item_id);

    public function findMemberSettings($memberid, $groupid);

    public function updateOrInsertSetting(
        $member_id,
        $member_setting_item_id,
        $member_setting_value_id,
        $value
    );
}