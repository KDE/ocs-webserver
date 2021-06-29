<?php /** @noinspection PhpRedundantOptionalArgumentInspection */

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

namespace Application\Controller;

use Application\Model\Repository\CommentsRepository;
use Application\Model\Repository\ProjectRatingRepository;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Service\ActivityLogService;
use Application\Model\Service\EmailBuilder;
use Application\Model\Service\HtmlPurifyService;
use Application\Model\Service\ProjectCommentsService;
use Application\Model\Service\ProjectService;
use Application\Model\Service\Util;
use JobQueue\Jobs\EmailJob;
use JobQueue\Jobs\JobBuilder;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Request;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\NotEmpty;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Library\Filter\HtmlPurify;
use stdClass;

/**
 * Class ProductcommentController
 *
 * @package Application\Controller
 */
class ProductcommentController extends DomainSwitch
{
    protected $commentsService;
    protected $commentsTable;
    protected $projectTable;
    protected $projectService;
    protected $projectRatingTable;

    protected $configArray;
    /** @var EmailBuilder */
    private $member_email_service;

    public function __construct(
        AdapterInterface $db,
        array $config,
        Request $request,
        ProjectCommentsService $commentsService,
        CommentsRepository $commentsTable,
        ProjectRepository $projectTable,
        ProjectRatingRepository $projectRatingTable,
        ProjectService $projectService,
        EmailBuilder $mailer
    ) {
        parent::__construct($db, $config, $request);
        parent::init();
        $this->commentsService = $commentsService;
        $this->commentsTable = $commentsTable;
        $this->projectTable = $projectTable;
        $this->projectRatingTable = $projectRatingTable;
        $this->projectService = $projectService;
        $this->member_email_service = $mailer;
        $this->_authMember = $GLOBALS['ocs_user'];
        $this->configArray = $config;

    }

    /**
     * @return ViewModel
     * @acl(accessRole=Roles::ROLENAME_GUEST)
     */
    public function showcommentsUX1Action()
    {
        $this->view->setTerminal(true);

        $p = (int)$this->getParam('p');
        $t = (int)$this->getParam('t');

        if (empty($p)) {
            return $this->sendErrorResponse('not found', 404);
        }

        $productInfo = $this->loadProductInfo($p);

        $page = (int)$this->getParam('page');

        $this->view->setVariable('comments', $this->loadComments($page, $p, $t));
        $this->view->setVariable('product', $productInfo);
        $this->view->setVariable('member_id', (int)$this->_authMember->member_id);
        $this->view->setVariable('ratingOfUser', $this->projectRatingTable->getProjectRateForUser($p, $this->_authMember->member_id));

        return $this->view;
    }

    /**
     * @param array|string $message
     * @param int          $statuscode
     *
     * @return JsonModel
     */
    private function sendErrorResponse($message, $statuscode = 400)
    {
        $this->getResponse()->setStatusCode($statuscode);

        return new JsonModel(
            array(
                'status'  => 'error',
                'message' => $message,
            )
        );
    }

    private function loadProductInfo($param)
    {
        return Util::arrayToObject($this->projectTable->fetchProductInfo($param));
    }

    private function loadComments($page_offset, $project_id, $comment_type)
    {
        $modelComments = $this->commentsTable;
        $paginationComments = $modelComments->getCommentTreeForProject($project_id, $comment_type);
        $paginationComments->setItemCountPerPage(25);
        $paginationComments->setCurrentPageNumber($page_offset);

        return $paginationComments;
    }

    /**
     * @return JsonModel
     * @acl(accessRole=staff)
     */
    public function delcommentAction()
    {
        $this->view->setTerminal(true);
        $p = (int)$this->getParam('p', null);
        $c = (int)$this->getParam('c', null);

        // quick check
        if (empty($p) or empty($c)) {
            return $this->sendErrorResponse('missing parameters');
        }

        $comments = $this->commentsTable->getChildCommentsHierarchic(['comment_id' => $c]);

        foreach ($comments as $comment) {
            $this->commentsTable->deactivateComment($comment['comment_id']);
        }
        $this->commentsTable->deactivateComment($c);
        $this->projectRatingTable->setDeletedByProjectComment($p, $c);

        ActivityLogService::logActivity($c, $p, $this->_authMember->member_id, ActivityLogService::PROJECT_COMMENT_VOTE_DELETED);

        //$model = new JsonModel(['result'=>'ok','comments'=>$comments,'message'=>'succeed!']);
        return new JsonModel(['result' => 'ok', 'message' => 'succeed!', 'redirect' => '/p/' . $p]);
    }

    /**
     * @return JsonModel|ViewModel
     * @acl(accessRole=Roles::ROLENAME_GUEST)
     */
    public function showcommentsUX2Action()
    {
        $this->view->setTerminal(true);

        $p = (int)$this->getParam('p', null);
        $t = (int)$this->getParam('t', null);

        if (empty($p)) {
            return $this->sendErrorResponse('not found', 404);
        }

        $productInfo = $this->loadProductInfo($p);

        $page = (int)$this->getParam('page', null);

        $this->view->setVariable('comments', $this->loadComments($page, $p, $t));
        $this->view->setVariable('product', $productInfo);
        $this->view->setVariable('member_id', (int)$this->_authMember->member_id);

        return $this->view;
    }

    /**
     * @return JsonModel|ViewModel
     * @acl(accessRole=Roles::ROLENAME_GUEST)
     */
    public function showcommentsUX3Action()
    {
        $this->view->setTerminal(true);

        $p = (int)$this->getParam('p', null);
        $t = (int)$this->getParam('t', null);

        if (empty($p)) {
            return $this->sendErrorResponse('not found', 404);
        }

        $productInfo = $this->loadProductInfo($p);

        $page = (int)$this->getParam('page', null);

        $this->view->setVariable('comments', $this->loadComments($page, $p, $t));
        $this->view->setVariable('product', $productInfo);
        $this->view->setVariable('member_id', (int)$this->_authMember->member_id);

        return $this->view;
    }

    /**
     * @return JsonModel|ViewModel
     * @acl(accessRole=Roles::ROLENAME_GUEST)
     */
    public function indexAction()
    {
        $this->view->setTerminal(true);

        $project_id = (int)$this->getParam('p', null);

        if (empty($project_id)) {
            return $this->sendErrorResponse("not found", 404);
        }

        $list = $this->commentsTable->getCommentTreeForProjectList($project_id);

        return new JsonModel($list);
    }

    /**
     * @return \Laminas\Http\Response|JsonModel
     * @acl(accessRole=Roles::ROLENAME_COOKIEUSER)
     */
    public function addreplyAction()
    {
        $this->view->setTerminal(true);

        header('Access-Control-Allow-Origin: *');

        $this->getResponse()->setMetadata('Access-Control-Allow-Origin', '*')
             ->setMetadata('Access-Control-Allow-Credentials', 'true')
             ->setMetadata('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
             ->setMetadata('Access-Control-Allow-Headers', 'origin, content-type, accept');

        $inputFilter = $this->getInputFilterForAddReply();
        $inputFilter->setData(
            array(
                'p' => $this->getParam('p', null),
                'i' => $this->getParam('i', 0),
                't' => $this->getParam('t', 0),
                'r' => $this->getParam('r', null),
                'msg' => $this->getParam('msg', null)
            )
        );
        if (false == $inputFilter->isValid()) {
            return $this->sendErrorResponse($inputFilter->getMessages());
        }

        $project_id = $inputFilter->getValue('p');
        $parent_id = $inputFilter->getValue('i');
        $member_id = $this->_authMember->member_id;
        $comment_type = $inputFilter->getValue('t');
        $comment_text = $inputFilter->getValue('msg');
        $score = empty($inputFilter->getValue('r')) ? null : $inputFilter->getValue('r');

        $page = (int)$this->getParam('page', null);

        // first comment
        $comment_id = $this->projectService->saveComment($project_id, $member_id, $comment_text, $parent_id, $comment_type);
        if (empty($comment_id)) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - could not save comment for ' . "{$project_id}, {$member_id}, {$comment_text}, {$parent_id}, {$comment_type}");

            return $this->sendErrorResponse(array('internal error'), 500);
        }

        $activity_type = ActivityLogService::PROJECT_COMMENT_CREATED;
        if ($parent_id > 0) {
            $activity_type = ActivityLogService::PROJECT_COMMENT_REPLY;
        }

        // project owner not allow rate
        $isOwner = $this->currentUser()->isOwner($project_id);
        // $parent_id>0 sub comment ignore change rating.
        if ($parent_id == 0 && !$isOwner && isset($score)) {
            // add rating
            $resultRating = $this->projectService->scoreForProject(
                $project_id, $member_id, $score, $comment_text, $comment_id
            );
            //update activity type
            $activity_type = ActivityLogService::PROJECT_COMMENT_VOTE_ADDED;
            if ($score < 0) {
                $activity_type = ActivityLogService::PROJECT_COMMENT_VOTE_REMOVED;
            }
        }

        $productInfo = $this->loadProductInfo($project_id);
        $this->view->setVariable('product', $productInfo);
        $this->view->setVariable('member_id', (int)$this->_authMember->member_id);

        $data = $this->commentsTable->findById($comment_id)->getArrayCopy();
        $this->updateActivityLog($data, $productInfo->image_small, $activity_type);

        //Send a notification to the owner
        $this->sendNotificationToOwner($productInfo, $comment_text, $comment_type);

        //Send a notification to the parent comment writer
        $this->sendNotificationToParent(
            $productInfo, $comment_text, $parent_id, $comment_type
        );

        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            if ($comment_type == 30) {
                return $this->redirect()->toUrl(
                    "showcommentsUX2?p=" . $this->getParam('p', null) . "&m=" . $this->getParam('m', null) . "&t=30"
                );
                //$this->view->setVariable('comments', $this->loadComments($page, $project_id, $comment_type));
                //$requestResult = $this->view->render('product/partials/productCommentsUX2.phtml');
                //$renderer = new \Laminas\View\Renderer\PhpRenderer();
                //$this->view->setTemplate('partials/productCommentsUX2.phtml');
                //$this->view->setTerminal(true);
                //$requestResult = $renderer->render($this->view);
            } else {
                if ($comment_type == 50) {
                    return $this->redirect()->toUrl(
                        "showcommentsUX3?p=" . $this->getParam('p', null) . "&m=" . $this->getParam('m', null) . "&t=50"
                    );

                } else {
                    return $this->redirect()->toUrl(
                        "showcommentsUX1?p=" . $this->getParam('p', null) . "&m=" . $this->getParam('m', null)
                    );
                }
            }
        } else {
            return $this->redirect()->toUrl('/p/' . $project_id);
        }
    }

    private function getInputFilterForAddReply()
    {
        $inputFilter = new InputFilter();

        // Add input for "comment_target_id" field
        $inputFilter->add(
            [
                'name'       => 'p',
                'required'   => true,
                'filters'    => [
                    ['name' => 'Digits'],
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    ['name' => 'NotEmpty',
                        'options' => [
                            NotEmpty::INTEGER, NotEmpty::ZERO,
                        ]],
                ],
            ]
        );

        // Add input for "comment_parent_id" field
        $inputFilter->add(
            [
                'name'       => 'i',
                'required'   => false,
                'filters'    => [
                    ['name' => 'Digits'],
                ],
                'validators' => [
                    ['name' => 'Digits',],
                ],
            ]
        );

        // Add input for "score" field
        $inputFilter->add(
            [
                'name'       => 'r',
                'required'   => false,
                'filters'    => [
                    ['name' => 'ToInt'],
                ],
                'validators' => [
                    ['name' => \Laminas\I18n\Validator\IsInt::class],
                ],
            ]
        );

        // Add input for "comment_type" field
        $inputFilter->add(
            [
                'name'       => 't',
                'required'   => true,
                'filters'    => [
                    ['name' => 'Digits'],
                ],
                'validators' => [
                    ['name' => 'Digits',],
                ],
            ]
        );

        // Add input for "msg" field
        $inputFilter->add(
            [
                'name'       => 'msg',
                'required'   => true,
                'filters'    => [
                    ['name' => 'StringTrim'],
                    ['name' => HtmlPurify::class],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 3000,
                        ],
                    ],
                ],
            ]
        );

        return $inputFilter;
    }

    /**
     * @param array  $data
     * @param string $image_small
     * @param int    $type
     */
    private function updateActivityLog($data, $image_small, $type = null)
    {
        if (false == isset($type)) {
            throw new \RuntimeException("unknown or missing activity type");
        }

        ActivityLogService::logActivity(
            $data['comment_id'], $data['comment_target_id'], $data['comment_member_id'], $type, array(
                                   'title'       => '',
                                   'description' => $data['comment_text'],
                                   'image_small' => $image_small,
                               )
        );
    }

    /**
     * @param      $product
     * @param      $comment
     * @param null $comment_type
     */
    private function sendNotificationToOwner($product, $comment, $comment_type = null)
    {
        //Don't send email notification for comments from product owner

        if ($this->_authMember->member_id == $product->member_id) {
            return;
        }

        $productData = new stdClass();
        $productData->mail = $product->mail;
        $productData->username = $product->username;
        $productData->username_sender = $this->_authMember->username;
        $productData->title = $product->title;
        $productData->project_id = $product->project_id;

        $template = 'tpl_user_comment_note';
        if (!empty($comment_type) && $comment_type == '30') {
            $template = 'tpl_user_comment_note_' . $comment_type;
        }

        $emailBuilder = $this->member_email_service;
        //@formatter:off
        $mail = $emailBuilder
            ->withTemplate($template)
            ->setReceiverMail($productData->mail)
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

    /**
     * @param $product
     * @param $comment
     * @param $parent_id
     * @param $comment_type
     */
    private function sendNotificationToParent($product, $comment, $parent_id, $comment_type)
    {

        if (0 == $parent_id) {
            return;
        }

        $tableReplies = $this->commentsTable;
        $parentComment = $tableReplies->getCommentWithMember($parent_id);
        if (0 == count($parentComment)) {
            return;
        }

        if ($this->_authMember->member_id == $parentComment['member_id']) {
            return;
        }

        // email sent by sendNotificationToOwner already
        if ($product->member_id == $parentComment['member_id']) {
            return;
        }

        $productData = new stdClass();
        $productData->mail = $parentComment['mail'];
        $productData->username = $parentComment['username'];
        $productData->username_sender = $this->_authMember->username;
        $productData->title = $product->title;
        $productData->project_id = $product->project_id;

        $template = 'tpl_user_comment_reply_note';
        if (!empty($comment_type) && $comment_type == '30') {
            $template = 'tpl_user_comment_reply_note_' . $comment_type;
        }

        $emailBuilder = $this->member_email_service;
        //@formatter:off
        $mail = $emailBuilder
            ->withTemplate($template)
            ->setReceiverMail($productData->mail)
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

    /**
     * @return JsonModel
     * @acl(accessRole=Roles::ROLENAME_COOKIEUSER)
     */
    public function addreplyreviewnewAction()
    {
        $this->view->setTerminal(true);

        $inputFilter = $this->getInputFilterForAddReview();
        $inputFilter->setData(
            array(
                'p' => $this->getParam('p'),
                'msg' => $this->getParam('msg'),
                's' => $this->getParam('s')
            )
        );

        if (false === $inputFilter->isValid()) {
            return $this->sendErrorResponse($inputFilter->getMessages(), 200);
        }

        $msg = $inputFilter->getValue('msg');
        $project_id = (int)$inputFilter->getValue('p');
        $score = $inputFilter->getValue('s');
        $comment_id = null;
        $status = 'ok';
        $message = '';


        // negative voting msg length > 5
        if ($score < 6 && strlen($msg) < 5) {
            return new JsonModel(
                array('status' => 'error', 'message' => ' At least 5 chars. ', 'data' => '')
            );
        }

        if ($this->currentUser()->isOwner($project_id)) {
            return new JsonModel(
                array('status' => 'error', 'message' => ' Not allowed. ', 'data' => '')
            );
        }

        $comment_id = $this->projectService->saveComment($project_id, $this->_authMember->member_id, $msg, 0, 0);
        if (empty($comment_id)) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - could not save comment for ' . "{$project_id}, {$this->_authMember->member_id}, {$msg}, 0, 0");

            return $this->sendErrorResponse(array('internal error'), 500);
        }

        $resultRating = $this->projectService->scoreForProject(
            $project_id, $this->_authMember->member_id, $score, $msg, $comment_id
        );

        $projectInfo = $this->loadProductInfo($project_id);
        if ($projectInfo) {
            //Send a notification to the owner
            $this->sendNotificationToOwner($projectInfo, HtmlPurifyService::purify($this->getParam('msg')));
        }

        $activity_type = ActivityLogService::PROJECT_COMMENT_VOTE_ADDED;
        if ($score < 0) {
            $activity_type = ActivityLogService::PROJECT_COMMENT_VOTE_REMOVED;
        }
        $data = $this->commentsTable->findById($comment_id)->getArrayCopy();
        $this->updateActivityLog($data, $projectInfo->image_small, $activity_type);

        return new JsonModel(
            array(
                'status'        => $status,
                'message'       => $message,
                'data'          => '',
                'laplace_score' => $projectInfo->laplace_score,
            )
        );
    }

    private function getInputFilterForAddReview()
    {
        $inputFilter = new InputFilter();

        // Add input for "comment_target_id" field
        $inputFilter->add(
            [
                'name'       => 'p',
                'required'   => true,
                'filters'    => [
                    ['name' => 'Digits'],
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    ['name' => 'NotEmpty',
                     'options' => [
                         NotEmpty::INTEGER, NotEmpty::ZERO
                     ]]
                ],
            ]
        );

        // Add input for "score" field
        $inputFilter->add(
            [
                'name'       => 's',
                'required'   => true,
                'filters'    => [
                    ['name' => 'ToInt'],
                ],
                'validators' => [
                    ['name' => \Laminas\I18n\Validator\IsInt::class,],
                ],
            ]
        );

        // Add input for "msg" field
        $inputFilter->add(
            [
                'name'       => 'msg',
                'required'   => true,
                'filters'    => [
                    ['name' => 'StringTrim'],
                    ['name' => HtmlPurify::class],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 3000,
                        ],
                    ],
                ],
            ]
        );

        return $inputFilter;
    }

}