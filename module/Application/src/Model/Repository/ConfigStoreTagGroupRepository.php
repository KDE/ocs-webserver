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

namespace Application\Model\Repository;

use Application\Model\Entity\ConfigStoreTagGroup;
use Application\Model\Interfaces\ConfigStoreTagGroupInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;

class ConfigStoreTagGroupRepository extends BaseRepository implements ConfigStoreTagGroupInterface
{
    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "config_store_tag_group";
        $this->_key = "config_store_taggroup_id";
        $this->_prototype = ConfigStoreTagGroup::class;
    }

    public function delete($where)
    {
        $values = array();
        $values['active'] = 0;
        $values['deleted_at'] = new Expression("NOW()");

        return $this->update($values, $where);
    }

    public function getTagGroupsAsIdForStore($store_id, $onlyActive = true)
    {
        $sql = "SELECT `tag_group_id` FROM `config_store_tag_group` WHERE `store_id` = :store_id AND `is_active` = :active ORDER BY `tag_group_id`;";
        $result = $this->fetchAll($sql, array('store_id' => $store_id, 'active' => ($onlyActive ? 1 : 0)));
        if (0 == count($result)) {
            return null;
        }

        return $result;
    }

}
