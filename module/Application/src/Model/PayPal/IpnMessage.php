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


use Application\Model\Repository\MemberRepository;
use Application\Model\Repository\PlingsRepository;
use Application\Model\Repository\ProjectRepository;
use Laminas\Db\Adapter\AdapterInterface;
use Library\Payment\PayPal\AdaptivePayment\Ipn;
use Zend_Currency;

class IpnMessage extends Ipn
{

    protected $_tablePling;

    function __construct(AdapterInterface $db, $config, $logger)
    {
        parent::__construct($db, $config->third_party->paypal, $logger);

        $this->_tablePling = new PlingsRepository($db);
    }

    protected function validateTransaction()
    {
        if (!$this->_ipnMessage) {
            return false;
        } else {
            $this->_dataIpn = $this->_tablePling->fetchPlingFromResponse($this->_ipnMessage)->getArrayCopy();
            if (empty($this->_dataIpn)) {
                $this->_logger->err(__METHOD__ . ' - ' . 'No transaction found for IPN message.' . PHP_EOL);

                return false;
            }
            $tableProject = new ProjectRepository($this->db);
            $project = $tableProject->findById($this->_dataIpn['project_id']);
            $tableMember = new MemberRepository($this->db);
            $member = $tableMember->findById($project->member_id);

            return $this->_checkAmount() and $this->_checkEmail();
        }
    }

    protected function _checkAmount()
    {
        $amount = isset($this->_dataIpn['amount']) ? $this->_dataIpn['amount'] : 0;
        $receiver_amount = (float)$amount - (float)$this->_config->facilitator_fee;
        $currency = new Zend_Currency('en_US');
        $this->_logger->debug(__METHOD__ . ' - ' . $this->_ipnMessage->getTransactionAmount() . ' == ' . $currency->getShortName() . ' ' . $receiver_amount);

        return $this->_ipnMessage->getTransactionAmount() == $currency->getShortName() . ' ' . $amount;
    }

    protected function _checkEmail()
    {
        $email = isset($this->_dataIpn['email']) ? $this->_dataIpn['email'] : '';
        $this->_logger->debug(__METHOD__ . ' - ' . $this->_ipnMessage->getTransactionReceiver() . ' == ' . $email);

        return $this->_ipnMessage->getTransactionReceiver() == $email;
    }

    protected function _statusCompleted()
    {
        $this->processTransactionStatus();
    }

    protected function _statusError()
    {
        $this->_tablePling->deactivatePlingsFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusCompleted()
    {
        $this->_tablePling->activatePlingsFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusPending()
    {
        $this->_tablePling->activatePlingsFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusRefunded()
    {
        $this->_tablePling->deactivatePlingsFromResponse($this->_ipnMessage);
    }

    protected function _processTransactionStatusDenied()
    {
        $this->_tablePling->deactivatePlingsFromResponse($this->_ipnMessage);
    }

} 