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

    public function updateInsertModeration($project_id,$project_moderation_type_id, $is_set, $userid,$note,$is_deleted,$is_valid)
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
                $this->update($updateValues,  'project_moderation_id=' . $row->project_moderation_id);

                 $insertValues = array(
                    'project_moderation_type_id' =>$row->project_moderation_type_id,
                    'project_id' => $row->project_id,
                    
                    'created_by' => $row->updated_by!=null ? $row->updated_by : $row->created_by,
                    'created_at' => $row->updated_at!=null ? $row->updated_at : $row->created_at,

                    'updated_by' =>$userid,
                    'updated_at' => new Zend_Db_Expr('Now()'),
                    'note' => $note                  
                );                

               if($is_set==1)
               {
                  $insertValues['is_deleted'] = 0;
               }else{
                  $insertValues['is_deleted'] = 1;
               }

                if($is_deleted)
                {
                    $insertValues['is_deleted'] = $is_deleted;
                }
                if($is_valid)
                {
                    $insertValues['is_valid'] = $is_valid;
                }
                $this->_db->insert($this->_name, $insertValues);
            }else
            {            
                if($is_set==1)
                {
                    $this->insertModeration($project_moderation_type_id,$project_id, $userid,$note);             
                 }   
            }                         
    }

     public function getList()
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
                       ,(select username from member mm where mm.member_id = m.updated_by) as updated_by_username
                       FROM project_moderation m
                       join project_moderation_type t on m.project_moderation_type_id = t.project_moderation_type_id
                       join stat_projects p on m.project_id = p.project_id
                       where m.is_deleted= 0
                       order by created_at desc             
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