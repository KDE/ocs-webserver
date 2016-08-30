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
class Default_View_Helper_FetchCategoriesForProductAsString extends Zend_View_Helper_Abstract
{

    public function fetchCategoriesForProductAsString($productId)
    {

/*
        $modelSubcategories = new Default_Model_SubCategory();
        $result = $modelSubcategories->fetchSubcategoriesForProduct($productId);

        if (count($result) == 0) {
            $modelCategories = new Default_Model_DbTable_ProjectCategory();
            $result = $modelCategories->fetchMainCategoryForProduct($productId);
        }

        $resultString = '';
        foreach ($result as $element) {
            $resultString .= $element['title'] . ',';
        }
        return substr($resultString, 0, -1);
    

    */

        $modelCategories = new Default_Model_DbTable_ProjectCategory();
        $result = $modelCategories->fetchMainCategoryForProduct($productId);
        return  $result[0]['title'];
    }

}