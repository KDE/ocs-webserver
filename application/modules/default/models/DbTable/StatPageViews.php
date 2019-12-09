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
class Default_Model_DbTable_StatPageViews extends Zend_Db_Table_Abstract
{

    protected $_name = "stat_page_views";

    public function savePageView($project_id, $clientIp, $member_id)
    {
        if (SEARCHBOT_DETECTED) { // we don't save a page view when a search bot was detected

            return;
        }
        if (false == Zend_Registry::get('config')->settings->savePageView) {

            return;
        }

        //$this->_db->beginTransaction();
        try {
            $this->_db->query("INSERT LOW_PRIORITY INTO {$this->_name} (`project_id`, `ip`, `member_id`) VALUES (:param1, :param2, :param3);",
                array(
                    'param1' => $project_id,
                    'param2' => $clientIp,
                    'param3' => $member_id
                ));
//            $this->_db->commit();
        } catch (Exception $ex) {
//            $this->_db->rollBack();
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . $ex->getMessage());
        }
    }

}