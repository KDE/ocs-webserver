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

class Default_View_Helper_GetCurrentMonthPlings extends Zend_View_Helper_Abstract
{  	

    public function getCurrentMonthPlings($ppload_collection_id,$project_category_id)
    {
        
         $pploadApi = new Ppload_Api(array(
             'apiUri'   => PPLOAD_API_URI,
             'clientId' => PPLOAD_CLIENT_ID,
             'secret'   => PPLOAD_SECRET
         ));
        $dcnt = 0;
        if ($ppload_collection_id)
        {
             $filesRequest = array(
                 'collection_id' => $ppload_collection_id,
                  'perpage'       => 100,
                  'downloaded_timeperiod_begin' => date('Y-m-01') 
             );

             $filesResponse = $pploadApi->getFiles($filesRequest);

             if (isset($filesResponse->status)  && $filesResponse->status == 'success') {
                 $i=0;
                
                 foreach ($filesResponse->files as $file) {                     
                     $dcnt = $dcnt +$file->downloaded_timeperiod_count;
                 }
             }
         }

         $pc = new Default_Model_DbTable_ProjectCategory();
         $cat = $pc->fetchElement($project_category_id);
         $plingfactor = $cat['dl_pling_factor'];
        return $dcnt*$plingfactor*0.01;
    }

} 