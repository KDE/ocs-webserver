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

use Application\Model\Entity\ConfigStoreTag;
use Application\Model\Interfaces\ConfigStoreTagInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ConfigStoreTagRepository extends BaseRepository implements ConfigStoreTagInterface
{
    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "config_store_tag";
        $this->_key = "config_store_tag_id";
        $this->_prototype = ConfigStoreTag::class;
    }

    //////////////////////// FROM _MODEL
    public function getPackageTagsForStore($store_id, $onlyActive = true)
    {
        $sql = "
                SELECT `t`.`tag_id`, `t`.`tag_name` FROM `config_store_tag` `c` , `tag` `t`
                WHERE `c`.`tag_id` = `t`.`tag_id`
                AND  `c`.`store_id` = :store_id AND `c`.`is_active` = :active
             ";

        return $this->fetchAll($sql, array('store_id' => $store_id, 'active' => ($onlyActive ? 1 : 0)));
    }

    public function getTagsAsIdForStore($store_id, $onlyActive = true)
    {
        $sql = "SELECT `tag_id` FROM `config_store_tag` WHERE `store_id` = :store_id AND `is_active` = :active ORDER BY `tag_id`;";
        $result = $this->fetchAll($sql, array('store_id' => $store_id, 'active' => ($onlyActive ? 1 : 0)));
        if (0 == count($result)) {
            return null;
        }

        return $result;
    }
}
