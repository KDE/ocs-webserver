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
class Default_Model_Dwhdata 
{

      /** @var Zend_Db_Adapter_Pdo_Abstract */
      protected $_db;

      public  function __construct()  {         
          $options =  Zend_Registry::get('config')->settings->dwh->toArray();
          if (isset($options['db'])) {
              $this->initDbAdapter($options['db']);
          } else {
              throw new Exception('configuration parameter for database connection needed');
          }
      }

      private function initDbAdapter($db)
      {
          $adapter = $db['adapter'];
          $params = $db['params'];
          unset($params['adapter'], $params['default'], $params['isDefaultTableAdapter']);
          $adapter = Zend_Db::factory($adapter, $params);
          $this->_db = $adapter;
      }

      public function getDownloadhistory($member_id){           
           $sql = "select 
                                m.member_id
                                ,m.collection_id
                                ,m.project_id
                                ,m.file_id
                                ,m.user_id
                                ,m.downloaded_timestamp
                                ,m.downloaded_ip
                                ,p.project_category_id
                                ,(select c.title from category c where p.project_category_id = c.project_category_id) as catTitle
                                ,p.title                               
                                ,p.laplace_score
                                ,p.image_small
                                ,p.count_likes
                                ,p.count_dislikes
                                ,f.name as file_name
                                ,f.type as file_type
                                ,f.size as file_size
                                ,f.ocs_compatible as file_ocs_compatible
                                ,f.downloaded_count as file_downloaded_count
                                ,f.active as file_active
                                ,(select max(d.downloaded_timestamp) from dwh.member_dl_history d where m.project_id = d.project_id and d.user_id = m.user_id) as max_downloaded_timestamp
                                from dwh.member_dl_history m
                                join dwh.project p on p.project_id = m.project_id
                                join dwh.files f on m.file_id = f.id
                                where m.user_id = :member_id
                                order by m.project_id, m.downloaded_timestamp desc
                        ";
           $result = $this->_db->fetchAll($sql, array("member_id"=>$member_id));
          return new Zend_Paginator(new Zend_Paginator_Adapter_Array($result ));
          // return $result; 
      }
}