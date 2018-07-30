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
class Default_Model_ProjectModeration extends Default_Model_DbTable_ProjectModeration
{


    const M_TYPE_GET_HOT_NEW_STUFF_EXCLUDED = 1;

    public function createModeration($project_id,$project_moderation_type_id, $is_set, $userid,$note)
    {              
            $sql = '
                              SELECT
                              p.*
                              FROM project_moderation AS p
                              WHERE 
                              p.project_id = :project_id
                              AND p.is_deleted = :is_deleted                             
                              and p.project_moderation_type_id = :project_moderation_type_id
                      ';
            $row = $this->_db->fetchRow($sql, array(
                'project_id'  => $project_id,
                'is_deleted' => 0,
                'project_moderation_type_id'        => $project_moderation_type_id
            ));

            if($row!=null)
            {                
                 $row = $this->generateRowClass($row);
                 $updateValues = array(
                      'is_deleted'     =>1                                      
                  );
               
                $this->update($updateValues,  ' project_id=' . $row->project_id .' and project_moderation_type_id='.$row->project_moderation_type_id);

                 $insertValues = array(
                    'project_moderation_type_id' =>$row->project_moderation_type_id,
                    'project_id' => $row->project_id,                    
                    'value' => $is_set,
                    'created_by' =>$userid,                                     
                    'note' => $note                                      
                );                

                $this->_db->insert($this->_name, $insertValues);
            }else
            {                            
                  $this->insertModeration($project_moderation_type_id,$project_id, $is_set,$userid,$note);                             
            }                         
    }

  

    public function getTotalCount()
    {
        $sql = "select count(1)  as cnt
                    FROM project_moderation m
                    join project_moderation_type t on m.project_moderation_type_id = t.project_moderation_type_id
                    join stat_projects p on m.project_id = p.project_id and p.status=100
                    where m.is_deleted= 0  and m.value = 1             
        ";
        $result = $this->getAdapter()->query($sql, array())->fetchAll();      
        return  $result[0]['cnt'];
    }

     public function getList($member_id=null, $orderby='created_at desc',$limit = null, $offset  = null)
    {            
            $sql = "
                        SELECT 
                       m.*
                       ,t.tag_id
                       ,t.name as type_name
                       , p.title
                       , p.count_comments
                       , p.count_dislikes
                       ,p.count_likes
                       ,p.laplace_score
                       ,p.image_small
                       ,p.version
                       ,p.member_id
                       ,p.username
                       ,p.created_at  as project_created_at
                       ,p.changed_at as project_changed_at
                       ,p.cat_title
                       ,(select username from member mm where mm.member_id = m.created_by) as exclude_member_name                       
                       FROM project_moderation m
                       join project_moderation_type t on m.project_moderation_type_id = t.project_moderation_type_id
                       join stat_projects p on m.project_id = p.project_id and p.status=100
                       where m.is_deleted= 0  and m.value = 1                   
             ";

             if(isset($member_id) && $member_id!=''){
                $sql = $sql.' and m.created_by = '.$member_id;
             }

            

             if(isset($orderby)){
                $sql = $sql.'  order by '.$orderby;
             }

             if (isset($limit)) {
                 $sql .= ' limit ' . (int)$limit;
             }

             if (isset($offset)) {
                 $sql .= ' offset ' . (int)$offset;
             }
             

            $resultSet = $this->getAdapter()->fetchAll($sql);

            $image = new Default_View_Helper_Image();
            foreach ($resultSet as &$value) {
               $value['image_small']= $image->image($value['image_small'],array('height' => 120, 'width' => 120));              
            }
            //return$this->generateRowClass($resultSet);;        
            return $resultSet;
    }  

     public function getMembers()
    {            
            $sql = "
                       SELECT 
                           distinct m.created_by as member_id                    
                          ,(select username from member mm where mm.member_id = m.created_by) as username
                          FROM project_moderation m                       
                          join stat_projects p on m.project_id = p.project_id and p.status=100
                          where m.is_deleted= 0   and m.value = 1                                                            
             ";

            
            $resultSet = $this->getAdapter()->fetchAll($sql);

         
            //return$this->generateRowClass($resultSet);;        
            return $resultSet;
    }

    
     /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return Zend_Db_Table::getDefaultAdapter();
    }
   
} 