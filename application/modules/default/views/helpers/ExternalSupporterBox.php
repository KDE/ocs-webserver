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
class Default_View_Helper_ExternalSupporterBox extends Zend_View_Helper_Abstract
{

    /**
     * @param int $project_id
     * @return string
     */
    public function externalSupporterBox($project_id)
    {
        $projectModel = new Default_Model_Project();
        $this->view->product = $productRow = $projectModel->fetchRow(array('project_id = ?' => $project_id));

        $plingModel = new Default_Model_DbTable_Plings();
        $this->view->supporters = $plingModel->getProjectSupporters($productRow->project_id, 8, true);
        $this->view->nrOfSupporters = $plingModel->getCountSupporters($productRow->project_id);

        $this->view->authCode = '';
        if ($productRow->link_1) {
            $websiteOwner = new Local_Verification_WebsiteAuthCodeExist();
            $this->view->authCode = '<meta name="ocs-site-verification" content="' . $websiteOwner->generateAuthCode(stripslashes($productRow->link_1)) . '" />';
        }

        return $this->view->render('supporterbox/partial/supporterbox.phtml');
    }

}