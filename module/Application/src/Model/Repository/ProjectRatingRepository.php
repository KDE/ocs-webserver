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

namespace Application\Model\Repository;

use Application\Model\Entity\ProjectRating;
use Application\Model\Interfaces\ProjectRatingInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ProjectRatingRepository extends BaseRepository implements ProjectRatingInterface
{
    public static $options = array(
        1  => 'ugh',
        2  => 'really bad',
        3  => 'bad',
        4  => 'soso',
        5  => 'average',
        6  => 'okay',
        7  => 'good',
        8  => 'great',
        9  => 'excellent',
        10 => 'the best',
    );

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "project_rating";
        $this->_key = "rating_id";
        $this->_prototype = ProjectRating::class;
    }

    /**
     * @param int $project_id
     *
     * @return array
     */
    public function fetchRating($project_id)
    {
        $sql = "
                SELECT
                   `p`.* ,
                   (SELECT `profile_image_url` FROM `member` `m` WHERE `m`.`member_id` = `p`.`member_id`)  AS `profile_image_url`,
                   (SELECT `username` FROM `member` `m` WHERE `m`.`member_id` = `p`.`member_id`)  AS `username`,
                   (SELECT `comment_text` FROM `comments` `c` WHERE `c`.`comment_id` = `p`.`comment_id`)  AS `comment_text`
                FROM
                    `project_rating` `p`
                WHERE
                    `project_id` = :project_id AND `rating_active` = 1
                    ORDER BY `created_at` DESC
                ;
               ";

        return $this->fetchAll($sql, array('project_id' => $project_id));
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
                   `p`.* ,
                   (SELECT `comment_text` FROM `comments` `c` WHERE `c`.`comment_id` = `p`.`comment_id`)  AS `comment_text`
                FROM
                    `project_rating` `p`
                WHERE
                    `project_id` = :project_id
                    AND `member_id` = :member_id
                    AND `rating_active` = 1
                ;
               ";
        $result = $this->fetchAll($sql, array('project_id' => $project_id, 'member_id' => $member_id));
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
        return $this->fetchAllRowsCount(['project_id' => $project_id, 'rating_active' => 1]);
    }

    public function getScore($project_id)
    {
        $sql = "select (sum(t.totalscore)+5*5)/(sum(t.count)+5)*100 as score
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

        $result = $this->fetchAll($sql, array('project_id' => $project_id));
        if ($result[0]['score']) {
            return $result[0]['score'];
        } else {
            return 500;
        }
    }

    public function getScoreOld($project_id)
    {
        $sql = "
            SELECT `laplace_score`(sum(`pr`.`user_like`), sum(`pr`.`user_dislike`)) AS `score`
                FROM `project_rating` AS `pr`
              WHERE `pr`.`project_id` = :project_id AND (`pr`.`rating_active` = 1 OR (`rating_active`=0 AND `user_like`>1))
              ";

        $result = $this->fetchAll($sql, array('project_id' => $project_id));
        if ($result[0]['score']) {
            return $result[0]['score'];
        } else {
            return 50;
        }
    }

    /**
     * @param int $memberId
     *
     * @return void returns array of affected rows. can be empty.
     */

    public function setDeletedByMemberId($memberId)
    {
        $this->update(['rating_active' => 0], ['member_id' => $memberId, 'rating_active' => 1]);
    }

    /**
     * @param int $project_id
     * @param int $comment_id
     *
     * @return void returns array of affected rows. can be empty.
     */

    public function setDeletedByProjectComment($project_id, $comment_id)
    {
        $this->update(['rating_active' => 0], ['project_id' => $project_id, 'comment_id' => $comment_id, 'rating_active'=>1]);
    }

    public function getRatedForMember($member_id)
    {
        $cache_name = __FUNCTION__ . '_' . $member_id;
        if ($result = $this->readCache($cache_name)) {
            return $result;
        }

        $sql = "
                     SELECT
                       `r`.`user_like`
                       ,`r`.`user_dislike`
                       ,`r`.`rating_active`
                       ,`r`.`created_at` `rating_created_at`
                       ,(SELECT `comment_text` FROM `comments` `c` WHERE `c`.`comment_id` = `r`.`comment_id`)  AS `comment_text`
                       ,`r`.`project_id`
                       ,`r`.`score`
                        ,`p`.`member_id` AS `project_member_id`
                        ,`p`.`username` AS `project_username`
                        ,`p`.`project_category_id`
                        ,`p`.`status`
                        ,`p`.`title`
                        ,`p`.`description`
                        ,`p`.`image_small`
                        ,`p`.`project_created_at`
                        ,`p`.`project_changed_at`
                        ,`p`.`laplace_score`
                        ,`p`.`cat_title`
                        ,`p`.`count_likes`
                        ,`p`.`count_dislikes`
                    FROM
                        `project_rating` `r`
                    INNER JOIN `stat_projects` `p` ON `r`.`project_id` = `p`.`project_id` AND `p`.`status` = 100
                    WHERE
                        `r`.`member_id` = :member_id
                        AND `r`.`rating_active` = 1
                    ORDER BY `r`.`created_at` DESC
        ";

        $result = $this->fetchAll($sql, array('member_id' => $member_id));
        $this->writeCache($cache_name, $result, 600);

        return $result;
    }

    
}