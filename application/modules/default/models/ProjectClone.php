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
class Default_Model_ProjectClone extends Default_Model_DbTable_ProjectClone
{
     public function fetchOrigins($project_id)
    {            
            $sql = "
                        SELECT 
                        c.project_id as project_id_clone
                        ,c.project_id_parent as project_id
                        ,c.external_link
                        ,c.member_id
                        ,c.text
                        ,(select cat_title from stat_projects p where p.project_id = c.project_id_parent) catTitle
                        ,(select title from stat_projects p where p.project_id = c.project_id_parent) title
                        ,(select image_small from stat_projects p where p.project_id = c.project_id_parent) image_small
                        ,(select changed_at from stat_projects p where p.project_id = c.project_id_parent) changed_at
                        FROM project_clone c
                        WHERE c.is_deleted = 0 and c.is_valid = 1 and c.project_id = :project_id
                        order by c.created_at desc
             ";
            $resultSet = $this->_db->fetchAll($sql, array('project_id' => $project_id));
             return $this->generateRowSet($resultSet);
            // return $resultSet;     
    }

     public function fetchClones($project_id)
    {            
            $sql = "
                         SELECT 
                            c.project_id as project_id
                            ,c.project_id_parent as project_id_origin
                            ,c.external_link
                            ,c.member_id
                            ,c.text
                            ,(select cat_title from stat_projects p where p.project_id = c.project_id) catTitle
                            ,(select title from stat_projects p where p.project_id = c.project_id) title
                            ,(select image_small from stat_projects p where p.project_id = c.project_id) image_small
                            ,(select changed_at from stat_projects p where p.project_id = c.project_id) changed_at
                            FROM project_clone c
                            WHERE c.is_deleted = 0 and c.is_valid = 1 and  c.project_id_parent = :project_id
                            order by c.created_at desc
             ";
            $resultSet = $this->_db->fetchAll($sql, array('project_id' => $project_id));
             return $this->generateRowSet($resultSet);
            // return $resultSet;     
    }

    public function fetchParent($project_id)
    {
            $sql = "
                        SELECT 
                        *
                        FROM project_clone c
                        WHERE c.is_deleted = 0 and c.is_valid = 1 and  c.project_id = :project_id
             ";
            $resultSet = $this->_db->fetchRow($sql, array('project_id' => $project_id));
             return $this->generateRowSet($resultSet);
    }

    public function fetchRelatedProducts($project_id)
    {
            $sql = "
                                    SELECT 
                                       c.project_id as project_id
                                       ,c.project_id_parent as project_id_origin
                                       ,c.external_link
                                       ,c.member_id
                                       ,c.text
                                       ,(select cat_title from stat_projects p where p.project_id = c.project_id) catTitle
                                       ,(select title from stat_projects p where p.project_id = c.project_id) title
                                       ,(select image_small from stat_projects p where p.project_id = c.project_id) image_small
                                       ,(select changed_at from stat_projects p where p.project_id = c.project_id) changed_at
                                       FROM project_clone c
                                       WHERE c.is_deleted = 0 and c.is_valid = 1 and  c.project_id_parent =  :project_id 

                                   union

                                      SELECT 
                                       c.project_id as project_id
                                       ,c.project_id_parent as project_id_origin
                                       ,c.external_link
                                       ,c.member_id
                                       ,c.text
                                       ,(select cat_title from stat_projects p where p.project_id = c.project_id) catTitle
                                       ,(select title from stat_projects p where p.project_id = c.project_id) title
                                       ,(select image_small from stat_projects p where p.project_id = c.project_id) image_small
                                       ,(select changed_at from stat_projects p where p.project_id = c.project_id) changed_at
                                       FROM project_clone c
                                       WHERE  c.project_id<> :project_id and  c.is_deleted = 0 and c.is_valid = 1 and  c.project_id_parent = (
                                            select project_id_parent from project_clone c 
                                                where c.project_id = :project_id and c.is_valid = 1 and c.is_deleted = 0
                                          )

                        ";
             $resultSet = $this->_db->fetchAll($sql, array('project_id' => $project_id));

         
              return $this->generateRowSet($resultSet);
             
    }

} 