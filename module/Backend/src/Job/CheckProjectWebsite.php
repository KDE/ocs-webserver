<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Backend\Job;


use Application\Job\Interfaces\JobInterface;

/**
 * Class CheckProjectWebsite
 * @package Backend\Job
 * @deprecated
 */
class CheckProjectWebsite implements JobInterface
{
   
    protected $project_id;
    protected $websiteUrl;
    protected $authCode;
    protected $websiteProject;

    public function setUp()
    {
        // Set up environment for this job
    }

    /**     
     * @param $args
     */
    public function perform($args)
    {
        var_export($args);        
        $this->project_id = $args['project_id'];                       
        $this->websiteUrl = $args['websiteUrl'];  
        $this->authCode = $args['authCode'];
        $this->websiteProject = $args['websiteProject'];        
        $this->doCommand();        
    }


    
    

    public function tearDown()
    {
        // Remove environment for this job
    }


    public function doCommand()
    {
        $websiteProject = $this->websiteProject;
        $verificationResult = $websiteProject->testForAuthCodeExist($this->websiteUrl, $this->authCode);
        $websiteProject->updateData($this->project_id, $verificationResult);
    }


}