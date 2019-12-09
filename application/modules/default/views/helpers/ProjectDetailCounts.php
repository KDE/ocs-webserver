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
class Default_View_Helper_ProjectDetailCounts extends Zend_View_Helper_Abstract
{
    /*
    * for project detail show count info:              
            page views today 
            page views total
    */
    public function projectDetailCounts($project_id)
    {
        $sql = "
                SELECT
                 count(1) AS `count_views`
                 FROM
                     `stat_page_views_48h`
                 WHERE `project_id` = :project_id
                 AND `created_at` >= subdate(NOW(), 1)
                UNION
                SELECT
                 count(1) AS `count_views`
                 FROM
                 `stat_page_views`
                 WHERE `project_id` = :project_id             
                ";

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array('project_id' => $project_id));

        return $resultSet;
    }

}