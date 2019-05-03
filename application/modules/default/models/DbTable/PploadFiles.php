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
class Default_Model_DbTable_PploadFiles extends Local_Model_Table
{
    /** @var  Zend_Cache_Core */
    protected $cache;
    
    protected $_name = "ppload_files";

    protected $_keyColumnsForRow = array('id');

    protected $_key = 'id';

    


    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->cache = Zend_Registry::get('cache');
    }
    
    
    /**
     * @param int $projectId Description   
     * @return array
     */
    public function fetchFilesForProject($collection_id)
    {

        if(empty($collection_id)) {
            return null;
        }
        
        $sql = " select * 
                     from ppload.ppload_files f 
                     where f.collection_id = :collection_id     
                     order by f.created_timestamp desc               
                   ";        
        /*
        $sql = " select * 
                     ,
                     (select tag.tag_fullname from tag_object, tag where tag_type_id = 3 and tag_group_id = 8 and tag_object.tag_id = tag.tag_id and tag_object.is_deleted = 0
                     and tag_object_id = f.id ) packagename
                    ,
                    (select tag.tag_fullname from tag_object, tag where tag_type_id = 3 and tag_group_id = 9 and tag_object.tag_id = tag.tag_id and tag_object.is_deleted = 0
                    and tag_object_id = f.id ) archname

                     from ppload.ppload_files f 
                     where f.collection_id = :collection_id     
                     order by f.created_timestamp desc               
                   ";        
         * 
         */
        $result = $this->_db->query($sql,array('collection_id' => $collection_id))->fetchAll();      
        return $result;
    }        

    public function fetchFilesCntForProject($collection_id)
    {

        if(empty($collection_id)) {
            return 0;
        }
        
        $sql = " select  count(1) as cnt
                     from ppload.ppload_files f 
                     where f.collection_id = :collection_id and f.active = 1                  
                   ";        
        $result = $this->_db->query($sql,array('collection_id' => $collection_id))->fetchAll();      
        return $result[0]['cnt'];
    }     
    
    
    public function fetchCountDownloadsTodayForProject($collection_id)
    {
        if(empty($collection_id)) {
            return 0;
        }
        
        $today = (new DateTime())->modify('-1 day');
        $filterDownloadToday = $today->format("Y-m-d H:i:s");

        $sql = "    SELECT COUNT(1) AS cnt
                    FROM ppload.ppload_files_downloaded f
                    WHERE f.collection_id = " . $collection_id . " 
                    AND f.downloaded_timestamp >= '" . $filterDownloadToday . "'               
                   ";        
        $result = $this->_db->query($sql)->fetchAll();      
        return $result[0]['cnt'];
    }     
    
    
    public function fetchCountDownloadsForFileAllTime($collectionId, $file_id)
    {
        if(empty($file_id) || empty($collectionId)) {
            return 0;
        }
        
        $sql = "    SELECT count_dl AS cnt
                    FROM ppload.stat_ppload_files_downloaded f
                    WHERE f.collection_id = " . $collectionId . " 
                    AND f.file_id = " . $file_id . "
                   ";        
        $result = $this->_db->query($sql)->fetchAll();      
        return $result[0]['cnt'];
    }     

        public function fetchCountDownloadsForFileToday($collectionId, $file_id)
    {
        if(empty($file_id) || empty($collectionId)) {
            return 0;
        }
        
        $sql = "    SELECT COUNT(1) AS cnt
                    FROM ppload.ppload_files_downloaded f
                    WHERE f.collection_id = " . $collectionId . " 
                    AND f.file_id = " . $file_id . "
                    AND f.downloaded_timestamp >= DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:01')  
                   ";        
        $result = $this->_db->query($sql)->fetchAll();      
        return $result[0]['cnt'];
    }     

    
    private function fetchAllFiles($collection_id, $ignore_status = true, $activeFiles = false)
    {
        
        if(empty($collection_id)) {
            return null;
        }
        /*
        $sql = "    select  *
                     from ppload.ppload_files f 
                     where f.collection_id = :collection_id 
                   ";        
         * 
         */
        $sql = "    SELECT  f.*
                    , count_dl_totday.cnt AS count_dl_today
                    (SELECT count_dl AS cnt
                        FROM ppload.stat_ppload_files_downloaded f3
                        WHERE f3.collection_id = f.collection_id AND f3.file_id = f.id) AS count_dl_all

                    from ppload.ppload_files f 
                    LEFT JOIN (
                            SELECT COUNT(1) AS cnt, collection_id, file_id
                              FROM ppload.ppload_files_downloaded f2
                              WHERE f2.downloaded_timestamp >= DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:01') 
                              GROUP BY collection_id, file_id
                    ) count_dl_totday ON count_dl_totday.collection_id = f.collection_id AND count_dl_totday.file_id = f.id
                    where f.collection_id = :collection_id  
                    ";
        if($ignore_status == FALSE && $activeFiles == TRUE) {
           $sql .= " and f.active = 1";
        }
        if($ignore_status == FALSE && $activeFiles == FALSE) {
           $sql .= " and f.active = 0";
        }
        $result = $this->_db->query($sql,array('collection_id' => $collection_id, ))->fetchAll();      
        return $result;
    }

    /*
    * @$collection_ids array of ids
    */
    private function fetchAllFilesExtended($collection_ids, $ignore_status = true, $activeFiles = false)
    {
        
        if(empty($collection_ids) || sizeof($collection_ids)==0) {
            return null;
        }

        $sql = "    select  *
                     from ppload.ppload_files f 
                     where f.collection_id in (".implode(',',$collection_ids).") ";        

        if($ignore_status == FALSE && $activeFiles == TRUE) {
           $sql .= " and f.active = 1 ";
        }
        if($ignore_status == FALSE && $activeFiles == FALSE) {
           $sql .= " and f.active = 0 ";
        }

        $sql.="order by f.collection_id,f.created_timestamp desc ";

        $result = $this->_db->query($sql)->fetchAll();      
        return $result;
    }

    
    public function fetchAllFilesForProject($collection_id)
    {
        return $this->fetchAllFiles($collection_id, true);
    }   

    public function fetchAllFilesForCollection($collection_ids)
    {
        return $this->fetchAllFilesExtended($collection_ids, true);
    } 
    
    public function fetchAllActiveFilesForCollection($collection_ids)
    {
        return $this->fetchAllFilesExtended($collection_ids, false, true);
    } 
    
    public function fetchAllActiveFilesForProject($collection_id)
    {
        return $this->fetchAllFiles($collection_id, false, true);
    }   

    public function fetchAllInactiveFilesForProject($collection_id)
    {
        return $this->fetchAllFiles($collection_id, false, false);
    }   
    
}