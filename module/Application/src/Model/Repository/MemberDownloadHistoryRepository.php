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

use Application\Model\Entity\MemberDownloadHistory;
use Application\Model\Interfaces\MemberDownloadHistoryInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSetInterface;


class MemberDownloadHistoryRepository extends BaseRepository implements MemberDownloadHistoryInterface
{
    private $cache;

    public function __construct(
        AdapterInterface $db,
        StorageInterface $storage
    ) {
        parent::__construct($db);
        $this->_name = "member_download_history";
        $this->_key = "id";
        $this->_prototype = MemberDownloadHistory::class;
        $this->cache = $storage;
    }

    public function countDownloads($memberId)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql = "select count(1) as 'count' from member_download_history mh
                JOIN member m ON m.member_id = mh.member_id
                WHERE m.is_deleted = 0
                AND mh.member_id = " . $fp('id');

        $statement = $this->db->query($sql);
        /** @var ResultSetInterface $resultSet */
        $resultSet = $statement->execute(['id' => $memberId])->current();

        if ($resultSet && count($resultSet) > 0) {
            return $resultSet['count'];
        } else {
            return 0;
        }
    }

    public function countDownloadsAnonymous($cookie)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql = "select count(1) as 'count' from member_download_history where anonymous_cookie=" . $fp('id');

        $statement = $this->db->query($sql);
        /** @var ResultSetInterface $resultSet */
        $resultSet = $statement->execute(['id' => $cookie])->current();

        if ($resultSet && count($resultSet) > 0) {
            return $resultSet['count'];
        } else {
            return 0;
        }
    }

    //not needed. 
    public function getAnonymousDLSection($cookie, $member_id = null)
    {
        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql_filter = '';
        if ($member_id) {
            $sql_filter = " and h.member_id =" . $member_id;
        } else {
            $sql_filter = " and h.anonymous_cookie ='" . $cookie . "'";
        }

        $sql = "select c.section_id,count(1) as dls
               from member_download_history h , project p, section_category s, section c
               where h.project_id = p.project_id
               and s.section_id = c.section_id
               and p.project_category_id = s.project_category_id       
               " . $sql_filter . " 
               group by c.section_id";

        $resultSet = $this->fetchAll($sql);

        $sm = new SectionRepository($this->db, $this->cache);
        $sections = $sm->fetchAllSections();
        $sectionsCopy = array();
        $section = array();
        foreach ($sections as $s) {
            $o = null;
            $section = array();
            $section['section_id'] = $s['section_id'];
            $section['name'] = $s['name'];
            $section['description'] = $s['description'];

            foreach ($resultSet as $r) {
                if ($r['section_id'] == $s['section_id']) {
                    $o = $r;
                    break;
                }
            }

            if ($o) {
                $section['dls'] = $o['dls'];
            } else {
                $section['dls'] = 0;
            }
            $sectionsCopy[] = $section;
        }

        return $sectionsCopy;
    }

    /*
    public function getDownloadhistory($member_id){
       /*
        
        $sql = "
                  select
                  m.member_id
                  ,m.project_id
                  ,m.file_id
                  ,m.downloaded_timestamp
                  ,p.project_category_id
                  ,(select c.title from project_category c where p.project_category_id = c.project_category_id) as catTitle
                  ,p.member_id as project_member_id
                  ,p.title
                  ,p.laplace_score
                  ,p.image_small
                  ,p.count_likes
                  ,p.count_dislikes
                  ,m.file_name as file_name
                  ,m.file_type as file_type
                  ,m.file_size as file_size
                  from member_download_history m
                  join stat_projects p on p.project_id = m.project_id
                  where m.member_id = :member_id 
                  order by m.downloaded_timestamp desc
                  limit 1000
        ";
        
        //$result = $this->_db->fetchAll($sql, array("member_id"=>$member_id));
        //return new Zend_Paginator(new Zend_Paginator_Adapter_Array($result ));
        
        // $statement = $this->db->query($sql);
     
        //$resultSet = $statement->execute(['id' => $member_id]);
        $resultSet = $this->fetchAll($sql,['member_id'=>$member_id]);
        $paginator = new Paginator(new ArrayAdapter($resultSet));
        
        return $paginator;
    } 
*/
    /**
     * @param      $member_id
     * @param null $limit
     * @param null $offset
     *
     * @return array|\Laminas\Db\ResultSet\ResultSet
     */
    public function getDownloadhistory($member_id, $limit = null, $offset = null)
    {

        $sql = "
                   SELECT
                   `m`.`member_id`
                   ,`m`.`project_id`
                   ,`m`.`file_id`
                   ,`m`.`downloaded_timestamp`
                   ,`p`.`project_category_id`
                   ,(SELECT `c`.`title` FROM `project_category` `c` WHERE `p`.`project_category_id` = `c`.`project_category_id`) AS `catTitle`
                   ,`p`.`member_id` AS `project_member_id`
                   ,`p`.`title`
                   ,`p`.`laplace_score`
                   ,`p`.`image_small`
                   ,`p`.`count_likes`
                   ,`p`.`count_dislikes`
                   ,`m`.`file_name` AS `file_name`
                   ,`m`.`file_type` AS `file_type`
                   ,`m`.`file_size` AS `file_size`
                   FROM `member_download_history` `m`
                   JOIN `stat_projects` `p` ON `p`.`project_id` = `m`.`project_id`
                   WHERE `m`.`member_id` = :member_id 
                   ORDER BY `m`.`downloaded_timestamp` DESC
                   
         ";
        if (isset($limit)) {
            $sql .= " LIMIT " . $limit;
        }
        if (isset($offset)) {
            $sql .= " OFFSET " . $offset;
        }

        return $this->fetchAll($sql, ['member_id' => $member_id]);
    }

    public function countDownloadhistory($member_id)
    {

        $sql = "
                  SELECT
                  count(1) AS `count`
                  FROM `member_download_history` `m`
                  JOIN `stat_projects` `p` ON `p`.`project_id` = `m`.`project_id`
                  WHERE `m`.`member_id` = :member_id                                    
        ";
        $resultSet = $this->fetchRow($sql,['member_id'=>$member_id],false);
        return $resultSet->count;
    } 
    
}
