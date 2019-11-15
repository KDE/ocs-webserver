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
                        ,' ' as catTitle
                        ,p.project_category_id
                        ,p.title
                        ,p.image_small
                        ,p.changed_at
                        FROM project_clone c
                        JOIN project p ON p.project_id = c.project_id_parent
                        WHERE c.is_deleted = 0 and c.is_valid = 1 and c.project_id = :project_id
                        AND p.`status` = 100
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
                            ,' ' as catTitle
                            ,(select project_category_id from project p where p.project_id = c.project_id_parent) project_category_id
                            ,p.title
                            ,p.image_small
                            ,p.changed_at
                            FROM project_clone c
                            JOIN project p ON p.project_id = c.project_id
                            WHERE c.is_deleted = 0 and c.is_valid = 1 and  c.project_id_parent = :project_id
                            AND p.`status` = 100
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
                              select distinct * from 
                              (
                                    SELECT 
                                       c.project_id as project_id
                                       
                                       ,c.external_link
                                       ,c.member_id
                                       ,c.text
                                       ,' ' as catTitle
                                       ,(select project_category_id from project p where p.project_id = c.project_id_parent) project_category_id
                                       ,p.title
                                      ,p.image_small
                                      ,p.changed_at
                                       FROM project_clone c
                                       JOIN project p on p.project_id = c.project_id
                                       WHERE c.is_deleted = 0 and c.is_valid = 1 and  c.project_id_parent =  :project_id 
                                       AND p.`status` = 100

                                   union

                                      SELECT 
                                       c.project_id as project_id
                                      
                                       ,c.external_link
                                       ,c.member_id
                                       ,c.text
                                       ,' ' as catTitle
                                       ,(select project_category_id from project p where p.project_id = c.project_id_parent) project_category_id
                                       ,p.title
                                       ,p.image_small
                                       ,p.changed_at
                                       FROM project_clone c
                                       JOIN project p on p.project_id = c.project_id
                                       WHERE  c.project_id<> :project_id and  c.is_deleted = 0 and c.is_valid = 1 AND p.`status` = 100 and  c.project_id_parent in (
                                            select project_id_parent from project_clone c 
                                                where c.project_id = :project_id and c.is_valid = 1 and c.is_deleted = 0
                                          )
                              ) a
                              where a.catTitle is not null
                              order by changed_at desc
                        ";
             $resultSet = $this->_db->fetchAll($sql, array('project_id' => $project_id));         
              return $this->generateRowSet($resultSet);             
    }

    public function fetchCredits()
    {
          $sql = "

                      SELECT 
                       c.project_clone_id
                      ,c.project_id 
                      ,c.project_id_parent
                      ,c.external_link
                      ,c.text
                      ,c.member_id as reported_by
                      ,m.username as reporter_username
                      ,m.profile_image_url  as reporter_profile_image_url
                      ,p.cat_title catTitle
                      ,p.title
                      ,p.image_small
                      ,p.changed_at
                      ,p.laplace_score
                      ,p.member_id
                      ,p.username
                      ,pp.cat_title parent_catTitle
                      ,pp.title parent_title
                      ,pp.image_small parent_image_small
                      ,pp.changed_at parent_changed_at
                      ,pp.laplace_score parent_laplace_score
                      ,pp.member_id parent_member_id
                      ,pp.username parent_username
                      FROM project_clone c
                      join stat_projects pp on  pp.project_id =c.project_id_parent 
                      join member m on m.member_id = c.member_id
                      left JOIN stat_projects p on p.project_id = c.project_id                                            
                      WHERE c.is_deleted = 0 and c.is_valid = 0  AND pp.status = 100
                      order by c.created_at desc

          ";

          $resultSet = $this->_db->fetchAll($sql);         
          return $this->generateRowSet($resultSet);            
    }

    /**
     * @return string comma seperated ids
     */
    function fetchChildrensIds($project_id){
        $sql = "
        select GROUP_CONCAT(project_id) as ids from project_clone c where c.project_id_parent = :project_id and is_valid=1
        ";
        $resultSet = $this->_db->fetchRow($sql, array('project_id' => $project_id));
        return $resultSet['ids'];
    }

    /**
     * @return string comma seperated ids
     */
    function fetchChildrensChildrenIds($ids){
        $sql = "
        select GROUP_CONCAT(project_id) as ids from project_clone c where c.project_id_parent in (".$ids.") and is_valid=1
        ";
        $resultSet = $this->_db->fetchRow($sql);
        return $resultSet['ids'];
    }

    /**
     * @return string comma seperated ids
     */
    function fetchParentIds($project_id){
        $sql = "
        select GROUP_CONCAT(distinct project_id_parent) as ids from project_clone c where c.project_id = :project_id and c.is_valid=1
        and c.project_id_parent >0
        ";
        $resultSet = $this->_db->fetchRow($sql, array('project_id' => $project_id));
        return $resultSet['ids'];
    }

    /**
     * @return string siblings project ids without itself
     */
    function fetchSiblings($project_id){
            $sql = "
                select GROUP_CONCAT(distinct project_id) as ids from project_clone c where c.project_id_parent in (
                        select project_id_parent from project_clone c where c.project_id = :project_id and  c.is_valid=1
                ) and c.project_id <> :project_id and c.is_valid=1
            ";
            $resultSet = $this->_db->fetchRow($sql, array('project_id' => $project_id));     
            return $resultSet['ids'];
    }

    /**
     * @return string comma seperated ids
     */
    function fetchAncestersIds($project_id,$level=5){
        
        $parentIds = self::fetchParentIds($project_id);
        $ids='';        
        while ($level>0 && strlen($parentIds)>0) {
              $sql = "select GROUP_CONCAT(distinct project_id_parent) as ids from project_clone c where c.project_id in(".$parentIds.") and c.is_valid=1 and c.project_id_parent>0";
              $resultSet = $this->_db->fetchRow($sql);     
              if($resultSet['ids'])
              {
                      $ids.=','.$resultSet['ids'];
              }
              else{
                      break;
              }
              $parentIds = $resultSet['ids'];              
              $level--;
        }    
        if(substr($ids, 0, 1)==','){ $ids=substr($ids,1);};    
        return $ids;
    }

    function fetchParentLevelRelatives($project_id){
        $ancesters = self::fetchAncestersIds($project_id);
        $sql = "
                select GROUP_CONCAT(project_id) as ids from project_clone c where c.project_id_parent in (".$ancesters.") and is_valid=1
        ";
        $resultSet = $this->_db->fetchRow($sql);
        return $resultSet['ids'];
    }
    function fetchSiblingsLevelRelatives($parentids,$project_id){        
        $sql = "
                select GROUP_CONCAT(project_id) as ids from project_clone c where c.project_id_parent in (".$parentids.") and is_valid=1
                        and c.project_id <> :project_id
        ";
        $resultSet = $this->_db->fetchRow($sql,array('project_id' =>$project_id));
        return $resultSet['ids'];
    }
} 