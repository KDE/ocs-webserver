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

class Default_Model_SubCategory extends Default_Model_DbTable_ProjectSubCategory
{

    const ORDERED_TITLE = 'title';
    const ORDERED_ID = 'project_category_id';
    const ORDERED_HIERARCHIC = 'lft';

    /**
     * @param array $categories
     * @param int $product_id
     * @throws Exception
     */
    public function saveSubCategoriesForProject($categories, $product_id)
    {
        if (false == is_array($categories)) {
            throw new Exception('Categories should be an array.');
        }

        $sql = "DELETE FROM " . $this->_name . " WHERE project_id = ? AND project_sub_category_id NOT IN (" . implode(',', $categories) . ")";
        $sql = $this->_db->quoteInto($sql, $product_id, 'INTEGER', 1);
        $deleteOldCategories = $this->_db->query($sql);
        $deleteOldCategories->execute();

        foreach ($categories as $key => $category) {
            $sql = 'project_id = ? AND project_sub_category_id = ?';
            $sql = $this->_db->quoteInto($sql, $product_id, 'INTEGER', 1);
            $sql = $this->_db->quoteInto($sql, $category, 'INTEGER', 1);
            if ($this->fetchRow($sql)) {
                continue;
            }

            $newElement = $this->createRow();
            $newElement->project_id = $product_id;
            $newElement->project_sub_category_id = $category;

            $newElement->save();
        }

    }

    /**
     * @param $identifier
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchSelectedSubCategories($identifier)
    {
        $sql = "
                SELECT
                    a.project_sub_category_id, b.title
                FROM
                    project_subcategory AS a
                        JOIN
                    project_category AS b ON (a.project_sub_category_id = b.project_category_id)
                WHERE
                    a.project_id = ?;
               ";

        $sql = $this->_db->quoteInto($sql, $identifier, 'INTEGER');

        $resultSet = $this->_db->fetchAll($sql);

        $rows = array();
        if (count($resultSet > 0)) {
            foreach ($resultSet as $row) {
                $rows[] = $row['project_sub_category_id'];
            }
        }

        return $rows;
    }

    /**
     * @param int $category_id
     * @param string $orderBy
     * @return array
     */
    public function fetchAllSubCategories($category_id, $orderBy = self::ORDERED_HIERARCHIC)
    {
        $tableCategories = new Default_Model_DbTable_ProjectCategory();

        $resultSet = $tableCategories->fetchImmediateChildren($category_id, $orderBy);

        $rows = array();
        if (count($resultSet > 0)) {
            foreach ($resultSet as $row) {
                $rows[$row['project_category_id']] = $row['title'];
            }
        }

        return $rows;

    }

    /**
     * @param int $identifier
     * @return int
     */
    public function countProductsInCategory($identifier)
    {
        $sql = "
            SELECT
                count(1) AS counterProduct
            FROM
                project_subcategory
               JOIN project ON (project.project_id = project_subcategory.project_id AND project.status = :project_status AND type_id = 1)
            WHERE
                project_sub_category_id = :sub_cat_id
            GROUP BY project_sub_category_id
            ;
        ";

        $resultSet = $this->_db->fetchAll($sql, array('sub_cat_id' => $identifier, 'project_status' => Default_Model_Project::PROJECT_ACTIVE));

        if (count($resultSet) == 0) {
            return 0;
        }

        return (int)$resultSet[0]['counterProduct'];
    }

    public function fetchSubcategoriesForProduct($productId)
    {
        $sql = "SELECT ps.project_sub_category_id, pc.title
                FROM project_subcategory AS ps
                JOIN project_category AS pc ON ps.project_sub_category_id = pc.project_category_id
                WHERE ps.project_id = :projectId
                AND pc.is_active = 1
                ";
        return $this->_db->fetchAll($sql, array('projectId' => $productId));
    }

} 