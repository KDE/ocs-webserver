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

namespace Backend\Controller;

use Application\Model\Entity\MemberPayout;
use Application\Model\Interfaces\MemberPayoutInterface;
use Application\Model\Interfaces\PayoutStatusInterface;
use Exception;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class MemberpayoutController extends BackendBaseController
{

    const DATA_ID_NAME = 'id';
    private $payoutStatusRepository;
    private $payoutRepository;

    public function __construct(
        PayoutStatusInterface $payoutStatusRepository,
        MemberPayoutInterface $payoutRepository
    ) {
        parent::__construct();
        $this->payoutStatusRepository = $payoutStatusRepository;
        $this->payoutRepository = $payoutRepository;
        $this->_model = $payoutStatusRepository;
        $this->_modelName = MemberPayout::class;
        $this->_pageTitle = 'Manage Payouts';
        $this->_defaultSorting = ' id asc ';
    }

    public function indexAction()
    {
        $viewModel = new ViewModel();
        $viewModel = $this->prepareIndex($viewModel);
        $this->layout()->pageTitle = $this->_pageTitle;

        return $viewModel;
    }

    private function prepareIndex(ViewModel $viewModel)
    {
        $optionString = "'':'',";
        $payoutStatusModel = $this->payoutStatusRepository;
        $list = $payoutStatusModel->getStatiForSelectList();
        foreach ($list as $key => $value) {
            $optionString .= "'" . $key . "':'" . $key . " - " . $value . "',";
        }
        $options = "options: {" . $optionString . "},";
        $viewModel->setVariable('payoutStatusOption', $options);
        $viewModel->setVariable('payoutStatusList', $list);

        return $viewModel;
    }

    public function updateAction()
    {

        $jTableResult = array();
        try {

            $values = $this->params()->fromPost();

            foreach ($values as $key => $value) {
                if ($value == '') {
                    $values[$key] = new Expression('NULL');
                }
            }

            $this->payoutRepository->update($values);

            $values['color'] = '#ffffff';
            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Record'] = $values;
        } catch (Exception $e) {
            // $this->ocsLog->err(__METHOD__ . ' - ' . print_r($e, true));            
            error_log($e->__toString());
            $jTableResult['Result'] = self::RESULT_ERROR;
            $jTableResult['Message'] = $this->translate('Error while processing data. ' . print_r($e, true));
        }

        return new JsonModel($jTableResult);
    }

    public function listAction()
    {
        $filter['yearmonth'] = $this->getParam('filter_yearmonth');
        if (!$this->getParam('filter_yearmonth')) {
            $filter['yearmonth'] = date("Ym", strtotime("first day of previous month"));
        }
        $filter['status'] = $this->getParam('filter_status');
        $filter['member_id'] = $this->getParam('filter_member_id');
        $filter['paypal_mail'] = $this->getParam('filter_paypal_mail');
        $filter['mail'] = $this->getParam('filter_mail');

        $select = new Select();
        $select->from(array('m' => 'member_payout'))->join(
                array('p' => 'payout_status'), 'm.status = p.id', array('color')
            );
        $jTableResult = $this->prepareListActionJTableResult($select, $filter);

        return new JsonModel($jTableResult);
    }

    public function exportAction()
    {
        $filter['yearmonth'] = $this->getParam('filter_yearmonth');
        if (!$this->getParam('filter_yearmonth')) {
            $filter['yearmonth'] = date("Ym", strtotime("first day of previous month"));
        }
        $filter['status'] = $this->getParam('filter_status');
        $filter['member_id'] = $this->getParam('filter_member_id');
        $filter['paypal_mail'] = $this->getParam('filter_paypal_mail');
        $filter['mail'] = $this->getParam('filter_mail');

        $select = new Select();
        $select->from(array('m' => 'member_payout'))->columns(
                array(
                    'member_id as MemberId',
                    'paypal_mail as PayPalMail',
                    'amount as Amount',
                    'status as Status',
                )
            );
        $jTableResult = $this->prepareListActionJTableResult($select, $filter);

        $filename = "Payout_" . $filter['yearmonth'] . ".xls";


        // Write HTTP headers
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine(
            "Content-type: application/vnd.ms-excel"
        );
        $headers->addHeaderLine(
            "Content-Disposition: attachment; filename=\"" . $filename . "\""
        );
        $headers->addHeaderLine("Cache-control: private");
        $response->setContent($this->exportFile($jTableResult['Records']));

        // Return Response to avoid default view rendering
        return $this->getResponse();
    }

    private function exportFile($records)
    {
        $content = '';
        $heading = false;
        if (!empty($records)) {
            foreach ($records as $row) {
                if (!$heading) {
                    // display field/column names as a first row
                    $content .= implode("\t", array_keys($row)) . "\n";
                    $heading = true;
                }
                $content .= implode("\t", array_values($row)) . "\n";
            }
        }

        return $content;

    }

} 