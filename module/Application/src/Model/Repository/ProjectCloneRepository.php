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

use Application\Model\Entity\ProjectClone;
use Application\Model\Interfaces\ProjectCloneInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ProjectCloneRepository extends BaseRepository implements ProjectCloneInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "project_clone";
        $this->_key = "project_clone_id";
        $this->_prototype = ProjectClone::class;
    }

    public function setDelete($project_clone_id)
    {
        $this->setIsDeleted($project_clone_id);
    }

    public function setValid($project_clone_id)
    {
        $this->update(['is_valid' => 1, 'project_clone_id' => $project_clone_id]);
    }

    /**
     * @param array $data
     *
     * @return ProjectClone
     */
    public function generateRowSet($data)
    {
        $clone = new ProjectClone();
        $clone->exchangeArray($data);

        return $clone;
    }

    public function listAll($startIndex = null, $pageSize = null, $sorting = null)
    {
        $rows = $this->fetchAllRows(['is_deleted' => 0], $sorting, $pageSize, $startIndex);
        $count = $this->fetchAllRowsCount(['is_deleted' => 0]);
        if (empty($rows)) {
            return array('rows' => array(), 'totalCount' => 0);
        }

        return array('rows' => $rows, 'totalCount' => $count);
    }

}