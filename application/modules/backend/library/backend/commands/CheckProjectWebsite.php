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
class Backend_Commands_CheckProjectWebsite implements Local_Queue_CommandInterface
{
    protected $project_id;
    protected $websiteUrl;
    protected $authCode;

    /**
     * Backend_Commands_CheckWebsiteAuthCode constructor.
     *
     * @param             $project_id
     * @param             $link_1
     * @param null|string $authCode
     */
    public function __construct($project_id, $link_1, $authCode)
    {
        $this->project_id = (int)$project_id;
        $this->websiteUrl = $link_1;
        $this->authCode = $authCode;
    }

    public function doCommand()
    {
        $websiteProject = new Local_Verification_WebsiteProject();
        $verificationResult = $websiteProject->testForAuthCodeExist($this->websiteUrl, $this->authCode);
        $websiteProject->updateData($this->project_id, $verificationResult);
    }

}