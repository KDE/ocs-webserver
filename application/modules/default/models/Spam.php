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
 *
 * Created: 31.05.2017
 */
class Default_Model_Spam
{

    const SPAM_THRESHOLD = 1;

    /**
     * naive approach for spam detection
     * @param array $project_data
     *
     * @return bool
     * @todo: define a list of stop words
     *
     */
    public static function hasSpamMarkers($project_data)
    {
        try {
            $active = (boolean)Zend_Registry::get('config')->settings->spam->filter->active;
        } catch (Zend_Exception $e) {
            $active = false;
        }

        if (false === $active) {
            return false;
        }

        $sql = "SELECT `spam_key_word` FROM `spam_keywords` WHERE `spam_key_is_active` = 1 AND `spam_key_is_deleted` = 0";
        $keywords = Zend_Db_Table::getDefaultAdapter()->fetchCol($sql);

        $needles = implode('|', $keywords);

        $haystack = implode(" ", array($project_data['title'], $project_data['description']));

        if (preg_match("/({$needles})/i", $haystack)) {
            return true;
        }

        return false;
    }

    public function fetchSpamCandidate()
    {
        $sql = "
            SELECT *
            FROM `stat_projects`
            WHERE `stat_projects`.`amount_reports` >= :threshold AND `stat_projects`.`status` = 100
            ORDER BY `stat_projects`.`changed_at` DESC, `stat_projects`.`created_at` DESC, `stat_projects`.`amount_reports` DESC
        ";

        $result = Zend_Db_Table::getDefaultAdapter()->query($sql, array('threshold' => self::SPAM_THRESHOLD));
        if ($result->rowCount() > 0) {
            return $result->fetchAll();
        } else {
            return array();
        }
    }

}