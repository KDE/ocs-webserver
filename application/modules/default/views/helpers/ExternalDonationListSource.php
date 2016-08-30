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
class Default_View_Helper_ExternalDonationListSource extends Zend_View_Helper_Abstract
{

    /**
     * @param int $projectUuid
     * @param array $config
     * @return string
     */
    public function externalDonationListSource($projectUuid, $config)
    {
        $url = 'https://' . Zend_Controller_Front::getInstance()->getRequest()->getHttpHost() . '/donationlist/' . urlencode($projectUuid) . '/';
        if (isset($config['websiteAuthCode'])) {
            $websiteAuthCode = 'data-auth="' . $config['websiteAuthCode'] . '"';
        } else {
            $websiteAuthCode = '';
        }
        $htmlCode = '<iframe src="' . $url . '" height="200px" width="100%" frameBorder=0 scrolling="no" allowTransparency="false" seamless' . $websiteAuthCode . '></iframe>';
        return $htmlCode;
    }

}