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

namespace Library\Payment\PayPal\SubscriptionCancel;

use Library\Payment\PayPal\SubscriptionSignupInterface;

/**
 * Class Response
 *
 * @package Library\Payment\PayPal\SubscriptionCancel
 */
class Response implements SubscriptionSignupInterface
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
     * @return mixed
     */
    public function getCustom()
    {
        return strtoupper($this->_rawResponse['custom']);
    }

    /**
     * @return mixed
     */
    public function getSubscriptionDate()
    {
        return strtoupper($this->_rawResponse['subscr_date']);
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

    public function getSubscriptionAmount()
    {
        $period = null;

        for ($index = 0; $index < 6; $index++) {
            if (array_key_exists('amount' . $index, $this->_rawResponse)) {
                $period = $this->_rawResponse['amount' . $index];
            }

        }

        return $period;
    }

    public function getSubscriptionId()
    {
        return strtoupper($this->_rawResponse['subscr_id']);
    }

    public function getSubscriptionPeriod()
    {
        $period = null;

        for ($index = 0; $index < 6; $index++) {
            if (array_key_exists('period' . $index, $this->_rawResponse)) {
                $period = $this->_rawResponse['period' . $index];
            }

        }

        return $period;
    }

}