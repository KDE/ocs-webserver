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
class Default_View_Helper_BuildDownloadLink extends Zend_View_Helper_Abstract
{


    /**     
     * @param $file pploadFile
     * data-link_type: install/download
     */
   public function shortFilename($filename) {
                    $returnString = $filename;


                    if(strlen($filename)> 25) {
                         $name = substr($filename,0,22);

                         $temparray = explode(".",$filename);
                         
                         $fileExt = end($temparray);
                        
                        if(strlen($fileExt)> 3) {
                            $name = substr($name,0,(25-strlen($fileExt)));
                        }
                        $returnString = $name .'...'.$fileExt;
                    }
                    return $returnString;
    }

    public function buildDownloadLink($file,$project,$dataLinkType)
    {

        $link = null;
        $isExternLink = false;
        if($file['tags'])
        {
            $tags = explode(',', $file['tags']);        
            foreach ($tags as $t) {
               $tagStr =  explode('##',$t);
               if(sizeof($tagStr)==2 && $tagStr[0]=='link')
               {
                $link = $tagStr[1];
               }
            }
        }

        
        if($link)
        {
            $isExternLink = true;
        }
             
        $downloadUrl = "https://".$_SERVER["SERVER_NAME"]."/p/".$project->project_id
                        ."/startdownload?file_id=".$file['id']
                        ."&file_name=".$file['name']
                        ."&file_type=".$file['type']
                        ."&file_size=".$file['size'];

        $truncateHelper = new Default_View_Helper_Truncate();
        $filesizeHelper = new Default_View_Helper_HumanFilesize();

        $fileShortName = $this->shortFilename($file['name']);
        // $fileShortName = $file['name'];
        $downloadLink = '<a href="'.$downloadUrl.'" id="data-link-dl'.$file['id'].'" class="opendownloadfile" data-file_id="'.$file['id']
            .'" data-username="'.$project->username 
            .'" data-file_name="'.$file['name'] 
            .'" data-file_type="'.$file['type']
            .'" data-file_size="'.$file['size']
            .'" data-project_id="'.$project->project_id
            .'" data-link_type="'.$dataLinkType
            .'" data-is-external-link="'
            .$isExternLink
            .'">'
            .'<span id="'.$dataLinkType.'"-link-filename'.$file['id'].'" class="downloadlink '.($isExternLink?'isExternal':'').'" >'
            .$fileShortName.'</span><span class="downloadlink filesize">'.$filesizeHelper->humanFilesize($file['size'])
            .'</span>'
            .'</a>';
        return $downloadLink;
        
    }
}