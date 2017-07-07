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
class Default_View_Helper_PrintRatingWidgetSimple extends Zend_View_Helper_Abstract
{

    public function printRatingWidgetSimple($score,$count_likes,$count_dislikes)
    {

       $cssNoVotes = ($count_likes+$count_dislikes == 0) ? ';opacity:0.5;' : '';
        // calculate colour
        $blue = $red = $green = $default=200;
       
        if($score==0) 
            $score = 50;

        if($score>50) {
            $red=dechex($default-(($score-50)*4));
            $green=dechex($default);
            $blue=dechex($default-(($score-50)*4));
        }elseif($score<51) {
            $red=dechex($default);
            $green=dechex($default-((50-$score)*4));
            $blue=dechex($default-((50-$score)*4));
        }
        if(strlen($green)==1) $green='0'.$green;
        if(strlen($red)==1) $red='0'.$red;

        return $this->getHTML(
            floor($score ),
            $red, $green, $blue,
            floor((100 - $score) ),
            $cssNoVotes
            );
    }

    protected function getHTML($likes, $red, $green , $blue, $dislikes, $cssNoVotes)
    {
         return <<< _END_HTML_
                                   
                    <div class="rating-text">
                        <small class="center-block text-center">Score {$likes}%</small>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="background-color: #{$red}{$green}{$blue};width: {$likes}%;">
                           
                        </div>
                        <div class="progress-bar" style="background-color:#eeeeee;width: {$dislikes}%;{$cssNoVotes}">
                            
                        </div>
                    </div>
             
_END_HTML_;
    }

}