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

class Default_View_Helper_FetchStoresForCatTreeJson extends Zend_View_Helper_Abstract
{
   
    public function fetchStoresForCatTreeJson()
    {        

       $sname = Zend_Registry::get('store_host');
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');        
        $cacheName = __FUNCTION__ . md5($sname).'_new';       

        if (false == ($domainobjects = $cache->load($cacheName))) {
            $tbl = new Default_Model_DbTable_ConfigStore();
            $result = $tbl->fetchDomainObjects();
                // sort Desktop manuelly to the front
            $arrayDesktop = array();
            $arrayRest =  array();  
           
            foreach ($result as $obj) {
                $tmp = array();
                $tmp['order'] = $obj['order'];
                $tmp['host'] = $obj['host'];
                $tmp['name'] = $obj['name'];
                $tmp['is_show_in_menu'] = $obj['is_show_in_menu'];
                $tmp['is_show_real_domain_as_url'] = $obj['is_show_real_domain_as_url']; 

                $arrayRest[] = $tmp;    
            }
            $domainobjects = array_merge($arrayDesktop, $arrayRest);

            
            $baseurl = Zend_Registry::get('config')->settings->client->default->baseurl_store;
            // set group name manully
            foreach ($domainobjects as &$obj) {

                    if($sname == $obj['host']){
                        $obj['menuactive'] = 1;
                    }else{
                        $obj['menuactive'] = 0;
                    }

                    $domainAsUrl = $obj['is_show_real_domain_as_url'];
                    if($domainAsUrl)
                    {
                        $obj['menuhref'] = $obj['host'];
                    }else{
                        $obj['menuhref'] = $baseurl.'/s/'.$obj['name'];
                    }
            }

            $cache->save($domainobjects, $cacheName, array(), 3600);
        }
        return  Zend_Json::encode($domainobjects);
    }

}