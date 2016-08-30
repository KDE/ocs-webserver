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
class Default_Model_DbTable_ProjectRating extends Local_Model_Table
{

    protected $_name = "project_rating";

    protected $_keyColumnsForRow = array('rating_id');

    protected $_key = 'rating_id';

    protected $_defaultResult = array(
        'project_id' => 0,
        'count_likes' => 0,
        'count_dislikes' => 0,
        'votes_total' => 0,
        'laplace_score' => 0.5,
        'wilson_score' => 0,
        'percentage_likes' => 50,
        'percentage_dislikes' => 50
    );


    public function fetchRating($project_id)
    {
        $sql = "
                SELECT
                    project_id,
                    SUM(user_like) AS count_likes,
                    SUM(user_dislike) AS count_dislikes,
                    (SUM(user_like) + SUM(user_dislike)) AS votes_total,
                    round((SUM(user_like) + 6) / (SUM(user_like) + SUM(user_dislike) + 12),2) AS laplace_score,
                    round(((SUM(user_like) + 1.9208) / (SUM(user_like) + SUM(user_dislike)) -
                   1.96 * SQRT((SUM(user_like) * SUM(user_dislike)) / (SUM(user_like) + SUM(user_dislike)) + 0.9604) /
                          (SUM(user_like) + SUM(user_dislike))) / (1 + 3.8416 / (SUM(user_like) + SUM(user_dislike))),2) AS wilson_score,
                    SUM(user_like) / (SUM(user_like) + SUM(user_dislike)) * 100 AS percentage_likes,
                    SUM(user_dislike) / (SUM(user_like) + SUM(user_dislike)) * 100 AS percentage_dislikes
                FROM
                    project_rating
                WHERE
                    project_id = :project_id;
               ";
        $result = $this->_db->query($sql, array('project_id' => $project_id))->fetch();
        if (false === empty($result['project_id'])) {
            return $result;
        } else {
            $result = $this->_defaultResult;
            $result['project_id'] = $project_id;
            return $result;
        }
    }

    public function rateForProject($projectId, $member_id, $userRating)
    {
        $alreadyExists = $this->fetchRow(array('project_id = ?' => $projectId, 'member_id = ?' => $member_id));
        if (false == is_null($alreadyExists)) {
            return;
        }
        if (2 < $userRating) {
            return;
        }
        $userLikeIt = $userRating == 1 ? 1 : 0;
        $userDislikeIt = $userRating == 2 ? 1 : 0;
        $this->save(array('project_id' => $projectId, 'member_id' => $member_id, 'user_like' => $userLikeIt, 'user_dislike' => $userDislikeIt));
        
        $projectTable = new Default_Model_Project();
        $project = $projectTable->fetchProductInfo($projectId);
        if($project) {
        	$numLikes = $project->count_likes + $userLikeIt;
        	$numDisLikes = $project->count_dislikes + $userDislikeIt;
        	$updatearray = array('count_likes' => $numLikes, 'count_dislikes' => $numDisLikes);
        	$projectTable->update($updatearray, 'project_id = '.$projectId);

            //update activity log
            if ($userRating == 1) {
                Default_Model_ActivityLog::logActivity($projectId, $projectId, $member_id,Default_Model_ActivityLog::PROJECT_RATED_HIGHER, $project->toArray());
            }
            if ($userRating == 2) {
                Default_Model_ActivityLog::logActivity($projectId, $projectId, $member_id,Default_Model_ActivityLog::PROJECT_RATED_LOWER, $project->toArray());
            }
        } 
        
    }

}