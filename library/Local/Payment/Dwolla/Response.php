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

class Local_Payment_Dwolla_Response
{

    /**
     * @param array|null $rawResponse
     * @throws Local_Payment_Exception
     * @return Local_Payment_Dwolla_ResponseInterface
     */
    public static function buildResponse($rawResponse = null)
    {
        if (isset($rawResponse['CheckoutId']) AND isset($rawResponse['TransactionId'])) {
            return new Local_Payment_Dwolla_ResponseGateway($rawResponse);
        }

        if (isset($rawResponse['Type']) AND ($rawResponse['Type'] == 'Transaction') AND isset($rawResponse['Subtype']) AND ($rawResponse['Subtype'] == 'Status')) {
            return new Local_Payment_Dwolla_ResponseTransactionStatus($rawResponse);
        }

        if (isset($rawResponse['Type']) AND ($rawResponse['Type'] == 'Transaction') AND isset($rawResponse['Subtype']) AND ($rawResponse['Subtype'] == 'Returned')) {
            return new Local_Payment_Dwolla_ResponseTransactionStatus($rawResponse);
        }

        throw new Local_Payment_Exception('Unknown response from Dwolla. Raw message:' . print_r($rawResponse, true) . "\n");
    }

}