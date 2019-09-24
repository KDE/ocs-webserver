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
class Default_Model_SectionSupport extends Default_Model_DbTable_SectionSupport
{

     public function fetchAffiliatesForProject($project_id)
    {            
            
        $sql = "
                    SELECT 
                    f.project_id
                    ,m.member_id
                    ,s.active_time
                    ,m.profile_image_url
                    ,m.created_at as member_created_at
                    ,m.username
                    FROM section_support f
                    INNER JOIN support s ON s.id = f.support_id
                    inner join member m on s.member_id = m.member_id and m.is_active=1 AND m.is_deleted=0 
                    WHERE  f.project_id = :project_id 
                    AND s.status_id = 2
                    order by s.active_time desc
         ";
        $resultSet = $this->_db->fetchAll($sql, array('project_id' => $project_id));
        return $resultSet;     
    }
    
    
    public function isMemberAffiliateForProject($project_id, $member_id)
    {            
            
        $cacheName = __FUNCTION__ . md5(serialize($project_id) .''. serialize($member_id));
        $cache = Zend_Registry::get('cache');

        $result = $cache->load($cacheName);

        if ($result) {
            return $result;
        }
        
        $isAffiliate = false;
        
        $sql_object =
            "SELECT 1
                FROM section_support f
                INNER JOIN support s ON s.id = f.support_id
                inner join member m on s.member_id = m.member_id and m.is_active=1 AND m.is_deleted=0 
                WHERE  f.project_id = :project_id 
                AND m.member_id = :member_id
                AND s.status_id = 2";
        $r = $this->getAdapter()->fetchRow($sql_object, array('project_id' => $project_id, 'member_id' => $member_id));
        if ($r) {
            $isAffiliate = true;
        }   
        
        $cache->save($isAffiliate, $cacheName);
        
        return $isAffiliate;
    }
    
    
    public function isMemberAffiliateForMember($member_id, $affiliate_member_id)
    {            
            
        $cacheName = __FUNCTION__ . md5(serialize($member_id) .''. serialize($affiliate_member_id));
        $cache = Zend_Registry::get('cache');

        $result = $cache->load($cacheName);

        if ($result) {
            return $result;
        }
        
        $isAffiliate = false;
        
        $sql_object =
            "SELECT 1
                FROM section_support f
                INNER JOIN project p ON p.project_id = f.project_id
                INNER JOIN support s ON s.id = f.support_id
                inner join member m on s.member_id = m.member_id and m.is_active=1 AND m.is_deleted=0 
                INNER JOIN member m2 on p.member_id = m2.member_id AND m2.is_active=1 AND m2.is_deleted=0 
                WHERE  p.member_id = :member_id
                AND m.member_id = :affiliate_member_id
                AND s.status_id = 2";
        $r = $this->getAdapter()->fetchRow($sql_object, array('affiliate_member_id' => $affiliate_member_id, 'member_id' => $member_id));
        if ($r) {
            $isAffiliate = true;
        }   
        
        $cache->save($isAffiliate, $cacheName);
        
        return $isAffiliate;
    }
} 