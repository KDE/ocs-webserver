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
class Statistics_Ranking_WeightedAverageRanking implements Statistics_Ranking_RankingInterface
{

    protected $weightForKeys;


    function __construct()
    {
        $this->weightForKeys = array(
            'count_views' => 0.1,
            'count_plings' => 5,
            'count_updates' => 1,
            'count_comments' => 1,
            'count_followers' => 0,
            'count_supporters' => 0,
            'count_money' => 0
        );
    }

    /**
     * @param $data
     * @return float
     */
    public function calculateRankingValue($data)
    {
        $weightedSum = 0.0;
        $dividerSum = 0.0;

        foreach ($this->weightForKeys as $key => $weight) {
            $weightedSum += $weight * (float)$data[$key];
            $dividerSum += $weight;
        }
        return $weightedSum / $dividerSum;
    }

}
