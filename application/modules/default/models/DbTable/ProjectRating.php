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


    public static $options = array(1 => 'ugh', 2=>'really bad',3=>'bad',4=>'soso',5=>'average', 6=>'okay',7=>'good', 8=>'great', 9=>'excellent',10=>'the best');

    /**
     * @param int $project_id
     *
     * @return array
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
                    project_id = :project_id and rating_active = 1
                    ORDER BY created_at DESC
                ;
               ";
        $result = $this->_db->query($sql, array('project_id' => $project_id))->fetchAll();

        return $result;
    }

    /**
     * @param int $project_id
     * @param int $member_id
     *
     * @return null
     */
    public function getProjectRateForUser($project_id, $member_id)
    {
        $sql = "
                SELECT
                   p.* ,
                   (SELECT `comment_text` FROM comments c WHERE c.comment_id = p.comment_id)  AS comment_text
                FROM
                    project_rating p
                WHERE
                    project_id = :project_id
                    AND member_id = :member_id
                    AND rating_active = 1
                ;
               ";
        $result = $this->_db->query($sql, array('project_id' => $project_id, 'member_id' => $member_id))->fetchAll();
        if (count($result) > 0) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * @param int $project_id
     *
     * @return mixed
     */
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

    public function getScore($project_id)
    {
          $sql = "select (sum(t.totalscore)+4*5)/(sum(t.count)+4)*100 as score
                    from
                    (
                        select project_id
                        ,user_like as likes
                        ,user_dislike as dislikes
                        ,1 as count
                        ,score as totalscore
                        from project_rating pr where pr.project_id=:project_id and pr.rating_active = 1
                    ) t
                ";

        $result = $this->_db->query($sql, array('project_id' => $project_id))->fetchAll();
        if($result[0]['score'])
        {
            return $result[0]['score'];
         }else
         {
            return 500;
         }
    }

    // public function getScore($project_id)
    // {
    //       $sql = "select (sum(t.totalscore)+4*5)/(sum(t.count)+4)*100 as score
    //                 from
    //                 (
    //                     select project_id
    //                     ,user_like as likes
    //                     ,user_dislike as dislikes
    //                     ,1 as count
    //                     ,score as totalscore
    //                     from project_rating pr where pr.project_id=:project_id and pr.rating_active = 1
    //
    //                     union all
    //
    //                     select
    //                     project_id
    //                     ,user_like-6 as likes
    //                     ,user_dislike-6 as dislikes
    //                     ,user_like+user_dislike-12 as count
    //                     ,(user_like-6)*9+(user_dislike-6)*3 as totalscore
    //                     from project_rating pr
    //                     where pr.project_id=:project_id and pr.rating_active = 0 and user_dislike >=6 and user_like>=6
    //                 ) t
    //             ";
    //
    //     $result = $this->_db->query($sql, array('project_id' => $project_id))->fetchAll();
    //     if($result[0]['score'])
    //     {
    //         return $result[0]['score'];
    //      }else
    //      {
    //         return 500;
    //      }
    // }

    public function getScoreOld($project_id)
    {
        $sql = "
            SELECT laplace_score(sum(pr.user_like), sum(pr.user_dislike)) AS score
                FROM project_rating AS pr
              WHERE pr.project_id = :project_id and (pr.rating_active = 1 or (rating_active=0 and user_like>1))
              ";

        $result = $this->_db->query($sql, array('project_id' => $project_id))->fetchAll();
        if($result[0]['score'])
        {
            return $result[0]['score'];
         }else
         {
            return 50;
         }
    }

    /**
     * @param int      $projectId
     * @param int      $member_id
     * @param int      $userRating
     * @param int|null $msg comment
     */
    public function rateForProject($projectId, $member_id, $userRating, $msg )
    {
        $msg = trim($msg);
        if(strlen($msg)<1) return;
        $userLikeIt = $userRating == 1 ? 1 : 0;
        $userDislikeIt = $userRating == 2 ? 1 : 0;
        $sql = 'select rating_id,comment_id from project_rating where project_id='.$projectId.'  and rating_active=1 and user_like='.$userLikeIt.' and user_dislike='.$userDislikeIt.' and member_id='.$member_id;
        $result = $this->getAdapter()->fetchRow($sql);
        $is_upvote=$userRating == 1 ? true : false;
        $is_exist = (($result!=null) && ($result['rating_id']!=null))?true:false;
        $modelComments = new Default_Model_ProjectComments();

        // Zend_Registry::get('logger')->info($msg);
        if($is_exist){
            // this do cancel old rating .  remove rating & deactive
            $rating_id = $result['rating_id'];
            $comment_id = $result['comment_id'];
            $this->update(array('rating_active' => 0), 'rating_id=' . $rating_id);
            $modelComments->deactiveComment($comment_id);
            /*if($is_upvote){
               $this->rateUpdateProject($projectId,1);
            }else{
                $this->rateUpdateProject($projectId,2);
            }*/
        }else{
            // this do first rating or change from - to + or + to -
            // first comment
            $data = array();
            $data['comment_target_id'] =$projectId;
            $data['comment_member_id'] =$member_id;
            $data['comment_parent_id'] = 0;
            $data['comment_text'] = $msg;
            $tableReplies = new Default_Model_ProjectComments();
            $result = $tableReplies->save($data);
            $comment_id =  $result->comment_id;

            // get old rating
            $sql = 'select rating_id,comment_id,user_like from project_rating where project_id='.$projectId.'  and rating_active=1 and member_id='.$member_id;
            $result = $this->getAdapter()->fetchRow($sql);
            if($result!=null && $result['rating_id']!=null){
                 $this->update(array('rating_active' => 0), 'rating_id=' . $result['rating_id']);
                $modelComments->deactiveComment($result['comment_id']);
            }

            if($userLikeIt==1)
            {
                $score = 8;
            }else
            {
                $score = 3;
            }
            $this->save(array(
                'project_id'    => $projectId,
                'member_id'     => $member_id,
                'user_like'     => $userLikeIt,
                'user_dislike'  => $userDislikeIt,
                'score'         => $score,
                'rating_active' => 1,
                'comment_id'    => $comment_id
            ));

            // deal with project table ratings
            /*if(($result!=null) && ($result['rating_id']!=null)){
                if($is_upvote){
                      $this->rateUpdateProject($projectId,5);
                }else{
                     $this->rateUpdateProject($projectId,6);
                }
            }else{
                // first time rating
                if($is_upvote){
                   $this->rateUpdateProject($projectId,3);
                }else{
                    $this->rateUpdateProject($projectId,4);
                }
            }*/

        }


    }

   

    /**
     * @param int      $projectId
     * @param int      $member_id
     * @param int      $userRating
     * @param int|null $msg comment
     */
    public function scoreForProject($projectId, $member_id, $score, $msg )
    {
        $msg = trim($msg);
        $score =(int)$score;
        if(strlen($msg)<1) return;
        if($score<6){
            $userLikeIt = 0;
            $userDislikeIt = 1;
        }else
        {
            $userLikeIt = 1;
            $userDislikeIt = 0;
        }

        $sql = 'select rating_id,comment_id from project_rating where project_id='.$projectId.'  and rating_active=1 and member_id='.$member_id;
        $result = $this->getAdapter()->fetchRow($sql);

        $is_upvote=$score<6 ? false : true;

        //$is_exist = (($result!=null) && ($result['rating_id']!=null))?true:false;
        $modelComments = new Default_Model_ProjectComments();

        if($score<=0){
            // this do cancel old rating .  remove rating & deactive
            $rating_id = $result['rating_id'];
            $comment_id = $result['comment_id'];
            $this->update(array('rating_active' => 0), 'rating_id=' . $rating_id);
            $modelComments->deactiveComment($comment_id);
            /*if($is_upvote){
               $this->rateUpdateProject($projectId,1);
            }else{
                $this->rateUpdateProject($projectId,2);
            }*/
        }else{
            // this do first rating or change from - to + or + to -
            // first comment
            $data = array();
            $data['comment_target_id'] =$projectId;
            $data['comment_member_id'] =$member_id;
            $data['comment_parent_id'] = 0;
            $data['comment_text'] = $msg;
            $tableReplies = new Default_Model_ProjectComments();
            $result = $tableReplies->save($data);
            $comment_id =  $result->comment_id;

            // get old rating
            $sql = 'select rating_id,comment_id from project_rating where project_id='.$projectId.'  and rating_active=1 and member_id='.$member_id;
            $result = $this->getAdapter()->fetchRow($sql);
            if($result!=null && $result['rating_id']!=null){
                 $this->update(array('rating_active' => 0), 'rating_id=' . $result['rating_id']);
                $modelComments->deactiveComment($result['comment_id']);
            }


            $this->save(array(
                'project_id'    => $projectId,
                'member_id'     => $member_id,
                'user_like'     => $userLikeIt,
                'user_dislike'  => $userDislikeIt,
                'score'         => $score,
                'rating_active' => 1,
                'comment_id'    => $comment_id
            ));

            // deal with project table ratings
            /*if(($result!=null) && ($result['rating_id']!=null)){
                if($is_upvote){
                      $this->rateUpdateProject($projectId,5);
                }else{
                     $this->rateUpdateProject($projectId,6);
                }
            }else{
                // first time rating
                if($is_upvote){
                   $this->rateUpdateProject($projectId,3);
                }else{
                    $this->rateUpdateProject($projectId,4);
                }
            }*/

        }


    }

      /**
     * @param int      $projectId
     * @param int      $member_id
     * @param int      $userRating
     * @param int|null $msg comment
     */
      /*
    public function rateForProject($projectId, $member_id, $userRating, $msg )
    {
        $msg = trim($msg);
        if(strlen($msg)<1) return;
        $userLikeIt = $userRating == 1 ? 1 : 0;
        $userDislikeIt = $userRating == 2 ? 1 : 0;
        $sql = 'select rating_id,comment_id from project_rating where project_id='.$projectId.'  and rating_active=1 and user_like='.$userLikeIt.' and user_dislike='.$userDislikeIt.' and member_id='.$member_id;
        $result = $this->getAdapter()->fetchRow($sql);
        $is_upvote=$userRating == 1 ? true : false;
        $is_exist = (($result!=null) && ($result['rating_id']!=null))?true:false;
        $modelComments = new Default_Model_ProjectComments();

        // Zend_Registry::get('logger')->info($msg);
        if($is_exist){
            // this do cancel old rating .  remove rating & deactive
            $rating_id = $result['rating_id'];
            $comment_id = $result['comment_id'];
            $this->update(array('rating_active' => 0), 'rating_id=' . $rating_id);
            $modelComments->deactiveComment($comment_id);
            if($is_upvote){
               $this->rateUpdateProject($projectId,1);
            }else{
                $this->rateUpdateProject($projectId,2);
            }
        }else{
            // this do first rating or change from - to + or + to -
            // first comment
            $data = array();
            $data['comment_target_id'] =$projectId;
            $data['comment_member_id'] =$member_id;
            $data['comment_parent_id'] = 0;
            $data['comment_text'] = $msg;
            $tableReplies = new Default_Model_ProjectComments();
            $result = $tableReplies->save($data);
            $comment_id =  $result->comment_id;

            // get old rating
            $sql = 'select rating_id,comment_id,user_like from project_rating where project_id='.$projectId.'  and rating_active=1 and member_id='.$member_id;
            $result = $this->getAdapter()->fetchRow($sql);
            if($result!=null && $result['rating_id']!=null){
                 $this->update(array('rating_active' => 0), 'rating_id=' . $result['rating_id']);
                $modelComments->deactiveComment($result['comment_id']);
            }

            $this->save(array(
                'project_id'    => $projectId,
                'member_id'     => $member_id,
                'user_like'     => $userLikeIt,
                'user_dislike'  => $userDislikeIt,
                'rating_active' => 1,
                'comment_id'    => $comment_id
            ));

            // deal with project table ratings
            if(($result!=null) && ($result['rating_id']!=null)){
                if($is_upvote){
                      $this->rateUpdateProject($projectId,5);
                }else{
                     $this->rateUpdateProject($projectId,6);
                }
            }else{
                // first time rating
                if($is_upvote){
                   $this->rateUpdateProject($projectId,3);
                }else{
                    $this->rateUpdateProject($projectId,4);
                }
            }

        }


    }*/

    /*private function rateUpdateProject($projectId,$action)
    {
        // $action ==1 => $project->count_likes - 1
        // $action ==2 => $project->count_dislikes - 1
        // $action ==3 => $project->count_likes + 1
        // $action ==4 => $project->count_dislikes + 1
        // $action ==5 => $project->count_likes+1 and $project->count_dislikes - 1
        // $action ==6 => $project->count_likes-1 and $project->count_dislikes +1
         $projectTable = new Default_Model_Project();
         $project = $projectTable->fetchProductInfo($projectId);
         if($action==1)
         {
            $numLikes = (int)$project->count_likes - 1;
            $updatearray = array('count_likes' => $numLikes);
         }else if($action==2)
         {
            $numLikes = (int)$project->count_dislikes - 1;
            $updatearray = array('count_dislikes' => $numLikes);
         }else if($action==3)
         {
            $numLikes = (int)$project->count_likes +1;
            $updatearray = array('count_likes' => $numLikes);
         }else if($action==4)
         {
            $numLikes = (int)$project->count_dislikes +1;
            $updatearray = array('count_dislikes' => $numLikes);
         }else if($action==5)
         {
            $numdisLikes = (int)$project->count_dislikes -1;
            $numLikes = (int)$project->count_likes +1;
            $updatearray = array('count_dislikes' => $numdisLikes,'count_likes' => $numLikes);
         }else if($action==6)
         {
            $numdisLikes = (int)$project->count_dislikes +1;
            $numLikes = (int)$project->count_likes -1;
            $updatearray = array('count_dislikes' => $numdisLikes,'count_likes' => $numLikes);
         }
         $projectTable->update($updatearray, 'project_id = ' . $projectId);
    }
*/

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

    public function getRatedForMember($member_id)
    {
        $sql = "
                     SELECT
                       r.user_like
                       ,r.user_dislike
                       ,r.rating_active
                       ,r.created_at rating_created_at
                       ,(select `comment_text` from comments c where c.comment_id = r.comment_id)  as comment_text
                       ,r.project_id
                       ,r.score
                        ,p.member_id as project_member_id
                        ,p.username as project_username
                        ,p.project_category_id
                        ,p.status
                        ,p.title
                        ,p.description
                        ,p.image_small
                        ,p.project_created_at
                        ,p.project_changed_at
                        ,p.laplace_score
                        ,p.cat_title
                        ,p.count_likes
                        ,p.count_dislikes
                    FROM
                        project_rating r
                    inner join stat_projects p on r.project_id = p.project_id and p.status = 100
                    WHERE
                        r.member_id = :member_id
                        and r.rating_active = 1
                    order by r.created_at desc
        ";
        $result = $this->_db->query($sql, array('member_id' => $member_id))->fetchAll();
        return $result;
    }

}
