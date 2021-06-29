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

namespace Application\Model\Service;

use Application\Model\Repository\CommentsRepository;
use Application\Model\Service\Interfaces\ProjectCommentsServiceInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ProjectCommentsService extends BaseService implements ProjectCommentsServiceInterface
{
    protected $db;
    private $commentsRepository;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
        $this->commentsRepository = new CommentsRepository($db);
    }

    public function deactivateComment($comment_id)
    {
        $this->commentsRepository->deactivateComment($comment_id);
    }

    public function setAllCommentsForUserDeleted($member_id)
    {

        $this->commentsRepository->setAllCommentsForUserDeleted($member_id);
    }

    public function setAllCommentsForUserActivated($member_id)
    {
        $this->commentsRepository->setAllCommentsForUserActivated($member_id);
    }

    /**
     * @param int $member_id
     * @param int $project_id
     *
     */
    public function setAllCommentsForProjectDeleted($member_id, $project_id)
    {
        $this->commentsRepository->setAllCommentsForProjectDeleted($member_id, $project_id);
    }

    /**
     * @param int $member_id
     * @param int $comment_id
     *
     */
    public function setDeleted($member_id, $comment_id)
    {
        $this->commentsRepository->setDeleted($member_id, $comment_id);
    }

    public function setActive($member_id, $comment_id)
    {
        $this->commentsRepository->setActive($member_id, $comment_id);
    }

    /**
     * @param int $member_id
     * @param int $project_id
     */
    public function setAllCommentsForProjectActivated($member_id, $project_id)
    {
        $this->commentsRepository->setAllCommentsForProjectActivated($member_id, $project_id);
    }

}