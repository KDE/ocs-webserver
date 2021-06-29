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

namespace Library\Payment\PayPal\AdaptivePayment;

use Library\Payment\PayPal\PaymentInterface;

/**
 * Class ResponsePayMock
 *
 * @package Library\Payment\PayPal\AdaptivePayment
 */
class ResponsePayMock implements PaymentInterface
{

    public $transactionStatus;
    public $payKey;
    public $transactionAmount;
    public $transactionReceiver;
    public $successful = true;
    public $status = null;
    public $transactionId = null;
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
        return $this->successful;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function getTransactionStatus()
    {
        return $this->transactionStatus;
    }

    /**
     * @return mixed
     */
    public function getPaymentId()
    {
        return $this->payKey;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getField($name)
    {
        return null;
    }

    /**
     * @return array|null
     */
    public function getRawMessage()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return 'paypal-MOCK';
    }

    public function getTransactionAmount()
    {
        return $this->transactionAmount;
    }

    public function getTransactionReceiver()
    {
        return $this->transactionReceiver;
    }

    public function getCustom()
    {
        return null;
    }

}