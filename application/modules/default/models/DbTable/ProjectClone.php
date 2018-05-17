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
class Default_Model_DbTable_ProjectClone extends Local_Model_Table
{

    protected $_name = "project_clone";

    protected $_keyColumnsForRow = array('project_clone_id');

    protected $_key = 'project_clone_id';

    protected $_defaultValues = array(
        'project_clone_id'   => null,
        'project_id'  => null,
        'project_id_parent'  => null,
        'external_link'  => null,
        'member_id'  => null,       
        'text' => null,
        'is_deleted'  => null,
        'is_valid'   => null,
        'created_at'  => null,
        'changed_at'  => null,
        'deleted_at'  => null
    );
 
    public function setDelete($project_clone_id)
    {
        $updateValues = array(
            'is_deleted' => 1,
        );

        $this->update($updateValues, 'project_clone_id=' . $project_clone_id);
    }

    public function setValid($project_clone_id)
    {
        $updateValues = array(
            'is_valid' => 1,
        );

        $this->update($updateValues, 'project_clone_id=' . $project_clone_id);
    }
   

    /**
     * @param array $data
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    protected function generateRowSet($data)
    {
        $classRowSet = $this->getRowsetClass();

        return new $classRowSet(array(
            'table'    => $this,
            'rowClass' => $this->getRowClass(),
            'stored'   => true,
            'data'     => $data
        ));
    }
}