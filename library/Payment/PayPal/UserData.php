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

namespace Library\Payment\PayPal;

use Library\Payment\UserDataInterface;

/**
 * Class UserData
 *
 * @package Library\Payment\PayPal
 */
class UserData implements UserDataInterface
{

    protected $_email;
    protected $_firstName;
    protected $_lastName;
    protected $_paypal_mail;
    protected $_productTitle;
    protected $_productId;

    public function getEmail()
    {
        return $this->_email;
    }

    public function getFirstName()
    {
        return $this->_firstName;
    }

    public function setFirstName($firstName)
    {
        $this->_firstName = $firstName;
    }

    public function getLastName()
    {
        return $this->_lastName;
    }

    public function setLastName($lastName)
    {
        $this->_lastName = $lastName;
    }

    public function getPaymentUserId()
    {
        return $this->_paypal_mail;
    }

    public function getProductTitle()
    {
        return $this->_productTitle;
    }

    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * @param $array
     */
    public function generateFromArray($array)
    {
        $this->_email = $array['mail'];
        if (array_key_exists('firstname', $array)) {
            $this->_firstName = $array['firstname'];
        }
        if (array_key_exists('lastname', $array)) {
            $this->_lastName = $array['lastname'];
        }
        if (array_key_exists('paypal_mail', $array)) {
            $this->_paypal_mail = $array['paypal_mail'];
        }
        if (array_key_exists('project_id', $array)) {
            $this->_productId = $array['project_id'];
        }
        if (array_key_exists('title', $array)) {
            $this->_productTitle = $array['title'];
        }
    }

}