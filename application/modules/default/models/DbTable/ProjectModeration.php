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
class Default_Model_DbTable_ProjectModeration extends Local_Model_Table
{

    protected $_name = "project_moderation";

    protected $_keyColumnsForRow = array('project_moderation_id');

    protected $_key = 'project_moderation_id';

    protected $_defaultValues = array(
        'project_moderation_id'   => null,
        'project_moderation_type_id'  => null,
        'project_id'  => null,        
        'created_by'  => null,       
        'value' => null,
        'note' => null,
        'is_deleted'  => null,
        'is_valid'   => null,
        'created_at'  => null        
    );
 
    public function setDelete($project_moderation_id)
    {
        $updateValues = array(
            'is_deleted' => 1,
        );

        $this->update($updateValues, 'project_moderation_id=' . $project_moderation_id);
    }

    public function setValid($project_moderation_id)
    {
        $updateValues = array(
            'is_valid' => 1,
        );

        $this->update($updateValues, 'project_moderation_id=' . $project_moderation_id);
    }



    public function insertModeration($project_moderation_type_id,$project_id, $value,$created_by,$note)
    {
         $insertValues = array(
            'project_moderation_type_id' => $project_moderation_type_id,
            'project_id' => $project_id,
            'value' => $value,           
            'created_by' => $created_by,    
            'note' => $note
        );
        $this->_db->insert($this->_name, $insertValues);
        $resultIds[] = $this->_db->lastInsertId();
        return $resultIds;
    }

    // public function deleteModeration($project_moderation_id,$note)
    // {                                
    //      $updateValues = array(            
    //         'is_deleted' =>  1,
    //         'note' => $note        
    //     );      
    //     $this->update($updateValues, 'project_moderation_id=' . $project_moderation_id);        
    // }




    // public function updateInsertModeration($project_moderation_id,$updated_by,$note,$is_valid,$is_deleted)
    // {
    //         $row = $this->fetchRow(array('project_moderation_id = ?' => $project_moderation_id));    
    //         $row->is_deleted = 1;
    //         $row->save();
    //         $created_by = $row['updated_by']!=null ? $row['updated_by'] : $row['created_by'];   

    //          $insertValues = array(
    //             'project_moderation_type_id' =>$row->project_moderation_type_id,
    //             'project_id' => $row->project_id,
    //             'created_by' => $row->updated_by!=null ? $row->updated_by : $row->created_by,
    //             'created_at' => $row->updated_by!=null ? $row->updated_at : $row->created_at,
    //             'updated_by' =>$updated_by,
    //             'note' => $note,
    //             'is_deleted' => $is_deleted,
    //             'is_valid' => $is_valid
    //         );

    // }

}