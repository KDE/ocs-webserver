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

class Default_View_Helper_ExternalPlingButtonSource extends Zend_View_Helper_Abstract
{

    /**
     * @param int $projectId
     * @param array $config
     * @return string
     */
    public function externalPlingButtonSource($projectId, $config = null)
    {
        $url = 'https://' . Zend_Controller_Front::getInstance()->getRequest()->getHttpHost() . '/button/' . urlencode($projectId) . '/';
        if (isset($config['websiteAuthCode'])) {
            $websiteAuthCode = 'data-auth="' . $config['websiteAuthCode'] . '"';
        } else {
            $websiteAuthCode = '';
        }
        $htmlCode = '<iframe src="' . $url . '" width="100%" height="60px" frameBorder=0 scrolling="no" allowTransparency="true" seamless ' . $websiteAuthCode . '></iframe>';
        return $htmlCode;
    }

}