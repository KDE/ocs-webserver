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
class Default_Model_DbTable_Tags extends Local_Model_Table
{

    protected $_name = "tag";

    protected $_keyColumnsForRow = array('tag_id');

    protected $_key = 'tag_id';

    protected $_tagsuser_groupid = 5; //  unsortied

    protected $_defaultValues = array(
        'tag_id'   => null,
        'tag_name' => null
    );


    /**
     * @param string $tags
     *
     * @return array
     */
    public function storeTags($tags)
    {
        $arrayTags = explode(',', $tags);
        $sqlFetchTag = "SELECT `tag_id` FROM tag WHERE tag_name = :name";
        $resultIds = array();
        foreach ($arrayTags as $tag) {
            $resultRow = $this->_db->fetchRow($sqlFetchTag, array('name' => $tag));
            if (empty($resultRow)) {
                $this->_db->insert($this->_name, array('tag_name' => $tag));
                $resultIds[] = $this->_db->lastInsertId();
            } else {
                $resultIds[] = $resultRow['tag_id'];
            }
        }

        return $resultIds;
    }

     /**
     * @param string $tags
     *
     * @return array
     */
    public function storeTagsUser($tags)
    {
        $arrayTags = explode(',', $tags);
        $sqlFetchTag = "SELECT `tag_id` FROM tag WHERE tag_name = :name";
        $resultIds = array();
        foreach ($arrayTags as $tag) {
            $resultRow = $this->_db->fetchRow($sqlFetchTag, array('name' => $tag));
            if (empty($resultRow)) {
                $this->_db->insert($this->_name, array('tag_name' => $tag));
                $tagId = $this->_db->lastInsertId();
                $resultIds[] = $tagId;

                $sql = "SELECT tag_group_item_id FROM tag_group_item WHERE tag_group_id = :group_id AND tag_id = :tag_id";
                $resultSet = $this->_db->fetchRow($sql, array('group_id' => $this->_tagsuser_groupid, 'tag_id' =>$tagId));
                if (empty($resultSet)) {
                    $this->_db->insert('tag_group_item', array('tag_group_id' => $this->_tagsuser_groupid, 'tag_id' => $tagId));                    
                }

            } else {
                $resultIds[] = $resultRow['tag_id'];
            }
        }

        return $resultIds;
    }

}