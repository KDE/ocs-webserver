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
class Default_Model_ProjectTagRatings
{
    
    /**
     * @param $project_id   
     */
    public function getProjectTagRatings($project_id)
    {
        $sql = "
                SELECT 
                r.tag_id,
                r.vote,
                r.member_id,
                r.tag_rating_id
                FROM stat_projects p
                inner join project_category g on p.project_category_id = g.project_category_id
                inner join tag_group_item i on i.tag_group_id = g.tag_rating
                inner join tag_rating r on r.tag_id = i.tag_id and r.project_id = p.project_id and r.is_deleted=0
                inner join tag t on t.tag_id = r.tag_id
                where p.project_id = :project_id
               ";        
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('project_id' => $project_id))->fetchAll();
        return $result;
    }

    public function getCategoryTagRatings($category_id)
    {
        $sql ="SELECT 
                t.tag_id as id,            
                t.tag_fullname as name,
                tg.group_display_name
                FROM project_category g
                inner join tag_group_item i on i.tag_group_id = g.tag_rating
                inner join tag t on t.tag_id = i.tag_id
                inner join tag_group tg on g.tag_rating = tg.group_id
                where g.project_category_id =:category_id
            ";
        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('category_id' => $category_id))->fetchAll();
        return $result;
    }

    /**
     * @return tag_rating_id,vote/false
     */
    public function checkIfVote($member_id,$project_id,$tag_id)
    {        
        $sql = "select tag_rating_id,vote from tag_rating where member_id=:member_id and project_id=:project_id and tag_id=:tag_id  
                and is_deleted=0";
        $result = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql,array("member_id"=>$member_id
                                                                        ,"project_id"=>$project_id
                                                                        ,"tag_id" =>$tag_id                                                                        
                                                                        ));
        return $result; 
        if($result && $result['tag_rating_id'])                                                                            
        {
            return $result;
        }else{
            return false;
        }
    }

    public function doVote($member_id,$project_id,$tag_id,$vote,$msg)
    {                
        $data = array();
        $data['comment_target_id'] =$project_id;
        $data['comment_member_id'] =$member_id;
        $data['comment_parent_id'] = 0;
        $data['comment_text'] = $msg;
        $commentmodel = new Default_Model_ProjectComments();
        $result = $commentmodel->save($data);
        $comment_id =  $result->comment_id;

        Zend_Db_Table::getDefaultAdapter()->insert('tag_rating'
        ,array('member_id' => $member_id
        ,'project_id' => $project_id
        ,'tag_id' => $tag_id
        ,'vote' => $vote
        ,'comment_id' => $comment_id 
        ));

        $this->sendNotificationToOwner($project_id, $msg,40);
       
    }

    public function removeVote($tag_rating_id)
    {        
        $sql ="update tag_rating set is_deleted=1, deleted_at=now() where tag_rating_id=".$tag_rating_id;
        Zend_Db_Table::getDefaultAdapter()->query($sql);
        
        $sql = "select comment_id from tag_rating where tag_rating_id=:tag_rating_id ";
        $result = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql,array("tag_rating_id"=>$tag_rating_id));
        if($result && $result['comment_id'])
        {
            $modelComments = new Default_Model_ProjectComments();
            $modelComments->deactiveComment($result['comment_id']);
        }
    }


    /**
     * @param Zend_Db_Table_Row_Abstract $product
     * @param string                     $comment
     */
    private function sendNotificationToOwner($project_id, $comment,$comment_type=null)
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $this->_authMember = $auth->getStorage()->read();
        }else{
            return;
        }
        $tableProject = new Default_Model_Project();
        $product = $tableProject->fetchProductInfo($project_id);
        //Don't send email notification for comments from product owner
        if ($this->_authMember->member_id == $product->member_id) {
            return;
        }

        $productData = new stdClass();
        $productData->mail = $product->mail;
        $productData->username = $product->username;
        $productData->username_sender = $this->_authMember->username;
        $productData->title = $product->title;
        $productData->project_id = $product->project_id;

        $queue = Local_Queue_Factory::getQueue();        
        if(!empty($comment_type)&& $comment_type=='30')
        {   
            $command = new Backend_Commands_SendCommentNotification('tpl_user_comment_note_'.$comment_type, $productData, $comment);
        }else
        {
            $command = new Backend_Commands_SendCommentNotification('tpl_user_comment_note', $productData, $comment);
        }        
        $queue->send(serialize($command));
    }
    
    
}