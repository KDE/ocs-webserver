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
class Statistics_Model_Stat
{

    public function dailyPageviews()
    {
       $sql = '
                INSERT INTO stat_daily_pageviews 
                SELECT project.project_id, count(stat_page_views.project_id) AS cnt, project.project_category_id, CURDATE() AS created_at
                FROM project
                JOIN stat_page_views on project.project_id = stat_page_views.project_id AND stat_page_views.created_at > DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                WHERE project.type_id = '.Default_Model_Project::PROJECT_TYPE_STANDARD.'
                GROUP BY project.project_id;
       ';
       $database = Zend_Db_Table::getDefaultAdapter();
       $database->query($sql)->execute();      
    }

}
