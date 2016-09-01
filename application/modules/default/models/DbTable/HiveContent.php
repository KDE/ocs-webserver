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
class Default_Model_DbTable_HiveContent extends Local_Model_Table
{
    protected $_keyColumnsForRow = array('id');
    protected $_key = 'id';
    protected $_name = "hive_content";

    
    
    
    /**
     * @param int $category_id
     * @return Num of rows
     **/
    public function fetchCountProjectsForCategory($category_id)
    {
    
    	$sql = "
                SELECT
                    count(1) AS 'count'
                FROM
                    hive_content
                WHERE
                    type = ?
                    AND is_imported = 0
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
     * @param int $category_id Hive-Dat-Id
     * @param int $startIndex Default 0
     * @param int $limit Default 5
     * @param int $alsoDeleted Default false
     * @return Num of rows
     **/
    public function fetchAllProjectsForCategory($category_id, $startIndex = 0, $limit = 5, $alsoDeleted = false)
    {
    
    	$sql = "
                SELECT
                    *,convert(cast(convert(description using  latin1) as binary) using utf8) as description_utf8,convert(cast(convert(name using  latin1) as binary) using utf8) as name_utf8,convert(cast(convert(changelog using  latin1) as binary) using utf8) as changelog_utf8,from_unixtime(created) as created_at,from_unixtime(changed) as changed_at, CASE WHEN deletedat > 0 THEN FROM_UNIXTIME(deletedat) ELSE null END as deleted_at
                FROM
                    hive_content
                WHERE
                    type = ?
                    AND is_imported = 0
    			";
    	if(!$alsoDeleted) { 
	    	$sql .= "
	    				AND deletedat = 0
	    			    AND status = 1";
    	}
    	$sql .= "
    			LIMIT ".$limit." OFFSET ".$startIndex."
               ";
    
    	$sql = $this->_db->quoteInto($sql, $category_id, 'INTEGER');
    	 
    	$result = $this->_db->fetchAll($sql);
    
    	return $result;
    
    }
    
    /**
     * @param int $category_id
     * @return Num of rows
     **/
    public function fetchCountOcsProjectsForCategory($category_id)
    {
    
    	$sql = "
                SELECT
                    count(1) AS 'count'
                FROM
    				project p
    			JOIN hive_content h ON 
                WHERE
                    type = ?
                    AND is_imported = 0
                GROUP BY project_category_id
               ";
    
    	$sql = $this->_db->quoteInto($sql, $category_id, 'INTEGER');
    
    	$result = $this->_db->fetchRow($sql);
    
    	return $result['count'];
    
    }
    
    /**
     * @return Num of rows
     **/
    public function fetchCountProjects()
    {
    
    	$sql = "
                SELECT
                    count(1) AS 'count'
                FROM
                    hive_content
                WHERE
                    is_imported = 0
               ";
    
    	$result = $this->_db->fetchRow($sql);
    
    	return $result['count'];
    
    }
    
    /**
     * @return array
     */
    public function fetchOcsCategories()
    {
    	$resultSet = $this->queryOcsCategories();
    	return $resultSet;
    }
    
    /**
     * @return array
     */
    public function fetchHiveCategories()
    {
   		$resultSet = $this->queryCategories();
    	return $resultSet;
    }
    
    /**
     * @return array
     */
    public function fetchHiveCategory($cat_id)
    {
    	$result = $this->queryCategory($cat_id);
    	return $result;
    }
    
    /**
     * @return array
     */
    private function queryCategory($id)
    {
    	$sql = "SELECT id, `desc` FROM hive_content_category WHERE id = ".$id." ORDER BY `desc`;";
    	$resultSet = $this->_db->fetchRow($sql);
    	return $resultSet;
    }
    
    /**
     * @return array
     */
    public function fetchOcsCategory($cat_id)
    {
    	$result = $this->queryOcsCategory($cat_id);
    	return $result;
    }
    
    /**
     * @return array
     */
    private function queryOcsCategory($id)
    {
    	$sql = "SELECT project_category_id as id, `title` as `desc` FROM project_category WHERE is_deleted = 0 AND is_active = 1 AND project_category_id = ".$id." ORDER BY `title`;";
    	$resultSet = $this->_db->fetchRow($sql);
    	return $resultSet;
    }
    
    /**
     * @return array
     */
    private function queryOcsCategories()
    {
    	$sql = "SELECT project_category_id as id, `title` as `desc` FROM project_category WHERE is_deleted = 0 AND is_active = 1 ORDER BY `title`;";
    	$resultSet = $this->_db->fetchAll($sql);
    	return $resultSet;
    }
    
    /**
     * @return array
     */
    private function queryCategories()
    {
    	$sql = "SELECT id, `desc` FROM hive_content_category ORDER BY `desc`;";
    	$resultSet = $this->_db->fetchAll($sql);
    	return $resultSet;
    }
    
    
    
    /**
     * @param array $resultSetConfig
     * @return array
     */
    private function createCategoriesArray($resultSetConfig)
    {
    	$result = array();
    	foreach ($resultSetConfig as $element) {
    		$result[$element['id']] = $element['desc'];
    	}
    	return $result;
    }
    
}