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

interface TagServiceInterface
{

    public function processTags($object_id, $tags, $tag_type);

    public function assignTags($object_id, $tags, $tag_type);

    public function getTags($object_id, $tag_type);

    public function deassignTags($object_id, $tags, $tag_type);

    public function isTagsUserExisting($project_id, $tagname);

    public function getTagsCategory($object_id, $tag_type);

    public function getTagsArray($object_id, $tag_type, $tag_group_ids);

    public function getTagsSystem($object_id, $tag_type);

    public function getTagsSystemList($project_id);

    public function getTagsUserCount($object_id, $tag_type);

    public function filterTagsUser($filter, $limit);

    public function processTagsUser($object_id, $tags, $tag_type);

    public function assignTagsUser($object_id, $tags, $tag_type);

    public function getTagsUser($object_id, $tag_type);

    public function deassignTagsUser($object_id, $tags, $tag_type);

    public function isProductOriginal($project_id);

    public function isProductModification($project_id);

    public function isProductDangerous($project_id);

    public function isProductDeprecatedModerator($project_id);

    public function isProductEbook($project_id);

    public function processTagProductOriginal($object_id, $is_original);

    public function processTagProductOriginalOrModification($object_id, $original);

    public function addTagUser($object_id, $tag, $tag_type);

    public function deleteTagUser($object_id, $tag, $tag_type);

    public function getTagsPerCategory($cat_id);

    public function validateCategoryTags($cat_id, $tags);

    public function updateTagsPerCategory($cat_id, $tags);

    public function updateTagsPerStore($store_id, $tags);

    public function getTagsPerGroup($groupid);

    public function getAllTagsForStoreFilter();

    public function getAllTagGroupsForStoreFilter();

    public function saveLicenseTagForProject($object_id, $tag_id);

    public function saveGhnsExcludedTagForProject($object_id, $tag_value);

    public function saveDangerosuTagForProject($object_id, $tag_value);

    public function saveDeprecatedModeratorTagForProject($object_id, $tag_value);

    public function saveArchitectureTagForProject($project_id, $file_id, $tag_id);

    public function saveFileTagForProjectAndTagGroup($project_id, $file_id, $tag_id, $tag_group_id);

    public function deleteFileTagForProject($project_id, $file_id, $tag_id);

    public function savePackageTagForProject($project_id, $file_id, $tag_id);

    public function getProjectPackageTypesString($projectId);

    public function getProjectPackageTypesPureStrings($projectId);

    public function deleteFileTagsOnProject($projectId, $fileId);

    public function deletePackageTypeOnProject($projectId, $fileId);

    public function deleteArchitectureOnProject($projectId, $fileId);

    public function getPackageType($projectId, $fileId);

    public function getFileTags($fileId);

    public function getTagsForFileAndTagGroup($projectId, $fileId, $tagGroup);

    public function getTagsEbookSubject($object_id);

    public function getTagsEbookAuthor($object_id);

    public function getTagsEbookEditor($object_id);

    public function getTagsEbookIllustrator($object_id);

    public function getTagsEbookTranslator($object_id);

    public function getTagsEbookShelf($object_id);

    public function getTagsEbookLanguage($object_id);

    public function getTagsEbookType($object_id);

    public function saveCollectionTypeTagForProject($object_id, $tag_id);

    public function fetchTagObject($tag_id, $tag_object_id, $tag_group_id, $tag_type_id);

    public function getTagGroupsOSUser();

    public function getTagGroups($tag_group_ids);

    public function saveOSTagForUser($tag_id, $tag_group_id, $member_id);

    public function deleteTagForTabObject($tag_object_id, $tag_group_id, $tag_type_id);

    public function insertTagObject($tag_ids, $tag_type_id, $tag_group_id, $tag_object_id, $tag_parent_object_id);

    public function getTagsOSUser($member_id);
}
