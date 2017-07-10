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
class Default_Model_ProjectUpdates extends Default_Model_DbTable_ProjectUpdates
{

    /**
     * @param $project_id
     *
     * @return array
     */
    public function fetchProjectUpdates($project_id)
    {
        $sql = '
                SELECT *
                    FROM project_updates
                    WHERE project_id = :project_id
                      AND public = 1
                    ORDER BY created_at DESC
        ';

        $rowset = $this->_db->fetchAll($sql, array('project_id' => $project_id));

        return $rowset;
    }

}