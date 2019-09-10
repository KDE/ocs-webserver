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

    public function countDownloadsAnonymous($cookie)
    {
        $sql = "select count(1) as cnt from member_download_history where anonymous_cookie=:cookie";
        $result = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql, array('cookie'=>$cookie));
        return (int)$result['cnt'];
    }

    public function getAnonymousDLSection($cookie, $member_id=null)
    {
       $sql_filter = '';
       if($member_id)
       {
         $sql_filter = " and h.member_id =".$member_id;
       }else
       {
        $sql_filter = " and h.anonymous_cookie ='".$cookie."'";
       }
       $sql = "select c.section_id,count(1) as dls
              from member_download_history h , project p, section_category s, section c
              where h.project_id = p.project_id
              and s.section_id = c.section_id
              and p.project_category_id = s.project_category_id       
              ".$sql_filter." 
              group by c.section_id";
        $result = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);


        $sm =  new Default_Model_Section();
        $sections = $sm->fetchAllSections();
        foreach ($sections as &$s) {
          $o = null;
          foreach ($result as $r ) {
            if($r['section_id'] == $s['section_id']){
              $o = $r;              
              break;
            }
          }

          if($o) {
            $s['dls'] = $o['dls'];
          }else{
            $s['dls'] = 0;
          }          
        }        
        return $sections;
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
                      from member_download_history m
                      join stat_projects p on p.project_id = m.project_id
                      where m.member_id = :member_id
                      order by m.downloaded_timestamp desc
                      limit 1000
            ";
           $result = $this->_db->fetchAll($sql, array("member_id"=>$member_id));
          return new Zend_Paginator(new Zend_Paginator_Adapter_Array($result ));
      }
}
