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
class Statistics_MonthlyController extends Zend_Controller_Action
{

    const PROJECT_ID = 'project_id';
    const YEAR = 'year';
    const MONTH = 'month';
    const DAY = 'day';

    /** @var Statistics_Model_GoalStatistics */
    protected $tableStatistics;
    /** @var Zend_Auth */
    protected $authorization;
    /** @var Zend_Controller_Request_Abstract */
    protected $request;
    /** @var mixed */
    protected $loginMemberId;

    public function init()
    {
        parent::init();

        $this->tableStatistics = new Statistics_Model_GoalStatistics();
        $this->authorization = Zend_Auth::getInstance();
        $this->loginMemberId =
            $this->authorization->hasIdentity() ? $this->authorization->getStorage()->read()->member_id : 0;
        $this->request = $this->getRequest();
    }

    public function showAction()
    {
        $this->_helper->layout->disableLayout();
    }

    public function ajaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $identifier = $this->request->getParam(self::PROJECT_ID);
        $year = $this->request->getParam(self::YEAR);
        $month = $this->request->getParam(self::MONTH);

        $resultSet = $this->tableStatistics->getMonthlyStatistics($identifier, $year, $month);

        if (empty($resultSet)) {
            $this->_helper->json(null);
        } else {
            $this->_helper->json($this->generateGoogleChartDataSet($resultSet));
        }
    }

    /**
     * @param array $dataSet
     *
     * @return array
     */
    protected function generateGoogleChartDataSet($dataSet)
    {
        $rows = array();
        $rows[] = array_keys($dataSet[0]);
        foreach ($dataSet as $value) {
            $row = array();
            foreach ($value as $key => $rowElement) {
                $row[] = (int)$rowElement;
            }
            $rows[] = $row;
        }

        return $rows;
    }

}
