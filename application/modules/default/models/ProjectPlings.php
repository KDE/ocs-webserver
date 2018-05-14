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
class Default_Model_ProjectPlings extends Default_Model_DbTable_ProjectPlings
{

   public function fetchPlingsForMember($memberId)
    {            
             $sql = "
                        SELECT 
                        f.project_id
                        ,f.member_id
                        ,f.created_at
                        ,p.member_id as project_member_id
                        ,p.username as project_username
                        ,p.project_category_id
                        ,p.status
                        ,p.title
                        ,p.description
                        ,p.image_small
                        ,p.project_created_at
                        ,p.project_changed_at
                        ,p.laplace_score
                        ,p.cat_title
                        ,p.count_likes
                        ,p.count_dislikes
                        ,p.laplace_score
                        FROM project_plings f                        
                        INNER JOIN stat_projects p ON p.project_id = f.project_id 
                        WHERE (p.status = 100) and f.is_active = 1 AND f.is_deleted= 0 AND (f.member_id = :member_id) 
                        order by f.created_at desc
             ";

            $resultSet = $this->_db->fetchAll($sql, array('member_id' => $memberId));
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array($resultSet ));     
    }

    public function fetchPlingsForSupporter($memberId)
     {            
            $config = Zend_Registry::get('config');
            $plingcat_id = $config->settings->member->plingcat->id;
              $sql = "
                                SELECT                          
                                                  f.member_id                       
                                                 , count(1) as cntplings
                                                ,(select profile_image_url from  member m where m.member_id = f.member_id ) as profile_image_url
                                                ,(select username from  member m where m.member_id = f.member_id ) as username
                                                 ,(select max(active_time) from support s where s.member_id = f.member_id and status_id = 2) as active_time
                                                 FROM project_plings f                        
                                                 INNER JOIN stat_projects p ON p.project_id = f.project_id 
                                                 WHERE (p.status = 100) AND f.is_active = 1 and f.is_deleted= 0 AND (p.member_id = :member_id) and f.member_id <> :plingcat_id
                                                 group by f.member_id
                                                 order by cntplings desc
                                                
              ";

             $resultSet = $this->_db->fetchAll($sql, array('member_id' => $memberId, 'plingcat_id' =>$plingcat_id));
             return new Zend_Paginator(new Zend_Paginator_Adapter_Array($resultSet ));     
     }

     public function fetchPlingsForProject($project_id)
    {            
            $config = Zend_Registry::get('config');
            $member_id = $config->settings->member->plingcat->id;
            $sql = "
                         SELECT 
                        f.project_id
                        ,f.member_id
                        ,f.created_at
                        ,m.profile_image_url
                        ,m.created_at as member_created_at
                        ,m.username
                        FROM project_plings f
                        inner join member m on f.member_id = m.member_id and m.is_active=1 AND m.is_deleted=0 
                        WHERE  f.project_id = :project_id and f.is_deleted = 0 and f.member_id <> :member_id
                        order by f.created_at desc
             ";
            $resultSet = $this->_db->fetchAll($sql, array('project_id' => $project_id,'member_id' => $member_id));
            return $resultSet;     
    }
} 