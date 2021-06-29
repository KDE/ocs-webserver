<?php

namespace Library\Payment\PayPal\AdaptivePayment;

use Library\Payment\PayPal\PaymentInterface;

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
class ResponsePay implements PaymentInterface
{

    /** @var array|null */
    protected $_rawResponse;

    /**
     * @param array|null $rawResponse
     */
    function __construct($rawResponse = null)
    {
        if (isset($rawResponse)) {
            $this->_rawResponse = $rawResponse;
        }
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getStatus() == 'COMPLETED';
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return strtoupper($this->_rawResponse['status']);
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->_rawResponse['transaction'][0]['id'];
    }

    public function getTransactionStatus()
    {
        return strtoupper($this->_rawResponse['transaction'][0]['status']);
    }

    public function getTransactionForSenderStatus()
    {
        return strtoupper($this->_rawResponse['transaction'][0]['status_for_sender_txn']);
    }

    /**
     * @return mixed
     */
    public function getPaymentId()
    {
        return $this->_rawResponse['pay_key'];
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getField($name)
    {
        return $this->_rawResponse[$name];
    }

    /**
     * @return array|null
     */
    public function getRawMessage()
    {
        return $this->_rawResponse;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return 'paypal';
    }

    public function getTransactionAmount()
    {
        return $this->_rawResponse['transaction'][0]['amount'];
    }

    public function getTransactionReceiver()
    {
        return $this->_rawResponse['transaction'][0]['receiver'];
    }

    public function getCustom()
    {
        return null;
    }

}