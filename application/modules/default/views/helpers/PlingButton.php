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

class Default_View_Helper_PlingButton extends Zend_View_Helper_Abstract
{

    /**
     * @param int $projectId
     * @param array $config
     * @return string
     */
    public function plingButton($projectId, $config = null)
    {
        $target = '';
        if (isset($config['target'])) {
            $target = ' target = ' . $config['target'];
        }
        $size = 'large';
        if (isset($config['size'])) {
            $size = $config['size'];
        }
        $url = $this->getPlingUrl($projectId);
        $countPlings = $this->getPlingsForProject($projectId);

        $htmlCode = '<a href="' . $url . '" class="plingbutton ' . $size . '"' . $target . '><span><span>' . $countPlings . '</span></span></a>';

        if (isset($config['attribute']) AND $config['attribute'] == 'disabled') {
            $htmlCode = '<button class="plingbutton large" disabled>
                    	<span>
							<span>' . $this->view->translate('pling.button.no_paypal') . '</span>
						</span>
                    </button>';
        }

        return $htmlCode;
    }

    /**
     * @param $projectId
     * @return string
     */
    protected function getPlingUrl($projectId)
    {
        $helpProductUrl = new Default_View_Helper_BuildProductUrl();
        return $helpProductUrl->buildProductUrl($projectId, 'pling');
    }

    /**
     * @param $projectId
     * @return double
     */
    protected function getPlingsForProject($projectId)
    {
        $plingTable = new Default_Model_DbTable_Plings();
        $countPlings = $plingTable->getAmountPlingsForProject($projectId);
        return $countPlings;
    }

}