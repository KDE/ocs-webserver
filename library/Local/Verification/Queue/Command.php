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

class Local_Verification_Queue_Command implements Local_Queue_CommandInterface
{

    private $memberId;
    private $websiteUrl;
    private $authCode;

    function __construct($memberId, $websiteUrl, $authCode)
    {
        if (empty($memberId)) {
            throw new Exception('The memberId is necessary');
        }
        $this->memberId = $memberId;
        $this->websiteUrl = $websiteUrl;
        $this->authCode = $authCode;
    }

    public function doCommand()
    {
        $websiteOwner = new Local_Verification_WebsiteOwner();
        $verificationResult = $websiteOwner->validateAuthCode($this->websiteUrl, $this->memberId);
        $websiteOwner->updateData($this->memberId, $verificationResult);
    }

    public function getWebsiteUrl()
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl($websiteUrl)
    {
        $this->websiteUrl = $websiteUrl;
    }

    public function getMemberId()
    {
        return $this->memberId;
    }

    public function setMemberId($memberId)
    {
        $this->memberId = $memberId;
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