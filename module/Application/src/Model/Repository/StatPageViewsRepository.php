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

use Application\Model\Entity\StatPageViews;
use Application\Model\Interfaces\StatPageViewsInterface;
use Exception;
use Laminas\Db\Adapter\AdapterInterface;

class StatPageViewsRepository extends BaseRepository implements StatPageViewsInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "stat_page_views";
        $this->_key = "stat_page_views_id";
        $this->_prototype = StatPageViews::class;
    }

    public function savePageView($project_id, $clientIp, $member_id)
    {

        if (SEARCHBOT_DETECTED) { // we don't save a page view when a search bot was detected

            return;
        }
        if (false == $GLOBALS['ocs_config']->settings->savePageView) {
            return;
        }

        $sql = "INSERT LOW_PRIORITY INTO {$this->_name} (`project_id`, `ip`, `member_id`) 
                VALUES (:param1, :param2, :param3);";
        try {
            $query = $this->db->query($sql);
            $result = $query->execute(
                array(
                    'param1' => $project_id,
                    'param2' => $clientIp,
                    'param3' => $member_id,
                )
            );

        } catch (Exception $e) {
            error_log(__METHOD__ . ' - ' . $e->getMessage());
        }

        /**
         * try {
         * $this->db->query("INSERT LOW_PRIORITY INTO {$this->_name} (`project_id`, `ip`, `member_id`)
         * VALUES (:param1, :param2, :param3);",
         * array(
         * 'param1' => $project_id,
         * 'param2' => $clientIp,
         * 'param3' => $member_id
         * ));
         * } catch (Exception $ex) {
         * error_log(__METHOD__ . ' - ' . $ex->getMessage());
         * }
         *
         */
    }

}