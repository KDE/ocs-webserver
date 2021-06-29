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

use Application\Model\Repository\SupportRepository;
use Laminas\Db\Adapter\AdapterInterface;
use Library\Payment\PayPal\SubscriptionSignup\Ipn;

class SubscriptionSignupIpnMessage extends Ipn
{

    protected $_tableSupport;

    function __construct(AdapterInterface $db, $config, $logger)
    {
        parent::__construct($db, $config->third_party->paypal, $logger);

        $this->_tableSupport = new SupportRepository($db);
    }

    protected function validateTransaction()
    {
        $donation = $this->_tableSupport->fetchSupportFromResponse($this->_ipnMessage);

        if (empty($donation)) {
            $this->_logger->err(__METHOD__ . ' - ' . 'No transaction found for IPN message.' . print_r($this->_ipnMessage, true));

            return false;
        } else {
            $this->_dataIpn = $donation->getArrayCopy();
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

    protected function _processTransactionStatusCompleted()
    {
        $this->_tableSupport->activateSupportFromResponse($this->_ipnMessage);
    }

    protected function _statusError()
    {
        $this->_tableSupport->deactivateSupportFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusPending()
    {
        //$this->_tableDonation->activateDonationFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusRefunded()
    {
        $this->_tableSupport->deactivateSupportFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusDenied()
    {
        $this->_tableSupport->deactivateSupportFromResponse($this->_ipnMessage);
    }

} 