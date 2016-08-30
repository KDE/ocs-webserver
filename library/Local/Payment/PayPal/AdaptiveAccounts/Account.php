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

class Local_Payment_PayPal_AdaptiveAccounts_Account
    extends Local_Payment_Paypal_Base
    implements Local_Payment_AccountInterface
{

    const SUCCESS = 'Success';
    const VERIFIED = 'VERIFIED';
    const RESPONSE_ENVELOPE_ACK = 'responseEnvelope_ack';
    const ACCOUNT_STATUS = 'accountStatus';

    const API_ADAPTIVE_ACCOUNTS = 'AdaptiveAccounts';

    const OPERATION_GET_VERIFIED_STATUS = 'GetVerifiedStatus';


    protected $_ipnNotificationUrl;
    protected $_cancelUrl;
    protected $_returnUrl;

    /** @var  \Local_Payment_PayPal_UserData */
    protected $_paymentUserData;
    protected $_dataIpn;


    /**
     * @param Local_Payment_UserDataInterface $userData
     * @param string $matchCriteria
     * @return bool
     */
    public function verifyAccount($userData, $matchCriteria = 'NAME')
    {
        $requestParameters = array(
            'emailAddress' => $userData->getPaymentUserId(),
            'firstName' => $userData->getFirstName(),
            'lastName' => $userData->getLastName(),
            'matchCriteria' => $matchCriteria
        );

        $response = $this->_makeRequest($requestParameters, self::API_ADAPTIVE_ACCOUNTS, self::OPERATION_GET_VERIFIED_STATUS);

        return $response->isRequestSuccessful() AND $response->isVerifiedAccount();
    }

    /**
     * @param Local_Payment_UserDataInterface $userData
     * @throws Exception
     */
    public function setUserDataStore($userData)
    {
        if (false === ($userData instanceof Local_Payment_UserDataInterface)) {
            throw new Exception('Wrong data type for user data');
        }
        $this->_paymentUserData = $userData;
    }

    /**
     * @return Local_Payment_PayPal_UserData
     */
    public function getUserDataStore()
    {
        return $this->_paymentUserData;
    }

}