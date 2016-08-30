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

class Default_Model_DbTable_ProjectWidget extends Local_Model_Table
{
    protected $_key = 'widget_id';

    protected $_keyColumnsForRow = array('uuid');

    protected $_name = 'project_widget';

    protected $_primary = 'widget_id';

    public function insert(array $data)
    {
        if (empty($data['uuid'])) {
            $data['uuid'] = md5($data['config']);
        }

        return parent::insert($data);
    }

    public function save($data)
    {
        $tempUuid = $this->generateConfigUuid($data);

        $data = $this->testConfigChanged($data, $tempUuid);

        return parent::save($data);
    }

    /**
     * @param $data
     * @return string
     */
    protected function generateConfigUuid($data)
    {
        $tempUuid = md5($data['config']);
        return $tempUuid;
    }

    /**
     * @param $data
     * @param $tempUuid
     * @return mixed
     */
    protected function testConfigChanged($data, $tempUuid)
    {
        if ($tempUuid != $data['uuid']) {
            $data['uuid'] = $tempUuid;
            return $data;
        }
        return $data;
    }

} 