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
     * @param int    $category_id
     * @param string $orderBy
     *
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

}