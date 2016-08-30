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

class Local_Verification_Queue_Command_WebsiteAuthCodeExist implements Local_Queue_CommandInterface
{

    /** @var \Local_Db_Table_Row_ValidateInterface */
    private $productData;
    /** @var  string */
    private $websiteUrl;
    /** @var  string */
    private $authCode;

    /**
     * @param Local_Db_Table_Row_ValidateInterface $productData
     * @param $websiteUrl
     * @param $authCode
     * @throws Exception
     */
    function __construct($productData, $websiteUrl, $authCode)
    {
        if (empty($productData)) {
            throw new Exception(__FILE__ . '(' . __LINE__ . '): ' . 'The productData is necessary');
        }
        $this->productData = $productData;
        $this->websiteUrl = $websiteUrl;
        $this->authCode = $authCode;
    }

    public function doCommand()
    {
        $websiteValidation = new Local_Verification_WebsiteAuthCodeExist();
        $verificationResult = $websiteValidation->testForAuthCodeExist($this->websiteUrl, $this->authCode);
        $this->productData->setVerifiedStatus($verificationResult);
    }

    /**
     * @param $productId
     * @param $verificationResult
     *
     */
    public function updateProductData($productId, $verificationResult)
    {
        $memberTable = new Default_Model_Product();
        /** @var Zend_Db_Table_Row $rowMember */
        $rowMember = $memberTable->find($productId)->current();
        $rowMember->validated_at = new Zend_Db_Expr('NOW()');
        $rowMember->validated = $verificationResult;
        $rowMember->save();
    }

    public function getWebsiteUrl()
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl($websiteUrl)
    {
        $this->websiteUrl = $websiteUrl;
    }

    public function getProductData()
    {
        return $this->productData;
    }

    public function setProductData($memberId)
    {
        $this->productData = $memberId;
    }

    public function getAuthCode()
    {
        return $this->authCode;
    }

    public function setAuthCode($authCode)
    {
        $this->authCode = $authCode;
    }

}