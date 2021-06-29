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

use Application\Model\Entity\PayoutStatus;
use Application\Model\Interfaces\PayoutStatusInterface;

class PayoutstatusController extends BackendBaseController
{

    const DATA_ID_NAME = 'id';

    /** @var PayoutStatus */
    protected $_model;

    protected $_modelName = 'Default_Model_DbTable_PayoutStatus';
    protected $_pageTitle = 'Manage Payout-Stati';

    private $payoutStatusRepository;

    public function __construct(
        PayoutStatusInterface $payoutStatusRepository
    ) {
        parent::__construct();
        $this->payoutStatusRepository = $payoutStatusRepository;
        $this->_model = $payoutStatusRepository;
        $this->_modelName = PayoutStatus::class;
        $this->_pageTitle = 'Manage Payout-Stati';
        $this->_defaultSorting = 'id asc';
    }

} 