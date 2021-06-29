<?php /** @noinspection PhpUnused */

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

use Application\Model\Entity\Comments;
use Application\Model\Interfaces\CommentsInterface;
use Application\Model\Service\MemberDeactivationLogService;
use ArrayObject;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;

/**
 * Class CommentsRepository
 *
 * @package Application\Model\Repository
 */
class CommentsRepository extends BaseRepository implements CommentsInterface
{
    const COMMENT_ACTIVE = 1;
    const COMMENT_INACTIVE = 0;
    const COMMENT_TYPE_PLING = 10;
    const COMMENT_TYPE_DONATION = 20;
    const COMMENT_TYPE_PRODUCT = 0;
    const COMMENT_TYPE_MODERATOR = 30;
    const COMMENT_TYPE_LICENSING = 50;

    /** @var  array */
    protected $data;
    /** @var  array */
    protected $index;
    /**
     * @var array
     */
    protected $_defaultValues = array(
        'comment_type'       => 0,
        'comment_parent_id'  => 0,
        'comment_target_id'  => null,
        'comment_member_id'  => null,
        'comment_text'       => null,
        'comment_created_at' => null,
        'comment_active'     => null,
        'source_id'          => 0,
        'source_pk'          => null,
    );

    /**
     * CommentsRepository constructor.
     *
     * @param AdapterInterface $db
     */
    private $infoService;

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);

        $this->_name = "comments";
        $this->_key = "comment_id";
        $this->_prototype = Comments::class;
    }

    /**
     * @param $comment_id
     *
     * @return array|ArrayObject
     */
    public function getCommentWithMember($comment_id)
    {
        $sql = "    SELECT *
                    FROM `comments`
                    STRAIGHT_JOIN `member` ON `comments`.`comment_member_id` = `member`.`member_id`
                    WHERE `comment_id` = :comment_id
                    ORDER BY `comments`.`comment_created_at` DESC, `comment_parent_id`
        ";

        $rowSet = $this->fetchRow($sql, array('comment_id' => $comment_id));
        if (!$rowSet) {
            return array();
        }

        return $rowSet;
    }

    /**
     * @param int $type
     * @param     $source_id
     * @param     $source_pk
     *
     * @return array|ArrayObject|bool
     */
    public function getCommentFromSource($type, $source_id, $source_pk)
    {
        $sql = "
                    SELECT *
                    FROM `comments`
                    WHERE `comment_type` = :type AND `source_id` = :source_id AND `source_pk` = :source_pk 
        ";

        $rowset = $this->fetchRow($sql, array('type' => $type, 'source_id' => $source_id, 'source_pk' => $source_pk));
        if (!$rowset) {
            return false;
        }

        return $rowset;
    }

    /**
     * @param     $project_id
     * @param int $type
     *
     * @return Paginator
     */
    public function getCommentTreeForProject($project_id, $type = 0)
    {

        $list = $this->getCommentTreeForProjectList($project_id, $type);

        return new Paginator(new ArrayAdapter($list));
    }

    /**
     * @param      $project_id
     * @param null $type
     * left join instead of subquery
     *
     * @return array
     */
    public function getCommentTreeForProjectList($project_id, $type = null)
    {
        $sql = "
                SELECT `comments`.`comment_id`
                , `comment_target_id`
                , `comment_parent_id`
                , `comment_text`
                , `comment_created_at`
                , `comment_active`
                , `comment_type`
                , `member`.`member_id`
                , `username`
                , `profile_image_url` 
                ,(   SELECT 
                        FLOOR((count(DISTINCT `yearmonth`)+11)/12) `active_years`
                        FROM `section_support_paypements` `ss`
                        JOIN `support` `s` ON `s`.`id` = `ss`.`support_id`
                        WHERE `s`.`member_id` = `comments`.`comment_member_id`
                ) AS `issupporter`
                ,`pr`.`score` AS `rating`
                ,`r`.`score` AS `rating_member`	
                ,`member`.`roleid`
                ,`tr`.`vote` AS `tag_vote`
                ,`tag`.`tag_fullname` 
                FROM `comments` 
                INNER JOIN `member` ON `comments`.`comment_member_id` = `member`.`member_id` AND `member`.`is_active`=1 AND `member`.`is_deleted` = 0    
                LEFT JOIN `project_rating` `pr` ON `comments`.`comment_id` = `pr`.`comment_id`
                LEFT JOIN `project_rating` `r` ON `r`.`project_id` =:project_id  AND `r`.`rating_active` = 1 AND `r`.`member_id` = `comments`.`comment_member_id`
                LEFT JOIN `tag_rating` `tr` ON `tr`.`comment_id` = `comments`.`comment_id` AND `tr`.`is_deleted` = 0
                LEFT JOIN `tag` ON `tag`.`tag_id` = `tr`.`tag_id`
                WHERE `comment_active` =  :status_active
                AND `comment_type` =:type_id
                AND `comment_target_id` = :project_id
                AND `comment_parent_id` = 0 
                ORDER BY `comment_created_at` DESC
        ";

        if ($type == null) {
            $type = $this::COMMENT_TYPE_PRODUCT;
        }
        $rowset = $this->fetchAll(
            $sql, array(
                    'status_active' => 1,
                    'type_id'       => $type,
                    'project_id'    => $project_id,
                )
        );

        $sql = "
                    SELECT `comments`.`comment_id`
                    , `comment_target_id`
                    , `comment_parent_id`
                    , `comment_text`
                    , `comment_created_at`
                    , `comment_active`
                    , `comment_type`
                    , `member`.`member_id`
                    , `username`
                    , `profile_image_url` 
                    ,(  SELECT 
                        FLOOR((count(DISTINCT `yearmonth`)+11)/12) `active_years`
                        FROM `section_support_paypements` `ss`
                        JOIN `support` `s` ON `s`.`id` = `ss`.`support_id`
                        WHERE `s`.`member_id` = `comments`.`comment_member_id`
                    ) AS `issupporter`
                    ,NULL AS `rating`
                    ,`r`.`score` AS `rating_member`	
                    ,`member`.`roleid`
                    ,NULL AS `tag_vote`
                    ,NULL AS `tag_fullname` 
                    FROM `comments` 
                    INNER JOIN `member` ON `comments`.`comment_member_id` = `member`.`member_id` AND `member`.`is_active`=1 AND `member`.`is_deleted` = 0                    
                    LEFT JOIN `project_rating` `r` ON `r`.`project_id` =:project_id AND `r`.`rating_active` = 1 AND `r`.`member_id` = `comments`.`comment_member_id`                              
                    WHERE `comment_active` =  :status_active
                    AND `comment_type` =:type_id
                    AND `comment_target_id` = :project_id
                    AND `comment_parent_id` <> 0 
                    ORDER BY `comment_created_at` DESC
                ";
        $rowset2 = $this->fetchAll(
            $sql, array(
                    'status_active' => 1,
                    'type_id'       => $type,
                    'project_id'    => $project_id,
                )
        );
        $rowset = array_merge($rowset, $rowset2);

        $this->data = array();
        $this->index = array();

        // create array with comment_id as key 
        foreach ($rowset as $item) {
            $this->data[$item['comment_id']] = $item;
        }

        // create an array with all parent_id's and their immediate children 
        foreach ($rowset as $item) {
            $this->index[$item['comment_parent_id']][] = $item['comment_id'];
        }

        // create the final sorted array 
        $list = array();
        $this->sort_child_nodes(0, 1, $list);


        return $list;
    }

    /**
     * public function getCommentTreeForProjectList($project_id, $type = null)
     * {
     * $sql = "
     * SELECT comments.comment_id
     * , comment_target_id, comment_parent_id, comment_text, comment_created_at, comment_active, comment_type,
     * member.member_id, username, profile_image_url
     * ,(  select count(distinct c.name) sections from
     * section_support s, support t , section c
     * where s.support_id = t.id and s.section_id = c.section_id
     * and  t.member_id = comments.comment_member_id   and t.status_id>=2
     * and s.is_active = 1
     * ) as issupporter
     * ,(select score from project_rating where project_rating.comment_id = comments.comment_id ) as rating
     * ,(select score from project_rating r where r.project_id =:project_id  and rating_active = 1 and r.member_id =
     * comments.comment_member_id) as rating_member
     * ,member.roleid
     * ,tr.vote as tag_vote
     * ,tag.tag_fullname
     * FROM comments
     * inner join member ON comments.comment_member_id = member.member_id
     * left join tag_rating tr on tr.comment_id = comments.comment_id and tr.is_deleted = 0
     * left join tag on tag.tag_id = tr.tag_id
     * WHERE comment_active =  :status_active AND comment_type =:type_id AND comment_target_id = :project_id  AND
     * comment_parent_id = 0 ORDER BY comment_created_at DESC
     * ";
     *
     *
     * if ($type == null) $type = $this::COMMENT_TYPE_PRODUCT;
     * $rowset = $this->fetchAll($sql, array(
     * 'status_active' => 1,
     * 'type_id'       => $type,
     * 'project_id'    => $project_id,
     * ));
     *
     * $sql = "
     * SELECT comments.comment_id, comment_target_id, comment_parent_id, comment_text, comment_created_at,
     * comment_active
     * , comment_type, member.member_id, username, profile_image_url
     * ,( select count(distinct c.name) sections from
     * section_support s, support t , section c
     * where s.support_id = t.id and s.section_id = c.section_id
     * and  t.member_id = comments.comment_member_id   and t.status_id>=2
     * and s.is_active = 1
     * ) as issupporter
     * ,(select score from project_rating where project_rating.comment_id = comments.comment_id ) as rating
     * ,(select score from project_rating r where r.project_id =:project_id and rating_active = 1 and r.member_id =
     * comments.comment_member_id) as rating_member
     * ,member.roleid
     * ,tr.vote as tag_vote
     * ,tag.tag_fullname
     * FROM comments
     * inner join member ON comments.comment_member_id = member.member_id
     * left join tag_rating tr on tr.comment_id = comments.comment_id and tr.is_deleted = 0
     * left join tag on tag.tag_id = tr.tag_id
     * WHERE comment_active = :status_active AND comment_type = :type_id AND comment_target_id = :project_id AND
     * comment_parent_id <> 0 ORDER BY comment_created_at DESC
     * ";
     * $rowset2 = $this->fetchAll($sql, array(
     * 'status_active' => 1,
     * 'type_id'       => $type,
     * 'project_id'    => $project_id,
     * ));
     *
     *
     *
     *
     * $rowset = array_merge($rowset, $rowset2);
     *
     *
     * $this->data = array();
     * $this->index = array();
     *
     * /* create array with comment_id as key
     * foreach ($rowset as $item) {
     * $this->data[$item['comment_id']] = $item;
     * }
     *
     * /* create an array with all parent_id's and their immediate children
     * foreach ($rowset as $item) {
     * $this->index[$item['comment_parent_id']][] = $item['comment_id'];
     * }
     *
     * /* create the final sorted array
     * $list = array();
     * $this->sort_child_nodes(0, 1, $list);
     *
     * return $list;
     * }
     **/

    /**
     * @param int  $parent_id
     * @param int  $level
     * @param null $result
     *
     * @return void
     */
    function sort_child_nodes($parent_id, $level, &$result = null)
    {
        // array(array('comment' => $rootElement, 'level' => 1));
        $parent_id = $parent_id === null ? "NULL" : $parent_id;
        if (isset($this->index[$parent_id])) {
            foreach ($this->index[$parent_id] as $id) {
                $result[] = array('comment' => $this->data[$id], 'level' => $level);
                $this->sort_child_nodes($id, $level + 1, $result);
            }
        }
    }

    /**
     * @param int $project_id
     *
     * @return Paginator
     */
    public function getAllCommentsForProject($project_id)
    {
        $rootElements = $this->getRootCommentsForProject($project_id);
        $returnValue = array();
        foreach ($rootElements as $rootElement) {
            $resultElement = array(array('comment' => $rootElement, 'level' => 1));
            $childs = $this->getAllChildComments($resultElement);
            if (0 == count($childs)) {
                $returnValue = array_merge($returnValue, $resultElement);
            } else {
                $returnValue = array_merge($returnValue, $childs);
            }
        }

        return new Paginator(new ArrayAdapter($returnValue));
    }

    /**
     * @param int $_projectId
     *
     * @return array
     */
    public function getRootCommentsForProject($_projectId)
    {
        $sql = '
                SELECT *
                    FROM `comments`
                    STRAIGHT_JOIN `member` ON `comments`.`comment_member_id` = `member`.`member_id`
                    WHERE `comment_target_id` = :project_id
                      AND `comment_parent_id` = 0
                      AND `comment_type` = :type_id
                      AND `comment_active` = :status
                    ORDER BY `comments`.`comment_created_at` DESC, `comment_parent_id`
        ';

        $rowset = $this->fetchAll(
            $sql, array(
                    'project_id' => $_projectId,
                    'status'     => $this::COMMENT_ACTIVE,
                    'type_id'    => $this::COMMENT_TYPE_PRODUCT,
                )
        );
        if (!$rowset) {
            return array();
        }

        return $rowset;
    }

    /**
     * @param array $element
     *
     * @return array
     */
    private function getAllChildComments($element)
    {
        $returnValue = array();
        $level = $element[0]['level'] + 1;
        $childs = $this->getChildCommentsForId($element[0]['comment']['comment_id']);
        if (0 == count($childs)) {
            return null;
        }
        foreach ($childs as $child) {
            $resultElement = array(array('comment' => $child, 'level' => $level));
            $subChilds = $this->getAllChildComments($resultElement);
            if (0 == count($subChilds)) {
                $returnValue = array_merge($returnValue, $resultElement);
            } else {
                $returnValue = array_merge($returnValue, $subChilds);
            }
        }

        return array_merge($element, $returnValue);
    }

    /**
     * @param int $parent_id
     *
     * @return array
     */
    public function getChildCommentsForId($parent_id)
    {
        $sql = "SELECT *
                    FROM `comments`
                    STRAIGHT_JOIN `member` ON `comments`.`comment_member_id` = `member`.`member_id`
                    WHERE `comment_parent_id` = :parent_id
                    AND `comment_active` = :status
                    ORDER BY `comments`.`comment_created_at`, `comments`.`comment_id`
               ";
        $rowset = $this->fetchAll(
            $sql, array('parent_id' => $parent_id, 'status' => CommentsRepository::COMMENT_ACTIVE)
        );
        if (!$rowset) {
            return array();
        }

        return $rowset;
    }

    /**
     * @param array $data
     *
     * @return int
     */
    public function save($data)
    {
        return $this->insertOrUpdate($data);
    }

    /**
     * @param $comment_id
     */
    public function deactivateComment($comment_id)
    {
        $sql = '
                UPDATE `comments`
                SET `comment_active` = 0
                WHERE `comment_id` = :comment_id';
        $this->db->query($sql, array('comment_id' => $comment_id));
    }

    /**
     * @param $member_id
     */
    public function setAllCommentsForUserDeleted($member_id)
    {

        $sql = "SELECT `comment_id` FROM `comments` WHERE `comment_member_id` = :member_id AND `comment_active` = 1";
        $commentsForDelete = $this->fetchAll(
            $sql, array(
                    'member_id' => $member_id,
                )
        );
        foreach ($commentsForDelete as $item) {
            $this->setDeleted($member_id, $item['comment_id']);
        }

        /*
        $sql = '
                UPDATE comments
                SET comment_active = 0
                WHERE comment_member_id = :member_id';
        $this->_dataTable->getAdapter()->query($sql, array('member_id' => $member_id))->execute();
        */
    }

    /**
     * @param int $member_id
     * @param int $comment_id
     */
    public function setDeleted($member_id, $comment_id)
    {
        $sql = '
                UPDATE `comments`
                SET `comment_active` = 0
                WHERE `comment_id` = :comment_id';
        $this->db->query($sql, array('comment_id' => $comment_id));

        $memberLog = new MemberDeactivationLogService($this->db);
        $memberLog->logCommentAsDeleted($member_id, $comment_id);
    }

    /**
     * @param $member_id
     */
    public function setAllCommentsForUserActivated($member_id)
    {
        $sql = "SELECT `comment_id` FROM `comments` `c`
                JOIN `member_deactivation_log` `l` ON `l`.`object_type_id` = 4 AND `l`.`object_id` = `c`.`comment_id` AND `l`.`deactivation_id` = `c`.`comment_member_id` AND `l`.`is_deleted` = 0
                WHERE `c`.`comment_member_id` = :member_id AND `c`.`comment_active` = 0";
        $commentsForDelete = $this->fetchAll(
            $sql, array(
                    'member_id' => $member_id,
                )
        );
        foreach ($commentsForDelete as $item) {
            $this->setActive($member_id, $item['comment_id']);
        }
    }

    /**
     * @param $member_id
     * @param $comment_id
     */
    public function setActive($member_id, $comment_id)
    {
        $sql = '
                UPDATE `comments`
                SET `comment_active` = 1, `comment_deleted_at` = NULL
                WHERE `comment_id` = :comment_id';
        $this->db->query($sql, array('comment_id' => $comment_id));

        $memberLog = new MemberDeactivationLogService($this->db);
        $memberLog->removeLogCommentAsDeleted($member_id, $comment_id);
    }

    /**
     * @param int $member_id
     * @param int $project_id
     */
    public function setAllCommentsForProjectDeleted($member_id, $project_id)
    {
        $sql = "SELECT `comment_id` FROM `comments` WHERE `comment_target_id` = :project_id AND `comment_type` = 0 AND `comment_active` = 1";
        $commentsForDelete = $this->fetchAll(
            $sql, array(
                    'project_id' => $project_id,
                )
        );
        foreach ($commentsForDelete as $item) {
            $this->setDeleted($member_id, $item['comment_id']);
        }
    }

    /**
     * @param int $member_id
     * @param int $project_id
     */
    public function setAllCommentsForProjectActivated($member_id, $project_id)
    {
        $sql = "SELECT `comment_id` FROM `comments` `c`
                JOIN `member_deactivation_log` `l` ON `l`.`object_type_id` = 4 AND `l`.`object_id` = `c`.`comment_id` AND `l`.`is_deleted` = 0
                WHERE `c`.`comment_target_id` = :project_id  AND `l`.`deactivation_id` = :member_id AND `comment_active` = 0";
        $commentsForDelete = $this->fetchAll(
            $sql, array(
                    'project_id' => $project_id,
                    'member_id'  => $member_id,
                )
        );
        foreach ($commentsForDelete as $item) {
            $this->setActive($member_id, $item['comment_id']);
        }
    }

    /**
     * @param $project_id
     *
     * @return Paginator
     */
    public function getCommentsHierarchic($project_id)
    {
        $rootElements = $this->getRootCommentsForProject($project_id);
        $returnValue = array();
        foreach ($rootElements as $parentComment) {
            $childs = $this->getChildCommentsHierarchic($parentComment);
            if (0 == count($childs)) {
                $parentComment['childcount'] = 0;
            } else {
                $parentComment['childcount'] = count($childs);
                $parentComment['children'] = $childs;
            }
            $returnValue[] = $parentComment;
        }

        return new Paginator(new ArrayAdapter($returnValue));
    }

    /**
     * @param $parentComment
     *
     * @return array
     */
    public function getChildCommentsHierarchic($parentComment)
    {
        $childs = $this->getChildCommentsForId($parentComment['comment_id']);
        if (0 == count($childs)) {
            return array();
        }
        $returnValue = array();
        foreach ($childs as $child) {
            $subChilds = $this->getChildCommentsHierarchic($child);
            if (0 == count($subChilds)) {
                $child['childcount'] = 0;
            } else {
                $child['childcount'] = count($subChilds);
                $child['children'] = $subChilds;
            }
            $returnValue[] = $child;
        }

        return $returnValue;
    }

    /**
     * @param        $comment_type
     * @param string $sorting
     * @param int    $pageSize
     * @param int    $offset
     *
     * @return array
     */
    public function fetchCommentsWithType(
        $comment_type,
        $sorting = 'comment_created_at desc',
        $pageSize = 10,
        $offset = 0
    ) {
        $sql = "SELECT 
        `comment_id`
        ,`comment_text`
        , `member`.`member_id`
        ,`member`.`profile_image_url`
        ,`comment_created_at`
        ,`member`.`username`            
        ,`p`.`title`
        ,`p`.`project_id`
        ,`p`.`image_small`
        ,`p`.`cat_title`
        ,`p`.`username` AS `product_username`
        FROM `comments`           
        JOIN `stat_projects` `p` ON `comments`.`comment_target_id` = `p`.`project_id` 
        JOIN `member` ON `comments`.`comment_member_id` = `member`.`member_id`
        WHERE `comments`.`comment_active` = 1                
        AND `comments`.`comment_type`=:comment_type       
        
        ";
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $offset;

        return $this->fetchAll($sql, array('comment_type' => $comment_type));
    }

    /**
     * @param $comment_type
     *
     * @return mixed
     */
    public function fetchCommentsWithTypeCount($comment_type)
    {
        $sql = "SELECT 
        COUNT(1) AS `cnt`
        FROM `comments`           
        JOIN `project` ON `comments`.`comment_target_id` = `project`.`project_id`         
        WHERE `comments`.`comment_active` = 1
        AND `project`.`status` = 100
        AND `comments`.`comment_type`=:comment_type
        ";
        $result = $this->fetchRow($sql, array('comment_type' => $comment_type));

        return $result['cnt'];
    }

    /**
     * @param $comment_type
     * @param $project_id
     *
     * @return mixed
     */
    public function fetchCommentsWithTypeProjectCount($comment_type, $project_id)
    {
        $sql = "SELECT 
        COUNT(1) AS `cnt`
        FROM `comments`           
        JOIN `project` ON `comments`.`comment_target_id` = `project`.`project_id`         
        WHERE `comments`.`comment_active` = 1       
        AND `project`.`project_id` = :project_id
        AND `comments`.`comment_type`=:comment_type
        ";
        $result = $this->fetchRow($sql, array('comment_type' => $comment_type, 'project_id' => $project_id));

        return $result['cnt'];
    }

}
