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

class Default_Model_PayPal_DonationIpnMessage extends Local_Payment_PayPal_Donation_Ipn
{

    /** @var \Default_Model_DbTable_Donation */
    protected $_tableDonation;

    function __construct($config = null, $logger = null)
    {
        if (null == $logger) {
            $logger = Zend_Registry::get('logger');
        }

        if (null == $config) {
            $config = Zend_Registry::get('config');
        }

        parent::__construct($config->third_party->paypal, $logger);

        $this->_tableDonation = new Default_Model_DbTable_Donation;
    }

    protected function validateTransaction()
    {
        $donation = $this->_tableDonation->fetchDonationFromResponse($this->_ipnMessage);
        
        if (empty($donation)) {
            $this->_logger->err(__METHOD__ . ' - ' . 'No transaction found for IPN message.' . print_r($this->_ipnMessage, true));
            return false;
        } else {
            $this->_dataIpn = $donation->toArray();
        }

        return $this->_checkAmount() && $this->_checkEmail();
    }

    protected function _checkAmount()
    {
        return true;
    }

    protected function _checkEmail()
    {
        return true;
    }

    protected function _statusCompleted()
    {
        $this->_processTransactionStatusCompleted();
    }

    protected function _statusError()
    {
        $this->_tableDonation->deactivateDonationFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusCompleted()
    {
        $this->_tableDonation->activateDonationFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusPending()
    {
        $this->_tableDonation->activateDonationFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusRefunded()
    {
        $this->_tableDonation->deactivateDonationFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusDenied()
    {
        $this->_tableDonation->deactivateDonationFromResponse($this->_ipnMessage);
    }

} 