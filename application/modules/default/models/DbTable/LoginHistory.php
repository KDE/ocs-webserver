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
class Default_Model_DbTable_LoginHistory extends Zend_Db_Table_Abstract
{

    protected $_name = "login_history";
    protected $_rowClass = 'Default_Model_DbRow_LoginHistory';

    /** @var  Zend_Cache_Core */
    protected $cache;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->cache = Zend_Registry::get('cache');
    }
    
    /**
     * @param int|array $memberId
     *
     * @return array
     * @throws Zend_Cache_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchLastLoginData($memberId)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5($memberId);
        
        $data = null;

        if (false === ($data = $cache->load($cacheName))) {
            $sql = '
            SELECT node.*
            FROM login_history AS node
            WHERE node.member_id = '.$memberId.'
            ORDER BY node.id DESC
            LIMIT 1
            ';
            $data = $this->_db->query($sql, $memberId)->fetchAll();
            if (count($data) == 0) {
                $data = array();
            } else {
                $data = $data[0];
            }
            //$cache->save($data, $cacheName, array(), 3600);
        }

        return $data;
    }
}