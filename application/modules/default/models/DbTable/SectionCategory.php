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
class Default_Model_DbTable_SectionCategory extends Local_Model_Table
{

    protected $_keyColumnsForRow = array('section_category_id');
    protected $_key = 'section_category_id';
    protected $_name = "section_store_category";
    

    /**
     * @param int $dataId
     */
    public function deleteId($dataId)
    {
        $sql = "DELETE FROM {$this->_name} WHERE {$this->_key} = ?";
        $this->_db->query($sql,$dataId)->execute();
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function fetchAllCategoriesForSection($sectionId)
    {
        $active = Default_Model_DbTable_ProjectCategory::CATEGORY_ACTIVE;
        $notDeleted = Default_Model_DbTable_ProjectCategory::CATEGORY_NOT_DELETED;
        $sql = "
                SELECT pc2.project_category_id
                FROM project_category AS pc, project_category AS pc2
                WHERE pc.project_category_id IN (SELECT DISTINCT csc.project_category_id
                                                 FROM section_category AS csc
                                                   JOIN project_category AS pc ON csc.project_category_id = pc.project_category_id AND pc.is_active = 1
                                                 WHERE csc.section_id = :sectionId)
                      AND pc2.lft BETWEEN pc.lft AND pc.rgt
                      AND pc2.is_active = {$active} AND pc2.is_deleted = {$notDeleted}
                ORDER BY pc2.lft;
                ";
        $results = $this->_db->fetchAll($sql, array('sectionId' => $sectionId));
        $values = array_map(function($row) { return $row['project_category_id']; }, $results);
        return $values;
    }

    /**
     * @param int|array $listCatId
     * @return array
     */
    public function fetchSectionForCatdId($listCatId)
    {
        $inQuery = '?';
        if (is_array($listCatId)) {
            $inQuery = implode(',', array_fill(0, count($listCatId), '?'));
        }

        $sql = '
            SELECT cs.section_id, cs.section_id_name 
            FROM section_category as csc
            join section as cs on cs.section_id = csc.section_id 
            where csc.project_category_id in ('.$inQuery.')
        ';
        
        $result = $this->_db->query($sql, $listCatId)->fetchAll();
        
        if (count($result) > 0) {
            return $result;
        } else {
            return array();
        }
    }

    public function fetchCatIdsForSection($section_id)
    {
        $sql = "
            SELECT csc.project_category_id 
            FROM section_category AS csc
            JOIN project_category AS pc ON pc.project_category_id = csc.project_category_id
            WHERE csc.section_id = :section_id
            AND csc.deleted_at IS NULL
             ORDER BY csc.`order`, pc.title
        ";
        $results = $this->_db->fetchAll($sql, array('section_id' => $section_id));
        $values = array_map(function($row) { return $row['project_category_id']; }, $results);
        return $values;
    }
    
    public function updateSectionPerCategory($cat_id,$section_id=null)
    {
        $sql = "delete from section_category where project_category_id=:cat_id";
        $this->getAdapter()->query($sql, array('cat_id' => $cat_id));

        if(!empty($section_id)) {
            $sql = "INSERT IGNORE INTO section_category (project_category_id, section_id) VALUES ($cat_id,$section_id)";
            $this->getAdapter()->query($sql);
        }

        
    }


}