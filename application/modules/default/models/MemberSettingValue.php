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
 */

class Default_Model_MemberSettingValue
{
   
    public function insert($itemid,$value,$memberid)
    {
        $tbl = new Default_Model_DbTable_MemberSettingValue();
        $values = array(
                    'member_setting_item_id' => $itemid
                    ,'value' => $value
                    ,'member_id' => $memberid
                );
        $tbl->insert($values);
    }

    public function update($itemid,$value,$memberid)
    {
       
        $tbl = new Default_Model_DbTable_MemberSettingValue();
        $tbl->update(array('value' => $value
                    ,'changed_at'=> new Zend_Db_Expr('Now()')
                    ,'is_active' => 1                    
                )
                ,'member_setting_item_id='.$itemid.' and member_id = '.$memberid
                );
    }

    public function updateSingle($valueid,$value)
    {

        $tbl = new Default_Model_DbTable_MemberSettingValue();
        $tbl->_db->update($tbl->_name, array('value' => $value
                    ,'changed_at'=> new Zend_Db_Expr('Now()')
                    ,'is_active' => 1                    
                )
                ,'member_setting_value_id='.$valueid
                );
    }

    public function fetchMemberSettingItem($member_id,$item_id)
    {
        $sql = "
            select             
             v.member_setting_item_id            
            ,v.value             
            from member_setting_value v                        
            where v.member_id = :member_id and  member_setting_item_id =:item_id
        ";
        $result = $this->getAdapter()->fetchRow($sql, array('member_id' => $member_id,'item_id' => $item_id));
        return $result;
    }

    public function findMemberSettings($memberid,$groupid)
    {
        $sql = "
            select 
            
            t.member_setting_item_id
            ,t.title
            ,v.value 
            ,v.member_setting_value_id
            from 
            member_setting_item t
            left join member_setting_value v on t.member_setting_item_id = v.member_setting_item_id and v.member_id =:memberid
            where t.member_setting_group_id = :groupid
        ";
        $result = $this->getAdapter()->fetchAll($sql, array('memberid' => $memberid,'groupid' => $groupid));
        return $result;
    }

    public function updateOrInsertSetting($member_id
            ,$member_setting_item_id
            ,$member_setting_value_id=null            
            ,$value)
    {
        
        if($member_setting_value_id){
            $this->updateSingle($member_setting_value_id,$value);
            return;
        }

        $sql = "
            select count(*) as cnt from  member_setting_value where member_id = :member_id 
                and member_setting_item_id = :member_setting_item_id
        ";
        $r = $this->getAdapter()->fetchRow($sql, array(
                'member_id' => $member_id
                ,'member_setting_item_id' => $member_setting_item_id
            ));
        if($r['cnt'] ==0){        
            //insert
            $this->insert($member_setting_item_id,$value,$member_id);    
        }else
        {
            //update
            $this->update($member_setting_item_id,$value,$member_id);    
        }                        
    }


    /**
     * @return Zend_Db_Adapter_Abstract
     */
    private function getAdapter()
    {
        return Zend_Db_Table::getDefaultAdapter();
    }



}