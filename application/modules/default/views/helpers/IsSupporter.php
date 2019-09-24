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

class Default_View_Helper_IsSupporter extends Zend_View_Helper_Abstract
{

    public function isSupporter($member_id)
    {

    	$cache = Zend_Registry::get('cache');
    	$cacheName = __FUNCTION__ . '_' . md5($member_id);

    	if (false !== ($issupporter = $cache->load($cacheName))) {
    	        return $issupporter;
    	}

    	$tableMembers = new Default_Model_Member();
    	//$row = $tableMembers->fetchSupporterDonationInfo($member_id);
        $row = $tableMembers->fetchSupporterSectionInfo($member_id);
        if($row==null)
        {
            $cache->save(false, $cacheName, array(), 3600);
            return false;
        }else{                
            $sections=explode(",", $row['sections']);
            $cache->save(sizeof($sections), $cacheName, array(), 3600);
            return sizeof($sections);
        }        	        	
    }

} 