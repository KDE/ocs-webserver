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

namespace Application\Model\Interfaces;

use Laminas\Paginator\Paginator;

interface CommentsInterface extends BaseInterface
{
    public function getCommentWithMember($comment_id);

    /**
     * @param int $type
     * @param     $source_id
     * @param     $source_pk
     *
     * @return mixed
     */
    public function getCommentFromSource($type, $source_id, $source_pk);

    /**
     * @param $project_id
     *
     * @return array
     */
    public function getCommentTreeForProjectList($project_id, $type = null);

    /**
     * @param     $project_id
     *
     * @param int $type
     *
     * @return Paginator
     */
    public function getCommentTreeForProject($project_id, $type = 0);

    /**
     * @param int $parent_id
     * @param int $level
     * @param null $result
     *
     * @return array|null
     */
    function sort_child_nodes($parent_id, $level, &$result = null);

    /**
     * @param int $project_id
     *
     * @return Paginator
     */
    public function getAllCommentsForProject($project_id);

    /**
     * @param int $_projectId
     *
     * @return array
     */
    public function getRootCommentsForProject($_projectId);

    /**
     * @param int $parent_id
     *
     * @return array
     */
    public function getChildCommentsForId($parent_id);

    /**
     * @param array $data
     *
     * @throws Exception
     */
    public function save($data);

    public function deactivateComment($comment_id);

    public function setAllCommentsForUserDeleted($member_id);

    public function setAllCommentsForUserActivated($member_id);

    /**
     * @param int $member_id
     * @param int $project_id
     *
     */
    public function setAllCommentsForProjectDeleted($member_id, $project_id);

    /**
     * @param int $member_id
     * @param int $comment_id
     *
     */
    public function setDeleted($member_id, $comment_id);

    public function setActive($member_id, $comment_id);

    /**
     * @param int $member_id
     * @param int $project_id
     */
    public function setAllCommentsForProjectActivated($member_id, $project_id);

    public function getCommentsHierarchic($project_id);

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
    );

    public function fetchCommentsWithTypeCount($comment_type);

    public function fetchCommentsWithTypeProjectCount($comment_type, $project_id);
    public function getChildCommentsHierarchic($parentComment);
}