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
class Default_View_Helper_FetchDomainCategories extends Zend_View_Helper_Abstract
{

    public function fetchDomainCategories($idCat = null)
    {
        $tableCategories = new Default_Model_DbTable_ProjectCategory();
        if (isset($idCat)) {
            return $tableCategories->fetchActive($idCat);
        }
        return $tableCategories->fetchMainCategories();
    }
    
    public function fetchDomainCategoriesOrdered($idCat = null)
    {
        if (false === is_array($idCat)) {
            $idCat = array($idCat);
        }
        $arr = array();
        if (isset($idCat)) {
            $helperFetchCategory = new Default_View_Helper_FetchCategory();
            foreach ($idCat as $row) {
                    $category = $helperFetchCategory->fetchCategory(($row));
                    $arr[] = $category;
            }                
            return $arr;
        }
        $tableCategories = new Default_Model_DbTable_ProjectCategory();
        return $tableCategories->fetchMainCatsOrdered();
    }

}