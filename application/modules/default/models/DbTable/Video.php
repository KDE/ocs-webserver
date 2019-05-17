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
class Default_Model_DbTable_Video extends Zend_Db_Table_Abstract
{
    protected $_name = "ppload.ppload_files";
    public static $VIDE_FILE_TYPES = array('video/3gpp','video/3gpp2','video/mpeg','video/quicktime','video/x-flv','video/webm','application/ogg','video/x-ms-asf','video/x-matroska');

    protected $_allowed = array(
        'video/3gpp'        => '.3gp',
        'video/3gpp2'       => '.3g2',
        'video/mpeg'        => '.mpeg',
        'video/quicktime'   => '.mov',
        'video/x-flv'       => '.flv',
        'video/webm'        => '.webm',
        'application/ogg'   => '.ogv',
        'video/x-matroska'  => '.mkv',
        'video/mp4'         => '.mp4'
    );
    protected $_allowedFileExtension = array(
        '3gp',
        '3g2',
        'mpeg',
        'mov',
        'flv',
        'webm',
        'ogv',
        'mkv',
        'mp4'
    );
    protected $_errorMsg = null;
    
    public function storeExternalVideo($collectionId, $url, $fileExtension = null)
    {
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));
        $data = null;
        try {
            set_time_limit(0);

            // File to save the contents to
            $fp = fopen ('files2.tar', 'w+');
            $config = Zend_Registry::get('config');
            $videourl = $config->videos->media->upload . "?url=".urlencode($url)."&colelction_id=".$collectionId;

            // Here is the file we are downloading, replace spaces with %20
            $ch = curl_init(str_replace(" ","%20",$videourl));

            curl_setopt($ch, CURLOPT_TIMEOUT, 50);

            // give curl the file pointer so that it can write to it
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $data = curl_exec($ch);//get curl response

            //done
            curl_close($ch);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        Zend_Registry::get('logger')->debug(__METHOD__ . ' Result: ' . print_r($data, true));

        return $data;
    }

}