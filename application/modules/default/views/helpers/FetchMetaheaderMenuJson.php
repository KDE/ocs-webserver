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

class Default_View_Helper_FetchMetaheaderMenuJson extends Zend_View_Helper_Abstract
{
   
    public function fetchMetaheaderMenuJson()
    {        

       $sname = Zend_Registry::get('store_host');
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');        
        $cacheName = __FUNCTION__ . md5($sname);       

        if (false == ($domainobjects = $cache->load($cacheName))) {
            $tbl = new Default_Model_DbTable_ConfigStore();
            $result = $tbl->fetchDomainObjects();
                // sort Desktop manuelly to the front
            $arrayDesktop = array();
            $arrayRest =  array();  
           
            foreach ($result as $obj) {
                $o =  $obj['order'];   
                $curOrder = floor($obj['order']/1000);      
                if($curOrder<10 or $curOrder>50) continue;
                $obj['calcOrder'] = $curOrder;              

                $tmp = array();
                $tmp['order'] = $obj['order'];
                $tmp['calcOrder'] = $obj['calcOrder'];
                $tmp['host'] = $obj['host'];
                $tmp['name'] = $obj['name'];                

                if($curOrder==30) {
                    // Desktop set calcOrder = 9 manuelly put desktop in front                    
                    $tmp['calcOrder'] = 9;
                    $arrayDesktop[] = $tmp;    
                }else{
                    $arrayRest[] = $tmp;    
                }                        
            }
            $domainobjects = array_merge($arrayDesktop, $arrayRest);

            
            $baseurl = Zend_Registry::get('config')->settings->client->default->baseurl;
            // set group name manully
            foreach ($domainobjects as &$obj) {

                    if($sname == $obj['host']){
                        $obj['menuactive'] = 1;
                    }else{
                        $obj['menuactive'] = 0;
                    }

                    $order =  $obj['order'];
                     // z.b 150001 ende ==1 go real link otherwise /s/$name
                    $last_char_check = substr($order, -1);
                    if($last_char_check=='1')
                    {
                        $obj['menuhref'] = $obj['host'];
                    }else{
                        $obj['menuhref'] = $baseurl.'/s/'.$obj['name'];
                    }

                    switch ($obj['calcOrder']) {
                        case 9:
                            $obj['menugroup']='Desktops';
                            break;
                        case 10:
                            $obj['menugroup']='Applications';
                            break;
                        case 20:
                            $obj['menugroup']='Addons';
                            break;
                        case 40:
                            $obj['menugroup']='Artwork';
                            break;                       
                        case 50:
                        $obj['menugroup']='Other';
                        break;
                    }
                         
            }

            $cache->save($domainobjects, $cacheName, array(), 28800);
        }
        return  Zend_Json::encode($domainobjects);
    }

}