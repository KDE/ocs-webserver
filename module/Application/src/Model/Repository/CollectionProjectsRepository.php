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

namespace Application\Model\Repository;

use Application\Model\Entity\CollectionProjects;
use Application\Model\Interfaces\CollectionProjectsInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;

class CollectionProjectsRepository extends BaseRepository implements CollectionProjectsInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "collection_projects";
        $this->_key = "collection_project_id";
        $this->_prototype = CollectionProjects::class;
    }

    public function getProjectsForMember($collection_id, $member_id, $search)
    {

        $withoutProjectIds = implode(',', $this->getCollectionProjectIds($collection_id));
        if (empty($withoutProjectIds)) {
            $withoutProjectIds = "0";
        }

        $sql = " SELECT project.title, project.project_id, project.image_small, project.username, project.member_id, project.cat_title,project.laplace_score
                 FROM stat_projects project
                 WHERE project.member_id = " . $member_id . "
                 AND project.type_id = 1
                 AND project.status = 100
                 AND project.project_id not in ($withoutProjectIds)
                 AND (project.title like('%" . $search . "%'))
                 ORDER BY project.changed_at desc, project.created_at DESC
                 LIMIT 50";
        $statement = $this->db->query($sql);

        return $statement->execute();
    }

    public function getCollectionProjectIds($collection_id)
    {
        $sql = " SELECT project.project_id
                 FROM collection_projects
                 JOIN project ON project.project_id = collection_projects.project_id
                 WHERE collection_projects.collection_id = " . $collection_id . "
                 AND collection_projects.active = 1
                 AND project.type_id = 1
                 AND project.status = 100
                 ORDER BY collection_projects.order ASC";
        $statement = $this->db->query($sql);
        /** @var ResultSet $resultSet */
        $resultSet = $statement->execute();

        $result = array();
        foreach ($resultSet as $projectId) {
            $result[] = $projectId['project_id'];
        }


        return $result;
    }

    public function getProjectsForAllMembers($collection_id, $member_id, $search)
    {
        $withoutProjectIds = implode(',', $this->getCollectionProjectIds($collection_id));
        if (empty($withoutProjectIds)) {
            $withoutProjectIds = "0";
        }

        $sql = " SELECT project.title, project.project_id, project.image_small, project.username, project.member_id, project.cat_title,project.laplace_score
                 FROM stat_projects project
                 WHERE project.member_id <> " . $member_id . "
                 AND project.type_id = 1
                 AND project.status = 100
                 AND project.project_id not in ($withoutProjectIds)
                 AND (project.title like('%" . $search . "%'))
                 ORDER BY project.changed_at desc, project.created_at DESC
                 LIMIT 50";
        $statement = $this->db->query($sql);
        /** @var ResultSet $resultSet */
        $resultSet = $statement->execute();


        return $resultSet;
    }

    public function countProjects($collection_id)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql = "
                SELECT count(*) AS count 
                FROM collection_projects f   
                WHERE  f.collection_id = " . $fp('id') . " and f.active = 1
        ";
        $statement = $this->db->query($sql);
        /** @var ResultSet $resultSet */
        $resultSet = $statement->execute(array('id' => $collection_id));
        $row = $resultSet->current();

        return $row['count'];
    }

    public function setCollectionProjects($collectionId, $projectIds)
    {

        //Delete old projects
        // $oldIds = $this->getCollectionProjects($collectionId);

        // foreach ($oldIds as $oldProjectId) {
        //     $this->setInactive($collectionId, $oldProjectId['project_id']);
        // }

        //
        //Insert new ones
        // foreach (array_keys($projectIds) as $fieldKey) {
        //     $projectId = $projectIds[$fieldKey];
        //     $this->createCollectionProject($collectionId, $projectId, $fieldKey);
        // }

        // inactive old
        $this->setInactiveAll($collectionId);
        // insert new
        $values = [];       
        foreach (array_keys($projectIds) as $fieldKey) {
            $projectId = $projectIds[$fieldKey];
            $values[] = "({$collectionId},{$projectId},{$fieldKey},1,now())";
        }                        
        $sql = "INSERT INTO collection_projects (collection_id, project_id, `order`,active,created_at) VALUES " . implode(
                ',', $values
            );
        $this->query($sql);
    }

    public function getCollectionProjects($collection_id)
    {
        
        $sql = "SELECT project.title
                    , project.project_id
                    , project.image_small
                    , member.username
                    , project.member_id
                    , collection_projects.order
                    , project.ppload_collection_id
                    , project.project_category_id
                    , project.description                   
                    , project_category.title as cat_title
                    , project_category.xdg_type
                FROM collection_projects
                JOIN project project ON project.project_id = collection_projects.project_id
                join member member on project.member_id = member.member_id
                join project_category on project.project_category_id = project_category.project_category_id
                WHERE collection_projects.collection_id = :collection_id
                AND collection_projects.active = 1
                AND project.type_id = 1
                AND project.status = 100
                ORDER BY collection_projects.order ASC
                limit 100
                ";
        
        $statement = $this->db->query($sql);
        /** @var ResultSet $resultSet */
        $resultInterface = $statement->execute(['collection_id' => $collection_id]);
        $resultSet = new ResultSet();
        $result = array();
        if ($resultInterface instanceof ResultInterface && $resultInterface->isQueryResult()) {
            $resultSet->initialize($resultInterface);
            $result = $resultSet->toArray();
            // $this->writeCache($cache_name, $result);
        }

        return $result;
    }

    public function setInactiveAll($collection_id)
    {
        $values = array();
        $values['active'] = 0;
        $values['deleted_at'] = new Expression("NOW()");

        $sql = new Sql($this->db);
        $update = $sql->update($this->_name)->set($values)->where(
            [
                'collection_id' => $collection_id,    
                'active'    => 1 
            ]
        );
        $statement = $sql->prepareStatementForSqlObject($update);
        return $statement->execute();
    }

    public function setInactive($collection_id, $project_id)
    {
        $values = array();

        $values['active'] = 0;
        $values['deleted_at'] = new Expression("NOW()");

        $sql = new Sql($this->db);
        $update = $sql->update($this->_name)->set($values)->where(
            [
                'collection_id' => $collection_id,
                'project_id'    => $project_id,
            ]
        );
        $statement = $sql->prepareStatementForSqlObject($update);

        return $statement->execute();
    }

    public function createCollectionProject($collection_id, $project_id, $order)
    {
        $values = array();

        $values['collection_id'] = $collection_id;
        $values['project_id'] = $project_id;
        $values['order'] = $order;
        $values['active'] = 1;
        $values['created_at'] = new Expression("NOW()");

        $savedRowId = $this->insert($values);
        $obj = null;
        if ($savedRowId) {
            $obj = $this->findById($savedRowId);
        }

        return $obj;
    }
}
