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

namespace Library\Payment\PayPal\Support;

use Library\Payment\PayPal\PaymentInterface;

/**
 * Class ResponseSupport
 *
 * @package Library\Payment\PayPal\Support
 */
class ResponseSupport implements PaymentInterface
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
        return strtoupper($this->_rawResponse['payment_status']);
    }

    /**
     * @return mixed
     */
    public function getCustom()
    {
        return strtoupper($this->_rawResponse['custom']);
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->_rawResponse['txn_id'];
    }

    public function getTransactionStatus()
    {
        return ($this->_rawResponse['payment_status']);
    }

    /**
     * @return mixed
     */
    public function getPaymentId()
    {
        return null;
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
        return null;
    }

    public function getTransactionReceiver()
    {
        return null;
    }

}