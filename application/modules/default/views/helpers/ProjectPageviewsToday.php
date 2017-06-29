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
class Default_View_Helper_ProjectPageviewsToday extends Zend_View_Helper_Abstract
{

    /**
     * @param int $ppload_collection_id
     *
     * @return array
     */
    public function projectPageviewsToday ($project_id)
    {
        $sql
            = "
                SELECT
                    `project_id`,
                    `count_views`,
                    `count_visitor`,
                    `last_view`
                FROM
                    `stat_page_views_today_mv`
                WHERE `project_id` = ?
                ";
        $database = Zend_Db_Table::getDefaultAdapter();
        $sql = $database->quoteInto($sql, $project_id, 'INTEGER', 1);
        $resultSet = $database->query($sql)->fetchAll();

        if (count($resultSet) > 0) {
            $result = $resultSet[0]['count_views'];
        } else {
            $result = 0;
        }

        return $result;
    }

}