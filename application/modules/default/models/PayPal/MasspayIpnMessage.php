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

class Default_Model_PayPal_MasspayIpnMessage extends Local_Payment_PayPal_Masspay_Ipn
{

    /** @var \Default_Model_Pling */
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
    
    function __construct($config = null, $logger = null)
    {
        if (null == $logger) {
            $logger = Zend_Registry::get('logger');
        }

        if (null == $config) {
            $config = Zend_Registry::get('config');
        }

        parent::__construct($config->third_party->paypal->masspay, $logger);

        $this->_tablePayment = new Default_Model_DbTable_MemberPayout();
        
    }

} 