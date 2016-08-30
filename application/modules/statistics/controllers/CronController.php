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
class Statistics_CronController extends Local_Controller_Action_CliAbstract
{

    const DATE_FORMAT = "Y-m-d";

    public function runAction()
    {
        $today = new DateTime();
//        $yesterday = $today->sub(new DateInterval("P1D"));

        $validator = new Zend_Validate_Date(array('format' => self::DATE_FORMAT));

        $date = $this->getRequest()->getParam('date', $today->format(self::DATE_FORMAT));
        if ($validator->isValid($date)) {
            $statistics = new Statistics_Model_GoalStatistics();
            $result = $statistics->generateDailyStatistics($date);

            foreach (get_object_vars($result) as $name => $value) {
                echo "$name: $value\n";
            }

        }
    }


    public function dailypageviewsAction()
    {
        $statistics = new Statistics_Model_GoalStatistics();
        $statistics->dailyPageviews();
    }
    

    public function migrateAction()
    {
        $statistics = new Statistics_Model_GoalStatistics();
        $result = $statistics->migrateStatistics();

        foreach (get_object_vars($result) as $name => $value) {
            echo "$name: $value\n";
        }
    }

}
