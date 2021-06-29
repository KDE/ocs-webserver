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

interface TagGroupServiceInterface
{
    public function fetchGroupHierarchy();

    public function fetchAllGroups();

    public function fetchGroupItems($group_id);

    public function assignGroupTag($group_id, $tag_name, $tag_fullname, $tag_description, $is_active = 1);

    public function saveTag($tag_name, $tag_fullname, $tag_description, $is_active = 1);

    public function saveGroupTag($group_id, $tag_id);

    public function fetchOneGroupItem($group_item_id);

    public function updateGroupTag($tag_id, $tag_name, $tag_fullname, $tag_description, $is_active = 1);

    public function deleteGroupTag($groupItemId);

    public function fetchTagGroupsForCategory($cat_id);

    public function updateTagGroupsPerCategory($cat_id, $taggroups);

    public function updateTagGroupsPerStore($store_id, $taggroups);
}
