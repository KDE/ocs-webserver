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

class Local_Payment_Dwolla_ResponsePayRequest implements Local_Payment_ResponseInterface
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

    public function getStatus()
    {
        if ($this->isSuccessful()) {
            return 'CREATED';
        } else {
            return 'ERROR';
        }
    }

    public function isSuccessful()
    {
        return ($this->_rawResponse['Result'] == 'Success');
    }

    public function getTransactionId()
    {
        return null;
    }

    public function getTransactionStatus()
    {
        return null;
    }

    public function getPaymentId()
    {
        return $this->_rawResponse['CheckoutId'];
    }

    public function getRawMessage()
    {
        return $this->_rawResponse;
    }

    public function getProviderName()
    {
        return 'dwolla';
    }

}