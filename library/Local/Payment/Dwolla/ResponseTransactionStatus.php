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

class Local_Payment_Dwolla_ResponseTransactionStatus implements Local_Payment_Dwolla_ResponseInterface
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
        return null;
    }

    public function getTransactionId()
    {
        return $this->_rawResponse['Id'];
    }

    public function getTransactionStatus()
    {
        return $this->getStatus();
    }

    public function getStatus()
    {
        return strtoupper($this->_rawResponse['Transaction']['Status']);
    }

    public function getPaymentId()
    {
        return null;
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
        // Get Dwolla's signature
        $headers = getallheaders();
        $signature = $headers['X-Dwolla-Signature'];

        //  Calculate hash, and compare to the signature
        $hash = hash_hmac('sha1', $this->_msgBody, $secret);

        return ($hash == $signature);

    }

    public function getTransactionAmount()
    {
        return $this->_rawResponse['Transaction']['Amount'];
    }

    public function getTransactionReceiver()
    {
        return $this->_rawResponse['Transaction']['Destination']['Id'];
    }


}