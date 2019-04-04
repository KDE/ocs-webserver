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
class Default_Model_DbTable_CollectionProjects extends Local_Model_Table
{
    protected $_keyColumnsForRow = array('collection_project_id');
    protected $_name = "collection_projects";


    public function getCollectionProjects($project_id)
    {
        $sql = " SELECT project.title, project.project_id, project.image_small, member.username, member.member_id,collection_projects.order
                 FROM collection_projects
                 JOIN project ON project.project_id = collection_projects.project_id
                 JOIN member ON member.member_id = project.member_id
                 WHERE collection_projects.collection_id = :project_id
                 AND collection_projects.active = 1
                 AND project.type_id = 1
                 AND project.status = 100
                 ORDER BY collection_projects.order ASC";
        $resultSet = $this->getAdapter()->fetchAll($sql, array('project_id' => $project_id));
        
        
        return $resultSet;
    }
    
    public function getCollectionProjectIds($project_id)
    {
        $sql = " SELECT project.project_id
                 FROM collection_projects
                 JOIN project ON project.project_id = collection_projects.project_id
                 WHERE collection_projects.collection_id = :project_id
                 AND collection_projects.active = 1
                 AND project.type_id = 1
                 AND project.status = 100
                 ORDER BY collection_projects.order ASC";
        $resultSet = $this->getAdapter()->fetchAll($sql, array('project_id' => $project_id));
        
        $result = array();
        foreach ($resultSet as $projectId) {
            $result[] = $projectId['project_id'];
        }
        
        
        return $result;
    }
    
    
    public function getProjectsForMember($collection_id, $member_id, $search)
    {
        
        $withoutProjectIds = implode(',',$this->getCollectionProjectIds($collection_id));
        if(empty($withoutProjectIds)) {
            $withoutProjectIds = "0";
        }
        
        $sql = " SELECT project.title, project.project_id, project.image_small, member.username, member.member_id
                 FROM project
                 JOIN member ON member.member_id = project.member_id
                 WHERE project.member_id = :member_id
                 AND project.type_id = 1
                 AND project.status = 100
                 AND project.project_id not in ($withoutProjectIds)
                 AND (project.title like('%".$search."%'))
                 ORDER BY project.changed_at desc, project.created_at DESC
                 LIMIT 50";
        $resultSet = $this->getAdapter()->fetchAll($sql, array('member_id' => $member_id));
        
        
        return $resultSet;
    }
    
    
    public function getProjectsForAllMembers($collection_id, $member_id, $search)
    {
        
        $withoutProjectIds = implode(',',$this->getCollectionProjectIds($collection_id));
        if(empty($withoutProjectIds)) {
            $withoutProjectIds = "0";
        }
        
        $sql = " SELECT project.title, project.project_id, project.image_small, member.username, member.member_id
                 FROM project
                 JOIN member ON member.member_id = project.member_id
                 WHERE project.member_id <> :member_id
                 AND project.type_id = 1
                 AND project.status = 100
                 AND project.project_id not in ($withoutProjectIds)
                 AND (project.title like('%".$search."%'))
                 ORDER BY project.changed_at desc, project.created_at DESC
                 LIMIT 50";
        $resultSet = $this->getAdapter()->fetchAll($sql, array('member_id' => $member_id));
        
        
        return $resultSet;
    }


    

    public function setInactive($collection_id, $project_id)
    {
        $values = array();

        $values['active'] = 0;
        $values['deleted_at'] = new Zend_Db_Expr('NOW()');

        $savedRow = $this->update($values, 'collection_id = '.$collection_id . ' AND project_id = ' . $project_id);


        return $savedRow;
    }
    
    public function createCollectionProject($collection_id, $project_id, $order)
    {
        $values = array();

        $values['collection_id'] = $collection_id;
        $values['project_id'] = $project_id;
        $values['order'] = $order;
        $values['active'] = 1;
        $values['created_at'] = new Zend_Db_Expr('NOW()');

        $savedRow = $this->save($values);

        return $savedRow;
    }

    public function countProjects($project_id)
    {        
        $sql ="
                SELECT count(*) AS count 
                FROM collection_projects f   
                WHERE  f.collection_id =:project_id and f.active = 1
        ";
        $resultRow = $this->_db->fetchRow($sql, array('project_id' => $project_id));
        return $resultRow['count'];
    }
    
    public function setCollectionProjects($collectionId, $projectIds) {
        
        
        //Delete old projects
        $oldIds = $this->getCollectionProjects($collectionId);
        
        foreach ($oldIds as $oldProjectId) {
            $this->setInactive($collectionId, $oldProjectId['project_id']);
        }
        
        //Insert new ones
        foreach (array_keys($projectIds) as $fieldKey) {
            $projectId = $projectIds[$fieldKey];
            $this->createCollectionProject($collectionId, $projectId, $fieldKey);
        }
        
    }

    
}