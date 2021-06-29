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
 * */

namespace Application\Model\Service;

use Application\Model\Repository\CommentsRepository;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Repository\TagRatingRepository;
use Application\Model\Service\Interfaces\ProjectTagRatingsServiceInterface;
use JobQueue\Jobs\EmailJob;
use JobQueue\Jobs\JobBuilder;
use Laminas\Db\Adapter\AdapterInterface;
use stdClass;

class ProjectTagRatingsService extends BaseService implements ProjectTagRatingsServiceInterface
{

    /** @var AdapterInterface */
    protected $db;
    /** @var TagRatingRepository */
    private $projectTagRatingsRepository;
    /** @var EmailBuilder */
    private $emailBuilder;

    public function __construct(
        AdapterInterface $db,
        EmailBuilder $emailBuilder
    ) {
        $this->db = $db;
        $this->projectTagRatingsRepository = new TagRatingRepository($db);
        $this->emailBuilder = $emailBuilder;
    }

    /**
     * @param int $project_id
     *
     * @return array
     */
    public function getProjectTagRatings($project_id)
    {
        $sql = "
                SELECT 
                `r`.`tag_id`,
                `r`.`vote`,
                `r`.`member_id`,
                `r`.`tag_rating_id`,
                `r`.`comment_id`,
                `comments`.`comment_text`   
                FROM `stat_projects` `p`
                INNER JOIN `project_category` `g` ON `p`.`project_category_id` = `g`.`project_category_id`
                INNER JOIN `tag_group_item` `i` ON `i`.`tag_group_id` = `g`.`tag_rating`
                INNER JOIN `tag_rating` `r` ON `r`.`tag_id` = `i`.`tag_id` AND `r`.`project_id` = `p`.`project_id` AND `r`.`is_deleted`=0
                INNER JOIN `tag` `t` ON `t`.`tag_id` = `r`.`tag_id`
                INNER JOIN `comments` ON `comments`.`comment_id` = `r`.`comment_id`	
                WHERE `p`.`project_id` = :project_id
                ORDER BY `r`.`tag_rating_id` DESC
               ";
        /** @var array $result */
        $result = $this->projectTagRatingsRepository->fetchAll($sql, array('project_id' => $project_id));

        return $result;
    }

    public function getCategoryTagRatings($category_id)
    {
        $sql = "SELECT 
                `t`.`tag_id` AS `id`,            
                `t`.`tag_fullname` AS `name`,
                `tg`.`group_display_name`
                FROM `project_category` `g`
                INNER JOIN `tag_group_item` `i` ON `i`.`tag_group_id` = `g`.`tag_rating`
                INNER JOIN `tag` `t` ON `t`.`tag_id` = `i`.`tag_id`
                INNER JOIN `tag_group` `tg` ON `g`.`tag_rating` = `tg`.`group_id`
                WHERE `g`.`project_category_id` =:category_id
                ORDER BY `t`.`tag_fullname`
            ";

        return $this->projectTagRatingsRepository->fetchAll($sql, array('category_id' => $category_id));
    }

    /**
     * @param int $member_id
     * @param int $project_id
     * @param int $tag_id
     *
     * @return array [tag_rating_id,vote]
     */
    public function checkIfVote($member_id, $project_id, $tag_id)
    {
        $sql = "
            SELECT `tag_rating_id`,`vote` 
            FROM `tag_rating` 
            WHERE `member_id`=:member_id 
              AND `project_id`=:project_id 
              AND `tag_id`=:tag_id  
              AND `is_deleted`=0
        ";

        return $this->projectTagRatingsRepository->fetchRow(
            $sql, array(
                    "member_id"  => $member_id,
                    "project_id" => $project_id,
                    "tag_id"     => $tag_id,
                )
        );
    }

    public function doVote($member_id, $project_id, $tag_id, $vote, $msg)
    {
        $data = array();
        $data['comment_target_id'] = $project_id;
        $data['comment_member_id'] = $member_id;
        $data['comment_parent_id'] = 0;
        $data['comment_text'] = $msg;
        $commentmodel = new CommentsRepository($this->db);
        $comment_id = $commentmodel->save($data);

        $this->projectTagRatingsRepository->insert(
            array(
                'member_id'  => $member_id,
                'project_id' => $project_id,
                'tag_id'     => $tag_id,
                'vote'       => $vote,
                'comment_id' => $comment_id,
            )
        );

        $this->sendNotificationToOwner($project_id, $msg, 40);
    }

    /**
     * @param int      $project_id
     * @param string   $comment
     * @param int|null $comment_type
     */
    private function sendNotificationToOwner($project_id, $comment, $comment_type = null)
    {
        /** @var \Application\Model\Entity\CurrentUser $auth */
        $auth = $GLOBALS['ocs_user'];
        if (false === $auth->hasIdentity()) {
            return;
        }
        $tableProject = new ProjectRepository($this->db);
        $product = $tableProject->fetchProductInfo($project_id);
        //Don't send email notification for comments from product owner
        if ($auth->member_id == $product['member_id']) {
            return;
        }

        $productData = new stdClass();
        $productData->mail = $product['mail'];
        $productData->username = $product['username'];
        $productData->username_sender = $auth->username;
        $productData->title = $product['title'];
        $productData->project_id = $product['project_id'];

        $template = 'tpl_user_comment_note';
        if (!empty($comment_type) && $comment_type == '30') {
            $template = 'tpl_user_comment_note_' . $comment_type;
        }
        //@formatter:off
        $emailBuilder = $this->emailBuilder;
        $mail = $emailBuilder->withTemplate($template)->setReceiverMail($productData->mail)
                             ->setReceiverAlias($productData->username)
                             ->setTemplateVar('username', $productData->username)
                             ->setTemplateVar('username_sender', $productData->username_sender)
                             ->setTemplateVar('product_title', $productData->title)
                             ->setTemplateVar('product_id', $productData->project_id)
                             ->setTemplateVar('comment_text', $comment)
                             ->build();

        $mail_config = $GLOBALS['ocs_config']['settings']['mail'];
        JobBuilder::getJobBuilder()
                                 ->withJobClass(EmailJob::class)
                                 ->withParam('mail', serialize($mail))
                                 ->withParam('withFileTransport', $mail_config['transport']['withFileTransport'])
                                 ->withParam('withSmtpTransport', $mail_config['transport']['withSmtpTransport'])
                                 ->withParam('config', serialize($mail_config))
                                 ->build();
        //@formatter:on
    }

    public function removeVote($tag_rating_id)
    {
        $sql = "update tag_rating set is_deleted=1, deleted_at=now() where tag_rating_id=" . $tag_rating_id;
        $this->projectTagRatingsRepository->query($sql);

        $sql = "SELECT `comment_id` FROM `tag_rating` WHERE `tag_rating_id`=:tag_rating_id ";
        $result = $this->projectTagRatingsRepository->fetchRow($sql, array("tag_rating_id" => $tag_rating_id));
        if ($result && $result['comment_id']) {
            $modelComments = new ProjectCommentsService($this->db);
            $modelComments->deactivateComment($result['comment_id']);
        }
    }

}
