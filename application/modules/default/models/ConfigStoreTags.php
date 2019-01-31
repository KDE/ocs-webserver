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
class Default_Model_ConfigStoreTags
{

    /**
     * @param int  $store_id
     * @param bool $onlyActive
     *
     * @return null|array
     */
    public function getTagsAsIdForStore($store_id, $onlyActive = true)
    {
        $modelConfigStoreTags = new Default_Model_DbTable_ConfigStoreTags();

        $sql = "SELECT `tag_id` FROM `config_store_tag` WHERE `store_id` = :store_id AND `is_active` = :active ORDER BY `tag_id`;";

        $result = $modelConfigStoreTags->getAdapter()->fetchAll($sql, array('store_id' => $store_id, 'active' => ($onlyActive ? 1 : 0)), Zend_Db::FETCH_COLUMN);

        if (0 == count($result)) {
            return null;
        }

        return $result;
    }

    public function getPackageTagsForStore($store_id, $onlyActive = true)
    {
        $modelConfigStoreTags = new Default_Model_DbTable_ConfigStoreTags();

        $sql = "
                SELECT t.tag_id, t.tag_name FROM config_store_tag c , tag t
                WHERE c.tag_id = t.tag_id
                and  c.store_id = :store_id AND c.is_active = :active
             ";
        $result = $modelConfigStoreTags->getAdapter()->fetchAll($sql, array('store_id' => $store_id, 'active' => ($onlyActive ? 1 : 0)));
        if (0 == count($result)) {
            return null;
        }

        return $result;
    }

}