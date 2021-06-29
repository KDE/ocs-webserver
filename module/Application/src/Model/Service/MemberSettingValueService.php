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

namespace Application\Model\Service;

use Application\Model\Interfaces\MemberSettingValueInterface;
use Application\Model\Service\Interfaces\MemberSettingValueServiceInterface;
use Laminas\Db\Sql\Expression;

class MemberSettingValueService extends BaseService implements MemberSettingValueServiceInterface
{
    protected $memberSettingValueRepository;

    public function __construct(
        MemberSettingValueInterface $memberSettingValueRepository
    ) {
        $this->memberSettingValueRepository = $memberSettingValueRepository;
    }

    public function fetchMemberSettingItem($member_id, $item_id)
    {
        $sql = "
            SELECT             
             `v`.`member_setting_item_id`            
            ,`v`.`value`             
            FROM `member_setting_value` `v`                        
            WHERE `v`.`member_id` = :member_id AND  `member_setting_item_id` =:item_id
        ";

        return $this->memberSettingValueRepository->fetchRow(
            $sql, array(
                    'member_id' => $member_id,
                    'item_id'   => $item_id,
                )
        );
    }

    public function findMemberSettings($memberid, $groupid)
    {
        $sql = "
            SELECT 
            
            `t`.`member_setting_item_id`
            ,`t`.`title`
            ,`v`.`value` 
            ,`v`.`member_setting_value_id`
            FROM 
            `member_setting_item` `t`
            LEFT JOIN `member_setting_value` `v` ON `t`.`member_setting_item_id` = `v`.`member_setting_item_id` AND `v`.`member_id` =:memberid
            WHERE `t`.`member_setting_group_id` = :groupid
        ";

        return $this->memberSettingValueRepository->fetchAll(
            $sql, array(
                    'memberid' => $memberid,
                    'groupid'  => $groupid,
                )
        );
    }

    public function updateOrInsertSetting(
        $member_id,
        $member_setting_item_id,
        $member_setting_value_id,
        $value
    ) {

        if ($member_setting_value_id) {
            $this->updateSingle($member_setting_value_id, $value);

            return;
        }

        $sql = "
            SELECT count(*) AS `cnt` FROM  `member_setting_value` WHERE `member_id` = :member_id 
                AND `member_setting_item_id` = :member_setting_item_id
        ";
        $r = $this->memberSettingValueRepository->fetchRow(
            $sql, array(
                    'member_id'              => $member_id,
                    'member_setting_item_id' => $member_setting_item_id,
                )
        );
        if ($r['cnt'] == 0) {
            //insert
            $this->insert($member_setting_item_id, $value, $member_id);
        } else {
            //update
            $this->update($member_setting_item_id, $value, $member_id);
        }
    }

    public function updateSingle($valueid, $value)
    {
        $this->memberSettingValueRepository->update(
            array(
                'value'                   => $value,
                'changed_at'              => new Expression('Now()'),
                'is_active'               => 1,
                'member_setting_value_id' => $valueid,
            )
        );
    }

    public function insert($itemid, $value, $memberid)
    {
        $values = array(
            'member_setting_item_id' => $itemid,
            'value'                  => $value,
            'member_id'              => $memberid,
        );
        $this->memberSettingValueRepository->insert($values);
    }

    public function update($itemid, $value, $memberid)
    {
        $this->memberSettingValueRepository->update(
            array(
                'value'      => $value,
                'changed_at' => new Expression('Now()'),
                'is_active'  => 1,
            ), 'member_setting_item_id=' . $itemid . ' and member_id = ' . $memberid
        );
    }

}