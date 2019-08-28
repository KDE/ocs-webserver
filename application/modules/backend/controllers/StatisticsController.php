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
class Backend_StatisticsController extends Local_Controller_Action_Backend
{
    
    public function init()
    {
        parent::init();
        $this->setLayout();     
    }

    public function indexAction(){}

    public function newMembersProjectsAction(){}
    public function newProductsWeeklyAction(){}
    public function downloadsDomainAction(){}
    public function downloadsPayoutsDailyAction(){}
    public function productPerDayAction(){}
    public function productPerMonthAction(){}
    public function payoutMemberAction(){}
    public function payoutNewcomerAction(){}
    public function payoutNewloserAction(){}
    public function payoutMonthDiffAction(){}
    public function payoutCategoriesAction(){}
    public function payoutCategoryMonthlyAction(){}
    

    protected function setLayout()
    {
        $this->_helper->layout()->setLayout('layout_statistics');
    }

   

}