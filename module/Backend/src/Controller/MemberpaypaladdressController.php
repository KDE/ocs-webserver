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

use Application\Model\Entity\MemberPaypalAddress;
use Application\Model\Interfaces\MemberPaypalAddressInterface;
use Laminas\View\Model\JsonModel;

class MemberpaypaladdressController extends BackendBaseController
{

    const DATA_ID_NAME = 'id';

    public function __construct(
        MemberPaypalAddressInterface $memberPaypalAddressRepository
    ) {
        parent::__construct();

        $this->_model = $memberPaypalAddressRepository;
        $this->_modelName = MemberPaypalAddress::class;
        $this->_pageTitle = 'Manage Paypal Addresses';
        $this->_defaultSorting = 'id asc';
    }

    public function deleteAction()
    {
        $dataId = (int)$this->getParam(self::DATA_ID_NAME, null);

        $this->_model->setDeleted($dataId);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);

    }

    public function listAction()
    {
        $filter['last_payment_status'] = $this->getParam('filter_status');
        $filter['member_id'] = $this->getParam('filter_member_id');
        $filter['paypal_address'] = $this->getParam('filter_paypal_mail');
        $jTableResult = $this->prepareListActionJTableResult(null, $filter);

        return new JsonModel($jTableResult);
    }

}