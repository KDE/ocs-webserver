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

class Local_Payment_Amazon_FlexiblePayment_Response implements Local_Payment_ResponseInterface
{

    protected $_rawResponse;

    /**
     * @param array|null $rawResponse
     */
    function __construct($rawResponse = null)
    {
        if (isset($rawResponse)) {
            $this->_rawResponse = $rawResponse;
        } else {
            $this->_rawResponse = array();
        }
    }

    public function isSuccessful()
    {
        // TODO: Implement isRequestSuccessful() method.
    }

    public function isPaymentCompleted()
    {
        // TODO: Implement isPaymentCompleted() method.
    }

    public function getMessage()
    {
        // TODO: Implement getMessage() method.
    }

    public function getStatus()
    {
        // TODO: Implement getStatus() method.
    }

    public function getCode()
    {
        // TODO: Implement getCode() method.
    }

    public function getTransactionId()
    {
        // TODO: Implement getTransactionReference() method.
    }

    public function getPaymentId()
    {
        return $this->_rawResponse['paymentId'];
    }

    public function setPaymentKey($key)
    {
        $this->_rawResponse['paymentId'] = $key;
    }

    public function getRawMessage()
    {
        return $this->_rawResponse;
    }

    public function getProviderName()
    {
        return 'amazon';
    }

}