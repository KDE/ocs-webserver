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
class Default_Model_DbTable_ProjectPlings extends Zend_Db_Table_Abstract
{

    protected $_name = "project_plings";


    public function getPlings($project_id)
    {
        $statement = $this->select()
            ->where('project_id=?', $project_id);
        return $this->fetchAll($statement);
    }


    public function getPling($project_id,$member_id)
    {
        $statement = $this->select()
            ->where('project_id=?', $project_id)
            ->where('member_id=?', $member_id);
        return $this->fetchRow($statement);
    }

    public function setDelete($id)
    {
        $updateValues = array(
            'is_deleted' => 1,
            'deleted_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, 'project_plings_id=' . $id);
    }

    public function countPlingsHeGave($member_id)
    {        
        $sql ="
                SELECT count(*) AS count 
                FROM project_plings f   
                WHERE  f.member_id =:member_id and f.is_deleted = 0
        ";
        $resultRow = $this->_db->fetchRow($sql, array('member_id' => $member_id));
        return $resultRow['count'];
    }

    public function countPlingsHeGot($member_id)
    {       
        $sql ="
                SELECT count(*) AS count 
                FROM project_plings f  
                inner join stat_projects p on p.project_id = f.project_id and p.status = 100 
                WHERE  p.member_id =:member_id
        ";
        $resultRow = $this->_db->fetchRow($sql, array('member_id' => $member_id));
        return $resultRow['count'];
    }

}