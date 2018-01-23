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
class Default_Model_DbTable_MemberDownloadHistory extends Zend_Db_Table_Abstract
{

    protected $_name = "member_download_history";

    public function countDownloads($memberId)
    {
        $select = $this->_db->select()
            ->from('member_download_history')
            ->joinUsing('member', 'member_id')
            ->where('member.is_deleted = ?', 0)
            ->where('member_download_history.member_id = ?', $memberId);
        return count($select->query()->fetchAll());
    }

    public function getDownloadhistory($member_id){                      
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
                      ,(select max(d.downloaded_timestamp) from member_download_history d where m.project_id = d.project_id and d.member_id = m.member_id) as max_downloaded_timestamp
                      from member_download_history m
                      join stat_projects p on p.project_id = m.project_id
                      where m.member_id = :member_id
                      order by m.downloaded_timestamp desc
            ";
           $result = $this->_db->fetchAll($sql, array("member_id"=>$member_id));
          return new Zend_Paginator(new Zend_Paginator_Adapter_Array($result ));
          // return $result; 
      }
}