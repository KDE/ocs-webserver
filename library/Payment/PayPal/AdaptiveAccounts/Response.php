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

namespace Library\Payment\PayPal\AdaptiveAccounts;


use Library\Payment\Exception;

class Response
{


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

    public function getRawMessage()
    {
        return $this->_rawResponse;
    }

    public function getProviderName()
    {
        return 'paypal';
    }

    public function getField($name)
    {
        return $this->_rawResponse[$name];
    }

    public function isSuccessful()
    {
        throw new Exception("not implemented");
    }

    public function getStatus()
    {
        throw new Exception("not implemented");
    }

    public function isRequestSuccessful()
    {
        throw new Exception("not implemented");
    }

    public function isVerifiedAccount()
    {
        throw new Exception("not implemented");
    }

}