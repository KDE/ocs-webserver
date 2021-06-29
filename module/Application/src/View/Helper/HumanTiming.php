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

namespace Application\View\Helper;

use DateTime;
use Laminas\View\Helper\AbstractHelper;

class HumanTiming extends AbstractHelper
{

    public function __invoke($strTime)
    {
        if (empty($strTime)) {
            return array('age' => null, 'unit' => null);
        }

        $date = DateTime::createFromFormat('Y-m-d H:i:s', $strTime);
        $now = new DateTime();
        $interval = $date->diff($now);

        $tokens = array(
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );

        foreach ($tokens as $unit => $text) {
            if ($interval->$unit == 0) {
                continue;
            }

            return array('age' => $interval->$unit, 'unit' => $text . (($interval->$unit > 1) ? 's' : ''));
        }
    }

} 