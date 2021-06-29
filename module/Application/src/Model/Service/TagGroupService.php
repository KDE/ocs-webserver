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

use Application\Model\Interfaces\TagGroupInterface;
use Application\Model\Service\Interfaces\TagGroupServiceInterface;

class TagGroupService extends BaseService implements TagGroupServiceInterface
{

    private $tagGroupRepository;

    public function __construct(
        TagGroupInterface $t
    ) {
        $this->tagGroupRepository = $t;
    }

    public function fetchGroupHierarchy()
    {
        $sql = "
            SELECT `tag_group`.`group_name`, `tag`.`tag_id`, `tag`.`tag_name`
            FROM `tag_group_item`
            JOIN `tag_group` ON `tag_group`.`group_id` = `tag_group_item`.`tag_group_id`
            JOIN `tag` ON `tag`.`tag_id` = `tag_group_item`.`tag_id`
        ";
        $resultSet = $this->getAdapter()->fetchAll($sql);
        $optgroup = array();
        foreach ($resultSet as $item) {
            $optgroup[$item['group_name']][$item['tag_id']] = $item['tag_name'];
        }

        return $optgroup;
    }

    private function getAdapter()
    {
        return $this->tagGroupRepository;
    }

    public function fetchAllGroups()
    {
        $sql = "
            SELECT `tag_group`.`group_name`, `tag_group`.`group_id`
            FROM `tag_group`
        ";

        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * @param int $group_id
     *
     * @return array
     */
    public function fetchGroupItems($group_id)
    {
        $sql = "SELECT `tag_group_item`.`tag_group_item_id`
                    , `tag_group_item`.`tag_group_id`
                    , `tag`.`tag_id`, `tag`.`tag_name`
                    , `tag`.`tag_fullname`
                    , `tag`.`tag_description`
                    , `tag`.`is_active`
             FROM `tag_group_item` 
             JOIN `tag` ON `tag`.`tag_id` = `tag_group_item`.`tag_id` 
             WHERE `tag_group_id` = :group_id";

        return $this->getAdapter()->fetchAll($sql, array('group_id' => $group_id));
    }

    /**
     * @param int    $group_id
     * @param string $tag_name
     * @param        $tag_fullname
     * @param        $tag_description
     * @param int    $is_active
     *
     * @return array
     */
    public function assignGroupTag($group_id, $tag_name, $tag_fullname, $tag_description, $is_active = 1)
    {
        $tag_id = $this->saveTag($tag_name, $tag_fullname, $tag_description, $is_active);
        $group_tag_id = $this->saveGroupTag($group_id, $tag_id);

        return $this->fetchOneGroupItem($group_tag_id);
    }

    /**
     * @param string $tag_name
     * @param        $tag_fullname
     * @param        $tag_description
     * @param int    $is_active
     *
     * @return int
     */
    public function saveTag($tag_name, $tag_fullname, $tag_description, $is_active = 1)
    {
        $tag_name = strtolower($tag_name);
        $sql = "SELECT `tag_id` FROM `tag` WHERE `tag_name` = :tagName";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('tagName' => $tag_name));
        if (empty($resultSet)) {
            $resultId = $this->getAdapter()->insertTable(
                'tag', array(
                         'tag_name'        => $tag_name,
                         'tag_fullname'    => $tag_fullname,
                         'tag_description' => $tag_description,
                         'is_active'       => $is_active,
                     )
            );
        } else {
            $resultId = $resultSet['tag_id'];
        }

        return $resultId;
    }

    /**
     * @param int $group_id
     * @param int $tag_id
     *
     * @return int
     */
    public function saveGroupTag($group_id, $tag_id)
    {
        $sql = "SELECT `tag_group_item_id` FROM `tag_group_item` WHERE `tag_group_id` = :group_id AND `tag_id` = :tag_id";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('group_id' => $group_id, 'tag_id' => $tag_id));
        if (empty($resultSet)) {
            $resultId = $this->getAdapter()->insertTable(
                'tag_group_item', array(
                                    'tag_group_id' => $group_id,
                                    'tag_id'       => $tag_id,
                                )
            );
        } else {
            $resultId = $resultSet['tag_group_item_id'];
        }

        return $resultId;
    }

    /**
     * @param int $group_item_id
     *
     * @return array|false
     */
    public function fetchOneGroupItem($group_item_id)
    {
        $sql = "SELECT `tag_group_item`.`tag_group_item_id`
                    , `tag_group_item`.`tag_group_id`
                    , `tag`.`tag_id`, `tag`.`tag_name`
                    , `tag`.`tag_fullname`
                    , `tag`.`tag_description`
                    , `tag`.`is_active`
             FROM `tag_group_item` 
             JOIN `tag` ON `tag`.`tag_id` = `tag_group_item`.`tag_id` 
             WHERE `tag_group_item_id` = :group_item_id";

        return $this->getAdapter()->fetchRow($sql, array('group_item_id' => $group_item_id));
    }

    public function updateGroupTag($tag_id, $tag_name, $tag_fullname, $tag_description, $is_active = 1)
    {
        $updateValues = array(
            'tag_name'        => $tag_name,
            'tag_fullname'    => $tag_fullname,
            'tag_description' => $tag_description,
            'is_active'       => $is_active,
        );

        $this->getAdapter()->updateTable('tag', $updateValues, ['tag_id' => $tag_id]);
    }

    public function deleteGroupTag($groupItemId)
    {
        $sql = "delete from tag_group_item where tag_group_item_id =" . $groupItemId;
        $this->getAdapter()->query($sql);
    }

    public function fetchTagGroupsForCategory($cat_id)
    {
        $sql = " SELECT `category_tag_group`.`tag_group_id`
                        , `tag_group`.`group_name`
                        , `tag_group`.`group_display_name`
                        , `tag_group`.`group_legacy_name`
                        , `tag_group`.`is_multi_select`
                        , `category_tag_group`.`category_id`
                        , `project_category`.`title`
                 FROM `category_tag_group`
                 JOIN `tag_group` ON `tag_group`.`group_id` = `category_tag_group`.`tag_group_id`
                 JOIN `project_category` ON `project_category`.`project_category_id` = `category_tag_group`.`category_id`
                 WHERE `category_tag_group`.`category_id` = :cat_id";

        return $this->getAdapter()->fetchAll($sql, array('cat_id' => $cat_id));
    }

    public function updateTagGroupsPerCategory($cat_id, $taggroups)
    {
        $sql = "DELETE FROM `category_tag_group` WHERE `category_id`=:cat_id";
        $this->getAdapter()->query($sql, array('cat_id' => $cat_id));

        if ($taggroups) {
            $taggroup_id = explode(',', $taggroups);
            $prepared_insert = array_map(
                function ($id) use ($cat_id) {
                    return "({$cat_id},{$id})";
                }, $taggroup_id
            );
            $sql = "INSERT IGNORE INTO category_tag_group (category_id, tag_group_id) VALUES " . implode(
                    ',', $prepared_insert
                );

            $this->getAdapter()->query($sql);
        }
    }

    public function updateTagGroupsPerStore($store_id, $taggroups)
    {
        $sql = "DELETE FROM `config_store_tag_group` WHERE `store_id`=:store_id";
        $this->getAdapter()->query($sql, array('store_id' => $store_id));

        if ($taggroups) {
            $taggroup_id = explode(',', $taggroups);
            $prepared_insert = array_map(
                function ($id) use ($store_id) {
                    return "({$store_id},{$id})";
                }, $taggroup_id
            );
            $sql = "INSERT IGNORE INTO config_store_tag_group (store_id, tag_group_id) VALUES " . implode(
                    ',', $prepared_insert
                );

            $this->getAdapter()->query($sql);
        }
    }
}