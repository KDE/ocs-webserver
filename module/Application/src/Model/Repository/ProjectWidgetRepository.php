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

namespace Application\Model\Repository;

use Application\Model\Entity\ProjectWidget;
use Application\Model\Interfaces\ProjectWidgetInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ProjectWidgetRepository extends BaseRepository implements ProjectWidgetInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "project_widget";
        $this->_key = "widget_id";
        $this->_prototype = ProjectWidget::class;
    }

    public function insert($data)
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

        return parent::update($data);
    }

    /**
     * @param $data
     *
     * @return string
     */
    protected function generateConfigUuid($data)
    {
        return md5($data['config']);
    }

    /**
     * @param $data
     * @param $tempUuid
     *
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