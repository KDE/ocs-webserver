<?php /** @noinspection PhpUndefinedFieldInspection */

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

namespace Backend\Controller;

use Application\Model\Interfaces\MemberInterface;
use Application\Model\Interfaces\PaypalValidStatusInterface;
use Application\Model\Service\ActivityLogService;
use Application\Model\Service\MemberService;
use Application\Model\Service\Ocs\ServerManager;
use Exception;
use Laminas\Db\Metadata\Source\Factory;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Where;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

/**
 * Class UserController
 *
 * @package Backend\Controller
 */
class UserController extends BackendBaseController
{

    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    const DATA_ID_NAME = 'member_id';

    protected $_model;

    protected $_modelName = 'Default_Model_Member';
    protected $_pageTitle = 'Manage Users';

    /** @var MemberInterface */
    private $memberRepository;
    /** @var MemberService */
    private $memberService;
    /** @var PhpRenderer */
    private $phpRenderer;
    /** @var PaypalValidStatusInterface */
    private $paypalValidStatusRepository;
    /** @var ServerManager */
    private $ocs_manager;

    public function __construct(
        MemberInterface $memberRepository,
        MemberService $memberService,
        PhpRenderer $phpRenderer,
        PaypalValidStatusInterface $paypalValidStatusRepository,
        ServerManager $serverManager
    ) {
        parent::__construct();
        $this->memberRepository = $memberRepository;
        $this->memberService = $memberService;
        $this->phpRenderer = $phpRenderer;
        $this->paypalValidStatusRepository = $paypalValidStatusRepository;
        $this->ocs_manager = $serverManager;
    }

    public function indexAction()
    {
        $viewModel = new ViewModel();
        $this->layout()->pageTitle = $this->_pageTitle;
        $paypalValidStatus = $this->paypalValidStatusRepository->getStatiForSelectList();
        $viewModel->setVariable('paypalValidStatus', $paypalValidStatus);

        return $viewModel;
    }

    public function createAction()
    {
        $jTableResult = array();
        try {
            $data = $this->params()->fromPost();
            $member_id = $this->memberRepository->insert($data);
            $member = $this->memberRepository->fetchById($member_id);

            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $member;
        } catch (Exception $e) {
            // $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            error_log($e->__toString());
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function updateAction()
    {
        $jTableResult = array();
        try {

            $data = $this->params()->fromPost();
            foreach ($data as $key => $value) {
                if ($value == '') {
                    $data[$key] = new Expression('NULL');
                }
            }
            $this->memberRepository->insertOrUpdate($data);
            $id = $data['member_id'];
            $record = $this->memberRepository->fetchById($id);
            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record;
        } catch (Exception $e) {
            //$this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            error_log($e->__toString());

            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function deleteAction()
    {
        $memberId = (int)$this->params()->fromPost('c', null);
        if (null == $memberId) {
            $memberId = (int)$this->params()->fromQuery('member_id', null);
        }

        if (null == $memberId) {
            $this->ocsLog->err("Error in deleteAction: no MemberId found");
        }

        try {
            $this->memberService->setDeleted($memberId);
            ActivityLogService::logActivity($memberId, null, $this->ocsUser->member_id, ActivityLogService::BACKEND_USER_DELETE, null);
        } catch (Exception $e) {
            $this->ocsLog->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function listAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');

        $filter['member_id'] = $this->params()->fromPost('filter_member_id');
        $filter['lastname'] = $this->params()->fromPost('filter_lastname');
        $filter['firstname'] = $this->params()->fromPost('filter_firstname');
        $filter['username'] = $this->params()->fromPost('filter_username');
        $filter['mail'] = $this->params()->fromPost('filter_mail');

        $metadata = Factory::createSourceFromAdapter($this->db);
        $table = $metadata->getTable('member');
        $columns = $table->getColumns();

        $whereObj = new Where();
        foreach ($filter as $key => $value) {
            if (is_array($value)) {
                $whereObj->in($key, $value);
                continue;
            }
            if (false === empty($value)) {
                $data_type = '';
                foreach ($columns as $column) {

                    if ($column->getName() == $key) {
                        $data_type = $column->getDataType();
                        break;
                    }
                }
                if (($data_type == 'varchar') or ($data_type == 'text')) {
                    $whereObj->like($key, '%' . $value . '%');
                } else {
                    $whereObj->equalTo($key, $value);
                }
            }
        }

        $reports = $this->memberRepository->fetchAllRows($whereObj, $sorting, $pageSize, $startIndex);
        $reportsCount = $this->memberRepository->fetchAllRowsCount($whereObj);
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $reports->toArray();
        $jTableResult['TotalRecordCount'] = $reportsCount;

        return new JsonModel($jTableResult);
    }

    public function memberinfoAction()
    {
        $jTableResult = array();
        try {
            $memberId = (int)$this->params()->fromPost('member_id');
            $record = $this->memberRepository->fetchById($memberId);

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $record;
            $jTableResult['ViewRecord'] = $this->phpRenderer->render(
                '/backend/user/member.phtml', ['member' => $record]
            );
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = 'Error while processing data.';
        }

        return new JsonModel($jTableResult);
    }

    public function exportAction()
    {
        $jTableResult = array();
        try {
            $memberId = (int)$this->params()->fromPost('c');

            $modelMember = $this->memberService;
            $record = $modelMember->fetchMemberData($memberId, false);

            if (is_object($record)) {
                // Gets the properties of the given object
                // with get_object_vars function
                $record = get_object_vars($record);
            }

            $this->ocs_manager->insert($record);


            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            //$jTableResult['Record'] = $record->toArray();
            $jTableResult['Message'] = "OK";
        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . ' - (Line ' . $e->getLine() . ') ' . $e->getMessage());
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $e->getMessage();
        }

        return new JsonModel($jTableResult);
    }

    public function reactivateAction()
    {
        $memberId = (int)$this->params()->fromPost('c');

        $this->memberService->setActivated($memberId);

        $identity = $this->ocsUser;

        try {
            ActivityLogService::logActivity(
                $memberId, null, $identity->member_id, ActivityLogService::BACKEND_USER_UNDELETE, null
            );
        } catch (Exception $e) {
            $this->ocsLog->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

    public function doexcludeAction()
    {
        $memberId = (int)$this->getParam('member_id', null);
        $member = $this->memberRepository->fetchById($memberId);
        $exclude = (int)$this->getParam('pling_excluded', null);
        $prev_pling_excluded = $member['pling_excluded'];

        $this->memberRepository->update(array('pling_excluded' => $exclude, 'member_id' => $memberId));

        $identity = $this->currentUser();

        $logArray = array();
        $logArray['title'] = $member['username'];
        $logArray['description'] = 'Change pling_excluded from ' . $prev_pling_excluded . ' to ' . $exclude;

        ActivityLogService::logActivity(
            $memberId, $memberId, $identity->member_id, ActivityLogService::BACKEND_USER_PLING_EXCLUDED, $logArray
        );

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

} 