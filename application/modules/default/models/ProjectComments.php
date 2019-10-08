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
class Default_Model_ProjectComments
{

    /** @var string */
    protected $_dataTableName;
    /** @var  Default_Model_DbTable_Comments */
    protected $_dataTable;
    /** @var  array */
    protected $data;
    /** @var  array */
    protected $index;

    /**
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * param [ mixed $args [, $... ]]
     *
     * @param string $_dataTableName
     *
     * @link http://php.net/manual/en/language.oop5.decon.php
     */
    public function __construct($_dataTableName = 'Default_Model_DbTable_Comments')
    {
        $this->_dataTableName = $_dataTableName;
        $this->_dataTable = new $this->_dataTableName;
    }

    public function getCommentWithMember($comment_id)
    {
        $sql = "    SELECT *
                    FROM comments
                    STRAIGHT_JOIN member ON comments.comment_member_id = member.member_id
                    WHERE comment_id = :comment_id
                    ORDER BY comments.comment_created_at DESC, comment_parent_id
        ";

        $rowSet = $this->_dataTable->getAdapter()->fetchAll($sql, array('comment_id' => $comment_id));
        if (0 == count($rowSet)) {
            return array();
        }

        return $rowSet[0];
    }

    public function getCommentFromSource($type = 0, $source_id, $source_pk)
    {
        $sql = "
                    SELECT *
                    FROM comments
                    WHERE comment_type = :type AND source_id = :source_id AND source_pk = :source_pk 
        ";

        $rowset = $this->_dataTable->getAdapter()->fetchRow($sql,
            array('type' => $type, 'source_id' => $source_id, 'source_pk' => $source_pk))
        ;
        if (!$rowset) {
            return false;
        }

        return $rowset;
    }

 /**
     * @param $project_id
     *
     * @return Zend_Paginator
     */
    public function getCommentTreeForProjectList($project_id)
    {

        $sql = "
                SELECT comment_id, comment_target_id, comment_parent_id, comment_text, comment_created_at, comment_active, comment_type, member_id, username, profile_image_url 
                    ,(  select count(distinct c.name) sections from 
                        section_support s, support t , section c
                        where s.support_id = t.id and s.section_id = c.section_id
                        and  t.member_id = comments.comment_member_id   and t.status_id=2
                        and s.is_active = 1
                    ) as issupporter
                    ,(select score from project_rating where project_rating.comment_id = comments.comment_id ) as rating     
                    ,(select score from project_rating r where r.project_id =:project_id and rating_active = 1 and r.member_id = comments.comment_member_id) as rating_member          
                    ,member.roleid
                    FROM comments 
                    STRAIGHT_JOIN member ON comments.comment_member_id = member.member_id                                 
                WHERE comment_active = :status_active AND comment_type = :type_id AND comment_target_id = :project_id AND comment_parent_id = 0 
                ORDER BY comment_created_at DESC
                ";
        $rowset = $this->_dataTable->getAdapter()->fetchAll($sql, array(
                'status_active' => 1,
                'type_id'       => Default_Model_DbTable_Comments::COMMENT_TYPE_PRODUCT,
                'project_id'    => $project_id
            ))
        ;
        $sql = "
               SELECT comment_id, comment_target_id, comment_parent_id, comment_text, comment_created_at, comment_active, comment_type, member_id, username, profile_image_url 
                ,( select count(distinct c.name) sections from 
                        section_support s, support t , section c
                        where s.support_id = t.id and s.section_id = c.section_id
                        and  t.member_id = comments.comment_member_id   and t.status_id=2
                        and s.is_active = 1
                    ) as issupporter
               ,(select score from project_rating where project_rating.comment_id = comments.comment_id ) as rating  
               ,(select score from project_rating r where r.project_id =:project_id and rating_active = 1 and r.member_id = comments.comment_member_id) as rating_member    
               ,member.roleid     
                FROM comments 
                STRAIGHT_JOIN member ON comments.comment_member_id = member.member_id                 
                WHERE comment_active = :status_active AND comment_type = :type_id AND comment_target_id = :project_id AND comment_parent_id <> 0 
                ORDER BY comment_created_at DESC
                ";
        $rowset2 = $this->_dataTable->getAdapter()->fetchAll($sql, array(
                'status_active' => 1,
                'type_id'       => Default_Model_DbTable_Comments::COMMENT_TYPE_PRODUCT,
                'project_id'    => $project_id
            ))
        ;



        $rowset = array_merge($rowset, $rowset2);

        /* create array with comment_id as key */
        foreach ($rowset as $item) {                     
            $this->data[$item['comment_id']] = $item;
        }
        /* create an array with all parent_id's and their immediate children */
        foreach ($rowset as $item) {           
            $this->index[$item['comment_parent_id']][] = $item['comment_id'];
        }
        /* create the final sorted array */
        $list = array();
        $this->sort_child_nodes(0, 1, $list);

        return $list;
    }

    /**
     * @param $project_id
     *
     * @return Zend_Paginator
     */
    public function getCommentTreeForProject($project_id)
    {

        $list = $this->getCommentTreeForProjectList($project_id);

        return new Zend_Paginator(new Zend_Paginator_Adapter_Array($list));
    }

    /**
     * @param int  $parent_id
     * @param int  $level
     * @param null $result
     *
     * @return array|null
     */
    function sort_child_nodes($parent_id, $level, &$result = null)
    {
        // array(array('comment' => $rootElement, 'level' => 1));
        $parent_id = $parent_id === null ? "NULL" : $parent_id;
        if (isset($this->index[$parent_id])) {
            foreach ($this->index[$parent_id] as $id) {
                $result[] = array('comment' => $this->data[$id], 'level' => $level);
                $this->sort_child_nodes($id, $level + 1, $result);
            }
        }
    }

    /**
     * @param int $project_id
     *
     * @return Zend_Paginator
     */
    public function getAllCommentsForProject($project_id)
    {
        $rootElements = $this->getRootCommentsForProject($project_id);
        $returnValue = array();
        foreach ($rootElements as $rootElement) {
            $resultElement = array(array('comment' => $rootElement, 'level' => 1));
            $childs = $this->getAllChildComments($resultElement);
            if (0 == count($childs)) {
                $returnValue = array_merge($returnValue, $resultElement);
            } else {
                $returnValue = array_merge($returnValue, $childs);
            }
        }

        return new Zend_Paginator(new Zend_Paginator_Adapter_Array($returnValue));
    }

    /**
     * @param int $_projectId
     *
     * @return array
     */
    public function getRootCommentsForProject($_projectId)
    {
        $sql = '
                SELECT *
                    FROM comments
                    STRAIGHT_JOIN member ON comments.comment_member_id = member.member_id
                    WHERE comment_target_id = :project_id
                      AND comment_parent_id = 0
                      AND comment_type = :type_id
                      AND comment_active = :status
                    ORDER BY comments.comment_created_at DESC, comment_parent_id
        ';

        $rowset = $this->_dataTable->getAdapter()->fetchAll($sql, array(
                'project_id' => $_projectId,
                'status'     => Default_Model_DbTable_Comments::COMMENT_ACTIVE,
                'type_id'    => Default_Model_DbTable_Comments::COMMENT_TYPE_PRODUCT
            ))
        ;
        if (0 == count($rowset)) {
            return array();
        }

        return $rowset;
    }

    /**
     * @param array $element
     *
     * @return array
     */
    private function getAllChildComments($element)
    {
        $returnValue = array();
        $level = $element[0]['level'] + 1;
        $childs = $this->getChildCommentsForId($element[0]['comment']['comment_id']);
        if (0 == count($childs)) {
            return null;
        }
        foreach ($childs as $child) {
            $resultElement = array(array('comment' => $child, 'level' => $level));
            $subChilds = $this->getAllChildComments($resultElement);
            if (0 == count($subChilds)) {
                $returnValue = array_merge($returnValue, $resultElement);
            } else {
                $returnValue = array_merge($returnValue, $subChilds);
            }
        }

        return array_merge($element, $returnValue);
    }

    /**
     * @param int $parent_id
     *
     * @return array
     */
    public function getChildCommentsForId($parent_id)
    {
        $sql = "SELECT *
                    FROM comments
                    STRAIGHT_JOIN member ON comments.comment_member_id = member.member_id
                    WHERE comment_parent_id = :parent_id
                    AND comment_active = :status
                    ORDER BY comments.comment_created_at, comments.comment_id
               ";
        $rowset = $this->_dataTable->getAdapter()->fetchAll($sql,
            array('parent_id' => $parent_id, 'status' => Default_Model_DbTable_Comments::COMMENT_ACTIVE))
        ;
        if (0 == count($rowset)) {
            return array();
        }

        return $rowset;
    }

    /**
     * @param array $data
     *
     * @return Zend_Db_Table_Row_Abstract
     * @throws Exception
     */
    public function save($data)
    {
        return $this->_dataTable->save($data);
    }

    public function deactiveComment($comment_id){
        $sql = '
                UPDATE comments
                SET comment_active = 0
                WHERE comment_id = :comment_id';
        $this->_dataTable->getAdapter()->query($sql, array('comment_id' => $comment_id))->execute();        
    }

    public function setAllCommentsForUserDeleted($member_id)
    {
        
        $sql = "SELECT comment_id FROM comments WHERE comment_member_id = :member_id AND comment_active = 1";
        $commentsForDelete = $this->_dataTable->getAdapter()->fetchAll($sql, array(
            'member_id'       => $member_id
        ));
        foreach ($commentsForDelete as $item) {
            $this->setDeleted($member_id, $item['comment_id']);
        }
        
        /*
        $sql = '
                UPDATE comments
                SET comment_active = 0
                WHERE comment_member_id = :member_id';
        $this->_dataTable->getAdapter()->query($sql, array('member_id' => $member_id))->execute();
        */
    }

    public function setAllCommentsForUserActivated($member_id)
    {
        $sql = "SELECT comment_id FROM comments c
                JOIN member_deactivation_log l ON l.object_type_id = 4 AND l.object_id = c.comment_id AND l.deactivation_id = c.comment_member_id AND l.is_deleted = 0
                WHERE c.comment_member_id = :member_id AND c.comment_active = 0";
        $commentsForDelete = $this->_dataTable->getAdapter()->fetchAll($sql, array(
            'member_id'       => $member_id
        ));
        foreach ($commentsForDelete as $item) {
            $this->setActive($member_id, $item['comment_id']);
        }
        /*
        $sql = '
                UPDATE comments
                SET comment_active = 1
                WHERE comment_member_id = :member_id';
        $this->_dataTable->getAdapter()->query($sql, array('member_id' => $member_id))->execute();
         * 
         */
    }

    /**
     * @param int $member_id
     * @param int $project_id
     *
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function setAllCommentsForProjectDeleted($member_id, $project_id)
    {
        $sql =
            "SELECT `comment_id` FROM `comments` WHERE `comment_target_id` = :project_id AND `comment_type` = 0 AND `comment_active` = 1";
        $commentsForDelete = $this->_dataTable->getAdapter()->fetchAll($sql, array(
            'project_id' => $project_id
        ))
        ;
        foreach ($commentsForDelete as $item) {
            $this->setDeleted($member_id, $item['comment_id']);
        }
    }

    /**
     * @param int $member_id
     * @param int $comment_id
     *
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function setDeleted($member_id, $comment_id)
    {
        $sql = '
                UPDATE `comments`
                SET `comment_active` = 0
                WHERE `comment_id` = :comment_id';
        $this->_dataTable->getAdapter()->query($sql, array('comment_id' => $comment_id))->execute();

        $memberLog = new Default_Model_MemberDeactivationLog();
        $memberLog->logCommentAsDeleted($member_id, $comment_id);
    }
    
    public function setActive($member_id, $comment_id)
    {
        $sql = '
                UPDATE comments
                SET comment_active = 1, comment_deleted_at = null
                WHERE comment_id = :comment_id';
        $this->_dataTable->getAdapter()->query($sql, array('comment_id' => $comment_id))->execute();
        
        $memberLog = new Default_Model_MemberDeactivationLog();
        $memberLog->removeLogCommentAsDeleted($member_id, $comment_id);
    }

    /**
     * @param int $member_id
     * @param int $project_id
     */
    public function setAllCommentsForProjectActivated($member_id, $project_id)
    {
        $sql = "SELECT `comment_id` FROM `comments` `c`
                JOIN `member_deactivation_log` `l` ON `l`.`object_type_id` = 4 AND `l`.`object_id` = `c`.`comment_id` AND `l`.`is_deleted` = 0
                WHERE `c`.`comment_target_id` = :project_id  AND `l`.`deactivation_id` = :member_id AND `comment_active` = 0";
        $commentsForDelete = $this->_dataTable->getAdapter()->fetchAll($sql, array(
            'project_id' => $project_id,
            'member_id' => $member_id
        ))
        ;
        foreach ($commentsForDelete as $item) {
            $this->setActive($member_id, $item['comment_id']);
        }
    }

    public function getCommentsHierarchic($project_id)
    {
        $rootElements = $this->getRootCommentsForProject($project_id);
        $returnValue = array();
        foreach ($rootElements as $parentComment) {
            $childs = $this->getChildCommentsHierarchic($parentComment);
            if (0 == count($childs)) {
                $parentComment['childcount'] = 0;
            } else {
                $parentComment['childcount'] = count($childs);
                $parentComment['children'] = $childs;
            }
            $returnValue[] = $parentComment;
        }

        return new Zend_Paginator(new Zend_Paginator_Adapter_Array($returnValue));
    }

    protected function getChildCommentsHierarchic($parentComment)
    {
        $childs = $this->getChildCommentsForId($parentComment['comment_id']);
        if (0 == count($childs)) {
            return array();
        }
        $returnValue = array();
        foreach ($childs as $child) {
            $subChilds = $this->getChildCommentsHierarchic($child);
            if (0 == count($subChilds)) {
                $child['childcount'] = 0;
            } else {
                $child['childcount'] = count($subChilds);
                $child['children'] = $subChilds;
            }
            $returnValue[] = $child;
        }

        return $returnValue;
    }

}