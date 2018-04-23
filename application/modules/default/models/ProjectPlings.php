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
                        FROM project_plings f
                        inner join member m on f.member_id = m.member_id and m.is_active=1 AND m.is_deleted=0 
                        INNER JOIN stat_projects p ON p.project_id = f.project_id 
                        WHERE (p.status = 100) AND f.is_deleted= 0 AND (f.member_id = :member_id) 
                        order by f.created_at desc
             ";

            $resultSet = $this->_db->fetchAll($sql, array('member_id' => $memberId));
            return new Zend_Paginator(new Zend_Paginator_Adapter_Array($resultSet ));     
    }

     public function fetchPlingsForProject($project_id)
    {            
            $sql = "
                         SELECT 
                        f.project_id
                        ,f.member_id
                        ,f.created_at
                        ,m.profile_image_url
                        ,m.created_at as member_created_at
                        ,m.username
                        FROM project_follower f
                        inner join member m on f.member_id = m.member_id and m.is_active=1 AND m.is_deleted=0 
                        WHERE  f.project_id = :project_id and f.is_deleted = 0
                        order by f.created_at desc
             ";
            $resultSet = $this->_db->fetchAll($sql, array('project_id' => $project_id));
            return $resultSet;     
    }
} 