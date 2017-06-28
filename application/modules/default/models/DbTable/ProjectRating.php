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

/*
    protected $_defaultResult = array(
        'project_id'          => 0,
        'count_likes'         => 0,
        'count_dislikes'      => 0,
        'votes_total'         => 0,
        'laplace_score'       => 0.5,
        'wilson_score'        => 0,
        'percentage_likes'    => 50,
        'percentage_dislikes' => 50
    );
*/
   

    public function fetchRating($project_id)
    {
        $sql = "
                SELECT
                   p.* ,
                   (SELECT `profile_image_url` FROM member m WHERE m.member_id = p.member_id)  AS profile_image_url,
                   (SELECT `username` FROM member m WHERE m.member_id = p.member_id)  AS username,
                   (SELECT `comment_text` FROM comments c WHERE c.comment_id = p.comment_id)  AS comment_text
                FROM
                    project_rating p            
                WHERE
                    project_id = :project_id                    
                    ORDER BY created_at DESC               
                ;                  
               ";
        $result = $this->_db->query($sql, array('project_id' => $project_id))->fetchAll();

        return $result;
    }

    public function getProjectRateForUser($project_id, $member_id){
        $sql = "
                SELECT
                   p.* ,
                   (select `comment_text` from comments c where c.comment_id = p.comment_id)  as comment_text
                FROM
                    project_rating p            
                WHERE
                    project_id = :project_id                                
                    and member_id = :member_id
                    and rating_active = 1
                ;                  
               ";
        $result = $this->_db->query($sql, array('project_id' => $project_id,'member_id' => $member_id))->fetchAll();
        if(count($result)>0){
            return $result[0];
        }else
        {
            return null;
        }        
    }

    public function fetchRatingCntActive($project_id)
    {
        $sql = "
                SELECT
                   count(*)
                FROM
                    project_rating p            
                WHERE
                    project_id = :project_id                    
                    AND rating_active = 1
                ;                  
               ";
        $result = $this->_db->query($sql, array('project_id' => $project_id))->fetch();

        return $result;
    }

    /**
     * @param          $projectId
     * @param          $member_id
     * @param          $userRating
     * @param int|null $comment_id
     */



    public function rateForProject($projectId, $member_id, $userRating, $comment_id = null)
    {
        $alreadyExists = $this->fetchRow(array(
            'project_id = ?'    => $projectId,
            'member_id = ?'     => $member_id,
            'rating_active = ?' => 1
        ));
        $flagFromDislikeToLike = false;
        $flagFromLikeToDislike = false;
        if (false == is_null($alreadyExists)) {
            // if exist then if not same then deactive it and add new line otherwise return            
            if ($alreadyExists->user_like == 1) {
                if ($userRating == 1) {
                    // update comment_id
                    //$this->update(array('comment_id' =>$comment_id),'rating_id='.$alreadyExists->rating_id);    
                    //return;
                    $this->update(array('rating_active' => 0), 'rating_id=' . $alreadyExists->rating_id);
                } else {
                    // else userRating ==2 dislike then deactive current rating add new line
                    $this->update(array('rating_active' => 0), 'rating_id=' . $alreadyExists->rating_id);
                    $flagFromLikeToDislike = true;
                }
            } else if ($alreadyExists->user_dislike == 1) {
                if ($userRating == 2) {
                    // update comment_id
                    //$this->update(array('comment_id' =>$comment_id),'rating_id='.$alreadyExists->rating_id);                   
                    //return;     
                    $this->update(array('rating_active' => 0), 'rating_id=' . $alreadyExists->rating_id);
                } else {
                    $this->update(array('rating_active' => 0), 'rating_id=' . $alreadyExists->rating_id);
                    $flagFromDislikeToLike = true;
                }
            }
        }
        if (2 < $userRating) {
            return;
        }
        $userLikeIt = $userRating == 1 ? 1 : 0;
        $userDislikeIt = $userRating == 2 ? 1 : 0;
        $this->save(array(
            'project_id'    => $projectId,
            'member_id'     => $member_id,
            'user_like'     => $userLikeIt,
            'user_dislike'  => $userDislikeIt,
            'rating_active' => 1,
            'comment_id'    => $comment_id
        ));

        $projectTable = new Default_Model_Project();
        $project = $projectTable->fetchProductInfo($projectId);
        if ($project) {

            if (is_null($alreadyExists) and !$flagFromDislikeToLike and !$flagFromLikeToDislike) { // first time vote
                $numLikes = (int)$project->count_likes + $userLikeIt;
                $numDisLikes = (int)$project->count_dislikes + $userDislikeIt;

                $updatearray = array('count_likes' => $numLikes, 'count_dislikes' => $numDisLikes);
                $projectTable->update($updatearray, 'project_id = ' . $projectId);
            } else if ($flagFromDislikeToLike == true) {
                $numLikes = (int)$project->count_likes + 1;
                $numDisLikes = (int)$project->count_dislikes - 1;

                $updatearray = array('count_likes' => $numLikes, 'count_dislikes' => $numDisLikes);
                $projectTable->update($updatearray, 'project_id = ' . $projectId);
            } else if ($flagFromLikeToDislike == true) {
                $numLikes = (int)$project->count_likes - 1;
                $numDisLikes = (int)$project->count_dislikes + 1;

                $updatearray = array('count_likes' => $numLikes, 'count_dislikes' => $numDisLikes);
                $projectTable->update($updatearray, 'project_id = ' . $projectId);
            } else {
                // like again or dislike again count not changed...                
            }

            //update activity log
            if ($userRating == 1) {
                Default_Model_ActivityLog::logActivity($projectId, $projectId, $member_id,
                    Default_Model_ActivityLog::PROJECT_RATED_HIGHER, $project->toArray());
            }
            if ($userRating == 2) {
                Default_Model_ActivityLog::logActivity($projectId, $projectId, $member_id,
                    Default_Model_ActivityLog::PROJECT_RATED_LOWER, $project->toArray());
            }
        }
    }

    /**
     * @param int $memberId
     *
     * @return array returns array of affected rows. can be empty.
     */
    public function setDeletedByMemberId($memberId)
    {
        $sql = "
            UPDATE {$this->_name} 
            SET rating_active = 0
            WHERE member_id = :member_id AND rating_active = 1
        ";

        $sqlAffectedRows =
            "SELECT rating_id, project_id, user_like, user_dislike FROM {$this->_name} WHERE member_id = :member_id AND rating_active = 1";
        $affectedRows = $this->_db->fetchAll($sqlAffectedRows, array('member_id' => $memberId));

        $result = $this->_db->query($sql, array('member_id' => $memberId))->execute();
        if ($result) {
            return $affectedRows;
        }

        return array();
    }

}