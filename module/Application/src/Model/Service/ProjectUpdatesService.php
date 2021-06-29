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
 * */

namespace Application\Model\Service;

use Application\Model\Repository\ProjectUpdatesRepository;
use Application\Model\Service\Interfaces\ProjectUpdatesServiceInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ProjectUpdatesService extends BaseService implements ProjectUpdatesServiceInterface
{

    protected $db;
    private $projectUpdatesRepository;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
        $this->projectUpdatesRepository = new ProjectUpdatesRepository($db);
    }

    /**
     * @param $project_id
     *
     * @return array
     */
    public function fetchProjectUpdates($project_id)
    {
        $sql = '
                SELECT *
                    FROM `project_updates`
                    WHERE `project_id` = :project_id
                      AND `public` = 1
                    ORDER BY `created_at` DESC
        ';

        return $this->projectUpdatesRepository->fetchAll($sql, array('project_id' => $project_id));
    }

}
