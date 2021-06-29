<?php
/** @noinspection PhpUnused */

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
use Application\Model\Repository\ProjectCloneRepository;
use Application\Model\Repository\ProjectRatingRepository;
use Application\Model\Repository\ProjectRepository;
use Application\Model\Repository\ReportCommentsRepository;
use Application\Model\Repository\ReportProductsRepository;
use Application\Model\Service\ProjectCommentsService;
use Application\Model\Service\ProjectService;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Request;
use Laminas\View\Model\JsonModel;

/**
 * Class ReportController
 *
 * @package Application\Controller
 */
class ReportController extends DomainSwitch
{

    protected $commentsService;
    protected $commentsTable;
    protected $projectTable;
    protected $projectService;
    protected $projectRatingTable;
    protected $configArray;

    public function __construct(
        AdapterInterface $db,
        array $config,
        Request $request,
        ProjectCommentsService $commentsService,
        CommentsRepository $commentsTable,
        ProjectRepository $projectTable,
        ProjectRatingRepository $projectRatingTable,
        ProjectService $projectService
    ) {
        parent::__construct($db, $config, $request);
        parent::init();
        $this->commentsService = $commentsService;
        $this->commentsTable = $commentsTable;
        $this->projectTable = $projectTable;
        $this->projectRatingTable = $projectRatingTable;
        $this->projectService = $projectService;

        $this->_authMember = $this->view->getVariable('ocs_user');
        $this->configArray = $config;
    }

    public function commentAction()
    {

        if ((APPLICATION_ENV != 'searchbotenv') and (false == SEARCHBOT_DETECTED)) {
            $comment_id = (int)$this->getParam('i');
            $project_id = (int)$this->getParam('p');
            $reported_by = $this->_authMember ? (int)$this->_authMember->member_id : 0;

            $clientIp = null;
            $clientIp2 = null;
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $clientIp = $_SERVER['REMOTE_ADDR'];
            }
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $clientIp2 = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }

            $tableReportComments = new ReportCommentsRepository($this->db);

            $commentReportArray = $tableReportComments->fetchAll(sprintf('select * from %s where `comment_id` = %d AND `user_ip` = "%s"', $tableReportComments->getName(), $comment_id, $clientIp));

            if (isset($commentReportArray) && count($commentReportArray) > 0) {
                return new JsonModel(
                    array(
                        'status'  => 'ok',
                        'message' => '<p>You have already submitted a report for this comment.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
                        'data'    => array(),
                    )
                );
            } else {
                $tableReportComments->insert(
                    array(
                        'project_id'  => $project_id,
                        'comment_id'  => $comment_id,
                        'reported_by' => $reported_by,
                        'user_ip'     => $clientIp,
                        'user_ip2'    => $clientIp2,
                    )
                );

                return new JsonModel(
                    array(
                        'status'  => 'ok',
                        'message' => '<p>Thank you for helping us to keep these sites SPAM-free.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
                        'data'    => array(),
                    )
                );
            }
        }
    }

    public function productAction()
    {
        if ((APPLICATION_ENV != 'searchbotenv') and (false == SEARCHBOT_DETECTED)) {

            $session = $GLOBALS['ocs_session'];

            $reportedProducts = isset($session->reportedProducts) ? $session->reportedProducts : array();
            $project_id = (int)$this->getParam('p');
            if (in_array($project_id, $reportedProducts)) {
                return new JsonModel(
                    array(
                        'status'  => 'ok',
                        'message' => '<p>Thank you, but you have already reported this product.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
                        'data'    => array(),
                    )
                );
            }
            $reported_by = 0;
            if ($this->_authMember) {
                $reported_by = (int)$this->_authMember->member_id;
            }

            $modelProduct = $this->projectTable;
            $productData = $modelProduct->findById($project_id);

            if (empty($productData)) {
                return new JsonModel(
                    array(
                        'status'  => 'ok',
                        'message' => '<p>Thank you for helping us to keep these sites SPAM-free.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
                        'data'    => array(),
                    )
                );
            }

            if ($productData->spam_checked == 0) {
                $tableReportComments = new ReportProductsRepository($this->db);
                $tableReportComments->insert(
                    array(
                        'project_id'  => $project_id,
                        'reported_by' => $reported_by,
                        'text'        => 'reported by user',
                    )
                );
            }
            $reportedProducts[] = $project_id;
            $session->reportedProducts = $reportedProducts;
        }

        return new JsonModel(
            array(
                'status'  => 'ok',
                'message' => '<p>Thank you for helping us to keep these sites SPAM-free.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
                'data'    => array(),
            )
        );
    }

    public function productfraudAction()
    {
        $report_type = 1;

        if ((APPLICATION_ENV != 'searchbotenv') and (false == SEARCHBOT_DETECTED)) {

            $session = $GLOBALS['ocs_seesion'];
            $reportedFraudProducts = isset($session->reportedFraudProducts) ? $session->reportedFraudProducts : array();
            $project_id = (int)$this->getParam('p');
            $text = $this->getParam('t');
            if (in_array($project_id, $reportedFraudProducts)) {
                return new JsonModel(
                    array(
                        'status'  => 'ok',
                        'message' => '<p>Thank you, but you have already reported this product.</p><div class="modal-footer">
                                                    <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                                </div>',
                        'data'    => array(),
                    )
                );
            }

            if ($this->_authMember) {
                $reported_by = (int)$this->_authMember->member_id;
                $reportProducts = new ReportProductsRepository($this->db);
                $reportProducts->insert(
                    array(
                        'project_id'  => $project_id,
                        'reported_by' => $reported_by,
                        'text'        => $text,
                        'report_type' => $report_type,
                    )
                );
            }

            $session->reportedFraudProducts[] = $project_id;
        }

        return new JsonModel(
            array(
                'status'  => 'ok',
                'message' => '<p>Thank you for reporting the misuse.</p><p>We will try to verify the reason for this case.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>',
                'data'    => array(),
            )
        );
    }

    public function flagmodAction()
    {

        //$params = $this->getAllParams();
        $params = array();
        if ((APPLICATION_ENV != 'searchbotenv') and (false == SEARCHBOT_DETECTED)) {

            $project_clone = $this->getParam('p');
            $text = $this->getParam('t');
            $url = $this->getParam('l');
            $project_id = 0;

            if ($this->_authMember) {
                $reported_by = (int)$this->_authMember->member_id;
                $reportProducts = new ProjectCloneRepository($this->db);
                $reportProducts->insert(
                    array(
                        'project_id'         => $project_clone,
                        'member_id'          => $reported_by,
                        'text'               => $text,
                        'external_link'      => $url,
                        'project_clone_type' => 1,
                        'project_id_parent'  => $project_id,
                    )
                );
            }
        }

        return new JsonModel(
            array(
                'status'  => 'ok',
                'message' => '<p>Thank you. The credits have been submitted.</p><p>It can take some time to appear while we verify it.</p>
                                           
                                       ',
                'data'    => $params,
            )
        );
    }

    public function productcloneAction()
    {

        //$params = $this->getAllParams();
        $params = array();
        $productInfo = null;
        if ((APPLICATION_ENV != 'searchbotenv') and (false == SEARCHBOT_DETECTED)) {

            $project_clone = $this->getParam('p');
            $text = $this->getParam('t');
            $project_id_parent = $this->getParam('pc');
            $type = $this->getParam('i');

            $modelProduct = $this->projectTable;
            $productInfo = $modelProduct->fetchProductInfo($project_id_parent);
            if (empty($productInfo)) {
                return new JsonModel(
                    array(
                        'status'  => 'err',
                        'message' => 'Please input a valid project ID from pling. ',
                        'data'    => $params,
                    )
                );
            }
            if ($project_id_parent) {
                $text = $text . ' ' . $project_id_parent;
            }
            if (!is_numeric($project_id_parent)) {
                $project_id_parent = 0;
            }
            if ($this->_authMember) {
                $reported_by = (int)$this->_authMember->member_id;
                $reportProducts = new ProjectCloneRepository($this->db);
                if ($type == 'is-original') {
                    $reportProducts->insert(
                        array(
                            'project_id'        => $project_clone,
                            'member_id'         => $reported_by,
                            'text'              => $text,
                            'project_id_parent' => $project_id_parent,
                        )
                    );
                } else {
                    $reportProducts->insert(
                        array(
                            'project_id'        => $project_id_parent,
                            'member_id'         => $reported_by,
                            'text'              => $text,
                            'project_id_parent' => $project_clone,
                        )
                    );
                }
            }
        }

        return new JsonModel(
            array(
                'status'  => 'ok',
                'message' => '<p>Thank you. The credits have been submitted.</p><p>It can take some time to appear while we verify it.</p>',
            )
        );
    }

}
