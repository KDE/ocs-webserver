<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Model\Repository;

use Application\Model\Entity\MemberSettingValue;
use Application\Model\Interfaces\MemberSettingValueInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;


class MemberSettingValueRepository extends BaseRepository implements MemberSettingValueInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "member_setting_value";
        $this->_key = "member_setting_value_id";
        $this->_prototype = MemberSettingValue::class;
    }

    public function delete($where)
    {
        $values = array();

        $values['active'] = 0;
        $values['deleted_at'] = new Expression("NOW()");

        //$savedRow = $this->db->update($values, 'collection_id = '.$collection_id . ' AND project_id = ' . $project_id);
        $sql = new Sql($this->db);
        $update = $sql->update($this->_name)->set($values)->where($where);
        $statement = $sql->prepareStatementForSqlObject($update);

        return $statement->execute();
    }

    /**
     * @param integer $member_id
     * @param integer $item_id
     *
     * @return array
     */
    public function fetchMemberSettingItem($member_id, $item_id)
    {
        $sql = "
            SELECT             
             `v`.`member_setting_item_id`            
            ,`v`.`value`             
            FROM `member_setting_value` `v`                        
            WHERE `v`.`member_id` = :member_id AND  `member_setting_item_id` =:item_id
        ";
        $result = $this->fetchRow($sql, array('member_id' => $member_id, 'item_id' => $item_id));

        if (empty($result)) {
            return $result;
        }

        return $result;
    }

}
