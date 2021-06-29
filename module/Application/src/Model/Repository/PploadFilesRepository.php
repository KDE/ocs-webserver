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

namespace Application\Model\Repository;

use Application\Model\Entity\PploadFiles;
use Application\Model\Interfaces\PploadFilesInterface;
use DateTime;
use Laminas\Db\Adapter\AdapterInterface;


class PploadFilesRepository extends BaseRepository implements PploadFilesInterface
{
    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "paypal_ipn";
        $this->_key = "id";
        $this->_prototype = PploadFiles::class;
    }

    /**
     * @param $collection_id
     *
     * @return array
     */
    public function fetchFilesForProject($collection_id)
    {

        if (empty($collection_id)) {
            return null;
        }

        $sql = " SELECT `f`.*, `pf`.`id` AS `ppload_file_preview_id`, `pf`.`url_preview`, `pf`.`url_thumb` 
                     FROM `ppload`.`ppload_files` `f` 
                     LEFT JOIN `ppload`.`ppload_file_preview` `pf` ON `pf`.`collection_id` = `f`.`collection_id` AND `pf`.`file_id` = `f`.`id`
                     WHERE `f`.`collection_id` = :collection_id     
                     ORDER BY `f`.`created_timestamp` DESC               
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

        return $this->fetchAll($sql, array('collection_id' => $collection_id));
    }

    public function fetchFilesCntForProject($collection_id)
    {

        if (empty($collection_id)) {
            return 0;
        }

        $sql = " SELECT  count(1) AS `cnt`
                     FROM `ppload`.`ppload_files` `f` 
                     WHERE `f`.`collection_id` = :collection_id AND `f`.`active` = 1                  
                   ";
        $result = $this->fetchRow($sql, array('collection_id' => $collection_id));

        return $result['cnt'];
    }

    public function fetchCountDownloadsTodayForProject($collection_id)
    {
        if (empty($collection_id)) {
            return 0;
        }
        // $today = (new DateTime())->modify('-1 day');
        // $filterDownloadToday = $today->format("Y-m-d H:i:s");

        // $sql = "    SELECT COUNT(1) AS cnt
        //             FROM ppload.ppload_files_downloaded f
        //             WHERE f.collection_id = " . $collection_id . " 
        //             AND f.downloaded_timestamp >= '" . $filterDownloadToday . "'               
        //             ";

        $sql = "    SELECT COUNT(1) AS cnt
        FROM ppload.ppload_files_downloaded f
        WHERE f.collection_id = " . $collection_id . " 
        AND f.downloaded_timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)               
        ";
        $result = $this->fetchRow($sql);
        return $result['cnt'];
    }

    public function fetchCountDownloadsTodayForProjectNew($collection_id)
    {
        if (empty($collection_id)) {
            return 0;
        }

        $today = (new DateTime())->modify('-1 day');
        $filterDownloadToday = $today->format("Y-m-d H:i:s");

        $sql = "    SELECT COUNT(1) AS cnt
                    FROM ppload.ppload_files_downloaded_unique f
                    WHERE f.collection_id = " . $collection_id . " 
                    AND f.downloaded_timestamp >= '" . $filterDownloadToday . "'               
                    ";
        $result = $this->fetchRow($sql);

        return $result['cnt'];
    }

    public function fetchCountDownloadsForFileAllTime($collectionId, $file_id)
    {
        if (empty($file_id) || empty($collectionId)) {
            return 0;
        }

        $sql = "    SELECT count_dl AS cnt
                    FROM ppload.stat_ppload_files_downloaded f
                    WHERE f.collection_id = " . $collectionId . " 
                    AND f.file_id = " . $file_id . "
                   ";
        $result = $this->fetchRow($sql);

        return $result['cnt'];
    }

    public function fetchCountDownloadsForFileToday($collectionId, $file_id)
    {
        if (empty($file_id) || empty($collectionId)) {
            return 0;
        }

        $sql = "    SELECT COUNT(1) AS cnt
                    FROM ppload.ppload_files_downloaded f
                    WHERE f.collection_id = " . $collectionId . " 
                    AND f.file_id = " . $file_id . "
                    AND f.downloaded_timestamp >= DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:01')  
                   ";
        $result = $this->fetchRow($sql);

        return $result['cnt'];
    }

    public function fetchCountDownloadsForFileTodayNew($collectionId, $file_id)
    {
        if (empty($file_id) || empty($collectionId)) {
            return 0;
        }

        $sql = "    SELECT COUNT(1) AS cnt
                    FROM ppload.ppload_files_downloaded_unique f
                    WHERE f.collection_id = " . $collectionId . " 
                    AND f.file_id = " . $file_id . "
                    AND f.downloaded_timestamp >= DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:01')  
                   ";
        $result = $this->fetchRow($sql);

        return $result['cnt'];
    }

    public function fetchAllFilesForProject($collection_id, $isForAdmin = false)
    {
        return $this->fetchAllFiles($collection_id, true, false, $isForAdmin);
    }

    /*
    * @$collection_ids array of ids
    */

    private function fetchAllFiles($collection_id, $ignore_status = true, $activeFiles = false, $forAdmin = false)
    {

        if (empty($collection_id)) {
            return null;
        }
        /*
        $sql = "    select  *
                     from ppload.ppload_files f
                     where f.collection_id = :collection_id
                   ";
         *
         */

        //Admin Select with extended data
        $sqlAdmin = "SELECT  f.*
                    , `pf`.`id` AS `ppload_file_preview_id`
                    , `pf`.`url_preview`
                    , `pf`.`url_thumb`
                    , 0 AS count_dl_today
                    , count_dl_uk_today.cnt AS count_dl_uk_today
                    ,0 AS count_dl_all
                    ,(SELECT count_dl AS cnt
                        FROM ppload.stat_ppload_files_downloaded_nounique f4
                        WHERE f4.collection_id = f.collection_id AND f4.file_id = f.id) AS count_dl_all_nouk
                    ,(SELECT count_dl AS cnt
                        FROM ppload.stat_ppload_files_downloaded_unique f3
                        WHERE f3.collection_id = f.collection_id AND f3.file_id = f.id) AS count_dl_all_uk

                    from ppload.ppload_files f 
                    LEFT JOIN `ppload`.`ppload_file_preview` `pf` ON `pf`.`collection_id` = `f`.`collection_id` AND `pf`.`file_id` = `f`.`id`
                    LEFT JOIN (
                            SELECT COUNT(1) AS cnt, collection_id, file_id
                              FROM ppload.ppload_files_downloaded_unique f2
                              WHERE f2.downloaded_timestamp >= DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:01') 
                              GROUP BY collection_id, file_id
                    ) count_dl_uk_today ON count_dl_uk_today.collection_id = f.collection_id AND count_dl_uk_today.file_id = f.id
                    where f.collection_id = :collection_id  
                    ";
        $sqlNormal = "SELECT  `f`.*
                    , `pf`.`id` AS `ppload_file_preview_id`
                    , `pf`.`url_preview`
                    , `pf`.`url_thumb` 
                    , 0 AS `count_dl_today`
                    , `count_dl_uk_today`.`cnt` AS `count_dl_uk_today`
                    ,0 AS `count_dl_all`
                    ,(SELECT `count_dl` AS `cnt`
                        FROM `ppload`.`stat_ppload_files_downloaded_nounique` `f4`
                        WHERE `f4`.`collection_id` = `f`.`collection_id` AND `f4`.`file_id` = `f`.`id`) AS `count_dl_all_nouk`
                    ,(SELECT `count_dl` AS `cnt`
                        FROM `ppload`.`stat_ppload_files_downloaded_unique` `f3`
                        WHERE `f3`.`collection_id` = `f`.`collection_id` AND `f3`.`file_id` = `f`.`id`) AS `count_dl_all_uk`
                    FROM `ppload`.`ppload_files` `f` 
                    LEFT JOIN `ppload`.`ppload_file_preview` `pf` ON `pf`.`collection_id` = `f`.`collection_id` AND `pf`.`file_id` = `f`.`id`
                    LEFT JOIN (
                            SELECT COUNT(1) AS `cnt`, `collection_id`, `file_id`
                              FROM `ppload`.`ppload_files_downloaded_unique` `f2`
                              WHERE `f2`.`downloaded_timestamp` >= DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:01') 
                              GROUP BY `collection_id`, `file_id`
                    ) `count_dl_uk_today` ON `count_dl_uk_today`.`collection_id` = `f`.`collection_id` AND `count_dl_uk_today`.`file_id` = `f`.`id`
                    WHERE `f`.`collection_id` = :collection_id  
                    ";

        $sql = $sqlNormal;
        if ($forAdmin == true) {
            $sql = $sqlAdmin;
        }
        if ($ignore_status == false && $activeFiles == true) {
            $sql .= " and f.active = 1";
        }
        if ($ignore_status == false && $activeFiles == false) {
            $sql .= " and f.active = 0";
        }

        return $this->fetchAll($sql, array('collection_id' => $collection_id,));
    }

    public function fetchAllFilesForCollection($collection_ids)
    {
        return $this->fetchAllFilesExtended($collection_ids, true);
    }

    private function fetchAllFilesExtended($collection_ids, $ignore_status = true, $activeFiles = false)
    {

        if (empty($collection_ids) || sizeof($collection_ids) == 0) {
            return null;
        }

        $sql = "    select  *
                     from ppload.ppload_files f 
                     where f.collection_id in (" . implode(',', $collection_ids) . ") ";

        if ($ignore_status == false && $activeFiles == true) {
            $sql .= " and f.active = 1 ";
        }
        if ($ignore_status == false && $activeFiles == false) {
            $sql .= " and f.active = 0 ";
        }

        $sql .= "order by f.collection_id,f.created_timestamp desc ";

        return $this->fetchAll($sql);
    }

    public function fetchAllActiveFilesForCollection($collection_ids)
    {
        return $this->fetchAllFilesExtended($collection_ids, false, true);
    }

    public function fetchAllActiveFilesForProject($collection_id, $isForAdmin = false)
    {
        return $this->fetchAllFiles($collection_id, false, true, $isForAdmin);
    }

    public function fetchAllInactiveFilesForProject($collection_id, $isForAdmin = false)
    {
        return $this->fetchAllFiles($collection_id, false, false, $isForAdmin);
    }   
}
