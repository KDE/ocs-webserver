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
class Default_View_Helper_ExternalDonationList extends Zend_View_Helper_Abstract
{

    /**
     * @param int $project_id
     * @return string
     */
    public function externalDonationList($project_id)
    {
        $projectTable = new Default_Model_Project();
        $projectInfo = $projectTable->fetchProductInfo((int) $project_id);

        $plingTable = new Default_Model_Pling();
        $donations = $plingTable->getDonationsForProject((int) $project_id);

        $authCode = '';
        if ($projectInfo->link_1) {
            $websiteOwner = new Local_Verification_WebsiteProject();
            $authCode = '<meta name="ocs-site-verification" content="' . $websiteOwner->generateAuthCode(stripslashes($projectInfo->link_1)) . '" />';
        }

        return $this->view->partial('donationlist/partial/donationlist.phtml', array('project' => $projectInfo, 'donations' => $donations, 'authCode' => $authCode));
    }

}