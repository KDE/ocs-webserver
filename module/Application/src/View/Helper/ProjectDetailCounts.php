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

namespace Application\View\Helper;

use Application\Model\Repository\ProjectRepository;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\View\Helper\AbstractHelper;

class ProjectDetailCounts extends AbstractHelper
{
    protected $projTable;

    /**
     * ProjectDetailCounts constructor.
     *
     * @param ProjectRepository $projTable
     */
    public function __construct(ProjectRepository $projTable)
    {
        $this->projTable = $projTable;
    }

    /**
     * for project detail show count info:
     *        page views today
     *        page views total
     *
     * @param int $project_id
     *
     * @return array|ResultSet
     */
    public function __invoke($project_id)
    {
        /** @var AbstractAdapter $cache */
        $cache = $GLOBALS['ocs_cache'];
        $oldTtl = $cache->getOptions()->getTtl();
        $key = "stat_page_views_48h_{$project_id}";
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

        if ($cache->hasItem($key)) {
            $resultSet = $cache->getItem($key, $failure, $token);

            return $resultSet;
        }

        $resultSet = $this->projTable->fetchAll($sql, array('project_id' => $project_id));
        $cache->getOptions()->setTtl(60);
        $cache->setItem($key, $resultSet);
        $cache->getOptions()->setTtl($oldTtl);

        return $resultSet;
    }

}