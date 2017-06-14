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
class Default_View_Helper_ExternalWidget extends Zend_View_Helper_Abstract
{

    /**
     * @param int $productUuid
     * @return string
     */
    public function externalWidget($productUuid)
    {
        $projectModel = new Default_Model_Project();
        $productRow = $projectModel->fetchRow(array('uuid = ?' => $productUuid));

        $plingModel = new Default_Model_DbTable_Plings();
        $supporters = $plingModel->getProjectSupporters($productRow->project_id, 8, true);
        $nrOfSupporters = $plingModel->getCountSupporters($productRow->project_id);
        $websiteOwner = new Local_Verification_WebsiteProject();
        $authCode = '<meta name="ocs-site-verification" content="' . $websiteOwner->generateAuthCode(stripslashes($productRow->link_1)) . '" />';

        return $this->view->partial('supporterbox/partial/supporterbox.phtml', array('product' => $productRow, 'supporters' => $supporters, 'nrOfSupporters' => $nrOfSupporters, 'authCode' => $authCode));
    }

}