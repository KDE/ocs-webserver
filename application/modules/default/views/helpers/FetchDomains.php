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

class Default_View_Helper_FetchDomains extends Zend_View_Helper_Abstract
{

    public function fetchDomainObjects()
    {
        $tbl = new Default_Model_DbTable_ConfigStore();
        $result = $tbl->fetchDomainObjects();

        /*
        foreach ($result as &$obj) {
            $clientFileId = '_' . $obj['config_id_name'];;
            $clientConfigPath = Zend_Registry::get('config')->settings->client->config->path;
            $clientConfigFileName = "client{$clientFileId}.ini.php";

            $clientConfigData = null;
            if (file_exists($clientConfigPath . $clientConfigFileName)) {
                $clientConfigData = require $clientConfigPath . $clientConfigFileName;
            } else {
                $clientConfigData = require $clientConfigPath . "default.ini.php";
            }

            // $obj['meta_keywords'] = $clientConfigData['head']['meta_keywords'];
            $obj['meta_keywords'] = $clientConfigData['footer_heading'];

        }
    */
            // sort Desktop in front
        $arrayDesktop = array();
        $arrayRest =  array();        
        foreach ($result as $obj) {
            $o =  $obj['order'];   
            $curOrder = floor($obj['order']/1000);      
             if($curOrder<10 or $curOrder>50) continue;
             $obj['calcOrder'] = $curOrder;
             if($curOrder==30) {
                // Desktop set calcOrder = 9 manuelly put desktop in front
                $obj['calcOrder'] = 9;
                $arrayDesktop[] = $obj;    
             }else{
                $arrayRest[] = $obj;    
             }                        
        }

        return array_merge($arrayDesktop, $arrayRest);
    }

}