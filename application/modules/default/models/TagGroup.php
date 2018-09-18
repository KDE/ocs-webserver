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
 *
 * Created: 13.09.2017
 */

class Default_Model_TagGroup
{

    /**
     * @inheritDoc
     */
    public function __construct()
    {

    }

    public function fetchGroupHierarchy()
    {
        $sql = "
            SELECT tag_group.group_name, tag.tag_id, tag.tag_name
            FROM tag_group_item
            JOIN tag_group ON tag_group.group_id = tag_group_item.tag_group_id
            JOIN tag ON tag.tag_id = tag_group_item.tag_id
        ";
        $resultSet = $this->getAdapter()->fetchAll($sql);
        $optgroup = array();
        foreach ($resultSet as $item) {
            $optgroup[$item['group_name']][$item['tag_id']] = $item['tag_name'];
        }

        return $optgroup;
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    private function getAdapter()
    {
        return Zend_Db_Table::getDefaultAdapter();
    }

    /**
     * @param int $group_id
     *
     * @return array
     */
    public function fetchGroupItems($group_id)
    {
        $sql = "SELECT tag_group_item.tag_group_item_id
                    , tag_group_item.tag_group_id
                    , tag.tag_id, tag.tag_name
                    , tag.tag_fullname
                    , tag.tag_description
                    , tag.is_active
             FROM tag_group_item 
             JOIN tag ON tag.tag_id = tag_group_item.tag_id 
             WHERE tag_group_id = :group_id";
        $resultSet = $this->getAdapter()->fetchAll($sql, array('group_id' => $group_id));

        return $resultSet;
    }

    /**
     * @param int    $group_id
     * @param string $tag_name
     *
     * @return array
     */
    public function assignGroupTag($group_id, $tag_name,$tag_fullname, $tag_description,$is_active=1)
    {
        $tag_id = $this->saveTag($tag_name,$tag_fullname, $tag_description,$is_active);
        $group_tag_id = $this->saveGroupTag($group_id, $tag_id);
        $resultSet = $this->fetchOneGroupItem($group_tag_id);

        return $resultSet;
    }



    /**
     * @param string $tag_name
     *
     * @return int
     */
    public function saveTag($tag_name,$tag_fullname, $tag_description,$is_active=1)
    {
        $tag_name = strtolower($tag_name);
        $sql = "SELECT tag_id FROM tag WHERE tag_name = :tagName";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('tagName' => $tag_name));
        if (empty($resultSet)) {
            $this->getAdapter()->insert('tag', array('tag_name' => $tag_name, 'tag_fullname' => $tag_fullname, 'tag_description' => $tag_description,'is_active' => $is_active));
            $resultId = $this->getAdapter()->lastInsertId();
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
        $sql = "SELECT tag_group_item_id FROM tag_group_item WHERE tag_group_id = :group_id AND tag_id = :tag_id";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('group_id' => $group_id, 'tag_id' => $tag_id));
        if (empty($resultSet)) {
            $this->getAdapter()->insert('tag_group_item', array('tag_group_id' => $group_id, 'tag_id' => $tag_id));
            $resultId = $this->getAdapter()->lastInsertId();
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
        $sql = "SELECT tag_group_item.tag_group_item_id
                    , tag_group_item.tag_group_id
                    , tag.tag_id, tag.tag_name
                    , tag.tag_fullname
                    , tag.tag_description
                    , tag.is_active
             FROM tag_group_item 
             JOIN tag ON tag.tag_id = tag_group_item.tag_id 
             WHERE tag_group_item_id = :group_item_id";
        $resultSet = $this->getAdapter()->fetchRow($sql, array('group_item_id' => $group_item_id));

        return $resultSet;
    }

    public function updateGroupTag($tag_id, $tag_name,$tag_fullname, $tag_description,$is_active=1)
    {        
            $updateValues = array(
                'tag_name' =>$tag_name,
                'tag_fullname' => $tag_fullname,
                'tag_description' => $tag_description,
                'is_active' => $is_active
            );
        
            $this->getAdapter()->update('tag', $updateValues, array('tag_id = ?' => $tag_id));        
    }

    public function deleteGroupTag($groupItemId)
    {
        $this->getAdapter()->delete('tag_group_item', array('tag_group_item_id = ?' => $groupItemId));
    }

}