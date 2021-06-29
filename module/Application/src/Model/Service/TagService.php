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

namespace Application\Model\Service;

use Application\Model\Interfaces\TagObjectInterface;
use Application\Model\Interfaces\TagsInterface;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Service\Interfaces\TagServiceInterface;
use Laminas\Db\Adapter\Adapter;

class TagService extends BaseService implements TagServiceInterface
{
    private $tagObjectRepository;
    private $tagRepository;
    private $config;
    private $projectCategoryRepository;

    public function __construct(
        TagObjectInterface $t,
        TagsInterface $m,
        ProjectCategoryRepository $projectCategoryRepository
    ) {
        $this->tagObjectRepository = $t;
        $this->tagRepository = $m;
        $this->projectCategoryRepository = $projectCategoryRepository;
        $this->config = $GLOBALS['ocs_config'];
    }

    /**
     * @param int    $object_id
     * @param string $tags
     * @param int    $tag_type
     */
    public function processTags($object_id, $tags, $tag_type)
    {
        $this->assignTags($object_id, $tags, $tag_type);
        $this->deassignTags($object_id, $tags, $tag_type);
    }

    /**
     * @param int    $object_id
     * @param string $tags
     * @param int    $tag_type
     */
    public function assignTags($object_id, $tags, $tag_type)
    {
        $new_tags = array_diff(explode(',', $tags), explode(',', $this->getTags($object_id, $tag_type)));
        $listIds = $this->getTagRepository()->storeTags(implode(',', $new_tags));

        $prepared_insert = array_map(
            function ($id) use ($object_id, $tag_type) {
                return "({$id}, {$tag_type}, {$object_id})";
            }, $listIds
        );
        $sql = sprintf("INSERT  INTO `tag_object` (`tag_id`, `tag_type_id`, `tag_object_id`) VALUES %s", implode(',', $prepared_insert));


        $this->getAdapter()->query($sql);
    }

    /**
     * @param int $object_id
     * @param int $tag_type
     *
     * @return string|null
     */
    public function getTags($object_id, $tag_type)
    {
        $sql = "
            SELECT GROUP_CONCAT(`tag`.`tag_name`) AS `tag_names` 
            FROM `tag_object`
            JOIN `tag` ON `tag`.`tag_id` = `tag_object`.`tag_id`
            JOIN `tag_group_item` ON `tag_object`.`tag_id` = `tag_group_item`.`tag_id`
            WHERE `tag_type_id` = :type AND `tag_object_id` = :object_id
            AND `tag_group_item`.`tag_group_id` <> :tag_user_groupid
            AND `tag_object`.`is_deleted` = 0
            GROUP BY `tag_object`.`tag_object_id`
        ";

        $result = $this->getAdapter()->fetchRow(
            $sql, array(
                    'type'             => $tag_type,
                    'object_id'        => $object_id,
                    'tag_user_groupid' => TagConst::TAG_USER_GROUPID,
                )
        );
        if (isset($result['tag_names'])) {
            return $result['tag_names'];
        }

        return null;
    }

    private function getAdapter()
    {
        return $this->tagObjectRepository;
    }

    private function getTagRepository()
    {
        return $this->tagRepository;
    }

    /**
     * @param int    $object_id
     * @param string $tags
     * @param int    $tag_type
     */
    public function deassignTags($object_id, $tags, $tag_type)
    {
        $removable_tags = array_diff(explode(',', $this->getTags($object_id, $tag_type)), explode(',', $tags));

        //$sql = "DELETE tag_object FROM tag_object JOIN tag ON tag.tag_id = tag_object.tag_id WHERE tag.tag_name = :name and tag_object.tag_object_id=:object_id";
        //$sql = "UPDATE `tag_object` INNER JOIN `tag` ON `tag`.`tag_id` = `tag_object`.`tag_id`  SET `tag_changed` = NOW() , `is_deleted` = 1 WHERE `tag`.`tag_name` = :name AND `tag_object`.`tag_object_id`=:object_id";
        $sql = "UPDATE `tag_object` INNER JOIN `tag` ON `tag`.`tag_id` = `tag_object`.`tag_id`  SET `tag_changed` = NOW() , `is_deleted` = 1 
        WHERE `tag`.`tag_name` = :name AND `tag_object`.`tag_object_id`=:object_id AND `tag_object`.`tag_type_id` = :tagType";
        //$this->getAdapter()->query($sql, array('tagObjectId' => $object_id, 'tagType' => $tag_type));

        foreach ($removable_tags as $removable_tag) {
            $this->getAdapter()->query(
                $sql, array(
                        'name'      => $removable_tag,
                        'object_id' => $object_id,
                        'tagType'   => $tag_type,
                    )
            );
        }
        $this->updateChanged($object_id, $tag_type);
    }

    private function updateChanged($object_id, $tag_type)
    {
        $sql = "UPDATE `tag_object` SET `tag_changed` = NOW() WHERE `tag_object_id` = :tagObjectId AND `tag_type_id` = :tagType";
        $this->getAdapter()->query($sql, array('tagObjectId' => $object_id, 'tagType' => $tag_type));
    }

    /**
     * @param int    $project_id
     * @param string $tagname
     *
     * @return string|null
     */
    public function isTagsUserExisting($project_id, $tagname)
    {
        $sql_object = "SELECT COUNT(1) AS `cnt` FROM `tag_object` JOIN `tag` ON `tag`.`tag_id` = `tag_object`.`tag_id` WHERE `tag`.`tag_name` = :tagname AND `tag_object`.`tag_group_id`=:tag_group_id AND `tag_object`.`is_deleted`=0 AND `tag_object`.`tag_object_id`=:project_id AND `tag_object`.`tag_type_id`=:tag_type_id";
        $r = $this->getAdapter()->fetchRow(
            $sql_object, array(
                           'tagname'      => $tagname,
                           'tag_group_id' => TagConst::TAG_USER_GROUPID,
                           'project_id'   => $project_id,
                           'tag_type_id'  => TagConst::TAG_TYPE_PROJECT,
                       )
        );
        if ($r['cnt'] == 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param int $object_id
     * @param int $tag_type
     *
     * @return string|null
     */
    public function getTagsCategory($object_id, $tag_type)
    {
        $tag_group_ids = TagConst::TAG_CATEGORY_GROUPID;
        $tags = $this->getTagsArray($object_id, $tag_type, $tag_group_ids);

        $tag_names = '';
        foreach ($tags as $tag) {
            $tag_names = $tag_names . $tag['tag_name'] . ',';
        }
        $len = strlen($tag_names);
        if ($len > 0) {
            return substr($tag_names, 0, ($len - 1));
        }

        return null;
    }

    /**
     * @param int    $object_id
     * @param int    $tag_type
     * @param String $tag_group_ids
     *
     * @return array
     */
    public function getTagsArray($object_id, $tag_type, $tag_group_ids)
    {
        $sql = "
                        SELECT tag.tag_id,tag.tag_name,tag_group_item.tag_group_id,tag.tag_fullname, tag.tag_description
                        FROM tag_object
                        JOIN tag ON tag.tag_id = tag_object.tag_id
                        join tag_group_item on tag_object.tag_id = tag_group_item.tag_id and tag_object.tag_group_id = tag_group_item.tag_group_id
                        WHERE tag_type_id = :type AND tag_object_id = :object_id
                        and tag_object.tag_group_id in  ({$tag_group_ids} )     
                        and tag_object.is_deleted = 0  
                        order by tag_group_item.tag_group_id desc , tag.tag_name asc
            ";

        return $this->getAdapter()->fetchAll($sql, array('type' => $tag_type, 'object_id' => $object_id));
    }

    /**
     * @param int $object_id
     * @param int $tag_type
     *
     * @return string|null
     */
    public function getTagsSystem($object_id, $tag_type)
    {
        $tag_group_ids = TagConst::TAG_PROJECT_GROUP_IDS;
        $tags = $this->getTagsArray($object_id, $tag_type, $tag_group_ids);

        $tag_names = '';
        foreach ($tags as $tag) {
            $tag_names = $tag_names . $tag['tag_name'] . ',';
        }
        $len = strlen($tag_names);
        if ($len > 0) {
            return substr($tag_names, 0, ($len - 1));
        }

        return null;
    }

    /**
     * @param int $project_id
     *
     * @return array
     */
    public function getTagsSystemList($project_id)
    {
        $tag_project_group_ids = TagConst::TAG_PROJECT_GROUP_IDS;
        $tag_file_group_ids = TagConst::TAG_FILE_GROUP_IDS;
        $sql = "
                SELECT tag.tag_id,tag.tag_name,tag_object.tag_group_id
                FROM tag_object
                JOIN tag ON tag.tag_id = tag_object.tag_id                
                WHERE tag_type_id = :type_project AND tag_object_id = :project_id
                and tag_object.tag_group_id in  ({$tag_project_group_ids} )     
                and tag_object.is_deleted = 0  
                union all
                SELECT distinct t.tag_id,t.tag_name,o.tag_group_id
                FROM tag_object o
                JOIN tag t ON t.tag_id = o.tag_id  
                inner join project p on o.tag_parent_object_id = p.project_id                 
                inner join ppload.ppload_files f on p.ppload_collection_id = f.collection_id and o.tag_object_id=f.id and f.active = 1
                WHERE o.tag_type_id = :type_file AND p.project_id = :project_id
                and o.tag_group_id in  ({$tag_file_group_ids} )       
                and o.is_deleted = 0  
                order by tag_group_id  , tag_name                 
        ";

        return $this->getAdapter()->fetchAll(
            $sql, array(
                    'type_project' => TagConst::TAG_TYPE_PROJECT,
                    'project_id'   => $project_id,
                    'type_file'    => TagConst::TAG_TYPE_FILE,
                )
        );
    }

    /* @param int $project_category_id
    *
    * @return true/false
    */
    public function hasCategoryTagGroup($project_category_id)
    {
        $sql = "select count(1) as cnt from category_tag_group where category_id = :project_category_id";
        $row = $this->getAdapter()->fetchRow($sql,array('project_category_id'=>$project_category_id));
        return $row['cnt']>0;
    }
    /* @param int $project_id
    *
    * @return array
    */
   public function getTagsFromCategoryTagGroup($project_id)
   {      
       $sql = "       
                select distinct t.tag_group_id,g.group_name, g.group_display_name,o.tag_id,a.tag_name,a.tag_fullname
                from category_tag_group t
                inner join tag_group_item i on t.tag_group_id = i.tag_group_id
                inner join project p on t.category_id = p.project_category_id                
                inner join tag_object o on  i.tag_id = o.tag_id and o.tag_group_id = t.tag_group_id and o.tag_type_id = :type_file and  o.tag_parent_object_id = p.project_id and o.is_deleted = 0
                inner join ppload.ppload_files f on f.id = o.tag_object_id and f.active = 1
                inner join tag_group g on t.tag_group_id = g.group_id
                inner join tag a on o.tag_id = a.tag_id
                where p.project_id = :project_id 
                order by tag_fullname asc            
       ";

       return $this->getAdapter()->fetchAll(
           $sql, array(                  
                   'project_id'   => $project_id,
                   'type_file'    => TagConst::TAG_TYPE_FILE,
               )
       );
   }

    /**
     * @param int $object_id
     * @param int $tag_type
     *
     * @return string|null
     */
    public function getTagsUserCount($object_id, $tag_type)
    {
        $sql = "
            SELECT COUNT(*) AS `cnt`
            FROM `tag_object`
            JOIN `tag` ON `tag`.`tag_id` = `tag_object`.`tag_id`
            JOIN `tag_group_item` ON `tag_object`.`tag_id` = `tag_group_item`.`tag_id` AND `tag_object`.`tag_group_id` = `tag_group_item`.`tag_group_id`
            WHERE `tag_type_id` = :type AND `tag_object_id` = :object_id      
            AND `tag_object`.`is_deleted` = 0      
            AND `tag_group_item`.`tag_group_id` = :tag_user_groupid     

        ";

        $result = $this->getAdapter()->fetchRow(
            $sql, array(
                    'type'             => $tag_type,
                    'object_id'        => $object_id,
                    'tag_user_groupid' => TagConst::TAG_USER_GROUPID,
                )
        );
        if (isset($result['cnt'])) {
            return $result['cnt'];
        }

        return 0;
    }

    public function filterTagsUser($filter, $limit)
    {
        $sql = "
             select 
             tag.tag_id 
             ,tag.tag_name
             from tag
             join tag_group_item on tag.tag_id = tag_group_item.tag_id and tag_group_item.tag_group_id = :tag_user_groupid
             where tag.tag_name like '%" . $filter . "%'
               ";
        if (isset($limit)) {
            $sql .= ' limit ' . $limit;
        }

        return $this->getAdapter()->fetchAll(
            $sql, array('tag_user_groupid' => TagConst::TAG_USER_GROUPID)
        );
    }

    /**
     * @param int    $object_id
     * @param string $tags
     * @param int    $tag_type
     */
    public function processTagsUser($object_id, $tags, $tag_type)
    {
        if ($tags) {
            $this->assignTagsUser($object_id, $tags, $tag_type);
        }
        $this->deassignTagsUser($object_id, $tags, $tag_type);
    }

    /**
     * @param int    $object_id
     * @param string $tags
     * @param int    $tag_type
     */
    public function assignTagsUser($object_id, $tags, $tag_type)
    {
        $tags = strtolower($tags);
        $tag_group_id = TagConst::TAG_USER_GROUPID;
        $new_tags = array_diff(explode(',', $tags), explode(',', $this->getTagsUser($object_id, $tag_type)));

        if (sizeof($new_tags) > 0) {
            $listIds = $this->getTagRepository()->storeTagsUser(implode(',', $new_tags));

            $prepared_insert = array_map(
                function ($id) use ($object_id, $tag_type, $tag_group_id) {
                    return "({$id}, {$tag_type}, {$object_id},{$tag_group_id})";
                }, $listIds
            );
            $sql = "INSERT  INTO tag_object (tag_id, tag_type_id, tag_object_id,tag_group_id) VALUES " . implode(
                    ',', $prepared_insert
                );

            $this->getAdapter()->query($sql);
        }
    }

    /**
     * @param int $object_id
     * @param int $tag_type
     *
     * @return string|null
     */
    public function getTagsUser($object_id, $tag_type)
    {
        $tag_group_ids = TagConst::TAG_USER_GROUPID;
        $tags = $this->getTagsArray($object_id, $tag_type, $tag_group_ids);

        $tag_names = '';
        foreach ($tags as $tag) {
            $tag_names = $tag_names . $tag['tag_name'] . ',';
        }
        $len = strlen($tag_names);
        if ($len > 0) {
            return substr($tag_names, 0, ($len - 1));
        }

        return null;
    }

    public function deassignTagsUser($object_id, $tags, $tag_type)
    {
        if ($tags) {
            $tags = strtolower($tags);
            $removable_tags = array_diff(explode(',', $this->getTagsUser($object_id, $tag_type)), explode(',', $tags));
        } else {
            $removable_tags = explode(',', $this->getTagsUser($object_id, $tag_type));
        }

        //$sql = "DELETE tag_object FROM tag_object JOIN tag ON tag.tag_id = tag_object.tag_id WHERE tag_group_id = ".Default_Model_Tags::TAG_USER_GROUPID." and tag.tag_name = :name and tag_object.tag_object_id=:object_id";
        $sql = "UPDATE `tag_object` INNER JOIN `tag` ON `tag`.`tag_id` = `tag_object`.`tag_id` SET `tag_changed` = NOW() , `is_deleted` = 1 
                    WHERE `tag_group_id` = " . TagConst::TAG_USER_GROUPID . " AND `tag`.`tag_name` = :name AND `tag_object`.`tag_object_id`=:object_id";

        foreach ($removable_tags as $removable_tag) {
            $this->getAdapter()->query($sql, array('name' => $removable_tag, 'object_id' => $object_id));
            // if Tag is the only one in Tag_object table then delete this tag for user_groupid = 5

            $sql_object = "SELECT COUNT(1)  AS `cnt` FROM `tag_object` JOIN `tag` ON `tag`.`tag_id` = `tag_object`.`tag_id` WHERE `tag`.`tag_name` = :name";
            $r = $this->getAdapter()->fetchRow($sql_object, array('name' => $removable_tag));
            if ($r['cnt'] == 0) {
                // then remove tag if not existing in Tag_object
                $sql_delete_tag = "DELETE FROM `tag` WHERE `tag_name`=:name";
                $this->getAdapter()->query($sql_delete_tag, array('name' => $removable_tag));
            }
        }
        $this->updateChanged($object_id, $tag_type);
    }

    public function isProductOriginal($project_id)
    {
        $sql_object = "SELECT `tag_item_id`  FROM `tag_object` WHERE `tag_id` = :tag_id AND `tag_object_id`=:tag_object_id AND `tag_group_id`=:tag_group_id  
                                    AND `tag_type_id` = :tag_type_id AND `is_deleted` = 0";
        $r = $this->getAdapter()->fetchRow(
            $sql_object, array(
                           'tag_id'        => TagConst::TAG_PRODUCT_ORIGINAL_ID,
                           'tag_object_id' => $project_id,
                           'tag_group_id'  => TagConst::TAG_PRODUCT_ORIGINAL_GROUPID,
                           'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                       )
        );
        if ($r) {
            return true;
        } else {
            return false;
        }
    }

    public function isProductModification($project_id)
    {
        $tag_modification_id = $this->config->settings->client->default->tag_modification_id;

        $sql_object = "SELECT `tag_item_id`  FROM `tag_object` WHERE `tag_id` = :tag_id AND `tag_object_id`=:tag_object_id AND `tag_group_id`=:tag_group_id  
                                    AND `tag_type_id` = :tag_type_id AND `is_deleted` = 0";
        $r = $this->getAdapter()->fetchRow(
            $sql_object, array(
                           'tag_id'        => $tag_modification_id,
                           'tag_object_id' => $project_id,
                           'tag_group_id'  => TagConst::TAG_PRODUCT_ORIGINAL_GROUPID,
                           'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                       )
        );

        if ($r) {
            return true;
        } else {
            return false;
        }
    }

    public function isProductDangerous($project_id)
    {
        $tag_dangerous_group_id = $this->config->settings->client->default->tag_group_dangerous_id;
        $tag_dangerous_id = $this->config->settings->client->default->tag_dangerous_id;

        $sql_object = "SELECT `tag_item_id`  FROM `tag_object` WHERE `tag_id` = :tag_id AND `tag_object_id`=:tag_object_id AND `tag_group_id`=:tag_group_id  
                                    AND `tag_type_id` = :tag_type_id AND `is_deleted` = 0";
        $r = $this->getAdapter()->fetchRow(
            $sql_object, array(
                           'tag_id'        => $tag_dangerous_id,
                           'tag_object_id' => $project_id,
                           'tag_group_id'  => $tag_dangerous_group_id,
                           'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                       )
        );
        if ($r) {
            return true;
        } else {
            return false;
        }
    }

    public function isProductDeprecatedModerator($project_id)
    {
        $tag_group_id = $this->config->settings->client->default->tag_group_deprecated_id;
        $tag_id = $this->config->settings->client->default->tag_deprecated_id;

        return $this->isTagObjectExistingForProject($project_id, $tag_group_id, $tag_id);
    }

    protected function isTagObjectExistingForProject($project_id, $tag_group_id, $tag_id)
    {
        $sql_object = "select tag_item_id  from tag_object WHERE tag_id = :tag_id and tag_object_id=:tag_object_id and tag_group_id=:tag_group_id  
                                    and tag_type_id = :tag_type_id and is_deleted = 0";
        $r = $this->getAdapter()->fetchRow(
            $sql_object, array(
                           'tag_id'        => $tag_id,
                           'tag_object_id' => $project_id,
                           'tag_group_id'  => $tag_group_id,
                           'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                       )
        );
        if ($r) {
            return true;
        } else {
            return false;
        }

    }

    public function isProductEbook($project_id)
    {
        $ebookTagGroupId = $this->config->settings->client->default->tag_group_ebook;
        $ebookTagId = $this->config->settings->client->default->tag_is_ebook;

        $sql_object = "SELECT `tag_item_id`  FROM `tag_object` WHERE `tag_id` = :tag_id AND `tag_object_id`=:tag_object_id AND `tag_group_id`=:tag_group_id  
                                AND `tag_type_id` = :tag_type_id AND `is_deleted` = 0";
        $r = $this->getAdapter()->fetchRow(
            $sql_object, array(
                           'tag_id'        => $ebookTagId,
                           'tag_object_id' => $project_id,
                           'tag_group_id'  => $ebookTagGroupId,
                           'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                       )
        );
        if ($r) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $object_id
     * @param int $is_original
     */
    public function processTagProductOriginal($object_id, $is_original)
    {
        $sql_object = "select tag_item_id  from tag_object WHERE tag_id = :tag_id and tag_object_id=:tag_object_id and tag_group_id=:tag_group_id  
                                and tag_type_id = :tag_type_id and is_deleted = 0";
        $r = $this->getAdapter()->fetchRow(
            $sql_object, array(
                           'tag_id'        => TagConst::TAG_PRODUCT_ORIGINAL_ID,
                           'tag_object_id' => $object_id,
                           'tag_group_id'  => TagConst::TAG_PRODUCT_ORIGINAL_GROUPID,
                           'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                       )
        );

        if ($is_original == '1') {
            if (!$r) {
                $sql = "INSERT  INTO tag_object (tag_id, tag_type_id, tag_object_id, tag_group_id) VALUES (:tag_id, :tag_type_id, :tag_object_id, :tag_group_id)";
                $this->getAdapter()->query(
                    $sql, array(
                            'tag_id'        => TagConst::TAG_PRODUCT_ORIGINAL_ID,
                            'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                            'tag_object_id' => $object_id,
                            'tag_group_id'  => TagConst::TAG_PRODUCT_ORIGINAL_GROUPID,
                        )
                );
            }
        } else {

            if ($r) {
                $sql = "UPDATE `tag_object` SET `tag_changed` = NOW() , `is_deleted` = 1  WHERE `tag_item_id` = :tagItemId";
                $this->getAdapter()->query($sql, array('tagItemId' => $r['tag_item_id']));
            }
        }

    }

    /**
     * @param int $object_id
     * @param int $original (null, 1 or 2)
     */
    public function processTagProductOriginalOrModification($object_id, $original)
    {
        $tag_original_id = $this->config->settings->client->default->tag_original_id;
        $tag_modification_id = $this->config->settings->client->default->tag_modification_id;

        $sql_object = "select tag_item_id  from tag_object WHERE tag_object_id=:tag_object_id and tag_group_id=:tag_group_id  
                                and tag_type_id = :tag_type_id and is_deleted = 0";
        $r = $this->getAdapter()->fetchAll(
            $sql_object, array(
                           'tag_object_id' => $object_id,
                           'tag_group_id'  => TagConst::TAG_PRODUCT_ORIGINAL_GROUPID,
                           'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                       )
        );

        if ($original == '1' || $original == '2') {
            if ($r) {
                //delte all old tags
                foreach ($r as $tag) {
                    $sql = "UPDATE `tag_object` SET `tag_changed` = NOW() , `is_deleted` = 1  WHERE `tag_item_id` = :tagItemId";
                    $this->getAdapter()->query($sql, array('tagItemId' => $tag['tag_item_id']));
                }
            }

            $sql = "INSERT  INTO `tag_object` (`tag_id`, `tag_type_id`, `tag_object_id`, `tag_group_id`) VALUES (:tag_id, :tag_type_id, :tag_object_id, :tag_group_id)";
            if ($original == '1') {
                $this->getAdapter()->query(
                    $sql, array(
                            'tag_id'        => $tag_original_id,
                            'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                            'tag_object_id' => $object_id,
                            'tag_group_id'  => TagConst::TAG_PRODUCT_ORIGINAL_GROUPID,
                        )
                );
            } else {
                $this->getAdapter()->query(
                    $sql, array(
                            'tag_id'        => $tag_modification_id,
                            'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                            'tag_object_id' => $object_id,
                            'tag_group_id'  => TagConst::TAG_PRODUCT_ORIGINAL_GROUPID,
                        )
                );
            }

        } else {

            if ($r) {
                foreach ($r as $tag) {
                    $sql = "UPDATE `tag_object` SET `tag_changed` = NOW() , `is_deleted` = 1  WHERE `tag_item_id` = :tagItemId";
                    $this->getAdapter()->query($sql, array('tagItemId' => $tag['tag_item_id']));
                }
            }
        }

    }

    /**
     * @param int    $object_id
     * @param string $tag
     * @param int    $tag_type
     */
    public function addTagUser($object_id, $tag, $tag_type)
    {

        $listIds = $this->getTagRepository()->storeTagsUser($tag);
        $tag_group_id = TagConst::TAG_USER_GROUPID;
        $prepared_insert = array_map(
            function ($id) use ($object_id, $tag_type, $tag_group_id) {
                return "({$id}, {$tag_type}, {$object_id},{$tag_group_id})";
            }, $listIds
        );
        $sql = "INSERT  INTO tag_object (tag_id, tag_type_id, tag_object_id,tag_group_id) VALUES " . implode(
                ',', $prepared_insert
            );


        $this->getAdapter()->query($sql, Adapter::QUERY_MODE_EXECUTE);
    }

    public function deleteTagUser($object_id, $tag, $tag_type)
    {
        $removable_tag = $tag;
        // $sql = "DELETE tag_object FROM tag_object JOIN tag ON tag.tag_id = tag_object.tag_id WHERE tag_group_id = ".Default_Model_Tags::TAG_USER_GROUPID." and  tag.tag_name = :name and tag_object.tag_object_id=:object_id
        //             and tag_group_id =".Default_Model_Tags::TAG_USER_GROUPID;

        $sql = "UPDATE `tag_object` INNER JOIN `tag` ON `tag`.`tag_id` = `tag_object`.`tag_id` SET `tag_changed` = NOW() , `is_deleted` = 1 
                    WHERE `tag_group_id` = " . TagConst::TAG_USER_GROUPID . " AND `tag`.`tag_name` = :name AND `tag_object`.`tag_object_id`=:object_id";

        $this->getAdapter()->query($sql, array('name' => $removable_tag, 'object_id' => $object_id));
        // if Tag is the only one in Tag_object table then delete this tag for user_groupid = 5

        $sql_object = "select count(1)  as cnt from tag_object JOIN tag ON tag.tag_id = tag_object.tag_id WHERE tag.tag_name = :name ";
        $r = $this->getAdapter()->fetchRow($sql_object, array('name' => $removable_tag));
        if ($r['cnt'] == 0) {
            // then remove tag if not existing in Tag_object
            $sql_delete_tag = "DELETE FROM `tag` WHERE `tag_name`=:name";
            $this->getAdapter()->query($sql_delete_tag, array('name' => $removable_tag));
        }
        $this->updateChanged($object_id, $tag_type);
    }

    public function getTagsPerCategory($cat_id)
    {
        $sql = "select t.*  from  category_tag as c ,tag as t where c.tag_id = t.tag_id and c.category_id = :cat_id";

        return $this->getAdapter()->fetchAll($sql, array('cat_id' => $cat_id));
    }

    public function validateCategoryTags($cat_id, $tags)
    {
        if ($tags == null) {
            return true;
        }
        //check if $cat_id children has tag already
        $sql = '
            select * from category_tag where tag_id in (' . $tags . ') and category_id in
            (
                select c.project_category_id 
                from project_category c 
                join project_category d where d.project_category_id = ' . $cat_id . ' and c.lft> d.lft and c.rgt<d.rgt    
            )
        ';


        $r = $this->getAdapter()->fetchAll($sql);
        if (sizeof($r) > 0) {
            return false;
        }

        // check parent
        $sql = ' select ancestor_id_path from stat_cat_tree where project_category_id = ' . $cat_id;
        $r = $this->getAdapter()->fetchRow($sql);
        $sql = '
            select * from category_tag where category_id in (' . $r['ancestor_id_path'] . ') and category_id <> ' . $cat_id . ' and tag_id in (' . $tags . ')';


        $r = $this->getAdapter()->fetchAll($sql);
        if (sizeof($r) > 0) {
            return false;
        }

        return true;
    }

    public function updateTagsPerCategory($cat_id, $tags)
    {
        $sql = "delete from category_tag  where category_id=:cat_id";
        $this->getAdapter()->query($sql, array('cat_id' => $cat_id));

        if ($tags) {
            $tags_id = explode(',', $tags);
            $prepared_insert = array_map(
                function ($id) use ($cat_id) {
                    return "({$cat_id},{$id})";
                }, $tags_id
            );
            $sql = "INSERT INTO category_tag (category_id, tag_id) VALUES " . implode(
                    ',', $prepared_insert
                );


            $this->getAdapter()->query($sql);
        }
    }

    public function updateTagsPerStore($store_id, $tags)
    {
        $sql = "delete from config_store_tag  where store_id=:store_id";
        $this->getAdapter()->query($sql, array('store_id' => $store_id));

        if ($tags) {
            $tags_id = explode(',', $tags);
            $prepared_insert = array_map(
                function ($id) use ($store_id) {
                    return "({$store_id},{$id})";
                }, $tags_id
            );
            $sql = "INSERT  INTO config_store_tag (store_id, tag_id) VALUES " . implode(
                    ',', $prepared_insert
                );

            $this->getAdapter()->query($sql);
        }
    }

    public function getTagsPerGroup($groupid)
    {
        $sql = "
                         SELECT 
                         `tag`.`tag_id` 
                         ,`tag`.`tag_name`
                         ,`tag_fullname`
                         ,`tag_description`
                         FROM `tag`
                         JOIN `tag_group_item` ON `tag`.`tag_id` = `tag_group_item`.`tag_id` AND `tag_group_item`.`tag_group_id` = :groupid                         
                         ORDER BY `tag_name`
                    ";

        return $this->getAdapter()->fetchAll($sql, array('groupid' => $groupid));
    }

    public function getAllTagsForStoreFilter()
    {
        $sql = "
                         SELECT 
                         `tag`.`tag_id`, 
                         CASE WHEN `tag`.`tag_fullname` IS NULL THEN `tag_name` ELSE `tag`.`tag_fullname` END AS `tag_name`
                         FROM `tag`
                         WHERE `tag`.`is_active` = 1
                         ORDER BY `tag_name`
                    ";

        return $this->getAdapter()->fetchAll($sql);
    }

    public function getAllTagGroupsForStoreFilter()
    {
        $sql = "
             SELECT 
                `tag_group`.`group_id`, 
                `tag_group`.`group_name` AS `group_name`
                FROM `tag_group`
                ORDER BY `tag_group`.`group_name`
               ";

        //$result = $this->tagObjectRepository->fetchAll($sql);

        return $this->getAdapter()->fetchAll($sql);
    }

    public function saveLicenseTagForProject($object_id, $tag_id)
    {

        $tags = $this->getTagRepository()->fetchLicenseTagsForProject($object_id);
        if (count($tags) == 0) {
            //insert new tag
            if ($tag_id) {
                $sql = "INSERT  INTO tag_object (tag_id, tag_type_id, tag_object_id, tag_group_id) VALUES (:tag_id, :tag_type_id, :tag_object_id, :tag_group_id)";
                $this->getAdapter()->query(
                    $sql, array(
                            'tag_id'        => $tag_id,
                            'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                            'tag_object_id' => $object_id,
                            'tag_group_id'  => TagConst::TAG_LICENSE_GROUPID,
                        )
                );
            }
        } else {
            $tag = $tags[0];

            //remove tag license
            if (!$tag_id) {
                //$sql = "DELETE FROM tag_object WHERE tag_item_id = :tagItemId";
                $sql = "UPDATE `tag_object` SET `tag_changed` = NOW() , `is_deleted` = 1  WHERE `tag_item_id` = :tagItemId";
                $this->getAdapter()->query($sql, array('tagItemId' => $tag['tag_item_id']));
            } else {
                //Update old tag
                if ($tag_id <> $tag['tag_id']) {
                    $sql = "UPDATE tag_object SET tag_changed = NOW(),tag_id = :tag_id WHERE tag_item_id = :tagItemId";
                    $this->getAdapter()->query($sql, array('tagItemId' => $tag['tag_item_id'], 'tag_id' => $tag_id));
                }
            }

        }
    }

    public function saveGhnsExcludedTagForProject($object_id, $tag_value)
    {

        $ghnsExcludedTagId = TagConst::TAG_GHNS_EXCLUDED_ID;
        $sql = "UPDATE tag_object SET tag_changed = NOW() , is_deleted = 1  WHERE tag_group_id = :tag_group_id AND tag_type_id = :tag_type_id AND tag_object_id = :tag_object_id";
        $this->getAdapter()->query(
            $sql, array(
                    'tag_group_id'  => TagConst::TAG_GHNS_EXCLUDED_GROUPID,
                    'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                    'tag_object_id' => $object_id,
                )
        );

        if ($tag_value == 1) {
            $sql = "INSERT  INTO tag_object (tag_id, tag_type_id, tag_object_id, tag_group_id) VALUES (:tag_id, :tag_type_id, :tag_object_id, :tag_group_id)";
            $this->getAdapter()->query(
                $sql, array(
                        'tag_id'        => $ghnsExcludedTagId,
                        'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                        'tag_object_id' => $object_id,
                        'tag_group_id'  => TagConst::TAG_GHNS_EXCLUDED_GROUPID,
                    )
            );
        }

    }

    public function saveDangerosuTagForProject($object_id, $tag_value)
    {

        $tag_dangerous_group_id = $this->config->settings->client->default->tag_group_dangerous_id;
        $tag_dangerous_id = $this->config->settings->client->default->tag_dangerous_id;

        $sql = "UPDATE `tag_object` SET `tag_changed` = NOW() , `is_deleted` = 1  WHERE `tag_group_id` = :tag_group_id AND `tag_type_id` = :tag_type_id AND `tag_object_id` = :tag_object_id";
        $this->getAdapter()->query(
            $sql, array(
                    'tag_group_id'  => $tag_dangerous_group_id,
                    'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                    'tag_object_id' => $object_id,
                )
        );

        if ($tag_value == 1) {
            $sql = "INSERT  INTO tag_object (tag_id, tag_type_id, tag_object_id, tag_group_id) VALUES (:tag_id, :tag_type_id, :tag_object_id, :tag_group_id)";
            $this->getAdapter()->query(
                $sql, array(
                        'tag_id'        => $tag_dangerous_id,
                        'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                        'tag_object_id' => $object_id,
                        'tag_group_id'  => $tag_dangerous_group_id,
                    )
            );
        }

    }

    public function saveDeprecatedModeratorTagForProject($object_id, $tag_value)
    {
        $tag_group_id = $this->config->settings->client->default->tag_group_deprecated_id;
        $tag_id = $this->config->settings->client->default->tag_deprecated_id;
        $this->saveOrUpdateTagObjectProject($object_id, $tag_value, $tag_group_id, $tag_id);
    }

    protected function saveOrUpdateTagObjectProject($object_id, $tag_value, $tag_group_id, $tag_id)
    {
        $sql = "UPDATE tag_object SET tag_changed = NOW() , is_deleted = 1  WHERE tag_group_id = :tag_group_id AND tag_type_id = :tag_type_id AND tag_object_id = :tag_object_id";
        $this->getAdapter()->query(
            $sql, array(
                    'tag_group_id'  => $tag_group_id,
                    'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                    'tag_object_id' => $object_id,
                )
        );

        if ($tag_value == 1) {
            $sql = "INSERT  INTO tag_object (tag_id, tag_type_id, tag_object_id, tag_group_id) VALUES (:tag_id, :tag_type_id, :tag_object_id, :tag_group_id)";
            $this->getAdapter()->query(
                $sql, array(
                        'tag_id'        => $tag_id,
                        'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                        'tag_object_id' => $object_id,
                        'tag_group_id'  => $tag_group_id,
                    )
            );
        }
    }

    public function saveArchitectureTagForProject($project_id, $file_id, $tag_id)
    {

        //first delete old
        //$sql = "DELETE FROM tag_object WHERE tag_group_id = :tag_group_id AND tag_type_id = :tag_type_id AND tag_object_id = :tag_object_id AND tag_parent_object_id = :tag_parent_object_id";
        $sql = "UPDATE tag_object SET tag_changed = NOW() , is_deleted = 1  WHERE tag_group_id = :tag_group_id AND tag_type_id = :tag_type_id AND tag_object_id = :tag_object_id AND tag_parent_object_id = :tag_parent_object_id";
        $this->getAdapter()->query(
            $sql, array(
                    'tag_group_id'         => TagConst::TAG_ARCHITECTURE_GROUPID,
                    'tag_type_id'          => TagConst::TAG_TYPE_FILE,
                    'tag_object_id'        => $file_id,
                    'tag_parent_object_id' => $project_id,
                )
        );

        if ($tag_id) {
            $sql = "INSERT  INTO tag_object (tag_id, tag_type_id, tag_object_id, tag_parent_object_id, tag_group_id) VALUES (:tag_id, :tag_type_id, :tag_object_id, :tag_parent_object_id, :tag_group_id)";
            $this->getAdapter()->query(
                $sql, array(
                        'tag_id'               => $tag_id,
                        'tag_type_id'          => TagConst::TAG_TYPE_FILE,
                        'tag_object_id'        => $file_id,
                        'tag_parent_object_id' => $project_id,
                        'tag_group_id'         => TagConst::TAG_ARCHITECTURE_GROUPID,
                    )
            );
        }

    }

    public function saveFileTagForProjectAndTagGroup($project_id, $file_id, $tag_id, $tag_group_id)
    {

        //first delete old
        $sql = "UPDATE tag_object SET tag_changed = NOW() , is_deleted = 1  WHERE tag_group_id = :tag_group_id AND tag_type_id = :tag_type_id AND tag_object_id = :tag_object_id AND tag_parent_object_id = :tag_parent_object_id";

        $this->getAdapter()->query(
            $sql, array(
                    'tag_group_id'         => $tag_group_id,
                    'tag_type_id'          => TagConst::TAG_TYPE_FILE,
                    'tag_object_id'        => $file_id,
                    'tag_parent_object_id' => $project_id,
                )
        );

        if (!empty($tag_id)) {
            if (is_array($tag_id)) {
                foreach ($tag_id as $tag) {
                    $sql = "INSERT  INTO `tag_object` (`tag_id`, `tag_type_id`, `tag_object_id`, `tag_parent_object_id`, `tag_group_id`) VALUES (:tag_id, :tag_type_id, :tag_object_id, :tag_parent_object_id, :tag_group_id)";
                    $this->getAdapter()->query(
                        $sql, array(
                                'tag_id'               => $tag,
                                'tag_type_id'          => TagConst::TAG_TYPE_FILE,
                                'tag_object_id'        => $file_id,
                                'tag_parent_object_id' => $project_id,
                                'tag_group_id'         => $tag_group_id,
                            )
                    );
                }

            } else {
                $sql = "INSERT  INTO tag_object (tag_id, tag_type_id, tag_object_id, tag_parent_object_id, tag_group_id) VALUES (:tag_id, :tag_type_id, :tag_object_id, :tag_parent_object_id, :tag_group_id)";
                $this->getAdapter()->query(
                    $sql, array(
                            'tag_id'               => $tag_id,
                            'tag_type_id'          => TagConst::TAG_TYPE_FILE,
                            'tag_object_id'        => $file_id,
                            'tag_parent_object_id' => $project_id,
                            'tag_group_id'         => $tag_group_id,
                        )
                );
            }

        }
    }

    public function deleteFileTagForProject($project_id, $file_id, $tag_id)
    {

        //first delete old
        $sql = "UPDATE tag_object SET tag_changed = NOW() , is_deleted = 1  WHERE tag_id= :tag_id AND tag_type_id = :tag_type_id AND tag_object_id = :tag_object_id AND tag_parent_object_id = :tag_parent_object_id";

        $this->getAdapter()->query(
            $sql, array(
                    'tag_id'               => $tag_id,
                    'tag_type_id'          => TagConst::TAG_TYPE_FILE,
                    'tag_object_id'        => $file_id,
                    'tag_parent_object_id' => $project_id,
                )
        );

    }

    public function savePackageTagForProject($project_id, $file_id, $tag_id)
    {

        //first delete old
        $sql = "UPDATE tag_object SET tag_changed = NOW() , is_deleted = 1  WHERE tag_group_id = :tag_group_id AND tag_type_id = :tag_type_id AND tag_object_id = :tag_object_id AND tag_parent_object_id = :tag_parent_object_id";

        $this->getAdapter()->query(
            $sql, array(
                    'tag_group_id'         => TagConst::TAG_PACKAGETYPE_GROUPID,
                    'tag_type_id'          => TagConst::TAG_TYPE_FILE,
                    'tag_object_id'        => $file_id,
                    'tag_parent_object_id' => $project_id,
                )
        );

        if ($tag_id) {
            $sql = "INSERT  INTO tag_object (tag_id, tag_type_id, tag_object_id, tag_parent_object_id, tag_group_id) VALUES (:tag_id, :tag_type_id, :tag_object_id, :tag_parent_object_id, :tag_group_id)";
            $this->getAdapter()->query(
                $sql, array(
                        'tag_id'               => $tag_id,
                        'tag_type_id'          => TagConst::TAG_TYPE_FILE,
                        'tag_object_id'        => $file_id,
                        'tag_parent_object_id' => $project_id,
                        'tag_group_id'         => TagConst::TAG_PACKAGETYPE_GROUPID,
                    )
            );
        }

    }

    public function getProjectPackageTypesString($projectId)
    {
        $sql = 'SELECT DISTINCT ta.tag_fullname as name FROM tag_object t INNER JOIN tag ta on ta.tag_id = t.tag_id WHERE t.tag_group_id = :tag_group_id AND t.tag_parent_object_id = :project_id AND t.is_deleted = 0';
        $resultSet = $this->getAdapter()->fetchAll(
            $sql, array('tag_group_id' => TagConst::TAG_PACKAGETYPE_GROUPID, 'project_id' => $projectId)
        );
        $resultString = '';
        if (count($resultSet) > 0) {
            foreach ($resultSet as $item) {
                $resultString = $resultString . ' <span class="packagetypeos" > ' . stripslashes($item['name']) . '</span>';
            }

            return $resultString;
        }

        return '';
    }

    public function getProjectPackageTypesPureStrings($projectId)
    {
        $sql = 'SELECT DISTINCT ta.tag_fullname as name FROM tag_object t INNER JOIN tag ta on ta.tag_id = t.tag_id WHERE t.tag_group_id = :tag_group_id AND t.tag_parent_object_id = :project_id AND t.is_deleted = 0';
        $resultSet = $this->getAdapter()->fetchAll(
            $sql, array('tag_group_id' => TagConst::TAG_PACKAGETYPE_GROUPID, 'project_id' => $projectId)
        );
        $resultString = '';
        if (count($resultSet) > 0) {
            foreach ($resultSet as $item) {
                $resultString = $resultString . '  ' . stripslashes($item['name']);
            }

            return $resultString;
        }

        return '';
    }

    public function deleteFileTagsOnProject($projectId, $fileId)
    {
        $sql = "UPDATE tag_object inner join tag ON tag.tag_id = tag_object.tag_id set tag_changed = NOW() , is_deleted = 1 
                    WHERE tag_type_id = :tag_type_id and tag_object.tag_object_id=:object_id and tag_object.tag_parent_object_id=:parent_object_id";

        $this->getAdapter()->query(
            $sql, array(
                    'tag_type_id'      => TagConst::TAG_TYPE_FILE,
                    'object_id'        => $fileId,
                    'parent_object_id' => $projectId,
                )
        );
    }

    public function deletePackageTypeOnProject($projectId, $fileId)
    {
        $sql = "UPDATE tag_object inner join tag ON tag.tag_id = tag_object.tag_id set tag_changed = NOW() , is_deleted = 1 
                    WHERE tag_group_id = :tag_group_id and tag_object.tag_object_id=:object_id and tag_object.tag_parent_object_id=:parent_object_id";

        $this->getAdapter()->query(
            $sql, array(
                    'tag_group_id'     => TagConst::TAG_PACKAGETYPE_GROUPID,
                    'object_id'        => $fileId,
                    'parent_object_id' => $projectId,
                )
        );
    }

    public function deleteArchitectureOnProject($projectId, $fileId)
    {
        $sql = "UPDATE tag_object inner join tag ON tag.tag_id = tag_object.tag_id set tag_changed = NOW() , is_deleted = 1 
                    WHERE tag_group_id = :tag_group_id and tag_object.tag_object_id=:object_id and tag_object.tag_parent_object_id=:parent_object_id";

        $this->getAdapter()->query(
            $sql, array(
                    'tag_group_id'     => TagConst::TAG_ARCHITECTURE_GROUPID,
                    'object_id'        => $fileId,
                    'parent_object_id' => $projectId,
                )
        );
    }

    /**
     * @param int $projectId
     * @param int $fileId
     *
     * @return string
     */
    public function getPackageType($projectId, $fileId)
    {
        $sql = 'SELECT ta.tag_fullname as name FROM tag_object t INNER JOIN tag ta on ta.tag_id = t.tag_id WHERE t.tag_group_id = :tag_group_id AND t.tag_parent_object_id = :project_id AND t.tag_object_id = :file_id AND t.is_deleted = 0';
        $resultSet = $this->getAdapter()->fetchAll(
            $sql, array(
                    'tag_group_id' => TagConst::TAG_PACKAGETYPE_GROUPID,
                    'project_id'   => $projectId,
                    'file_id'      => $fileId,
                )
        );

        if (count($resultSet) > 0) {
            return $resultSet[0]['name'];
        } else {
            return '';
        }
    }

    /**
     * @param int $fileId
     *
     * @return array
     */
    public function getFileTags($fileId)
    {
        $sql = 'SELECT ta.tag_id, ta.tag_fullname as name FROM tag_object t INNER JOIN tag ta on ta.tag_id = t.tag_id WHERE t.tag_type_id = :tag_type_id AND t.tag_object_id = :file_id AND t.is_deleted = 0';

        return $this->getAdapter()->fetchAll(
            $sql, array('tag_type_id' => TagConst::TAG_TYPE_FILE, 'file_id' => $fileId)
        );
    }

    /**
     * @param int $projectId
     * @param int $fileId
     * @param int $tagGroup
     *
     * @return array
     */
    public function getTagsForFileAndTagGroup($projectId, $fileId, $tagGroup)
    {
        $sql = 'SELECT ta.tag_fullname as name FROM tag_object t INNER JOIN tag ta on ta.tag_id = t.tag_id WHERE t.tag_group_id = :tag_group_id AND t.tag_parent_object_id = :project_id AND t.tag_object_id = :file_id AND t.is_deleted = 0';
        $resultSet = $this->getAdapter()->fetchAll(
            $sql, array('tag_group_id' => $tagGroup, 'project_id' => $projectId, 'file_id' => $fileId)
        );

        return $resultSet;
    }

    /**
     * @param int $object_id
     *
     * @return array
     */
    public function getTagsEbookSubject($object_id)
    {
        $tag_group_ids = $this->config->settings->client->default->tag_group_ebook_subject;
        $tags = $this->getTagsArray($object_id, TagConst::TAG_TYPE_PROJECT, $tag_group_ids);

        return $tags;
    }

    /**
     * @param int $object_id
     *
     * @return array
     */
    public function getTagsEbookAuthor($object_id)
    {
        $tag_group_ids = $this->config->settings->client->default->tag_group_ebook_author;
        $tags = $this->getTagsArray($object_id, TagConst::TAG_TYPE_PROJECT, $tag_group_ids);

        return $tags;
    }

    /**
     * @param int $object_id
     *
     * @return array
     */
    public function getTagsEbookEditor($object_id)
    {
        $tag_group_ids = $this->config->settings->client->default->tag_group_ebook_editor;

        return $this->getTagsArray($object_id, TagConst::TAG_TYPE_PROJECT, $tag_group_ids);
    }

    /**
     * @param int $object_id
     *
     * @return array
     */
    public function getTagsEbookIllustrator($object_id)
    {
        $tag_group_ids = $this->config->settings->client->default->tag_group_ebook_illustrator;

        return $this->getTagsArray($object_id, TagConst::TAG_TYPE_PROJECT, $tag_group_ids);
    }

    /**
     * @param int $object_id
     *
     * @return array
     */
    public function getTagsEbookTranslator($object_id)
    {
        $tag_group_ids = $this->config->settings->client->default->tag_group_ebook_translator;
        $tags = $this->getTagsArray($object_id, TagConst::TAG_TYPE_PROJECT, $tag_group_ids);

        return $tags;
    }

    /**
     * @param int $object_id
     *
     * @return array
     */
    public function getTagsEbookShelf($object_id)
    {
        $tag_group_ids = $this->config->settings->client->default->tag_group_ebook_shelf;

        return $this->getTagsArray($object_id, TagConst::TAG_TYPE_PROJECT, $tag_group_ids);
    }

    /**
     * @param int $object_id
     *
     * @return array
     */
    public function getTagsEbookLanguage($object_id)
    {
        $tag_group_ids = $this->config->settings->client->default->tag_group_ebook_language;

        return $this->getTagsArray($object_id, TagConst::TAG_TYPE_PROJECT, $tag_group_ids);
    }

    /**
     * @param int $object_id
     *
     * @return array
     */
    public function getTagsEbookType($object_id)
    {
        $tag_group_ids = $this->config->settings->client->default->tag_group_ebook_type;

        return $this->getTagsArray($object_id, TagConst::TAG_TYPE_PROJECT, $tag_group_ids);
    }

    public function saveCollectionTypeTagForProject($object_id, $tag_id)
    {

        $tableTags = $this->getTagRepository();

        $collectionTagGroup = $this->config->settings->client->default->tag_group_collection_type_id;


        $tags = $tableTags->fetchTagsForProject($object_id, $collectionTagGroup);
        if (count($tags) == 0) {
            //insert new tag
            if ($tag_id) {
                $sql = "INSERT  INTO tag_object (tag_id, tag_type_id, tag_object_id, tag_group_id) VALUES (:tag_id, :tag_type_id, :tag_object_id, :tag_group_id)";
                $this->getAdapter()->query(
                    $sql, array(
                            'tag_id'        => $tag_id,
                            'tag_type_id'   => TagConst::TAG_TYPE_PROJECT,
                            'tag_object_id' => $object_id,
                            'tag_group_id'  => $collectionTagGroup,
                        )
                );
            }
        } else {
            $tag = $tags[0];

            //remove tag license
            if (!$tag_id) {
                //$sql = "DELETE FROM tag_object WHERE tag_item_id = :tagItemId";
                $sql = "UPDATE tag_object set tag_changed = NOW() , is_deleted = 1  WHERE tag_item_id = :tagItemId";
                $this->getAdapter()->query($sql, array('tagItemId' => $tag['tag_item_id']));
            } else {
                //Update old tag
                if ($tag_id <> $tag['tag_id']) {
                    $sql = "UPDATE tag_object SET tag_changed = NOW(),tag_id = :tag_id WHERE tag_item_id = :tagItemId";
                    $this->getAdapter()->query($sql, array('tagItemId' => $tag['tag_item_id'], 'tag_id' => $tag_id));
                }
            }

        }

    }

    //========================== generic methods =============================

    public function fetchTagObject($tag_id, $tag_object_id, $tag_group_id, $tag_type_id)
    {
        $sql = $sql_object = "select tag_item_id  from tag_object WHERE tag_id = :tag_id and tag_object_id=:tag_object_id and tag_group_id=:tag_group_id  
                                and tag_type_id = :tag_type_id and is_deleted = 0";
        $r = $this->getAdapter()->fetchRow(
            $sql_object, array(
                           'tag_id'        => $tag_id,
                           'tag_object_id' => $tag_object_id,
                           'tag_group_id'  => $tag_group_id,
                           'tag_type_id'   => $tag_type_id,
                       )
        );

        return $r;
    }

    /*
    * $tag_ids array tag ids 
    */

    /**
     * @return array
     */
    public function getTagGroupsOSUser()
    {
        $tag_group_ids = $this->config->settings->client->default->tag_group_osuser;

        return $this->getTagGroups($tag_group_ids);
    }

    public function getTagGroups($tag_group_ids)
    {
        $sql = 'select g.group_id
                ,g.group_name
                ,g.group_display_name
                ,g.is_multi_select
                ,i.tag_group_item_id
                ,i.tag_id
                ,t.tag_name
                ,t.tag_fullname
                ,t.tag_description    
             from 
             tag_group g,
             tag_group_item i,
             tag t
             where g.group_id=i.tag_group_id
             and i.tag_id = t.tag_id 
             and g.group_id in (' . $tag_group_ids . ')';

        return $this->getAdapter()->fetchAll($sql);
    }

    public function saveOSTagForUser($tag_id, $tag_group_id, $member_id)
    {
        $tag_type_id = $this->config->settings->client->default->tag_type_osuser;
        $this->deleteTagForTabObject($member_id, $tag_group_id, $tag_type_id);
        $this->insertTagObject($tag_id, $tag_type_id, $tag_group_id, $member_id, null);
    }

    //========================== generic methods end =============================

    public function deleteTagForTabObject($tag_object_id, $tag_group_id, $tag_type_id)
    {

        $sql = "UPDATE tag_object SET tag_changed = NOW() , is_deleted = 1  WHERE tag_group_id = :tag_group_id AND tag_type_id = :tag_type_id AND tag_object_id = :tag_object_id";
        $this->getAdapter()->query(
            $sql, array(
                    'tag_group_id'  => $tag_group_id,
                    'tag_type_id'   => $tag_type_id,
                    'tag_object_id' => $tag_object_id,
                )
        );

    }

    // ======================== settings profile user os ==========================================

    public function insertTagObject($tag_ids, $tag_type_id, $tag_group_id, $tag_object_id, $tag_parent_object_id)
    {
        if ($tag_ids == null || sizeof($tag_ids) == 0) {
            return;
        }
        if (!is_array($tag_ids)) {
            $tag_ids = array($tag_ids);
        }

        if ($tag_parent_object_id) {
            $prepared_insert = array_map(
                function ($id) use ($tag_type_id, $tag_group_id, $tag_object_id, $tag_parent_object_id) {
                    return "({$id}, {$tag_type_id},{$tag_group_id},{$tag_object_id},{$tag_parent_object_id})";
                }, $tag_ids
            );
            $sql = "INSERT INTO tag_object (tag_id, tag_type_id, tag_group_id,tag_object_id,tag_parent_object_id) VALUES " . implode(
                    ',', $prepared_insert
                );

            $this->getAdapter()->query($sql);
        } else {
            $prepared_insert = array_map(
                function ($id) use ($tag_type_id, $tag_group_id, $tag_object_id) {
                    return "({$id}, {$tag_type_id},{$tag_group_id},{$tag_object_id})";
                }, $tag_ids
            );
            $sql = "INSERT INTO tag_object (tag_id, tag_type_id, tag_group_id,tag_object_id) VALUES " . implode(
                    ',', $prepared_insert
                );


            $this->getAdapter()->query($sql, Adapter::QUERY_MODE_EXECUTE);
        }
    }

    public function getTopTagsPerCategory($cat_id, $limit = 25)
    {                       
        if(empty($cat_id))
        {
            $storeConfig = $GLOBALS['ocs_store']->config;
            $cache_name = __FUNCTION__ . '_' . $storeConfig->config_id_name . $limit;

        }else{
            $cache_name = __FUNCTION__ . '_' . $cat_id . $limit;
        }        
        
        if ($result = $this->readCache($cache_name)) {
            return $result;
        }
       
        if(empty($cat_id))
        {
            $store_id = $GLOBALS['ocs_store']->config->store_id;             
            $sql = "select c.tag_id,tag_name,cnt 
                    from stat_tags_category c 
                    inner join tag t on c.tag_id = t.tag_id
                    where store = :store_id
                    order by cnt desc
                    ";
            $result = $this->getAdapter()->fetchAll($sql,["store_id"=>$store_id]);
        }else{
            $sql = "select c.tag_id,tag_name,cnt 
                    from stat_tags_category c 
                    inner join tag t on c.tag_id = t.tag_id
                    where project_category_id = :cat_id
                    order by cnt desc";      
                
             $result = $this->getAdapter()->fetchAll($sql,["cat_id"=>$cat_id]);            
        }
        $this->writeCache($cache_name, $result);
        return $result;
    }

    public function getTopTagsPerCategory_($cat_id, $limit = 25)
    {                       
        if(empty($cat_id))
        {
            $storeConfig = $GLOBALS['ocs_store']->config;
            $cache_name = __FUNCTION__ . '_' . $storeConfig->config_id_name . $limit;

        }else{
            $cache_name = __FUNCTION__ . '_' . $cat_id . $limit;
        }        
        
        if ($result = $this->readCache($cache_name)) {
            return $result;
        }
        $inQuery = '?';
        if(empty($cat_id))
        {
            $cat_id = $GLOBALS['ocs_store_category_list'];      
          
            if (is_array($cat_id)) {
                $inQuery = implode(',', array_fill(0, count($cat_id), '?'));
            }
           
        }
                
        $sql = "
                    SELECT 
                    o.tag_id, tt.tag_name, COUNT(1) cnt
                FROM
                    pling.tag_object o
                        INNER JOIN
                    pling.tag tt ON o.tag_id = tt.tag_id
                        INNER JOIN
                    project p ON o.tag_object_id = p.project_id
                        AND p.status = 100
                        INNER JOIN
                    (SELECT 
                        pc1.project_category_id
                    FROM
                        project_category AS pc1
                    JOIN project_category AS parent ON (pc1.lft BETWEEN parent.lft AND parent.rgt)
                        AND pc1.is_active = 1
                    WHERE
                        parent.project_category_id IN ({$inQuery})) c ON c.project_category_id = p.project_category_id
                        JOIN
                    stat_tags_user t ON o.tag_id = t.tag_id
                WHERE
                    o.tag_group_id = 5
                        AND o.tag_type_id = 1
                        AND o.is_deleted = 0
                GROUP BY o.tag_id
                ORDER BY cnt DESC                
                ";
        
        $sql .= " limit " . $limit;       
        
        $result = $this->getAdapter()->fetchAll($sql,$cat_id);

        $this->writeCache($cache_name, $result);

        return $result;
    }

   
    public function getTagsOSUser($member_id)
    {
        $tag_group_ids = $this->config->settings->client->default->tag_group_osuser;
        $tag_type_id = $this->config->settings->client->default->tag_type_osuser;

        return $this->getTagsArray($member_id, $tag_type_id, $tag_group_ids);
    }

}