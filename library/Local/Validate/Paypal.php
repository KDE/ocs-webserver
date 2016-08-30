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

class Local_Validate_Paypal extends Zend_Validate_Abstract
{

    const INVALID_URL = 'invalidPayPal';
    const INVALID_FIRSTNAME = 'invalidFirstname';
    const INVALID_NAME = 'invalidName';

    protected $_messageTemplates = array(
        self::INVALID_URL => "Cannot determine PayPal Account status. Please check that name and firstname fit to your PayPal email address.",
        self::INVALID_FIRSTNAME => "Your firstname is needed to check your PayPal Account.",
        self::INVALID_NAME => "Your name is needed to check your PayPal Account."
    );

    /**
     * @var string
     */
    private $fieldFirstname;

    /**
     * @var string
     */
    private $fieldName;

    function __construct($fieldFirstname, $fieldName)
    {
        $this->fieldFirstname = $fieldFirstname;
        $this->fieldName = $fieldName;
    }

    public function isValid($value, $context = null)
    {
        $valueString = ( string )$value;
        $this->_setValue($valueString);

        if (empty($context[$this->fieldFirstname])) {
            $this->_error(self::INVALID_FIRSTNAME);
            return false;
        }

        if (empty($context[$this->fieldName])) {
            $this->_error(self::INVALID_NAME);
            return false;
        }

        if (!$this->verifyPayPalAccount($value, $context)) {
            $this->_error(self::INVALID_URL);
            return false;
        }
        return true;
    }

    private function verifyPayPalAccount($mail, $context)
    {
        $data = array('mail' => $mail);
        $data = array_merge($data, $context);

        $config = Zend_Registry::get('config');
        $paymentProvider = new Local_Payment_PayPal_AdaptiveAccounts_Account($config->third_party->paypal);
        $userData = new Local_Payment_PayPal_UserData();
        $userData->generateFromArray($data);
        $result = $paymentProvider->verifyAccount($userData);

        return $result;
    }

}