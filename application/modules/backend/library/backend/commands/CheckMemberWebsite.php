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
 *
 * Created: 13.06.2017
 */
class Backend_Commands_CheckMemberWebsite implements Local_Queue_CommandInterface
{
    protected $member_id;
    protected $website;
    protected $authCode;

    /**
     * Backend_Commands_CheckMemberWebsite constructor.
     *
     * @param             $_memberId
     * @param             $link_website
     * @param null|string $authCode
     */
    public function __construct($_memberId, $link_website, $authCode)
    {
        $this->member_id = (int)$_memberId;
        $this->website = $link_website;
        $this->authCode = $authCode;
    }

    public function doCommand()
    {
        $websiteOwner = new Local_Verification_WebsiteOwner();
        $verificationResult = $websiteOwner->testForAuthCodeExist($this->website, $this->authCode);
        $websiteOwner->updateData($this->member_id, $verificationResult);
    }

}