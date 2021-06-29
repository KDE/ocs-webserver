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
use Library\Payment\Exception;
use Library\Payment\PayPal\AdaptivePayment\Ipn;

class PayoutIpnMessage extends Ipn
{

    protected $_tablePayout;

    function __construct(AdapterInterface $db, $config, $logger)
    {
        $logger->info(__METHOD__ . ' - Init Class ');
        parent::__construct($db, $config->third_party->paypal, $logger);

        $this->_tablePayout = new MemberPayoutRepository($db);
    }

    protected function processPaymentStatus()
    {
        $this->_logger->info(__METHOD__ . ' - TransactionStatus: ' . $this->_ipnMessage->getTransactionStatus());
        $this->_logger->info(__METHOD__ . ' - TransactionForSenderStatus: ' . $this->_ipnMessage->getTransactionForSenderStatus());

        switch ($this->_ipnMessage->getTransactionStatus()) {
            case 'COMPLETED':
                $this->_statusCompleted();
                break;
            case 'INCOMPLETE':
                $this->_statusIncomplete();
                break;
            case 'CREATED':
                $this->_statusCreated();
                break;
            case 'DENIED':
                $this->_statusDenied();
                break;
            case 'REVERSED':
                $this->_statusReserved();
                break;
            case 'REFUNDED':
                $this->_statusRefunded();
                break;
            case 'FAILED':
                $this->_statusFailed();
                break;
            case 'PARTIALLY_REFUNDED':
                $this->_statusPartiallyRefunded();
                break;
            case 'ERROR':
                $this->_statusError();
                break;
            case 'REVERSALERROR':
                $this->_statusReversalError();
                break;
            case 'PROCESSING':
                $this->_statusProcessing();
                break;
            case 'PENDING':
                $this->_statusPending();
                break;
            default:
                //No normal transaction status, so look into status_for_sender_txn
                switch ($this->_ipnMessage->getTransactionForSenderStatus()) {
                    case 'COMPLETED':
                        $this->_statusCompleted();
                        break;
                    case 'INCOMPLETE':
                        $this->_statusIncomplete();
                        break;
                    case 'CREATED':
                        $this->_statusCreated();
                        break;
                    case 'DENIED':
                        $this->_statusDenied();
                        break;
                    case 'REVERSED':
                        $this->_statusReserved();
                        break;
                    case 'REFUNDED':
                        $this->_statusRefunded();
                        break;
                    case 'FAILED':
                        $this->_statusFailed();
                        break;
                    case 'PARTIALLY_REFUNDED':
                        $this->_statusPartiallyRefunded();
                        break;
                    case 'ERROR':
                        $this->_statusError();
                        break;
                    case 'REVERSALERROR':
                        $this->_statusReversalError();
                        break;
                    case 'PROCESSING':
                        $this->_statusProcessing();
                        break;
                    case 'PENDING':
                        $this->_statusPending();
                        break;
                    default:
                        $this->_logger->info(__METHOD__ . ' Unknown transaction status from PayPal: IPN = ' . print_r($this->_ipnMessage, true));
                }
        }
    }

    protected function _statusCompleted()
    {
        $this->_logger->info(__METHOD__);
        $this->_processTransactionStatusCompleted();
    }

    protected function _processTransactionStatusCompleted()
    {
        $this->_logger->info(__METHOD__);
        $this->_tablePayout->setPayoutStatusCompletedFromResponse($this->_ipnMessage);
    }

    protected function _statusDenied()
    {
        $this->_logger->info(__METHOD__);
        $this->_processTransactionStatusDenied();
    }

    protected function _processTransactionStatusDenied()
    {
        $this->_tablePayout->setPayoutStatusDeniedFromResponse($this->_ipnMessage);
    }

    protected function _statusReserved()
    {
        $this->_logger->info(__METHOD__);
        $this->_processTransactionStatusReserved();
    }

    protected function _processTransactionStatusReserved()
    {
        $this->_tablePayout->setPayoutStatusReservedFromResponse($this->_ipnMessage);
    }

    protected function _statusRefunded()
    {
        $this->_logger->info(__METHOD__);
        $this->_processTransactionStatusRefunded();
    }

    protected function _processTransactionStatusRefunded()
    {
        $this->_tablePayout->setPayoutStatusRefundFromResponse($this->_ipnMessage);
    }

    protected function _statusFailed()
    {
        $this->_logger->info(__METHOD__);
        $this->_processTransactionStatusFailed();
    }

    protected function _processTransactionStatusFailed()
    {
        $this->_tablePayout->setPayoutStatusFailedFromResponse($this->_ipnMessage);
    }

    protected function _statusError()
    {
        $this->_tablePayout->deactivatePayoutFromResponse($this->_ipnMessage);
    }

    protected function _statusPending()
    {
        $this->_logger->info(__METHOD__);
        $this->_processTransactionStatusPending();
    }

    protected function _processTransactionStatusPending()
    {
        $this->_tablePayout->setPayoutStatusPendingFromResponse($this->_ipnMessage);
    }

    protected function validateTransaction()
    {
        $this->_logger->info(__METHOD__ . ' - Status: ' . $this->_ipnMessage->getTransactionStatus());
        $this->_logger->info(__METHOD__ . ' - PaymentId: ' . $this->_ipnMessage->getPaymentId());
        $this->_logger->info(__METHOD__ . ' - TransactionId: ' . $this->_ipnMessage->getTransactionId());

        $donation = $this->_tablePayout->fetchPayoutFromResponse($this->_ipnMessage);

        if (empty($donation)) {
            $this->_logger->err(__METHOD__ . ' - ' . 'No transaction found for IPN message.' . print_r($this->_ipnMessage, true));

            return false;
        } else {
            $this->_dataIpn = $donation;
        }

        $ckAmount = $this->_checkAmount();
        $ckEmail = $this->_checkEmail();

        return $ckAmount and $ckEmail;
    }

    protected function _checkAmount()
    {
        $amount = isset($this->_dataIpn['amount']) ? $this->_dataIpn['amount'] : 0;
        $this->_logger->info(__METHOD__ . ' - ' . $this->_ipnMessage->getTransactionAmount() . ' == ' . 'USD ' . number_format((float)$amount, 2, '.', ''));

        return ($this->_ipnMessage->getTransactionAmount()) == 'USD ' . number_format((float)$amount, 2, '.', '');
    }

    protected function _checkEmail()
    {
        //$email = isset($this->_dataIpn['email']) ? $this->_dataIpn['email'] : '';
        //$this->_logger->info(__METHOD__ . ' - ' . $this->_ipnMessage->getTransactionReceiver() . ' == ' . $email);
        //return $this->_ipnMessage->getTransactionReceiver() == $email;

        return true;
    }

    protected function processTransactionStatus()
    {
        $this->_logger->info(__METHOD__ . ' - IPN Status: ' . $this->_ipnMessage->getTransactionStatus());
        $this->_tablePayout->updatePayoutTransactionStatusFromResponse($this->_ipnMessage);


        switch (strtoupper($this->_ipnMessage->getTransactionStatus())) {
            case 'COMPLETED':
                $this->_processTransactionStatusCompleted();
                break;
            case 'PENDING':
                $this->_processTransactionStatusPending();
                break;
            case 'DENIED':
                $this->_processTransactionStatusDenied();
                break;
            case 'REVERSED':
                $this->_processTransactionStatusReserved();
                break;
            case 'REFUNDED':
                $this->_processTransactionStatusRefunded();
                break;
            case 'FAILED':
                $this->_processTransactionStatusFailed();
                break;
            default:
                switch (strtoupper($this->_ipnMessage->getTransactionForSenderStatus())) {
                    case 'PENDING':
                        $this->_processTransactionStatusPending();
                        break;
                    default:
                        throw new Exception('Unknown transaction status from PayPal: TransactionStatus = ' . $this->_ipnMessage->getTransactionStatus() . ' --- TransactionForSenderStatus = ' . $this->_ipnMessage->getTransactionForSenderStatus);
                }
                throw new Exception('Unknown transaction status from PayPal: TransactionStatus = ' . $this->_ipnMessage->getTransactionStatus() . ' --- TransactionForSenderStatus = ' . $this->_ipnMessage->getTransactionForSenderStatus);
        }
    }
} 