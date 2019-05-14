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

class Default_View_Helper_FetchScoreColor extends Zend_View_Helper_Abstract
{

    public function fetchScoreColor($score)
    {
        
        	$score2 = $score;   
            $blue2 = $red2 = $green2 = $default2=200;
            if($score2>=5) {
                $red2=dechex($default2-(($score2*10-50)*4));
                $green2=dechex($default2);
                $blue2=dechex($default2-(($score2*10-50)*4));
            }elseif($score2<5) {
                $red2=dechex($default2);
                $green2=dechex($default2-((50-$score2*10)*4));
                $blue2=dechex($default2-((50-$score2*10)*4));
            }
            if(strlen($green2)==1) $green2='0'.$green2;
            if(strlen($red2)==1) $red2='0'.$red2;
            if(strlen($blue2)==1) $blue2='0'.$blue2;
            return '#'.$red2.$green2.$blue2;
    }

} 