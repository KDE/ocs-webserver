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
class Default_View_Helper_PrintRatingWidget extends Zend_View_Helper_Abstract
{

    public function printRatingWidget($project_id)
    {
        $modelRating = new Default_Model_DbTable_ProjectRating();
        $rating = $modelRating->fetchRating($project_id);
        $likesAndDislikes = ($rating['count_likes'] == 0 and $rating['count_dislikes'] == 0) ? '' : '(' . $rating['count_likes'] . '/' . $rating['count_dislikes'] . ')';
        $cssNoVotes = ($rating['votes_total'] == 0) ? ';opacity:0.5;' : '';

        // calculate colour
        $blue = $red = $green = $default=200;
        $score = $rating['laplace_score'] * 100;

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
            floor($rating['laplace_score'] * 100),
            $red, $green, $blue,
            floor((1 - $rating['laplace_score']) * 100),
            $cssNoVotes
            );
    }

    protected function getHTML($likes, $red, $green , $blue, $dislikes, $cssNoVotes)
    {
        return <<< _END_HTML_
                <div class="rating">
                	
                    <div class="rating-text">
                        <small class="center-block text-center">Score {$likes}%</small>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="background-color: #{$red}{$green}{$blue};width: {$likes}%;">
                            <span class="sr-only">{$likes} Likes</span>
                        </div>
                        <div class="progress-bar" style="background-color:#eeeeee;width: {$dislikes}%;{$cssNoVotes}">
                            <span class="sr-only">{$dislikes} Dislikes</span>
                        </div>
                    </div>
                </div>
_END_HTML_;
    }

}