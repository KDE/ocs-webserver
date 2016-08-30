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

class Local_Payment_Dwolla_ResponseGateway implements Local_Payment_Dwolla_ResponseInterface
{
    protected $_rawResponse;

    protected $_msgBody;

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
     * @return string
     */
    public function getMsgBody()
    {
        return $this->_msgBody;
    }

    /**
     * @param string $msgBody
     */
    public function setMsgBody($msgBody)
    {
        $this->_msgBody = $msgBody;
    }

    public function isSuccessful()
    {
        if ($this->getStatus() == 'COMPLETED' AND $this->getErrorMessage() == null) {
            return true;
        }
        return false;
    }

    public function getStatus()
    {
        return strtoupper($this->_rawResponse['Status']);
    }

    public function getTransactionId()
    {
        return $this->_rawResponse['TransactionId'];
    }

    public function getTransactionStatus()
    {
        return $this->getStatus();
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

    public function verifySignature($secret)
    {
        $amount = number_format($this->_rawResponse['Amount'], 2);
        $signature = hash_hmac("sha1", "{$this->_rawResponse['CheckoutId']}&{$amount}", $secret);

        return $signature == $this->_rawResponse['Signature'];
    }

    public function getTransactionAmount()
    {
        return $this->_rawResponse['Amount'];
    }

    public function getTransactionReceiver()
    {
        return null;
    }

}