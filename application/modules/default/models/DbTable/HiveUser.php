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
class Default_Model_DbTable_HiveUser extends Local_Model_Table
{
    protected $_keyColumnsForRow = array('id');
    protected $_key = 'id';
    protected $_name = "hive_user";
    
    /**
     * @param int $category_id
     * @return Num of rows
     **/
    public function fetchCountUsers()
    {
    
    	$sql = "
                SELECT
                    count(1) AS 'count'
                FROM
                    hive_user
                WHERE
                    userdb = 0
    				AND is_imported = 0
                GROUP BY userdb
               ";
    
    	$result = $this->_db->fetchRow($sql);
    
    	return $result['count'];
    
    }
    
    /**
     * @param int $category_id
     * @return Num of rows
     **/
    public function fetchCountAllProjectsForCategory($category_id)
    {
    
    	$sql = "
                SELECT
                    count(1) AS 'count'
                FROM
                    hive_content
                WHERE
                    type = ?
    				AND deletedat = 0
                GROUP BY type
               ";
    
    	$sql = $this->_db->quoteInto($sql, $category_id, 'INTEGER');
    
    	$result = $this->_db->fetchRow($sql);
    
    	return $result['count'];
    
    }
    
    /**
     * @param int $category_id
     * @return Num of rows
     **/
    public function fetchAllUsers($startIndex = 0, $limit = 5)
    {
    
    	$sql = "
                SELECT
                    *,from_unixtime(createtime) as created_at,from_unixtime(lastvisit) as last_online,co.country as country_text
                FROM
                    hive_user
    			JOIN hive_country co ON co.id = hive_user.country
                WHERE
                    userdb = 0
                    AND is_imported = 0
    			LIMIT ".$limit." OFFSET ".$startIndex."
               ";
    
    	$result = $this->_db->fetchAll($sql);
    
    	return $result;
    
    }
    
    
}