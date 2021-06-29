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

namespace Application\Model\PayPal;


use Application\Model\Repository\MemberPayoutRepository;
use Laminas\Db\Adapter\AdapterInterface;
use Library\Payment\PayPal\Masspay\Ipn;

class MasspayIpnMessage extends Ipn
{

    protected $_tablePayment;
    protected $_payoutsArray;

    protected $_payer_id;
    protected $_payment_date;
    protected $_payment_status;
    protected $_charset;
    protected $_first_name;
    protected $_notify_version;
    protected $_payer_status;
    protected $_verify_sign;
    protected $_payer_email;
    protected $_payer_business_name;
    protected $_last_name;
    protected $_txn_type;
    protected $_residence_country;
    protected $_test_ipn;
    protected $_ipn_track_id;

    function __construct(AdapterInterface $db, $config, $logger)
    {
        $logger->info(__METHOD__ . ' - Init Class ');
        parent::__construct($db, $config->third_party->paypal, $logger);

        $this->_tablePayment = new MemberPayoutRepository($db);
    }

} 