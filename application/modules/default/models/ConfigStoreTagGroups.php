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
 * Created: 23.01.2019
 */
class Default_Model_ConfigStoreTagGroups
{

    /**
     * @param int  $store_id
     * @param bool $onlyActive
     *
     * @return null|array
     */
    public function getTagGroupsAsIdForStore($store_id, $onlyActive = true)
    {
        $modelConfigStoreTagGroups = new Default_Model_DbTable_ConfigStoreTagGroups();

        $sql = "SELECT `tag_group_id` FROM `config_store_tag_group` WHERE `store_id` = :store_id AND `is_active` = :active ORDER BY `tag_group_id`;";

        $result = $modelConfigStoreTagGroups->getAdapter()->fetchAll($sql, array('store_id' => $store_id, 'active' => ($onlyActive ? 1 : 0)), Zend_Db::FETCH_COLUMN);

        if (0 == count($result)) {
            return null;
        }

        return $result;
    }

}