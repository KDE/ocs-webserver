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

class Default_Model_Dwolla_Callback extends Local_Payment_Dwolla_Callback
{

    /** @var \Default_Model_Pling */
    protected $_tablePling;

    /** @var  Zend_Db_Table_Row_Abstract */
    protected $_storedDataTransaction;

    function __construct($config = null, $logger = null)
    {
        if (null == $logger) {
            $logger = Zend_Registry::get('logger');
        }

        if (null == $config) {
            $config = Zend_Registry::get('config');
        }

        parent::__construct($config->third_party->dwolla, $logger);

        $this->_tablePling = new Default_Model_Pling();
    }

    protected function validateTransaction()
    {
        $this->_storedDataTransaction = $this->_tablePling->fetchPlingFromResponse($this->_transactionMessage);
        if (null === $this->_storedDataTransaction) {
            $this->_logger->err(__METHOD__ . ' - ' . 'No transaction found for IPN message.' . PHP_EOL);
            return false;
        } else {
            $this->_logger->debug(__METHOD__ . " - " . print_r($this->_storedDataTransaction->toArray(), true) . PHP_EOL);
        }

        return $this->_checkAmount();
    }

    protected function _checkAmount()
    {
        $this->_logger->debug(__METHOD__ . ' - ' . "{$this->_storedDataTransaction->amount} == {$this->_transactionMessage->getTransactionAmount()}");
        return $this->_storedDataTransaction->amount == $this->_transactionMessage->getTransactionAmount();
    }

    protected function _statusCompleted()
    {
        $this->_logger->info(__METHOD__ . '::' . "activate plings." . PHP_EOL);
        $this->_tablePling->activatePlingsFromResponse($this->_transactionMessage);
    }

    protected function _statusCancelled()
    {
        $this->_statusError();
    }

    protected function _statusError()
    {
        $this->_logger->info(__METHOD__ . '::' . "deactivate plings." . PHP_EOL);
        $this->_tablePling->deactivatePlingsFromResponse($this->_transactionMessage);
    }

}