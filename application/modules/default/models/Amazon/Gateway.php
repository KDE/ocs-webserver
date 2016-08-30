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

class Default_Model_Amazon_Gateway extends Local_Payment_Amazon_FlexiblePayment_Gateway
{

    protected $amount;
    protected $userData;
    protected $paymentId;

    public function requestPayment($amount, $userData)
    {
        $this->amount = $amount;
        $this->userData = $userData;

        /** @var Local_Payment_Amazon_FlexiblePayment_Response $response */
        $response = parent::requestPayment($amount, $userData);

        $this->paymentId = Local_Tools_UUID::generateUUID();

        $response->setPaymentKey($this->paymentId);

        return $response;

    }


    /**
     * @return string
     */
    public function getCheckoutEndpoint()
    {

        $pipeline = new Amazon_FPS_CBUISingleUsePipeline($this->_config->consumer->access_key, $this->_config->consumer->access_secret);

        $pipeline->setMandatoryParameters(
            "callerReferenceSingleUse",
            $this->_returnUrl,
            $this->amount
        );

        //optional parameters
        $pipeline->addParameter("currencyCode", "USD");
        $pipeline->addParameter("paymentReason", 'Thank you for supporting: ' . $this->userData->getProductTitle());

        //SingleUse url
        print "Sample CBUI url for SingleUse pipeline : " . $pipeline->getUrl() . "\n";

    }

}