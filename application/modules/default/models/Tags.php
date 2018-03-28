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
 * Created: 11.09.2017
 */

class Default_Model_Tags
{
    const TAG_TYPE_PROJECT = 1;
    const TAG_USER_GROUPID = 5;

    /**
     * Default_Model_Tags constructor.
     */
    public function __construct()
    {

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


        $tableTags = new Default_Model_DbTable_Tags();
        $listIds = $tableTags->storeTags(implode(',', $new_tags));

        $prepared_insert =
            array_map(function ($id) use ($object_id, $tag_type) { return "({$id}, {$tag_type}, {$object_id})"; },
                $listIds);
        $sql = "INSERT IGNORE INTO tag_object (tag_id, tag_type_id, tag_object_id) VALUES " . implode(',',
                $prepared_insert);


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
            SELECT GROUP_CONCAT(tag.tag_name) AS tag_names 
            FROM tag_object
            JOIN tag ON tag.tag_id = tag_object.tag_id
            join tag_group_item on tag_object.tag_id = tag_group_item.tag_id
            WHERE tag_type_id = :type AND tag_object_id = :object_id
            and tag_group_item.tag_group_id <> :tag_user_groupid
            GROUP BY tag_object.tag_object_id
        ";

        $result = $this->getAdapter()->fetchRow($sql, array('type' => $tag_type, 'object_id' => $object_id, 'tag_user_groupid' =>Default_Model_Tags::TAG_USER_GROUPID ));
        if (isset($result['tag_names'])) {
            return $result['tag_names'];
        }

        return null;
    }

     /**
     * @param int $object_id
     * @param int $tag_type
     *
     * @return string|null
     */
    public function getTagsUser($object_id, $tag_type)
    {
        $sql = "
            SELECT GROUP_CONCAT(tag.tag_name) AS tag_names 
            FROM tag_object
            JOIN tag ON tag.tag_id = tag_object.tag_id
            join tag_group_item on tag_object.tag_id = tag_group_item.tag_id
            WHERE tag_type_id = :type AND tag_object_id = :object_id
            and tag_group_item.tag_group_id = :tag_user_groupid
            GROUP BY tag_object.tag_object_id
        ";

        $result = $this->getAdapter()->fetchRow($sql, array('type' => $tag_type, 'object_id' => $object_id, 'tag_user_groupid' =>Default_Model_Tags::TAG_USER_GROUPID ));
        if (isset($result['tag_names'])) {
            return $result['tag_names'];
        }

        return null;
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
            SELECT count(*) as cnt
            FROM tag_object
            JOIN tag ON tag.tag_id = tag_object.tag_id
            join tag_group_item on tag_object.tag_id = tag_group_item.tag_id
            WHERE tag_type_id = :type AND tag_object_id = :object_id
            and tag_group_item.tag_group_id = :tag_user_groupid           
        ";

        $result = $this->getAdapter()->fetchRow($sql, array('type' => $tag_type, 'object_id' => $object_id, 'tag_user_groupid' =>Default_Model_Tags::TAG_USER_GROUPID ));
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
                         where tag.tag_name like '%".$filter."%'
                    ";
        if (isset($limit)) {
            $sql.= ' limit ' . $limit;
        }

      
        $result = $this->getAdapter()->fetchAll($sql, array('tag_user_groupid' =>Default_Model_Tags::TAG_USER_GROUPID ));
        return $result;
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    private function getAdapter()
    {
        return Zend_Db_Table::getDefaultAdapter();
    }

    /**
     * @param int    $object_id
     * @param string $tags
     * @param int    $tag_type
     */
    public function deassignTags($object_id, $tags, $tag_type)
    {
        $removable_tags = array_diff(explode(',', $this->getTags($object_id, $tag_type)), explode(',', $tags));

        $sql = "DELETE tag_object FROM tag_object JOIN tag ON tag.tag_id = tag_object.tag_id WHERE tag.tag_name = :name and tag_object.tag_object_id=:object_id";
        foreach ($removable_tags as $removable_tag) {
            $this->getAdapter()->query($sql, array('name' => $removable_tag,'object_id' => $object_id));
        }

        $this->updateChanged($object_id, $tag_type);
    }
    
    

 /**
     * @param int    $object_id
     * @param string $tags
     * @param int    $tag_type
     */
    public function processTagsUser($object_id, $tags, $tag_type)
    {
        $this->assignTagsUser($object_id, $tags, $tag_type);
        $this->deassignTagsUser($object_id, $tags, $tag_type);
    }


    /**
     * @param int    $object_id
     * @param string $tags
     * @param int    $tag_type
     */
    public function assignTagsUser($object_id, $tags, $tag_type)
    {
        $tags =  strtolower($tags);
        $new_tags = array_diff(explode(',', $tags), explode(',', $this->getTagsUser($object_id, $tag_type)));
        if(sizeof($new_tags)>0)
        {
            $tableTags = new Default_Model_DbTable_Tags();
            $listIds = $tableTags->storeTagsUser(implode(',', $new_tags));

            $prepared_insert =
                array_map(function ($id) use ($object_id, $tag_type) { return "({$id}, {$tag_type}, {$object_id})"; },
                    $listIds);
            $sql = "INSERT IGNORE INTO tag_object (tag_id, tag_type_id, tag_object_id) VALUES " . implode(',',
                    $prepared_insert);
            $this->getAdapter()->query($sql);
        }
    }


    /**
     * @param int    $object_id
     * @param string $tags
     * @param int    $tag_type
     */
    public function addTagUser($object_id, $tag, $tag_type)
    {
        
        $tableTags = new Default_Model_DbTable_Tags();
        $listIds = $tableTags->storeTagsUser($tag);

        $prepared_insert =
            array_map(function ($id) use ($object_id, $tag_type) { return "({$id}, {$tag_type}, {$object_id})"; },
                $listIds);
        $sql = "INSERT IGNORE INTO tag_object (tag_id, tag_type_id, tag_object_id) VALUES " . implode(',',
                $prepared_insert);

     
        $this->getAdapter()->query($sql);
    }

    public function deassignTagsUser($object_id, $tags, $tag_type)
    {
        $tags =  strtolower($tags);
        $removable_tags = array_diff(explode(',', $this->getTagsUser($object_id, $tag_type)), explode(',', $tags));
        $sql = "DELETE tag_object FROM tag_object JOIN tag ON tag.tag_id = tag_object.tag_id WHERE tag.tag_name = :name and tag_object.tag_object_id=:object_id";
        foreach ($removable_tags as $removable_tag) {
            $this->getAdapter()->query($sql, array('name' => $removable_tag,'object_id' => $object_id));
            // if Tag is the only one in Tag_object table then delete this tag for user_groupid = 5

            $sql_object= "select count(1)  as cnt from tag_object JOIN tag ON tag.tag_id = tag_object.tag_id WHERE tag.tag_name = :name";
            $r = $this->getAdapter()->fetchRow($sql_object, array('name' => $removable_tag));
            if($r['cnt'] ==0){
                // then remove tag if not existing in Tag_object
                $sql_delete_tag = "delete from tag where tag_name=:name";
                $this->getAdapter()->query($sql_delete_tag, array('name' => $removable_tag));
            }
        }
        $this->updateChanged($object_id, $tag_type);
    }


    public function deleteTagUser($object_id, $tag, $tag_type)
    {
        $removable_tag =$tag;
        $sql = "DELETE tag_object FROM tag_object JOIN tag ON tag.tag_id = tag_object.tag_id WHERE tag.tag_name = :name and tag_object.tag_object_id=:object_id";
       
        $this->getAdapter()->query($sql, array('name' => $removable_tag,'object_id' => $object_id));
            // if Tag is the only one in Tag_object table then delete this tag for user_groupid = 5

        $sql_object= "select count(1)  as cnt from tag_object JOIN tag ON tag.tag_id = tag_object.tag_id WHERE tag.tag_name = :name";
        $r = $this->getAdapter()->fetchRow($sql_object, array('name' => $removable_tag));
        if($r['cnt'] ==0){
            // then remove tag if not existing in Tag_object
            $sql_delete_tag = "delete from tag where tag_name=:name";
            $this->getAdapter()->query($sql_delete_tag, array('name' => $removable_tag));
        }       
        $this->updateChanged($object_id, $tag_type);
    }

    private function updateChanged($object_id, $tag_type)
    {
        $sql = "UPDATE tag_object SET tag_changed = NOW() WHERE tag_object_id = :tagObjectId AND tag_type_id = :tagType";
        $this->getAdapter()->query($sql, array('tagObjectId' => $object_id, 'tagType' => $tag_type));
    }


}