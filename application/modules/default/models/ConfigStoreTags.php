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
     * @param int $store_id
     *
     * @return null|Zend_Db_Table_Rowset_Abstract
     */
    public function getTagsForStore($store_id)
    {
        $modelConfigStoreTags = new Default_Model_DbTable_ConfigStoreTags();

        $result = $modelConfigStoreTags->fetchAll(array('store_id = ?' => (int)$store_id));

        if (0 == $result->count()) {
            return null;
        }

        return $result;
    }

}