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
 *
 * Created: 31.07.2017
 */

class Statistics_Model_Data
{

    /** @var Zend_Db_Adapter_Pdo_Abstract */
    protected $_db;

    public  function __construct($options)  {
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
        //$default = (int)(isset($params['isDefaultTableAdapter']) && $params['isDefaultTableAdapter']
        //    || isset($params['default']) && $params['default']);
        unset($params['adapter'], $params['default'], $params['isDefaultTableAdapter']);
        $adapter = Zend_Db::factory($adapter, $params);
        $this->_db = $adapter;
    }

    public function getNewmemberstats(){
          $sql = "SELECT DATE(`created_at`) as memberdate , count(*) as daycount FROM dwh.ods_member_v  group by  memberdate    order by memberdate desc limit 30";
          $result = $this->_db->fetchAll($sql);
          return $result;  
    }
   
    public function getNewprojectstats(){        
          $sql = "SELECT DATE(`created_at`) as projectdate , count(*) as daycount  FROM dwh.ods_project_v  group by  projectdate    order by projectdate desc limit 30";
          $result = $this->_db->fetchAll($sql);
          return $result;  
    }
    

    public function getProject($project_id)
    {
        $sql = "SELECT * FROM ods_project_v WHERE project_id = :projectId";
        $result = $this->_db->fetchAll($sql, array('projectId' => $project_id));
        return $result;
    }

    public function getProjects($limit = 50)
    {
        $limit = (int)$limit;
        $sql = "SELECT * FROM ods_project_v LIMIT {$limit}";
        $result = $this->_db->fetchAll($sql);
        return $result;
    }

    public function getMember($member_id)
    {
        $sql = "SELECT * FROM ods_member_v WHERE member_id = :memberId";
        $result = $this->_db->fetchAll($sql, array('memberId' => (int)$member_id));
        return $result;
    }
    

    public function getMembers($limit = 50)
    {
        $sql = "SELECT * FROM ods_member_v";
        $sql = $this->_db->limit($sql, (int)$limit);
        $result = $this->_db->fetchAll($sql);
        return $result;
    }

}